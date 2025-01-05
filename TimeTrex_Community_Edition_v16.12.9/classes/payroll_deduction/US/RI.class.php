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
class PayrollDeduction_US_RI extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20240101 => [
					10 => [
							[ 'income' => 77450, 'rate' => 3.75, 'constant' => 0.00 ],
							[ 'income' => 176050, 'rate' => 4.75, 'constant' => 2904.38 ],
							[ 'income' => 176050, 'rate' => 5.99, 'constant' => 7587.88 ],
					],
					20 => [
							[ 'income' => 77450, 'rate' => 3.75, 'constant' => 0.00 ],
							[ 'income' => 176050, 'rate' => 4.75, 'constant' => 2904.38 ],
							[ 'income' => 176050, 'rate' => 5.99, 'constant' => 7587.88 ],
					],
			],
			20230101 => [
					10 => [
							[ 'income' => 73450, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 166950, 'rate' => 4.75, 'constant' => 2754.38 ],
							[ 'income' => 166950, 'rate' => 5.99, 'constant' => 7195.63 ],
					],
					20 => [
							[ 'income' => 73450, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 166950, 'rate' => 4.75, 'constant' => 2754.38 ],
							[ 'income' => 166950, 'rate' => 5.99, 'constant' => 7195.63 ],
					],
			],
			20220101 => [
					10 => [
							[ 'income' => 68200, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 155050, 'rate' => 4.75, 'constant' => 2557.50 ],
							[ 'income' => 155050, 'rate' => 5.99, 'constant' => 6682.88 ],
					],
					20 => [
							[ 'income' => 68200, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 155050, 'rate' => 4.75, 'constant' => 2557.50 ],
							[ 'income' => 155050, 'rate' => 5.99, 'constant' => 6682.88 ],
					],
			],
			20210101 => [
					10 => [
							[ 'income' => 66200, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 150550, 'rate' => 4.75, 'constant' => 2482.50 ],
							[ 'income' => 150550, 'rate' => 5.99, 'constant' => 6489.13 ],
					],
					20 => [
							[ 'income' => 66200, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 150550, 'rate' => 4.75, 'constant' => 2482.50 ],
							[ 'income' => 150550, 'rate' => 5.99, 'constant' => 6489.13 ],
					],
			],
			20200101 => [
					10 => [
							[ 'income' => 65250, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 148350, 'rate' => 4.75, 'constant' => 2446.88 ],
							[ 'income' => 148350, 'rate' => 5.99, 'constant' => 6394.13 ],
					],
					20 => [
							[ 'income' => 65250, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 148350, 'rate' => 4.75, 'constant' => 2446.88 ],
							[ 'income' => 148350, 'rate' => 5.99, 'constant' => 6394.13 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 64050, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 145600, 'rate' => 4.75, 'constant' => 2401.88 ],
							[ 'income' => 145600, 'rate' => 5.99, 'constant' => 6275.50 ],
					],
					20 => [
							[ 'income' => 64050, 'rate' => 3.75, 'constant' => 0 ], //Same as Single rate.
							[ 'income' => 145600, 'rate' => 4.75, 'constant' => 2401.88 ],
							[ 'income' => 145600, 'rate' => 5.99, 'constant' => 6275.50 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 62550, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 142150, 'rate' => 4.75, 'constant' => 2345.63 ],
							[ 'income' => 142150, 'rate' => 5.99, 'constant' => 6126.63 ],
					],
					20 => [
							[ 'income' => 62550, 'rate' => 3.75, 'constant' => 0 ], //Same as Single rate.
							[ 'income' => 142150, 'rate' => 4.75, 'constant' => 2345.63 ],
							[ 'income' => 142150, 'rate' => 5.99, 'constant' => 6126.63 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 61300, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 139400, 'rate' => 4.75, 'constant' => 2298.75 ],
							[ 'income' => 139400, 'rate' => 5.99, 'constant' => 6008.50 ],
					],
					20 => [
							[ 'income' => 61300, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 139400, 'rate' => 4.75, 'constant' => 2298.75 ],
							[ 'income' => 139400, 'rate' => 5.99, 'constant' => 6008.50 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 60850, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 138300, 'rate' => 4.75, 'constant' => 2281.88 ],
							[ 'income' => 138300, 'rate' => 5.99, 'constant' => 5960.75 ],
					],
					20 => [
							[ 'income' => 60850, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 138300, 'rate' => 4.75, 'constant' => 2281.88 ],
							[ 'income' => 138300, 'rate' => 5.99, 'constant' => 5960.75 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 60000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 137650, 'rate' => 4.75, 'constant' => 2270.63 ],
							[ 'income' => 137650, 'rate' => 5.99, 'constant' => 5932.88 ],
					],
					20 => [
							[ 'income' => 60000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 137650, 'rate' => 4.75, 'constant' => 2270.63 ],
							[ 'income' => 137650, 'rate' => 5.99, 'constant' => 5932.88 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 59600, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 135500, 'rate' => 4.75, 'constant' => 2235.00 ],
							[ 'income' => 135500, 'rate' => 5.99, 'constant' => 5840.25 ],
					],
					20 => [
							[ 'income' => 59600, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 135000, 'rate' => 4.75, 'constant' => 2235.00 ],
							[ 'income' => 135000, 'rate' => 5.99, 'constant' => 5840.25 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 58600, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 133250, 'rate' => 4.75, 'constant' => 2197.50 ],
							[ 'income' => 133250, 'rate' => 5.99, 'constant' => 5743.38 ],
					],
					20 => [
							[ 'income' => 58600, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 133250, 'rate' => 4.75, 'constant' => 2197.50 ],
							[ 'income' => 133250, 'rate' => 5.99, 'constant' => 5743.38 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 57150, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 129900, 'rate' => 4.75, 'constant' => 2143.13 ],
							[ 'income' => 129900, 'rate' => 5.99, 'constant' => 5598.75 ],
					],
					20 => [
							[ 'income' => 57150, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 129900, 'rate' => 4.75, 'constant' => 2143.13 ],
							[ 'income' => 129900, 'rate' => 5.99, 'constant' => 5598.75 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 55000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 125000, 'rate' => 4.75, 'constant' => 2063 ],
							[ 'income' => 125000, 'rate' => 5.99, 'constant' => 5388 ],
					],
					20 => [
							[ 'income' => 55000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 125000, 'rate' => 4.75, 'constant' => 2063 ],
							[ 'income' => 125000, 'rate' => 5.99, 'constant' => 5388 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36050, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 78850, 'rate' => 7.0, 'constant' => 1252.50 ],
							[ 'income' => 173900, 'rate' => 7.75, 'constant' => 4248.50 ],
							[ 'income' => 375650, 'rate' => 9.0, 'constant' => 11614.88 ],
							[ 'income' => 375650, 'rate' => 9.9, 'constant' => 29772.38 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 62700, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 133450, 'rate' => 7.0, 'constant' => 2109.38 ],
							[ 'income' => 215100, 'rate' => 7.75, 'constant' => 7061.88 ],
							[ 'income' => 379500, 'rate' => 9.0, 'constant' => 13389.75 ],
							[ 'income' => 379500, 'rate' => 9.9, 'constant' => 28185.75 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 78700, 'rate' => 7.0, 'constant' => 1250.63 ],
							[ 'income' => 173600, 'rate' => 7.75, 'constant' => 4239.63 ],
							[ 'income' => 374950, 'rate' => 9.0, 'constant' => 11594.38 ],
							[ 'income' => 374950, 'rate' => 9.9, 'constant' => 29715.88 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 62600, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 133200, 'rate' => 7.0, 'constant' => 2105.63 ],
							[ 'income' => 214700, 'rate' => 7.75, 'constant' => 7047.63 ],
							[ 'income' => 378800, 'rate' => 9.0, 'constant' => 13363.88 ],
							[ 'income' => 378800, 'rate' => 9.9, 'constant' => 28132.88 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 34500, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 75500, 'rate' => 7.0, 'constant' => 1194.38 ],
							[ 'income' => 166500, 'rate' => 7.75, 'constant' => 4064.38 ],
							[ 'income' => 359650, 'rate' => 9.0, 'constant' => 11116.88 ],
							[ 'income' => 359650, 'rate' => 9.9, 'constant' => 28500.38 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 60000, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 127750, 'rate' => 7.0, 'constant' => 2008.13 ],
							[ 'income' => 205950, 'rate' => 7.75, 'constant' => 6750.63 ],
							[ 'income' => 363300, 'rate' => 9.0, 'constant' => 12811.13 ],
							[ 'income' => 363300, 'rate' => 9.9, 'constant' => 26972.63 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 33520, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 77075, 'rate' => 7.0, 'constant' => 1157.63 ],
							[ 'income' => 162800, 'rate' => 7.75, 'constant' => 4206.48 ],
							[ 'income' => 351650, 'rate' => 9.0, 'constant' => 10850.17 ],
							[ 'income' => 351650, 'rate' => 9.9, 'constant' => 27846.67 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 58700, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 124900, 'rate' => 7.0, 'constant' => 1959.38 ],
							[ 'income' => 201300, 'rate' => 7.75, 'constant' => 6593.38 ],
							[ 'income' => 355200, 'rate' => 9.0, 'constant' => 12514.38 ],
							[ 'income' => 355200, 'rate' => 9.9, 'constant' => 26365.38 ],
					],
			],
			20060625 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 32240, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 73250, 'rate' => 7.0, 'constant' => 1109.63 ],
							[ 'income' => 156650, 'rate' => 7.75, 'constant' => 3980.33 ],
							[ 'income' => 338400, 'rate' => 9.0, 'constant' => 10443.83 ],
							[ 'income' => 338400, 'rate' => 9.9, 'constant' => 26801.33 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 56500, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 120200, 'rate' => 7.0, 'constant' => 1876.88 ],
							[ 'income' => 193750, 'rate' => 7.75, 'constant' => 6335.88 ],
							[ 'income' => 341850, 'rate' => 9.0, 'constant' => 12036.01 ],
							[ 'income' => 341850, 'rate' => 9.9, 'constant' => 25365.01 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 31500, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 69750, 'rate' => 7.0, 'constant' => 1081.88 ],
							[ 'income' => 151950, 'rate' => 7.75, 'constant' => 3759.38 ],
							[ 'income' => 328250, 'rate' => 9.0, 'constant' => 10129.88 ],
							[ 'income' => 328250, 'rate' => 9.9, 'constant' => 25996.88 ],
					],
					20 => [
							[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 54750, 'rate' => 3.75, 'constant' => 0 ],
							[ 'income' => 116600, 'rate' => 7.0, 'constant' => 1811.25 ],
							[ 'income' => 187900, 'rate' => 7.75, 'constant' => 6140.75 ],
							[ 'income' => 331500, 'rate' => 9.0, 'constant' => 11666.50 ],
							[ 'income' => 331500, 'rate' => 9.9, 'constant' => 24590.50 ],
					],
			],
	];

	var $state_options = [
			20240101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 274650, //If annual income more than this, allowance is 0.
			],
			20230101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 260550, //If annual income more than this, allowance is 0.
			],
			20220101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 241850, //If annual income more than this, allowance is 0.
			],
			20210101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 234750, //If annual income more than this, allowance is 0.
			],
			20200101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 231500, //If annual income more than this, allowance is 0.
			],
			//01-Jan-19: No Change
			20180101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 227050, //If annual income more than this, allowance is 0.
			],
			20180101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 221800, //If annual income more than this, allowance is 0.
			],
			20170101 => [
					'allowance'           => 1000,
					'allowance_threshold' => 217350, //If annual income more than this, allowance is 0.
			],
			//01-Jan-12: No Change
			20110101 => [
					'allowance' => 1000,
			],
			//01-Jan-10: No Change
			20090101 => [
					'allowance' => 3650,
			],
			20080101 => [
					'allowance' => 3500,
			],
			20070101 => [
					'allowance' => 3400,
			],
			20060101 => [
					'allowance' => 3200,
			],
			20060625 => [
					'allowance' => 3300,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 29200, 'max_wage_base' => 30700, 'max_wage_base_rate_threshold' => 9.7, 'is_variable' => true, 'new_employer_rate' => 0.88 ], //Wage Base: $24,600 or $26,100 for employers that have an experience rate of 9.59% or higher. -- https://dlt.ri.gov/employers/employer-tax-unit
			20230101 => [ 'wage_base' => 28200, 'max_wage_base' => 29700, 'max_wage_base_rate_threshold' => 9.49, 'is_variable' => true, 'new_employer_rate' => 0.88 ], //Wage Base: $24,600 or $26,100 for employers that have an experience rate of 9.59% or higher. -- https://dlt.ri.gov/employers/employer-tax-unit
			//20220101 => [ 'wage_base' => 24600, 'new_employer_rate' => 0.95 ],
			20210101 => [ 'wage_base' => 24600, 'new_employer_rate' => 0.95 ],
			20200101 => [ 'wage_base' => 24000, 'new_employer_rate' => 0.95 ],
			20190101 => [ 'wage_base' => 23600, 'new_employer_rate' => 0.95 ],
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

		$annual_income = $this->getAnnualTaxableIncome();

		$allowance_arr = $retarr['allowance'];
		$retval = TTMath::mul( $this->getStateAllowance(), $allowance_arr );

		if ( isset( $retarr['allowance_threshold'] ) && $annual_income > $retarr['allowance_threshold'] ) {
			Debug::text( 'Annual income exceeds threshold, setting allowance amount to 0 from: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
			$retval = 0;
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

	function _getStateUIWageBase( $rate = null ) {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_ui_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( isset( $retarr['wage_base'] ) && !empty( $retarr['wage_base'] ) ) {
			if ( isset( $retarr['max_wage_base_rate_threshold'] ) && !empty( $retarr['max_wage_base_rate_threshold'] ) ) {
				if ( $rate >= $retarr['max_wage_base_rate_threshold'] ) {
					Debug::text( '  State UI, due to Rate: '. $rate .' Using Max Wage Base: ' . $retarr['max_wage_base'], __FILE__, __LINE__, __METHOD__, 10 );
					return $retarr['max_wage_base'];
				}
			}
			return $retarr['wage_base'];
		}

		return 0;
	}
}

?>
