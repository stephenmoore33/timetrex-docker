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
class PayrollDeduction_US_MS extends PayrollDeduction_US {
	/*
		protected $state_filing_status_options = array(
															10 => 'Single',
															20 => 'Married - Spouse Works',
															30 => 'Married - Spouse does not Work',
															40 => 'Head of Household',
										);
	*/

	var $state_income_tax_rate_options = [
			20240101 => [
					0 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.7, 'constant' => 0 ], //Only taxed on $10K and over.
					],
			],
			20230101 => [
					0 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 5.0, 'constant' => 0 ], //Only taxed on $10K and over.
					],
			],
			20210101 => [
					0 => [
							[ 'income' => 4000, 'rate' => 0.0, 'constant' => 0 ],
							[ 'income' => 5000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 30 ],
							[ 'income' => 10000, 'rate' => 5.0, 'constant' => 230 ],
					],
			],
			20200101 => [
					0 => [
							[ 'income' => 3000, 'rate' => 0.0, 'constant' => 0 ],
							[ 'income' => 5000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 60 ],
							[ 'income' => 10000, 'rate' => 5.0, 'constant' => 260 ],
					],
			],
			20190101 => [
					0 => [
							[ 'income' => 2000, 'rate' => 0.0, 'constant' => 0 ],
							[ 'income' => 5000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 90 ],
							[ 'income' => 10000, 'rate' => 5.0, 'constant' => 290 ],
					],
			],
			20060101 => [
					0 => [
							[ 'income' => 5000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 150 ],
							[ 'income' => 10000, 'rate' => 5.0, 'constant' => 350 ],
					],
			],
	];

	var $state_options = [
			20060101 => [
					'standard_deduction' => [
							'10' => 2300,
							'20' => 2300,
							'30' => 4600,
							'40' => 3400,
					],
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 14000, 'new_employer_rate' => null ], //New employer rate varies.
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowance(); //This is Excemptions Claimed amount.

		$income = TTMath::sub( TTMath::sub( $annual_income, $state_deductions ), $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( !isset( $retarr['standard_deduction'][$this->getStateFilingStatus()] ) ) {
			return false;
		}

		$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

}

?>
