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
class PayrollDeduction_US_WV extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20230301 => [
					10 => [ //Single, Head of Household, Married with nonemployed spouse
							[ 'income' => 10000, 'rate' => 2.36, 'constant' => 0 ],
							[ 'income' => 25000, 'rate' => 3.15, 'constant' => 236 ],
							[ 'income' => 40000, 'rate' => 3.54, 'constant' => 708.50 ],
							[ 'income' => 60000, 'rate' => 4.72, 'constant' => 1239.50 ],
							[ 'income' => 60000, 'rate' => 5.12, 'constant' => 2183.50 ],
					],
					20 => [ //Married filing jointly, Both working, individual earning wages from two jobs.
							[ 'income' => 7500, 'rate' => 2.36, 'constant' => 0 ],
							[ 'income' => 18750, 'rate' => 3.15, 'constant' => 177 ],
							[ 'income' => 30000, 'rate' => 3.54, 'constant' => 531.38 ],
							[ 'income' => 45000, 'rate' => 4.72, 'constant' => 929.63 ],
							[ 'income' => 45000, 'rate' => 5.12, 'constant' => 1637.63 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 10000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 25000, 'rate' => 4.0, 'constant' => 300 ],
							[ 'income' => 40000, 'rate' => 4.5, 'constant' => 900 ],
							[ 'income' => 60000, 'rate' => 6.0, 'constant' => 1575 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2775 ],
					],
					20 => [
							[ 'income' => 6000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 15000, 'rate' => 4.0, 'constant' => 180 ],
							[ 'income' => 24000, 'rate' => 4.5, 'constant' => 540 ],
							[ 'income' => 36000, 'rate' => 6.0, 'constant' => 945 ],
							[ 'income' => 36000, 'rate' => 6.5, 'constant' => 1665 ],
					],
			],
	];

	var $state_options = [
			20060101 => [
					'allowance' => 2000,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 9521, 'new_employer_rate' => 2.7 ],
			20220101 => [ 'wage_base' => 9000, 'new_employer_rate' => 2.7 ],
			20060101 => [ 'wage_base' => 12000, 'new_employer_rate' => 2.7 ],
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
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = TTMath::mul( $this->getStateAllowance(), $allowance_arr );

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
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
