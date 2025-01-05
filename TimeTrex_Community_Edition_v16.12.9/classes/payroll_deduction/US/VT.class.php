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
class PayrollDeduction_US_VT extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20240101 => [
					10 => [
							[ 'income' => 3700, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 51600, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 119700, 'rate' => 6.6, 'constant' => 1604.65 ],
							[ 'income' => 245700, 'rate' => 7.6, 'constant' => 6099.25 ],
							[ 'income' => 245700, 'rate' => 8.75, 'constant' => 15675.25 ],
					],
					20 => [
							[ 'income' => 11138, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 91088, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 204488, 'rate' => 6.6, 'constant' => 2678.33 ],
							[ 'income' => 305788, 'rate' => 7.6, 'constant' => 10162.73 ],
							[ 'income' => 305788, 'rate' => 8.75, 'constant' => 17861.53 ],
					],
			],
			20230101 => [
					10 => [
							[ 'income' => 3500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 48900, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 113550, 'rate' => 6.6, 'constant' => 1520.90 ],
							[ 'income' => 233050, 'rate' => 7.6, 'constant' => 5787.80 ],
							[ 'income' => 233050, 'rate' => 8.75, 'constant' => 14869.80 ],
					],
					20 => [
							[ 'income' => 10538, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 86388, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 193938, 'rate' => 6.6, 'constant' => 2540.98 ],
							[ 'income' => 289988, 'rate' => 7.6, 'constant' => 9639.28 ],
							[ 'income' => 289988, 'rate' => 8.75, 'constant' => 16939.08 ],
					],
			],
			20220101 => [
					10 => [
							[ 'income' => 3250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 45400, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 105450, 'rate' => 6.6, 'constant' => 1412.03 ],
							[ 'income' => 216400, 'rate' => 7.6, 'constant' => 5375.33 ],
							[ 'income' => 216400, 'rate' => 8.75, 'constant' => 13807.53 ],
					],
					20 => [
							[ 'income' => 9788, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 80238, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 180088, 'rate' => 6.6, 'constant' => 2360.08 ],
							[ 'income' => 269288, 'rate' => 7.6, 'constant' => 8950.18 ],
							[ 'income' => 269288, 'rate' => 8.75, 'constant' => 15729.38 ],
					],
			],
			20210101 => [
					10 => [
							[ 'income' => 3175, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 44125, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 102375, 'rate' => 6.6, 'constant' => 1371.83 ],
							[ 'income' => 210125, 'rate' => 7.6, 'constant' => 5216.33 ],
							[ 'income' => 210125, 'rate' => 8.75, 'constant' => 13405.33 ],
					],
					20 => [
							[ 'income' => 9525, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 77925, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 174875, 'rate' => 6.6, 'constant' => 2291.40 ],
							[ 'income' => 261475, 'rate' => 7.6, 'constant' => 8690.10 ],
							[ 'income' => 261475, 'rate' => 8.75, 'constant' => 15271.70 ],
					],
			],
			20200101 => [
					10 => [
							[ 'income' => 3125, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 43475, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 100925, 'rate' => 6.6, 'constant' => 1351.73 ],
							[ 'income' => 207125, 'rate' => 7.6, 'constant' => 5143.43 ],
							[ 'income' => 207125, 'rate' => 8.75, 'constant' => 13214.63 ],
					],
					20 => [
							[ 'income' => 9375, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 76825, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 172375, 'rate' => 6.6, 'constant' => 2259.58 ],
							[ 'income' => 257725, 'rate' => 7.6, 'constant' => 8565.88 ],
							[ 'income' => 257725, 'rate' => 8.75, 'constant' => 15052.48 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 3075, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 42675, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 99075, 'rate' => 6.6, 'constant' => 1326.60 ],
							[ 'income' => 203275, 'rate' => 7.6, 'constant' => 5049.00 ],
							[ 'income' => 203275, 'rate' => 8.75, 'constant' => 12968.20 ],
					],
					20 => [
							[ 'income' => 9225, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 75375, 'rate' => 3.35, 'constant' => 0 ],
							[ 'income' => 169175, 'rate' => 6.6, 'constant' => 2216.03 ],
							[ 'income' => 252975, 'rate' => 7.6, 'constant' => 8406.83 ],
							[ 'income' => 252975, 'rate' => 8.75, 'constant' => 14775.63 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 40250, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 94200, 'rate' => 6.8, 'constant' => 1334.80 ],
							[ 'income' => 193950, 'rate' => 7.8, 'constant' => 5003.40 ],
							[ 'income' => 419000, 'rate' => 8.8, 'constant' => 12783.90 ],
							[ 'income' => 419000, 'rate' => 8.95, 'constant' => 32588.30 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 70500, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 161750, 'rate' => 6.8, 'constant' => 2218.75 ],
							[ 'income' => 242000, 'rate' => 7.8, 'constant' => 8423.75 ],
							[ 'income' => 425350, 'rate' => 8.8, 'constant' => 14683.25 ],
							[ 'income' => 425350, 'rate' => 8.95, 'constant' => 30818.05 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39900, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 93400, 'rate' => 6.8, 'constant' => 1322.38 ],
							[ 'income' => 192400, 'rate' => 7.8, 'constant' => 4960.38 ],
							[ 'income' => 415600, 'rate' => 8.8, 'constant' => 12682.38 ],
							[ 'income' => 415600, 'rate' => 8.95, 'constant' => 32323.98 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 69900, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 160450, 'rate' => 6.8, 'constant' => 2197.45 ],
							[ 'income' => 240000, 'rate' => 7.8, 'constant' => 8354.85 ],
							[ 'income' => 421900, 'rate' => 8.8, 'constant' => 14559.75 ],
							[ 'income' => 421900, 'rate' => 8.95, 'constant' => 30566.95 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39750, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 93050, 'rate' => 6.8, 'constant' => 1317.05 ],
							[ 'income' => 191600, 'rate' => 7.8, 'constant' => 4941.45 ],
							[ 'income' => 413800, 'rate' => 8.8, 'constant' => 12628.35 ],
							[ 'income' => 413800, 'rate' => 8.95, 'constant' => 32181.95 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 68700, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 159800, 'rate' => 6.8, 'constant' => 2154.85 ],
							[ 'income' => 239050, 'rate' => 7.8, 'constant' => 8349.65 ],
							[ 'income' => 420100, 'rate' => 8.8, 'constant' => 14531.15 ],
							[ 'income' => 420100, 'rate' => 8.95, 'constant' => 30463.55 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39150, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 91600, 'rate' => 6.8, 'constant' => 1295.75 ],
							[ 'income' => 188600, 'rate' => 7.8, 'constant' => 4862.35 ],
							[ 'income' => 407350, 'rate' => 8.8, 'constant' => 12428.35 ],
							[ 'income' => 407350, 'rate' => 8.95, 'constant' => 31678.35 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 68600, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 157300, 'rate' => 6.8, 'constant' => 2151.30 ],
							[ 'income' => 235300, 'rate' => 7.8, 'constant' => 8182.90 ],
							[ 'income' => 413550, 'rate' => 8.8, 'constant' => 14266.90 ],
							[ 'income' => 413550, 'rate' => 8.95, 'constant' => 29952.90 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 38450, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 90050, 'rate' => 6.8, 'constant' => 1270.90 ],
							[ 'income' => 185450, 'rate' => 7.8, 'constant' => 4779.70 ],
							[ 'income' => 400550, 'rate' => 8.8, 'constant' => 12220.90 ],
							[ 'income' => 400550, 'rate' => 8.95, 'constant' => 31149.70 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 67400, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 154700, 'rate' => 6.8, 'constant' => 2108.70 ],
							[ 'income' => 231350, 'rate' => 7.8, 'constant' => 8045.10 ],
							[ 'income' => 406650, 'rate' => 8.8, 'constant' => 14023.80 ],
							[ 'income' => 406650, 'rate' => 8.95, 'constant' => 29450.20 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 37500, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 87800, 'rate' => 6.8, 'constant' => 1237.18 ],
							[ 'income' => 180800, 'rate' => 7.8, 'constant' => 4657.58 ],
							[ 'income' => 390500, 'rate' => 8.8, 'constant' => 11911.58 ],
							[ 'income' => 390500, 'rate' => 8.95, 'constant' => 30365.18 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 65800, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 150800, 'rate' => 6.8, 'constant' => 2051.90 ],
							[ 'income' => 225550, 'rate' => 7.8, 'constant' => 7831.90 ],
							[ 'income' => 396450, 'rate' => 8.8, 'constant' => 13662.40 ],
							[ 'income' => 396450, 'rate' => 8.95, 'constant' => 28701.60 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36050, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 84450, 'rate' => 6.8, 'constant' => 1185.70 ],
							[ 'income' => 173900, 'rate' => 7.8, 'constant' => 4476.90 ],
							[ 'income' => 375700, 'rate' => 8.8, 'constant' => 11454.00 ],
							[ 'income' => 375700, 'rate' => 8.95, 'constant' => 29212.40 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 63200, 'rate' => 3.55, 'constant' => 0 ],
							[ 'income' => 145050, 'rate' => 6.8, 'constant' => 1959.60 ],
							[ 'income' => 217000, 'rate' => 7.8, 'constant' => 7525.40 ],
							[ 'income' => 381400, 'rate' => 8.8, 'constant' => 13137.50 ],
							[ 'income' => 381400, 'rate' => 8.95, 'constant' => 27604.70 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 35400, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 84300, 'rate' => 7.2, 'constant' => 1179.00 ],
							[ 'income' => 173600, 'rate' => 8.5, 'constant' => 4699.80 ],
							[ 'income' => 375000, 'rate' => 9.0, 'constant' => 12290.30 ],
							[ 'income' => 375000, 'rate' => 9.5, 'constant' => 30416.30 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 63100, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 144800, 'rate' => 7.2, 'constant' => 1983.60 ],
							[ 'income' => 216600, 'rate' => 8.5, 'constant' => 7866.00 ],
							[ 'income' => 380700, 'rate' => 9.0, 'constant' => 13969.00 ],
							[ 'income' => 380700, 'rate' => 9.5, 'constant' => 28738.00 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 33960, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 79725, 'rate' => 7.2, 'constant' => 1127.16 ],
							[ 'income' => 166500, 'rate' => 8.5, 'constant' => 4422.24 ],
							[ 'income' => 359650, 'rate' => 9.0, 'constant' => 11798.12 ],
							[ 'income' => 359650, 'rate' => 9.5, 'constant' => 29181.62 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 60200, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 137850, 'rate' => 7.2, 'constant' => 1879.20 ],
							[ 'income' => 207700, 'rate' => 8.5, 'constant' => 7470.00 ],
							[ 'income' => 365100, 'rate' => 9.0, 'constant' => 13407.25 ],
							[ 'income' => 365100, 'rate' => 9.5, 'constant' => 27573.25 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 33520, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 77075, 'rate' => 7.2, 'constant' => 1111.32 ],
							[ 'income' => 162800, 'rate' => 8.5, 'constant' => 4247.28 ],
							[ 'income' => 351650, 'rate' => 9.0, 'constant' => 11533.91 ],
							[ 'income' => 351650, 'rate' => 9.5, 'constant' => 28530.41 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 58900, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 133800, 'rate' => 7.2, 'constant' => 1832.40 ],
							[ 'income' => 203150, 'rate' => 8.5, 'constant' => 7225.20 ],
							[ 'income' => 357000, 'rate' => 9.0, 'constant' => 13119.95 ],
							[ 'income' => 357000, 'rate' => 9.5, 'constant' => 26966.45 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 32240, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 73250, 'rate' => 7.2, 'constant' => 1065.24 ],
							[ 'income' => 156650, 'rate' => 8.5, 'constant' => 4017.97 ],
							[ 'income' => 338400, 'rate' => 9.0, 'constant' => 11106.96 ],
							[ 'income' => 338400, 'rate' => 9.5, 'constant' => 27464.46 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 56800, 'rate' => 3.6, 'constant' => 0 ],
							[ 'income' => 126900, 'rate' => 7.2, 'constant' => 1756.80 ],
							[ 'income' => 195450, 'rate' => 8.5, 'constant' => 6804 ],
							[ 'income' => 343550, 'rate' => 9.0, 'constant' => 12630.75 ],
							[ 'income' => 343550, 'rate' => 9.5, 'constant' => 25959.75 ],
					],
			],
	];

	var $state_options = [
			20240101 => [
					'allowance' => 5100,
			],
			20230101 => [
					'allowance' => 4850,
			],
			20220101 => [
					'allowance' => 4500,
			],
			20210101 => [
					'allowance' => 4400,
			],
			20200101 => [
					'allowance' => 4350,
			],
			20190101 => [
					'allowance' => 4250,
			],
			//20180101 - No Change
			//20170101 - No Change
			20160101 => [
					'allowance' => 4050,
			],
			20150101 => [
					'allowance' => 4000,
			],
			20140101 => [
					'allowance' => 3950,
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
					'allowance' => 3300,
			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 14300, 'new_employer_rate' => 1.0 ],
			20230101 => [ 'wage_base' => 13500, 'new_employer_rate' => 1.0 ],
			20220101 => [ 'wage_base' => 15500, 'new_employer_rate' => 1.0 ],
			20210101 => [ 'wage_base' => 14100, 'new_employer_rate' => 1.0 ],
			20200101 => [ 'wage_base' => 16100, 'new_employer_rate' => 1.0 ],
			20190101 => [ 'wage_base' => 15600, 'new_employer_rate' => 1.0 ],
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
