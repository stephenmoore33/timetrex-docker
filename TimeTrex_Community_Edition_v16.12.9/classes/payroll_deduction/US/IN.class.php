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
class PayrollDeduction_US_IN extends PayrollDeduction_US {

	var $state_options = [
			20240101 => [
					'rate'                => 3.05,
					'allowance'           => 1000, //Deduction Constant Table A for 1 allowance * 12 months.
					'dependant_allowance' => 1500, //Deduction Constant Table B for 1 allowance * 12 months.
					'adopted_allowance'   => 3000, //Deduction Constant Table C for 1 allowance * 12 months. -- Haven't added this into the formula calculation yet.
			],
			20230101 => [
					'rate'                => 3.15, //This was changed in September 2023 and back-dated to 2023-01-01.
					'allowance'           => 1000, //Deduction Constant Table A for 1 allowance * 12 months.
					'dependant_allowance' => 1500, //Deduction Constant Table B for 1 allowance * 12 months.
					'adopted_allowance'   => 3000, //Deduction Constant Table C for 1 allowance * 12 months. -- Haven't added this into the formula calculation yet.
			],
			20170101 => [
					'rate'                => 3.23,
					'allowance'           => 1000, //Deduction Constant Table A for 1 allowance * 12 months.
					'dependant_allowance' => 1500, //Deduction Constant Table B for 1 allowance * 12 months.
			],
			20150101 => [
					'rate'                => 3.3,
					'allowance'           => 1000,
					'dependant_allowance' => 1500,
			],
			20060101 => [
					'rate'                => 3.4,
					'allowance'           => 1000,
					'dependant_allowance' => 1500,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 9500, 'new_employer_rate' => 2.5 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();
		$state_dependant_allowance = $this->getStateDependantAllowanceAmount();

		$income = TTMath::sub( TTMath::sub( $annual_income, $state_allowance ), $state_dependant_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
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

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['dependant_allowance'];

		$retval = TTMath::mul( (float)$this->getUserValue2(), $allowance_arr );

		Debug::text( 'State Dependant Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
