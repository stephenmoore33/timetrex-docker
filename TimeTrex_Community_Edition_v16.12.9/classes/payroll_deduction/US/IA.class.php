<?php
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
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_IA extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20240101 => [
					0 => [
							[ 'income' => 4896, 'rate' => 4.40, 'constant' => 0 ],
							[ 'income' => 24480, 'rate' => 4.82, 'constant' => 215.42 ],
							[ 'income' => 24480, 'rate' => 5.70, 'constant' => 1159.37 ],
					],
			],
			20230101 => [
					0 => [
							[ 'income' => 4800, 'rate' => 4.40, 'constant' => 0 ],
							[ 'income' => 24000, 'rate' => 4.82, 'constant' => 211.20 ],
							[ 'income' => 50000, 'rate' => 5.70, 'constant' => 1136.64 ],
							[ 'income' => 50000, 'rate' => 6.00, 'constant' => 2618.64 ],
					],
			],
			20220101 => [
					0 => [
							[ 'income' => 1743, 'rate' => 0.33, 'constant' => 0 ],
							[ 'income' => 3486, 'rate' => 0.67, 'constant' => 5.75 ],
							[ 'income' => 6972, 'rate' => 2.25, 'constant' => 17.43 ],
							[ 'income' => 15687, 'rate' => 4.14, 'constant' => 95.87 ],
							[ 'income' => 26145, 'rate' => 5.63, 'constant' => 456.67 ],
							[ 'income' => 34860, 'rate' => 5.96, 'constant' => 1045.46 ],
							[ 'income' => 52290, 'rate' => 6.25, 'constant' => 1564.87 ],
							[ 'income' => 78435, 'rate' => 7.44, 'constant' => 2654.25 ],
							[ 'income' => 78435, 'rate' => 8.53, 'constant' => 4599.44 ],
					],
			],
			20210101 => [
					0 => [
							[ 'income' => 1676, 'rate' => 0.33, 'constant' => 0 ],
							[ 'income' => 3352, 'rate' => 0.67, 'constant' => 5.53 ],
							[ 'income' => 6704, 'rate' => 2.25, 'constant' => 16.76 ],
							[ 'income' => 15084, 'rate' => 4.14, 'constant' => 92.18 ],
							[ 'income' => 25140, 'rate' => 5.63, 'constant' => 439.11 ],
							[ 'income' => 33520, 'rate' => 5.96, 'constant' => 1005.26 ],
							[ 'income' => 50280, 'rate' => 6.25, 'constant' => 1504.71 ],
							[ 'income' => 75420, 'rate' => 7.44, 'constant' => 2552.21 ],
							[ 'income' => 75420, 'rate' => 8.53, 'constant' => 4422.63 ],
					],
			],
			20200101 => [
					0 => [
							[ 'income' => 1480, 'rate' => 0.33, 'constant' => 0 ],
							[ 'income' => 2959, 'rate' => 0.67, 'constant' => 4.88 ],
							[ 'income' => 5918, 'rate' => 2.25, 'constant' => 14.79 ],
							[ 'income' => 13316, 'rate' => 4.14, 'constant' => 81.37 ],
							[ 'income' => 22193, 'rate' => 5.63, 'constant' => 387.65 ],
							[ 'income' => 29590, 'rate' => 5.96, 'constant' => 887.43 ],
							[ 'income' => 44385, 'rate' => 6.25, 'constant' => 1328.29 ],
							[ 'income' => 66578, 'rate' => 7.44, 'constant' => 2252.98 ],
							[ 'income' => 66578, 'rate' => 8.53, 'constant' => 3904.14 ],
					],
			],
			20190101 => [
					0 => [
							[ 'income' => 1333, 'rate' => 0.33, 'constant' => 0 ],
							[ 'income' => 2666, 'rate' => 0.67, 'constant' => 4.40 ],
							[ 'income' => 5331, 'rate' => 2.25, 'constant' => 13.33 ],
							[ 'income' => 11995, 'rate' => 4.14, 'constant' => 73.29 ],
							[ 'income' => 19992, 'rate' => 5.63, 'constant' => 349.18 ],
							[ 'income' => 26656, 'rate' => 5.96, 'constant' => 799.41 ],
							[ 'income' => 39984, 'rate' => 6.25, 'constant' => 1196.58 ],
							[ 'income' => 59976, 'rate' => 7.44, 'constant' => 2029.58 ],
							[ 'income' => 59976, 'rate' => 8.53, 'constant' => 3516.98 ],
					],
			],
			20060401 => [
					0 => [
							[ 'income' => 1300, 'rate' => 0.36, 'constant' => 0 ],
							[ 'income' => 2600, 'rate' => 0.72, 'constant' => 4.68 ],
							[ 'income' => 5200, 'rate' => 2.43, 'constant' => 14.04 ],
							[ 'income' => 11700, 'rate' => 4.50, 'constant' => 77.22 ],
							[ 'income' => 19500, 'rate' => 6.12, 'constant' => 369.72 ],
							[ 'income' => 26000, 'rate' => 6.48, 'constant' => 847.08 ],
							[ 'income' => 39000, 'rate' => 6.80, 'constant' => 1268.28 ],
							[ 'income' => 58500, 'rate' => 7.92, 'constant' => 2152.28 ],
							[ 'income' => 58500, 'rate' => 8.98, 'constant' => 3696.68 ],
					],
			],
	];

	var $state_options = [
			20240101 => [
					'standard_deduction' => [ 14600.00, 29200.00 ], //First is less than $80 claimed on W4 line 6. 2nd is $80 or more. -- Note: As of 01-Jan-24, W4 has been modified and the formula has been mostly changed.
					'allowance'          => 40,
					'allowance_threshold' => 80,
			],
			20230101 => [
					'standard_deduction' => [ 13850.00, 27700.00 ], //First is 0 or 1 allowances. 2nd is 2 or more allowances -- Note: As of 01-Jan-23, the federal withholding is no longer subtracted from taxable wages.
					'allowance'          => 40,
			],
			20220101 => [
					'standard_deduction' => [ 2210.00, 5450.00 ], //First is 0 or 1 allowances. 2nd is 2 or more allowances
					'allowance'          => 40,
			],
			20210101 => [
					'standard_deduction' => [ 2130.00, 5240.00 ], //First is 0 or 1 allowances. 2nd is 2 or more allowances
					'allowance'          => 40,
			],
			20200101 => [
					'standard_deduction' => [ 1880.00, 4630.00 ], //First is 0 or 1 allowances. 2nd is 2 or more allowances
					'allowance'          => 40,
			],
			20190101 => [
					'standard_deduction' => [ 1690.00, 4160.00 ],
					'allowance'          => 40,
			],
			20060401 => [ //01-Apr-06
						  'standard_deduction' => [ 1650.00, 4060.00 ],
						  'allowance'          => 40,
			],
			20060101 => [
					'standard_deduction' => [ 1500.00, 2600.00 ],
					'allowance'          => 40,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 38200, 'new_employer_rate' => 1.0 ],
			20230101 => [ 'wage_base' => 36100, 'new_employer_rate' => 1.0 ],
			20220101 => [ 'wage_base' => 34800, 'new_employer_rate' => 1.0 ],
			20210101 => [ 'wage_base' => 32400, 'new_employer_rate' => 1.0 ],
			20200101 => [ 'wage_base' => 31600, 'new_employer_rate' => 1.0 ],
			20190101 => [ 'wage_base' => 30600, 'new_employer_rate' => 1.0 ],
	];

	function isFederalTaxRequired() {
		if ( $this->getDate() >= 20230101 ) { //Federal tax is no longer subtracted from taxable wages as of this date.
			return false;
		}

		return true;
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		if ( $this->getDate() >= 20230101 ) { //Federal tax is no longer subtracted from taxable wages as of this date.
			$federal_tax = 0;
		} else {
			$federal_tax = $this->getFederalTaxPayable();
		}

		$state_deductions = $this->getStateStandardDeduction();

		$income = TTMath::sub( TTMath::sub( $annual_income, $federal_tax ), $state_deductions );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$deduction = $retarr['standard_deduction'];

		if ( $this->getDate() >= 20240101 ) { //Allowances have been changed from integer amounts to dollars.
			if ( $this->getStateAllowance() > 0 && $this->getStateAllowance() < 20 ) { //$20 is the minimum, so if its lower than this, assume they haven't filed a 2024 W4 yet, and convert it to $40 as instructed in the formula. Do allow a $0 allowance though.
				Debug::text( 'State Allowance is less than $20, converting it to $40: ' . $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->setStateAllowance( 40 );
			}

			$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
			if ( $retarr == false ) {
				return false;
			}

			if ( $this->getStateAllowance() < $retarr['allowance_threshold'] ) {
				$retval = $deduction[0];
			} else {
				$retval = $deduction[1];
			}
		} else {
			if ( $this->getStateAllowance() <= 1 ) {
				$retval = $deduction[0];
			} else {
				$retval = $deduction[1];
			}
		}

		Debug::text( 'Standard Deduction: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getDate() >= 20240101 ) {
			$retval = $this->getStateAllowance(); //Allowances have been changed from integer amounts to dollars.
		} else {
			$allowance = $retarr['allowance'];

			$retval = TTMath::mul( $allowance, $this->getStateAllowance() );
		}

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			$retval = TTMath::sub( $retval, $this->getStateAllowanceAmount() );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
