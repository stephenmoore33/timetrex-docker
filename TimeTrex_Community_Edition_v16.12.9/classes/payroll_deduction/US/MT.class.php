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
class PayrollDeduction_US_MT extends PayrollDeduction_US {
	//As of 2024, filing statuses match Federal.

	var $state_income_tax_rate_options = [
			20240101 => [
				//As of 2024, filing statuses match Federal.
				10 => [ //Single or Married Filing Separately
						[ 'income' => 14600, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 35100, 'rate' => 4.7, 'constant' => 0 ],
						[ 'income' => 35100, 'rate' => 5.9, 'constant' => 964 ]
				],
				20 => [ //Married Filing Jointly
						[ 'income' => 29200, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 70200, 'rate' => 4.7, 'constant' => 0 ],
						[ 'income' => 70200, 'rate' => 5.9, 'constant' => 1927 ]
				],
				40 => [ //Head of Household
						[ 'income' => 21900, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 52650, 'rate' => 4.7, 'constant' => 0 ],
						[ 'income' => 52650, 'rate' => 5.9, 'constant' => 1445 ]
				],
			],
			20230101 => [
					0 => [
							[ 'income' => 7630, 'rate' => 1.8, 'constant' => 0 ],
							[ 'income' => 16350, 'rate' => 4.4, 'constant' => 137 ],
							[ 'income' => 130790, 'rate' => 6.0, 'constant' => 521 ],
							[ 'income' => 130790, 'rate' => 6.6, 'constant' => 7387 ],
					],
			],
			20060101 => [
					0 => [
							[ 'income' => 7000, 'rate' => 1.8, 'constant' => 0 ],
							[ 'income' => 15000, 'rate' => 4.4, 'constant' => 126 ],
							[ 'income' => 120000, 'rate' => 6.0, 'constant' => 478 ],
							[ 'income' => 120000, 'rate' => 6.6, 'constant' => 6778 ],
					],
			],
	];

	var $state_options = [
			//No longer used after 01-Jan-2024
			20230101 => [
					'allowance' => 2070,
			],
			20060101 => [
					'allowance' => 1900,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 43000, 'new_employer_rate' => null ], //New employer rate varies
			20230101 => [ 'wage_base' => 40500, 'new_employer_rate' => null ], //New employer rate varies
			20220101 => [ 'wage_base' => 38100, 'new_employer_rate' => null ], //New employer rate varies
			20210101 => [ 'wage_base' => 35300, 'new_employer_rate' => null ],
			20200101 => [ 'wage_base' => 34100, 'new_employer_rate' => null ],
			20190101 => [ 'wage_base' => 33000, 'new_employer_rate' => null ],
	];

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = TTMath::sub( $annual_income, $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateAllowanceAmount() {
		if ( $this->getDate() >= 20240101 ) { //Allowances discontined with W4 form change in 2024.
			return 0;
		} else {
			$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
			if ( $retarr == false ) {
				return false;
			}

			$allowance_arr = $retarr['allowance'];

			$retval = TTMath::mul( $this->getStateAllowance(), $allowance_arr );

			Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}
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
