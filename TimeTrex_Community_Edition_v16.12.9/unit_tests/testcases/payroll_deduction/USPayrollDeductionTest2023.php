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
 * @group USPayrollDeductionTest2023
 */
class USPayrollDeductionTest2023 extends PHPUnit\Framework\TestCase {
	public $company_id = null;
	public $tax_table_file = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/USPayrollDeductionTest2023.csv';

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone( 'Etc/GMT+8' ); //Force to non-DST timezone. 'PST' isnt actually valid.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function mf( $amount ) {
		return TTMath::MoneyRound( $amount );
	}

	public function MatchWithinMarginOfError( $source, $destination, $error = 0 ) {
		//Source: 125.01
		//Destination: 125.00
		//Source: 124.99
		$high_water_mark = TTMath::add( $destination, $error );
		$low_water_mark = TTMath::sub( $destination, $error );

		if ( $source <= $high_water_mark && $source >= $low_water_mark ) {
			return $destination;
		}

		return $source;
	}

	//
	// January 2023
	//
	function testCSVFile() {
		$this->assertEquals( true, file_exists( $this->tax_table_file ) );

		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( $row['gross_income'] == '' && isset( $row['low_income'] ) && $row['low_income'] != '' && isset( $row['high_income'] ) && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], ( ( $row['province'] == '00' ) ? 'AK' : $row['province'] ) ); //Valid state is needed to calculate something, even for just federal numbers.
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				//Federal
				$pd_obj->setFederalFormW4Version( $row['federal_form_w4_version'] );
				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );
				$pd_obj->setFederalMultipleJobs( false ); //2020 or newer W4 settings.
				$pd_obj->setFederalClaimDependents( $row['federal_claim_dependents'] );
				$pd_obj->setFederalOtherIncome( $row['federal_other_income'] );
				$pd_obj->setFederalDeductions( $row['federal_other_deductions'] );
				$pd_obj->setFederalAdditionalDeduction( 0 );

				//State
				$pd_obj->setStateFilingStatus( $row['filing_status'] );
				$pd_obj->setStateAllowance( $row['allowance'] );

				//Some states use other values for allowance/deductions.
				switch ( $row['province'] ) {
					case 'GA':
						Debug::text( 'Setting UserValue3: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue3( $row['allowance'] );
						break;
					case 'MD':
						Debug::text( 'Setting UserValue3: 2.25', __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue3( 2.25 ); //Non-resident rate
						break;
					case 'IN':
					case 'IL':
					case 'VA':
						Debug::text( 'Setting UserValue1: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue1( $row['allowance'] );
						break;
				}

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->MatchWithinMarginOfError( $this->mf( $row['federal_deduction'] ), $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.01 ) );
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ), 'I: '. $i .' State: '. $row['province'] .' Income: '. $row['gross_income'] );
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testCompareWithLastYearCSVFile() {
		$this->assertEquals( true, file_exists( $this->tax_table_file ) );

		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'] .' Federal Filing Status: '. $row['filing_status'] .' W4 Version: '. $row['federal_form_w4_version'], __FILE__, __LINE__, __METHOD__, 10);
			if ( $row['gross_income'] == '' && isset( $row['low_income'] ) && $row['low_income'] != '' && isset( $row['high_income'] ) && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], ( ( $row['province'] == '00' ) ? 'AK' : $row['province'] ) ); //Valid state is needed to calculate something, even for just federal numbers.
				$pd_obj->setDate( strtotime( '-2 days', strtotime( $row['date'] ) ) ); //Test against the immediate previous tax tables from the day before any change. -1 year may not work if they changed mid-year.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				//Federal
				$pd_obj->setFederalFormW4Version( $row['federal_form_w4_version'] );
				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );
				$pd_obj->setFederalMultipleJobs( false ); //2020 or newer W4 settings.
				$pd_obj->setFederalClaimDependents( $row['federal_claim_dependents'] );
				$pd_obj->setFederalOtherIncome( $row['federal_other_income'] );
				$pd_obj->setFederalDeductions( $row['federal_other_deductions'] );
				$pd_obj->setFederalAdditionalDeduction( 0 );

				//State
				$pd_obj->setStateFilingStatus( $row['filing_status'] );
				$pd_obj->setStateAllowance( $row['allowance'] );


				//Some states use other values for allowance/deductions.
				switch ( $row['province'] ) {
					case 'GA':
						Debug::text( 'Setting UserValue3: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue3( $row['allowance'] );
						break;
					case 'IN':
					case 'IL':
					case 'VA':
						Debug::text( 'Setting UserValue1: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue1( $row['allowance'] );
						break;
				}

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					//$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->MatchWithinMarginOfError( $this->mf( $row['federal_deduction'] ), $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.01 ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['federal_deduction'] > 0 ) {
						$amount_diff = abs( ( $pd_obj->getFederalPayPeriodDeductions() - $row['federal_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['federal_deduction'] ) * 100 );
					}

					Debug::text( $i . '. Federal Amount: This Year: ' . $row['federal_deduction'] . ' Last Year: ' . $pd_obj->getFederalPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $amount_diff > 5 ) { //This is dollars, not percent.
						if ( $amount_diff_percent > 5 && $amount_diff < 20 ) {
							$this->assertLessThan( 1000, $amount_diff_percent, $i . '. Federal Filing Status: '. $row['filing_status'] .' Income: '. $this->mf( $row['gross_income'] ) .'  Amount: This Year: ' . $row['federal_deduction'] . ' Last Year: ' . $pd_obj->getFederalPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%' ); //Handle lower tax brackets that could change by just $20-30 but we 30-40% difference.
							$this->assertGreaterThan( 0, $amount_diff_percent );
						} else {
							$this->assertLessThan( 14, $amount_diff_percent, $i . '. Federal Filing Status: '. $row['filing_status'] .' Income: '. $this->mf( $row['gross_income'] ) .' Amount: This Year: ' . $row['federal_deduction'] . ' Last Year: ' . $pd_obj->getFederalPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%' ); //Should be slightly higher than inflation.
							$this->assertGreaterThan( 0, $amount_diff_percent );
						}
					}
				}

				if ( $row['provincial_deduction'] != '' ) {
					//$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['provincial_deduction'] > 0 && $pd_obj->getStatePayPeriodDeductions() > 0 ) {
						$amount_diff = abs( ( $pd_obj->getStatePayPeriodDeductions() - $row['provincial_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['provincial_deduction'] ) * 100 );
					}

					Debug::text( $i . '. State Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getStatePayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					if ( !in_array( $row['province'], [ '00', 'IA', 'ME', 'MN', 'MO', 'MS', 'ND', 'SC', 'CA', 'OR', 'MD', 'OH' ] ) && $amount_diff > 5 ) { //Some states had significant changes.
						$this->assertLessThan( 15, $amount_diff_percent, $i . '. State: '. $row['province'] .' Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getStatePayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%' ); //Reasonable margin of error.
						$this->assertGreaterThan( 0, $amount_diff_percent, $i . '. State: '. $row['province'] .' Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getStatePayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%' );
					}
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testTaxBracketConstants() { //Double checks to make sure there isn't an error in the tax brackets or constants.
		$cf = new CompanyFactory();
		$provinces = $cf->getOptions('province', 'US');
		if ( !empty($provinces) ) {
			$this->assertTrue( true );

			$filing_status_arr = [ 10, 20, 30, 40, 50 ];

			foreach( $provinces as $province => $province_name ) {
				if ( in_array( $province, [ 'AR', 'NC', 'SC', 'UT', 'WI' ] ) ) { // States with non-standard tax brackets.
					continue;
				}

				$pd_obj = new PayrollDeduction( 'US', $province );
				$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
				$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly
				$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

				//2020 or newer W4
				$pd_obj->setFederalFormW4Version( 2020 );
				$pd_obj->setFederalMultipleJobs( false );
				$pd_obj->setFederalClaimDependents( 0 );
				$pd_obj->setFederalOtherIncome( 0 );
				$pd_obj->setFederalDeductions( 0 );
				$pd_obj->setFederalAdditionalDeduction( 0 );

				$pd_obj->setYearToDateSocialSecurityContribution( 0 );

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setGrossPayPeriodIncome( 1000 );

				foreach( $filing_status_arr as $filing_status ) {
					$pd_obj->setFederalFilingStatus( $filing_status );
					$pd_obj->setStateFilingStatus( $filing_status );
					$pd_obj->setDistrictFilingStatus( $filing_status );

					$new_pd_obj = $pd_obj->getData();
					foreach( $new_pd_obj->income_tax_rates as $region_name => $region_tax_brackets ) {
						$cumulative_constant = 0;

						foreach( $region_tax_brackets as $key => $tax_bracket ) {
							Debug::text( 'State: '. $province .' Filing Status: '. $filing_status .' Bracket: '. $key .' Prev Income: '. $tax_bracket['prev_income']  .' Income: '. $tax_bracket['income'] .' Rate: '. $tax_bracket['rate'] .' Constant: '. $tax_bracket['constant'] .' Cummulative Constant: '. $cumulative_constant .' Diff: '. TTMath::sub( $tax_bracket['constant'], $cumulative_constant ), __FILE__, __LINE__, __METHOD__, 10 );

							$tmp_constant = TTMath::mul( TTMath::sub( $tax_bracket['income'], $tax_bracket['prev_income'] ), $tax_bracket['rate'] );

							$this->assertEqualsWithDelta( $tax_bracket['constant'], $cumulative_constant, 2.50, 'State: '. $province .' Filing Status: '. $filing_status .' Bracket: '. $key .' Prev Income: '. $tax_bracket['prev_income']  .' Income: '. $tax_bracket['income'] .' Rate: '. $tax_bracket['rate'] .' Constant: '. $tax_bracket['constant'] .' Cummulative Constant: '. $cumulative_constant .' Diff: '. TTMath::sub( $tax_bracket['constant'], $cumulative_constant ));

							$cumulative_constant = TTMath::add( $cumulative_constant, $tmp_constant ); //Must come after the above assertEqualsWithDelta as its "delayed" by one bracket always.
						}
					}
				}
			}
		} else {
			$this->assertTrue( false, 'No US States to test!' );
		}
	}

	function testUS_2023_Test2019W4() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2097.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_TestAdditionalDeductionExcessA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 3000 ); //Exceeds Gross Pay Period Income below. Should be ignored.

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000 );

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1277.62', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_TestAdditionalDeductionExcessB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 700 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000 );

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '867.62', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Would be 174.42 without the additional deduction. Test max out of additional deduction at highest tax rate plus buffer.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_TestAdditionalDeductionExcessC() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 250 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 600 );

		$this->assertEquals( '600.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '256.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Would be 11.73 without the additional deduction. Test max out of additional deduction at highest tax rate plus buffer.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_TestAdditionalDeductionExcessD() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 250 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 100 );

		$this->assertEquals( '100.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '55.50', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Would be 0.00 without the additional deduction. Test max out of additional deduction at highest tax rate plus buffer.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4AdditionalDeductionWithLowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 10 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1 );

		$this->assertEquals( '1.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.56', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //10.00
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4AdditionalDeductionWithHighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 10 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2107.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //2227.24
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4AdditionalDeductionWithHighIncomeAndNonPeriodic() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly
		$pd_obj->setFormulaType( 20 ); //Periodic

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 10 ); //Non-Perodic formula should ignore additional withholding.

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2097.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4SingleJob1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2097.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //2228.90
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4TwoJobs1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2731.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4OneJobWithDependents1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 2500 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2001.67', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4OneJobWithOtherIncome1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 10000 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2232.44', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4OneJobWithDeductions1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 5000 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2030.65', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_Test2020W4OneJobWithAdditionalDeduction1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2097.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //2128.90 + 100
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket1a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '158.62', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 214 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket1b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '158.62', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 166 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket2a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '369.85', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 395 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket2b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married Filing Jointly
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '274.66', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 291 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket2c() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '335.10', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 362 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2020W4WageBracket3a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '214.37', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 236 based on 2023 W4 standard withholding tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket1a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '188.38', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 212 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket1b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '168.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 177 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket2a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '369.85', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 395 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket2b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married Filing Jointly
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '274.66', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 291 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket2c() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '335.10', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 362 based on tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	//Examples from 15-T publication.
	function testUS_2023_Test2019W4WageBracket3a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( '1925.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '250.75', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be about 236 based on 2023 W4 standard withholding tax tables.
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_ID_2023a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 4 ); //2 + 2

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1212 );

		$this->assertEquals( '1212.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '4.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_ID_2023a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 4 ); //2 + 2

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '7.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_LA_2023a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );
		$pd_obj->setUserValue3( 2 ); //Dependent

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 700 );

		$this->assertEquals( '700.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '18.22', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_LA_2023a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 2 );
		$pd_obj->setUserValue3( 3 ); //Dependent

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4600 );

		$this->assertEquals( '4600.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '142.25', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023a_Test1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 576.923 );

		$this->assertEquals( '576.92', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '37.50', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '36.55', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
		$this->assertEquals( '35.77', $this->mf( $pd_obj->getEmployeeSocialSecurity() ) );
	}

	function testUS_AR_2023a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2127 );

		$this->assertEquals( '2127.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '39.07', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //Should be 56.83, but they round a lot.
	}

	function testUS_AR_2023a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 8333.33 );

		$this->assertEquals( '8333.33', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '380.30', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_MS_2023a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 ); //Exemption Claimed Amount

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1890 );

		$this->assertEquals( '1890.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '71.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //80.85
	}

	function testUS_MS_2023a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 18000 ); //Exemption Claimed Amount

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1890 );

		$this->assertEquals( '1890.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '36.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //46.23
	}

	//
	// US Social Security
	//
	function testUS_2023a_SocialSecurity() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '62.00', $this->mf( $pd_obj->getEmployeeSocialSecurity() ) );
		$this->assertEquals( '62.00', $this->mf( $pd_obj->getEmployerSocialSecurity() ) );
	}

	function testUS_2023a_SocialSecurity_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );


		$pd_obj->setYearToDateSocialSecurityContribution( ( $pd_obj->getSocialSecurityMaximumContribution() - 1 ) ); //7347

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeSocialSecurity() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerSocialSecurity() ) );
	}

	function testUS_2023a_Medicare() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployerMedicare() ) );
	}

	function testUS_2023a_Additional_MedicareA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 199000.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployerMedicare() ) );
		$this->assertEquals( '200000.00', $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ) );
	}

	function testUS_2023a_Additional_MedicareB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 199500.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '19.00', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployerMedicare() ) );
		$this->assertEquals( '200000.00', $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ) );
	}

	function testUS_2023a_Additional_MedicareC() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 500000.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '23.50', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployerMedicare() ) );
		$this->assertEquals( '200000.00', $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ) );
	}

	function testUS_2023a_Additional_MedicareD() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setGrossPayPeriodIncome( 500000.00 );

		$this->assertEquals( '500000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '9950.00', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '7250.00', $this->mf( $pd_obj->getEmployerMedicare() ) );
		$this->assertEquals( '200000.00', $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ) );
	}

	function testUS_2023a_FederalUI_NoState() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '6.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2023a_FederalUI_NoState_CustomRate() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 1.0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '10.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2023a_FederalUI_NoState_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 0 );
		$pd_obj->setStateUIWageBase( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 419 ); //420
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2023a_FederalUI_State_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 419 ); //420
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2023a_StateUI() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 419 ); //420
		$pd_obj->setYearToDateStateUIContribution( 0.00 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
		$this->assertEquals( '35.10', $this->mf( $pd_obj->getStateEmployerUI() ) );
	}

	function testUS_2023a_StateUI_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 10500 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 419 ); //420
		$pd_obj->setYearToDateStateUIContribution( 367.55 ); //368.55

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getStateEmployerUI() ) );
	}

	function testUS_RI_2023_StateUI_WageBase() { //Variable wage base limits depending on experience rate.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 1.00 );
		$this->assertEquals( '28200', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 9.48 );
		$this->assertEquals( '28200', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 9.49 );
		$this->assertEquals( '29700', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 15.00 );
		$this->assertEquals( '29700', $pd_obj->getStateUIWageBase() );
	}

	function testUS_NE_2023_StateUI_WageBase() { //Variable wage base limits depending on experience rate.
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NE' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );
		$pd_obj->setFederalUIRate( 6.0 );

		$pd_obj->setStateUIRate( 5.00 );
		$this->assertEquals( '9000', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 5.39 );
		$this->assertEquals( '9000', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 5.40 );
		$this->assertEquals( '24000', $pd_obj->getStateUIWageBase() );

		$pd_obj->setStateUIRate( 15.00 );
		$this->assertEquals( '24000', $pd_obj->getStateUIWageBase() );
	}

	function testUS_2023a_State_UI_WageBase() {
		$cf = new CompanyFactory();
		$provinces = $cf->getOptions('province', 'US');
		if ( !empty($provinces) ) {
			asort( $provinces );
			$i = 1;
			foreach( $provinces as $state => $name ) {
				$pd_obj = new PayrollDeduction( 'US', strtoupper( $state ) );
				$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
				$this->assertGreaterThan( 0, $this->mf( $pd_obj->getStateUIWageBase() ), 'State: '. $name .' ('. $state .') Wage Base: '. $pd_obj->getStateUIWageBase() );
				//echo $i . '. State: '. $name .' ('. $state .') UI Wage Base: '. $pd_obj->getStateUIWageBase() ."\n";
				$i++;
			}
		}

		$pd_obj = new PayrollDeduction( 'US', 'AK' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setStateUIWageBase( 1.13 );
		$this->assertEquals( '1.13', $this->mf( $pd_obj->getStateUIWageBase() ), 'Forcing wage base to: 1.13' );
	}

	function testUS_2023_MD1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MD' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setStateFilingStatus( 10 );
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setUserValue3( 2.25 ); //County Rate

		$pd_obj->setGrossPayPeriodIncome( 4000 );

		$this->assertEquals( '273.69', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_MD2() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MD' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );     //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setStateFilingStatus( 10 );
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setUserValue3( 3.20 ); //County Rate

		$pd_obj->setGrossPayPeriodIncome( 100000 );

		$this->assertEquals( '8881.64', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testUS_2023_MD3() {
		Debug::text( 'US - SemiMonthly - Beginning of 2023 01-Jan-2023: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MD' );
		$pd_obj->setDate( strtotime( '01-Jan-2023' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );     //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setStateFilingStatus( 10 );
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setUserValue3( 3.05 ); //County Rate - Allegany County

		$pd_obj->setGrossPayPeriodIncome( 100000 );

		$this->assertEquals( '8731.78', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}
}

?>