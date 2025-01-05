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

class ExceptionTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $user_obj = null;
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;
	protected $branch_id = null;
	protected $policy_ids = [];

	public $calculate_policy_flags = [
			'meal'              => false,
			'undertime_absence' => false,
			'break'             => false,
			'holiday'           => false,
			'schedule_absence'  => false,
			'absence'           => false,
			'regular'           => false,
			'overtime'          => false,
			'premium'           => false,
			'accrual'           => false,

			'exception'           => true,
			//Exception options
			'exception_premature' => true, //Calculates premature exceptions
			'exception_future'    => false, //Calculates exceptions in the future.

			//Calculate policies for future dates.
			'future_dates'        => false, //Calculates dates in the future.
	];

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
		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->user_obj = $ulf->getById( $this->user_id )->getCurrent();


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

	function createPayPeriodSchedule( $shift_assigned_day = 10, $maximum_shift_time = 57600, $new_shift_trigger_time = 14400 ) {
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
		$ppsf->setMaximumShiftTime( $maximum_shift_time );
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
					$end_date = TTDate::incrementDate( $end_date, 14, 'day' );
				}

				Debug::Text( 'I: ' . $i . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				$pps_obj->createNextPayPeriod( $end_date, ( 86400 + 3600 ), false ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}
		}

		return true;
	}

	function createMealPolicy( $type_id ) {
		$mpf = TTnew( 'MealPolicyFactory' ); /** @var MealPolicyFactory $mpf */

		$mpf->setCompany( $this->company_id );

		switch ( $type_id ) {
			case 10: //60min auto-deduct.
				$mpf->setName( '60min (AutoDeduct)' );
				$mpf->setType( 10 ); //AutoDeduct
				$mpf->setTriggerTime( ( 3600 * 5 ) );
				$mpf->setAmount( 3600 );
				$mpf->setStartWindow( ( 3600 * 4 ) );
				$mpf->setWindowLength( ( 3600 * 2 ) );
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

	function createExceptionPolicy( $type ) {
		$epcf = TTnew( 'ExceptionPolicyControlFactory' ); /** @var ExceptionPolicyControlFactory $epcf */

		$epcf->setCompany( $this->company_id );
		$epcf->setName( $type );

		if ( $epcf->isValid() ) {
			$epc_id = $epcf->Save();
			Debug::Text( 'Exception Policy Control ID: ' . $epc_id, __FILE__, __LINE__, __METHOD__, 10 );

			switch ( strtoupper( $type ) ) {
				case 'M1': //Missing In
					$data['exceptions'] = [
							$type => [
									'active'      => true,
									'severity_id' => 30,
							],
					];
					break;
				case 'M2': //Missing Out
					$data['exceptions'] = [
							$type => [
									'active'      => true,
									'severity_id' => 30,
							],
					];
					break;
				case 'M3': //Missing Lunch In/Out
					$data['exceptions'] = [
							$type => [
									'active'      => true,
									'severity_id' => 30,
							],
					];
					break;
				case 'M4': //Missing Break In/Out
					$data['exceptions'] = [
							$type => [
									'active'      => true,
									'severity_id' => 30,
							],
					];
					break;
				case 'S2': //Normal
					$data['exceptions'] = [
							$type => [
									'active'       => true,
									'severity_id'  => 20,
							],
					];
					break;
				case 'S4': //Normal
					$data['exceptions'] = [
							$type => [
									'active'       => true,
									'severity_id'  => 20,
									'grace'        => 300,
									'watch_window' => 3600,
							],
					];
					break;
				case 'S6': //Normal
					$data['exceptions'] = [
							$type => [
									'active'       => true,
									'severity_id'  => 20,
									'grace'        => 300,
									'watch_window' => 3600,
							],
					];
					break;
				case 'S9': //Normal
					$data['exceptions'] = [
							$type => [
									'active'       => true,
									'severity_id'  => 20,
									'grace'        => 300,
							],
					];
					break;
				case 'C1': //Normal
					$data['exceptions'] = [
							$type => [
									'active'       => true,
									'severity_id'  => 20,
									'grace'        => 7200, //2hrs
									'watch_window' => 3600,
							],
					];
					break;
			}
			/*
						$data['exceptions'] = array(
												'S1' => array(
															'active' => TRUE,
															'severity_id' => 10,
															),
												'S2' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),
												'S3' => array(
															'active' => TRUE,
															'severity_id' => 10,
															'grace' => 300,
															'watch_window' => 3600,
															),
												'S4' => array(
															'active' => TRUE,
															'severity_id' => 20,
															'grace' => 300,
															'watch_window' => 3600,

															),
												'S5' => array(
															'active' => TRUE,
															'severity_id' => 20,
															'grace' => 300,
															'watch_window' => 3600,

															),
												'S6' => array(
															'active' => TRUE,
															'severity_id' => 10,
															'grace' => 300,
															'watch_window' => 3600,
															),
												'S7' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),
												'S8' => array(
															'active' => TRUE,
															'severity_id' => 10,
															),
												'M1' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),
												'M2' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),
												'L3' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),
												'M3' => array(
															'active' => TRUE,
															'severity_id' => 30,
															),

												);
			*/
			if ( count( $data['exceptions'] ) > 0 ) {

				foreach ( $data['exceptions'] as $code => $exception_data ) {
					Debug::Text( 'Looping Code: ' . $code, __FILE__, __LINE__, __METHOD__, 10 );

					$epf = TTnew( 'ExceptionPolicyFactory' ); /** @var ExceptionPolicyFactory $epf */
					$epf->setExceptionPolicyControl( $epc_id );
					if ( isset( $exception_data['active'] ) ) {
						$epf->setActive( true );
					} else {
						$epf->setActive( false );
					}
					$epf->setType( $code );
					$epf->setSeverity( $exception_data['severity_id'] );
					if ( isset( $exception_data['demerit'] ) && $exception_data['demerit'] != '' ) {
						$epf->setDemerit( $exception_data['demerit'] );
					}
					if ( isset( $exception_data['grace'] ) && $exception_data['grace'] != '' ) {
						$epf->setGrace( $exception_data['grace'] );
					}
					if ( isset( $exception_data['watch_window'] ) && $exception_data['watch_window'] != '' ) {
						$epf->setWatchWindow( $exception_data['watch_window'] );
					}
					if ( $epf->isValid() ) {
						$epf->Save();
					}
				}

				Debug::Text( 'Creating Exception Policy ID: ' . $epc_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $epc_id;
			}
		}

		Debug::Text( 'Failed Creating Exception Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createSchedulePolicy( $type, $meal_policy_id ) {
		$spf = TTnew( 'SchedulePolicyFactory' ); /** @var SchedulePolicyFactory $spf */
		$spf->setCompany( $this->company_id );

		switch ( $type ) {
			case 10: //Normal
				$spf->setName( 'Schedule Policy' );
				//$spf->setAbsencePolicyID( 0 );
				$spf->setStartStopWindow( ( 3600 * 2 ) );
				break;
			case 20: //No Lunch
				$spf->setName( 'No Lunch' );
				//$spf->setAbsencePolicyID( 0 );
				$spf->setStartStopWindow( ( 3600 * 2 ) );
				break;
		}

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save( false );

			$spf->setMealPolicy( $meal_policy_id );

			Debug::Text( 'Schedule Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createSchedule( $user_id, $date_stamp, $data = null ) {
		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$sf->setCompany( $this->company_id );
		$sf->setUser( $user_id );
		//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate( $user_id, $date_stamp) );

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
			$sf->setEnableReCalculateDay( false );
			$insert_id = $sf->Save();
			Debug::Text( 'Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
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

						'quantity'     => $udt_obj->getQuantity(),
						'bad_quantity' => $udt_obj->getBadQuantity(),

						'hourly_rate'  => $udt_obj->getHourlyRate(),
						//Override only shows for SYSTEM override columns...
						//Need to check Worked overrides too.
						'tmp_override' => $udt_obj->getOverride(),
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
					$date_stamp = TTDate::getMiddleDayEpoch( $p_obj->getPunchControlObject()->getDateStamp() );
					$p_obj->setUser( $this->user_id );
					$p_obj->getPunchControlObject()->setPunchObject( $p_obj );

					$retarr[$date_stamp][$i] = [
							'id'         => $p_obj->getPunchControlObject()->getID(),
							'branch_id'  => $p_obj->getPunchControlObject()->getBranch(),
							'date_stamp' => $date_stamp,
							//'user_date_id' => $p_obj->getPunchControlObject()->getUserDateID(),
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

	function checkCalcQuickExceptions( $user_id, $start_date, $end_date, $check_date ) {
		$udtlf = TTNew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$udtlf->getMidDayExceptionsByStartDateAndEndDateAndPayPeriodStatus( $start_date, $end_date, [ 10, 12, 15, 30 ], $user_id );
		Debug::text( '  MidDayException Filter Start Date: ' . TTDate::getDate( 'DATE', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE', $end_date ) . ' Check Date: ' . TTDate::getDate( 'DATE', $check_date ) . ' Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $udt_obj ) {
				Debug::text( '  MidDayException Start Date: ' . TTDate::getDate( 'DATE', strtotime( $udt_obj->getColumn( 'start_date' ) ) ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( TTDate::getMiddleDayEpoch( $check_date ) == TTDate::getMiddleDayEpoch( strtotime( $udt_obj->getColumn( 'start_date' ) ) ) ) {
					Debug::text( '  Returning TRUE... ', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		Debug::text( '  Returning FALSE... ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function calculateExceptions( $date_epoch ) {
		$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
		$cp->setFlag( $this->calculate_policy_flags );
		$cp->setUserObject( $this->user_obj );
		$cp->addPendingCalculationDate( $date_epoch, $date_epoch );
		$cp->calculate( $date_epoch ); //This sets timezone itself.

		return $cp->Save();
	}

	function deleteExceptions( $date_epoch ) {
		$elf = TTnew( 'ExceptionListFactory' ); /** @var ExceptionListFactory $elf */
		$elf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $date_epoch, $date_epoch );
		if ( $elf->getRecordCount() > 0 ) {
			foreach ( $elf as $e_obj ) {
				$e_obj->Delete();
			}
		}

		return true;
	}

	function getExceptions( $date_epoch ) {
		$elf = TTnew( 'ExceptionListFactory' ); /** @var ExceptionListFactory $elf */
		//$elf->getByUserIdAndDateStamp( $this->user_id, $date_epoch );
		$elf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $date_epoch, $date_epoch );
		if ( $elf->getRecordCount() > 0 ) {
			foreach ( $elf as $e_obj ) {
				Debug::text( '  Exception: Type: ' . $e_obj->getType(), __FILE__, __LINE__, __METHOD__, 10 );
				$retarr[$e_obj->getColumn( 'exception_policy_type_id' )][] = $e_obj->getObjectAsArray();
			}

			return $retarr;
		}

		return [];
	}

	/*
	 Tests:
		Test calcQuickExceptions, to make sure we are catching the all cases as soon as possible.

	*/

	/**
	 * @group Punch_testExceptionMissingNormalIn
	 */
	function testExceptionMissingNormalIn() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M1' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  null,
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);


		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M1', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M1'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingNormalOut
	 */
	function testExceptionMissingNormalOut() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  null,
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);


		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'M2', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M2'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingNormalOutPreMature
	 */
	function testExceptionMissingNormalOutPreMature() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::getMiddleDayEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  null,
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);


		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'M2', $exception_arr );
		$this->assertEquals( 5, $exception_arr['M2'][0]['type_id'] ); //PreMature

		return true;
	}


	/**
	 * @group Punch_testExceptionMissingLunchInA
	 */
	function testExceptionMissingLunchInA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M3' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -3, 'day' );
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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M3', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M3'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingLunchInB
	 */
	function testExceptionMissingLunchInB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M3' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -2, 'day' );
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
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M3', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M3'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingLunchInPreMature
	 */
	function testExceptionMissingLunchInPreMature() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M3' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = ( time() - ( 3600 * 5 ) );

		$dd->createPunchPair( $this->user_id,
							  $date_epoch, //Real-time
				( $date_epoch + ( 3600 * 4 ) ), //Real-time
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);


		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M3', $exception_arr );
		$this->assertEquals( 5, $exception_arr['M3'][0]['type_id'] ); //PreMature

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingLunchOut
	 */
	function testExceptionMissingLunchOut() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M3' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -2, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

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
									  'punch_tag_id'  => []
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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M3', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M3'][0]['type_id'] ); //ACTIVE

		return true;
	}


	/**
	 * @group Punch_testExceptionMissingBreakInA
	 */
	function testExceptionMissingBreakInA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -3, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M4'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingBreakInB
	 */
	function testExceptionMissingBreakInB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -2, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M4'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingBreakInPreMature
	 */
	function testExceptionMissingBreakInPreMature() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = ( time() - ( 3600 * 5 ) );

		$dd->createPunchPair( $this->user_id,
							  $date_epoch, //Real-time
				( $date_epoch + ( 3600 * 4 ) ), //Real-time
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);


		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M4', $exception_arr );
		$this->assertEquals( 5, $exception_arr['M4'][0]['type_id'] ); //PreMature

		return true;
	}

	/**
	 * @group Punch_testExceptionMissingBreakOut
	 */
	function testExceptionMissingBreakOut() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'M4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( time() ), -2, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		//print_r($exception_arr);
		$this->assertArrayHasKey( 'M4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['M4'][0]['type_id'] ); //ACTIVE

		return true;
	}




	/**
	 * @group Punch_testExceptionNoScheduleA
	 */
	function testExceptionNoScheduleA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		////Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00AM' ),
							  strtotime( $date_stamp . ' 6:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 3:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3600 * 3), $udt_arr[$date_epoch][0]['total_time'] );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S2', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S2'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionNoScheduleB1
	 */
	function testExceptionNoScheduleB1() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		////Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '7:00AM',
				'end_time'           => '9:00AM',
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '9:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 9:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3600 * 7), $udt_arr[$date_epoch][0]['total_time'] );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S2', $exception_arr );
		//$this->assertEquals( 50, $exception_arr['S2'][0]['type_id'] ); //ACTIVE

		return true;
	}


	/**
	 * @group Punch_testExceptionNoScheduleB2
	 */
	function testExceptionNoScheduleB2() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		////Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'status_id'			 => 20, //20=Absence
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '9:00AM',
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '9:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:45AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:45AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3600 * 7.25), $udt_arr[$date_epoch][0]['total_time'] );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S2', $exception_arr );
		//$this->assertEquals( 50, $exception_arr['S2'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionNoScheduleB3
	 */
	function testExceptionNoScheduleB3() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S2' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		////Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'status_id'			 => 20, //20=Absence
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '9:00AM',
		] );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '9:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:15AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 6:15AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3600 * 9.75), $udt_arr[$date_epoch][0]['total_time'] );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S2', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S2'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionInLateA
	 */
	function testExceptionInLateA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 60 * 20 ) ) ), //More than the 15min grace.
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 3600 * 8 ) ) ),
		] );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 0, $punch_arr );
		$this->assertEquals( true, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S4'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionInLateA2
	 */
	function testExceptionInLateA2() {
		global $dd;

		//
		// Create In Late exception the previous day due to punching in late, then make sure it still triggers on the current day when they don't punch.
		//   This tests a bug that used to exist.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:20AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:20AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 27600, $udt_arr[$date_epoch][0]['total_time'] );

		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S4'][0]['type_id'] ); //ACTIVE


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 60 * 20 ) ) ), //More than the 15min grace.
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 3600 * 8 ) ) ),
		] );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 0, $punch_arr );
		$this->assertEquals( true, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S4'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionInLateB
	 */
	function testExceptionInLateB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 60 * 20 ) ) ), //More than the 15min grace.
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 3600 * 8 ) ) ),
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' ' . TTDate::getDate( 'TIME', ( time() - ( 60 * 0 ) ) ) ),
							  null,
				//strtotime($date_stamp.' 5:00PM'),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertEquals( false, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S4', $exception_arr );
		$this->assertEquals( 50, $exception_arr['S4'][0]['type_id'] ); //ACTIVE

		return true;
	}

	/**
	 * @group Punch_testExceptionInLateC
	 */
	function testExceptionInLateC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 60 * 4 ) ) ), //More than the 15min grace.
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 3600 * 8 ) ) ),
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' ' . TTDate::getDate( 'TIME', ( time() - ( 60 * 0 ) ) ) ),
							  null,
				//strtotime($date_stamp.' 5:00PM'),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertEquals( false, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S4', $exception_arr );

		return true;
	}

	/**
	 * @group Punch_testExceptionInLateD
	 */
	function testExceptionInLateD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S4' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 60 * 4 ) ) ), //More than the 15min grace.
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 3600 * 8 ) ) ),
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' ' . TTDate::getDate( 'TIME', ( time() - ( 60 * 10 ) ) ) ),
							  null,
				//strtotime($date_stamp.' 5:00PM'),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertEquals( false, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S4', $exception_arr );

		return true;
	}


	/**
	 * @group Punch_testExceptionOutLateA
	 */
	function testExceptionOutLateA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S6' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 3600 * 8 ) ) ),
				'end_time'           => TTDate::getDate( 'TIME', ( time() + ( 60 * 20 ) ) ), //More than the 15min grace.
		] );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 0, $punch_arr );
		$this->assertEquals( false, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S6', $exception_arr );

		return true;
	}

	/**
	 * @group Punch_testExceptionOutLateB
	 */
	function testExceptionOutLateB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S6' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() - ( 3600 * 8 ) ) );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 3600 * 8 ) ) ),
				'end_time'           => TTDate::getDate( 'TIME', ( time() - ( 60 * 15 ) ) ), //More than the 15min grace.
		] );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);

		//If this is run before 8AM, the In punch is on the previous day.
		if ( TTDate::getBeginDayEpoch( time() ) > $date_epoch ) {
			$this->assertCount( 1, $punch_arr[$date_epoch] );
		} else {
			$this->assertCount( 0, $punch_arr );
		}
		$this->assertEquals( true, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayNotHasKey( 'S6', $exception_arr ); //***Because their is no IN punch, the actual exception shouldnt be triggered in this case.

		return true;
	}

	/**
	 * @group Punch_testExceptionOutLateC
	 */
	function testExceptionOutLateC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S6' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//


		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::getMiddleDayEpoch( ( time() - ( 3600 * 8 ) ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => TTDate::getDate( 'TIME', ( time() - ( 3600 * 8 ) ) ),
				'end_time'           => TTDate::getDate( 'TIME', ( time() - ( 60 * 15 ) ) ), //More than the 15min grace.
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' ' . TTDate::getDate( 'TIME', ( time() - ( 3600 * 8 ) ) ) ),
							  null,
				//strtotime($date_stamp.' 5:00PM'),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$this->deleteExceptions( $date_epoch ); //Exceptions could be calculated above, so delete them here.
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		//If this is run before 8AM, the In punch is on the previous day.
		if ( TTDate::getBeginDayEpoch( time() ) > $date_epoch ) {
			$this->assertCount( 2, $punch_arr[$date_epoch] );
		} else {
			$this->assertCount( 1, $punch_arr[$date_epoch] );
		}
		$this->assertEquals( true, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S6', $exception_arr );

		return true;
	}

	/**
	 * @group Punch_testExceptionOverWeeklyScheduledTimeA
	 */
	function testExceptionOverWeeklyScheduledTimeA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S9' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//
		$date_epoch = TTDate::getMiddleDayEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$this->deleteExceptions( $date_epoch ); //Exceptions could be calculated above, so delete them here.
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);

		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertCount( 2, $punch_arr[$date_epoch][0]['shift_data']['punches'] ); //Make sure today has two punches.

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertArrayHasKey( 'S9', $exception_arr );

		return true;
	}

	/**
	 * @group Punch_testExceptionOverWeeklyScheduledTimeB
	 */
	function testExceptionOverWeeklyScheduledTimeB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();
		$exception_policy_id = $this->createExceptionPolicy( 'S9' );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								$exception_policy_id,
								null,
								null,
								null,
								null,
								[ $this->user_id ] );

		//Always start with a proper punch on the previous day, as that can affect the exception.
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime( $date_stamp . ' 8:00AM' ) );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime( $date_stamp . ' 5:00PM' ) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 28800, $udt_arr[$date_epoch][0]['total_time'] );


		//
		// Test exception for today.
		//
		$date_epoch = TTDate::getMiddleDayEpoch( time() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );

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
									  'punch_tag_id'  => []
							  ],
							  true
		);
		$this->deleteExceptions( $date_epoch ); //Exceptions could be calculated above, so delete them here.
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);

		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertCount( 2, $punch_arr[$date_epoch][0]['shift_data']['punches'] ); //Make sure today has two punches.

		//Calculate exceptions, and check to make sure the proper ones exist.
		$this->calculateExceptions( $date_epoch );
		$exception_arr = $this->getExceptions( $date_epoch );
		$this->assertEmpty( $exception_arr ); //No exceptions.

		return true;
	}

	/**
	 * @group Punch_testExceptionCheckInA
	 */
	/*
		function testExceptionCheckInA() {
			global $dd;

			$this->createPayPeriodSchedule( 10 );
			$this->createPayPeriods();
			$this->getAllPayPeriods();
			$exception_policy_id = $this->createExceptionPolicy( 'C1' );

			//Create Policy Group
			$dd->createPolicyGroup( 	$this->company_id,
										NULL,
										$exception_policy_id,
										NULL,
										NULL,
										NULL,
										NULL,
										array( $this->user_id ) );

			//Always start with a proper punch on the previous day, as that can affect the exception.
			$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' );
			$date_stamp = TTDate::getDate('DATE', $date_epoch );

			$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
			$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
			$this->createSchedule( $this->user_id, $date_epoch, array(
																		'schedule_policy_id' => $schedule_policy_id,
																		'start_time' => '8:00AM',
																		'end_time' => '5:00PM',
																		) );

			$dd->createPunchPair( 	$this->user_id,
									strtotime($date_stamp.' 8:00AM'),
									strtotime($date_stamp.' 9:59AM'),
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

			$dd->createPunchPair( 	$this->user_id,
									strtotime($date_stamp.' 9:59AM'),
									strtotime($date_stamp.' 10:59AM'),
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

			$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
			//print_r($punch_arr);
			$this->assertEquals( 2, count($punch_arr[$date_epoch]) );

			$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:00AM') );
			$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 9:59AM') );
			$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['time_stamp'], strtotime($date_stamp.' 9:59AM') );
			$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['time_stamp'], strtotime($date_stamp.' 10:59AM') );

			$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
			//print_r($punch_arr);
			//Total Time
			$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
			$this->assertEquals( 10740, $udt_arr[$date_epoch][0]['total_time'] );


			//
			// Test exception for today.
			//


			//Always start with a proper punch on the previous day, as that can affect the exception.
			$date_epoch = TTDate::getMiddleDayEpoch( ( time() ) );
			$date_stamp = TTDate::getDate('DATE', $date_epoch );

			$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
			$schedule_policy_id = $this->createSchedulePolicy( 10, $meal_policy_id );
			$this->createSchedule( $this->user_id, $date_epoch, array(
																		'schedule_policy_id' => $schedule_policy_id,
																		'start_time' => TTDate::getDate('TIME', ( time() - ( 3600 * 8 ) ) ),
																		'end_time' => TTDate::getDate('TIME', ( time() - ( 60 * 15 ) ) ), //More than the 15min grace.
																		) );

			$dd->createPunchPair( 	$this->user_id,
									strtotime($date_stamp.' '. TTDate::getDate('TIME', ( time() - ( 3600 * 8 ) ) ) ),
									NULL,
									//strtotime($date_stamp.' 5:00PM'),
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
			$this->deleteExceptions( $date_epoch ); //Exceptions could be calculated above, so delete them here.
			$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
			//print_r($punch_arr);
			$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
			$this->assertEquals( TRUE, $this->checkCalcQuickExceptions( $this->user_id, TTDate::incrementDate( $date_epoch, -1, 'day' ), TTDate::incrementDate( $date_epoch, 1, 'day' ), $date_epoch ) );

			//Calculate exceptions, and check to make sure the proper ones exist.
			$this->calculateExceptions( $date_epoch );
			$exception_arr = $this->getExceptions( $date_epoch );
			$this->assertArrayHasKey('C1', $exception_arr );

			return TRUE;
		}
	*/
}

?>