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
class PayrollDeduction_US_NC extends PayrollDeduction_US {
	/*
		*Prior to 2015 it was:
		protected $state_nc_filing_status_options = array(
															10 => 'Single',
															20 => 'Married or Qualified Widow(er)',
															30 => 'Head of Household',
										);

		After 2015:

															10 => TTi18n::gettext('Single'),
															20 => TTi18n::gettext('Married - Filing Jointly or Qualified Widow(er)'),
															30 => TTi18n::gettext('Married - Filing Separately'),
															40 => TTi18n::gettext('Head of Household'),

	*/
	var $state_options = [
			//**NOTE: The rate used for withholding is 0.1% higher than ther actual income tax rate. So use the rate mentioned in the examples.
			20240101 => [
					'standard_deduction' => [
							'10' => 12750.00,
							'20' => 12750.00,
							'30' => 12750.00,
							'40' => 19125.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 4.6, //Flat 4.6% -- Top of the instructions mention 4.5%, but examples all show 4.6%
			],
			20230101 => [
					'standard_deduction' => [
							'10' => 12750.00,
							'20' => 12750.00,
							'30' => 12750.00,
							'40' => 19125.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 4.85, //Flat 4.85% -- Top of the instructions mention 4.75%, but examples all show 4.85%
			],
			20220101 => [
					'standard_deduction' => [
							'10' => 12750.00,
							'20' => 12750.00,
							'30' => 12750.00,
							'40' => 19125.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.09, //Flat 5.09% -- Top of the instructions mention 4.99%, but examples all show 5.09%
			],
			20200101 => [
					'standard_deduction' => [
							'10' => 10750.00,
							'20' => 10750.00,
							'30' => 10750.00,
							'40' => 16125.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.35, //Flat 5.35%
			],
			20190101 => [
					'standard_deduction' => [
							'10' => 10000.00,
							'20' => 10000.00,
							'30' => 10000.00,
							'40' => 15000.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.35, //Flat 5.35%
			],
			// 2018 - No Change
			20170101 => [
					'standard_deduction' => [
							'10' => 8750.00,
							'20' => 8750.00,
							'30' => 8750.00,
							'40' => 14000.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.599, //Flat 5.599%
			],
			20160101 => [
					'standard_deduction' => [
							'10' => 7750.00,
							'20' => 7750.00,
							'30' => 7750.00,
							'40' => 12400.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.85, //Flat 5.85%
			],
			//Formula changed for 01-Jan-15
			20150101 => [
					'standard_deduction' => [
							'10' => 7500.00,
							'20' => 15000.00,
							'30' => 7500.00,
							'40' => 12000.00,
					],
					'allowance_cutoff'   => [
							'10' => 50000.00,
							'20' => 100000.00,
							'30' => 50000.00,
							'40' => 80000.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.75, //Flat 5.75%
			],
			//Formula changed for 01-Jan-14
			20140101 => [
					'standard_deduction' => [
							'10' => 7500.00,
							'20' => 7500.00,
							'30' => 12000.00, //30 used to be HoH
							'40' => 12000.00,
					],
					'allowance_cutoff'   => [
							'10' => 60000.00,
							'20' => 50000.00,
							'30' => 80000.00, //30 used to be HoH
							'40' => 80000.00,
					],
					'allowance'          => [
							1 => 2500,
					],
					'rate'               => 5.8, //Flat 5.8%
			],
			//No changes for 01-Jan-09
			20060101 => [
					'standard_deduction' => [
							'10' => 3000.00,
							'20' => 3000.00,
							'30' => 4400.00, //30 used to be HoH
							'40' => 4400.00,
					],
					'allowance_cutoff'   => [
							'10' => 60000.00,
							'20' => 50000.00,
							'30' => 80000.00, //30 used to be HoH
							'40' => 80000.00,
					],
					'allowance'          => [
							1 => 2500,
							2 => 2000 //Legacy formula.
					],
			],
	];

	var $state_income_tax_rate_options = [
		//Switched to flat rate percent using the above $state_options array 01-Jan-2014
		20090101 => [
				10 => [
						[ 'income' => 12750, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 60000, 'rate' => 7, 'constant' => 127.50 ],
						[ 'income' => 60000, 'rate' => 7.75, 'constant' => 577.50 ],
				],
				20 => [
						[ 'income' => 10625, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 50000, 'rate' => 7, 'constant' => 106.25 ],
						[ 'income' => 50000, 'rate' => 7.75, 'constant' => 481.25 ],
				],
				30 => [
						[ 'income' => 17000, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 80000, 'rate' => 7, 'constant' => 170 ],
						[ 'income' => 80000, 'rate' => 7.75, 'constant' => 770 ],
				],
		],
		20070101 => [
				10 => [
						[ 'income' => 12750, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 60000, 'rate' => 7, 'constant' => 127.50 ],
						[ 'income' => 120000, 'rate' => 7.75, 'constant' => 577.50 ],
						[ 'income' => 120000, 'rate' => 8, 'constant' => 877.50 ],
				],
				20 => [
						[ 'income' => 10625, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 50000, 'rate' => 7, 'constant' => 106.25 ],
						[ 'income' => 100000, 'rate' => 7.75, 'constant' => 481.25 ],
						[ 'income' => 100000, 'rate' => 8, 'constant' => 731.25 ],
				],
				30 => [
						[ 'income' => 17000, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 80000, 'rate' => 7, 'constant' => 170 ],
						[ 'income' => 160000, 'rate' => 7.75, 'constant' => 770 ],
						[ 'income' => 160000, 'rate' => 8, 'constant' => 1170 ],
				],
		],
		20060101 => [
				10 => [
						[ 'income' => 12750, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 60000, 'rate' => 7, 'constant' => 127.50 ],
						[ 'income' => 120000, 'rate' => 7.75, 'constant' => 577.50 ],
						[ 'income' => 120000, 'rate' => 8.25, 'constant' => 1177.50 ],
				],
				20 => [
						[ 'income' => 10625, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 50000, 'rate' => 7, 'constant' => 106.25 ],
						[ 'income' => 100000, 'rate' => 7.75, 'constant' => 481.25 ],
						[ 'income' => 100000, 'rate' => 8.25, 'constant' => 981.25 ],
				],
				30 => [
						[ 'income' => 17000, 'rate' => 6, 'constant' => 0 ],
						[ 'income' => 80000, 'rate' => 7, 'constant' => 170 ],
						[ 'income' => 160000, 'rate' => 7.75, 'constant' => 770 ],
						[ 'income' => 160000, 'rate' => 8.25, 'constant' => 1570 ],
				],
		],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 31400, 'new_employer_rate' => 1.0 ],
			20230101 => [ 'wage_base' => 29600, 'new_employer_rate' => 1.0 ],
			20220101 => [ 'wage_base' => 28000, 'new_employer_rate' => 1.0 ],
			20210101 => [ 'wage_base' => 26000, 'new_employer_rate' => 1.0 ],
			20200101 => [ 'wage_base' => 25200, 'new_employer_rate' => 1.0 ],
			20190101 => [ 'wage_base' => 24300, 'new_employer_rate' => 1.0 ],
	];

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = TTMath::sub( TTMath::sub( $annual_income, $state_deductions ), $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceCutOff() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$retval = $retarr['allowance_cutoff'][$this->getStateFilingStatus()];

		Debug::text( 'State Employee Allowance Cutoff: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getDate() >= 20140101 ) {
			$allowance_arr = $retarr['allowance'][1];
		} else {
			if ( $this->getAnnualTaxableIncome() < $this->getStateAllowanceCutOff() ) {
				$allowance_arr = $retarr['allowance'][1];
			} else {
				$allowance_arr = $retarr['allowance'][2];
			}
		}

		$retval = TTMath::mul( $this->getStateAllowance(), $allowance_arr );

		Debug::text( 'Allowances: ' . $this->getStateAllowance() . ' Allowance Multiplier: ' . $allowance_arr . ' State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			if ( $this->getDate() >= 20140101 ) {
				$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
				if ( $retarr == false ) {
					return false;
				}

				$retval = TTMath::mul( $annual_income, TTMath::div( $retarr['rate'], 100 ) );
			} else {
				$rate = $this->getData()->getStateRate( $annual_income );
				$state_constant = $this->getData()->getStateConstant( $annual_income );
				$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

				$retval = TTMath::sub( TTMath::mul( $annual_income, $rate ), $state_constant );
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
