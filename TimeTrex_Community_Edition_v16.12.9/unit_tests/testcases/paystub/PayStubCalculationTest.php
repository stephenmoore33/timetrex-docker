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

class PayStubCalculationTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $currency_id = [];
	protected $user_id = null;
	protected $policy_ids = [];
	protected $remittance_source_account_ids = [];
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

		$this->currency_id[10] = $dd->createCurrency( $this->company_id, 10 );
		$this->currency_id[20] = $dd->createCurrency( $this->company_id, 20 );
		$this->currency_id[30] = $dd->createCurrency( $this->company_id, 30 );
		$this->currency_id[40] = $dd->createCurrency( $this->company_id, 40 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id, $this->user_id, $this->legal_entity_id );
		$this->createCompanyDeductions();

		$dd->createUserWageGroups( $this->company_id );

		$this->remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[10], 10 ); // Check
		$this->remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[10], 20 ); // US - EFT
		$this->remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[10], 30 ); // CA - EFT

		//createUser() also handles remittance destination accounts.
		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, null, null, null, null, null, null, null, $this->remittance_source_account_ids );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create policies
		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Regular
		$this->policy_ids['pay_formula_policy'][110] = $dd->createPayFormulaPolicy( $this->company_id, 110 ); //Vacation
		$this->policy_ids['pay_formula_policy'][120] = $dd->createPayFormulaPolicy( $this->company_id, 120 ); //Bank
		$this->policy_ids['pay_formula_policy'][130] = $dd->createPayFormulaPolicy( $this->company_id, 130 ); //Sick
		$this->policy_ids['pay_formula_policy'][200] = $dd->createPayFormulaPolicy( $this->company_id, 200 ); //OT1.5
		$this->policy_ids['pay_formula_policy'][210] = $dd->createPayFormulaPolicy( $this->company_id, 210 ); //OT2.0
		$this->policy_ids['pay_formula_policy'][300] = $dd->createPayFormulaPolicy( $this->company_id, 300 ); //Prem1
		$this->policy_ids['pay_formula_policy'][310] = $dd->createPayFormulaPolicy( $this->company_id, 310 ); //Prem2

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][190] = $dd->createPayCode( $this->company_id, 190, $this->policy_ids['pay_formula_policy'][100] ); //Lunch
		$this->policy_ids['pay_code'][192] = $dd->createPayCode( $this->company_id, 192, $this->policy_ids['pay_formula_policy'][100] ); //Break
		$this->policy_ids['pay_code'][200] = $dd->createPayCode( $this->company_id, 200, $this->policy_ids['pay_formula_policy'][200] ); //OT1
		$this->policy_ids['pay_code'][210] = $dd->createPayCode( $this->company_id, 210, $this->policy_ids['pay_formula_policy'][210] ); //OT2
		$this->policy_ids['pay_code'][300] = $dd->createPayCode( $this->company_id, 300, $this->policy_ids['pay_formula_policy'][300] ); //Prem1
		$this->policy_ids['pay_code'][310] = $dd->createPayCode( $this->company_id, 310, $this->policy_ids['pay_formula_policy'][310] ); //Prem2
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900, $this->policy_ids['pay_formula_policy'][110] ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $this->policy_ids['pay_formula_policy'][120] ); //Bank
		$this->policy_ids['pay_code'][920] = $dd->createPayCode( $this->company_id, 920, $this->policy_ids['pay_formula_policy'][130] ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $this->policy_ids['pay_code'][100] ] ); //Regular
		$this->policy_ids['contributing_pay_code_policy'][12] = $dd->createContributingPayCodePolicy( $this->company_id, 12, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ] ); //Regular+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][14] = $dd->createContributingPayCodePolicy( $this->company_id, 14, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][900] ] ); //Regular+Meal/Break+Absence
		$this->policy_ids['contributing_pay_code_policy'][20] = $dd->createContributingPayCodePolicy( $this->company_id, 20, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][200], $this->policy_ids['pay_code'][210], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ] ); //Regular+OT+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][90] = $dd->createContributingPayCodePolicy( $this->company_id, 90, [ $this->policy_ids['pay_code'][900] ] ); //Absence
		$this->policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $this->policy_ids['pay_code'] ); //All Time

		$this->policy_ids['contributing_shift_policy'][12] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][20] = $dd->createContributingShiftPolicy( $this->company_id, 20, $this->policy_ids['contributing_pay_code_policy'][20] ); //Regular+OT+Meal/Break

		$this->policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][200] );
		$this->policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][210] );

		$this->policy_ids['premium'][] = $dd->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][20], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][] = $dd->createPremiumPolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][20], $this->policy_ids['pay_code'][310] );

		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								$this->policy_ids['overtime'], //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$this->createPunchData();

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function deleteUserWage( $user_id ) {
		$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
		$uwlf->getByUserId( $user_id );
		if ( $uwlf->getRecordCount() > 0 ) {
			foreach ( $uwlf as $uw_obj ) {
				$uw_obj->setDeleted( true );
				if ( $uw_obj->isValid() ) {
					$uw_obj->Save();
				}
			}
		}

		return true;
	}

	function createUserSalaryWage( $user_id, $rate, $effective_date, $wage_group_id = 0 ) {
		$uwf = TTnew( 'UserWageFactory' ); /** @var UserWageFactory $uwf */

		$uwf->setUser( $user_id );
		$uwf->setWageGroup( $wage_group_id );
		$uwf->setType( 13 ); //BiWeekly
		$uwf->setWage( $rate );
		$uwf->setWeeklyTime( ( 3600 * 40 ) );
		$uwf->setHourlyRate( 10.00 );
		$uwf->setEffectiveDate( $effective_date );

		if ( $uwf->isValid() ) {
			$insert_id = $uwf->Save();
			Debug::Text( 'User Wage ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Wage!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
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
				'cpp'                      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ),
				'cpp2'                     => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP2' ),
				'ei'                       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'EI' ),
				'advanced_percent_2'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 2' ),
				'advanced_percent_1'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 1' ),
				'other2'                   => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other2' ),
				'other'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other' ),


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

		Debug::text( 'Saving.... Employee Deduction - Custom1', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Custom1' );
		$pseaf->setOrder( 291 );
		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text( 'Saving.... Employee Deduction - Custom2', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Custom2' );
		$pseaf->setOrder( 291 );
		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}


		Debug::text( 'Saving.... Employee Deduction - Advanced Percent 1', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Advanced Percent 1' );
		$pseaf->setOrder( 291 );
		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text( 'Saving.... Employee Deduction - Advanced Percent 2', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Advanced Percent 2' );
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

		Debug::text( 'Saving.... Employee Deduction - CPP2', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'CPP2' );
		$pseaf->setOrder( 294 );
		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		/*
		//Do this in createPayStubEntryAccountLink() instead, otherwise we have to deal with multiple account link records.
		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() == 1 ) {
			$pseal_obj = $pseallf->getCurrent();
			Debug::text('PayStubEntryAccountLink ID: '. $pseal_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
			$pseal_obj->Save();
		} else {
			Debug::text('PayStubEntryAccountLink ID: FAILED!', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		return true;
	}

	function createCompanyDeductions() {
		//Vacation Accrual Calculation.
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 20 ); //Deduction
		$cdf->setName( 'Vacation Accrual' );
		$cdf->setCalculation( 10 );
		$cdf->setCalculationOrder( 50 );
		//$cdf->setPayStubEntryAccount( $vacation_accrual_id );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ) );
		$cdf->setUserValue1( 4 );

		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross' ) ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		//Test Wage Base amount
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Union Dues' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Union Dues' ) );
		$cdf->setUserValue1( 1 ); //10%
		$cdf->setUserValue2( 3000 );

		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_gross'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		//Test Wage Exempt Amount
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Union Dues2' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other' ) );
		$cdf->setUserValue1( 10 ); //10%
		//$cdf->setUserValue2( 0 );
		$cdf->setUserValue3( '78, 000' ); //Annual -- Test with commas in the values to make sure they are handled properly.

		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_gross'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		//Test Advanced Percent Calculation maximum amount.
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Test Advanced Percent 1' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 1' ) );
		$cdf->setUserValue1( 1 ); //1%
		$cdf->setUserValue2( 2000 ); //Wage Base

		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['regular_time'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}
		//Test Advanced Percent Calculation maximum amount.
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Test Advanced Percent 2' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 2' ) );
		$cdf->setUserValue1( 1 ); //1%
		$cdf->setUserValue2( 2500 ); //Wage Base

		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['regular_time'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setLegalEntity( $this->legal_entity_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 30 );
			$cdf->setName( 'Test Custom Formula' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other2' ) );
			$cdf->setCompanyValue1( "(#custom_value1#+#custom_value2#+#custom_value3#+#custom_value4#+#custom_value5#+#custom_value6#+#custom_value7#+#custom_value8#+#custom_value9#+#custom_value10#)/100" );
			$cdf->setUserValue1( 10 );
			$cdf->setUserValue2( 20 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40 );
			$cdf->setUserValue5( 50 );
			$cdf->setUserValue6( 60 );
			$cdf->setUserValue7( 70 );
			$cdf->setUserValue8( 80 );
			$cdf->setUserValue9( 90 );
			$cdf->setUserValue10( 100 );

			if ( $cdf->isValid() ) {
				$cdf->Save( false );

				$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_deductions'] ] );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}

			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setLegalEntity( $this->legal_entity_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 20 );
			$cdf->setName( 'Test Custom Formula 1' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom1' ) );
			$cdf->setCompanyValue1( "(#custom_value1#*((#custom_value2#+#custom_value3#)*#custom_value4#/#custom_value5#)+#custom_value6#/(#custom_value7#+#custom_value8#)+#custom_value9#+#custom_value10#)/100" );
			$cdf->setUserValue1( 45 );
			$cdf->setUserValue2( 20 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40 );
			$cdf->setUserValue5( 78.12 );
			$cdf->setUserValue6( 60 );
			$cdf->setUserValue7( 44.34 );
			$cdf->setUserValue8( 33 );
			$cdf->setUserValue9( 90 );
			$cdf->setUserValue10( 8 );

			if ( $cdf->isValid() ) {
				$cdf->Save( false );

				$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_deductions'] ] );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}

			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setLegalEntity( $this->legal_entity_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 20 );
			$cdf->setName( 'Test Custom Formula 2' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom2' ) );
			$cdf->setCompanyValue1( "values(n) = (#custom_value1#+#custom_value2#+#custom_value3#+#custom_value4#+#custom_value5#+#custom_value6#+#custom_value7#+#custom_value8#+#custom_value9#+#custom_value10#)/n
									include_amounts(n)=(#include_pay_stub_amount#+#include_pay_stub_ytd_amount#)/n
									exclude_amounts(n)=(#exclude_pay_stub_amount#+#exclude_pay_stub_ytd_amount#)/n
									(values(2)+include_amounts(3)+exclude_amounts(4)+#employee_hourly_rate#)/100" );
			$cdf->setUserValue1( 0.23 );
			$cdf->setUserValue2( 1114.65 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40.55 );
			$cdf->setUserValue5( 55.55 );
			$cdf->setUserValue6( 32.33 );
			$cdf->setUserValue7( 44.34 );
			$cdf->setUserValue8( 21 );
			$cdf->setUserValue9( 47 );
			$cdf->setUserValue10( 8 );
			if ( $cdf->isValid() ) {
				$cdf->Save( false );

				$cdf->setIncludePayStubEntryAccount( [
														 //$this->pay_stub_account_link_arr['total_deductions'],
														 //$this->pay_stub_account_link_arr['employer_contribution'],
														 $this->pay_stub_account_link_arr['regular_time'],
														 $this->pay_stub_account_link_arr['vacation_accrual'],
														 $this->pay_stub_account_link_arr['advanced_percent_1'],
														 $this->pay_stub_account_link_arr['cpp'],
														 $this->pay_stub_account_link_arr['cpp2'],
														 $this->pay_stub_account_link_arr['ei'],
													 ] );

				$cdf->setExcludePayStubEntryAccount( [
														 //$this->pay_stub_account_link_arr['vacation_accrual_release'],
														 $this->pay_stub_account_link_arr['total_gross'],
														 //$this->pay_stub_account_link_arr['other2'],
													 ] );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}
		}

		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'EI - Employee' );
		$cdf->setCalculation( 91 ); //EI Formula
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'EI' ) );
		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_gross'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'CPP - Employee' );
		$cdf->setCalculation( 90 ); //CPP Formula
		$cdf->setCalculationOrder( 91 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );
		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_gross'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'CPP2 - Employee' );
		$cdf->setCalculation( 92 ); //CPP2 Formula
		$cdf->setCalculationOrder( 92 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP2' ) );
		if ( $cdf->isValid() ) {
			$cdf->Save( false );

			$cdf->setIncludePayStubEntryAccount( [ $this->pay_stub_account_link_arr['total_gross'] ] );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

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
		$max_pay_periods = 5;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginYearEpoch( strtotime( '01-Jan-06' ) );
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
						'rate'       => TTMath::MoneyRound( $pse_obj->getRate() ),
						'units'      => TTMath::MoneyRound( $pse_obj->getUnits() ),
						'amount'     => TTMath::MoneyRound( $pse_obj->getAmount() ),
						'ytd_amount' => TTMath::MoneyRound( $pse_obj->getYTDAmount() ),
				];
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return false;
	}

	function getPayStubTransactionArray( $pay_stub_id ) {
		$pstlf = new PayStubTransactionListFactory();
		$pstlf->getByPayStubId( $pay_stub_id );
		if ( $pstlf->getRecordCount() > 0 ) {
			foreach ( $pstlf as $pst_obj ) {
				$retarr[] = $pst_obj->getObjectAsArray();
			}
		}

		if ( isset( $retarr ) ) {
			return $retarr;
		}

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

	function addPayStubAmendments() {
		//Regular FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Bonus' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 10 );
		$psaf->setUnits( 10 );

		$psaf->setDescription( 'Test Fixed PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//Regular percent PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Commission' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 20 );
		$psaf->setPercentAmount( 10 ); //10%
		$psaf->setPercentAmountEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ) );

		$psaf->setDescription( 'Test Percent PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}


		//Vacation Accrual Release percent PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 20 );
		$psaf->setPercentAmount( 50 ); //50% - Leave some balance to check against.
		$psaf->setPercentAmountEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ) );

		$psaf->setDescription( 'Test Vacation Release Percent PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 2' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 1.99 );
		$psaf->setYTDAdjustment( true );

		$psaf->setDescription( 'Test YTD PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Commission' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		//$psaf->setAmount( 0.09 );
		$psaf->setAmount( 1000 ); //Increase this so Union Dues are closer to the maximum earnings and are calculated to be less.
		$psaf->setYTDAdjustment( true );

		$psaf->setDescription( 'Test YTD (2) PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Maximum EI contribution
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'EI' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 700.00 );
		$psaf->setYTDAdjustment( true );

		$psaf->setDescription( 'Test EI YTD PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Maximum CPP contribution
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 1900.00 );
		$psaf->setYTDAdjustment( true );

		$psaf->setDescription( 'Test CPP YTD PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Vacation Accrual totaling issues.
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 99.01 );
		$psaf->setYTDAdjustment( true );

		$psaf->setDescription( 'Test Vacation Accrual YTD PS Amendment' );

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//
		// Add EARNING PS amendments for a pay period that has no Punch hours.
		// Include a regular time adjustment so we can test Wage Base amounts for some tax/deductions.

		//Regular FIXED PS amendment as regular time.
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 33.33 );
		$psaf->setUnits( 3 );

		$psaf->setDescription( 'Test Fixed PS Amendment (1)' );

		$psaf->setEffectiveDate( $this->pay_period_objs[1]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//Regular FIXED PS amendment as Bonus
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Bonus' ) );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 10 );
		$psaf->setUnits( 30 );

		$psaf->setDescription( 'Test Fixed PS Amendment (2)' );

		$psaf->setEffectiveDate( $this->pay_period_objs[1]->getEndDate() );

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		return true;
	}

	function createPayStub() {
		$cps = new CalculatePayStub();
		$cps->setUser( $this->user_id );
		$cps->setPayPeriod( $this->pay_period_objs[0]->getId() );
		$cps->calculate();

		//Pay stub for 2nd pay period
		$cps = new CalculatePayStub();
		$cps->setUser( $this->user_id );
		$cps->setPayPeriod( $this->pay_period_objs[1]->getId() );
		$cps->calculate();

		return true;
	}

	function getPayStub( $pay_period_id = false ) {
		if ( $pay_period_id == false ) {
			$pay_period_id = $this->pay_period_objs[0]->getId();
		}

		$pslf = new PayStubListFactory();
		$pslf->getByUserIdAndPayPeriodId( $this->user_id, $pay_period_id );
		if ( $pslf->getRecordCount() > 0 ) {
			$retval = $pslf->getCurrent()->getId();
			Debug::Text( '  Found Pay Stub ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10 );
			return $retval;
		}

		Debug::Text( 'ERROR: Pay Stub not found! User ID: '. $this->user_id .' Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * @group PayStubCalculation_testMain
	 */
	function testMain() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'premium_1'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 1' ),
				'premium_2'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 2' ),
				'bonus'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Bonus' ),
				'other'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Commission' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'state_disability'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - Disability Insurance' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Medicare' ),
				'union_dues'               => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Union Dues' ),
				'advanced_percent_1'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 1' ),
				'advanced_percent_2'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 2' ),
				'deduction_other'          => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other' ),
				'ei'                       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'ei' ),
				'cpp'                      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'cpp' ),
				'employer_medicare'        => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'employer_fica'            => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Social Security (FICA)' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub_id = $this->getPayStub();

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_accounts);
		//var_dump($pse_arr);

		$this->assertEquals( '2408.00', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '2408.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertEquals( '451.50', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '451.50', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '47.88', $pse_arr[$pse_accounts['premium_1']][0]['amount'] );
		$this->assertEquals( '47.88', $pse_arr[$pse_accounts['premium_1']][0]['ytd_amount'] );

		$this->assertEquals( '10.00', $pse_arr[$pse_accounts['bonus']][0]['rate'] );
		$this->assertEquals( '10.00', $pse_arr[$pse_accounts['bonus']][0]['units'] );
		$this->assertEquals( '100.00', $pse_arr[$pse_accounts['bonus']][0]['amount'] );
		$this->assertEquals( '100.00', $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'] );


		//NOTICE: After switching to UUID, it caused ordering by ID (specifically in CalculatePayStub->getOrderedDeductionAndPSAmendment() ) to be inconsistent from one run to the next.
		//		  This casued failures here because 240.80 and 1000.00 could be in reverse order.
		//		  In this case, the sort order doesn't really matter, as long as its consistent, which it would be for customers.
		// 		  However when running unit tests it can switch from one test to another, so lets account for that.
		//YTD adjustment
		if ( $pse_arr[$pse_accounts['other']][0]['amount'] == '240.80' ) {
			$this->assertEquals( '240.80', $pse_arr[$pse_accounts['other']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['other']][0]['ytd_amount'] );
			//Fixed amount PS amendment
			$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['other']][1]['amount'] );
			$this->assertEquals( '1240.80', $pse_arr[$pse_accounts['other']][1]['ytd_amount'] );
		} else {
			//Fixed amount PS amendment
			$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['other']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['other']][0]['ytd_amount'] );

			$this->assertEquals( '240.80', $pse_arr[$pse_accounts['other']][1]['amount'] );
			$this->assertEquals( '1240.80', $pse_arr[$pse_accounts['other']][1]['ytd_amount'] );
		}

		$this->assertEquals( '10.00', $pse_arr[$pse_accounts['premium_2']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['premium_2']][0]['ytd_amount'] );

		$this->assertEquals( '1.99', $pse_arr[$pse_accounts['premium_2']][1]['amount'] );
		$this->assertEquals( '11.99', $pse_arr[$pse_accounts['premium_2']][1]['ytd_amount'] );

		//Vacation accrual release
		$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );

		//Vacation accrual deduction
		$this->assertEquals( '99.01', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '130.33', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '-114.67', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] );
		$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

		//Union Dues - Should be 19.98 due to getting close to hitting Wage Base, because a YTD adjustment for Total Gross exists for around 1001.99.
		$this->assertEquals( '19.98', $pse_arr[$pse_accounts['union_dues']][0]['amount'] );
		$this->assertEquals( '19.98', $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'] );

		//Advanced Percent
		$this->assertEquals( '20.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'] );
		$this->assertEquals( '20.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'] ); //Exceeds Wage Base

		$this->assertEquals( '24.08', $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'] );
		$this->assertEquals( '24.08', $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'] ); //Not close to Wage Base.

		$this->assertEquals( '37.29', $pse_arr[$pse_accounts['deduction_other']][0]['amount'] );
		$this->assertEquals( '37.29', $pse_arr[$pse_accounts['deduction_other']][0]['ytd_amount'] );

		//EI
		$this->assertEquals( '700.00', $pse_arr[$pse_accounts['ei']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['ei']][0]['ytd_amount'] );
		$this->assertEquals( '29.30', $pse_arr[$pse_accounts['ei']][1]['amount'] ); //HAS TO BE 29.30, as it reached maximum contribution.
		$this->assertEquals( '729.30', $pse_arr[$pse_accounts['ei']][1]['ytd_amount'] );

		//CPP
		$this->assertEquals( '1900.00', $pse_arr[$pse_accounts['cpp']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['cpp']][0]['ytd_amount'] );
		$this->assertEquals( '10.70', $pse_arr[$pse_accounts['cpp']][1]['amount'] );
		$this->assertEquals( '1910.70', $pse_arr[$pse_accounts['cpp']][1]['ytd_amount'] );

		if ( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] >= 600
				&& $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] <= 800 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Federal Income Tax not within range! Amount: ' . $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		}

		if ( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] >= 100
				&& $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] <= 300 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'State Income Tax not within range! Amount: ' . $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		}

		if ( $pse_arr[$pse_accounts['medicare']][0]['amount'] >= 10
				&& $pse_arr[$pse_accounts['medicare']][0]['amount'] <= 100 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Medicare not within range!' );
		}

		if ( $pse_arr[$pse_accounts['state_disability']][0]['amount'] >= 2
				&& $pse_arr[$pse_accounts['state_disability']][0]['amount'] <= 50 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'State Disability not within range!' );
		}

		if ( $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] >= 10
				&& $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] <= 100 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Employer Medicare not within range!' );
		}

		if ( $pse_arr[$pse_accounts['employer_fica']][0]['amount'] >= 100
				&& $pse_arr[$pse_accounts['employer_fica']][0]['amount'] <= 250 ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Employer FICA not within range!' );
		}


		if ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] >= 3300
				&& $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] <= 3450
				&& ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] + ( 1000 + 1.99 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Total Gross not within range!' );
		}

		if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 1300
				&& $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 1500
				&& ( TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 2600 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'Total Deductions not within range! Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		}

		if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 1800
				&& $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 2100
				&& TTMath::sub( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 1598.01 ) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
			$this->assertTrue( true );
		} else {
			$this->assertTrue( false, 'NET PAY not within range!' );
		}

		return true;
	}

	/**
	 * @group PayStubCalculation_testMainCustomFormulas
	 */
	function testMainCustomFormulas() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$pse_accounts = [
					'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
					'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
					'premium_1'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 1' ),
					'premium_2'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 2' ),
					'bonus'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Bonus' ),
					'other'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Commission' ),
					'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
					'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
					'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
					'state_disability'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - Disability Insurance' ),
					'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Medicare' ),
					'union_dues'               => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Union Dues' ),
					'advanced_percent_1'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 1' ),
					'advanced_percent_2'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 2' ),
					'deduction_other'          => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other' ),
					'ei'                       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'ei' ),
					'cpp'                      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'cpp' ),
					'employer_medicare'        => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
					'employer_fica'            => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Social Security (FICA)' ),
					'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
					'test_custom_formula'      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other2' ),
					'test_custom_formula_1'    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom1' ),
					'test_custom_formula_2'    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom2' ),
			];

			$pay_stub_id = $this->getPayStub();

			$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
			//var_dump($pse_accounts);
			//var_dump($pse_arr);
			$this->assertTrue( is_array( $pse_arr ), 'Pay Stub was not created!' );

			$this->assertEquals( '2408.00', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
			$this->assertEquals( '2408.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

			$this->assertEquals( '451.50', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
			$this->assertEquals( '451.50', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

			$this->assertEquals( '47.88', $pse_arr[$pse_accounts['premium_1']][0]['amount'] );
			$this->assertEquals( '47.88', $pse_arr[$pse_accounts['premium_1']][0]['ytd_amount'] );

			$this->assertEquals( '10.00', $pse_arr[$pse_accounts['bonus']][0]['rate'] );
			$this->assertEquals( '10.00', $pse_arr[$pse_accounts['bonus']][0]['units'] );
			$this->assertEquals( '100.00', $pse_arr[$pse_accounts['bonus']][0]['amount'] );
			$this->assertEquals( '100.00', $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'] );

			//NOTICE: After switching to UUID, it caused ordering by ID (specifically in CalculatePayStub->getOrderedDeductionAndPSAmendment() ) to be inconsistent from one run to the next.
			//		  This casued failures here because 240.80 and 1000.00 could be in reverse order.
			//		  In this case, the sort order doesn't really matter, as long as its consistent, which it would be for customers.
			// 		  However when running unit tests it can switch from one test to another, so lets account for that.
			//YTD adjustment
			if ( $pse_arr[$pse_accounts['other']][0]['amount'] == '240.80' ) {
				$this->assertEquals( '240.80', $pse_arr[$pse_accounts['other']][0]['amount'] );
				$this->assertEquals( '0.00', $pse_arr[$pse_accounts['other']][0]['ytd_amount'] );
				//Fixed amount PS amendment
				$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['other']][1]['amount'] );
				$this->assertEquals( '1240.80', $pse_arr[$pse_accounts['other']][1]['ytd_amount'] );
			} else {
				//Fixed amount PS amendment
				$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['other']][0]['amount'] );
				$this->assertEquals( '0.00', $pse_arr[$pse_accounts['other']][0]['ytd_amount'] );

				$this->assertEquals( '240.80', $pse_arr[$pse_accounts['other']][1]['amount'] );
				$this->assertEquals( '1240.80', $pse_arr[$pse_accounts['other']][1]['ytd_amount'] );
			}

			$this->assertEquals( '10.00', $pse_arr[$pse_accounts['premium_2']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['premium_2']][0]['ytd_amount'] );

			$this->assertEquals( '1.99', $pse_arr[$pse_accounts['premium_2']][1]['amount'] );
			$this->assertEquals( '11.99', $pse_arr[$pse_accounts['premium_2']][1]['ytd_amount'] );

			//Vacation accrual release
			$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
			$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );

			//Vacation accrual deduction
			$this->assertEquals( '99.01', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

			$this->assertEquals( '130.33', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

			$this->assertEquals( '-114.67', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] );
			$this->assertEquals( '114.67', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

			//Union Dues - Should be 19.98 due to getting close to hitting Wage Base, because a YTD adjustment for Total Gross exists for around 1001.99.
			$this->assertEquals( '19.98', $pse_arr[$pse_accounts['union_dues']][0]['amount'] );
			$this->assertEquals( '19.98', $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'] );

			//Advanced Percent
			$this->assertEquals( '20.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'] );
			$this->assertEquals( '20.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'] ); //Exceeds Wage Base

			$this->assertEquals( '24.08', $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'] );
			$this->assertEquals( '24.08', $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'] ); //Not close to Wage Base.

			$this->assertEquals( '37.29', $pse_arr[$pse_accounts['deduction_other']][0]['amount'] );
			$this->assertEquals( '37.29', $pse_arr[$pse_accounts['deduction_other']][0]['ytd_amount'] );

			//EI
			$this->assertEquals( '700.00', $pse_arr[$pse_accounts['ei']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['ei']][0]['ytd_amount'] );
			$this->assertEquals( '29.30', $pse_arr[$pse_accounts['ei']][1]['amount'] ); //HAS TO BE 29.30, as it reached maximum contribution.
			$this->assertEquals( '729.30', $pse_arr[$pse_accounts['ei']][1]['ytd_amount'] );

			//CPP
			$this->assertEquals( '1900.00', $pse_arr[$pse_accounts['cpp']][0]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['cpp']][0]['ytd_amount'] );
			$this->assertEquals( '10.70', $pse_arr[$pse_accounts['cpp']][1]['amount'] );
			$this->assertEquals( '1910.70', $pse_arr[$pse_accounts['cpp']][1]['ytd_amount'] );

			//Custom formula deductions.
			$this->assertEquals( '5.50', $pse_arr[$pse_accounts['test_custom_formula']][0]['amount'] );
			$this->assertEquals( '5.50', $pse_arr[$pse_accounts['test_custom_formula']][0]['ytd_amount'] );

			$this->assertEquals( '12.51', $pse_arr[$pse_accounts['test_custom_formula_1']][0]['amount'] );
			$this->assertEquals( '12.51', $pse_arr[$pse_accounts['test_custom_formula_1']][0]['ytd_amount'] );

			$this->assertEquals( '35.40', $pse_arr[$pse_accounts['test_custom_formula_2']][0]['amount'] );
			$this->assertEquals( '35.40', $pse_arr[$pse_accounts['test_custom_formula_2']][0]['ytd_amount'] );

			if ( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] >= 600
					&& $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] <= 800 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Federal Income Tax not within range! Amount: ' . $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
			}

			if ( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] >= 100
					&& $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] <= 300 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'State Income Tax not within range! Amount: ' . $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
			}

			if ( $pse_arr[$pse_accounts['medicare']][0]['amount'] >= 10
					&& $pse_arr[$pse_accounts['medicare']][0]['amount'] <= 100 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Medicare not within range!' );
			}

			if ( $pse_arr[$pse_accounts['state_disability']][0]['amount'] >= 2
					&& $pse_arr[$pse_accounts['state_disability']][0]['amount'] <= 50 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'State Disability not within range!' );
			}

			if ( $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] >= 10
					&& $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] <= 100 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Employer Medicare not within range!' );
			}

			if ( $pse_arr[$pse_accounts['employer_fica']][0]['amount'] >= 100
					&& $pse_arr[$pse_accounts['employer_fica']][0]['amount'] <= 250 ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Employer FICA not within range!' );
			}


			if ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] >= 3300
					&& $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] <= 3450
					&& ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] + ( 1000 + 1.99 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Total Gross not within range!' );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 1300
					&& $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 1500
					&& ( TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 2600 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Total Deductions not within range! Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 1800
					&& $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 2100
					&& TTMath::sub( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 1598.01 ) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'NET PAY not within range!' );
			}
		}

		return true;
	}

	/**
	 * @group PayStubCalculation_testNoHoursPayStub
	 */
	function testNoHoursPayStub() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'premium_1'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 1' ),
				'premium_2'                => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Premium 2' ),
				'bonus'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Bonus' ),
				'other'                    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Commission' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'state_disability'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - Disability Insurance' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Medicare' ),
				'union_dues'               => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Union Dues' ),
				'advanced_percent_1'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 1' ),
				'advanced_percent_2'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Advanced Percent 2' ),
				'deduction_other'          => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other' ),
				'ei'                       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'ei' ),
				'cpp'                      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'cpp' ),
				'employer_medicare'        => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'employer_fica'            => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Social Security (FICA)' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '33.33', $pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '3.00', $pse_arr[$pse_accounts['regular_time']][0]['units'] );
		$this->assertEquals( '99.99', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '2507.99', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertEquals( '10.00', $pse_arr[$pse_accounts['bonus']][0]['rate'] );
		$this->assertEquals( '30.00', $pse_arr[$pse_accounts['bonus']][0]['units'] );
		$this->assertEquals( '300.00', $pse_arr[$pse_accounts['bonus']][0]['amount'] );
		$this->assertEquals( '400.00', $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['union_dues']][0]['amount'] );
		$this->assertEquals( '19.98', $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'] );

		$this->assertEquals( '399.99', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );

		//Check deductions.
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'] ); //Already Exceeded Wage Base, this should be 0!!
		$this->assertEquals( '20.00', $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'] );
		$this->assertEquals( '0.92', $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'] ); //Nearing Wage Base, this should be less than 1!!
		$this->assertEquals( '25.00', $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'] );

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 110
					&& $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 150
					&& ( TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 4000.32 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Total Deductions not within range! Total Deductions: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 225
					&& $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 290
					&& TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 374.52 ) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'NET PAY not within range! Net Pay: ' . $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
			}
		} else {
			//If Community Edition without custom formulas these values all change.
			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 65
					&& $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 80
					&& ( TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 3946.91 ) ) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'Total Deductions not within range! Total Deductions: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 225
					&& $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 350
					&& TTMath::add( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 427.93 ) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( true );
			} else {
				$this->assertTrue( false, 'NET PAY not within range! Net Pay: ' . $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] . ' YTD Amount: ' . $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
			}
		}

		return true;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubA
	 */
	//Test basic salary calculation.
	function testSalaryPayStubA() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime( '01-Jan-2001' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), -1, 'day' ) );

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = [
				'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		//$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '3.00' );
		$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '1000.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertCount( 1, $pse_arr[$pse_accounts['regular_time']] );


		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '33.33', $pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '3.00', $pse_arr[$pse_accounts['regular_time']][0]['units'] );
		$this->assertEquals( '99.99', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '1099.99', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertCount( 1, $pse_arr[$pse_accounts['regular_time']] );

		return true;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubB
	 */
	//Test advanced pro-rating salary calculation.
	function testSalaryPayStubB() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime( '01-Jan-2001' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), -1, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1500, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), 4, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 2000, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), 8, 'day' ) );

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = [
				'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '48.00', $pse_arr[$pse_accounts['regular_time']][0]['units'] );
		$this->assertEquals( '857.14', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][1]['rate'] );
		$this->assertEquals( '32.00', $pse_arr[$pse_accounts['regular_time']][1]['units'] );
		$this->assertEquals( '428.57', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][2]['rate'] );
		$this->assertEquals( '32.00', $pse_arr[$pse_accounts['regular_time']][2]['units'] );
		$this->assertEquals( '285.71', $pse_arr[$pse_accounts['regular_time']][2]['amount'] );
		$this->assertEquals( '1571.42', $pse_arr[$pse_accounts['regular_time']][2]['ytd_amount'] );

		$this->assertCount( 3, $pse_arr[$pse_accounts['regular_time']] );


		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '33.33', $pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '3.00', $pse_arr[$pse_accounts['regular_time']][0]['units'] );
		$this->assertEquals( '99.99', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '1671.41', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertCount( 1, $pse_arr[$pse_accounts['regular_time']] );

		return true;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubC
	 */
	//Test advanced pro-rating salary calculation.
	function testSalaryPayStubC() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime( '01-Jan-2001' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), -1, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 1, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 2, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 3, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 4, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 5, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 6, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 7, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 8, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 9, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 10, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 11, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 12, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 13, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 14, 'day' ) );
		$this->createUserSalaryWage( $this->user_id, 1000, TTDate::incrementDate( $this->pay_period_objs[1]->getStartDate(), 15, 'day' ) );


		//Create one punch in the next pay period so we can test pro-rating without any regular time.
		global $dd;
		$date_stamp = TTDate::getDate( 'DATE', $this->pay_period_objs[1]->getStartDate() );
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

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = [
				'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		//Just check the final pay stub.
		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		for ( $i = 0; $i <= 12; $i++ ) {
			$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][$i]['rate'] );
			$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][$i]['units'] );
			$this->assertEquals( '71.43', $pse_arr[$pse_accounts['regular_time']][$i]['amount'] );
			$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][$i]['ytd_amount'] );
		}

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][13]['rate'] );
		$this->assertEquals( '3.00', $pse_arr[$pse_accounts['regular_time']][13]['units'] );
		$this->assertEquals( '71.43', $pse_arr[$pse_accounts['regular_time']][13]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][13]['ytd_amount'] );

		$this->assertEquals( '33.33', $pse_arr[$pse_accounts['regular_time']][14]['rate'] );
		$this->assertEquals( '3.00', $pse_arr[$pse_accounts['regular_time']][14]['units'] );
		$this->assertEquals( '99.99', $pse_arr[$pse_accounts['regular_time']][14]['amount'] );
		$this->assertEquals( '1172.37', $pse_arr[$pse_accounts['regular_time']][14]['ytd_amount'] );

		$this->assertCount( 15, $pse_arr[$pse_accounts['regular_time']] );

		return true;
	}

	/**
	 * @group PayStubCalculation_testMultiCurrencyPayStubA
	 */
	function testMultiCurrencyPayStubA() {
		global $dd;
		$this->remittance_source_account_ids[$this->legal_entity_id][20] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[20], 100 ); // Check - CAD
		$this->remittance_source_account_ids[$this->legal_entity_id][30] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[30], 110 ); // Check - EUR
		$this->remittance_source_account_ids[$this->legal_entity_id][40] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[40], 120 ); // Check - MXN

		//Delete existing destination accounts so random fixed amounts don't cause problems.
		$rdlf = TTnew('RemittanceDestinationAccountListFactory');
		$rdlf->getByUserIdAndCompany( $this->user_id, $this->company_id );
		if ( $rdlf->getRecordCount() > 0 ) {
			foreach( $rdlf as $rd_obj ) {
				$rd_obj->Delete();
			}
		}
		unset($rdlf);

		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[10], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][0], 100, 55 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[20], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][20], 110, 10 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[30], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][30], 120, 15 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[40], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][40], 130, 20 );


		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime( '01-Jan-2001' ) );
		$this->createUserSalaryWage( $this->user_id, 10000, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), -1, 'day' ) );

		//$this->addPayStubAmendments();

		//When run on Community Edition, custom formula Tax/Deductions don't exist, so emulate those manually.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other2' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 5.50 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}

			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom1' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 12.51 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}

			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom2' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 69.05 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}
		}

		$this->createPayStub();

		$pse_accounts = [
				'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '10000.00', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '10000.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertCount( 1, $pse_arr[$pse_accounts['regular_time']] );

		$pst_arr = $this->getPayStubTransactionArray( $pay_stub_id );
		//var_dump($pst_arr);

		//Different calculations due to custom formula Tax/Deductions.
		$this->assertEquals( '2269.85', $pst_arr[0]['amount'] );
		$this->assertEquals( $pst_arr[0]['currency_id'], $this->currency_id[10] ); //USD
		$this->assertEquals( '1.0000000000', $pst_arr[0]['currency_rate'] );

		$this->assertEquals( '495.24', $pst_arr[1]['amount'] );
		$this->assertEquals( $pst_arr[1]['currency_id'], $this->currency_id[20] ); //CAD
		$this->assertEquals( '0.8333333333', $pst_arr[1]['currency_rate'] );

		$this->assertEquals( '804.765', $pst_arr[2]['amount'] );
		$this->assertEquals( $pst_arr[2]['currency_id'], $this->currency_id[30] ); //EUR
		$this->assertEquals( '0.7692307692', $pst_arr[2]['currency_rate'] );

		$this->assertEquals( '8051777.00', $pst_arr[3]['amount'] );
		$this->assertEquals( $pst_arr[3]['currency_id'], $this->currency_id[40] ); //MXN (Pesos)
		$this->assertEquals( '0.0001025115', $pst_arr[3]['currency_rate'] );

		$this->assertCount( 4, $pst_arr );

		return true;
	}

	/**
	 * @group PayStubCalculation_testMultiCurrencyPayStubA
	 */
	function testMultiCurrencyPayStubB() {
		global $dd;
		$this->remittance_source_account_ids[$this->legal_entity_id][20] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[20], 100 ); // Check - CAD
		$this->remittance_source_account_ids[$this->legal_entity_id][30] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[30], 110 ); // Check - EUR
		$this->remittance_source_account_ids[$this->legal_entity_id][40] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id[40], 120 ); // Check - MXN

		//Delete existing destination accounts so random fixed amounts don't cause problems.
		$rdlf = TTnew('RemittanceDestinationAccountListFactory');
		$rdlf->getByUserIdAndCompany( $this->user_id, $this->company_id );
		if ( $rdlf->getRecordCount() > 0 ) {
			foreach( $rdlf as $rd_obj ) {
				$rd_obj->Delete();
			}
		}
		unset($rdlf);

		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[10], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][0], 200, 60 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[20], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][20], 210, 50 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[30], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][30], 220, 40 );
		$dd->createRemittanceDestinationAccount( $this->user_id, $this->currency_id[40], $this->legal_entity_id, $this->remittance_source_account_ids[$this->legal_entity_id][40], 230, 30 );


		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime( '01-Jan-2001' ) );
		$this->createUserSalaryWage( $this->user_id, 10000, TTDate::incrementDate( $this->pay_period_objs[0]->getStartDate(), -1, 'day' ) );

		//$this->addPayStubAmendments();

		//When run on Community Edition, custom formula Tax/Deductions don't exist, so emulate those manually.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Other2' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 5.50 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}

			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom1' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 12.51 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}

			$psaf = new PayStubAmendmentFactory();
			$psaf->setUser( $this->user_id );
			$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Custom2' ) );
			$psaf->setStatus( 50 ); //Active
			$psaf->setType( 10 );
			$psaf->setAmount( 69.05 );
			$psaf->setDescription( 'Emulate Custom Formula' );
			$psaf->setEffectiveDate( $this->pay_period_objs[0]->getStartDate() );
			$psaf->setAuthorized( true );
			if ( $psaf->isValid() ) {
				$psaf->Save();
			}
		}

		$this->createPayStub();

		$pse_accounts = [
				'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( '0.00', (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'] );
		$this->assertEquals( '10000.00', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '10000.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );

		$this->assertCount( 1, $pse_arr[$pse_accounts['regular_time']] );

		$pst_arr = $this->getPayStubTransactionArray( $pay_stub_id );
		//print_r($pst_arr);

		//Different calculations due to custom formula Tax/Deductions.
		$this->assertEquals( '2476.20', $pst_arr[0]['amount'] );
		$this->assertEquals( $pst_arr[0]['currency_id'], $this->currency_id[10] ); //USD
		$this->assertEquals( '1.0000000000', $pst_arr[0]['currency_rate'] );

		$this->assertEquals( '990.48', $pst_arr[1]['amount'] );
		$this->assertEquals( $pst_arr[1]['currency_id'], $this->currency_id[20] ); //CAD
		$this->assertEquals( '0.8333333333', $pst_arr[1]['currency_rate'] );

		$this->assertEquals( '429.208', $pst_arr[2]['amount'] );
		$this->assertEquals( $pst_arr[2]['currency_id'], $this->currency_id[30] ); //EUR
		$this->assertEquals( '0.7692307692', $pst_arr[2]['currency_rate'] );

		$this->assertEquals( '4831066.20', $pst_arr[3]['amount'] );
		$this->assertEquals( $pst_arr[3]['currency_id'], $this->currency_id[40] ); //MXN (Pesos)
		$this->assertEquals( '0.0001025115', $pst_arr[3]['currency_rate'] );

		$this->assertCount( 4, $pst_arr );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCPPAgeLimitsA
	 */
	//Test 18/70 age limits for CPP and pro-rating.
	function testCPPAgeLimitsA() {
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'CPP' );
		$cdf->setCalculation( 90 ); //CPP
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );
		$cdf->setMinimumUserAge( 18 );
		$cdf->setMaximumUserAge( 70 );
		//if ( $cdf->isValid() ) {
		//	$cdf->Save(FALSE);
		//	$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );
		//	if ( $cdf->isValid() ) {
		//		$cdf->Save( FALSE );
		//	}
		//}

		//Test with no birth date defaulting to having CPP deducted.
		$this->assertEquals( true, $cdf->isCPPAgeEligible( strtotime( '01-Sep-1990' ), strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( '', strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( false, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( null, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( strtotime( '01-Sep-2000' ), '' ) );    //No transaction date specified, always false.
		$this->assertEquals( false, $cdf->isCPPAgeEligible( strtotime( '01-Sep-2000' ), false ) ); //No transaction date specified, always false.
		$this->assertEquals( false, $cdf->isCPPAgeEligible( strtotime( '01-Sep-2000' ), null ) );  //No transaction date specified, always false.


		$birth_date = strtotime( '16-Oct-1997' ); //18yrs old
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Sep-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Oct-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Nov-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Nov-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '14-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '16-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Jan-2017' ) ) );

		$birth_date = strtotime( '31-Dec-1997' ); //18yrs old
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Sep-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Nov-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Nov-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Dec-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '14-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '16-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '31-Jan-2017' ) ) );


		$birth_date = strtotime( '15-Jun-1997' ); //18yrs old
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-May-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jun-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '15-Jun-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '03-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Aug-2015' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2016' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2017' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2017' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2017' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2018' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2018' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2018' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2019' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2019' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2019' ) ) );


		$birth_date = strtotime( '15-Jun-1960' ); //55yrs old
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2015' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2016' ) ) );


		$birth_date = strtotime( '15-Jun-1945' ); //70yrs old
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-May-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '15-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2015' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '01-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '03-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Aug-2015' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2016' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2017' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2017' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2017' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2018' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2018' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2018' ) ) );

		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-May-2019' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jun-2019' ) ) );
		$this->assertEquals( false, $cdf->isCPPAgeEligible( $birth_date, strtotime( '30-Jul-2019' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeduductionDateFilters
	 */
	function testCompanyDeduductionDateFilters() {
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 );   //Tax
		$cdf->setName( '401K' );
		$cdf->setCalculation( 10 ); //Percent
		$cdf->setCalculationOrder( 100 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setStartDate( strtotime( '02-Apr-2022' ) );
		$udf->setEndDate( strtotime( '29-Apr-2022' ) );

		$cdf->setFilterDateType( 100 ); //100=Pay Stub Start Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '02-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '03-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '28-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '29-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '30-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );

		$cdf->setFilterDateType( 110 ); //110=Pay Stub End Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '02-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '03-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '28-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '29-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '30-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );

		$cdf->setFilterDateType( 120 ); //120=Pay Stub Transaction Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '02-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '03-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '28-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '29-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '30-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );

		$cdf->setFilterDateType( 200 ); //200=Pay Period Start Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '02-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '03-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '28-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '29-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '30-Apr-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ) ) );

		$cdf->setFilterDateType( 210 ); //200=Pay Period End Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '02-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '03-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '28-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '29-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '30-Apr-2022' ),  strtotime( '01-Jun-2022' ) ) );

		$cdf->setFilterDateType( 220 ); //200=Pay Period Transaction Date.
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Apr-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '02-Apr-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '03-Apr-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '28-Apr-2022' ) ) );
		$this->assertEquals( true,  $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '29-Apr-2022' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '01-Jun-2022' ), strtotime( '30-Apr-2022' ) ) );
	}

	/**
	 * @group PayStubCalculation_testCPPAgeLimitsB
	 */
	//Test 18/70 age limits for CPP and pro-rating.
	function testCPPAgeLimitsB() {
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setLegalEntity( $this->legal_entity_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'CPP' );
		$cdf->setCalculation( 90 ); //CPP
		$cdf->setCalculationOrder( 100 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );
		//if ( $cdf->isValid() ) {
		//	$cdf->Save(FALSE);
		//	$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );
		//	if ( $cdf->isValid() ) {
		//		$cdf->Save( FALSE );
		//	}
		//}


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setStartDate( strtotime( '16-Oct-2015' ) );
		$udf->setEndDate( '' );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Sep-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Oct-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Nov-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Nov-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '14-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '16-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Jan-2017' ) ) );

		$udf->setStartDate( strtotime( '31-Dec-2015' ) ); //18yrs old
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Sep-2014' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Sep-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Oct-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Nov-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Nov-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Dec-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Dec-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '14-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '16-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Jan-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '31-Jan-2017' ) ) );


		$udf->setStartDate( strtotime( '15-Jun-2015' ) );    //18yrs old
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-May-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jun-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '15-Jun-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '03-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Aug-2015' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2016' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2017' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2017' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2017' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2018' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2018' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2018' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2019' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2019' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2019' ) ) );


		$udf->setStartDate( strtotime( '15-Jun-2010' ) );    //55yrs old
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2015' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2016' ) ) );


		$udf->setStartDate( strtotime( '15-Jun-1970' ) );
		$udf->setEndDate( strtotime( '15-Jun-2015' ) );    //70yrs old
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2011' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2011' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2011' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2012' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2012' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2012' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2013' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2013' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2013' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2014' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2014' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2014' ) ) );

		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-May-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '15-Jun-2015' ) ) );
		$this->assertEquals( true, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2015' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '01-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '03-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Aug-2015' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2016' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2016' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2016' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2017' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2017' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2017' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2018' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2018' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2018' ) ) );

		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-May-2019' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jun-2019' ) ) );
		$this->assertEquals( false, $cdf->isActiveDate( $udf, null, null, null, null, null, strtotime( '30-Jul-2019' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsA
	 */
	function testCompanyDeductionLengthOfServiceBracketsA() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 4.999' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 40 );
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 40 );
		$cdf_a->setMaximumLengthOfService( 4.999 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 5 -> 9.999' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 40 );
		$cdf_b->setMinimumLengthOfService( 5 );
		$cdf_b->setMaximumLengthOfServiceUnit( 40 );
		$cdf_b->setMaximumLengthOfService( 9.999 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '15-Jul-2010' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );

		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '14-Jul-2010' ) ) ); //Before length of service date, its consider true if it starts on 0.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '15-Jul-2010' ) ) ); //Right on length of service date.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2010' ) ) ); //One day after length of service date.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2011' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2012' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2013' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2014' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '14-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2016' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2017' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2018' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2019' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2020' ) ) );


		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2011' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2012' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2013' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2016' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2017' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2018' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2019' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2020' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2020' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2020' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsB
	 */
	function testCompanyDeductionLengthOfServiceBracketsB() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 5' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 40 );
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 40 );
		$cdf_a->setMaximumLengthOfService( 5 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 5 -> 10' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 40 );
		$cdf_b->setMinimumLengthOfService( 5 );
		$cdf_b->setMaximumLengthOfServiceUnit( 40 );
		$cdf_b->setMaximumLengthOfService( 10 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '15-Jul-2010' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );

		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '14-Jul-2010' ) ) ); //Before length of service date, its consider true if it starts on 0.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '15-Jul-2010' ) ) ); //Right on length of service date.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2010' ) ) ); //One day after length of service date.
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2011' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2012' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2013' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2014' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '14-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2015' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2016' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2017' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2018' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2019' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '16-Jul-2020' ) ) );


		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2010' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2011' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2012' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2013' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2014' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2015' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2016' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2017' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2018' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2019' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Jul-2020' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Jul-2020' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '16-Jul-2020' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsC
	 */
	function testCompanyDeductionLengthOfServiceBracketsC() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 7.999' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 40 );
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 40 );
		$cdf_a->setMaximumLengthOfService( 7.999 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 8 -> 12' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 40 );
		$cdf_b->setMinimumLengthOfService( 8 );
		$cdf_b->setMaximumLengthOfServiceUnit( 40 );
		$cdf_b->setMaximumLengthOfService( 11.999 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '14-Feb-2013' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );



		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '12-Feb-2021' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '13-Feb-2021' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, 1613275199 ) ); //Sat, 13 Feb 2021 23:59:59 -0400
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '14-Feb-2021' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '15-Feb-2021' ) ) );


		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '12-Feb-2021' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '13-Feb-2021' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, 1613275199 ) ); //Sat, 13 Feb 2021 23:59:59 -0400
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '14-Feb-2021' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '15-Feb-2021' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsD
	 */
	function testCompanyDeductionLengthOfServiceBracketsD() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 2.999' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 40 ); //40=Years
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 40 ); //40=Years
		$cdf_a->setMaximumLengthOfService( 3 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 0 -> 3' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 40 );
		$cdf_b->setMinimumLengthOfService( 3 );
		$cdf_b->setMaximumLengthOfServiceUnit( 40 );
		$cdf_b->setMaximumLengthOfService( 5 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '01-Feb-2019' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );



		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsE
	 */
	function testCompanyDeductionLengthOfServiceBracketsE() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 2.999' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 30 ); //30=Month
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 30 ); //30=Month
		$cdf_a->setMaximumLengthOfService( 36 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 0 -> 3' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 30 );
		$cdf_b->setMinimumLengthOfService( 36 );
		$cdf_b->setMaximumLengthOfServiceUnit( 30 );
		$cdf_b->setMaximumLengthOfService( 60 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '01-Feb-2019' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );



		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		return true;
	}

	/**
	 * @group PayStubCalculation_testCompanyDeductionLengthOfServiceBracketsF
	 */
	function testCompanyDeductionLengthOfServiceBracketsF() {
		$cdf_a = new CompanyDeductionFactory();
		$cdf_a->setCompany( $this->company_id );
		$cdf_a->setLegalEntity( $this->legal_entity_id );
		$cdf_a->setStatus( 10 ); //Enabled
		$cdf_a->setType( 10 ); //Tax
		$cdf_a->setName( 'Vacation Accrual 0 -> 2.999' );
		$cdf_a->setCalculation( 10 ); //Percent
		$cdf_a->setCalculationOrder( 100 );
		$cdf_a->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_a->setMinimumLengthOfServiceUnit( 20 ); //20=Week
		$cdf_a->setMinimumLengthOfService( 0 );
		$cdf_a->setMaximumLengthOfServiceUnit( 20 ); //20=Week
		$cdf_a->setMaximumLengthOfService( 156 );
		$cdf_a->preSave(); //Calculates the  setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$cdf_b = new CompanyDeductionFactory();
		$cdf_b->setCompany( $this->company_id );
		$cdf_b->setLegalEntity( $this->legal_entity_id );
		$cdf_b->setStatus( 10 ); //Enabled
		$cdf_b->setType( 10 ); //Tax
		$cdf_b->setName( 'Vacation Accrual 0 -> 3' );
		$cdf_b->setCalculation( 10 ); //Percent
		$cdf_b->setCalculationOrder( 100 );
		$cdf_b->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'Vacation Accrual' ) );
		$cdf_b->setMinimumLengthOfServiceUnit( 20 );
		$cdf_b->setMinimumLengthOfService( 156 );
		$cdf_b->setMaximumLengthOfServiceUnit( 20 );
		$cdf_b->setMaximumLengthOfService( 260 );
		$cdf_b->preSave(); //Calculates the setMinimumLengthOfServiceDays(), setMaximumLengthOfServiceDays()


		$udf = new UserDeductionFactory();
		$udf->setUser( $this->user_id );
		$udf->setLengthOfServiceDate( strtotime( '01-Feb-2019' ) );
		$udf->setStartDate( '' );
		$udf->setEndDate( '' );



		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '26-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_a->isActiveLengthOfService( $udf, strtotime( '27-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '28-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '29-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( false, $cdf_a->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '26-Jan-2022' ) ) );
		$this->assertEquals( false, $cdf_b->isActiveLengthOfService( $udf, strtotime( '27-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '28-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '29-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '30-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '31-Jan-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '01-Feb-2022' ) ) );
		$this->assertEquals( true, $cdf_b->isActiveLengthOfService( $udf, strtotime( '02-Feb-2022' ) ) );

		return true;
	}

}

?>
