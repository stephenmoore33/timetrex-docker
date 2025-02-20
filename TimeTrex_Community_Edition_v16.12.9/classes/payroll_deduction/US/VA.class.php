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
class PayrollDeduction_US_VA extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20060101 => [
					0 => [
							[ 'income' => 3000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 5000, 'rate' => 3, 'constant' => 60 ],
							[ 'income' => 17000, 'rate' => 5, 'constant' => 120 ],
							[ 'income' => 17000, 'rate' => 5.75, 'constant' => 720 ],
					],
			],
	];

	var $state_options = [
			20240401 => [ //01-Apr-2024
						  'standard_deduction' => 8500, //Single=8500, Married=17000 -- Haven't implemented married this yet.
						  'allowance'          => 930,
						  'age65_allowance'    => 800,
			],
			20220901 => [ //01-Oct-2022
					'standard_deduction' => 8000, //Single=8000, Married=16000 -- Haven't implemented married this yet.
					'allowance'          => 930,
					'age65_allowance'    => 800,
			],
			20190101 => [
					'standard_deduction' => 4500,
					'allowance'          => 930,
					'age65_allowance'    => 800,
			],
			20080101 => [
					'standard_deduction' => 3000,
					'allowance'          => 930,
					'age65_allowance'    => 800,
			],
			20060101 => [
					'standard_deduction' => 3000,
					'allowance'          => 900,
					'age65_allowance'    => 800,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 8000, 'new_employer_rate' => 2.5 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_standard_deduction = $this->getStateStandardDeductionAmount();
		$state_allowance = $this->getStateAllowanceAmount();
		$state_age65_allowance = $this->getStateAge65AllowanceAmount();

		$income = TTMath::sub( TTMath::sub( TTMath::sub( $annual_income, $state_standard_deduction ), $state_allowance ), $state_age65_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeductionAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$retval = $retarr['standard_deduction'];

		Debug::text( 'State Standard Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = TTMath::mul( (float)$this->getUserValue1(), $allowance_arr );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAge65AllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['age65_allowance'];

		$retval = TTMath::mul( (float)$this->getUserValue2(), $allowance_arr );

		Debug::text( 'State Age65 Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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
