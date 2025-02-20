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
class PayrollDeduction_US_SC extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20240101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 3460, 'rate' => 0, 'constant' => 0 ],
						   [ 'income' => 17330, 'rate' => 3, 'constant' => 103.80 ],
						   [ 'income' => 17330, 'rate' => 6.4, 'constant' => 693.02 ],
					],
			],
			20230101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 3330, 'rate' => 0, 'constant' => 0 ],
						   [ 'income' => 16680, 'rate' => 3, 'constant' => 99.90 ],
						   [ 'income' => 16680, 'rate' => 6.5, 'constant' => 683.70 ],
					],
			],
			20220101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2980, 'rate' => 0.2, 'constant' => 0 ],
						   [ 'income' => 5960, 'rate' => 3, 'constant' => 83.44 ],
						   [ 'income' => 8940, 'rate' => 4, 'constant' => 143.04 ],
						   [ 'income' => 11920, 'rate' => 5, 'constant' => 232.44 ],
						   [ 'income' => 14900, 'rate' => 6, 'constant' => 351.64 ],
						   [ 'income' => 14900, 'rate' => 7, 'constant' => 500.64 ],
					],
			],
			20210101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2800, 'rate' => 0.5, 'constant' => 0 ],
						   [ 'income' => 5610, 'rate' => 3, 'constant' => 70.00 ],
						   [ 'income' => 8410, 'rate' => 4, 'constant' => 126.10 ],
						   [ 'income' => 11220, 'rate' => 5, 'constant' => 210.20 ],
						   [ 'income' => 14030, 'rate' => 6, 'constant' => 322.40 ],
						   [ 'income' => 14030, 'rate' => 7, 'constant' => 462.70 ],
					],
			],
			20200101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2620, 'rate' => 0.8, 'constant' => 0 ],
						   [ 'income' => 5240, 'rate' => 3, 'constant' => 57.64 ],
						   [ 'income' => 7860, 'rate' => 4, 'constant' => 110.04 ],
						   [ 'income' => 10490, 'rate' => 5, 'constant' => 188.64 ],
						   [ 'income' => 13110, 'rate' => 6, 'constant' => 293.54 ],
						   [ 'income' => 13110, 'rate' => 7, 'constant' => 424.64 ],
					],
			],
			20190101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2450, 'rate' => 1.1, 'constant' => 0 ],
						   [ 'income' => 4900, 'rate' => 3, 'constant' => 46.55 ],
						   [ 'income' => 7350, 'rate' => 4, 'constant' => 95.55 ],
						   [ 'income' => 9800, 'rate' => 5, 'constant' => 169.05 ],
						   [ 'income' => 12250, 'rate' => 6, 'constant' => 267.05 ],
						   [ 'income' => 12250, 'rate' => 7, 'constant' => 389.55 ],
					],
			],
			20180101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2290, 'rate' => 1.4, 'constant' => 0 ],
						   [ 'income' => 4580, 'rate' => 3, 'constant' => 36.64 ],
						   [ 'income' => 6870, 'rate' => 4, 'constant' => 82.44 ],
						   [ 'income' => 9160, 'rate' => 5, 'constant' => 151.14 ],
						   [ 'income' => 11450, 'rate' => 6, 'constant' => 242.74 ],
						   [ 'income' => 11450, 'rate' => 7, 'constant' => 357.24 ],
					],
			],
			20170101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2140, 'rate' => 1.7, 'constant' => 0 ],
						   [ 'income' => 4280, 'rate' => 3, 'constant' => 27.82 ],
						   [ 'income' => 6420, 'rate' => 4, 'constant' => 70.62 ],
						   [ 'income' => 8560, 'rate' => 5, 'constant' => 134.82 ],
						   [ 'income' => 10700, 'rate' => 6, 'constant' => 220.42 ],
						   [ 'income' => 10700, 'rate' => 7, 'constant' => 327.42 ],
					],
			],
			20060101 => [
					0 => [ //Uses Subtraction method constants.
						   [ 'income' => 2000, 'rate' => 2, 'constant' => 0 ],
						   [ 'income' => 4000, 'rate' => 3, 'constant' => 20 ],
						   [ 'income' => 6000, 'rate' => 4, 'constant' => 60 ],
						   [ 'income' => 8000, 'rate' => 5, 'constant' => 120 ],
						   [ 'income' => 10000, 'rate' => 6, 'constant' => 200 ],
						   [ 'income' => 10000, 'rate' => 7, 'constant' => 300 ],
					],
			],
	];

	var $state_options = [
			20240101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 6925,
					'allowance'                  => 4610,
			],
			20230101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 6475,
					'allowance'                  => 4310,
			],
			20220101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 4580,
					'allowance'                  => 2750,
			],
			20210101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 4200,
					'allowance'                  => 2670,
			],
			20200101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 3820,
					'allowance'                  => 2590,
			],
			20190101 => [
					'standard_deduction_rate'    => 10, //Standard Deduction Rate of 10%
					'standard_deduction_maximum' => 3470,
					'allowance'                  => 2510,
			],
			20180101 => [
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 3150,
					'allowance'                  => 2440,
			],
			20170101 => [
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 2860,
					'allowance'                  => 2370,
			],
			20060101 => [
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 2600,
					'allowance'                  => 2300,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 14000, 'new_employer_rate' => 0.49 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deductions = $this->getStateStandardDeduction();
		$allowance = $this->getStateAllowanceAmount();

		$income = TTMath::sub( TTMath::sub( $annual_income, $standard_deductions ), $allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$maximum = $retarr['federal_tax_maximum'][$this->getStateFilingStatus()];

		Debug::text( 'Maximum State allowed Federal Tax: ' . $maximum, __FILE__, __LINE__, __METHOD__, 10 );

		return $maximum;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getStateAllowance() == 0 ) {
			$deduction = 0;
		} else {
			$rate = TTMath::div( $retarr['standard_deduction_rate'], 100 );
			$deduction = TTMath::mul( $this->getAnnualTaxableIncome(), $rate );
			if ( $deduction > $retarr['standard_deduction_maximum'] ) {
				$deduction = $retarr['standard_deduction_maximum'];
			}
		}

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance = $retarr['allowance'];

		$retval = TTMath::mul( $this->getStateAllowance(), $allowance );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );

			$retval = TTMath::sub( TTMath::mul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
