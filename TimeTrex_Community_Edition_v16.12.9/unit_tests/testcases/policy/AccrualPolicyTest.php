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

class AccrualPolicyTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
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
		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$user_obj = $this->getUserObject( $this->user_id );
		//Use a consistent hire date, otherwise its difficult to get things correct due to the hire date being in different parts or different pay periods.
		//Make sure it is not on a pay period start date though.
		$user_obj->setHireDate( strtotime( '05-Mar-2001' ) );
		$user_obj->Save( false );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( TTDate::getBeginDayEpoch( TTDate::getBeginYearEpoch( $user_obj->getHireDate() ) ) );
		$this->getAllPayPeriods();

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		$ppsf->setName( 'Semi-Monthly' );
		$ppsf->setDescription( '' );
		$ppsf->setType( 30 );
		$ppsf->setStartWeekDay( 0 );

		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( time(), -42, 'day' ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setPrimaryDayOfMonth( 1 );
		$ppsf->setSecondaryDayOfMonth( 16 );
		$ppsf->setPrimaryTransactionDayOfMonth( 20 );
		$ppsf->setSecondaryTransactionDayOfMonth( 5 );

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

	function createPayPeriods( $start_date = null ) {
		if ( $start_date == '' ) {
			$start_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( time(), -42, 'day' ) );
		}

		//Note for testHourAccrualMaximumBalanceB() it needs to calculate up to todays date.
		$max_pay_periods = ( 26 * 40 ); //Make a lot of pay periods (40yrs) as we need to test 6 years worth of accruals for different milestones.

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = $start_date;
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
				$pfpf->setAccrualBalanceThreshold( (86400 * -999) ); //Don't use default lower threshold of 0.
				break;
			case 100:
				$pfpf->setName( 'Regular' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				$pfpf->setAccrualBalanceThreshold( (86400 * -999) ); //Don't use default lower threshold of 0.
				break;
			case 910:
				$pfpf->setName( 'Bank' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( -1.0 );
				$pfpf->setAccrualBalanceThreshold( (86400 * -999) ); //Don't use default lower threshold of 0.
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

	function createAccrualPolicyUserModifier( $accrual_policy_id, $user_id, $length_of_service_date = null, $accrual_rate = null ) {
		$apumf = TTnew( 'AccrualPolicyUserModifierFactory' ); /** @var AccrualPolicyUserModifierFactory $apumf */
		$apumf->setAccrualPolicy( $accrual_policy_id );
		$apumf->setUser( $user_id );

		if ( $length_of_service_date !== null ) {
			$apumf->setLengthOfServiceDate( $length_of_service_date );
		}

		if ( $accrual_rate !== null ) {
			$apumf->setAccrualRateModifier( $accrual_rate );
		}

		if ( $apumf->isValid() ) {
			$insert_id = $apumf->Save();
			Debug::Text( 'AccrualPolicyUserModifier ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating AccrualPolicyUserModifier!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createAccrualPolicyAccount( $company_id, $type ) {
		$apaf = TTnew( 'AccrualPolicyAccountFactory' ); /** @var AccrualPolicyAccountFactory $apaf */

		$apaf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Bank Time
				$apaf->setName( 'Unit Test' );
				break;
			case 20: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Personal Time Off (PTO)/Vacation' );
				break;
			case 30: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Sick Time' );
				break;
		}

		if ( $apaf->isValid() ) {
			$insert_id = $apaf->Save();
			Debug::Text( 'Accrual Policy Account ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Accrual Policy Account!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createAccrualPolicy( $company_id, $type, $accrual_policy_account_id, $contributing_shift_policy_id = 0 ) {
		$apf = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $apf */

		$apf->setCompany( $company_id );

		switch ( $type ) {
			case 20: //Calendar Based: Check minimum employed days
				$apf->setName( 'Calendar: Minimum Employed' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 9999 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 30: //Calendar Based: Check milestone not applied yet.
				$apf->setName( 'Calendar: Milestone not applied' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 9999 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 40: //Calendar Based: Pay Period with one milestone
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 50: //Calendar Based: Pay Period with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 60: //Calendar Based: Pay Period with 5 milestones
				$apf->setName( 'Calendar: 5 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 70: //Calendar Based: Pay Period with 5 milestones rolling over on January 1st.
				$apf->setName( 'Calendar: 5 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( false );
				$apf->setMilestoneRolloverMonth( 1 );
				$apf->setMilestoneRolloverDayOfMonth( 1 );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 71: //Calendar Based: Pay Period with 5 milestones rolling over on July 15th.
				$apf->setName( 'Calendar: 5 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( false );
				$apf->setMilestoneRolloverMonth( 9 ); //Sept
				$apf->setMilestoneRolloverDayOfMonth( 15 );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 80: //Calendar Based: Pay Period with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;


			case 200: //Calendar Based: Weekly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 0 ); //Sunday

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 210: //Calendar Based: Weekly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 3 ); //Wed

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 300: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 310: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 15 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 320: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 31 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 350: //Calendar Based: Quarterly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 25 ); //Quarterly
				$apf->setApplyFrequencyDayOfMonth( 1 );
				$apf->setApplyFrequencyQuarterMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 360: //Calendar Based: Quarterly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 25 ); //Quarterly
				$apf->setApplyFrequencyDayOfMonth( 15 );
				$apf->setApplyFrequencyQuarterMonth( 2 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 370: //Calendar Based: Quarterly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 25 ); //Quarterly
				$apf->setApplyFrequencyDayOfMonth( 31 );
				$apf->setApplyFrequencyQuarterMonth( 3 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 400: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 402: //Calendar Based: Annually with 2 milestones - Minimum Employed Days=90
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );

				$apf->setMinimumEmployedDays( 90 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 410: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 6 );
				$apf->setApplyFrequencyDayOfMonth( 15 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 420: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyHireDate( true );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 500: //Calendar Based: Monthly with 2 milestones and rollover set low.
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1000: //Hour Based: 1 milestone, no maximums at all.
			case 1001: //Hour Based: 1 milestone, Rollover = 0 (set below).
				$apf->setName( 'Hour: 1 milestone (basic)' );
				$apf->setType( 30 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1010: //Hour Based: 1 milestone, maximum balance.
				$apf->setName( 'Hour: 1 milestone (max. balance)' );
				$apf->setType( 30 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1020: //Hour Based: 1 milestone, maximum balance.
				$apf->setName( 'Hour: 1 milestone (max. annual)' );
				$apf->setType( 30 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1100: //Hour Based: 1 milestone, no maximums at all. Minimum/Maximum eligibility
				$apf->setName( 'Hour: 1 milestone (basic) w/Eligiblity' );
				$apf->setType( 30 );

				//Eligibility
				$apf->setEligiblePeriod( 40 ); //40=Weekly
				$apf->setMinimumEligibleTime( ( 7 * 3600 ) );
				$apf->setMaximumEligibleTime( ( 53 * 3600 ) );
				$apf->setMinimumEligibleApplyRetroactive( false );
				$apf->setEligibleContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1101: //Hour Based: 1 milestone, no maximums at all. Minimum/Maximum eligibility
				$apf->setName( 'Hour: 1 milestone (basic) w/Eligiblity' );
				$apf->setType( 30 );

				//Eligibility
				$apf->setEligiblePeriod( 40 ); //40=Weekly
				$apf->setMinimumEligibleTime( ( 11 * 3600 ) );
				$apf->setMaximumEligibleTime( ( 53 * 3600 ) );
				$apf->setMinimumEligibleApplyRetroactive( true );
				$apf->setEligibleContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1102: //Hour Based: 1 milestone, no maximums at all. Minimum/Maximum eligibility
				$apf->setName( 'Hour: 1 milestone (basic) w/Eligiblity' );
				$apf->setType( 30 );

				//Eligibility
				$apf->setEligiblePeriod( 10 ); //10=Pay Period
				$apf->setMinimumEligibleTime( ( 11 * 3600 ) );
				$apf->setMaximumEligibleTime( ( 53 * 3600 ) );
				$apf->setMinimumEligibleApplyRetroactive( true );
				$apf->setEligibleContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 1103: //Hour Based: 1 milestone, no maximums at all. Minimum/Maximum eligibility
				$apf->setName( 'Hour: 1 milestone (basic) w/Eligiblity' );
				$apf->setType( 30 );

				//Eligibility
				$apf->setEligiblePeriod( 10 ); //10=Pay Period
				$apf->setMinimumEligibleTime( ( 40 * 3600 ) );
				$apf->setMaximumEligibleTime( ( 80 * 3600 ) );
				$apf->setMinimumEligibleApplyRetroactive( true );
				$apf->setEligibleContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 1200: //Calendar Based: Pay Period with one milestone - Minimum/Maximum eligibility
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				//Eligibility
				$apf->setEligiblePeriod( 10 ); //10=Pay Period
				$apf->setMinimumEligibleTime( ( 11 * 3600 ) );
				$apf->setMaximumEligibleTime( ( 53 * 3600 ) );
				$apf->setMinimumEligibleApplyRetroactive( true );
				$apf->setEligibleContributingShiftPolicy( $contributing_shift_policy_id );

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 2000: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2001: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 15 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2010: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 3 ); //Wednesday

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2020: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2030: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2031: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyHireDate( true );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2100: //Calendar Based: Pay Period with one milestone - Opening Balance
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2101: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2110: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 3 ); //Wednesday

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2120: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2122: //Calendar Based: Pay Period with one milestone - ProRate Initial Period w/Minimum Employed Days
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 90 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2130: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2131: //Calendar Based: Pay Period with one milestone - ProRate Initial Period
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyHireDate( true );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 2132: //Calendar Based: Pay Period with one milestone - ProRate Initial Period w/Minimum Employed Days
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( true );
				$apf->setEnableOpeningBalance( true );
				$apf->setEnableProRateInitialPeriod( true );

				$apf->setMinimumEmployedDays( 90 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
		}

		if ( $apf->isValid() ) {
			$insert_id = $apf->Save();
			Debug::Text( 'Accrual Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$apmf = TTnew( 'AccrualPolicyMilestoneFactory' ); /** @var AccrualPolicyMilestoneFactory $apmf */

			switch ( $type ) {
				case 20:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 30:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 99 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 40:
				case 1200:
				case 2000:
				case 2001:
				case 2010:
				case 2020:
				case 2030:
				case 2031:
				case 2100:
				case 2101:
				case 2110:
				case 2120:
				case 2122:
				case 2130:
				case 2131:
				case 2132:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 50:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 60:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 2 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 15 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 3 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 20 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 4 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 25 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 5 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 30 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					break;
				case 70:
				case 71:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 2 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 15 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 3 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 20 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 4 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 25 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 5 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 30 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;

				case 80:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 6 );
					$apmf->setMaximumTime( ( 3600 * 8 ) * 3 );
					$apmf->setRolloverTime( ( 3600 * 8 ) * 2 );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 8 ) * 5 );
					$apmf->setRolloverTime( ( 3600 * 8 ) * 4 );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 200:
				case 210:
				case 300:
				case 310:
				case 320:
				case 350:
				case 360:
				case 370:
				case 400:
				case 402:
				case 410:
				case 420:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 500:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 5 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 8 ) * 1 );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( ( 3600 * 8 ) * 10 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 8 ) * 2 );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 1000:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( 1.0 );
					$apmf->setAnnualMaximumTime( 0 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 1001:
				case 1100:
				case 1101:
				case 1102:
				case 1103:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( 1.0 );
					$apmf->setAnnualMaximumTime( 0 );
					$apmf->setMaximumTime( ( 3600 * 9999 ) );
					$apmf->setRolloverTime( ( 3600 * 0 ) ); //Zero out balance.

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 1010:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( 1.0 );
					$apmf->setAnnualMaximumTime( ( 3600 * 9999 ) );
					$apmf->setMaximumTime( ( 3600 * 118 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
				case 1020:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( 1.0 );
					$apmf->setAnnualMaximumTime( ( 3600 * 112 ) );
					$apmf->setMaximumTime( ( 3600 * 118 ) );
					$apmf->setRolloverTime( ( 3600 * 9999 ) );

					if ( $apmf->isValid() ) {
						Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
						$apmf->Save();
					}
					break;
			}

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Accrual Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPunches( $start_date, $end_date, $in_time, $out_time ) {
		global $dd;

		Debug::Text( 'Start Date: ' . TTDate::getDate( 'DATE', $start_date ) . '(' . $start_date . ') End: ' . TTDate::getDate( 'DATE', $end_date ) . '(' . $end_date . ')', __FILE__, __LINE__, __METHOD__, 10 );
		for ( $i = $start_date; $i < $end_date; $i += ( 86400 + 3601 ) ) {
			$i = TTDate::getBeginDayEpoch( $i );

			Debug::Text( 'Date: ' . TTDate::getDate( 'DATE', $i ) . ' In: ' . $in_time . ' Out: ' . $out_time, __FILE__, __LINE__, __METHOD__, 10 );
			$dd->createPunchPair( $this->user_id,
								  strtotime( TTDate::getDate( 'DATE', $i ) . ' ' . $in_time ),
								  strtotime( TTDate::getDate( 'DATE', $i ) . ' ' . $out_time ),
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
		}

		return true;
	}

	function calcAccrualTime( $company_id, $accrual_policy_id, $start_date, $end_date, $day_multiplier = 1 ) {
		$start_date = TTDate::getMiddleDayEpoch( $start_date );
		$end_date = TTDate::getMiddleDayEpoch( $end_date );
		//$offset = 79200;
		$offset = ( ( 86400 * $day_multiplier ) - 7200 );

		$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */

		$aplf->getByIdAndCompanyId( $accrual_policy_id, $company_id );
		if ( $aplf->getRecordCount() > 0 ) {
			foreach ( $aplf as $ap_obj ) {
				$aplf->StartTransaction();

				Debug::Text( 'Recalculating Accruals between Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				$x = 0;
				for ( $i = $start_date; $i < $end_date; $i += ( 86400 * $day_multiplier ) ) { //Try skipping by two days to speed up this test.
					//Debug::Text('Recalculating Accruals for Date: '. TTDate::getDate('DATE+TIME', TTDate::getBeginDayEpoch( $i ) ), __FILE__, __LINE__, __METHOD__,10);
					$ap_obj->addAccrualPolicyTime( ( TTDate::getBeginDayEpoch( $i ) + ( 3600 * 4 ) ), $offset ); //Assume a 4AM maintenance job run-time.
					//Debug::Text('----------------------------------', __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}

				$aplf->CommitTransaction();
			}
		}

		return true;
	}

	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text( '   Current Accrual Balance: ' . $accrual_balance, __FILE__, __LINE__, __METHOD__, 10 );

		return $accrual_balance;
	}

	function getAccrualArray( $user_id, $accrual_policy_account_id = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		$retarr = [];

		//Check min/max times of accrual policy.
		$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
		$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccount( $this->company_id, $user_id, $accrual_policy_account_id );
		if ( $alf->getRecordCount() > 0 ) {
			foreach ( $alf as $a_obj ) {
				$retarr[TTDate::getMiddleDayEpoch( $a_obj->getTimeStamp() )][] = $a_obj;
			}
		}

		//Debug::Arr( $retarr, '   Current Accrual Records: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retarr;
	}

	function getUserObject( $user_id ) {
		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			return $ulf->getCurrent();
		}

		return false;
	}


	/*
	 Tests:
		Calendar Based - Minimum Employed Days
		Calendar Based - 1st milestone high length of service.
		Calendar Based - PayPeriod Frequency (1 milestone)
		Calendar Based - PayPeriod Frequency (2 milestones)
		Calendar Based - PayPeriod Frequency (5 milestones)
	*/

	/**
	 * @group AccrualPolicy_testCalendarAccrualA
	 */
	function testCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( TTDate::incrementDate( $hire_date, 2, 'year' ) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 20, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualB
	 */
	function testCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( TTDate::incrementDate( $hire_date, 2, 'year' ) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 30, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualC
	 */
	function testCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( TTDate::incrementDate( $hire_date, 2, 'year' ) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 40, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 40 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualD
	 */
	function testCalendarAccrualD() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( TTDate::incrementDate( $hire_date, 2, 'year' ) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 50, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 80 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualE
	 */
	function testCalendarAccrualE() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 60, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+7 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 1080 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualF
	 */
	function testCalendarAccrualF() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 70, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+7 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 4038000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualF2A
	 */
	function testCalendarAccrualF2A() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 71, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+1 year', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 144000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualF2B
	 */
	function testCalendarAccrualF2B() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate(); //05-Mar-2001
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 71, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+3 months', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 36000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualF2C
	 */
	function testCalendarAccrualF2C() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate(); //05-Mar-2001
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 71, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+7 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 3528000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualG
	 */
	function testCalendarAccrualG() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 80, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+5 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 144000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testWeeklyCalendarAccrualA
	 */
	function testWeeklyCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 200, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, 434733 ); //Was this value before we added pro-rating/opening balance. The only difference was the first entry.
		$this->assertEquals( 431964, $accrual_balance );
	}


	/**
	 * @group AccrualPolicy_testWeeklyCalendarAccrualB
	 */
	function testWeeklyCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 210, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 431964, $accrual_balance );
	}


	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualA
	 */
	function testMonthlyCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 300, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance ); //Was this value before we added pro-rating/opening balance. The only difference was the first entry.
		//$this->assertEquals( $accrual_balance, 420000 );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualB
	 */
	function testMonthlyCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 310, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualC
	 */
	function testMonthlyCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 320, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance );
	}


	/**
	 * @group AccrualPolicy_testQuarterlyCalendarAccrualA
	 */
	function testQuarterlyCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 350, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testQuarterlyCalendarAccrualB
	 */
	function testQuarterlyCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 360, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testQuarterlyCalendarAccrualC
	 */
	function testQuarterlyCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 370, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+2 years', $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 432000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualA
	 */
	function testAnnualCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 400, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+5 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 1296000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualB
	 */
	function testAnnualCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 410, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+5 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 1296000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualC
	 */
	function testAnnualCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 420, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+5 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 1296000, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualWithMinimumEmployedDaysA
	 */
	function testAnnualCalendarAccrualWithMinimumEmployedDaysA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 402, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+5 years', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 1440000, $accrual_balance );

		//Make sure the proper accrual records exist that reduce the accrual, then accrue more on the last three days.
		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 6, $accrual_arr ); //6 total days.

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '03-Jun-01' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 40 * 3600 ) );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '01-Jan-02' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 40 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualwithRolloverA
	 */
	function testMonthlyCalendarAccrualwithRolloverA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 500, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+13 months', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 81600, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualwithRolloverB
	 */
	function testMonthlyCalendarAccrualwithRolloverB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 500, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+26 months', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 105600, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualwithRolloverC2
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testMonthlyCalendarAccrualwithRolloverC2() {
		global $dd;

		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 500, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		//Test UserModifier values.
		$this->createAccrualPolicyUserModifier( $accrual_policy_id, $this->user_id, strtotime( '+10 months', $hire_date ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+37 months', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 129600, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualwithRolloverC3
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testMonthlyCalendarAccrualwithRolloverC3() {
		global $dd;

		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 500, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		//Test UserModifier values.
		$this->createAccrualPolicyUserModifier( $accrual_policy_id, $this->user_id, strtotime( '+10 months', $hire_date ), 2 );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+37 months', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 201600, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualwithRolloverC4
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testMonthlyCalendarAccrualwithRolloverC4() {
		global $dd;

		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 500, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		//Test UserModifier values.
		$this->createAccrualPolicyUserModifier( $accrual_policy_id, $this->user_id, strtotime( '+10 months', $hire_date ), 0 ); //Accrual modifier at 0, should stop any accrual from occurring.

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+37 months', $current_epoch ), 1 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( 0, $accrual_balance );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateA
	 */
	function testCalendarAccrualProRateA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2000, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, ( (111*3600)+(60*13)+20 ) ); //111:13:20 <-- This was pre-MiddleDayEpoch() in the proRate function.
		$this->assertEquals( $accrual_balance, ( ( 111 * 3600 ) + ( 60 * 11 ) + 26 ) ); //111:11:26 <-- This is after MiddleDayEpoch() in the proRate function.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateB
	 */
	function testCalendarAccrualProRateB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2001, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, ( ( 110 * 3600 ) + ( 60 * 0 ) ) ); //110:00
		$this->assertEquals( $accrual_balance, 394400 ); //110:00
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateC
	 */
	function testCalendarAccrualProRateC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2010, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 112 * 3600 ) + ( 60 * 31 ) + 5 ) ); //112:31:05
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateD
	 */
	function testCalendarAccrualProRateD() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2020, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, ( (109*3600)+(60*34)+12 ) ); //109:34:12
		$this->assertEquals( $accrual_balance, ( ( 109 * 3600 ) + ( 60 * 34 ) + 10 ) ); //109:34:10 <-- This is after MiddleDayEpoch() in the proRate function.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateE
	 */
	function testCalendarAccrualProRateE() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2030, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 73 * 3600 ) + ( 60 * 5 ) + 45 ) ); //73:05:45
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateF
	 */
	function testCalendarAccrualProRateF() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2031, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 120 * 3600 ) ) ); //120:00:00
	}


	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateHireDateTimeA
	 */
	function testCalendarAccrualProRateHireDateTimeA() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = TTDate::getMiddleDayEpoch( $u_obj->getHireDate() );
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2020, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 109 * 3600 ) + ( 60 * 34 ) + 10 ) ); //109:34:10
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateHireDateTimeB
	 */
	function testCalendarAccrualProRateHireDateTimeB() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = ( TTDate::getBeginDayEpoch( $u_obj->getHireDate() ) + 60 );
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2020, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 109 * 3600 ) + ( 60 * 34 ) + 10 ) ); //109:34:10
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualProRateHireDateTimeC
	 */
	function testCalendarAccrualProRateHireDateTimeC() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = ( TTDate::getEndDayEpoch( $u_obj->getHireDate() ) - 60 );
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2020, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 109 * 3600 ) + ( 60 * 34 ) + 10 ) ); //109:34:10
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOnHireDateA
	 */
	function testCalendarAccrualOnHireDateA() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $u_obj->getHireDate() ), 10, 'day' ); //15-Mar-2001
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 310, $accrual_policy_account_id ); //15th of each month.
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $current_epoch ), 45, 'day' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 6 * 3600 ) + ( 60 * 40 ) + 0 ) ); //6:40
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOnHireDateB
	 */
	function testCalendarAccrualOnHireDateB() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $u_obj->getHireDate() ), 9, 'day' ); //14-Mar-2001 (day before the frequency date)
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 310, $accrual_policy_account_id ); //15th of each month.
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $current_epoch ), 45, 'day' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 6 * 3600 ) + ( 60 * 40 ) + 0 ) ); //6:40
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOnHireDateC
	 */
	function testCalendarAccrualOnHireDateC() {
		global $dd;

		$u_obj = $this->getUserObject( $this->user_id );
		$u_obj->data['hire_date'] = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $u_obj->getHireDate() ), 11, 'day' ); //16-Mar-2001 (day after the frequency date)
		$u_obj->Save();

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 310, $accrual_policy_account_id ); //15th of each month.
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $current_epoch ), 45, 'day' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 3 * 3600 ) + ( 60 * 20 ) + 0 ) ); //3:20
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceA
	 */
	function testCalendarAccrualOpeningBalanceA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2100, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, ( (112*3600)+(60*53)+20 ) ); //111:53:20
		$this->assertEquals( $accrual_balance, ( ( 112 * 3600 ) + ( 60 * 51 ) + 26 ) ); //111:51:26 <-- This is after MiddleDayEpoch() in the proRate function.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceB
	 */
	function testCalendarAccrualOpeningBalanceB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2101, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//Pro-Rate part of the first accrual balance.
		$this->assertEquals( $accrual_balance, ( ( 112 * 3600 ) + ( 3086 ) ) ); //112:51.432
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceC
	 */
	function testCalendarAccrualOpeningBalanceC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2110, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 113 * 3600 ) + ( 60 * 17 ) + 14 ) ); //113:17:14
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceD
	 */
	function testCalendarAccrualOpeningBalanceD() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2120, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		//$this->assertEquals( $accrual_balance, ( (112*3600)+(60*54)+12 ) ); //112:54:12
		$this->assertEquals( $accrual_balance, ( ( 112 * 3600 ) + ( 60 * 54 ) + 10 ) ); //112:54:10 <-- This is after MiddleDayEpoch() in the proRate function.

		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 34, $accrual_arr ); //34 total records.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceD2
	 */
	function testCalendarAccrualOpeningBalanceD2() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2122, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 112 * 3600 ) + ( 60 * 54 ) + 10 ) );

		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 31, $accrual_arr ); //31 total records. -- 3 less than in testCalendarAccrualOpeningBalanceD()
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceE
	 */
	function testCalendarAccrualOpeningBalanceE() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2130, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 113 * 3600 ) + ( 60 * 5 ) + 45 ) ); //113:05:45

		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 3, $accrual_arr ); //3 total records.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceF
	 */
	function testCalendarAccrualOpeningBalanceF() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2131, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 120 * 3600 ) ) ); //120:00:00
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualOpeningBalanceG
	 */
	function testCalendarAccrualOpeningBalanceG() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 2132, $accrual_policy_account_id );
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								null,
								null,
								null,
								null,
								[ $this->user_id ],
								null,
								[ $accrual_policy_id ] );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, TTDate::incrementDate( TTDate::getEndYearEpoch( $current_epoch ), 2, 'year' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( ( 113 * 3600 ) + ( 60 * 5 ) + 45 ) ); //113:05:45

		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 3, $accrual_arr ); //3 total records.
	}

	/**
	 * @group testAbsenceAccrualPolicyA
	 */
	function testAbsenceAccrualPolicyA() {
		global $dd;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 ); //Bank Time

		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910, $accrual_policy_account_id ); //Bank Accrual

		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

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
								$policy_ids['absence_policy'], //Absence
								null //Regular
		);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );

		//Make sure balance starts at 0
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( 0, $accrual_balance );

		//Day 1
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 1 * -3600 ) );

		//Day 2
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 1, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 2 * -3600 ) );

		//Day 3
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 2, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 3 * -3600 ) );

		//Day 4
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 3, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 4 * -3600 ) );

		//Day 5
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 4, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 5 * -3600 ) );

		//Day 6
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 5, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * -3600 ) );

		//Day 7
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 6, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 7 * -3600 ) );

		//Day 8
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 7, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 8 * -3600 ) );

		//Day 9
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 8, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 9 * -3600 ) );

		//Day 10
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 9, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * -3600 ) );

		//Day 11
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 10, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 11 * -3600 ) );

		//Day 12
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 11, 'day' );
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 12 * -3600 ) );

		//Day 13
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 12, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 13 * -3600 ) );

		//Day 14
		$date_epoch = TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), 13, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 1 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 14 * -3600 ) );

		//Delete absence_id from Day 12th.
		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 13 * -3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualA
	 */
	function testHourAccrualA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1000, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 9, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 110 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 11, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 116 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 120 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualB
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	function testHourAccrualB() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1000, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		//Test UserModifier values.
		$this->createAccrualPolicyUserModifier( $accrual_policy_id, $this->user_id, strtotime( '+10 months', $hire_date ), 2 );

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 12 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 20 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 9, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 220 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 11, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 232 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 240 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualMaximumBalanceA
	 */
	function testHourAccrualMaximumBalanceA() {
		global $dd;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910, $accrual_policy_account_id ); //Bank Accrual

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );
		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1010, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								$policy_ids['absence_policy'], //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * 3600 ) );


		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 9, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 110 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 11, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 116 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Hit maximum balance.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 12, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 5 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 113 * 3600 ) ); //Reduce maximum balance, so we can hit again below.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 13, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Hit maximum balance.
	}

	/**
	 * @group AccrualPolicy_testHourAccrualMaximumBalanceB
	 */
	function testHourAccrualMaximumBalanceB() {
		global $dd;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910, $accrual_policy_account_id ); //Bank Accrual

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );
		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

		//$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1010, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								$policy_ids['absence_policy'], //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), -16, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 4 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( -4 * 3600 ) );


		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), -15, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 13, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 12 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 106 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 2, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 116 * 3600 ) );


		//Recalulcate the last week, then confirm the balance is still the same.
		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			UserDateTotalFactory::reCalculateDay( $ulf->getCurrent(), TTDate::getDateArray( TTDate::getBeginWeekEpoch( $date_epoch ), TTDate::getEndWeekEpoch( $date_epoch ) ) );
		} else {
			$this->assertTrue( false );
		}
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 116 * 3600 ) );
	}

	/**
	 * Test recalculating the entire pay period to ensure the balances are still the same as if the user just punched in/out day-by-day in real-time.
	 * @group AccrualPolicy_testHourAccrualMaximumBalanceReCalculate
	 */
	function testHourAccrualMaximumBalanceReCalculate() {
		global $dd;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910, $accrual_policy_account_id ); //Bank Accrual

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );
		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1010, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								$policy_ids['absence_policy'], //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginWeekEpoch( ( TTDate::getEndWeekEpoch( $hire_date ) + ( 8 * 86400 + 3601 ) ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $current_epoch ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 14 * 3600 ) );


		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $current_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 9, 'day' ), '8:00AM', '8:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $current_epoch ), 11, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 7 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 111 * 3600 ) ); //Reduce maximum balance, so we can hit again below.

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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 117 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Hit maximum balance.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $current_epoch ), 12, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 7 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 111 * 3600 ) ); //Reduce maximum balance, so we can hit again below.

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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 117 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Hit maximum balance.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $current_epoch ), 13, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 7 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 111 * 3600 ) ); //Reduce maximum balance, so we can hit again below.

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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 117 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Hit maximum balance.


		//Make sure the proper accrual records exist that reduce the accrual, then accrue more on the last three days.
		$accrual_arr = $this->getAccrualArray( $this->user_id, $accrual_policy_account_id );
		$this->assertCount( 13, $accrual_arr ); //13 total days.

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '27-Mar-01' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 8 * 3600 ) );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '29-Mar-01' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 7 * 3600 ) );
		$this->assertEquals( $accrual_arr[$date_epoch][1]->getAmount(), ( -7 * 3600 ) );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '30-Mar-01' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 7 * 3600 ) );
		$this->assertEquals( $accrual_arr[$date_epoch][1]->getAmount(), ( -7 * 3600 ) );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '31-Mar-01' ) );
		$this->assertArrayHasKey( $date_epoch, $accrual_arr );
		$this->assertEquals( $accrual_arr[$date_epoch][0]->getAmount(), ( 7 * 3600 ) );
		$this->assertEquals( $accrual_arr[$date_epoch][1]->getAmount(), ( -7 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualAnnualMaximumA
	 */
	function testHourAccrualAnnualMaximumA() {
		global $dd;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910, $accrual_policy_account_id ); //Bank Accrual

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );
		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		//$current_epoch = TTDate::getBeginYearEpoch( $hire_date+(86400*365*5) );
		$current_epoch = TTDate::getBeginMonthEpoch( ( $hire_date - ( 86400 * 7 ) + ( 86400 * 365 * 5 ) ) );

		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1020, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								$policy_ids['absence_policy'], //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * 3600 ) );


		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 9, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 110 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 11, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 112 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 112 * 3600 ) ); //Hit maximum balance.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 12, 'day' );
		$dd->createAbsence( $this->user_id, $date_epoch, ( 5 * 3600 ), $policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 107 * 3600 ) ); //Reduce maximum balance, so we can hit again below.


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 13, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 107 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 107 * 3600 ) ); //Hit maximum balance.

		//
		//Test immediately before employment anniversary date (shouldn't be any increases as maximum accrual limit is still in effect)
		//
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 34, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 107 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 107 * 3600 ) );

		//
		//Test on employment anniversary date (limit should be reset now, so increases balance)
		//
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 35, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:00AM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 109 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 111 * 3600 ) );

		//
		//Test on the day after the employment anniversary date (limit should be reset now, so increases balance)
		//
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $current_epoch ) ), 36, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 117 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 118 * 3600 ) ); //Reached maximum balance here.
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowA
	 */
	function testInApplyFrequencyWindowA() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 10 ); //Each Pay Period
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 79200;

		$current_epoch = strtotime( '14-Aug-2016 1:30AM EDT' );
		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();

		$pay_period_dates = [ 'start_date' => strtotime( '31-Jul-2016 12:00AM' ), 'end_date' => strtotime( '13-Aug-2016 11:59:59PM' ) ];

		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );


		TTDate::setTimeZone( 'America/Denver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$pay_period_dates = [ 'start_date' => strtotime( '31-Jul-2016 12:00AM' ), 'end_date' => strtotime( '13-Aug-2016 11:59:59PM' ) ];

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '13-Aug-2016 11:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '13-Aug-2016 11:59PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 12:00AM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:59:58PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:59:59PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:00PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:01PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 11:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 11:59PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 12:00AM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 12:01AM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 7:59PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 8:00PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 8:01PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 11:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '16-Aug-2016 11:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '17-Aug-2016 11:30PM MDT' ), $offset, $pay_period_dates, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowB
	 */
	function testInApplyFrequencyWindowB() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/Denver', but some users are 'America/New_York'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 10 ); //Each Pay Period
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 79200;

		$current_epoch = strtotime( '14-Aug-2016 1:30AM MDT' );
		TTDate::setTimeZone( 'America/Denver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();

		$pay_period_dates = [ 'start_date' => strtotime( '31-Jul-2016 12:00AM' ), 'end_date' => strtotime( '13-Aug-2016 11:59:59PM' ) ];

		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );


		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$pay_period_dates = [ 'start_date' => strtotime( '31-Jul-2016 12:00AM' ), 'end_date' => strtotime( '13-Aug-2016 11:59:59PM' ) ];

		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '13-Aug-2016 11:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '13-Aug-2016 11:59PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 12:00AM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:59:58PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 9:59:59PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:00PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:01PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 10:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 11:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '14-Aug-2016 11:59PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 12:00AM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 12:01AM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 7:59PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 8:00PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 8:01PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '15-Aug-2016 11:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '16-Aug-2016 11:30PM EDT' ), $offset, $pay_period_dates, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowC
	 */
	function testInApplyFrequencyWindowC() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 10 ); //Each Pay Period
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 79200;

		$current_epoch = strtotime( '02-Jan-20 1:31 AM EST' ); //Was: 14-Aug-2016 1:30AM EDT
		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();

		$pay_period_dates = [ 'start_date' => strtotime( '16-Jan-2020 12:00AM' ), 'end_date' => strtotime( '01-Jan-2020 11:59:59PM' ) ];
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );


		TTDate::setTimeZone( 'US/Pacific', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$pay_period_dates = [ 'start_date' => strtotime( '16-Jan-2020 12:00AM' ), 'end_date' => strtotime( '01-Jan-2020 11:59:59PM' ) ];

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 11:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 11:59PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 8:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 8:59:58PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 8:59:59PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 9:00PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 9:01PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 9:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:59PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 11:00AM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 11:01AM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 6:59PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 7:00PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 7:01PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 10:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '04-Jan-2020 10:30PM PST' ), $offset, $pay_period_dates, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowESTA
	 */
	function testInApplyFrequencyWindowESTA() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 30 ); //Each Month
		$ap_obj->setApplyFrequencyDayOfMonth( 1 );
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 79200;

		$current_epoch = strtotime( '01-Jan-20 1:30 AM EST' );
		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, null, $user_obj ) );


		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowESTB
	 */
	function testInApplyFrequencyWindowESTB() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 30 ); //Each Month
		$ap_obj->setApplyFrequencyDayOfMonth( 1 );
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 86400;

		$current_epoch = strtotime( '01-Jan-20 1:30 AM EST' );
		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, null, $user_obj ) );


		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 10:30PM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 10:30PM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:30PM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 10:30PM EST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 1:30AM EST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 4:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 4:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 4:30AM EST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 4:30AM EST' ), $offset, null, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowPSTA
	 */
	function testInApplyFrequencyWindowPSTA() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 30 ); //Each Month
		$ap_obj->setApplyFrequencyDayOfMonth( 1 );
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 79200;

		$current_epoch = strtotime( '01-Jan-20 1:30 AM PST' );
		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, null, $user_obj ) );


		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 4:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 4:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 4:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 4:30AM PST' ), $offset, null, $user_obj ) );
	}

	/**
	 * @group AccrualPolicy_testInApplyFrequencyWindowPSTB
	 */
	function testInApplyFrequencyWindowPSTB() {
		global $config_vars;
		//Test InApplyFrequency when the system timezone is 'America/New_York', but some users are 'America/Denver'.
		$user_obj = $this->getUserObject( $this->user_id );

		$ap_obj = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $ap_obj */
		$ap_obj->setType( 20 );
		$ap_obj->setApplyFrequency( 30 ); //Each Month
		$ap_obj->setApplyFrequencyDayOfMonth( 1 );
		$ap_obj->setMilestoneRolloverHireDate( true );
		$ap_obj->setMinimumEmployedDays( 0 );


		$offset = 86400;

		$current_epoch = strtotime( '01-Jan-20 1:30 AM PST' );
		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$config_vars['other']['system_timezone'] = TTDate::getTimeZone();
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( $current_epoch, $offset, null, $user_obj ) );


		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 1:30AM PST' ), $offset, null, $user_obj ) );

		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '30-Dec-2019 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '31-Dec-2019 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( true, $ap_obj->inApplyFrequencyWindow( strtotime( '01-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '02-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
		$this->assertEquals( false, $ap_obj->inApplyFrequencyWindow( strtotime( '03-Jan-2020 10:30PM PST' ), $offset, null, $user_obj ) );
	}

	/**
	 * @group testHourAccrualWithRollOverOnHireDateA
	 */
	function testHourAccrualWithRollOverOnHireDateA() {
		//Make sure that a new hire who works on their hire date and accrues time, doesn't trigger a rollover and have their
		// accrued time reset to 0.
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( TTDate::incrementDate( time(), -1, 'day' ) ); //Set hire date to yesterday.
		if ( $user_obj->isValid() ) {
			$user_obj->Save( false );
		}

		$hire_date = $user_obj->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1001, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add punches on hire date.
		$date_epoch = TTDate::getBeginDayEpoch( $hire_date );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 6 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * 3600 ) );


		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime( '+1 months', $current_epoch ), 10 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 10 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualWeeklyEligibilityA
	 */
	function testHourAccrualWeeklyEligibilityA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1100, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 3 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 43 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 5, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 46 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 46 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualWeeklyEligibilityWithRetroA
	 */
	function testHourAccrualWeeklyEligibilityWithRetroA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1101, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 50 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) ), 5, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualPayPeriodEligibilityWithRetroA
	 */
	function testHourAccrualPayPeriodEligibilityWithRetroA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1102, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 1)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 50 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 5, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualPayPeriodEligibilityWithAbsenceAndRetroA
	 */
	function testHourAccrualPayPeriodEligibilityWithAbsenceAndRetroA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$policy_ids['pay_formula_policy'][910] = $this->createPayFormulaPolicy( $this->company_id, 910 ); //Bank Accrual

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
		$policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $policy_ids['pay_formula_policy'][910] ); //Bank

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][99] ); //All Time

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $policy_ids['pay_code'][910] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1102, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								$policy_ids['absence_policy'], //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 1)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 2, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.

		$dd->createAbsence( $this->user_id, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), ( 10 * 3600 ), $policy_ids['absence_policy'][10] ); //Add absence for one day so we know its handled properly as well.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 50 * 3600 ) );



		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 5, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 53 * 3600 ) );
	}

	/**
	 * @group AccrualPolicy_testHourAccrualPayPeriodEligibilityWithRetroB
	 */
	function testHourAccrualPayPeriodEligibilityWithRetroB() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1103, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 1)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 2, 'day' ), '8:00AM', '4:00PM' ); //Create 3 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 40 * 3600 ) ); //40hrs kicks in on the 5th day for exactly 40hrs.

		//
		//Start next week.
		//

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ); //Skip two days (weekend)
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), '8:00AM', '4:00PM' ); //Create 3 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 72 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 4, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 4:15PM' ), //Add a few extra minutes to make sure the maximum isn't exceeded within the same day.
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 80 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 1, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 80 * 3600 ) ); //This exceeds the maximum balance and therefore shouldn't increase anymore from the last day.
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualPayPeriodEligibilityWithRetroA
	 */
	function testCalendarAccrualPayPeriodEligibilityWithRetroA() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1200, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 2)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Calculate Calendar Accrual
		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::incrementDate( $date_epoch, -2, 'month' ), TTDate::incrementDate( $date_epoch, 2, 'month' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) ); //Minimum Eligibility has been not been exceeded, so nothing accrues.

	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualPayPeriodEligibilityWithRetroB
	 */
	function testCalendarAccrualPayPeriodEligibilityWithRetroB() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1200, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 2)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $date_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 2, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $date_epoch ), 3, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );


		//Calculate Calendar Accrual
		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::incrementDate( $date_epoch, -2, 'month' ), TTDate::incrementDate( $date_epoch, 2, 'month' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 100 * 60 ) ); //01:40hrs (100 mins)

	}


	/**
	 * @group AccrualPolicy_testCalendarAccrualPayPeriodEligibilityWithRetroC
	 */
	function testCalendarAccrualPayPeriodEligibilityWithRetroC() {
		global $dd;

		$policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular

		$policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $policy_ids['pay_code'][100] ] ); //Regular
		$policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $policy_ids['pay_code'] ); //All Time

		$policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

		$this->getUserObject( $this->user_id )->getHireDate();

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 1200, $accrual_policy_account_id, $policy_ids['contributing_shift_policy'][10] );
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								[ $accrual_policy_id ], //Accrual
								null, //Expense
								null, //Absence
								[ $policy_ids['regular'][10] ] //Regular
		);

		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $this->pay_period_objs[(count( $this->pay_period_objs ) - 2)]->getStartDate() ), 0, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		//Add batch of punches
		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $date_epoch ), 1, 'day' );
		$this->createPunches( $date_epoch, TTDate::incrementDate( TTDate::getMiddleDayEpoch( $date_epoch ), 3, 'day' ), '8:00AM', '6:00PM' ); //Create 10 days worth of punches.
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );


		$date_epoch = TTDate::incrementDate( TTDate::getBeginDayEpoch( $date_epoch ), 4, 'day' );
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );
		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) );


		//Calculate Calendar Accrual
		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::incrementDate( $date_epoch, -2, 'month' ), TTDate::incrementDate( $date_epoch, 2, 'month' ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, ( 0 * 3600 ) ); //Maximum Eligibility has been exceeded, so nothing accrues.
	}

}

?>