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
class PayrollDeduction_US_IL extends PayrollDeduction_US {

	var $state_options = [
			20240101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2775, //Publication: FY 2024-02 -- Effective June 2023, allowance went from 2625 to 2425 (retroactive) for 2023. 2225 is the "catch-up" amount (even though it was announced late). Customers can use the alternative tax formula, or additional withholdings to accomodate this though.
					'line_2_allowance' => 1000,
			],
			20230101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2425, //Publication: FY 2024-02 -- Effective June 2023, allowance went from 2625 to 2425 (retroactive) for 2023. 2225 is the "catch-up" amount (even though it was announced late). Customers can use the alternative tax formula, or additional withholdings to accomodate this though.
					'line_2_allowance' => 1000,
			],
			20220101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2425,
					'line_2_allowance' => 1000,
			],
			20210101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2375,
					'line_2_allowance' => 1000,
			],
			20200101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2325,
					'line_2_allowance' => 1000,
			],
			20190101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2275,
					'line_2_allowance' => 1000,
			],
			20180101 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2225,
					'line_2_allowance' => 1000,
			],
			20170701 => [
					'rate'             => 4.95,
					'line_1_allowance' => 2175,
					'line_2_allowance' => 1000,
			],
			20160101 => [
					'rate'             => 3.75,
					'line_1_allowance' => 2175,
					'line_2_allowance' => 1000,
			],
			20150101 => [
					'rate'             => 3.75,
					'line_1_allowance' => 2150,
					'line_2_allowance' => 1000,
			],
			20140101 => [
					'rate'             => 5.0,
					'line_1_allowance' => 2125,
					'line_2_allowance' => 1000,
			],
			20130101 => [
					'rate'             => 5.0,
					'line_1_allowance' => 2100,
					'line_2_allowance' => 1000,
			],
			20060101 => [
					'rate'             => 3.0,
					'line_1_allowance' => 2000,
					'line_2_allowance' => 1000,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 13590, 'new_employer_rate' => 3.175 ],
			20230101 => [ 'wage_base' => 13271, 'new_employer_rate' => 3.175 ],
			20060101 => [ 'wage_base' => 12960, 'new_employer_rate' => 3.175 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		$line_1_allowance = $this->getStateLine1AllowanceAmount();
		$line_2_allowance = $this->getStateLine2AllowanceAmount();

		$income = TTMath::sub( TTMath::sub( $annual_income, $line_1_allowance ), $line_2_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}


	function getStateLine1AllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance = $retarr['line_1_allowance'];

		$retval = TTMath::mul( (float)$this->getUserValue1(), $allowance );

		Debug::text( 'State Line 1 Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateLine2AllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance = $retarr['line_2_allowance'];

		$retval = TTMath::mul( (float)$this->getUserValue2(), $allowance );

		Debug::text( 'State Line 1 Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
			if ( $retarr == false ) {
				return false;
			}

			$rate = TTMath::div( $retarr['rate'], 100 );
			$retval = TTMath::mul( $annual_income, $rate );
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}
}

?>
