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

class Form941ReportTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $user_id2 = null;
	protected $currency_id = null;
	protected $user_obj = null;
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

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only - *NOTE* //Permissions are required so the user has permissions to run reports.

		$dd->createPayStubAccount( $this->company_id );
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createPayrollRemittanceAgency( $this->company_id, $this->user_id, $this->legal_entity_id ); //Must go before createCompanyDeduction()

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id, $this->user_id, $this->legal_entity_id );

		$dd->createUserWageGroups( $this->company_id );

		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 10 ); // Check
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 20 ); // US - EFT
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 30 ); // CA - EFT

		//createUser() also handles remittance destination accounts.
		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id2 = $dd->createUser( $this->company_id, $this->legal_entity_id, 10, null, null, null, null, null, null, null, $remittance_source_account_ids );

		//Get User Object.
		$ulf = new UserListFactory();
		$this->user_obj = $ulf->getById( $this->user_id )->getCurrent();

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$dd->createTaxForms( $this->company_id, $this->user_id );

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
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

			$ppsf->setUser( [ $this->user_id ] );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayPeriods() {
		$max_pay_periods = 28; //Just over one year.

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginYearEpoch( strtotime( '01-Jan-2019' ) );
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

	function createPayStubAmendment( $pay_stub_entry_name_id, $amount, $effective_date, $user_ids = null ) {
		if ( $user_ids == null ) {
			$user_ids = [ $this->user_id ];
		}

		if ( !is_array( $user_ids ) ) {
			$user_ids = [ $user_ids ];
		}

		foreach( $user_ids as $user_id ) {
			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $user_id );
			$psaf->setPayStubEntryNameId( $pay_stub_entry_name_id ); //CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus')
			$psaf->setStatus( 50 );                                  //Active

			$psaf->setType( 10 );
			$psaf->setAmount( $amount );

			$psaf->setEffectiveDate( $effective_date );

			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			} else {
				Debug::text( ' ERROR: Pay Stub Amendment Failed! User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return true;
	}

	function createPayStub( $max = 12, $user_ids = null ) {
		if ( $user_ids == null ) {
			$user_ids = [ $this->user_id ];
		}

		if ( !is_array( $user_ids ) ) {
			$user_ids = [ $user_ids ];
		}

		foreach( $user_ids as $user_id ) {
			for ( $i = 0; $i <= $max; $i++ ) { //Calculate pay stubs for each pay period.
				$cps = new CalculatePayStub();
				$cps->setUser( $user_id );
				$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
				$cps->calculate();
			}
		}

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositA
	 */
	function testMonthlyDepositA() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.22, $report_output[2]['wages'] );
		$this->assertEquals( 12006.43, $report_output[2]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 18129.67, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 100053.20, $report_output[3]['wages'] );
		$this->assertEquals( 30016.13, $report_output[3]['income_tax'] );
		$this->assertEquals( 12406.60, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 2901.50, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 45324.23, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertTrue( true, property_exists( $form_objs, 'objs') );
		//$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertTrue( true, property_exists( $form_objs->objs[0], 'data') );
		//$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 100053.20, $form_objs->objs[0]->l2 );
		$this->assertEquals( 30016.13, $form_objs->objs[0]->l3 );
		$this->assertEquals( 100001.60, $form_objs->objs[0]->l5a );
		$this->assertEquals( 51.60, $form_objs->objs[0]->l5b );
		$this->assertEquals( 100053.20, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 15308.10, $form_objs->objs[0]->l7z );
		$this->assertEquals( 15308.10, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 9064.86, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 18129.70, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 18129.67, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 45324.23, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 12400.20, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 6.40, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 2901.54, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 15308.14, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 45324.27, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.04, $form_objs->objs[0]->l7 );
		$this->assertEquals( 45324.23, $form_objs->objs[0]->l10 );
		$this->assertEquals( 45324.23, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'April', $report_output[0]['date_month'] );
		$this->assertEquals( 40021.14, $report_output[0]['wages'] );
		$this->assertEquals( 12006.40, $report_output[0]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 17240.00, $report_output[0]['total_tax'] );

		$this->assertEquals( 'May', $report_output[1]['date_month'] );
		$this->assertEquals( 60031.62, $report_output[1]['wages'] );
		$this->assertEquals( 18009.57, $report_output[1]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1740.90, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.95, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 19751.42, $report_output[1]['total_tax'] );

		$this->assertEquals( 'June', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.08, $report_output[2]['wages'] );
		$this->assertEquals( 12006.38, $report_output[2]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 360.18, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 13527.16, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 140073.84, $report_output[3]['wages'] );
		$this->assertEquals( 42022.35, $report_output[3]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 4062.10, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 361.13, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 50518.58, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 140073.84, $form_objs->objs[0]->l2 );
		$this->assertEquals( 42022.35, $form_objs->objs[0]->l3 );
		$this->assertEquals( 32826.23, $form_objs->objs[0]->l5a );
		$this->assertEquals( 20.57, $form_objs->objs[0]->l5b );
		$this->assertEquals( 140073.84, $form_objs->objs[0]->l5c );
		$this->assertEquals( 40127.04, $form_objs->objs[0]->l5d );
		$this->assertEquals( 8496.23, $form_objs->objs[0]->l7z );
		$this->assertEquals( 8496.23, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 17240.00, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 19751.42, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 13527.16, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 50518.58, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 4070.45, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 2.55, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 4062.14, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 361.14, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 8496.28, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 50518.63, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.05, $form_objs->objs[0]->l7 );
		$this->assertEquals( 50518.58, $form_objs->objs[0]->l10 );
		$this->assertEquals( 50518.58, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Jun-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.22, $report_output[2]['wages'] );
		$this->assertEquals( 12006.43, $report_output[2]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 18129.67, $report_output[2]['total_tax'] );

		$this->assertEquals( 'April', $report_output[3]['date_month'] );
		$this->assertEquals( 40021.14, $report_output[3]['wages'] );
		$this->assertEquals( 12006.40, $report_output[3]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 17240.00, $report_output[3]['total_tax'] );

		$this->assertEquals( 'May', $report_output[4]['date_month'] );
		$this->assertEquals( 60031.62, $report_output[4]['wages'] );
		$this->assertEquals( 18009.57, $report_output[4]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[4]['social_security_tax_total'] );
		$this->assertEquals( 1740.90, $report_output[4]['medicare_tax_total'] );
		$this->assertEquals( 0.95, $report_output[4]['additional_medicare_tax'] );
		$this->assertEquals( 19751.42, $report_output[4]['total_tax'] );

		$this->assertEquals( 'June', $report_output[5]['date_month'] );
		$this->assertEquals( 40021.08, $report_output[5]['wages'] );
		$this->assertEquals( 12006.38, $report_output[5]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[5]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[5]['medicare_tax_total'] );
		$this->assertEquals( 360.18, $report_output[5]['additional_medicare_tax'] );
		$this->assertEquals( 13527.16, $report_output[5]['total_tax'] );

		//Total
		$this->assertEquals( 240127.04, $report_output[6]['wages'] );
		$this->assertEquals( 72038.48, $report_output[6]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[6]['social_security_tax_total'] );
		$this->assertEquals( 6963.60, $report_output[6]['medicare_tax_total'] );
		$this->assertEquals( 361.13, $report_output[6]['additional_medicare_tax'] );
		$this->assertEquals( 95842.81, $report_output[6]['total_tax'] );

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositB
	 */
	function testMonthlyDepositB() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//3rd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ) );

		//4th Quarter
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ) );

		$this->createPayStub( 19 );

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 9221.40, $report_output[0]['wages'] );
		$this->assertEquals( 2222.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 1143.46, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 267.42, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 3633.12, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 46107.00, $report_output[3]['wages'] );
		$this->assertEquals( 11111.20, $report_output[3]['income_tax'] );
		$this->assertEquals( 5717.30, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1337.10, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 18165.60, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 46107.00, $form_objs->objs[0]->l2 );
		$this->assertEquals( 11111.20, $form_objs->objs[0]->l3 );
		$this->assertEquals( 46107.00, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 46107.00, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 7054.40, $form_objs->objs[0]->l7z );
		$this->assertEquals( 7054.40, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 3633.12, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 5717.27, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1337.10, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 7054.37, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 18165.57, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.03, $form_objs->objs[0]->l7 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l10 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'April', $report_output[0]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[0]['wages'] );
		$this->assertEquals( 4444.48, $report_output[0]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[0]['total_tax'] );

		$this->assertEquals( 'May', $report_output[1]['date_month'] );
		$this->assertEquals( 27664.20, $report_output[1]['wages'] );
		$this->assertEquals( 6666.72, $report_output[1]['income_tax'] );
		$this->assertEquals( 3430.38, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 802.26, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 10899.36, $report_output[1]['total_tax'] );

		$this->assertEquals( 'June', $report_output[2]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 64549.80, $report_output[3]['wages'] );
		$this->assertEquals( 15555.68, $report_output[3]['income_tax'] );
		$this->assertEquals( 8004.22, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1871.94, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 25431.84, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 64549.80, $form_objs->objs[0]->l2 );
		$this->assertEquals( 15555.68, $form_objs->objs[0]->l3 );
		$this->assertEquals( 64549.80, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 64549.80, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 9876.16, $form_objs->objs[0]->l7z );
		$this->assertEquals( 9876.16, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 10899.36, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 8004.18, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1871.94, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 9876.12, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 25431.80, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.04, $form_objs->objs[0]->l7 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l10 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 3rd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'July', $report_output[0]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[0]['wages'] );
		$this->assertEquals( 4444.48, $report_output[0]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[0]['total_tax'] );

		$this->assertEquals( 'August', $report_output[1]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 471.16, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 5450.48, $report_output[1]['total_tax'] );

		$this->assertEquals( 'September', $report_output[2]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 4979.32, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 55328.40, $report_output[3]['wages'] );
		$this->assertEquals( 13333.44, $report_output[3]['income_tax'] );
		$this->assertEquals( 2758.08, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1604.52, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 17696.04, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 55328.40, $form_objs->objs[0]->l2 );
		$this->assertEquals( 13333.44, $form_objs->objs[0]->l3 );
		$this->assertEquals( 22243.20, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 55328.40, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 4362.60, $form_objs->objs[0]->l7z );
		$this->assertEquals( 4362.60, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 5450.48, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 4979.32, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 2758.16, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1604.52, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 4362.68, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 17696.12, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.08, $form_objs->objs[0]->l7 ); //Since the user reached the social security maximum contribution, we have to back out the fractions of the cent from previous quarters, at least to within 0.01.
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l10 );
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Sep-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 9221.40, $report_output[0]['wages'] );
		$this->assertEquals( 2222.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 1143.46, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 267.42, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 3633.12, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		$this->assertEquals( 'April', $report_output[3]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[3]['wages'] );
		$this->assertEquals( 4444.48, $report_output[3]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[3]['total_tax'] );

		$this->assertEquals( 'May', $report_output[4]['date_month'] );
		$this->assertEquals( 27664.20, $report_output[4]['wages'] );
		$this->assertEquals( 6666.72, $report_output[4]['income_tax'] );
		$this->assertEquals( 3430.38, $report_output[4]['social_security_tax_total'] );
		$this->assertEquals( 802.26, $report_output[4]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[4]['additional_medicare_tax'] );
		$this->assertEquals( 10899.36, $report_output[4]['total_tax'] );

		$this->assertEquals( 'June', $report_output[5]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[5]['wages'] );
		$this->assertEquals( 4444.48, $report_output[5]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[5]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[5]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[5]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[5]['total_tax'] );

		$this->assertEquals( 'July', $report_output[6]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[6]['wages'] );
		$this->assertEquals( 4444.48, $report_output[6]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[6]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[6]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[6]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[6]['total_tax'] );

		$this->assertEquals( 'August', $report_output[7]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[7]['wages'] );
		$this->assertEquals( 4444.48, $report_output[7]['income_tax'] );
		$this->assertEquals( 471.16, $report_output[7]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[7]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[7]['additional_medicare_tax'] );
		$this->assertEquals( 5450.48, $report_output[7]['total_tax'] );

		$this->assertEquals( 'September', $report_output[8]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[8]['wages'] );
		$this->assertEquals( 4444.48, $report_output[8]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[8]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[8]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[8]['additional_medicare_tax'] );
		$this->assertEquals( 4979.32, $report_output[8]['total_tax'] );

		//Total
		$this->assertEquals( 165985.20, $report_output[9]['wages'] );
		$this->assertEquals( 40000.32, $report_output[9]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[9]['social_security_tax_total'] );
		$this->assertEquals( 4813.56, $report_output[9]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[9]['additional_medicare_tax'] );
		$this->assertEquals( 61293.48, $report_output[9]['total_tax'] );

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositWithExemptEmployee
	 */
	function testMonthlyDepositWithExemptEmployee() {
		$udlf = new UserDeductionListFactory(); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndUserId( $this->company_id, $this->user_id2 );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach ( $udlf as $ud_obj ) {
				if ( in_array( $ud_obj->getCompanyDeductionObject()->getCalculation(), [ 82, 83, 84, 85, 100 ] ) ) {
					$ud_obj->setDeleted( true );
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
			}
		}

		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );

		//2nd Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );

		//3rd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );

		//4th Quarter
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), [ $this->user_id, $this->user_id2 ]  );

		$this->createPayStub( 19, [ $this->user_id, $this->user_id2 ]  );

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[0]['wages'] );
		$this->assertEquals( 2222.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 1143.46, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 267.42, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 3633.12, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 92214.00, $report_output[3]['wages'] );
		$this->assertEquals( 11111.20, $report_output[3]['income_tax'] );
		$this->assertEquals( 5717.30, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1337.10, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 18165.60, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 92214.00, $form_objs->objs[0]->l2 );
		$this->assertEquals( 11111.20, $form_objs->objs[0]->l3 );
		$this->assertEquals( 46107.00, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 46107.00, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 7054.40, $form_objs->objs[0]->l7z );
		$this->assertEquals( 7054.40, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 3633.12, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 5717.27, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1337.10, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 7054.37, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 18165.57, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.03, $form_objs->objs[0]->l7 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l10 );
		$this->assertEquals( 18165.60, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'April', $report_output[0]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[0]['wages'] );
		$this->assertEquals( 4444.48, $report_output[0]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[0]['total_tax'] );

		$this->assertEquals( 'May', $report_output[1]['date_month'] );
		$this->assertEquals( 55328.40, $report_output[1]['wages'] );
		$this->assertEquals( 6666.72, $report_output[1]['income_tax'] );
		$this->assertEquals( 3430.38, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 802.26, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 10899.36, $report_output[1]['total_tax'] );

		$this->assertEquals( 'June', $report_output[2]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 129099.60, $report_output[3]['wages'] );
		$this->assertEquals( 15555.68, $report_output[3]['income_tax'] );
		$this->assertEquals( 8004.22, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1871.94, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 25431.84, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 129099.60, $form_objs->objs[0]->l2 );
		$this->assertEquals( 15555.68, $form_objs->objs[0]->l3 );
		$this->assertEquals( 64549.80, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 64549.80, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 9876.16, $form_objs->objs[0]->l7z );
		$this->assertEquals( 9876.16, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 10899.36, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 8004.18, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1871.94, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 9876.12, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 25431.80, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.04, $form_objs->objs[0]->l7 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l10 );
		$this->assertEquals( 25431.84, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 3rd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'July', $report_output[0]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[0]['wages'] );
		$this->assertEquals( 4444.48, $report_output[0]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[0]['total_tax'] );

		$this->assertEquals( 'August', $report_output[1]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 471.16, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 5450.48, $report_output[1]['total_tax'] );

		$this->assertEquals( 'September', $report_output[2]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 4979.32, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 110656.80, $report_output[3]['wages'] );
		$this->assertEquals( 13333.44, $report_output[3]['income_tax'] );
		$this->assertEquals( 2758.08, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1604.52, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 17696.04, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 110656.80, $form_objs->objs[0]->l2 );
		$this->assertEquals( 13333.44, $form_objs->objs[0]->l3 );
		$this->assertEquals( 22243.20, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 55328.40, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 4362.60, $form_objs->objs[0]->l7z );
		$this->assertEquals( 4362.60, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 7266.24, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( 5450.48, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( 4979.32, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 2758.16, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 1604.52, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 4362.68, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 17696.12, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.08, $form_objs->objs[0]->l7 ); //Since the user reached the social security maximum contribution, we have to back out the fractions of the cent from previous quarters, at least to within 0.01.
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l10 );
		$this->assertEquals( 17696.04, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Sep-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 18442.80, $report_output[0]['wages'] );
		$this->assertEquals( 2222.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 1143.46, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 267.42, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 3633.12, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[1]['wages'] );
		$this->assertEquals( 4444.48, $report_output[1]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[2]['wages'] );
		$this->assertEquals( 4444.48, $report_output[2]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[2]['total_tax'] );

		$this->assertEquals( 'April', $report_output[3]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[3]['wages'] );
		$this->assertEquals( 4444.48, $report_output[3]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[3]['total_tax'] );

		$this->assertEquals( 'May', $report_output[4]['date_month'] );
		$this->assertEquals( 55328.40, $report_output[4]['wages'] );
		$this->assertEquals( 6666.72, $report_output[4]['income_tax'] );
		$this->assertEquals( 3430.38, $report_output[4]['social_security_tax_total'] );
		$this->assertEquals( 802.26, $report_output[4]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[4]['additional_medicare_tax'] );
		$this->assertEquals( 10899.36, $report_output[4]['total_tax'] );

		$this->assertEquals( 'June', $report_output[5]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[5]['wages'] );
		$this->assertEquals( 4444.48, $report_output[5]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[5]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[5]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[5]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[5]['total_tax'] );

		$this->assertEquals( 'July', $report_output[6]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[6]['wages'] );
		$this->assertEquals( 4444.48, $report_output[6]['income_tax'] );
		$this->assertEquals( 2286.92, $report_output[6]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[6]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[6]['additional_medicare_tax'] );
		$this->assertEquals( 7266.24, $report_output[6]['total_tax'] );

		$this->assertEquals( 'August', $report_output[7]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[7]['wages'] );
		$this->assertEquals( 4444.48, $report_output[7]['income_tax'] );
		$this->assertEquals( 471.16, $report_output[7]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[7]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[7]['additional_medicare_tax'] );
		$this->assertEquals( 5450.48, $report_output[7]['total_tax'] );

		$this->assertEquals( 'September', $report_output[8]['date_month'] );
		$this->assertEquals( 36885.60, $report_output[8]['wages'] );
		$this->assertEquals( 4444.48, $report_output[8]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[8]['social_security_tax_total'] );
		$this->assertEquals( 534.84, $report_output[8]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[8]['additional_medicare_tax'] );
		$this->assertEquals( 4979.32, $report_output[8]['total_tax'] );

		//Total
		$this->assertEquals( 331970.40, $report_output[9]['wages'] );
		$this->assertEquals( 40000.32, $report_output[9]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[9]['social_security_tax_total'] );
		$this->assertEquals( 4813.56, $report_output[9]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[9]['additional_medicare_tax'] );
		$this->assertEquals( 61293.48, $report_output[9]['total_tax'] );

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositWithEmployeeDateFilters
	 */
	function testMonthlyDepositWithEmployeeDateFilters() {
		//Add Start/End Dates to Social Security and Medicare and Federal Taxes.
		$cdlf = new CompanyDeductionListFactory(); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyId( $this->company_id );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) {
				if ( in_array( $cd_obj->getCalculation(), [ 82, 83, 84, 85, 100 ] ) ) {
					$cd_obj->setStartDate( strtotime( '2000-01-01' ) );
					if ( $cd_obj->isValid() ) {
						$cd_obj->Save();
					}
				}
			}
		}

		$udlf = new UserDeductionListFactory(); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndUserId( $this->company_id, $this->user_id2 );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach ( $udlf as $ud_obj ) {
				if ( in_array( $ud_obj->getCompanyDeductionObject()->getCalculation(), [ 82, 83, 84, 85, 100 ] ) ) {
					$ud_obj->setStartDate( $this->pay_period_objs[6]->getStartDate() );
					$ud_obj->setEndDate( $this->pay_period_objs[10]->getEndDate() );
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
			}
		}

		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );

		//2nd Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );

		//3rd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );

		//4th Quarter
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), [ $this->user_id, $this->user_id2 ] );

		$this->createPayStub( 19, [ $this->user_id, $this->user_id2 ] );

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );

		//var_export($report_output);
		$this->assertEquals(
				[
						0 =>
								[
										'date_month'                => 'January',
										'wages'                     => '18442.80',
										'income_tax'                => '2222.24',
										'social_security_tax_total' => '1143.46',
										'medicare_tax_total'        => '267.42',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '3633.12',
								],
						1 =>
								[
										'date_month'                => 'February',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						2 =>
								[
										'date_month'                => 'March',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						3 =>
								[
										'date_month'                => 'Grand Total[3]:',
										'wages'                     => '92214.00',
										'income_tax'                => '11111.20',
										'social_security_tax_total' => '5717.30',
										'medicare_tax_total'        => '1337.10',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '18165.60',
										'_total'                    => true,
								],
				],
				$report_output
		);

		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs, 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0], 'data' ) );

		//var_export($form_objs->objs[0]->data);
		$this->assertEquals(
				[
						'year'               => '2019',
						'ein'                => '123456789',
						'name'               => 'ACME USA East Inc.',
						'trade_name'         => 'ACME USA East',
						'address'            => '123 Main St Unit #123',
						'city'               => 'New York',
						'state'              => 'NY',
						'zip_code'           => '12345',
						'quarter'            => 1.0,
						'l1'                 => '0',
						'l2'                 => '92214.000000',
						'l3'                 => '11111.200000',
						'l5a'                => '46107.000000',
						'l5b'                => '0.000000',
						'l5c'                => '46107.000000',
						'l5d'                => '0.000000',
						'l7z'                => '7054.4000000000',
						'l5_actual_deducted' => '7054.4000000000',
						'l8'                 => '0.000000',
						'l15b'               => true,
						'l16_month1'         => '3633.1200000000',
						'l16_month2'         => '7266.2400000000',
						'l16_month3'         => '7266.2400000000',
						'l5a2'               => '5717.27',
						'l5b2'               => '0.00',
						'l5c2'               => '1337.10',
						'l5d2'               => '0.00',
						'l5e'                => '7054.3700000000',
						'l4'                 => false,
						'l6'                 => '18165.5700000000',
						'l7'                 => '0.0300000000',
						'l10'                => '18165.6000000000',
						'l12'                => 18165.6,
						'l13'                => 18165.6,
						'l16_month_total'    => '18165.6000000000',
				],
				$form_objs->objs[0]->data
		);

		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );

		//var_export($report_output);
		$this->assertEquals(
				[
						0 =>
								[
										'date_month'                => 'April',
										'wages'                     => '36885.60',
										'income_tax'                => '8888.96',
										'social_security_tax_total' => '3430.38',
										'medicare_tax_total'        => '802.26',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '13121.60',
								],
						1 =>
								[
										'date_month'                => 'May',
										'wages'                     => '55328.40',
										'income_tax'                => '13333.44',
										'social_security_tax_total' => '6860.76',
										'medicare_tax_total'        => '1604.52',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '21798.72',
								],
						2 =>
								[
										'date_month'                => 'June',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '3430.38',
										'medicare_tax_total'        => '802.26',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '8677.12',
								],
						3 =>
								[
										'date_month'                => 'Grand Total[3]:',
										'wages'                     => '129099.60',
										'income_tax'                => '26666.88',
										'social_security_tax_total' => '13721.52',
										'medicare_tax_total'        => '3209.04',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '43597.44',
										'_total'                    => true,
								],
				],
				$report_output
		);


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs, 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0], 'data' ) );

		//var_export( $form_objs->objs[0]->data );
		$this->assertEquals(
				[
						'year'               => '2019',
						'ein'                => '123456789',
						'name'               => 'ACME USA East Inc.',
						'trade_name'         => 'ACME USA East',
						'address'            => '123 Main St Unit #123',
						'city'               => 'New York',
						'state'              => 'NY',
						'zip_code'           => '12345',
						'quarter'            => 2.0,
						'l1'                 => '0',
						'l2'                 => '129099.600000',
						'l3'                 => '26666.880000',
						'l5a'                => '110656.800000',
						'l5b'                => '0.000000',
						'l5c'                => '110656.800000',
						'l5d'                => '0.000000',
						'l7z'                => '16930.5600000000',
						'l5_actual_deducted' => '16930.5600000000',
						'l8'                 => '0.000000',
						'l15b'               => true,
						'l16_month1'         => '13121.6000000000',
						'l16_month2'         => '21798.7200000000',
						'l16_month3'         => '8677.1200000000',
						'l5a2'               => '13721.44',
						'l5b2'               => '0.00',
						'l5c2'               => '3209.05',
						'l5d2'               => '0.00',
						'l5e'                => '16930.4900000000',
						'l4'                 => false,
						'l6'                 => '43597.3700000000',
						'l7'                 => '0.0700000000',
						'l10'                => '43597.4400000000',
						'l12'                => 43597.44,
						'l13'                => 43597.44,
						'l16_month_total'    => '43597.4400000000',
				],
				$form_objs->objs[0]->data
		);

		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for 3rd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );

		//var_export($report_output);
		$this->assertEquals(
				[
						0 =>
								[
										'date_month'                => 'July',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						1 =>
								[
										'date_month'                => 'August',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '471.16',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '5450.48',
								],
						2 =>
								[
										'date_month'                => 'September',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '0.00',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '4979.32',
								],
						3 =>
								[
										'date_month'                => 'Grand Total[3]:',
										'wages'                     => '110656.80',
										'income_tax'                => '13333.44',
										'social_security_tax_total' => '2758.08',
										'medicare_tax_total'        => '1604.52',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '17696.04',
										'_total'                    => true,
								],
				],
				$report_output
		);


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs, 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0], 'data' ) );

		//var_export($form_objs->objs[0]->data);
		$this->assertEquals(
				[
					'year' => '2019',
					'ein' => '123456789',
					'name' => 'ACME USA East Inc.',
					'trade_name' => 'ACME USA East',
					'address' => '123 Main St Unit #123',
					'city' => 'New York',
					'state' => 'NY',
					'zip_code' => '12345',
					'quarter' => 3.0,
					'l1' => '0',
					'l2' => '110656.800000',
					'l3' => '13333.440000',
					'l5a' => '22243.200000',
					'l5b' => '0.000000',
					'l5c' => '55328.400000',
					'l5d' => '0.000000',
					'l7z' => '4362.6000000000',
					'l5_actual_deducted' => '4362.6000000000',
					'l8' => '0.000000',
					'l15b' => true,
					'l16_month1' => '7266.2400000000',
					'l16_month2' => '5450.4800000000',
					'l16_month3' => '4979.3200000000',
					'l5a2' => '2758.16',
					'l5b2' => '0.00',
					'l5c2' => '1604.52',
					'l5d2' => '0.00',
					'l5e' => '4362.6800000000',
					'l4' => false,
					'l6' => '17696.1200000000',
					'l7' => '-0.0800000000',
					'l10' => '17696.0400000000',
					'l12' => 17696.04,
					'l13' => 17696.04,
					'l16_month_total' => '17696.0400000000',
				], $form_objs->objs[0]->data
		);

		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );
		$this->assertEquals( -0.08, $form_objs->objs[0]->l7 ); //Since the user reached the social security maximum contribution, we have to back out the fractions of the cent from previous quarters, at least to within 0.01.
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Sep-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );

		//var_export( $report_output );
		$this->assertEquals(
				[
						0 =>
								[
										'date_month'                => 'January',
										'wages'                     => '18442.80',
										'income_tax'                => '2222.24',
										'social_security_tax_total' => '1143.46',
										'medicare_tax_total'        => '267.42',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '3633.12',
								],
						1 =>
								[
										'date_month'                => 'February',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						2 =>
								[
										'date_month'                => 'March',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						3 =>
								[
										'date_month'                => 'April',
										'wages'                     => '36885.60',
										'income_tax'                => '8888.96',
										'social_security_tax_total' => '3430.38',
										'medicare_tax_total'        => '802.26',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '13121.60',
								],
						4 =>
								[
										'date_month'                => 'May',
										'wages'                     => '55328.40',
										'income_tax'                => '13333.44',
										'social_security_tax_total' => '6860.76',
										'medicare_tax_total'        => '1604.52',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '21798.72',
								],
						5 =>
								[
										'date_month'                => 'June',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '3430.38',
										'medicare_tax_total'        => '802.26',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '8677.12',
								],
						6 =>
								[
										'date_month'                => 'July',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '2286.92',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '7266.24',
								],
						7 =>
								[
										'date_month'                => 'August',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '471.16',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '5450.48',
								],
						8 =>
								[
										'date_month'                => 'September',
										'wages'                     => '36885.60',
										'income_tax'                => '4444.48',
										'social_security_tax_total' => '0.00',
										'medicare_tax_total'        => '534.84',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '4979.32',
								],
						9 =>
								[
										'date_month'                => 'Grand Total[9]:',
										'wages'                     => '331970.40',
										'income_tax'                => '51111.52',
										'social_security_tax_total' => '22196.90',
										'medicare_tax_total'        => '6150.66',
										'additional_medicare_tax'   => '0.00',
										'total_tax'                 => '79459.08',
										'_total'                    => true,
								],
				],
				$report_output
		);

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositLargePayPeriod
	 */
	function testMonthlyDepositLargePayPeriod() {
		//1st Quarter - Exceed all limits in first pay period
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 250000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 200.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );

		//Skip a month, then a small pay period.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 250200.68, $report_output[0]['wages'] );
		$this->assertEquals( 91173.54, $report_output[0]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 7255.82, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 451.81, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 115360.77, $report_output[0]['total_tax'] );

		//February is blank - Skipped

		$this->assertEquals( 'March', $report_output[1]['date_month'] );
		$this->assertEquals( 10010.68, $report_output[1]['wages'] );
		$this->assertEquals( 2498.49, $report_output[1]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 290.30, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 90.10, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 2878.89, $report_output[1]['total_tax'] );

		//Total
		$this->assertEquals( 260211.36, $report_output[2]['wages'] );
		$this->assertEquals( 93672.03, $report_output[2]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 7546.12, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 541.91, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 118239.66, $report_output[2]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 260211.36, $form_objs->objs[0]->l2 );
		$this->assertEquals( 93672.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 132699.66, $form_objs->objs[0]->l5a );
		$this->assertEquals( 200.34, $form_objs->objs[0]->l5b );
		$this->assertEquals( 260211.36, $form_objs->objs[0]->l5c );
		$this->assertEquals( 60211.36, $form_objs->objs[0]->l5d );
		$this->assertEquals( 24567.63, $form_objs->objs[0]->l7z );
		$this->assertEquals( 24567.63, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( 115360.77, $form_objs->objs[0]->l16_month1 );
		//$this->assertEquals( $form_objs->objs[0]->l16_month2, 0.00 );
		$this->assertEquals( 2878.89, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 118239.66, $form_objs->objs[0]->l16_month_total );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 16454.76, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 24.84, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 7546.13, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 541.90, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 24567.63, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 118239.66, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 118239.66, $form_objs->objs[0]->l10 );
		$this->assertEquals( 118239.66, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );

		return true;
	}

	/**
	 * @group Form941Report_testSemiWeeklyDeposit
	 */
	function testSemiWeeklyDeposit() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['deposit_schedule'] = 20; //Semi-Weekly
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.22, $report_output[2]['wages'] );
		$this->assertEquals( 12006.43, $report_output[2]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 18129.67, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 100053.20, $report_output[3]['wages'] );
		$this->assertEquals( 30016.13, $report_output[3]['income_tax'] );
		$this->assertEquals( 12406.60, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 2901.50, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 45324.23, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 100053.20, $form_objs->objs[0]->l2 );
		$this->assertEquals( 30016.13, $form_objs->objs[0]->l3 );
		$this->assertEquals( 100001.60, $form_objs->objs[0]->l5a );
		$this->assertEquals( 51.60, $form_objs->objs[0]->l5b );
		$this->assertEquals( 100053.20, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 15308.10, $form_objs->objs[0]->l7z );
		$this->assertEquals( 15308.10, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16_month_total );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 12400.20, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 6.40, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 2901.54, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 15308.14, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 45324.27, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.04, $form_objs->objs[0]->l7 );
		$this->assertEquals( 45324.23, $form_objs->objs[0]->l10 );
		$this->assertEquals( 45324.23, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( 9064.86, $form_objs->objs[1]->month1[25] );
		$this->assertEquals( 9064.86, $form_objs->objs[1]->month1_total );

		$this->assertEquals( 9064.85, $form_objs->objs[1]->month2[8] );
		$this->assertEquals( 9064.85, $form_objs->objs[1]->month2[22] );
		$this->assertEquals( 18129.70, $form_objs->objs[1]->month2_total );

		$this->assertEquals( 9064.84, $form_objs->objs[1]->month3[8] );
		$this->assertEquals( 9064.83, $form_objs->objs[1]->month3[22] );
		$this->assertEquals( 18129.67, $form_objs->objs[1]->month3_total );

		$this->assertEquals( 45324.23, $form_objs->objs[1]->total );
		$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l10 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['deposit_schedule'] = 20; //Semi-Weekly
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'April', $report_output[0]['date_month'] );
		$this->assertEquals( 40021.14, $report_output[0]['wages'] );
		$this->assertEquals( 12006.40, $report_output[0]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 17240.00, $report_output[0]['total_tax'] );

		$this->assertEquals( 'May', $report_output[1]['date_month'] );
		$this->assertEquals( 60031.62, $report_output[1]['wages'] );
		$this->assertEquals( 18009.57, $report_output[1]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1740.90, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.95, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 19751.42, $report_output[1]['total_tax'] );

		$this->assertEquals( 'June', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.08, $report_output[2]['wages'] );
		$this->assertEquals( 12006.38, $report_output[2]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 360.18, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 13527.16, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 140073.84, $report_output[3]['wages'] );
		$this->assertEquals( 42022.35, $report_output[3]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 4062.10, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 361.13, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 50518.58, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 140073.84, $form_objs->objs[0]->l2 );
		$this->assertEquals( 42022.35, $form_objs->objs[0]->l3 );
		$this->assertEquals( 32826.23, $form_objs->objs[0]->l5a );
		$this->assertEquals( 20.57, $form_objs->objs[0]->l5b );
		$this->assertEquals( 140073.84, $form_objs->objs[0]->l5c );
		$this->assertEquals( 40127.04, $form_objs->objs[0]->l5d );
		$this->assertEquals( 8496.23, $form_objs->objs[0]->l7z );
		$this->assertEquals( 8496.23, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16_month_total );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 4070.45, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 2.55, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 4062.14, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 361.14, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 8496.28, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 50518.63, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.05, $form_objs->objs[0]->l7 );
		$this->assertEquals( 50518.58, $form_objs->objs[0]->l10 );
		$this->assertEquals( 50518.58, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( 9064.82, $form_objs->objs[1]->month1[5] );
		$this->assertEquals( 8175.18, $form_objs->objs[1]->month1[19] );
		$this->assertEquals( 17240.00, $form_objs->objs[1]->month1_total );

		$this->assertEquals( 6583.49, $form_objs->objs[1]->month2[3] );
		$this->assertEquals( 6583.49, $form_objs->objs[1]->month2[17] );
		$this->assertEquals( 6584.44, $form_objs->objs[1]->month2[31] );
		$this->assertEquals( 19751.42, $form_objs->objs[1]->month2_total );

		$this->assertEquals( 6763.58, $form_objs->objs[1]->month3[14] );
		$this->assertEquals( 6763.58, $form_objs->objs[1]->month3[28] );
		$this->assertEquals( 13527.16, $form_objs->objs[1]->month3_total );

		$this->assertEquals( 50518.58, $form_objs->objs[1]->total );
		$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l10 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Jun-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 40021.22, $report_output[2]['wages'] );
		$this->assertEquals( 12006.43, $report_output[2]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 18129.67, $report_output[2]['total_tax'] );

		$this->assertEquals( 'April', $report_output[3]['date_month'] );
		$this->assertEquals( 40021.14, $report_output[3]['wages'] );
		$this->assertEquals( 12006.40, $report_output[3]['income_tax'] );
		$this->assertEquals( 4073.00, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 17240.00, $report_output[3]['total_tax'] );

		$this->assertEquals( 'May', $report_output[4]['date_month'] );
		$this->assertEquals( 60031.62, $report_output[4]['wages'] );
		$this->assertEquals( 18009.57, $report_output[4]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[4]['social_security_tax_total'] );
		$this->assertEquals( 1740.90, $report_output[4]['medicare_tax_total'] );
		$this->assertEquals( 0.95, $report_output[4]['additional_medicare_tax'] );
		$this->assertEquals( 19751.42, $report_output[4]['total_tax'] );

		$this->assertEquals( 'June', $report_output[5]['date_month'] );
		$this->assertEquals( 40021.08, $report_output[5]['wages'] );
		$this->assertEquals( 12006.38, $report_output[5]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[5]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[5]['medicare_tax_total'] );
		$this->assertEquals( 360.18, $report_output[5]['additional_medicare_tax'] );
		$this->assertEquals( 13527.16, $report_output[5]['total_tax'] );

		//Total
		$this->assertEquals( 240127.04, $report_output[6]['wages'] );
		$this->assertEquals( 72038.48, $report_output[6]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[6]['social_security_tax_total'] );
		$this->assertEquals( 6963.60, $report_output[6]['medicare_tax_total'] );
		$this->assertEquals( 361.13, $report_output[6]['additional_medicare_tax'] );
		$this->assertEquals( 95842.81, $report_output[6]['total_tax'] );

		return true;
	}

	/**
	 * @group Form941Report_testSemiWeeklyDepositNegativeSocialSecurityWages
	 */
	function testSemiWeeklyDepositNegativeSocialSecurityWages() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Advance' ), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Health Benefits Plan' ), 1000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) ); //Health Plan Expenses - This should cause a negative SS wage


		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 21499.42, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 21499.44, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11203.46, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Advance' ), 11499.48, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Health Benefits Plan' ), 1100.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) ); //Health Plan Expenses - This should cause a negative SS wage

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.50, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.26, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.52, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.25, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.54, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.24, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.56, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.23, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );



		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.58, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.22, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ) );

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Advance' ), 11499.60, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Health Benefits Plan' ), 1200.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ) ); //Health Plan Expenses - This should *not* cause a negative SS wage, since we are well past the maximum wage limit.

		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 11499.62, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 10.21, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['deposit_schedule'] = 20; //Semi-Weekly
		$form_config['social_security_wages']['exclude_pay_stub_entry_account'] = array_merge( $form_config['social_security_wages']['exclude_pay_stub_entry_account'], [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Health Benefits Plan' ) ] );

		$report_obj->setCompanyFormConfig( $form_config ); //Save form config for easy debugging.
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 20010.62, $report_output[2]['wages'] );
		$this->assertEquals( 6003.22, $report_output[2]['income_tax'] );
		$this->assertEquals( 2357.28, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 8940.80, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 80042.60, $report_output[3]['wages'] );
		$this->assertEquals( 24012.92, $report_output[3]['income_tax'] );
		$this->assertEquals( 9801.24, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 2321.20, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 36135.36, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 80042.60, $form_objs->objs[0]->l2 );
		$this->assertEquals( 24012.92, $form_objs->objs[0]->l3 );
		$this->assertEquals( 79042.30, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5ai );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5aii );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 80042.60, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 12122.44, $form_objs->objs[0]->l7z );
		$this->assertEquals( 12246.48, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16_month_total );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 9801.25, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5ai2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5aii2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 2321.24, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 12122.49, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 36135.41, $form_objs->objs[0]->l6 );
		$this->assertEquals( -0.05, $form_objs->objs[0]->l7 );
		$this->assertEquals( 36135.36, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11d );
		$this->assertEquals( 36135.36, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( 9064.86, $form_objs->objs[1]->month1[25] );
		$this->assertEquals( 9064.86, $form_objs->objs[1]->month1_total );

		$this->assertEquals( 9064.85, $form_objs->objs[1]->month2[8] );
		$this->assertEquals( 9064.85, $form_objs->objs[1]->month2[22] );
		$this->assertEquals( 18129.70, $form_objs->objs[1]->month2_total );

		$this->assertEquals( 9064.84, $form_objs->objs[1]->month3[8] );
		$this->assertEquals( 0.00, $form_objs->objs[1]->month3[22] );
		$this->assertEquals( 9064.84, $form_objs->objs[1]->month3_total );

		$this->assertEquals( 36259.40, $form_objs->objs[1]->total );
		//$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l10 ); //Because the entire liability for a single pay period is negative, it will not match L10.


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'April', $report_output[0]['date_month'] );
		$this->assertEquals( 43019.43, $report_output[0]['wages'] );
		$this->assertEquals( 13115.76, $report_output[0]['income_tax'] );
		$this->assertEquals( 5334.40, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 1247.56, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 19697.72, $report_output[0]['total_tax'] );

		$this->assertEquals( 'May', $report_output[1]['date_month'] );
		$this->assertEquals( 22723.49, $report_output[1]['wages'] );
		$this->assertEquals( 5942.73, $report_output[1]['income_tax'] );
		$this->assertEquals( 1343.96, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 658.98, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 7945.67, $report_output[1]['total_tax'] );

		$this->assertEquals( 'June', $report_output[2]['date_month'] );
		$this->assertEquals( 23019.55, $report_output[2]['wages'] );
		$this->assertEquals( 6046.34, $report_output[2]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 667.56, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 6713.90, $report_output[2]['total_tax'] );

		//Total
		$this->assertEquals( 88762.47, $report_output[3]['wages'] );
		$this->assertEquals( 25104.83, $report_output[3]['income_tax'] );
		$this->assertEquals( 6678.36, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 2574.10, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 34357.29, $report_output[3]['total_tax'] );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertEquals( true, property_exists( $form_objs , 'objs' ) );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertEquals( true, property_exists( $form_objs->objs[0] , 'data' ) );

		$this->assertEquals( 0.00, $form_objs->objs[0]->l1 );
		$this->assertEquals( 88762.47, $form_objs->objs[0]->l2 );
		$this->assertEquals( 25104.83, $form_objs->objs[0]->l3 );
		$this->assertEquals( 53857.70, $form_objs->objs[0]->l5a );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5ai );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5aii );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b );
		$this->assertEquals( 88762.47, $form_objs->objs[0]->l5c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d );
		$this->assertEquals( 9252.46, $form_objs->objs[0]->l7z );
		$this->assertEquals( 9128.42, $form_objs->objs[0]->l5_actual_deducted );
		$this->assertEquals( true, $form_objs->objs[0]->l15b );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month1 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month2 );
		$this->assertEquals( false, $form_objs->objs[0]->l16_month3 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16_month_total );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( 6678.35, $form_objs->objs[0]->l5a2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5ai2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5aii2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5b2 );
		$this->assertEquals( 2574.11, $form_objs->objs[0]->l5c2 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5d2 );
		$this->assertEquals( 9252.46, $form_objs->objs[0]->l5e );
		$this->assertEquals( false, $form_objs->objs[0]->l4 );
		$this->assertEquals( 34357.29, $form_objs->objs[0]->l6 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 34357.29, $form_objs->objs[0]->l10 );
		//$this->assertEquals( 1571.47, $form_objs->objs[0]->l11b );
		//$this->assertEquals( 0.00, $form_objs->objs[0]->l11c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11d );
		$this->assertEquals( 34357.29, $form_objs->objs[0]->l12 );
		$this->assertEquals( $form_objs->objs[0]->l12, $form_objs->objs[0]->l13 );

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( 9848.86, $form_objs->objs[1]->month1[5] );
		$this->assertEquals( 9848.86, $form_objs->objs[1]->month1[19] );
		$this->assertEquals( 19697.72, $form_objs->objs[1]->month1_total );

		$this->assertEquals( 4588.72, $form_objs->objs[1]->month2[3] );
		$this->assertEquals( 0.00, $form_objs->objs[1]->month2[17] );
		$this->assertEquals( 3356.95, $form_objs->objs[1]->month2[31] );
		$this->assertEquals( 7945.67, $form_objs->objs[1]->month2_total );

		$this->assertEquals( 3356.95, $form_objs->objs[1]->month3[14] );
		$this->assertEquals( 3356.95, $form_objs->objs[1]->month3[28] );
		$this->assertEquals( 6713.90, $form_objs->objs[1]->month3_total );

		$this->assertEquals( 34357.29, $form_objs->objs[1]->total );
		$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l12 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Jun-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( 'January', $report_output[0]['date_month'] );
		$this->assertEquals( 20010.68, $report_output[0]['wages'] );
		$this->assertEquals( 6003.24, $report_output[0]['income_tax'] );
		$this->assertEquals( 2481.32, $report_output[0]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[0]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[0]['additional_medicare_tax'] );
		$this->assertEquals( 9064.86, $report_output[0]['total_tax'] );

		$this->assertEquals( 'February', $report_output[1]['date_month'] );
		$this->assertEquals( 40021.30, $report_output[1]['wages'] );
		$this->assertEquals( 12006.46, $report_output[1]['income_tax'] );
		$this->assertEquals( 4962.64, $report_output[1]['social_security_tax_total'] );
		$this->assertEquals( 1160.60, $report_output[1]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[1]['additional_medicare_tax'] );
		$this->assertEquals( 18129.70, $report_output[1]['total_tax'] );

		$this->assertEquals( 'March', $report_output[2]['date_month'] );
		$this->assertEquals( 20010.62, $report_output[2]['wages'] );
		$this->assertEquals( 6003.22, $report_output[2]['income_tax'] );
		$this->assertEquals( 2357.28, $report_output[2]['social_security_tax_total'] );
		$this->assertEquals( 580.30, $report_output[2]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[2]['additional_medicare_tax'] );
		$this->assertEquals( 8940.80, $report_output[2]['total_tax'] );

		$this->assertEquals( 'April', $report_output[3]['date_month'] );
		$this->assertEquals( 43019.43, $report_output[3]['wages'] );
		$this->assertEquals( 13115.76, $report_output[3]['income_tax'] );
		$this->assertEquals( 5334.40, $report_output[3]['social_security_tax_total'] );
		$this->assertEquals( 1247.56, $report_output[3]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[3]['additional_medicare_tax'] );
		$this->assertEquals( 19697.72, $report_output[3]['total_tax'] );

		$this->assertEquals( 'May', $report_output[4]['date_month'] );
		$this->assertEquals( 22723.49, $report_output[4]['wages'] );
		$this->assertEquals( 5942.73, $report_output[4]['income_tax'] );
		$this->assertEquals( 1343.96, $report_output[4]['social_security_tax_total'] );
		$this->assertEquals( 658.98, $report_output[4]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[4]['additional_medicare_tax'] );
		$this->assertEquals( 7945.67, $report_output[4]['total_tax'] );

		$this->assertEquals( 'June', $report_output[5]['date_month'] );
		$this->assertEquals( 23019.55, $report_output[5]['wages'] );
		$this->assertEquals( 6046.34, $report_output[5]['income_tax'] );
		$this->assertEquals( 0.00, $report_output[5]['social_security_tax_total'] );
		$this->assertEquals( 667.56, $report_output[5]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[5]['additional_medicare_tax'] );
		$this->assertEquals( 6713.90, $report_output[5]['total_tax'] );

		//Total
		$this->assertEquals( 168805.07, $report_output[6]['wages'] );
		$this->assertEquals( 49117.75, $report_output[6]['income_tax'] );
		$this->assertEquals( 16479.60, $report_output[6]['social_security_tax_total'] );
		$this->assertEquals( 4895.30, $report_output[6]['medicare_tax_total'] );
		$this->assertEquals( 0.00, $report_output[6]['additional_medicare_tax'] );
		$this->assertEquals( 70492.65, $report_output[6]['total_tax'] );

		return true;
	}

	/**
	 * @group Form941Report_testMonthlyDepositWithStartEndDateFilter
	 */
	function testMonthlyDepositWithStartEndDateFilter() {
		//Set UserDeduction start/end dates to reduce Social Security/Medicare taxable wages, and state/local wages.
		$cdlf = TTnew('CompanyDeductionListFactory');
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, [ 'calculation_id' => [ 82, 84, 200, 300 ] ] );
		if ( $cdlf->getRecordCount() ) {
			$udlf = TTNew('UserDeductionListFactory');

			foreach( $cdlf as $cd_obj ) {
				$udlf->getByUserIdAndCompanyDeductionId( $this->user_id, $cd_obj->getId() );
				if ( $udlf->getRecordCount() > 0 ) {
					foreach( $udlf as $ud_obj ) {
						$ud_obj->setStartDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ) );
						$ud_obj->setEndDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
						if ( $ud_obj->isValid() ) {
							$ud_obj->Save();
						}
					}
				}
			}
		}

		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$expected_report_raw_arr = array (
				0 =>
						array (
								'date_month' => 'January',
								'wages' => '20010.68',
								'income_tax' => '6003.24',
								'social_security_tax_total' => '2481.32',
								'medicare_tax_total' => '580.30',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '9064.86',
						),
				1 =>
						array (
								'date_month' => 'February',
								'wages' => '40021.30',
								'income_tax' => '12006.46',
								'social_security_tax_total' => '4962.64',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18129.70',
						),
				2 =>
						array (
								'date_month' => 'March',
								'wages' => '40021.22',
								'income_tax' => '12006.43',
								'social_security_tax_total' => '4962.64',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18129.67',
						),
				3 =>
						array (
								'date_month' => 'Grand Total[3]:',
								'wages' => '100053.20',
								'income_tax' => '30016.13',
								'social_security_tax_total' => '12406.60',
								'medicare_tax_total' => '2901.50',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '45324.23',
								'_total' => true,
						),
		);

		$this->assertEquals( $expected_report_raw_arr, $report_output );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$expected_report_raw_arr = array (
				0 =>
						array (
								'date_month' => 'April',
								'wages' => '40021.14',
								'income_tax' => '12006.40',
								'social_security_tax_total' => '4073.00',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '17240.00',
						),
				1 =>
						array (
								'date_month' => 'May',
								'wages' => '60031.62',
								'income_tax' => '18009.57',
								'social_security_tax_total' => '0.00',
								'medicare_tax_total' => '580.30',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18589.87',
						),
				2 =>
						array (
								'date_month' => 'June',
								'wages' => '40021.08',
								'income_tax' => '12006.38',
								'social_security_tax_total' => '0.00',
								'medicare_tax_total' => '0.00',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '12006.38',
						),
				3 =>
						array (
								'date_month' => 'Grand Total[3]:',
								'wages' => '140073.84',
								'income_tax' => '42022.35',
								'social_security_tax_total' => '4073.00',
								'medicare_tax_total' => '1740.90',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '47836.25',
								'_total' => true,
						),
		);

		$this->assertEquals( $expected_report_raw_arr, $report_output );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Jun-2019' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$expected_report_raw_arr = array (
				0 =>
						array (
								'date_month' => 'January',
								'wages' => '20010.68',
								'income_tax' => '6003.24',
								'social_security_tax_total' => '2481.32',
								'medicare_tax_total' => '580.30',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '9064.86',
						),
				1 =>
						array (
								'date_month' => 'February',
								'wages' => '40021.30',
								'income_tax' => '12006.46',
								'social_security_tax_total' => '4962.64',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18129.70',
						),
				2 =>
						array (
								'date_month' => 'March',
								'wages' => '40021.22',
								'income_tax' => '12006.43',
								'social_security_tax_total' => '4962.64',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18129.67',
						),
				3 =>
						array (
								'date_month' => 'April',
								'wages' => '40021.14',
								'income_tax' => '12006.40',
								'social_security_tax_total' => '4073.00',
								'medicare_tax_total' => '1160.60',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '17240.00',
						),
				4 =>
						array (
								'date_month' => 'May',
								'wages' => '60031.62',
								'income_tax' => '18009.57',
								'social_security_tax_total' => '0.00',
								'medicare_tax_total' => '580.30',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '18589.87',
						),
				5 =>
						array (
								'date_month' => 'June',
								'wages' => '40021.08',
								'income_tax' => '12006.38',
								'social_security_tax_total' => '0.00',
								'medicare_tax_total' => '0.00',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '12006.38',
						),
				6 =>
						array (
								'date_month' => 'Grand Total[6]:',
								'wages' => '240127.04',
								'income_tax' => '72038.48',
								'social_security_tax_total' => '16479.60',
								'medicare_tax_total' => '4642.40',
								'additional_medicare_tax' => '0.00',
								'total_tax' => '93160.48',
								'_total' => true,
						),
		);

		$this->assertEquals( $expected_report_raw_arr, $report_output );

		return true;
	}

}

?>
