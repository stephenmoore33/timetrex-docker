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

class USStateUnemploymentReportTest extends PHPUnit\Framework\TestCase {
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

		//Skip setup for all testEFile* tests, as they don't need any of this data.
		if ( strpos( $this->getName(), 'testEFile' ) === false ) {
			$dd = new DemoData();
			$dd->setEnableQuickPunch( false );                     //Helps prevent duplicate punch IDs and validation failures.
			$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
			$dd->setDate( TTDate::strtotime( '30-Dec-2020' ) );
			$dd->setRandomSeed( $dd->getDate() ); //Force the random seed to always be the same, even if the UserNamePostFix is different.

			$this->company_id = $dd->createCompany();
			$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
			Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

			$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

			//Permissions are required so the user has permissions to run reports.
			$dd->createPermissionGroups( $this->company_id, 40 );  //Administrator only.

			$dd->createPayStubAccount( $this->company_id );
			$dd->createPayStubAccountLink( $this->company_id );
			$this->getPayStubAccountLinkArray();

			$dd->createUserWageGroups( $this->company_id );

			$dd->createPayrollRemittanceAgency( $this->company_id, null, $this->legal_entity_id ); //Must go before createCompanyDeduction()

			//Company Deductions
			$dd->createCompanyDeduction( $this->company_id, null, $this->legal_entity_id );

			//Create multiple state tax/deductions.
			$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
			$sp->setCompany( $this->company_id );
			$sp->setUser( null );
			$sp->PayStubAccounts( 'US', 'CA' );
			$sp->PayrollRemittanceAgencys( 'US', 'CA', null, null, $this->legal_entity_id );
			$sp->CompanyDeductions( 'US', 'CA', null, null, $this->legal_entity_id );

			//Need to define the California State Unemployment Percent.
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndName( $this->company_id, 'CA - Unemployment Insurance - Employer' );
			if ( $cdlf->getRecordCount() > 0 ) {
				$cd_obj = $cdlf->getCurrent();
				$cd_obj->setUserValue1( 0.047 ); //Percent.
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			} else {
				$this->assertTrue( false, 'CA - Unemployment Insurance failed to be created.' );
			}

			//Need to define the California State Unemployment Percent.
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndName( $this->company_id, 'NY - Unemployment Insurance - Employer' );
			if ( $cdlf->getRecordCount() > 0 ) {
				$cd_obj = $cdlf->getCurrent();
				$cd_obj->setUserValue1( 0.056 ); //Percent.
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			} else {
				$this->assertTrue( false, 'NY - Unemployment Insurance failed to be created.' );
			}


			$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 10 ); // Check
			$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 20 ); // US - EFT
			$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 30 ); // CA - EFT

			//createUser() also handles remittance destination accounts.
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 11, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 12, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 13, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 14, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 15, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 16, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 17, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 18, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 19, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') );
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 20, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') ); //Different State
			$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 21, null, null, null, null, null, null, null, $remittance_source_account_ids, null, null, null, strtotime('01-Jan-2010') ); //Different State


			//Get User Object.
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $this->user_id[0] )->getCurrent();
			$this->user_obj->setWorkEmail( 'demoadmin@abc-company.com' ); //Force a consistent/stable email address.
			if ( $this->user_obj->isValid() ) {
				$this->user_obj->Save( false );
			}

			$this->createPayPeriodSchedule();
			$this->createPayPeriods();
			$this->getAllPayPeriods();

			$dd->createTaxForms( $this->company_id, $this->user_id[0] );

			$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
			$this->assertGreaterThan( 0, $this->user_id[0] );
		}
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = [
				'total_gross'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross' ),
				'total_deductions'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions' ),
				'employer_contribution'    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Employer Total Contributions' ),
				'net_pay'                  => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Net Pay' ),
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		return true;
	}

	function createPayPeriodSchedule() {
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
		$ppsf->setMaximumShiftTime( ( 16 * 3600 ) );

		$ppsf->setEnableInitialPayPeriods( false );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save( false );
			Debug::Text( 'Pay Period Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$ppsf->setUser( $this->user_id );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayPeriods() {
		$max_pay_periods = 28;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getEndDayEpoch( strtotime( '23-Dec-2018' ) );
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

	function getPayStubEntryArray( $pay_stub_id ) {
		//Check Pay Stub to make sure it was created correctly.
		$pself = new PayStubEntryListFactory();
		$pself->getByPayStubId( $pay_stub_id );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $pse_obj ) {
				$ps_entry_arr[$pse_obj->getPayStubEntryNameId()][] = [
						'rate'       => $pse_obj->getRate(),
						'units'      => $pse_obj->getUnits(),
						'amount'     => $pse_obj->getAmount(),
						'ytd_amount' => $pse_obj->getYTDAmount(),
				];
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return false;
	}

	function createPayStubAmendment( $pay_stub_entry_name_id, $amount, $effective_date, $user_id ) {
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $user_id );
		$psaf->setPayStubEntryNameId( $pay_stub_entry_name_id ); //CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus')
		$psaf->setStatus( 50 );                                  //Active

		$psaf->setType( 10 );
//		$psaf->setRate( 10 );
//		$psaf->setUnits( 10 );
		$psaf->setAmount( $amount );

		$psaf->setEffectiveDate( $effective_date );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		} else {
			Debug::text( ' ERROR: Pay Stub Amendment Failed!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	function createPayStub( $user_id ) {
		for ( $i = 0; $i <= 26; $i++ ) { //Calculate pay stubs for each pay period.
			$cps = new CalculatePayStub();
			$cps->setUser( $user_id );
			$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
			$cps->calculate();
		}

		return true;
	}

	/**
	 * @group USStateUnemploymentReport_testEFileFederalA
	 */
	function testEFileFederalA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'TX';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = null; //Null for default ICESA format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '000'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'TX',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileFederalMultiEmployeeA
	 */
	function testEFileFederalMultiEmployeeA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'TX';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = null; //Null for default ICESA format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '000'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'TX',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6799',
				'address1' => '3322 CARRINGTON ST',
				'address2' => 'UNIT 827',
				'city'     => 'SEATTLE',
				'state'    => 'MS',
				'zip_code' => '12572',

				'first_name'  => 'JANE',
				'middle_name' => 'N',
				'last_name'   => 'SMITH',

				'hire_date'           => strtotime('02-Jan-2018'),
				'termination_date'    => strtotime('02-Feb-2022'),

				'user_wage_wage'         => '20.51',
				'user_wage_hourly_rate'  => '20.52',
				'subject_rate'           => '20.53',

				'pay_period_taxable_wages_weeks' => 15,
				'pay_period_tax_withheld_weeks'  => 14,
				'pay_period_weeks'               => 13,

				'subject_units'       => 122,
				'subject_wages' 	  => 4000.50,
				'taxable_wages' 	  => 3000.49,
				'excess_wages'  	  => 1000.48,
				'tax_withheld'  	  => 41.58,

				'paid_12th_day_month1' => 0,
				'paid_12th_day_month2' => 1,
				'paid_12th_day_month3' => 0,
		];
		$state_ui->addRecord( $ee_data );

		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileAL
	 */
	function testEFileAL() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'AL';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'AL';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'AL',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileAR
	 */
	function testEFileAR() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'AR';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'AR';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'AR',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'AR',
				'zip_code' => '00572',

				'first_name'  => 'JANE',
				'middle_name' => 'R',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2018'),
				'termination_date'    => strtotime('01-Feb-2021'),

				'user_wage_wage'         => '22.51',
				'user_wage_hourly_rate'  => '22.52',
				'subject_rate'           => '22.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 124,
				'subject_wages' 	  => 4001.51,
				'taxable_wages' 	  => 3001.50,
				'excess_wages'  	  => 1001.49,
				'tax_withheld'  	  => 42.59,

				'paid_12th_day_month1' => 0,
				'paid_12th_day_month2' => 1,
				'paid_12th_day_month3' => 0,
		];
		$state_ui->addRecord( $ee_data );

		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileAZ
	 */
	function testEFileAZ() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'AZ';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'AZ';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'AZ',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileCA
	 */
	function testEFileCA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'CA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'CA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'CA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'state_income_tax_subject_units' => 0,
				'state_income_tax_subject_wages' => 4001.52,
				'state_income_tax_taxable_wages' => 3001.51,
				'state_income_tax_excess_wages'  => 1001.50,
				'state_income_tax_tax_withheld'  => 42.60,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,

				'branch_code' => '090',
				'wage_plan_code' => 'Z',
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileCO
	 */
	function testEFileCO() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'CO';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'CO';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'CO',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'state_income_tax_subject_units' => 0,
				'state_income_tax_subject_wages' => 4001.52,
				'state_income_tax_taxable_wages' => 3001.51,
				'state_income_tax_excess_wages'  => 1001.50,
				'state_income_tax_tax_withheld'  => 42.60,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,

				'branch_code' => '090',
				'is_seasonal' => true,
		];
		$state_ui->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6799',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'CO',
				'zip_code' => '00572',

				'first_name'  => 'JANE',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'state_income_tax_subject_units' => 0,
				'state_income_tax_subject_wages' => 4001.52,
				'state_income_tax_taxable_wages' => 3001.51,
				'state_income_tax_excess_wages'  => 1001.50,
				'state_income_tax_tax_withheld'  => 42.60,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,

				'branch_code' => '101',
				'is_seasonal' => false,
		];
		$state_ui->addRecord( $ee_data );

		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileCT
	 */
	function testEFileCT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'CT';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'CT';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'CT',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileKS
	 */
	function testEFileKS() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'KS';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'KS';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'KS',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileKY
	 */
	function testEFileKY() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'KY';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'KY';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'KY',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileFL
	 */
	function testEFileFL() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'FL';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'FL';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'FL',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileGA
	 */
	function testEFileGA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'GA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'GA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'GA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileLA
	 */
	function testEFileLA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'LA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'LA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->is_multiple_county_industry = 1;
		$state_ui->is_multiple_worksite_location = 0;
		$state_ui->is_multiple_worksite_indicator = 1;

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'LA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );

		$ee_data = [ //0 Subject wages and Tax Withheld, entire record should be skipped.
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'LA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 0,
				'subject_wages' 	  => 0,
				'taxable_wages' 	  => 0,
				'excess_wages'  	  => 0,
				'tax_withheld'  	  => 0,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );

		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileIA
	 */
	function testEFileIA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'IA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'IA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'IA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'reporting_unit_number' => 123,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileIL
	 */
	function testEFileIL() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'IL';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'IL';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'IL',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',
				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileIN
	 */
	function testEFileIN() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'IN';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'IN';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'IN',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,

				'occupation_classification_code' => '123456',
				'designation' => 'PT',
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileMI
	 */
	function testEFileMI() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'MI';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'MI'; //MS=MMREF format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'MI',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileMN
	 */
	function testEFileMN() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'MN';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'MN'; //MN=Custom ICESA format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'MN',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileMS
	 */
	function testEFileMS() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'MS';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'MS'; //MS=MMREF format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'MS',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileMT
	 */
	function testEFileMT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'MT';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'MT';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'MT',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileNC
	 */
	function testEFileNC() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'NC';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'NC';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'NC',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileNE
	 */
	function testEFileNE() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'NE';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'NE';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'NE',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileNY
	 */
	function testEFileNY() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'NY';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'NY';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'NY',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileOK
	 */
	function testEFileOK() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'OK';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'OK';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'OK',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileTN
	 */
	function testEFileTN() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'TN';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'TN'; //MS=MMREF format.
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'TN',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileTX
	 */
	function testEFileTX() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'TX';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'TX';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'TX',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileVA
	 */
	function testEFileVA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'VA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'VA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'VA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileWA
	 */
	function testEFileWA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'WA';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'WA';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'WA',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group USStateUnemploymentReport_testEFileWI
	 */
	function testEFileWI() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$state_ui = $gf->getFormObject( 'state_ui', 'US' );
		$state_ui->setDebug( false );
		$state_ui->setShowBackground( false );

		$state_ui->month_of_year = 3;
		$state_ui->quarter_of_year = 1;
		$state_ui->year = 2022;

		$state_ui->name = 'ACME USA EAST';
		$state_ui->trade_name = 'ACME CO';
		$state_ui->company_address1 = '123 MAIN ST';
		$state_ui->company_city = 'AUSTIN';
		$state_ui->company_state = 'WI';
		$state_ui->company_zip_code = '12345';

		$state_ui->ein = '92-9356262';
		$state_ui->efile_state = 'WI';
		$state_ui->state_primary_id = 999999999;
		$state_ui->state_secondary_id = '';
		$state_ui->state_tertiary_id = '';
		$state_ui->efile_agency_id = '';

		$state_ui->contact_name = 'MR ADMINISTRATOR';
		$state_ui->contact_phone = '555-555-5555';
		$state_ui->contact_phone_ext = '';
		$state_ui->contact_fax = '444-444-4444';
		$state_ui->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$state_ui->tax_rate = '2.8'; //2.8%
		$state_ui->county_code = '001'; //TODO: Texas requires this based on the county with the most number of employees. This probably needs to be manaully defined from the Remittance Agency record?

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'AUSTIN',
				'state'    => 'WI',
				'zip_code' => '00572',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'hire_date'           => strtotime('01-Jan-2019'),
				'termination_date'    => strtotime('01-Feb-2022'),

				'user_wage_wage'         => '21.51',
				'user_wage_hourly_rate'  => '21.52',
				'subject_rate'           => '21.53',

				'pay_period_taxable_wages_weeks' => 16,
				'pay_period_tax_withheld_weeks'  => 15,
				'pay_period_weeks'               => 14,

				'subject_units'       => 123,
				'subject_wages' 	  => 4000.51,
				'taxable_wages' 	  => 3000.50,
				'excess_wages'  	  => 1000.49,
				'tax_withheld'  	  => 41.59,

				'paid_12th_day_month1' => 1,
				'paid_12th_day_month2' => 0,
				'paid_12th_day_month3' => 1,
		];
		$state_ui->addRecord( $ee_data );
		$gf->addForm( $state_ui );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}



}

?>
