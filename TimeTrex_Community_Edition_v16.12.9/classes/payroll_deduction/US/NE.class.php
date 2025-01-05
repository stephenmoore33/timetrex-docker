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
class PayrollDeduction_US_NE extends PayrollDeduction_US {

	//10=40, 20=30
	var $state_income_tax_rate_options = [
			20240101 => [
					10 => [
							[ 'income' => 3210, 'rate' => 0.00, 'constant' => 0.00 ],
							[ 'income' => 6290, 'rate' => 2.26, 'constant' => 0.00 ],
							[ 'income' => 20440, 'rate' => 3.22, 'constant' => 69.61 ],
							[ 'income' => 29620, 'rate' => 4.91, 'constant' => 525.24 ],
							[ 'income' => 37610, 'rate' => 5.77, 'constant' => 975.98 ],
							[ 'income' => 70630, 'rate' => 5.94, 'constant' => 1437.00 ],
							[ 'income' => 70630, 'rate' => 6.10, 'constant' => 3398.39 ]
					],
					20 => [
							[ 'income' => 7680, 'rate' => 0.00, 'constant' => 0.00 ],
							[ 'income' => 12190, 'rate' => 2.26, 'constant' => 0.00 ],
							[ 'income' => 30360, 'rate' => 3.22, 'constant' => 101.93 ],
							[ 'income' => 47230, 'rate' => 4.91, 'constant' => 687.00 ],
							[ 'income' => 58600, 'rate' => 5.77, 'constant' => 1515.32 ],
							[ 'income' => 77710, 'rate' => 5.94, 'constant' => 2171.37 ],
							[ 'income' => 77710, 'rate' => 6.10, 'constant' => 3306.50 ]
					],
					30 => [
							[ 'income' => 7680, 'rate' => 0.00, 'constant' => 0.00 ],
							[ 'income' => 12190, 'rate' => 2.26, 'constant' => 0.00 ],
							[ 'income' => 30360, 'rate' => 3.22, 'constant' => 101.93 ],
							[ 'income' => 47230, 'rate' => 4.91, 'constant' => 687.00 ],
							[ 'income' => 58600, 'rate' => 5.77, 'constant' => 1515.32 ],
							[ 'income' => 77710, 'rate' => 5.94, 'constant' => 2171.37 ],
							[ 'income' => 77710, 'rate' => 6.10, 'constant' => 3306.50 ]
					],
					40 => [
							[ 'income' => 3210, 'rate' => 0.00, 'constant' => 0.00 ],
							[ 'income' => 6290, 'rate' => 2.26, 'constant' => 0.00 ],
							[ 'income' => 20440, 'rate' => 3.22, 'constant' => 69.61 ],
							[ 'income' => 29620, 'rate' => 4.91, 'constant' => 525.24 ],
							[ 'income' => 37610, 'rate' => 5.77, 'constant' => 975.98 ],
							[ 'income' => 70630, 'rate' => 5.94, 'constant' => 1437.00 ],
							[ 'income' => 70630, 'rate' => 6.10, 'constant' => 3398.39 ]
					],
			],
			20230101 => [
					10 => [
							[ 'income' => 3060, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 5990, 'rate' => 2.26, 'constant' => 0 ],
							[ 'income' => 19470, 'rate' => 3.22, 'constant' => 66.22 ],
							[ 'income' => 28210, 'rate' => 4.91, 'constant' => 500.28 ],
							[ 'income' => 35820, 'rate' => 6.20, 'constant' => 929.41 ],
							[ 'income' => 67270, 'rate' => 6.39, 'constant' => 1401.23 ],
							[ 'income' => 67270, 'rate' => 6.75, 'constant' => 3410.89 ],
					],
					20 => [
							[ 'income' => 7530, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11610, 'rate' => 2.26, 'constant' => 0 ],
							[ 'income' => 28910, 'rate' => 3.22, 'constant' => 92.21 ],
							[ 'income' => 44980, 'rate' => 4.91, 'constant' => 649.27 ],
							[ 'income' => 55810, 'rate' => 6.20, 'constant' => 1438.31 ],
							[ 'income' => 74010, 'rate' => 6.39, 'constant' => 2109.77 ],
							[ 'income' => 74010, 'rate' => 6.75, 'constant' => 3272.75 ],
					],
					30 => [
							[ 'income' => 7530, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11610, 'rate' => 2.26, 'constant' => 0 ],
							[ 'income' => 28910, 'rate' => 3.22, 'constant' => 92.21 ],
							[ 'income' => 44980, 'rate' => 4.91, 'constant' => 649.27 ],
							[ 'income' => 55810, 'rate' => 6.20, 'constant' => 1438.31 ],
							[ 'income' => 74010, 'rate' => 6.39, 'constant' => 2109.77 ],
							[ 'income' => 74010, 'rate' => 6.75, 'constant' => 3272.75 ],
					],
					40 => [
							[ 'income' => 3060, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 5990, 'rate' => 2.26, 'constant' => 0 ],
							[ 'income' => 19470, 'rate' => 3.22, 'constant' => 66.22 ],
							[ 'income' => 28210, 'rate' => 4.91, 'constant' => 500.28 ],
							[ 'income' => 35820, 'rate' => 6.20, 'constant' => 929.41 ],
							[ 'income' => 67270, 'rate' => 6.39, 'constant' => 1401.23 ],
							[ 'income' => 67270, 'rate' => 6.75, 'constant' => 3410.89 ],
					],
			],
		20220101 => [
				10 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5820, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 18900, 'rate' => 3.22, 'constant' => 64.30 ],
						[ 'income' => 27390, 'rate' => 4.91, 'constant' => 485.48 ],
						[ 'income' => 34780, 'rate' => 6.20, 'constant' => 902.34 ],
						[ 'income' => 65310, 'rate' => 6.59, 'constant' => 1360.52 ],
						[ 'income' => 65310, 'rate' => 6.95, 'constant' => 3372.45 ],
				],
				20 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 11270, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 28070, 'rate' => 3.22, 'constant' => 94.24 ],
						[ 'income' => 43670, 'rate' => 4.91, 'constant' => 635.20 ],
						[ 'income' => 54180, 'rate' => 6.20, 'constant' => 1401.16 ],
						[ 'income' => 71850, 'rate' => 6.59, 'constant' => 2052.78 ],
						[ 'income' => 71850, 'rate' => 6.95, 'constant' => 3217.23 ],
				],
				30 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 11270, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 28070, 'rate' => 3.22, 'constant' => 94.24 ],
						[ 'income' => 43670, 'rate' => 4.91, 'constant' => 635.20 ],
						[ 'income' => 54180, 'rate' => 6.20, 'constant' => 1401.16 ],
						[ 'income' => 71850, 'rate' => 6.59, 'constant' => 2052.78 ],
						[ 'income' => 71850, 'rate' => 6.95, 'constant' => 3217.23 ],
				],
				40 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5820, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 18900, 'rate' => 3.22, 'constant' => 64.30 ],
						[ 'income' => 27390, 'rate' => 4.91, 'constant' => 485.48 ],
						[ 'income' => 34780, 'rate' => 6.20, 'constant' => 902.34 ],
						[ 'income' => 65310, 'rate' => 6.59, 'constant' => 1360.52 ],
						[ 'income' => 65310, 'rate' => 6.95, 'constant' => 3372.45 ],
				],
		],
		//20200101 - No Change
		//20190101 - No Change
		//20180101 - No Change
		20170101 => [
				10 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5480, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 17790, 'rate' => 3.22, 'constant' => 56.61 ],
						[ 'income' => 25780, 'rate' => 4.91, 'constant' => 452.99 ],
						[ 'income' => 32730, 'rate' => 6.20, 'constant' => 845.30 ],
						[ 'income' => 61470, 'rate' => 6.59, 'constant' => 1276.20 ],
						[ 'income' => 61470, 'rate' => 6.95, 'constant' => 3170.17 ],
				],
				20 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 10610, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 26420, 'rate' => 3.22, 'constant' => 79.33 ],
						[ 'income' => 41100, 'rate' => 4.91, 'constant' => 588.41 ],
						[ 'income' => 50990, 'rate' => 6.20, 'constant' => 1309.20 ],
						[ 'income' => 67620, 'rate' => 6.59, 'constant' => 1922.38 ],
						[ 'income' => 67620, 'rate' => 6.95, 'constant' => 3018.30 ],
				],
				30 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 10610, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 26420, 'rate' => 3.22, 'constant' => 79.33 ],
						[ 'income' => 41100, 'rate' => 4.91, 'constant' => 588.41 ],
						[ 'income' => 50990, 'rate' => 6.20, 'constant' => 1309.20 ],
						[ 'income' => 67620, 'rate' => 6.59, 'constant' => 1922.38 ],
						[ 'income' => 67620, 'rate' => 6.95, 'constant' => 3018.30 ],
				],
				40 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5480, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 17790, 'rate' => 3.22, 'constant' => 56.61 ],
						[ 'income' => 25780, 'rate' => 4.91, 'constant' => 452.99 ],
						[ 'income' => 32730, 'rate' => 6.20, 'constant' => 845.30 ],
						[ 'income' => 61470, 'rate' => 6.59, 'constant' => 1276.20 ],
						[ 'income' => 61470, 'rate' => 6.95, 'constant' => 3170.17 ],
				],
		],
		20130101 => [
				10 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5325, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 17275, 'rate' => 3.22, 'constant' => 53.11 ],
						[ 'income' => 25025, 'rate' => 4.91, 'constant' => 437.90 ],
						[ 'income' => 31775, 'rate' => 6.20, 'constant' => 818.43 ],
						[ 'income' => 59675, 'rate' => 6.59, 'constant' => 1236.93 ],
						[ 'income' => 59675, 'rate' => 6.95, 'constant' => 3075.54 ],
				],
				20 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 10300, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 25650, 'rate' => 3.22, 'constant' => 72.32 ],
						[ 'income' => 39900, 'rate' => 4.91, 'constant' => 566.59 ],
						[ 'income' => 49500, 'rate' => 6.20, 'constant' => 1266.27 ],
						[ 'income' => 65650, 'rate' => 6.59, 'constant' => 1861.47 ],
						[ 'income' => 65650, 'rate' => 6.95, 'constant' => 2925.76 ],
				],
				30 => [
						[ 'income' => 7100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 10300, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 25650, 'rate' => 3.22, 'constant' => 72.32 ],
						[ 'income' => 39900, 'rate' => 4.91, 'constant' => 566.59 ],
						[ 'income' => 49500, 'rate' => 6.20, 'constant' => 1266.27 ],
						[ 'income' => 65650, 'rate' => 6.59, 'constant' => 1861.47 ],
						[ 'income' => 65650, 'rate' => 6.95, 'constant' => 2925.76 ],
				],
				40 => [
						[ 'income' => 2975, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5325, 'rate' => 2.26, 'constant' => 0 ],
						[ 'income' => 17275, 'rate' => 3.22, 'constant' => 53.11 ],
						[ 'income' => 25025, 'rate' => 4.91, 'constant' => 437.90 ],
						[ 'income' => 31775, 'rate' => 6.20, 'constant' => 818.43 ],
						[ 'income' => 59675, 'rate' => 6.59, 'constant' => 1236.93 ],
						[ 'income' => 59675, 'rate' => 6.95, 'constant' => 3075.54 ],
				],
		],
		20100101 => [
				10 => [
						[ 'income' => 2400, 'rate' => 2.56, 'constant' => 0 ],
						[ 'income' => 17500, 'rate' => 3.57, 'constant' => 61.44 ],
						[ 'income' => 27000, 'rate' => 5.12, 'constant' => 600.51 ],
						[ 'income' => 27000, 'rate' => 6.84, 'constant' => 1086.91 ],
				],
				20 => [
						[ 'income' => 4800, 'rate' => 2.56, 'constant' => 0 ],
						[ 'income' => 35000, 'rate' => 3.57, 'constant' => 122.88 ],
						[ 'income' => 54000, 'rate' => 5.12, 'constant' => 1201.02 ],
						[ 'income' => 54000, 'rate' => 6.84, 'constant' => 2173.82 ],
				],
				30 => [
						[ 'income' => 2400, 'rate' => 2.56, 'constant' => 0 ],
						[ 'income' => 17500, 'rate' => 3.57, 'constant' => 61.44 ],
						[ 'income' => 27000, 'rate' => 5.12, 'constant' => 600.51 ],
						[ 'income' => 27000, 'rate' => 6.84, 'constant' => 1086.91 ],
				],
				40 => [
						[ 'income' => 4500, 'rate' => 2.56, 'constant' => 0 ],
						[ 'income' => 28000, 'rate' => 3.57, 'constant' => 115.20 ],
						[ 'income' => 40000, 'rate' => 5.12, 'constant' => 954.15 ],
						[ 'income' => 40000, 'rate' => 6.84, 'constant' => 1568.55 ],
				],
		],
		20080101 => [
				10 => [
						[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 4400, 'rate' => 2.35, 'constant' => 0 ],
						[ 'income' => 15500, 'rate' => 3.27, 'constant' => 51.70 ],
						[ 'income' => 22750, 'rate' => 5.02, 'constant' => 414.67 ],
						[ 'income' => 29000, 'rate' => 6.20, 'constant' => 778.62 ],
						[ 'income' => 55000, 'rate' => 6.59, 'constant' => 1166.12 ],
						[ 'income' => 55000, 'rate' => 6.95, 'constant' => 2879.52 ],
				],
				20 => [
						[ 'income' => 6450, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 9450, 'rate' => 2.35, 'constant' => 0 ],
						[ 'income' => 23750, 'rate' => 3.27, 'constant' => 70.50 ],
						[ 'income' => 37000, 'rate' => 5.02, 'constant' => 538.11 ],
						[ 'income' => 46000, 'rate' => 6.20, 'constant' => 1203.26 ],
						[ 'income' => 61000, 'rate' => 6.59, 'constant' => 1761.26 ],
						[ 'income' => 61000, 'rate' => 6.95, 'constant' => 2749.76 ],
				],
		],
		20070101 => [
				10 => [
						[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 4400, 'rate' => 2.43, 'constant' => 0 ],
						[ 'income' => 15500, 'rate' => 3.38, 'constant' => 53.46 ],
						[ 'income' => 22750, 'rate' => 5.19, 'constant' => 428.64 ],
						[ 'income' => 28100, 'rate' => 6.41, 'constant' => 804.92 ],
						[ 'income' => 54100, 'rate' => 6.81, 'constant' => 1147.86 ],
						[ 'income' => 75100, 'rate' => 7.04, 'constant' => 2918.46 ],
						[ 'income' => 75100, 'rate' => 7.18, 'constant' => 4396.86 ],
				],
				20 => [
						[ 'income' => 5250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 8250, 'rate' => 2.43, 'constant' => 0 ],
						[ 'income' => 22400, 'rate' => 3.38, 'constant' => 72.90 ],
						[ 'income' => 35400, 'rate' => 5.19, 'constant' => 551.17 ],
						[ 'income' => 42950, 'rate' => 6.41, 'constant' => 1225.87 ],
						[ 'income' => 58250, 'rate' => 6.81, 'constant' => 1709.83 ],
						[ 'income' => 75250, 'rate' => 7.04, 'constant' => 2751.76 ],
						[ 'income' => 75250, 'rate' => 7.18, 'constant' => 3948.56 ],
				],
		],
		20060101 => [
				10 => [
						[ 'income' => 2000, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 4400, 'rate' => 2.49, 'constant' => 0 ],
						[ 'income' => 15500, 'rate' => 3.47, 'constant' => 54.78 ],
						[ 'income' => 22750, 'rate' => 5.32, 'constant' => 439.95 ],
						[ 'income' => 28100, 'rate' => 6.57, 'constant' => 825.65 ],
						[ 'income' => 54100, 'rate' => 6.98, 'constant' => 1177.15 ],
						[ 'income' => 75100, 'rate' => 7.22, 'constant' => 2991.95 ],
						[ 'income' => 75100, 'rate' => 7.36, 'constant' => 4508.15 ],
				],
				20 => [
						[ 'income' => 5250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 8250, 'rate' => 2.49, 'constant' => 0 ],
						[ 'income' => 22400, 'rate' => 3.47, 'constant' => 74.70 ],
						[ 'income' => 35400, 'rate' => 5.32, 'constant' => 565.71 ],
						[ 'income' => 42950, 'rate' => 6.57, 'constant' => 1257.35 ],
						[ 'income' => 58250, 'rate' => 6.98, 'constant' => 1753.35 ],
						[ 'income' => 75250, 'rate' => 7.22, 'constant' => 2821.29 ],
						[ 'income' => 75250, 'rate' => 7.36, 'constant' => 4048.69 ],
				],
		],
	];

	var $state_options = [
			20240101 => [ // 01-Jan-2024
						  'allowance' => 2250,
			],
			20230101 => [ // 01-Jan-2023
						  'allowance' => 2140,
			],
			20220101 => [ // 01-Jan-2022
						  'allowance' => 2080,
			],
			//01-Jan-2021 - No Change
			//01-Jan-2020 - No Change
			//01-Jan-2019 - No Change
			//01-Jan-2018 - No Change
			20170101 => [ // 01-Jan-2017
						  'allowance' => 1960,
			],
			20130101 => [ // 01-Jan-2013
						  'allowance' => 1900,
			],
			20100101 => [ //01-Jan-2010: Formula changed, this is no longer used.
						  'allowance' => 118,
			],
			20080101 => [ //01-Jan-2008
						  'allowance' => 113,
			],
			20070101 => [ //01-Jan-2007
						  'allowance' => 111,
			],
			20060101 => [ //01-Jan-2006
						  'allowance' => 103,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 9000, 'max_wage_base' => 24000, 'max_wage_base_rate_threshold' => 5.40, 'is_variable' => true, 'new_employer_rate' => 1.25 ], //Variable wage base depending on experience level -- https://dol.nebraska.gov/UITax/UnemploymentInsuranceTax/CombinedTaxRates
	];

	/**
	 * @var bool
	 */
	private $is_nested_calculation;

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		if ( $this->getDate() >= 20130101 ) {
			$state_allowance = $this->getStateAllowanceAmount();
			$income = TTMath::sub( $annual_income, $state_allowance );
		} else {
			$income = $annual_income;
		}

		//Make sure income never drops into the negatives, as that will prevent getStateTaxPayable() from calculating the special threshold.
		if ( $income < 0 ) {
			$income = 0;
		}
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

		if ( $annual_income >= 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			Debug::text( 'aState Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getDate() < 20130101 ) {
				$retval = TTMath::sub( $retval, $this->getStateAllowanceAmount() );
			}

			if ( $this->getDate() >= 20170101 && ( !isset( $this->is_nested_calculation ) || $this->is_nested_calculation == false ) ) {                                     //Not 100% sure when this came into play.
				//Special income tax withholding procedures.
				//Ensure that the tax amount is at least 1.5% of the taxable income
				//  OR at least 50% of the income tax withholding for a single employee with one income tax withholding allowance, or for a married employee with two allowances.
				$special_threshold = TTMath::mul( $this->getAnnualTaxableIncome(), 0.015 ); //1.5% -- Use gross annual income, not state annual income after allowances come off.
				if ( $retval < $special_threshold ) {
					if ( $this->getDate() >= 20220101 ) { //To avoid having to go back to past years and update unit tests.
						$pd_obj = clone $this;
						$pd_obj->is_nested_calculation = true;

						if ( $this->getStateFilingStatus() == 10 ) {
							$pd_obj->setStateFilingStatus( 10 ); //Single
							$pd_obj->setStateAllowance( 1 );
						} else {
							$pd_obj->setStateFilingStatus( 20 ); //Married
							$pd_obj->setStateAllowance( 2 );
						}
						$tmp_annual_tax_amount = $pd_obj->_getStateTaxPayable();
						Debug::text( '  Calculated tax lower than special threshold of 1.5%: ' . $special_threshold . ' Annual Tax Amount for Single w/1 Allowance: ' . $tmp_annual_tax_amount, __FILE__, __LINE__, __METHOD__, 10 );
						if ( $tmp_annual_tax_amount > 0 ) {
							$minimum_annual_tax_amount = TTMath::div( $tmp_annual_tax_amount, 2 ); //50% of Single with 1 allowance -- Minimum tax amount.
							if ( $retval < $minimum_annual_tax_amount ) {
								$retval = $minimum_annual_tax_amount;
							}
						}
					} else {
						$retval = $special_threshold;
					}
				}
			}
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
