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

/**
 * @group USPayrollDeductionTest2008
 */
class USPayrollDeductionTest2008 extends PHPUnit\Framework\TestCase {
	public $company_id = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone( 'Etc/GMT+8' ); //Force to non-DST timezone. 'PST' isnt actually valid.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function mf( $amount ) {
		return TTMath::MoneyRound( $amount );
	}

	//
	//
	//
	// 2008
	//
	//
	//
	function testUS_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1010.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1010.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.31', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.31
	}

	function testUS_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '55.77', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //55.77
	}

	function testUS_2008a_BiWeekly_Married_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '28.85', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //28.85
	}

	function testUS_2008a_SemiMonthly_Single_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '95.63', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //95.63
	}

	function testUS_2008a_SemiMonthly_Married_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '52.08', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //52.08
	}

	function testUS_2008a_SemiMonthly_Single_MedIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '289.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //289.54
	}

	function testUS_2008a_SemiMonthly_Single_HighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
	}

	function testUS_2008a_SemiMonthly_Single_LowIncome_3Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '51.88', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //51.88
	}

	function testUS_2008a_SemiMonthly_Single_LowIncome_5Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 5 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '16.04', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //16.04
	}

	function testUS_2008a_SemiMonthly_Single_LowIncome_8AllowancesA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //0.00
	}

	function testUS_2008a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		$this->assertEquals( '1300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2.29', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //2.29
	}

	//
	// CA
	//
	function testCA_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '15.90', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testCA_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '8.43', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testCA_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '130.89', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// KY
	//
	function testKY_2008a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '346.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8.65', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testKY_2008a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '46.24', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testKY_2008a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '220.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// MN
	//
	function testMN_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '50.96', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testMN_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '31.07', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testMN_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '155.45', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// NE
	//
	/*
		function testNE_2008a_BiWeekly_Single_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-08'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 10 ); //Single
			$pd_obj->setStateAllowance( 0 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '99.81' ); //99.81
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '37.70' );
		}

		function testNE_2008a_BiWeekly_Married_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-08'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '99.81' ); //99.81
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '21.76' );
		}

		function testNE_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-08'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setStateAllowance( 8 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '805.51' ); //805.51
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '176.54' );
		}
	*/
	//
	// NM
	//
	function testNM_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '34.67', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testNM_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '14.22', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //14.22
	}

	function testNM_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '107.85', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// ND
	//
	function testND_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '18.01', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testND_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '10.90', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testND_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '56.48', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// OH
	//
	function testOH_2008a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '23.80', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testOH_2008a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '21.78', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testOH_2008a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '160.24', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}


	//
	// RI
	//
	function testRI_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '33.68', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //33.68
	}

	function testRI_2008a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 900.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '900.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '127.87', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //127.87
		$this->assertEquals( '30.10', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testRI_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '23.15', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testRI_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '107.01', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//
	// UT
	//
	function testUT_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'UT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '47.38', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //50.00
	}

	function testUT_2008b_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'UT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 5 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 250.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '250.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.35', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUT_2008b_BiWeekly_Single_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'UT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 5 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '829.70', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '200.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUT_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'UT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '34.77', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUT_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'UT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '184.96', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}


	//
	// VT
	//
	function testVT_2008a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '32.33', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //32.33
	}

	function testVT_2008a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '99.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //99.81
		$this->assertEquals( '20.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testVT_2008a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-08' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '805.51', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //805.51
		$this->assertEquals( '101.70', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}
}

?>