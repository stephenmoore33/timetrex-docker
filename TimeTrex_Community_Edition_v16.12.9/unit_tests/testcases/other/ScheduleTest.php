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

class ScheduleTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $user_id2 = null;
	protected $user_id3 = null;
	protected $user_id4 = null;
	protected $branch_id = null;
	protected $department_id = null;
	protected $branch_id2 = null;
	protected $department_id2 = null;
	protected $job_id = null;
	protected $job_id2 = null;
	protected $job_item_id = null;
	protected $job_item_id2 = null;
	protected $punch_tag = null;
	protected $punch_tag2 = null;
	protected $punch_tag_id = null;
	protected $punch_tag_id2 = null;
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

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY
		$this->department_id = $dd->createDepartment( $this->company_id, 10 );

		$this->branch_id2 = $dd->createBranch( $this->company_id, 20 ); //Seattle
		$this->department_id2 = $dd->createDepartment( $this->company_id, 20 );

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$this->job_id = $dd->createJob( $this->company_id, 10, TTUUID::getZeroID() );
			$this->job_item_id = $dd->createTask( $this->company_id, 10, TTUUID::getZeroID() );
			$this->job_id2 = $dd->createJob( $this->company_id, 20, TTUUID::getZeroID() );
			$this->job_item_id2 = $dd->createTask( $this->company_id, 20, TTUUID::getZeroID() );
			$this->punch_tag = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 1, 'Include NY Branch', [ 'branch_selection_type' => 10, 'branch_ids' => $this->branch_id ] );
			$this->punch_tag2 = $dd->createPunchTag( $this->company_id, TTUUID::getZeroID(), 2, 'Department Sales', [ 'department_selection_type' => 10, 'department_ids' => $this->department_id ] );
		} else {
			$this->job_id = $this->job_item_id = $this->job_id2 = $this->job_item_id2 = TTUUID::getZeroID();
			$this->punch_tag = $this->punch_tag2 = [];
		}

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$this->user_id2 = $dd->createUser( $this->company_id, $this->legal_entity_id, 10 );
		$this->user_id3 = $dd->createUser( $this->company_id, $this->legal_entity_id, 11 );
		$this->user_id4 = $dd->createUser( $this->company_id, $this->legal_entity_id, 12, null, $this->branch_id2, $this->department_id2 );
		//createUser( $company_id, $legal_entity_id, $type, $policy_group_id = null, $default_branch_id = null, $default_department_id = null, $default_currency_id = null, $user_group_id = null, $user_title_id = null, $ethnic_group_ids = null, $remittance_source_account_ids = null, $coordinates = null, $default_job_id = null, $default_job_item_id = null ) {

		$this->policy_ids['accrual_policy_account'][20] = $dd->createAccrualPolicyAccount( $this->company_id, 20 ); //Vacation
		$this->policy_ids['accrual_policy_account'][30] = $dd->createAccrualPolicyAccount( $this->company_id, 30 ); //Sick

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_formula_policy'][120] = $dd->createPayFormulaPolicy( $this->company_id, 120, $this->policy_ids['accrual_policy_account'][20] ); //Vacation
		$this->policy_ids['pay_formula_policy'][130] = $dd->createPayFormulaPolicy( $this->company_id, 130, $this->policy_ids['accrual_policy_account'][30] ); //Sick

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900, $this->policy_ids['pay_formula_policy'][120] ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $this->policy_ids['pay_formula_policy'][130] ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $this->policy_ids['pay_code'][100] ] ); //Regular
		$this->policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$this->policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][10], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $this->policy_ids['pay_code'][900] ); //Vacation
		$this->policy_ids['absence_policy'][30] = $dd->createAbsencePolicy( $this->company_id, 30, $this->policy_ids['pay_code'][910] ); //Sick

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

	function createPayPeriodSchedule( $shift_assigned_day = 10 ) {
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

	function createSchedulePolicy( $meal_policy_id, $full_shift_absence_policy_id = 0, $partial_shift_absence_policy_id = 0 ) {
		$spf = TTnew( 'SchedulePolicyFactory' ); /** @var SchedulePolicyFactory $spf */

		$spf->setCompany( $this->company_id );
		$spf->setName( 'Schedule Policy' );
		$spf->setFullShiftAbsencePolicyID( $full_shift_absence_policy_id );
		$spf->setPartialShiftAbsencePolicyID( $partial_shift_absence_policy_id );
		$spf->setStartStopWindow( ( 3600 * 2 ) );

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save( false );

			$spf->setMealPolicy( $meal_policy_id );
			Debug::Text( 'Schedule Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createRecurringScheduleTemplate( $company_id, $type, $data = null ) {
		$rstcf = TTnew( 'RecurringScheduleTemplateControlFactory' ); /** @var RecurringScheduleTemplateControlFactory $rstcf */
		$rstcf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Morning Shift
				$rstcf->setName( ( isset($data['name']) ? $data['name'] : 'Morning Shift' ) );
				$rstcf->setDescription( '6:00AM - 3:00PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( true );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( true );

					$rstf->setStartTime( strtotime( '06:00 AM' ) );
					$rstf->setEndTime( strtotime( '03:00 PM' ) );

					if ( isset($data['schedule_policy_id']) && TTUUID::isUUID( $data['schedule_policy_id'] ) && $data['schedule_policy_id'] != TTUUID::getZeroID() && $data['schedule_policy_id'] != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $data['schedule_policy_id'] );
					}

					$rstf->setBranch( isset( $data['branch_id'] ) ? $data['branch_id'] : TTUUID::getNotExistID() );             //Default
					$rstf->setDepartment( isset( $data['department_id'] ) ? $data['department_id'] : TTUUID::getNotExistID() ); //Default
					$rstf->setJob( isset( $data['job_id'] ) ? $data['job_id'] : TTUUID::getNotExistID() );                      //Default
					$rstf->setJobItem( isset( $data['job_item_id'] ) ? $data['job_item_id'] : TTUUID::getNotExistID() );        //Default
					$rstf->setPunchTag( isset( $data['punch_tag_id'] ) ? $data['punch_tag_id'] : [] );                          //Default
					$rstf->setOpenShiftMultiplier( ( isset($data['open_shift_multiplier']) ) ? $data['open_shift_multiplier'] : 1 );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 20: //Afternoon Shift
				$rstcf->setName( ( isset($data['name']) ? $data['name'] : 'Afternoon Shift' ) );
				$rstcf->setDescription( '3:00PM - 11:00PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( true );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( true );

					$rstf->setStartTime( strtotime( '03:00 PM' ) );
					$rstf->setEndTime( strtotime( '11:00 PM' ) );

					if ( isset($data['schedule_policy_id']) && TTUUID::isUUID( $data['schedule_policy_id'] ) && $data['schedule_policy_id'] != TTUUID::getZeroID() && $data['schedule_policy_id'] != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $data['schedule_policy_id'] );
					}
					$rstf->setBranch( isset( $data['branch_id'] ) ? $data['branch_id'] : TTUUID::getNotExistID() );             //Default
					$rstf->setDepartment( isset( $data['department_id'] ) ? $data['department_id'] : TTUUID::getNotExistID() ); //Default
					$rstf->setJob( isset( $data['job_id'] ) ? $data['job_id'] : TTUUID::getNotExistID() );                      //Default
					$rstf->setJobItem( isset( $data['job_item_id'] ) ? $data['job_item_id'] : TTUUID::getNotExistID() );        //Default
					$rstf->setPunchTag( isset( $data['punch_tag_id'] ) ? $data['punch_tag_id'] : [] );                          //Default
					$rstf->setOpenShiftMultiplier( ( isset($data['open_shift_multiplier']) ) ? $data['open_shift_multiplier'] : 1 );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 22: //Afternoon Overlap Shift. Overlaps morning shift by 30mins
				$rstcf->setName( ( isset($data['name']) ? $data['name'] : 'Afternoon Shift' ) );
				$rstcf->setDescription( '2:30PM - 10:30PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( true );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( true );

					$rstf->setStartTime( strtotime( '02:30 PM' ) );
					$rstf->setEndTime( strtotime( '10:30 PM' ) );

					if ( isset($data['schedule_policy_id']) && TTUUID::isUUID( $data['schedule_policy_id'] ) && $data['schedule_policy_id'] != TTUUID::getZeroID() && $data['schedule_policy_id'] != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $data['schedule_policy_id'] );
					}
					$rstf->setBranch( isset( $data['branch_id'] ) ? $data['branch_id'] : TTUUID::getNotExistID() );             //Default
					$rstf->setDepartment( isset( $data['department_id'] ) ? $data['department_id'] : TTUUID::getNotExistID() ); //Default
					$rstf->setJob( isset( $data['job_id'] ) ? $data['job_id'] : TTUUID::getNotExistID() );                      //Default
					$rstf->setJobItem( isset( $data['job_item_id'] ) ? $data['job_item_id'] : TTUUID::getNotExistID() );        //Default
					$rstf->setPunchTag( isset( $data['punch_tag_id'] ) ? $data['punch_tag_id'] : [] );                          //Default
					$rstf->setOpenShiftMultiplier( ( isset($data['open_shift_multiplier']) ) ? $data['open_shift_multiplier'] : 1 );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
		}

		Debug::Text( 'ERROR Saving schedule template!', __FILE__, __LINE__, __METHOD__, 10 );

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
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, [ 5, 20, 25, 30, 40, 50, 100, 110 ], $start_date, $end_date );
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

	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getId();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text( '&nbsp;&nbsp; Current Accrual Balance: ' . $accrual_balance, __FILE__, __LINE__, __METHOD__, 10 );

		return $accrual_balance;
	}

	/*
	 Tests:
		- Spanning midnight
		- Spanning DST.

	*/

	/**
	 * @group Schedule_testScheduleA
	 */
	function testScheduleA() {
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:00AM',
				'end_time'           => '5:00PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testScheduleB
	 */
	function testScheduleB() {
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::incrementDate( TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' ); //Was: (TTDate::getBeginWeekEpoch( time() ) + (86400 * 1.5)); //Use current year, handle DST.
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 11:00PM',
				'end_time'           => '8:00AM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testScheduleDSTFall
	 */
	function testScheduleDSTFall() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$date_epoch = strtotime( '02-Nov-2013' ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = strtotime( '03-Nov-2013' ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => 0,
				'start_time'         => ' 11:00PM',
				'end_time'           => '7:00AM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 9 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testScheduleDSTFallB
	 */
	function testScheduleDSTFallB() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$date_epoch = strtotime( '05-Nov-2016' ); //Use current year

		TTDate::setTimeFormat( 'g:i A T' );
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => 0,
				//'start_time' => '6:00PM PDT', //These will fail due to parsing PDT/PST -- ALSO SEE: test_DST() regarding the "quirk" about PST date parsing.
				//'end_time' => '6:00AM PST', //These will fail due to parsing PDT/PST -- ALSO SEE: test_DST() regarding the "quirk" about PST date parsing.
				'start_time'         => '6:00PM America/Vancouver',
				'end_time'           => '6:00AM America/Vancouver',
		] );
		TTDate::setTimeFormat( 'g:i A' );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( '05-Nov-16', TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( '06-Nov-16', TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 13 * 3600 ), $s_obj->getTotalTime() ); //6PM -> 6AM = 12hrs, plus 1hr DST.
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testScheduleDSTSpring
	 */
	function testScheduleDSTSpring() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$date_epoch = strtotime( '09-Mar-2013' ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = strtotime( '10-Mar-2013' ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => 0,
				'start_time'         => ' 11:00PM',
				'end_time'           => '7:00AM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 7 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testScheduleUnderTimePolicyA
	 */
	function testScheduleUnderTimePolicyA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createMealPolicy( 10 );                                                        //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], 0, $this->policy_ids['absence_policy'][10] ); //Partial Shift Only
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:00AM',
				'end_time'           => '4:00PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}


		//Create punches to trigger undertime on same day.
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
									  'punch_tag_id'  => []
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                  //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );     //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Absence Time
		$this->assertEquals( 25, $udt_arr[$date_epoch][2]['object_type_id'] );                                  //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] );     //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Absence Time
		$this->assertEquals( 50, $udt_arr[$date_epoch][3]['object_type_id'] );                                  //Absence
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][900] );     //Absence
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );

		//Add a 0.5hr absence of the same type, but because there is already an entry for this,
		//this will take precedance and override the undertime absence.
		//Therefore it shouldn't change the accrual due to the conflict detection.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, ( 0.5 * 3600 ), $this->policy_ids['absence_policy'][10], true );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 0.5 * -3600 ) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 1.0 * -3600 ) );


		//Add a 1hr absence of the same type, but because there is already an entry for this,
		//this will take precedance and override the undertime absence.
		//Therefore it shouldn't change the accrual due to the conflict detection.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $this->policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );


		//Add a 2hr absence of the same type, this should adjust the accrual balance by 2hrs though.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, ( 2 * 3600 ), $this->policy_ids['absence_policy'][10], true );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 2 * -3600 ) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );


		//Add a 1hr absence of a *different*  type
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $this->policy_ids['absence_policy'][30], true );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][30] );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][30] );
		$this->assertEquals( $accrual_balance, ( 0 * -3600 ) );

		return true;
	}

	/**
	 * @group Schedule_testScheduleUnderTimePolicyB
	 */
	function testScheduleUnderTimePolicyB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createMealPolicy( 10 );                                                    //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], 0, 0 );                                   //No undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:00AM',
				'end_time'           => '4:00PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}


		//Create punches to trigger undertime on same day.
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
									  'punch_tag_id'  => [],
							  ],
							  true
		);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch( $date_epoch ), TTDate::getEndDayEpoch( $date_epoch ) );
		//print_r($punch_arr);
		$this->assertCount( 1, $punch_arr[$date_epoch] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                              //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] ); //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Absence Time
		//$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 25 ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Absence Time
		//$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 50 ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( 0, $accrual_balance );

		return true;
	}

	/**
	 * @group Schedule_testScheduleUnderTimePolicyC
	 */
	function testScheduleUnderTimePolicyC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$this->createMealPolicy( 10 );                                                        //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:00AM',
				'end_time'           => '4:00PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Absence Time
		$this->assertEquals( 25, $udt_arr[$date_epoch][1]['object_type_id'] );                                  //Absence
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][900] );     //Absence
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Absence Time
		$this->assertEquals( 50, $udt_arr[$date_epoch][2]['object_type_id'] );                                  //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] );     //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, ( -8 * 3600 ) );

		return true;
	}

	/**
	 * @group Schedule_testScheduleConflictA
	 */
	function testScheduleConflictA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:35AM',
				'end_time'           => '4:30PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:35PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:25AM',
				'end_time'           => '4:30PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:25PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:25AM',
				'end_time'           => '4:25PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:35AM',
				'end_time'           => '4:35PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:25AM',
				'end_time'           => '4:35PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 1:00PM',
				'end_time'           => '1:05PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 1:25AM',
				'end_time'           => '11:35PM',
		] );
		$this->assertEquals( false, $schedule_id ); //Validation error should occur, conflicting start/end time.

		return true;
	}

	/**
	 * @group Schedule_testOpenScheduleConflictA
	 */
	function testOpenScheduleConflictA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$iso_date_stamp = TTDate::getISODateStamp( $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create OPEN shift.
		$open_schedule_id = $this->createSchedule( 0, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $open_schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		] );
		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( true, false );
		}

		$tmp_schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
		] );
		$this->assertEquals( false, $tmp_schedule_id ); //Validation error should occur, conflicting start/end time.


		$tmp_schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
				'replaced_id'        => $schedule_id,
		] );
		$this->assertEquals( false, $tmp_schedule_id ); //Validation error should occur, conflicting start/end time.


		//Attempt to "fill" a shift already assigned to a user to someone else.
		$schedule_id2 = $this->createSchedule( $this->user_id2, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
				'replaced_id'        => $schedule_id,
		] );
		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id2 );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		$tmp_schedule_id = $this->createSchedule( $this->user_id2, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
				'replaced_id'        => $schedule_id,
		] );
		$this->assertEquals( false, $tmp_schedule_id ); //Validation error should occur, conflicting start/end time.


		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 2, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );

			$this->assertEquals( $schedule_id2, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $this->user_id2, $schedule_arr[$iso_date_stamp][1]['user_id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
		} else {
			$this->assertEquals( true, false );
		}

		//Now delete the schedules and make sure the original open shift reappears.
		$dd->deleteSchedule( $schedule_id );
		$dd->deleteSchedule( $schedule_id2 );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][0]['user_id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testOpenScheduleConflictB
	 */
	function testOpenScheduleConflictB() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'day' ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$iso_date_stamp = TTDate::getISODateStamp( $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create OPEN shift.
		$open_schedule_id = $this->createSchedule( 0, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		] );

		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $open_schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		] );
		$slf = TTNew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate( 'DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( ( 8 * 3600 ), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( true, false );
		}

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now edit the filled shift and change just the note, make sure the open shift does not reappear.
		$dd->editSchedule( $schedule_id, [ 'note' => 'Test1' ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the start/end time and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, [ 'start_time' => strtotime( '8:35AM', $date_epoch ) ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][1] ) ) {
			$this->assertCount( 2, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the start/end time back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, [ 'start_time' => strtotime( '8:30AM', $date_epoch ) ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the branch and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, [ 'branch_id' => $this->branch_id ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][1] ) ) {
			$this->assertCount( 2, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the branch back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, [ 'branch_id' => 0 ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the department and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, [ 'department_id' => $this->department_id ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][1] ) ) {
			$this->assertCount( 2, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( true, false );
		}


		//Now change the department back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, [ 'department_id' => 0 ] );
		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $date_epoch, 'end_date' => $date_epoch ] );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset( $schedule_arr[$iso_date_stamp][0] ) ) {
			$this->assertCount( 1, $schedule_arr[$iso_date_stamp] );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( true, false );
		}

		return true;
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillA
	 */
	function testRecurringOpenScheduleFillA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getEndWeekEpoch( time() );

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create recurring schedule template with 3 open shift multiplier
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 3 ] );

		//Create OPEN shift recurring schedule.
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );

		//Create 2x employee recurring schedules to fill OPEN shifts.
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id, $this->user_id2 ] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 2:
						$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		//Create a new OPEN committed shift that override the last remaining OPEN shift.
		$this->createSchedule( TTUUID::getZeroID(), TTDate::incrementDate( $start_epoch, 0, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 2:
						$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		//Create a new OPEN committed shift that override the last remaining OPEN shift.
		$this->createSchedule( TTUUID::getZeroID(), TTDate::incrementDate( $start_epoch, 1, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		//Create a committed shift that overrides a recurring shift with exact same settings.
		$this->createSchedule( $this->user_id, TTDate::incrementDate( $start_epoch, 1, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 2:
						$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		//Create a new OPEN committed ABSENCE shift that override the last remaining OPEN shift.
		$this->createSchedule( TTUUID::getZeroID(), TTDate::incrementDate( $start_epoch, 2, 'day' ), [
				'status_id'          => 20, //Absent
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 2, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							$this->assertEquals( 20, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							$this->assertEquals( 10, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							$this->assertEquals( 10, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
					}
				} else {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							$this->assertEquals( 10, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							$this->assertEquals( 10, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							$this->assertEquals( 10, $schedule_shift_arr['status_id'] );
							$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
							break;
					}
				}

				$i++;
			}
		}


		//Create a committed shift assigned to the 3rd user that will override the remaining OPEN shift.
		$this->createSchedule( $this->user_id3, TTDate::incrementDate( $start_epoch, 3, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 3, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}

				}
				$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );

				$i++;
			}
		}


		//Create a committed shift assigned to a user that already has a recurring schedule to ensure it doesn't override another OPEN shift.
		$this->createSchedule( $this->user_id, TTDate::incrementDate( $start_epoch, 4, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			//var_dump(TTDate::getISODateStamp($date_epoch));
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 3, 'day') ) ){
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 4, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}
				} else {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}

				}
				$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );

				$i++;
			}
		}


		//Create a new OPEN committed shift that override the last remaining OPEN shift.
		$this->createSchedule( TTUUID::getZeroID(), TTDate::incrementDate( $start_epoch, 5, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		//Fill above committed OPEN shift with a new committed NON-OPEN shift.
		$this->createSchedule( $this->user_id3, TTDate::incrementDate( $start_epoch, 5, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 3, 'day') ) ){
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 4, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 5, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}

				}
				$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );

				$i++;
			}
		}


		//Override a recurring schedule that itself overrides a OPEN shift, but change the shift times so the original OPEN shift should no longer be overridden.
		$this->createSchedule( $this->user_id2, TTDate::incrementDate( $start_epoch, 6, 'day'), [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:15AM',
				'end_time'           => '3:15PM',
		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 7, $schedule_arr );
		$this->assertCount( 7, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 6, 'day') )  ) {
				$this->assertCount( 4, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			} else {
				$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )], TTDate::getISODateStamp( $date_epoch ) );
			}

			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 3, 'day') ) ){
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 4, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 5, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
							break;
					}
				} else if ( TTDate::getISODateStamp( $date_epoch ) == TTDate::getISODateStamp( TTDate::incrementDate( $start_epoch, 6, 'day') ) ) {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 3:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}
				} else {
					switch ( $i ) {
						case 0:
							$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
							break;
						case 1:
							$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
							break;
						case 2:
							$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
							break;
					}

				}
				$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );

				$i++;
			}
		}

		return true;
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillB
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testRecurringOpenScheduleFillB() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getBeginWeekEpoch( time() ); //One Day.

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create three separate shifts that all either overlap or touch each other.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );

		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 20, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );

		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 22, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );


		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->user_id2, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '3:00PM',
				'end_time'           => '11:00PM',
		] );

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->user_id3, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '2:30PM',
				'end_time'           => '10:30PM',
		] );


		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 3, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id3, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 28800, $schedule_shift_arr['total_time'] );
						break;
					case 2:
						$this->assertEquals( $this->user_id2, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 28800, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillC
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testRecurringOpenScheduleFillC() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = $start_epoch; //1 Day

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => TTUUID::getNotExistID(), 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 1, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 2, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => TTUUID::getNotExistID(), 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 3, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 4, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => TTUUID::getNotExistID(), 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 5, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 6, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => TTUUID::getNotExistID(), 'punch_tag_id' => [], 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 7, 'day' );
		$end_epoch = $start_epoch;

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => [], 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillD
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testRecurringOpenScheduleFillD() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id2 ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 1, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 2, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id2 ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 3, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id2 ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 4, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}





		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 5, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id2, null, $this->punch_tag_id_2 ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id,  'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 6, 'day' );
		$end_epoch = $start_epoch;

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//This is a specific branch, that is not the users default, therefore it shouldn't fill the above shift.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'EE Morning Shift', 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->tmp_user_id ] ); //Assigned Shift

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}


	/**
	 * @group Schedule_testRecurringOpenScheduleFillE
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testRecurringOpenScheduleFillE() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->tmp_user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 1, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->tmp_user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 2, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->tmp_user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 3, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->tmp_user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
				'job_id'		     => $this->job_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}


		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 4, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( $this->tmp_user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
				'job_id'		     => $this->job_id,
				'job_item_id'		 => $this->job_item_id,
				'punch_tag_id'		 => $this->punch_tag_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->tmp_user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}



		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 5, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( TTUUID::getZeroID(), $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
				'job_id'		     => $this->job_id,
				//'job_item_id'		 => $this->job_item_id, //This is not set, therefore OPEN recurring shift will not be filled.
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		$start_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 6, 'day' );
		$end_epoch = $start_epoch; //1 Day

		$this->tmp_user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999, null, $this->branch_id, $this->department_id, null, null, null, null, null, null, $this->job_id, $this->job_item_id, null, $this->punch_tag_id ); //Set Default Branch

		//Create OPEN recurring shift with the Branch set to --Default--
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'name' => 'OPEN Morning Shift', 'branch_id' => $this->branch_id, 'department_id' => $this->department_id, 'job_id' => $this->job_id, 'job_item_id' => $this->job_item_id, 'punch_tag_id' => $this->punch_tag_id, 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] ); //OPEN Shift

		//Override each of the recurring shifts with a different user.
		$this->createSchedule( TTUUID::getZeroID(), $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
				'job_id'		     => $this->job_id,
				'job_item_id'		 => $this->job_item_id,
				'punch_tag_id'		 => $this->punch_tag_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * Make sure absence shifts assigned to an employee don't fill recurring open shifts. Absence shifts assigned to OPEN employee do fill OPEN shifts though.
	 * @group Schedule_testRecurringOpenScheduleFillF
	 */
	function testRecurringOpenScheduleFillF() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getBeginWeekEpoch( time() ); //One Day.

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create three separate shifts that all either overlap or touch each other.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );

		//Override each of the recurring shifts with a different user.
		$schedule_id = $this->createSchedule( $this->user_id, $start_epoch, [
				'status_id'          => 20, //Absent
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertEquals( 1, count( $schedule_arr ) );
		$this->assertEquals( 1, count( iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertEquals( 2, count( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] ) );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * Make sure absence shifts assigned to an employee don't fill recurring open shifts. Absence shifts assigned to OPEN employee do fill OPEN shifts though.
	 * @group Schedule_testRecurringOpenScheduleFillF2
	 */
	function testRecurringOpenScheduleFillF2() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id4 ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getBeginWeekEpoch( time() ); //One Day.

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create three separate shifts that all either overlap or touch each other.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id4 ] );

		//Override each of the recurring shifts with a different user.
		//$schedule_id = $this->createSchedule( $this->user_id4, $start_epoch, [
		//		'status_id'          => 20, //Absent
		//		'schedule_policy_id' => $schedule_policy_id,
		//		'start_time'         => '6:00AM',
		//		'end_time'           => '3:00PM',
		//		'branch_id'			 => $this->branch_id2,
		//		'department_id'		 => $this->department_id2,
		//] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id4 )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertEquals( 1, count( $schedule_arr ) );
		$this->assertEquals( 1, count( iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertEquals( 1, count( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] ) );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( $this->user_id4, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillF2B
	 */
	function testRecurringOpenScheduleFillF2B() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id4 ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getBeginWeekEpoch( time() ); //One Day.

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create three separate shifts that all either overlap or touch each other.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id4 ] );

		//Override each of the recurring shifts with a different user.
		$schedule_id = $this->createSchedule( $this->user_id4, $start_epoch, [
				'status_id'          => 10, //Absent
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id,
				'department_id'		 => $this->department_id,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id4 )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertEquals( 1, count( $schedule_arr ) );
		$this->assertEquals( 1, count( iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertEquals( 2, count( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] ) );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id4, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * Make sure absence shifts assigned to an employee don't fill recurring open shifts. Absence shifts assigned to OPEN employee do fill OPEN shifts though.
	 * @group Schedule_testRecurringOpenScheduleFillF3
	 */
	function testRecurringOpenScheduleFillF3() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,                                                                                 //Meal
								null,                                                                                 //Exception
								null,                                                                                 //Holiday
								null,                                                                                 //OT
								null,                                                                                 //Premium
								null,                                                                                 //Round
								[ $this->user_id4 ],                                                                   //Users
								null,                                                                                 //Break
								null,                                                                                 //Accrual
								null,                                                                                 //Expense
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ]                                                  //Regular
		);

		$start_epoch = TTDate::getBeginWeekEpoch( time() );
		$end_epoch = TTDate::getBeginWeekEpoch( time() ); //One Day.

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create three separate shifts that all either overlap or touch each other.
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id4 ] );

		//Override each of the recurring shifts with a different user.
		$schedule_id = $this->createSchedule( $this->user_id4, $start_epoch, [
				'status_id'          => 20, //Absent
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:00AM',
				'end_time'           => '3:00PM',
				'branch_id'			 => $this->branch_id2,
				'department_id'		 => $this->department_id2,
		] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id4 )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertEquals( 1, count( $schedule_arr ) );
		$this->assertEquals( 1, count( iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) ) );

		foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp( $date_epoch ), $schedule_arr );
			$this->assertEquals( 2, count( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] ) );
			$i = 0;
			foreach ( $schedule_arr[TTDate::getISODateStamp( $date_epoch )] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id4, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}
	}

	/**
	 * @group Schedule_testRecurringOpenScheduleFillG
	 */
	function testRecurringOpenScheduleFillG() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		//Create OPEN shift with Branch/Department
		//Create Recurring shift that fills above OPEN shift.
		//Create COMMITTED shift that fills Recurring Shift but with a Job/Task

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


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
								[ $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ], //Absence
								[ $this->policy_ids['regular'][10] ] //Regular
		);

		$start_epoch = TTDate::getBeginDayEpoch( time() );
		$end_epoch = TTDate::getEndDayEpoch( time() );

		$schedule_policy_id = $this->createSchedulePolicy( [ 0 ], $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create recurring schedule template with 1 open shift multiplier
		$recurring_schedule_template_id = $this->createRecurringScheduleTemplate( $this->company_id, 10, [ 'schedule_policy_id' => $schedule_policy_id, 'open_shift_multiplier' => 1 ] );

		//Create OPEN shift recurring schedule.
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ TTUUID::getZeroID() ] );

		//Create 1x employee recurring schedules to fill OPEN shifts.
		$dd->createRecurringSchedule( $this->company_id, $recurring_schedule_template_id, $start_epoch, $end_epoch, [ $this->user_id ] );

		//Populate global variables for current_user.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 1, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		//Create a committed shift that overrides the same users recurring shift with Branch/Dept/Job/Task settings.
		//  This tests for a bug where setting a Job/Task would cause it to NOT override the recurring shift on the 2nd layer, and let the 8th layer override it instead.
		$this->createSchedule( $this->user_id, $start_epoch, [
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '6:15AM',
				'end_time'           => '3:15PM',
				'branch_id'          => $this->branch_id,
				'department_id'      => $this->department_id,
				'job_id'             => $this->job_id,
				'job_item_id'        => $this->job_item_id,
				'punch_tag_id'       => $this->punch_tag_id,

		] );

		$schedule_arr = $sf->getScheduleArray( [ 'start_date' => $start_epoch, 'end_date' => $end_epoch ] );
		//var_dump($schedule_arr);
		$this->assertCount( 1, $schedule_arr );
		$this->assertCount( 1, iterator_to_array( TTDate::getDatePeriod( $start_epoch, $end_epoch ) ) );

		foreach( TTDate::getDatePeriod( $start_epoch, $end_epoch ) as $date_epoch ) {
			$this->assertArrayHasKey( TTDate::getISODateStamp($date_epoch), $schedule_arr );
			$this->assertCount( 2, $schedule_arr[TTDate::getISODateStamp( $date_epoch )] );
			$i = 0;
			foreach( $schedule_arr[TTDate::getISODateStamp($date_epoch)] as $schedule_shift_arr ) {
				switch ( $i ) {
					case 0:
						$this->assertEquals( TTUUID::getZeroID(), $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
					case 1:
						$this->assertEquals( $this->user_id, $schedule_shift_arr['user_id'] );
						$this->assertEquals( 32400, $schedule_shift_arr['total_time'] );
						break;
				}

				$i++;
			}
		}

		return true;
	}

	/**
	 * @group Schedule_testSplitShiftByRequestedStartTimeAndEndTime
	 */
	function testSplitShiftByRequestedStartTimeAndEndTime() {

		TTDate::setDateFormat( 'd-M-y' );
		TTDate::setTimeFormat( 'g:i A' );

		/*
		A.   |-----------------------| <-- Date Pair 1

		0.   |-----------------------| <-- Date Pair 2
		1.      |-------|
		2.	           |-------------------------|
		3. |-----------------------|
		4. |------------------------------------------|
		*/

		//Exact match return the original times.
		//1.   |-----------------------|
		//2.   |-----------------------|
		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:09 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 3:37 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '8:09 AM', '3:37 PM' );

		$expected_shifts = [
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:09 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 3:37 PM' ),
				'split_state'      => 'replaced',
				'split_parent'     => $shift_1['id'],
				'comitted_shift'   => false,
		];

		$this->assertCount( 1, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts[0] );

		//Request is in the middle of the schedule, so it should split the shift into three.
		//0.   |-----------------------|
		//1.         |-----------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 11:30 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:30 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '2:05 PM', '3:15 PM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 11:30 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 2:05 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 2:05 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 3:15 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 3:15 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:30 PM' ),
						'split_state'      => 'modified_new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 3, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );

		//Request is for exact start of shift to the middle of the shift, so it should split the shift into two.
		//0.   |-----------------------|
		//1.   |-----------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '8:00 AM', '8:15 AM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:00 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 8:15 AM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:15 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 2, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );

		//Request is for the middle of the shift to the exact end, so it should split the shift into two.
		//0.   |-----------------------|
		//1.               |-----------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '4:45 PM', '5:00 PM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:00 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:45 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 4:45 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 2, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );

		//Request starts and ends after the shift.
		//1.   |-----------------------|
		//2.            |-------------------------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:35 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:27 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '11:39 AM', '5:21 PM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:35 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 11:39 AM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 11:39 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:21 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 2, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );


		//Start before and end before
		//1.   |-----------------------|
		//2. |------------------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 9:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 3:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '8:43 AM', '2:00 PM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:43 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 2:00 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 2:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 3:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 2, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );


		//Request starts before and ends after shift, engulfs/overlaps the entire shift.
		//1.     |------------------|
		//2.  |-------------------------|
		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 10:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '8:30 AM', '4:35 PM' );

		$expected_shifts = [
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 8:30 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 4:35 PM' ),
				'split_state'      => 'replaced',
				'split_parent'     => $shift_1['id'],
				'comitted_shift'   => false,
		];

		$this->assertCount( 1, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts[0] );

		//Split in middle of two shifts, touching the end of one and start of another
		//Also tests only the new shift merges in the provided properties.
		//1.  |-------|              |----------|
		//2.       |---------------------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 10:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 2:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];

		$shift_2 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 9:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];

		$merge_properties = [
				'value_1' => true,
				'value_2' => 7,
		];

		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1, $shift_2 ], '1:00 PM', '7:00 PM', $merge_properties );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 10:00 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 1:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 1:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'value_1'          => true,
						'value_2'          => 7,
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 9:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_2['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 3, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );

		//Split two shifts and entirely overlaps another
		//Also tests only the replaced shift merges in the provided properties.
		//1.  |-------|   [-------]   |----------|
		//2.       |---------------------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 10:00 AM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 2:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];

		$shift_2 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 3:00 PM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];

		$shift_3 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 6:00 PM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 10:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];

		$merge_properties = [
				'value_1' => true,
				'value_2' => 7,
		];

		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1, $shift_2, $shift_3 ], '1:00 PM', '7:00 PM', $merge_properties );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 10:00 AM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 1:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 1:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'split_state'      => 'replaced',
						'split_parent'     => $shift_1['id'],
						'value_1'          => true,
						'value_2'          => 7,
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 10:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_3['id'],
						'comitted_shift'   => false,
				],
		];


		$this->assertCount( 3, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );

		//Shift and request span across two days
		//Request is in the middle of the schedule, so it should split the shift into three.
		//0.   |-----------------------|
		//1.         |-----------|

		$shift_1 = [
				'date_stamp'       => '26-Jan-22',
				'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
				'end_time_stamp'   => TTDate::parseDateTime( '27-Jan-22 4:00 PM' ),
				'id'               => TTUUID::generateUUID(),
		];
		$split_shifts = TTDate::splitTimesByStartAndEndTime( [ $shift_1 ], '7:00 PM', '2:00 PM' );

		$expected_shifts = [
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 5:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'split_state'      => 'modified',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '26-Jan-22 7:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '27-Jan-22 2:00 PM' ),
						'split_state'      => 'new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
				[
						'start_time_stamp' => TTDate::parseDateTime( '27-Jan-22 2:00 PM' ),
						'end_time_stamp'   => TTDate::parseDateTime( '27-Jan-22 4:00 PM' ),
						'split_state'      => 'modified_new',
						'split_parent'     => $shift_1['id'],
						'comitted_shift'   => false,
				],
		];

		$this->assertCount( 3, $split_shifts );
		$this->assertEquals( $expected_shifts, $split_shifts );
	}
}

?>