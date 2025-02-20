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
class PayrollDeduction_US_MD_ALL extends PayrollDeduction_US_MD {

	var $district_options = [
			20180601 => [ //01-Jun-2018
						  'standard_deduction_rate'    => 15,
						  'standard_deduction_minimum' => 1500,
						  'standard_deduction_maximum' => 2250,
						  'allowance'                  => 3200,
			],

			//01-Jan-12: No change.
			//01-Jan-11: No change.
			//01-Jan-10: No change.
			//01-Jan-09: No change.
			20080701 => [
					'standard_deduction_rate'    => 15,
					'standard_deduction_minimum' => 1500,
					'standard_deduction_maximum' => 2000,
					'allowance'                  => 3200,
			],
			20060101 => [
					'standard_deduction_rate'    => 15,
					'standard_deduction_minimum' => 1500,
					'standard_deduction_maximum' => 2000,
					'allowance'                  => 2400,
			],
	];

	function getDistrictAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deduction = $this->getDistrictStandardDeductionAmount();
		$district_allowance = $this->getDistrictAllowanceAmount();

		$income = TTMath::sub( TTMath::sub( $annual_income, $standard_deduction ), $district_allowance );

		Debug::text( 'District Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getDistrictStandardDeductionAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->district_options );
		if ( $retarr == false ) {
			return false;
		}

		$rate = TTMath::div( $retarr['standard_deduction_rate'], 100 );

		$deduction = TTMath::mul( $this->getAnnualTaxableIncome(), $rate );

		if ( $deduction < $retarr['standard_deduction_minimum'] ) {
			$retval = $retarr['standard_deduction_minimum'];
		} else if ( $deduction > $retarr['standard_deduction_maximum'] ) {
			$retval = $retarr['standard_deduction_maximum'];
		} else {
			$retval = $deduction;
		}

		Debug::text( 'District Standard Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getDistrictAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->district_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = TTMath::mul( $this->getDistrictAllowance(), $allowance_arr );

		Debug::text( 'District Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getDistrictTaxPayable() {
		$annual_income = $this->getDistrictAnnualTaxableIncome();

		$rate = TTMath::div( (float)$this->getUserValue1(), 100 );

		$retval = TTMath::mul( $annual_income, $rate );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'District Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
