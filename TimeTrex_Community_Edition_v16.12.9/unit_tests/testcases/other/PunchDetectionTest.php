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

class PunchDetectionTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $user_obj = null;
	protected $branch_id = null;
	protected $department_id = null;
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

		$this->branch_id[] = $dd->createBranch( $this->company_id, 10 ); //NY
		$this->branch_id[] = $dd->createBranch( $this->company_id, 20 ); //Seattle
		$this->branch_id[] = $dd->createBranch( $this->company_id, 30 ); //Toronto
		$this->branch_id[] = $dd->createBranch( $this->company_id, 40 ); //Vancouver

		$this->department_id[] = $dd->createDepartment( $this->company_id, 10 ); //Sales
		$this->department_id[] = $dd->createDepartment( $this->company_id, 20 ); //Construction
		$this->department_id[] = $dd->createDepartment( $this->company_id, 30 ); //Administration
		$this->department_id[] = $dd->createDepartment( $this->company_id, 40 ); //Inspection

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->user_obj = $ulf->getById( $this->user_id )->getCurrent();


		//Don't in each test now, so we can control the new_shift_trigger_time
		//$this->createPayPeriodSchedule( 10 );
		//$this->createPayPeriods();
		//$this->getAllPayPeriods();

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular

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
			$sf->setPunchTag( $data['job_item_id'] );
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

	function createPayPeriodSchedule( $shift_assigned_day = 10, $new_shift_trigger_time = 14400 ) {
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
		$ppsf->setNewDayTriggerTime( $new_shift_trigger_time );
		$ppsf->setMaximumShiftTime( ( 16 * 3600 ) );
		$ppsf->setShiftAssignedDay( $shift_assigned_day );

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
		$max_pay_periods = 29;

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

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = [];

		//Get only system totals.
		//$udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, $start_date, $end_date);
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, [ 5, 20, 30, 40, 100, 110 ], $start_date, $end_date );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $udt_obj ) {
				$date_totals[$udt_obj->getDateStamp()][] = [
						'date_stamp' => $udt_obj->getDateStamp(),
						'id'         => $udt_obj->getId(),

						//'user_date_id' => $udt_obj->getUserDateId(),
						//Keep legacy status_id/type_id for now, so we don't have to change as many unit tests.
						'status_id'  => $udt_obj->getStatus(),
						'type_id'    => $udt_obj->getType(),
						//'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),

						'object_type_id' => $udt_obj->getObjectType(),
						'object_id'      => $udt_obj->getObjectID(),

						'branch_id'     => $udt_obj->getBranch(),
						'department_id' => $udt_obj->getDepartment(),
						'total_time'    => $udt_obj->getTotalTime(),
						'name'          => $udt_obj->getName(),
						//Override only shows for SYSTEM override columns...
						//Need to check Worked overrides too.
						'tmp_override'  => $udt_obj->getOverride(),
				];
			}
		}

		return $date_totals;
	}

	function getPunchDataArray( $start_date, $end_date ) {
		$plf = new PunchListFactory();

		$plf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $start_date, $end_date );
		if ( $plf->getRecordCount() > 0 ) {
			//Only return punch_control data for now
			$i = 0;
			$prev_punch_control_id = null;
			foreach ( $plf as $p_obj ) {
				if ( $prev_punch_control_id == null || $prev_punch_control_id != $p_obj->getPunchControlID() ) {
					$date_stamp = TTDate::getBeginDayEpoch( $p_obj->getPunchControlObject()->getDateStamp() );
					$p_obj->setUser( $this->user_id );
					$p_obj->getPunchControlObject()->setPunchObject( $p_obj );

					$retarr[$date_stamp][$i] = [
							'id'         => $p_obj->getPunchControlObject()->getID(),
							'date_stamp' => $date_stamp,
							'shift_data' => $p_obj->getPunchControlObject()->getShiftData(),
					];

					$prev_punch_control_id = $p_obj->getPunchControlID();
					$i++;
				}
			}

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		}

		return []; //Return blank array to make count() not complain about FALSE.
	}

	function createMealPolicy( $company_id, $type ) {
		$mpf = new MealPolicyFactory();
		$mpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 1hr lunch: Detect by Time Window
				$mpf->setName( 'Normal - Time Window' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
				$mpf->setAutoDetectType( 10 );

				$mpf->setStartWindow( ( 3 * 3600 ) );
				$mpf->setWindowLength( ( 2 * 3600 ) );
				$mpf->setIncludeLunchPunchTime( false );
				break;
			case 110: //Normal 1hr lunch: Detect by Punch Time
				$mpf->setName( 'Normal - Punch Time' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );  //6hrs into shift.
				$mpf->setAmount( 3600 );
				$mpf->setAutoDetectType( 20 );

				$mpf->setMinimumPunchTime( ( 60 * 30 ) ); ///0.5hr
				$mpf->setMaximumPunchTime( ( 60 * 75 ) ); //1.25hr
				$mpf->setIncludeLunchPunchTime( false );
				break;
			case 112: //Normal 1hr lunch: Detect by Punch Time
				$mpf->setName( 'Normal - Punch Time' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( ( 3600 * 4 ) ); //4hrs into shift.
				$mpf->setAmount( 3600 );
				$mpf->setAutoDetectType( 20 );

				$mpf->setMinimumPunchTime( ( 60 * 30 ) ); ///0.5hr
				$mpf->setMaximumPunchTime( ( 60 * 75 ) ); //1.25hr
				$mpf->setIncludeLunchPunchTime( false );
				break;

		}

		$mpf->setPayCode( $this->policy_ids['pay_code'][100] );

		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text( 'Meal Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createBreakPolicy( $company_id, $type ) {
		$bpf = new BreakPolicyFactory();
		$bpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 15min break: Detect by Time Window
				$bpf->setName( '15min (100)' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( ( 3600 * 0.5 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setAutoDetectType( 10 );

				$bpf->setStartWindow( ( 1 * 3600 ) );
				$bpf->setWindowLength( ( 1 * 3600 ) );

				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				break;
			case 110: //Normal 15min break: Detect by Punch Time
				$bpf->setName( '15min Morning (110)' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( ( 3600 * 0.5 ) ); //Morning Break
				$bpf->setAmount( 60 * 15 );
				$bpf->setAutoDetectType( 20 );

				$bpf->setMinimumPunchTime( ( 60 * 5 ) ); ///5min
				$bpf->setMaximumPunchTime( ( 60 * 25 ) ); //25min

				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				break;
			case 112: //Normal 15min break: Detect by Punch Time
				$bpf->setName( '15min Afternoon (112)' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( ( 3600 * 6.5 ) ); //Afternoon Break
				$bpf->setAmount( 60 * 15 );
				$bpf->setAutoDetectType( 20 );

				$bpf->setMinimumPunchTime( ( 60 * 5 ) ); ///5min
				$bpf->setMaximumPunchTime( ( 60 * 25 ) ); //25min

				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				break;

			case 200: //Multiple breaks, up to 30mins in total. Detect by Punch Time
				$bpf->setName( 'Multiple Breaks (200)' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( ( 3600 * 0.5 ) ); //Afternoon Break
				$bpf->setAmount( 60 * 30 );
				$bpf->setAutoDetectType( 20 );

				$bpf->setMinimumPunchTime( ( 60 * 5 ) ); ///5min
				$bpf->setMaximumPunchTime( ( 60 * 25 ) ); //25min

				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( true );
				break;

		}

		$bpf->setPayCode( $this->policy_ids['pay_code'][100] );

		if ( $bpf->isValid() ) {
			$insert_id = $bpf->Save();
			Debug::Text( 'Break Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Break Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function getPreviousPunch( $epoch ) {
		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$plf->getPreviousPunchByUserIDAndEpoch( $this->user_id, $epoch );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::Text( ' Found Previous Punch within Continuous Time from now...', __FILE__, __LINE__, __METHOD__, 10 );
			$prev_punch_obj = $plf->getCurrent();
			$prev_punch_obj->setUser( $this->user_id );

			return $prev_punch_obj;
		}
		Debug::Text( ' Previous Punch NOT found!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function getDefaultPunchSettings( $epoch, $station_obj = null, $permission_obj = null, $latitude = null, $longitude = null, $position_accuracy = null ) {
		$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */
		return $pf->getDefaultPunchSettings( $this->user_obj, $epoch, $station_obj, $permission_obj, $latitude, $longitude );
	}

	function getPunchTags( $company_id, $filter_data ) {
		$retarr = [];

		$ptlf = TTnew( 'PunchTagListFactory' );
		$ptlf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );

		if ( $ptlf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $ptlf as $pt_obj ) {
				$retarr[] = $pt_obj->getObjectAsArray( null, null );
			}
		}

		return $retarr;
	}

	function getBranches( $company_id, $filter_data ) {
		$retarr = [];

		$ptlf = TTnew( 'BranchListFactory' );
		$ptlf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );

		if ( $ptlf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $ptlf as $pt_obj ) {
				$retarr[] = $pt_obj->getObjectAsArray( null, null );
			}
		}

		return $retarr;
	}

	function getDepartments( $company_id, $filter_data ) {
		$retarr = [];

		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );

		if ( $dlf->getRecordCount() > 0 ) {
			$retarr = [];
			foreach ( $dlf as $d_obj ) {
				$retarr[] = $d_obj->getObjectAsArray( null, null );
			}
		}

		return $retarr;
	}

	function updateBranchEmployeeCriteria( $id, $data ) {
		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getById( $id );
		if ( $blf->getRecordCount() == 1 ) {
			$b_obj = $blf->getCurrent(); /** @var BranchFactory $b_obj */

			//User Group
			if ( isset( $data['user_group_selection_type'] ) ) {
				$b_obj->setUserGroupSelectionType( $data['user_group_selection_type'] );
			} else {
				$b_obj->setUserGroup( 10 );
			}
			if ( isset( $data['user_group_ids'] ) ) {
				$b_obj->setUserGroup( $data['user_group_ids'] );
			}

			//User Title
			if ( isset( $data['user_title_selection_type_id'] ) ) {
				$b_obj->setUserTitleSelectionType( $data['user_title_selection_type_id'] );
			} else {
				$b_obj->setUserTitle( 10 );
			}
			if ( isset( $data['title_ids'] ) ) {
				$b_obj->setUserTitle( $data['title_ids'] );
			}

			//User Default Branch
			if ( isset( $data['user_default_branch_selection_type_id'] ) ) {
				$b_obj->setUserDefaultBranchSelectionType( $data['user_default_branch_selection_type_id'] );
			} else {
				$b_obj->setUserDefaultBranch( 10 );
			}
			if ( isset( $data['branch_ids'] ) ) {
				$b_obj->setUserDefaultBranch( $data['branch_ids'] );
			}

			//User Default Department
			if ( isset( $data['user_default_department_selection_type_id'] ) ) {
				$b_obj->setUserDefaultDepartmentSelectionType( $data['user_default_department_selection_type_id'] );
			} else {
				$b_obj->setUserDefaultDepartment( 10 );
			}
			if ( isset( $data['department_ids'] ) ) {
				$b_obj->setUserDefaultDepartment( $data['department_ids'] );
			}

			if ( $b_obj->isValid() ) {
				$b_obj->Save();
			}
		}
	}

	function updateDepartmentEmployeeCriteria( $id, $data ) {
		$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
		$dlf->getById( $id );
		if ( $dlf->getRecordCount() == 1 ) {
			$d_obj = $dlf->getCurrent(); /** @var DepartmentFactory $d_obj */

			//User Group
			if ( isset( $data['user_group_selection_type'] ) ) {
				$d_obj->setUserGroupSelectionType( $data['user_group_selection_type'] );
			} else {
				$d_obj->setUserGroup( 10 );
			}
			if ( isset( $data['user_group_ids'] ) ) {
				$d_obj->setUserGroup( $data['user_group_ids'] );
			}

			//User Title
			if ( isset( $data['user_title_selection_type_id'] ) ) {
				$d_obj->setUserTitleSelectionType( $data['user_title_selection_type_id'] );
			} else {
				$d_obj->setUserTitle( 10 );
			}
			if ( isset( $data['title_ids'] ) ) {
				$d_obj->setUserTitle( $data['title_ids'] );
			}

			//User Default Branch
			if ( isset( $data['user_punch_branch_selection_type_id'] ) ) {
				$d_obj->setUserPunchBranchSelectionType( $data['user_punch_branch_selection_type_id'] );
			} else {
				$d_obj->setUserPunchBranch( 10 );
			}
			if ( isset( $data['branch_ids'] ) ) {
				$d_obj->setUserPunchBranch( $data['branch_ids'] );
			}

			//User Default Department
			if ( isset( $data['user_default_department_selection_type_id'] ) ) {
				$d_obj->setUserDefaultDepartmentSelectionType( $data['user_default_department_selection_type_id'] );
			} else {
				$d_obj->setUserDefaultDepartment( 10 );
			}
			if ( isset( $data['department_ids'] ) ) {
				$d_obj->setUserDefaultDepartment( $data['department_ids'] );
			}

			if ( $d_obj->isValid() ) {
				$d_obj->Save();
			}
		}
	}

	/*
	 Tests:
		- Normal In/Out punches in the middle of the day with no policies
		- Normal In/Out punches around midnight with no policies
		- Lunch punches with Time Window detection
		- Lunch punches with Punch Time detection
		- Break punches with Time Window detection
		- Break punches with Punch Time detection
	*/

	/**
	 * @group PunchDetection_testNoMealOrBreakA
	 */
	function testNoMealOrBreakA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testNoMealOrBreakB
	 */
	function testNoMealOrBreakB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 11:30PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp2 . ' 12:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp2 . ' 5:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testNoMealOrBreakBWithFutureShiftA
	 */
	function testNoMealOrBreakBWithFutureShiftA() {
		global $dd;

		//
		//Test case where a auto-punch scheduled shift is created in the future (ie: 21:00 - 23:00) and the employee is punching earlier than that.
		//

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		//$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create future shift first.
		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 9:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );
		$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 10:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 2, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][1]['shift_data']['punches'][1]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testNoMealOrBreakBWithFutureShiftB
	 */
	function testNoMealOrBreakBWithFutureShiftB() {
		global $dd;

		//
		//Test case where a auto-punch scheduled shift is created in the future (ie: 21:00 - 23:00) and the employee is punching earlier than that, but also has transfer punches.
		//

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		//$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create future shift first.
		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 9:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );
		$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 10:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 3, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][1]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][1]['shift_data']['punches'][1]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testMealTimeWindowA
	 */
	function testMealTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testMealTimeWindowB
	 */
	function testMealTimeWindowB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Lunch
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 11:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testMealTimeWindowC
	 */
	function testMealTimeWindowC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:30PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Lunch
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 4:30PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testMealPunchTimeWindowA
	 */
	function testMealPunchTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testMealPunchTimeWindowB
	 */
	function testMealPunchTimeWindowB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:30PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakTimeWindowA
	 */
	function testBreakTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break']
		);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 9:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 9:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakTimeWindowB
	 */
	function testBreakTimeWindowB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break']
		);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakTimeWindowC
	 */
	function testBreakTimeWindowC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break']
		);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		//Check all normal punches within the time window of the previous normal punch. This triggered a bug before.
		$punch_time = strtotime( $date_stamp . ' 3:30PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakPunchTimeWindowA
	 */
	function testBreakPunchTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakPunchTimeWindowB
	 */
	function testBreakPunchTimeWindowB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakPunchTimeWindowC
	 */
	function testBreakPunchTimeWindowC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   ////Break, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:03AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testBreakPunchTimeWindowD
	 */
	function testBreakPunchTimeWindowD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 2:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 2:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 30, $punch_type_id );   //Break
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 3, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 6, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][4]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][4]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][5]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][5]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testZeroNewShiftTriggerMealTimeWindowA
	 */
	function testZeroNewShiftTriggerMealTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10, 0 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testZeroNewShiftTriggerTimeMealPunchTimeWindowA
	 */
	function testZeroNewShiftTriggerTimeMealPunchTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10, 0 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 8:00AM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch, as detected based on smarter algorithm to determine based on position within schedule.
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithNoMealOrBreakA
	 */
	function testScheduleWithNoMealOrBreakA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
			'start_time'         => ' 8:00AM',
			'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithMealTimeWindowA
	 */
	function testScheduleWithMealTimeWindowA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );


		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 20, $punch_type_id );   //Lunch
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 2, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertCount( 4, $punch_arr[$date_epoch][0]['shift_data']['punches'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return true;
	}


	/**
	 * @group PunchDetection_testScheduleWithMissingInPunchA
	 */
	function testScheduleWithMissingInPunchA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In


		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out


		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithMissingInPunchB
	 */
	function testScheduleWithMissingInPunchB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In


		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out


		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithMissingInPunchC
	 */
	function testScheduleWithMissingInPunchC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In


		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out


		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithMissingInPunchD
	 */
	function testScheduleWithMissingInPunchD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		$dd->createPunch( $this->user_id, 10, 10, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out


		return true;
	}

	/**
	 * @group PunchDetection_testScheduleWithMissingInPunchD2
	 */
	function testScheduleWithMissingInPunchD2() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out


		return true;
	}










	/**
	 * @group PunchDetection_testSplitShiftScheduleWithMissingInPunchA
	 */
	function testSplitShiftScheduleWithMissingInPunchA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		//$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '11:00AM',
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '1:00PM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 7:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 9:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In


		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 11:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 11:59AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out



		//This matches the 2nd shift in the day.
		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 2:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In


		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		return true;
	}


	/**
	 * @group PunchDetection_testSplitShiftScheduleWithMissingInPunchA2
	 */
	function testSplitShiftScheduleWithMissingInPunchA2() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		//$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '11:00AM',
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '1:00PM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 7:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 11:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );




		//This matches the 2nd shift in the day.
		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testAbsenceScheduleWithMissingPunchA
	 */
	function testAbsenceScheduleWithMissingPunchA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
				'status_id'			 => 20, //20=Absent
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out

		return true;
	}

	/**
	 * @group PunchDetection_test24OnCallShiftScheduleStartA
	 */
	function test24OnCallShiftScheduleStartA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		//$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '1:00AM',
				'end_time'           => '8:00AM',
				'status_id'			 => 20, //20=Absent
				'branch_id'			 => $this->branch_id[1],
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
				'status_id'			 => 10, //10=Working
				'branch_id'			 => $this->branch_id[0],

		] );

		$punch_time = strtotime( $date_stamp . ' 1:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 2:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 5:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );


		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		//
		// Go back and create a 1AM punch and test the times again.
		//
		$punch_time = strtotime( $date_stamp . ' 1:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => $this->branch_id[1], 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 2:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 5:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] );


		$punch_time = strtotime( $date_stamp . ' 6:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		$punch_time = strtotime( $date_stamp . ' 7:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		$punch_time = strtotime( $date_stamp . ' 9:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		$punch_time = strtotime( $date_stamp . ' 11:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Switches to matching the working shift and uses a different branch.

		return true;
	}

	/**
	 * @group PunchDetection_test24OnCallShiftScheduleEndA
	 */
	function test24OnCallShiftScheduleEndA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );



		$date_epoch = TTDate::incrementDate( time(), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Create a IN punch at 5:00PM (schedule end time) that is in error to test the scenario with a missing IN punch, and the employee punching at 5PM that gets assigned as an In punch instead.
		//$dd->createPunch( $this->user_id, 10, 20, strtotime( $date_stamp . ' 5:00PM' ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );



		$date_epoch = TTDate::incrementDate( $date_epoch, 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
				'status_id'			 => 10, //10=Working
				'branch_id'			 => $this->branch_id[0],

		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => '5:00PM',
				'end_time'           => '11:30PM',
				'status_id'			 => 20, //20=Absent
				'branch_id'			 => $this->branch_id[1],
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => $this->branch_id[0], 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 6:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 8:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 11:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		//
		//Now punch them out and check the call-back scenario.
		//

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 6:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 7:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[0], $punch_data['branch_id'] );

		$punch_time = strtotime( $date_stamp . ' 8:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 10, $punch_status_id ); //In
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Matches to absent shift now and switches branch.
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, [ 'branch_id' => $this->branch_id[1], 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 9:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Matches to absent shift now and switches branch.

		$punch_time = strtotime( $date_stamp . ' 10:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Matches to absent shift now and switches branch.

		$punch_time = strtotime( $date_stamp . ' 11:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$punch_type_id = $punch_data['type_id'];
		$punch_status_id = $punch_data['status_id'];
		$this->assertEquals( 10, $punch_type_id );   //Normal
		$this->assertEquals( 20, $punch_status_id ); //Out
		$this->assertEquals( $this->branch_id[1], $punch_data['branch_id'] ); //Matches to absent shift now and switches branch.

		return true;
	}


	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithNoMealAndNoBreakAndNoSchedule
	 */
	function testNextTypeForPunchReminderWithNoMealAndNoBreakAndNoSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithNoMealAndNoBreakAndSchedule
	 */
	function testNextTypeForPunchReminderWithNoMealAndNoBreakAndSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndNoBreakAndSchedule
	 */
	function testNextTypeForPunchReminderWithMealAndNoBreakAndSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 ); //Detection Type: Punch Time.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 11:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 11:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndOneBreakAndSchedule
	 */
	function testNextTypeForPunchReminderWithMealAndOneBreakAndSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 112 ); //Detection Type: Punch Time.
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 ); //Detection Type: Punch Time.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:40AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 8:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndTwoBreakAndSchedule
	 */
	function testNextTypeForPunchReminderWithMealAndTwoBreakAndSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 112 ); //Detection Type: Punch Time.
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 ); //Detection Type: Punch Time. -- Morning Break
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 112 ); //Detection Type: Punch Time. -- Afternoon Break

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:40AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 8:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 2:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:05PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndMultipleBreakAndSchedule
	 */
	function testNextTypeForPunchReminderWithMealAndMultipleBreakAndSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 112 ); //Detection Type: Punch Time.
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 200 ); //Detection Type: Punch Time. -- Multiple Breaks up to 30mins

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:40AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 8:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 2:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:05PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:09PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndMultipleBreakAndNoSchedule
	 */
	function testNextTypeForPunchReminderWithMealAndMultipleBreakAndNoSchedule() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 112 ); //Detection Type: Punch Time.
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 200 ); //Detection Type: Punch Time. -- Multiple Breaks up to 30mins

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//$this->createSchedule( $this->user_id, $date_epoch, [
		//		'start_time'         => ' 8:00AM',
		//		'end_time'           => '5:00PM',
		//] );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:40AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 8:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 2:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:05PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:09PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testNextTypeForPunchReminderWithMealAndMultipleBreakAndNoScheduleAndPastPunches
	 */
	function testNextTypeForPunchReminderWithMealAndMultipleBreakAndNoScheduleAndPastPunches() {
		global $dd;

		//TTDate::setTimeZone( 'Etc/GMT+8', true ); //Force to timezone that does not observe DST.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 112 ); //Detection Type: Punch Time.
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 200 ); //Detection Type: Punch Time. -- Multiple Breaks up to 30mins

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'],
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								$policy_ids['break'] );

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) + ( 86400 * 4 ) ); //If this the beginning of the week (Sun/Mon), it fails on weeks where DST changes.
		$dd->createPunch( $this->user_id, 10, 10, ( TTDate::getBeginDayEpoch( $date_epoch - 86400 ) + ( 8 * 3600 ) ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true ); //8AM
		$dd->createPunch( $this->user_id, 10, 20, ( TTDate::getBeginDayEpoch( $date_epoch - 86400 ) + ( 17 * 3600 ) ), [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true ); //5PM

		$date_epoch = TTDate::getBeginDayEpoch( $date_epoch );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$punch_time = strtotime( $date_stamp . ' 8:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:01AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 8:15AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 8:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 8:40AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 8:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 8:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 9:50AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:00AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 10:30AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 10:45AM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out


		$punch_time = strtotime( $date_stamp . ' 12:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 12:01PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 12:45PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 1:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 20, $punch_data['type_id'] );   //Lunch
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );


		$punch_time = strtotime( $date_stamp . ' 1:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 2:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 3:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:05PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In

		$punch_time = strtotime( $date_stamp . ' 3:15PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 10, $punch_data['status_id'] ); //In
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		$punch_time = strtotime( $date_stamp . ' 3:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:09PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 30, $punch_data['type_id'] );   //Break
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 4:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 6:20PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out

		$punch_time = strtotime( $date_stamp . ' 5:00PM' );
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( 10, $punch_data['type_id'] );   //Normal
		$this->assertEquals( 20, $punch_data['status_id'] ); //Out
		$dd->createPunch( $this->user_id, $punch_data['type_id'], $punch_data['status_id'], $punch_time, [ 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0, 'punch_tag_id' => [] ], true );

		return true;
	}

	/**
	 * @group PunchDetection_testDefaultPunchTagsA
	 */
	function testDefaultPunchTagsA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create Policy Group
		/** @var DemoData $dd */
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$punch_time = strtotime( $date_stamp . ' 12:00PM' );

		//Create required data for punch tags to use.
		$job_id = $dd->createJob( $this->company_id, 10, TTUUID::getZeroID() );

		//Create Punch Tags
		$punch_tag = [];
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 1, 'NY Branch', [ 'branch_selection_type' => 10, 'branch_ids' => $this->branch_id ] ); /** @var DemoData $dd */
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 2, 'Job - House 1', [ 'job_selection_type' => 10, 'job_ids' => $job_id ] );

		//Save Punch Tags as User Default
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setDefaultPunchTag( $punch_tag );

			if ( $u_obj->isValid() ) {
				$u_obj->Save( false );
				$this->user_obj = $u_obj;
			}
		}

		//Check users default punch tags match return value of getDefaultPunchSettings()
		$punch_data = $this->getDefaultPunchSettings( $punch_time );
		$this->assertEquals( $punch_tag, $punch_data['punch_tag_id'] );

		return true;
	}

	/**

	/**
	 * @group PunchDetection_testDefaultPunchTagsA
	 */
	function testDefaultGEOPunchTagsA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create Policy Group
		/** @var DemoData $dd */
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Remove any default punch tags from user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setDefaultPunchTag( [] );

			if ( $u_obj->isValid() ) {
				$u_obj->Save( false );
				$this->user_obj = $u_obj;
			}
		}

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$punch_time = strtotime( $date_stamp . ' 12:00PM' );

		$geo_fence_id = [];
		$geo_fence_id[] = $dd->createGEOFence( $this->company_id, 10 ); //Yonkers
		$geo_fence_id[] = $dd->createGEOFence( $this->company_id, 20 ); //State Island
		$geo_fence_id[] = $dd->createGEOFence( $this->company_id, 30 ); //Jersey City

		$geo_fence_punch_tag_id = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 3, 'GEO Fence New York', [ 'geo_fence_ids' => $geo_fence_id ] );

		//If user has no default punch tags and punching in with a latitude / longitude grab a punch that falls within the geo locations.
		$punch_data = $this->getDefaultPunchSettings( $punch_time, null, null, '40.903221', '-73.826752', 0 );
		$this->assertEquals( $geo_fence_punch_tag_id, $punch_data['punch_tag_id'][0] );

		return true;
	}

	/**
	 * @group PunchDetection_testPunchTagEligibilityDetectionA
	 */
	function testPunchTagEligibilityDetectionA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create Policy Group
		/** @var DemoData $dd */
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$jobs = [];
		$jobs[] = $dd->createJob( $this->company_id, 15, TTUUID::getZeroID() ); //House 6
		$jobs[] = $dd->createJob( $this->company_id, 20, TTUUID::getZeroID() ); //Project A

		$job_items = [];
		$job_items[] = $dd->createTask( $this->company_id, 10, TTUUID::getZeroID() ); //Framing
		$job_items[] = $dd->createTask( $this->company_id, 20, TTUUID::getZeroID() ); //Sanding

		//Match Single Criteria Punch Tags
		$punch_tag = [];
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 1, 'Include Toronto or Vancouver Branch', [ 'branch_selection_type' => 20, 'branch_ids' => [ $this->branch_id[2], $this->branch_id[3], ] ] );
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 2, 'Include Sales Department', [ 'department_selection_type' => 20, 'department_ids' => $this->department_id[0] ] );
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 3, 'Anything', [] );

		//Match Multiple Criteria Punch Tags
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 4, 'Branch Toronto + Construction Department', [ 'branch_selection_type' => 20, 'branch_ids' => $this->branch_id[3], 'department_selection_type' => 20, 'department_ids' => $this->department_id[1], ] );
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 5, 'Branch Vancouver + Job House 6 + Task Framing', [ 'branch_selection_type' => 20, 'branch_ids' => $this->branch_id[3], 'job_selection_type' => 20, 'job_ids' => $jobs[0], 'job_item_selection_type' => 20, 'job_item_ids' => $job_items[0] ] );

		//Match Include and Exclude Default Punch Tags
		$punch_tag[] = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 6, 'Vancouver Branch - Exclude Default', [ 'branch_selection_type' => 20, 'branch_ids' => $this->branch_id[3], 'exclude_default_branch' => 1 ] );

		//Test "Anything" punch tag.
		$filter_data = [
			'status_id' => 10,
			'user_id' => $this->user_id,
			'branch_id' => TTUUID::getZeroID(),
			'department_id' => TTUUID::getZeroID(),
			'job_id' => TTUUID::getZeroID(),
			'job_item_id' => TTUUID::getZeroID()
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Anything', array_column( $data, 'name' ) ) );

		//Test Branch Toronto
		$filter_data = [
				'status_id' => 10,
				'user_id' => $this->user_id,
				'branch_id' => $this->branch_id[3], //Toronto
				'department_id' => TTUUID::getZeroID(),
				'job_id' => TTUUID::getZeroID(),
				'job_item_id' => TTUUID::getZeroID()
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Include Toronto or Vancouver Branch', array_column( $data, 'name' ) ) );


		//Test Department Sales
		$filter_data = [
				'status_id' => 10,
				'user_id' => $this->user_id,
				'branch_id' => TTUUID::getZeroID(),
				'department_id' => $this->department_id[0], //Sales
				'job_id' => TTUUID::getZeroID(),
				'job_item_id' => TTUUID::getZeroID()
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Include Sales Department', array_column( $data, 'name' ) ) );

		//Test branch Toronto and department construction.
		$filter_data = [
				'status_id' => 10,
				'user_id' => $this->user_id,
				'branch_id' => $this->branch_id[3], //Toronto
				'department_id' => $this->department_id[1], //Construction
				'job_id' => TTUUID::getZeroID(),
				'job_item_id' => TTUUID::getZeroID()
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Branch Toronto + Construction Department', array_column( $data, 'name' ) ) );

		//Test branch Vancouver, Job house 6 and job task framing.
		$filter_data = [
				'status_id' => 10,
				'user_id' => $this->user_id,
				'branch_id' => $this->branch_id[3], //Vancouver
				'department_id' => TTUUID::getZeroID(),
				'job_id' => $jobs[0], //House 6
				'job_item_id' => $job_items[0] //Framing
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Branch Vancouver + Job House 6 + Task Framing', array_column( $data, 'name' ) ) );

		//Test vancouver branch - No User default branch set.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setDefaultBranch( [] );

			if ( $u_obj->isValid() ) {
				$u_obj->Save( false );
				$this->user_obj = $u_obj;
			}
		}

		$filter_data = [
				'status_id' => 10,
				'user_id' => $this->user_id,
				'branch_id' => $this->branch_id[3], //Vancouver Branch
				'department_id' => TTUUID::getZeroID(),
				'job_id' => TTUUID::getZeroID(),
				'job_item_id' => TTUUID::getZeroID()
		];
		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Vancouver Branch - Exclude Default', array_column( $data, 'name' ) ) );

		//Test Vancouver branch again, but user has default branch (Vancouver) this time.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setDefaultBranch( $this->branch_id[3] ); //Vancouver Branch

			if ( $u_obj->isValid() ) {
				$u_obj->Save( false );
				$this->user_obj = $u_obj;
			}
		}

		$data = $this->getPunchTags( $this->company_id, $filter_data );
		$this->assertEquals( false, in_array( 'Vancouver Branch - Exclude Default', array_column( $data, 'name' ) ) );

		return true;
	}

	/**
	 * @group PunchDetection_testBranchEmployeeCriteriaDetectionA
	 */
	function testBranchEmployeeCriteriaDetectionA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create Policy Group
		/** @var DemoData $dd */
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );


		//Test no condition for branch.
		$filter_data = [
			'user_id' => $this->user_id
		];

		$data = $this->getBranches( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Vancouver', array_column( $data, 'name' ) ) );

		//Test Vancouver branch to only show if user default department is sales.
		$this->updateBranchEmployeeCriteria( $this->branch_id[3], [ 'user_default_department_selection_type_id' => 20, 'department_ids' => [ $this->department_id[0] ] ] );

		//Vancouver should not show, because user default department is not sales.
		$data = $this->getBranches( $this->company_id, $filter_data );
		$this->assertEquals( false, in_array( 'Vancouver', array_column( $data, 'name' ) ) );

		//Set sales as default department for user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setDefaultDepartment( $this->department_id[0] ); //Sales department

			if ( $u_obj->isValid() ) {
				$u_obj->Save( false );
				$this->user_obj = $u_obj;
			}
		}

		//Vancouver should now show as user default department is sales.
		$data = $this->getBranches( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Vancouver', array_column( $data, 'name' ) ) );

		return true;
	}

	/**
	 * @group PunchDetection_testDepartmentEmployeeCriteriaDetectionA
	 */
	function testDepartmentEmployeeCriteriaDetectionA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create Policy Group
		/** @var DemoData $dd */
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );


		//Test no condition for department.
		$filter_data = [
			'user_id' => $this->user_id
		];

		$data = $this->getDepartments( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Construction', array_column( $data, 'name' ) ) );

		//Test Construction department to only show if user punch branch is New York.
		$this->updateDepartmentEmployeeCriteria( $this->department_id[1], [ 'user_punch_branch_selection_type_id' => 20, 'branch_ids' => [ $this->branch_id[0] ] ] );

		//Construction should not show, because punch branch is not New York.
		$data = $this->getDepartments( $this->company_id, $filter_data );
		$this->assertEquals( false, in_array( 'Construction', array_column( $data, 'name' ) ) );

		$filter_data['branch_id'] = $this->branch_id[0]; // New York

		//Construction should now show as punch branch is New York.
		$data = $this->getDepartments( $this->company_id, $filter_data );
		$this->assertEquals( true, in_array( 'Construction', array_column( $data, 'name' ) ) );

		return true;
	}
}

?>
