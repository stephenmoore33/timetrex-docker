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
class PayrollDeduction_US_CO extends PayrollDeduction_US {
	var $state_options = [
			20240101 => [
					'allowance' => [ //These are not from the DR 0004 certificate, they are from the DR 1098 when not using the DR 0004. Based on the IRS form W-4 Step 1(c)
									 '10' => 5000, //Single
									 '20' => 10000, //Married Filing Jointly
									 '40' => 5000, //Head of Household
					],
					'rate'      => 4.40, //Flat tax rate.
			],
			20230101 => [
					'allowance' => [ //These are not from the DR 0004 certificate, they are from the DR 1098 when not using the DR 0004. Based on the IRS form W-4 Step 1(c)
									 '10' => 4500, //Single
									 '20' => 9000, //Married Filing Jointly
									 '40' => 4500, //Head of Household
					],
					'rate'      => 4.40, //Flat tax rate.
			],
			20210101 => [
					'allowance' => [ //These are not from the DR 0004 certificate, they are from the DR 1098 when not using the DR 0004. Based on the IRS form W-4 Step 1(c)
									 '10' => 4000, //Single
									 '20' => 8000, //Married Filing Jointly
									 '40' => 4000, //Head of Household
					],
					'rate'      => 4.55, //Flat tax rate.
			],
			20200101 => [ //2020: Allowances discontined with Federal 2020 W4 change.
					'allowance' => [  //These are not from the DR 0004 certificate, they are from the DR 1098 when not using the DR 0004. Based on the IRS form W-4 Step 1(c)
									'10' => 4000, //Single
									'20' => 8000, //Married Filing Jointly
									'40' => 4000, //Head of Household
					],
					'rate'      => 4.63, //Flat tax rate.
			],

			//2018 - No Change
			20170101 => [
					'allowance' => 4050,
			],
			20150101 => [
					'allowance' => 4000,
			],
			20130101 => [
					'allowance' => 3900,
			],
			20110101 => [
					'allowance' => 3700,
			],
			20090101 => [
					'allowance' => 3650,
			],
			20070101 => [
					'allowance' => 3400,
			],
			20060101 => [
					'allowance' => 3300,
			],
	];

	var $state_income_tax_rate_options = [
			//Flat rate tax, however Married or Single has a standard allowance amount that changes every year, specified in $state_options

			//2018 - No Change
			20170101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2300, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 8650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 8650, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2300, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 8600, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 8600, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2200, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 8300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 8300, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 2100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2100, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 7900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7900, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2050, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 7750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7750, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 1900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 1900, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 7200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7200, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 1850, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 1850, 'rate' => 4.63, 'constant' => 0 ],
					],
					20 => [
							[ 'income' => 7000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7000, 'rate' => 4.63, 'constant' => 0 ],
					],
			],
	];

	var $state_ui_options = [
			20260101 => [ 'wage_base' => 30600, 'new_employer_rate' => 1.7 ],
			20250101 => [ 'wage_base' => 27200, 'new_employer_rate' => 1.7 ],
			20240101 => [ 'wage_base' => 23800, 'new_employer_rate' => 1.7 ],
			20230101 => [ 'wage_base' => 20400, 'new_employer_rate' => 1.7 ],
			20220101 => [ 'wage_base' => 17000, 'new_employer_rate' => 1.7 ],
			20210101 => [ 'wage_base' => 13600, 'new_employer_rate' => 1.7 ],
			20200101 => [ 'wage_base' => 13600, 'new_employer_rate' => 1.7 ],
			20190101 => [ 'wage_base' => 13100, 'new_employer_rate' => 1.7 ],
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

		if ( $this->getDate() >= 20200101 ) { //Allowances discontined with Federal 2020 W4 change.
			//This has switched to dollars now, but its the same field.
			//  So if its 10 or less, ignore it. If its more than 10 treat it as dollars.
			if ( $this->getStateAllowance() > 10 ) {
				$retval = $this->getStateAllowance();
			} else {
				$retval = $allowance_arr[$this->getStateFilingStatus()] ?? 0;
			}
		} else {
			$retval = TTMath::mul( $this->getStateAllowance(), $allowance_arr );
		}

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			if ( $this->getDate() >= 20200101 ) { //Personal allowances were removed.
				$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
				if ( $retarr == false ) {
					return false;
				}

				$retval = TTMath::mul( $annual_income, TTMath::div( $retarr['rate'], 100 ) );
			} else {
				$rate = $this->getData()->getStateRate( $annual_income );
				$state_constant = $this->getData()->getStateConstant( $annual_income );
				$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

				$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			}
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
