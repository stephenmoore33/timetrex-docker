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
class PayrollDeduction_US_KS extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20240101 => [ //Effective: 01-Jul-24, but retroactive to 01-Jan-24
					10 => [ //Single
							[ 'income' => 3605, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 26605, 'rate' => 5.2, 'constant' => 0 ],
							[ 'income' => 26605, 'rate' => 5.58, 'constant' => 1196 ],
					],
					20 => [ //Married
							[ 'income' => 8240, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 54240, 'rate' => 5.2, 'constant' => 0 ],
							[ 'income' => 54240, 'rate' => 5.58, 'constant' => 2392 ],
					],
			],
			20210101 => [
					10 => [
							[ 'income' => 3500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18500, 'rate' => 3.1, 'constant' => 0 ],
							[ 'income' => 33500, 'rate' => 5.25, 'constant' => 465 ],
							[ 'income' => 33500, 'rate' => 5.70, 'constant' => 1252.50 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 38000, 'rate' => 3.1, 'constant' => 0 ],
							[ 'income' => 68000, 'rate' => 5.25, 'constant' => 930 ],
							[ 'income' => 68000, 'rate' => 5.70, 'constant' => 2505 ],
					],
			],
			20170601 => [
					10 => [
							[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 3.1, 'constant' => 0 ],
							[ 'income' => 33000, 'rate' => 5.25, 'constant' => 465 ],
							[ 'income' => 33000, 'rate' => 5.70, 'constant' => 1252.50 ],
					],
					20 => [
							[ 'income' => 7500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 37500, 'rate' => 3.1, 'constant' => 0 ],
							[ 'income' => 67500, 'rate' => 5.25, 'constant' => 930 ],
							[ 'income' => 67500, 'rate' => 5.70, 'constant' => 2505 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 2.7, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 4.6, 'constant' => 405 ],
					],
					20 => [
							[ 'income' => 6000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 2.7, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 4.6, 'constant' => 810 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 2.7, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 4.8, 'constant' => 405 ],
					],
					20 => [
							[ 'income' => 6000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 2.7, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 4.8, 'constant' => 810 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 4.9, 'constant' => 450 ],
					],
					20 => [
							[ 'income' => 6000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 3.0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 4.9, 'constant' => 900 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18000, 'rate' => 3.5, 'constant' => 0 ],
							[ 'income' => 33000, 'rate' => 6.25, 'constant' => 525 ],
							[ 'income' => 33000, 'rate' => 6.45, 'constant' => 1462.50 ],
					],
					20 => [
							[ 'income' => 6000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 3.5, 'constant' => 0 ],
							[ 'income' => 66000, 'rate' => 6.25, 'constant' => 1050 ],
							[ 'income' => 66000, 'rate' => 6.45, 'constant' => 2925 ],
					],
			],
	];

	var $state_options = [
			20240101 => [ //Effective: 01-Jul-24, but retroactive to 01-Jan-24
					'allowance' => [ //These are not from the DR 0004 certificate, they are from the DR 1098 when not using the DR 0004. Based on the IRS form W-4 Step 1(c)
								   '10' => 9160, //Single or Married Filing Separate **NOTE: This is considered allowance 1.
								   '20' => 18320, //Married Filing Jointly **NOTE: This is considered allowance 2.
					],
					'additional_allowance' => 2320, //Each additional dependent allowance
			],
			20060101 => [
					'allowance' => 2250,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 14000, 'new_employer_rate' => 2.7 ],
	];

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

		if ( $this->getDate() >= 20240101 ) { //Allowances changed to per filing status.
			$state_allowance_number = 0;
			$filing_status_allowance = 0;
			if ( $this->getStateFilingStatus() == 10 ) { //Single
				if ( $this->getStateAllowance() >= 1 ) { //First allowance is 9160
					$filing_status_allowance = $retarr['allowance'][10]; //9160 x 1
					$state_allowance_number = max( 0, ( $this->getStateAllowance() - 1 )) ;
				}
			} else if ( $this->getStateFilingStatus() == 20 ) { //Married
				if ( $this->getStateAllowance() >= 2 ) { //2nd allowance is 9160 x 2
					$filing_status_allowance = $retarr['allowance'][20]; //9160 x 2
					$state_allowance_number = max( 0, ( $this->getStateAllowance() - 2 ) );
				} else if ( $this->getStateAllowance() >= 1 ) { //First allowance is 9160
					$filing_status_allowance = $retarr['allowance'][10]; //9160 x 1
					$state_allowance_number = 0;
				}
			}

			$additional_allowance = $retarr['additional_allowance'] ?? 0; //Additional allowance amount

			$retval = TTMath::add( $filing_status_allowance, TTMath::mul( $state_allowance_number, $additional_allowance ) );

			Debug::text( 'Filing Status Allowance Amount: ' . $filing_status_allowance .' Additional Allowance: '. $additional_allowance .' Allowance Number: '. $state_allowance_number .'('. $this->getStateAllowance() .')', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$allowance = $retarr['allowance'];

			$retval = TTMath::mul( $this->getStateAllowance(), $allowance );
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
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
