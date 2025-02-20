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
 * @group USPayrollDeductionTest2007
 */
class USPayrollDeductionTest2007 extends PHPUnit\Framework\TestCase {
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
	// 2007
	//
	//
	//
	function testUS_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
	}

	function testUS_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.15', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //56.15
	}


	function testUS_2007a_BiWeekly_Married_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '30.00', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testUS_2007a_SemiMonthly_Single_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '96.63', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //96.63
	}

	function testUS_2007a_SemiMonthly_Married_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '52.50', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //52.50
	}

	function testUS_2007a_SemiMonthly_Single_MedIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '292.79', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //292.72
	}

	function testUS_2007a_SemiMonthly_Single_HighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
	}

	function testUS_2007a_SemiMonthly_Single_LowIncome_3Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.13', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //54.13
	}

	function testUS_2007a_SemiMonthly_Single_LowIncome_5Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 5 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '18.13', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //18.13
	}

	function testUS_2007a_SemiMonthly_Single_LowIncome_8AllowancesA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //0.00
	}

	function testUS_2007a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		$this->assertEquals( '1300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '5.63', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //5.63
	}

	//
	// OK
	//
	function testOK_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '41.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //41.00
	}

	function testOK_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '25.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //25.00
	}

	function testOK_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '175.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //175.00
	}

	//
	// NM
	//
	function testNM_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '35.92', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //35.92
	}

	function testNM_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '14.22', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //14.22
	}

	function testNM_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '114.04', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //114.04
	}

	//
	// NE
	//
	/*
		function testNE_2007a_BiWeekly_Single_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-07'));
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
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '100.73' ); //100.73
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.97' ); //38.97
		}

		function testNE_2007a_BiWeekly_Married_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '100.73' ); //100.73
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '25.33' ); //25.33
		}

		function testNE_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-07'));
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
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '812.20' ); //812.20
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '189.98' ); //189.98
		}
	*/
	//
	// MN
	//
	function testMN_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '51.28', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //51.28
	}

	function testMN_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '31.48', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //31.48
	}

	function testMN_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '158.59', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //158.59
	}

	//
	// HI
	//
	function testHI_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '57.92', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //57.92
	}

	function testHI_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '42.99', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //42.99
	}

	function testHI_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '238.45', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //238.45
	}

	//
	// CO
	//
	function testCO_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '43.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //42.92
	}

	function testCO_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '27.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //27.42
	}

	function testCO_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '119.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //118.84
	}

	//
	// MI
	//
	function testMI_2007a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '33.90', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //33.90
	}

	function testMI_2007a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '23.70', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //23.70
	}

	//
	// CA
	//
	function testCA_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '16.63', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //16.63
	}

	function testCA_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '8.78', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //8.78
	}

	function testCA_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '137.85', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //137.85
	}

	//
	// KY
	//
	function testKY_2007a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '346.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8.74', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //8.74
	}

	function testKY_2007a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '46.35', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //46.35
	}

	function testKY_2007a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '220.13', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //220.13
	}

	//
	// MO
	//
	function testMO_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '30.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.00
	}

	function testMO_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.15', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //56.15
		$this->assertEquals( '33.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //33.00
	}

	function testMO_2007a_SemiMonthly_Married_HighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '588.02', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //588.02
		$this->assertEquals( '202.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //202.00
	}

	//
	// NC
	//
	function testNC_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '50.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //50.00
	}

	function testNC_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '51.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //51.00
	}

	function testNC_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '229.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //229.00
	}

	//
	// ND
	//
	function testND_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '18.09', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //18.09
	}

	function testND_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '11.15', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //11.15
	}

	function testND_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '59.02', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //59.02
	}

	//
	// OR
	//
	function testOR_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '66.79', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //66.77
	}

	function testOR_2007a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 ); //3 - Should switch to married tax tables.

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '54.10', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //54.08
	}

	function testOR_2007a_BiWeekly_Single_LowIncomeC() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 3 ); //3 - Should switch to married tax tables.

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '40.04', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //40.04
	}

	function testOR_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '46.26', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //46.26
	}

	function testOR_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '253.68', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //253.68
	}

	//
	// RI
	//
	function testRI_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '33.68', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //33.68
	}

	function testRI_2007a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '129.37', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //129.37
		$this->assertEquals( '30.99', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.98
	}

	function testRI_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '23.29', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //23.58
	}

	function testRI_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '111.10', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //121.11
	}

	//
	// VT
	//
	function testVT_2007a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '32.33', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //32.33
	}

	function testVT_2007a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //100.73
		$this->assertEquals( '20.22', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //20.22
	}

	function testVT_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
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
		$this->assertEquals( '812.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //812.20
		$this->assertEquals( '106.05', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //106.05
	}

	//
	// AL
	//
	/*
		function testAL_2007a_BiWeekly_Single_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 10 ); // State "S"
			$pd_obj->setUserValue2( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.35' ); //120.35
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '35.33' ); //34.37
		}

		function testAL_2007a_BiWeekly_Single_MediumIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 10 ); //Single
			$pd_obj->setUserValue2( 0 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 2083 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.20' );
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '74.87' );
		}

		function testAL_2007a_BiWeekly_Married_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.35' ); //120.35
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '25.71' ); //23.79
		}

		function testAL_2007a_BiWeekly_Married_MediumIncome() {
			Debug::text('US - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 0 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 2083 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.20' );
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '52.78' );
		}

		function testAL_2007a_SemiMonthly_Married_HighIncome_8Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 8 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '812.20' ); //812.20
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '135.22' ); //133.14
		}

		function testAL_2007a_SemiMonthly_Married_HighIncome_2Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-07'));
			$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

			$pd_obj->setFederalFilingStatus( 20 ); //Married
			$pd_obj->setFederalAllowance( 2 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 2 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 435.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '435.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '15.04' ); //15.04
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '10.37' ); //9.41
		}
	*/
}

?>