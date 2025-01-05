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
class PayrollDeduction_US_NM extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
		//Matches Federal
		//10=Single
		//20=Married
		//40=Head of Household
		20240101 => [
				10 => [
						[ 'income' => 7300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 12800, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 18300, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 23300, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 33300, 'rate' => 4.9, 'constant' => 504.50 ],
						[ 'income' => 49300, 'rate' => 4.9, 'constant' => 994.50 ],
						[ 'income' => 72300, 'rate' => 4.9, 'constant' => 1778.50 ],
						[ 'income' => 132300, 'rate' => 4.9, 'constant' => 2905.50 ],
						[ 'income' => 217300, 'rate' => 4.9, 'constant' => 5845.50 ],
						[ 'income' => 217300, 'rate' => 5.9, 'constant' => 10010.50 ],
				],
				20 => [
						[ 'income' => 14600, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 22600, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 30600, 'rate' => 3.2, 'constant' => 136.00 ],
						[ 'income' => 38600, 'rate' => 4.7, 'constant' => 392.00 ],
						[ 'income' => 54600, 'rate' => 4.9, 'constant' => 768.00 ],
						[ 'income' => 78600, 'rate' => 4.9, 'constant' => 1552.00 ],
						[ 'income' => 114600, 'rate' => 4.9, 'constant' => 2728.00 ],
						[ 'income' => 214600, 'rate' => 4.9, 'constant' => 4492.00 ],
						[ 'income' => 329600, 'rate' => 4.9, 'constant' => 9392.00 ],
						[ 'income' => 329600, 'rate' => 5.9, 'constant' => 15027.00 ],
				],
				40 => [
						[ 'income' => 10950, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 18950, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 26950, 'rate' => 3.2, 'constant' => 136.00 ],
						[ 'income' => 34950, 'rate' => 4.7, 'constant' => 392.00 ],
						[ 'income' => 50950, 'rate' => 4.9, 'constant' => 768.00 ],
						[ 'income' => 74950, 'rate' => 4.9, 'constant' => 1552.00 ],
						[ 'income' => 110950, 'rate' => 4.9, 'constant' => 2728.00 ],
						[ 'income' => 210950, 'rate' => 4.9, 'constant' => 4492.00 ],
						[ 'income' => 325950, 'rate' => 4.9, 'constant' => 9392.00 ],
						[ 'income' => 325950, 'rate' => 5.9, 'constant' => 15027.00 ],
				],
		],
		20230101 => [
				10 => [
						[ 'income' => 6925, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 12425, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 17925, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 22925, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 32925, 'rate' => 4.9, 'constant' => 504.50 ],
						[ 'income' => 48925, 'rate' => 4.9, 'constant' => 994.50 ],
						[ 'income' => 71925, 'rate' => 4.9, 'constant' => 1778.50 ],
						[ 'income' => 131925, 'rate' => 4.9, 'constant' => 2905.50 ],
						[ 'income' => 216925, 'rate' => 4.9, 'constant' => 5845.50 ],
						[ 'income' => 216925, 'rate' => 5.9, 'constant' => 10010.50 ],
				],
				20 => [
						[ 'income' => 13850, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 21850, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 29850, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 37850, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 53850, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 77850, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 113850, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 213850, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 328850, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 328850, 'rate' => 5.9, 'constant' => 15027 ],
				],
				40 => [
						[ 'income' => 10400, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 18400, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 26400, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 34400, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 50400, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 74400, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 110400, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 210400, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 325400, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 325400, 'rate' => 5.9, 'constant' => 15027 ],
				],
		],
		20220101 => [
				10 => [
						[ 'income' => 6475, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 11975, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 17475, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 22475, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 32475, 'rate' => 4.9, 'constant' => 504.50 ],
						[ 'income' => 48475, 'rate' => 4.9, 'constant' => 994.50 ],
						[ 'income' => 71475, 'rate' => 4.9, 'constant' => 1778.50 ],
						[ 'income' => 131475, 'rate' => 4.9, 'constant' => 2905.50 ],
						[ 'income' => 216475, 'rate' => 4.9, 'constant' => 5845.50 ],
						[ 'income' => 216475, 'rate' => 5.9, 'constant' => 10010.50 ],
				],
				20 => [
						[ 'income' => 12950, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 20950, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 28950, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 36950, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 52950, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 76950, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 112950, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 212950, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 327950, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 327950, 'rate' => 5.9, 'constant' => 15027 ],
				],
				40 => [
						[ 'income' => 9700, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 17700, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 25700, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 33700, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 49700, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 73700, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 109700, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 209700, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 324700, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 324700, 'rate' => 5.9, 'constant' => 15027 ],
				],
		],
		20210101 => [
				10 => [
						[ 'income' => 6275, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 11775, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 17275, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 22275, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 32275, 'rate' => 4.9, 'constant' => 504.50 ],
						[ 'income' => 48275, 'rate' => 4.9, 'constant' => 994.50 ],
						[ 'income' => 71275, 'rate' => 4.9, 'constant' => 1778.50 ],
						[ 'income' => 131275, 'rate' => 4.9, 'constant' => 2905.50 ],
						[ 'income' => 216275, 'rate' => 4.9, 'constant' => 5845.50 ],
						[ 'income' => 216275, 'rate' => 5.9, 'constant' => 10010.50 ],
				],
				20 => [
						[ 'income' => 12550, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 20550, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 28550, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 36550, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 52550, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 76550, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 112550, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 212550, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 327550, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 327550, 'rate' => 5.9, 'constant' => 15027 ],
				],
				40 => [
						[ 'income' => 9400, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 17400, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 25400, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 33400, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 49400, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 73400, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 109400, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 209400, 'rate' => 4.9, 'constant' => 4492 ],
						[ 'income' => 324400, 'rate' => 4.9, 'constant' => 9392 ],
						[ 'income' => 324400, 'rate' => 5.9, 'constant' => 15027 ],
				],
		],
		20200101 => [
				10 => [
						[ 'income' => 6200, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 11700, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 17200, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 22200, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 32200, 'rate' => 4.9, 'constant' => 504.50 ],
						[ 'income' => 48200, 'rate' => 4.9, 'constant' => 994.50 ],
						[ 'income' => 71200, 'rate' => 4.9, 'constant' => 1778.50 ],
						[ 'income' => 71200, 'rate' => 4.9, 'constant' => 2905.50 ],
				],
				20 => [
						[ 'income' => 12400, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 20400, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 28400, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 36400, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 52400, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 76400, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 112400, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 112400, 'rate' => 4.9, 'constant' => 4492 ],
				],
				40 => [
						[ 'income' => 9325, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 17325, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 25325, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 33325, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 49325, 'rate' => 4.9, 'constant' => 768 ],
						[ 'income' => 73325, 'rate' => 4.9, 'constant' => 1552 ],
						[ 'income' => 109325, 'rate' => 4.9, 'constant' => 2728 ],
						[ 'income' => 109325, 'rate' => 4.9, 'constant' => 4492 ],
				],
		],
		20190101 => [
				10 => [
						[ 'income' => 3700, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 9200, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 14700, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 19700, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 19700, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 11550, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 19550, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 27550, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 35550, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 35550, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20180101 => [
				10 => [
						[ 'income' => 3700, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 9200, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 14700, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 19700, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 19700, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 11550, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 19550, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 27550, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 35550, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 35550, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20170101 => [
				10 => [
						[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7800, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13300, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18300, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18300, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8650, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16650, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24650, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32650, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32650, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20160101 => [
				10 => [
						[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7750, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13250, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18250, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18250, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8550, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16550, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24550, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32550, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32550, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20150101 => [
				10 => [
						[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7800, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13300, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18300, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18300, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8600, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16600, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24600, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32600, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32600, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20140101 => [
				10 => [
						[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7750, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13250, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18250, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18250, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8450, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16450, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24450, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32450, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32450, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20130101 => [
				10 => [
						[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7700, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13200, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18200, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18200, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16300, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24300, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32300, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32300, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20120101 => [
				10 => [
						[ 'income' => 2150, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7650, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13150, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18150, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18150, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 8100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 16100, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 24100, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 32100, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 32100, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20090101 => [
				10 => [
						[ 'income' => 2050, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7550, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 13050, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 18050, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 18050, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 7750, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 15750, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 23750, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 31750, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 31750, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20080101 => [
				10 => [
						[ 'income' => 1900, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7400, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 12900, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 17900, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 17900, 'rate' => 4.9, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 7250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 15250, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 23250, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 31250, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 31250, 'rate' => 4.9, 'constant' => 768 ],
				],
		],
		20070101 => [
				10 => [
						[ 'income' => 1900, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7400, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 12900, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 17900, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 17900, 'rate' => 5.3, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 7250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 15250, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 23250, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 31250, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 31250, 'rate' => 5.3, 'constant' => 768 ],
				],
		],
		20060101 => [
				10 => [
						[ 'income' => 1800, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7300, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 12800, 'rate' => 3.2, 'constant' => 93.50 ],
						[ 'income' => 17800, 'rate' => 4.7, 'constant' => 269.50 ],
						[ 'income' => 17800, 'rate' => 5.3, 'constant' => 504.50 ],
				],
				20 => [
						[ 'income' => 6950, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 14950, 'rate' => 1.7, 'constant' => 0 ],
						[ 'income' => 22950, 'rate' => 3.2, 'constant' => 136 ],
						[ 'income' => 30950, 'rate' => 4.7, 'constant' => 392 ],
						[ 'income' => 30950, 'rate' => 5.3, 'constant' => 768 ],
				],
		],
	];

	var $state_options = [
		//01-Jan-2020 - Allowances have been removed with 2020 federal W4 removal.
		//01-Jan-2019 - No Change
		20180101 => [
				'allowance' => 4150,
		],
		//01-Jan-2017 - No Change
		20160101 => [
				'allowance' => 4050,
		],
		20150101 => [
				'allowance' => 4000,
		],
		20140101 => [
				'allowance' => 3950,
		],
		20130101 => [
				'allowance' => 3900,
		],
		20120101 => [
				'allowance' => 3800,
		],
		20090101 => [
				'allowance' => 3650,
		],
		20080101 => [
				'allowance' => 3450,
		],
		20070101 => [
				'allowance' => 3450,
		],
		20060101 => [
				'allowance' => 3250,
		],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 31700, 'new_employer_rate' => null ], //New employer rate varies.
			20230101 => [ 'wage_base' => 30100, 'new_employer_rate' => null ], //New employer rate varies.
			20220101 => [ 'wage_base' => 28700, 'new_employer_rate' => null ], //New employer rate varies.
			20210101 => [ 'wage_base' => 27000, 'new_employer_rate' => null ],
			20200101 => [ 'wage_base' => 25800, 'new_employer_rate' => null ],
			20190101 => [ 'wage_base' => 24800, 'new_employer_rate' => null ],
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

		$allowances = $this->getStateAllowance();
		if ( $this->getDate() >= 20200101 ) {
			$allowances = 0;
		} else if ( $this->getDate() >= 20190101 && $allowances > 3 ) { //As of 01-Jan-2019, the allowances is capped at 3, however they should still be reported properly.
			$allowances = 3;
		}

		$retval = TTMath::mul( $allowances, $allowance_arr );

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
