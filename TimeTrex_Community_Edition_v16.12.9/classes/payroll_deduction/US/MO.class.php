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
class PayrollDeduction_US_MO extends PayrollDeduction_US {
	/*
															10 => 'Single',
															20 => 'Married - Spouse Works',
															30 => 'Married - Spouse does not Work',
															40 => 'Head of Household',
	*/

	var $state_income_tax_rate_options = [
		//Constants are calculated strange from the Government, just use their values. Remember to add all constant values from bottom to top together for each bracket. ie: 16 + 37 + 63 + 95, ...
		20240101 => [
				10 => [
						[ 'income' => 1273, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2546, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3819, 'rate' => 2.5, 'constant' => 25 ],
						[ 'income' => 5092, 'rate' => 3.0, 'constant' => 57 ],
						[ 'income' => 6365, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 7638, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 8911, 'rate' => 4.5, 'constant' => 191 ],
						[ 'income' => 8911, 'rate' => 4.80, 'constant' => 248 ],
				],
				20 => [
						[ 'income' => 1273, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2546, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3819, 'rate' => 2.5, 'constant' => 25 ],
						[ 'income' => 5092, 'rate' => 3.0, 'constant' => 57 ],
						[ 'income' => 6365, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 7638, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 8911, 'rate' => 4.5, 'constant' => 191 ],
						[ 'income' => 8911, 'rate' => 4.80, 'constant' => 248 ],
				],
				30 => [
						[ 'income' => 1273, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2546, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3819, 'rate' => 2.5, 'constant' => 25 ],
						[ 'income' => 5092, 'rate' => 3.0, 'constant' => 57 ],
						[ 'income' => 6365, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 7638, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 8911, 'rate' => 4.5, 'constant' => 191 ],
						[ 'income' => 8911, 'rate' => 4.80, 'constant' => 248 ],
				],
				40 => [
						[ 'income' => 1273, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2546, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3819, 'rate' => 2.5, 'constant' => 25 ],
						[ 'income' => 5092, 'rate' => 3.0, 'constant' => 57 ],
						[ 'income' => 6365, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 7638, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 8911, 'rate' => 4.5, 'constant' => 191 ],
						[ 'income' => 8911, 'rate' => 4.80, 'constant' => 248 ],
				],
		],
		20230101 => [
				10 => [
						[ 'income' => 1207, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2414, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3621, 'rate' => 2.5, 'constant' => 24 ],
						[ 'income' => 4828, 'rate' => 3.0, 'constant' => 54 ],
						[ 'income' => 6035, 'rate' => 3.5, 'constant' => 90 ],
						[ 'income' => 7242, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 8449, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8449, 'rate' => 4.95, 'constant' => 234 ],
				],
				20 => [
						[ 'income' => 1207, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2414, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3621, 'rate' => 2.5, 'constant' => 24 ],
						[ 'income' => 4828, 'rate' => 3.0, 'constant' => 54 ],
						[ 'income' => 6035, 'rate' => 3.5, 'constant' => 90 ],
						[ 'income' => 7242, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 8449, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8449, 'rate' => 4.95, 'constant' => 234 ],
				],
				30 => [
						[ 'income' => 1207, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2414, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3621, 'rate' => 2.5, 'constant' => 24 ],
						[ 'income' => 4828, 'rate' => 3.0, 'constant' => 54 ],
						[ 'income' => 6035, 'rate' => 3.5, 'constant' => 90 ],
						[ 'income' => 7242, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 8449, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8449, 'rate' => 4.95, 'constant' => 234 ],
				],
				40 => [
						[ 'income' => 1207, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 2414, 'rate' => 2.0, 'constant' => 0 ],
						[ 'income' => 3621, 'rate' => 2.5, 'constant' => 24 ],
						[ 'income' => 4828, 'rate' => 3.0, 'constant' => 54 ],
						[ 'income' => 6035, 'rate' => 3.5, 'constant' => 90 ],
						[ 'income' => 7242, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 8449, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8449, 'rate' => 4.95, 'constant' => 234 ],
				],
		],
		20220101 => [
				10 => [
						[ 'income' => 1121, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2242, 'rate' => 2.0, 'constant' => 17 ],
						[ 'income' => 3363, 'rate' => 2.5, 'constant' => 39 ],
						[ 'income' => 4484, 'rate' => 3.0, 'constant' => 67 ],
						[ 'income' => 5605, 'rate' => 3.5, 'constant' => 101 ],
						[ 'income' => 6726, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 7847, 'rate' => 4.5, 'constant' => 185 ],
						[ 'income' => 8968, 'rate' => 5.0, 'constant' => 235 ],
						[ 'income' => 8968, 'rate' => 5.3, 'constant' => 291 ],
				],
				20 => [
						[ 'income' => 1121, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2242, 'rate' => 2.0, 'constant' => 17 ],
						[ 'income' => 3363, 'rate' => 2.5, 'constant' => 39 ],
						[ 'income' => 4484, 'rate' => 3.0, 'constant' => 67 ],
						[ 'income' => 5605, 'rate' => 3.5, 'constant' => 101 ],
						[ 'income' => 6726, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 7847, 'rate' => 4.5, 'constant' => 185 ],
						[ 'income' => 8968, 'rate' => 5.0, 'constant' => 235 ],
						[ 'income' => 8968, 'rate' => 5.3, 'constant' => 291 ],
				],
				30 => [
						[ 'income' => 1121, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2242, 'rate' => 2.0, 'constant' => 17 ],
						[ 'income' => 3363, 'rate' => 2.5, 'constant' => 39 ],
						[ 'income' => 4484, 'rate' => 3.0, 'constant' => 67 ],
						[ 'income' => 5605, 'rate' => 3.5, 'constant' => 101 ],
						[ 'income' => 6726, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 7847, 'rate' => 4.5, 'constant' => 185 ],
						[ 'income' => 8968, 'rate' => 5.0, 'constant' => 235 ],
						[ 'income' => 8968, 'rate' => 5.3, 'constant' => 291 ],
				],
				40 => [
						[ 'income' => 1121, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2242, 'rate' => 2.0, 'constant' => 17 ],
						[ 'income' => 3363, 'rate' => 2.5, 'constant' => 39 ],
						[ 'income' => 4484, 'rate' => 3.0, 'constant' => 67 ],
						[ 'income' => 5605, 'rate' => 3.5, 'constant' => 101 ],
						[ 'income' => 6726, 'rate' => 4.0, 'constant' => 140 ],
						[ 'income' => 7847, 'rate' => 4.5, 'constant' => 185 ],
						[ 'income' => 8968, 'rate' => 5.0, 'constant' => 235 ],
						[ 'income' => 8968, 'rate' => 5.3, 'constant' => 291 ],
				],
		],
		20210101 => [
				10 => [
						[ 'income' => 1088, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2176, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3264, 'rate' => 2.5, 'constant' => 38 ],
						[ 'income' => 4352, 'rate' => 3.0, 'constant' => 65 ],
						[ 'income' => 5440, 'rate' => 3.5, 'constant' => 98 ],
						[ 'income' => 6528, 'rate' => 4.0, 'constant' => 136 ],
						[ 'income' => 7616, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8704, 'rate' => 5.0, 'constant' => 229 ],
						[ 'income' => 8704, 'rate' => 5.4, 'constant' => 283 ],
				],
				20 => [
						[ 'income' => 1088, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2176, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3264, 'rate' => 2.5, 'constant' => 38 ],
						[ 'income' => 4352, 'rate' => 3.0, 'constant' => 65 ],
						[ 'income' => 5440, 'rate' => 3.5, 'constant' => 98 ],
						[ 'income' => 6528, 'rate' => 4.0, 'constant' => 136 ],
						[ 'income' => 7616, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8704, 'rate' => 5.0, 'constant' => 229 ],
						[ 'income' => 8704, 'rate' => 5.4, 'constant' => 283 ],
				],
				30 => [
						[ 'income' => 1088, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2176, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3264, 'rate' => 2.5, 'constant' => 38 ],
						[ 'income' => 4352, 'rate' => 3.0, 'constant' => 65 ],
						[ 'income' => 5440, 'rate' => 3.5, 'constant' => 98 ],
						[ 'income' => 6528, 'rate' => 4.0, 'constant' => 136 ],
						[ 'income' => 7616, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8704, 'rate' => 5.0, 'constant' => 229 ],
						[ 'income' => 8704, 'rate' => 5.4, 'constant' => 283 ],
				],
				40 => [
						[ 'income' => 1088, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2176, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3264, 'rate' => 2.5, 'constant' => 38 ],
						[ 'income' => 4352, 'rate' => 3.0, 'constant' => 65 ],
						[ 'income' => 5440, 'rate' => 3.5, 'constant' => 98 ],
						[ 'income' => 6528, 'rate' => 4.0, 'constant' => 136 ],
						[ 'income' => 7616, 'rate' => 4.5, 'constant' => 180 ],
						[ 'income' => 8704, 'rate' => 5.0, 'constant' => 229 ],
						[ 'income' => 8704, 'rate' => 5.4, 'constant' => 283 ],
				],
		],
		20200101 => [
				10 => [
						[ 'income' => 1073, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2146, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3219, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4292, 'rate' => 3.0, 'constant' => 64 ],
						[ 'income' => 5365, 'rate' => 3.5, 'constant' => 96 ],
						[ 'income' => 6438, 'rate' => 4.0, 'constant' => 134 ],
						[ 'income' => 7511, 'rate' => 4.5, 'constant' => 177 ],
						[ 'income' => 8584, 'rate' => 5.0, 'constant' => 225 ],
						[ 'income' => 8584, 'rate' => 5.4, 'constant' => 279 ],
				],
				20 => [
						[ 'income' => 1073, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2146, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3219, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4292, 'rate' => 3.0, 'constant' => 64 ],
						[ 'income' => 5365, 'rate' => 3.5, 'constant' => 96 ],
						[ 'income' => 6438, 'rate' => 4.0, 'constant' => 134 ],
						[ 'income' => 7511, 'rate' => 4.5, 'constant' => 177 ],
						[ 'income' => 8584, 'rate' => 5.0, 'constant' => 225 ],
						[ 'income' => 8584, 'rate' => 5.4, 'constant' => 279 ],
				],
				30 => [
						[ 'income' => 1073, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2146, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3219, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4292, 'rate' => 3.0, 'constant' => 64 ],
						[ 'income' => 5365, 'rate' => 3.5, 'constant' => 96 ],
						[ 'income' => 6438, 'rate' => 4.0, 'constant' => 134 ],
						[ 'income' => 7511, 'rate' => 4.5, 'constant' => 177 ],
						[ 'income' => 8584, 'rate' => 5.0, 'constant' => 225 ],
						[ 'income' => 8584, 'rate' => 5.4, 'constant' => 279 ],
				],
				40 => [
						[ 'income' => 1073, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2146, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3219, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4292, 'rate' => 3.0, 'constant' => 64 ],
						[ 'income' => 5365, 'rate' => 3.5, 'constant' => 96 ],
						[ 'income' => 6438, 'rate' => 4.0, 'constant' => 134 ],
						[ 'income' => 7511, 'rate' => 4.5, 'constant' => 177 ],
						[ 'income' => 8584, 'rate' => 5.0, 'constant' => 225 ],
						[ 'income' => 8584, 'rate' => 5.4, 'constant' => 279 ],
				],
		],
		20190101 => [
				10 => [
						[ 'income' => 1053, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2106, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3159, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4212, 'rate' => 3.0, 'constant' => 63 ],
						[ 'income' => 5265, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 6318, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 7371, 'rate' => 4.5, 'constant' => 174 ],
						[ 'income' => 8424, 'rate' => 5.0, 'constant' => 221 ],
						[ 'income' => 8424, 'rate' => 5.4, 'constant' => 274 ],
				],
				20 => [
						[ 'income' => 1053, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2106, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3159, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4212, 'rate' => 3.0, 'constant' => 63 ],
						[ 'income' => 5265, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 6318, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 7371, 'rate' => 4.5, 'constant' => 174 ],
						[ 'income' => 8424, 'rate' => 5.0, 'constant' => 221 ],
						[ 'income' => 8424, 'rate' => 5.4, 'constant' => 274 ],
				],
				30 => [
						[ 'income' => 1053, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2106, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3159, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4212, 'rate' => 3.0, 'constant' => 63 ],
						[ 'income' => 5265, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 6318, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 7371, 'rate' => 4.5, 'constant' => 174 ],
						[ 'income' => 8424, 'rate' => 5.0, 'constant' => 221 ],
						[ 'income' => 8424, 'rate' => 5.4, 'constant' => 274 ],
				],
				40 => [
						[ 'income' => 1053, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2106, 'rate' => 2.0, 'constant' => 16 ],
						[ 'income' => 3159, 'rate' => 2.5, 'constant' => 37 ],
						[ 'income' => 4212, 'rate' => 3.0, 'constant' => 63 ],
						[ 'income' => 5265, 'rate' => 3.5, 'constant' => 95 ],
						[ 'income' => 6318, 'rate' => 4.0, 'constant' => 132 ],
						[ 'income' => 7371, 'rate' => 4.5, 'constant' => 174 ],
						[ 'income' => 8424, 'rate' => 5.0, 'constant' => 221 ],
						[ 'income' => 8424, 'rate' => 5.4, 'constant' => 274 ],
				],
		],
		20180101 => [
				10 => [
						[ 'income' => 103, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 1028, 'rate' => 1.5, 'constant' => 0.00 ],
						[ 'income' => 2056, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3084, 'rate' => 2.5, 'constant' => 36.00 ],
						[ 'income' => 4113, 'rate' => 3.0, 'constant' => 62.00 ],
						[ 'income' => 5141, 'rate' => 3.5, 'constant' => 93.00 ],
						[ 'income' => 6169, 'rate' => 4.0, 'constant' => 129.00 ],
						[ 'income' => 7197, 'rate' => 4.5, 'constant' => 170.00 ],
						[ 'income' => 8225, 'rate' => 5.0, 'constant' => 216.00 ],
						[ 'income' => 9253, 'rate' => 5.5, 'constant' => 267.00 ],
						[ 'income' => 9253, 'rate' => 5.9, 'constant' => 324.00 ],
				],
				20 => [
						[ 'income' => 103, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 1028, 'rate' => 1.5, 'constant' => 0.00 ],
						[ 'income' => 2056, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3084, 'rate' => 2.5, 'constant' => 36.00 ],
						[ 'income' => 4113, 'rate' => 3.0, 'constant' => 62.00 ],
						[ 'income' => 5141, 'rate' => 3.5, 'constant' => 93.00 ],
						[ 'income' => 6169, 'rate' => 4.0, 'constant' => 129.00 ],
						[ 'income' => 7197, 'rate' => 4.5, 'constant' => 170.00 ],
						[ 'income' => 8225, 'rate' => 5.0, 'constant' => 216.00 ],
						[ 'income' => 9253, 'rate' => 5.5, 'constant' => 267.00 ],
						[ 'income' => 9253, 'rate' => 5.9, 'constant' => 324.00 ],
				],
				30 => [
						[ 'income' => 103, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 1028, 'rate' => 1.5, 'constant' => 0.00 ],
						[ 'income' => 2056, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3084, 'rate' => 2.5, 'constant' => 36.00 ],
						[ 'income' => 4113, 'rate' => 3.0, 'constant' => 62.00 ],
						[ 'income' => 5141, 'rate' => 3.5, 'constant' => 93.00 ],
						[ 'income' => 6169, 'rate' => 4.0, 'constant' => 129.00 ],
						[ 'income' => 7197, 'rate' => 4.5, 'constant' => 170.00 ],
						[ 'income' => 8225, 'rate' => 5.0, 'constant' => 216.00 ],
						[ 'income' => 9253, 'rate' => 5.5, 'constant' => 267.00 ],
						[ 'income' => 9253, 'rate' => 5.9, 'constant' => 324.00 ],
				],
				40 => [
						[ 'income' => 103, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 1028, 'rate' => 1.5, 'constant' => 0.00 ],
						[ 'income' => 2056, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3084, 'rate' => 2.5, 'constant' => 36.00 ],
						[ 'income' => 4113, 'rate' => 3.0, 'constant' => 62.00 ],
						[ 'income' => 5141, 'rate' => 3.5, 'constant' => 93.00 ],
						[ 'income' => 6169, 'rate' => 4.0, 'constant' => 129.00 ],
						[ 'income' => 7197, 'rate' => 4.5, 'constant' => 170.00 ],
						[ 'income' => 8225, 'rate' => 5.0, 'constant' => 216.00 ],
						[ 'income' => 9253, 'rate' => 5.5, 'constant' => 267.00 ],
						[ 'income' => 9253, 'rate' => 5.9, 'constant' => 324.00 ],
				],
		],
		20170101 => [
				10 => [
						[ 'income' => 1008, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2016, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3024, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4032, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5040, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6048, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7056, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8064, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9072, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9072, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				20 => [
						[ 'income' => 1008, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2016, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3024, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4032, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5040, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6048, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7056, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8064, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9072, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9072, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				30 => [
						[ 'income' => 1008, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2016, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3024, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4032, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5040, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6048, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7056, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8064, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9072, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9072, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				40 => [
						[ 'income' => 1008, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2016, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3024, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4032, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5040, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6048, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7056, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8064, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9072, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9072, 'rate' => 6.0, 'constant' => 315.00 ],
				],
		],
		20060101 => [
				10 => [
						[ 'income' => 1000, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2000, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3000, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4000, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5000, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6000, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7000, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8000, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9000, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9000, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				20 => [
						[ 'income' => 1000, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2000, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3000, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4000, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5000, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6000, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7000, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8000, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9000, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9000, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				30 => [
						[ 'income' => 1000, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2000, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3000, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4000, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5000, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6000, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7000, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8000, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9000, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9000, 'rate' => 6.0, 'constant' => 315.00 ],
				],
				40 => [
						[ 'income' => 1000, 'rate' => 1.5, 'constant' => 0 ],
						[ 'income' => 2000, 'rate' => 2.0, 'constant' => 15.00 ],
						[ 'income' => 3000, 'rate' => 2.5, 'constant' => 35.00 ],
						[ 'income' => 4000, 'rate' => 3.0, 'constant' => 60.00 ],
						[ 'income' => 5000, 'rate' => 3.5, 'constant' => 90.00 ],
						[ 'income' => 6000, 'rate' => 4.0, 'constant' => 125.00 ],
						[ 'income' => 7000, 'rate' => 4.5, 'constant' => 165.00 ],
						[ 'income' => 8000, 'rate' => 5.0, 'constant' => 210.00 ],
						[ 'income' => 9000, 'rate' => 5.5, 'constant' => 260.00 ],
						[ 'income' => 9000, 'rate' => 6.0, 'constant' => 315.00 ],
				],
		],
	];

	var $state_options = [
			20240101 => [
					'standard_deduction' => [
							'10' => 14600.00,
							'20' => 14600.00,
							'30' => 29200.00,
							'40' => 21900.00,
					],
			],
			20230101 => [
					'standard_deduction' => [
							'10' => 13850.00,
							'20' => 13850.00,
							'30' => 27700.00,
							'40' => 20800.00,
					],
			],
			20220101 => [
					'standard_deduction' => [
							'10' => 12950.00,
							'20' => 12950.00,
							'30' => 25900.00,
							'40' => 19400.00,
					],
			],
			20210101 => [
					'standard_deduction' => [
							'10' => 12550.00,
							'20' => 12550.00,
							'30' => 25100.00,
							'40' => 18800.00,
					],
			],
			20200101 => [
					'standard_deduction' => [
							'10' => 12400.00,
							'20' => 12400.00,
							'30' => 24800.00,
							'40' => 18650.00,
					],
			],
			20190101 => [
					'standard_deduction'  => [
							'10' => 12200.00,
							'20' => 12200.00,
							'30' => 24400.00,
							'40' => 18350.00,
					],
					'federal_tax_maximum' => [ //Removed in 2019.
											   '10' => 5000.00,
											   '20' => 5000.00,
											   '30' => 10000.00,
											   '40' => 5000.00,
					],
			],
			20180101 => [
					'standard_deduction'  => [
							'10' => 12000.00,
							'20' => 12000.00,
							'30' => 24000.00,
							'40' => 18000.00,
					],
					'allowance'           => [ //Removed in 2018.
											   '10' => [ 2100.00, 1200.00, 1200.00 ],
											   '20' => [ 2100.00, 1200.00, 1200.00 ],
											   '30' => [ 2100.00, 2100.00, 1200.00 ],
											   '40' => [ 3500.00, 1200.00, 1200.00 ],
					],
					'federal_tax_maximum' => [
							'10' => 5000.00,
							'20' => 5000.00,
							'30' => 10000.00,
							'40' => 5000.00,
					],
			],
			20170101 => [
					'standard_deduction'  => [
							'10' => 6350.00,
							'20' => 6350.00,
							'30' => 12700.00,
							'40' => 9350.00,
					],
					'allowance'           => [
							'10' => [ 2100.00, 1200.00, 1200.00 ],
							'20' => [ 2100.00, 1200.00, 1200.00 ],
							'30' => [ 2100.00, 2100.00, 1200.00 ],
							'40' => [ 3500.00, 1200.00, 1200.00 ],
					],
					'federal_tax_maximum' => [
							'10' => 5000.00,
							'20' => 5000.00,
							'30' => 10000.00,
							'40' => 5000.00,
					],
			],
			20160101 => [
					'standard_deduction'  => [
							'10' => 6300.00,
							'20' => 6300.00,
							'30' => 12600.00,
							'40' => 9300.00,
					],
					'allowance'           => [
							'10' => [ 2100.00, 1200.00, 1200.00 ],
							'20' => [ 2100.00, 1200.00, 1200.00 ],
							'30' => [ 2100.00, 2100.00, 1200.00 ],
							'40' => [ 3500.00, 1200.00, 1200.00 ],
					],
					'federal_tax_maximum' => [
							'10' => 5000.00,
							'20' => 5000.00,
							'30' => 10000.00,
							'40' => 5000.00,
					],
			],
			20150101 => [ //01-Jan-15
						  'standard_deduction'  => [
								  '10' => 6300.00,
								  '20' => 6300.00,
								  '30' => 12600.00,
								  '40' => 9250.00,
						  ],
						  'allowance'           => [
								  '10' => [ 2100.00, 1200.00, 1200.00 ],
								  '20' => [ 2100.00, 1200.00, 1200.00 ],
								  '30' => [ 2100.00, 2100.00, 1200.00 ],
								  '40' => [ 3500.00, 1200.00, 1200.00 ],
						  ],
						  'federal_tax_maximum' => [
								  '10' => 5000.00,
								  '20' => 5000.00,
								  '30' => 10000.00,
								  '40' => 5000.00,
						  ],
			],
			20140101 => [ //01-Jan-14
						  'standard_deduction'  => [
								  '10' => 6200.00,
								  '20' => 6200.00,
								  '30' => 12400.00,
								  '40' => 9100.00,
						  ],
						  'allowance'           => [
								  '10' => [ 2100.00, 1200.00, 1200.00 ],
								  '20' => [ 2100.00, 1200.00, 1200.00 ],
								  '30' => [ 2100.00, 2100.00, 1200.00 ],
								  '40' => [ 3500.00, 1200.00, 1200.00 ],
						  ],
						  'federal_tax_maximum' => [
								  '10' => 5000.00,
								  '20' => 5000.00,
								  '30' => 10000.00,
								  '40' => 5000.00,
						  ],
			],
			20130101 => [ //01-Jan-13
						  'standard_deduction'  => [
								  '10' => 6100.00,
								  '20' => 6100.00,
								  '30' => 12200.00,
								  '40' => 8950.00,
						  ],
						  'allowance'           => [
								  '10' => [ 2100.00, 1200.00, 1200.00 ],
								  '20' => [ 2100.00, 1200.00, 1200.00 ],
								  '30' => [ 2100.00, 2100.00, 1200.00 ],
								  '40' => [ 3500.00, 1200.00, 1200.00 ],
						  ],
						  'federal_tax_maximum' => [
								  '10' => 5000.00,
								  '20' => 5000.00,
								  '30' => 10000.00,
								  '40' => 5000.00,
						  ],
			],
			20120101 => [ //01-Jan-12
						  'standard_deduction'  => [
								  '10' => 5800.00,
								  '20' => 5800.00,
								  '30' => 11600.00,
								  '40' => 8500.00,
						  ],
						  'allowance'           => [
								  '10' => [ 2100.00, 1200.00, 1200.00 ],
								  '20' => [ 2100.00, 1200.00, 1200.00 ],
								  '30' => [ 2100.00, 2100.00, 1200.00 ],
								  '40' => [ 3500.00, 1200.00, 1200.00 ],
						  ],
						  'federal_tax_maximum' => [
								  '10' => 5000.00,
								  '20' => 5000.00,
								  '30' => 10000.00,
								  '40' => 5000.00,
						  ],
			],
			20090101 => [ //01-Jan-09
						  'standard_deduction'  => [
								  '10' => 5700.00,
								  '20' => 5700.00,
								  '30' => 11400.00,
								  '40' => 8350.00,
						  ],
						  'allowance'           => [
								  '10' => [ 2100.00, 1200.00, 1200.00 ],
								  '20' => [ 2100.00, 1200.00, 1200.00 ],
								  '30' => [ 2100.00, 2100.00, 1200.00 ],
								  '40' => [ 3500.00, 1200.00, 1200.00 ],
						  ],
						  'federal_tax_maximum' => [
								  '10' => 5000.00,
								  '20' => 5000.00,
								  '30' => 10000.00,
								  '40' => 5000.00,
						  ],
			],
			20070101 => [
					'standard_deduction'  => [
							'10' => 5350.00,
							'20' => 5350.00,
							'30' => 10700.00,
							'40' => 7850.00,
					],
					'allowance'           => [
							'10' => [ 1200.00, 1200.00, 1200.00 ],
							'20' => [ 1200.00, 1200.00, 1200.00 ],
							'30' => [ 1200.00, 1200.00, 1200.00 ],
							'40' => [ 3500.00, 1200.00, 1200.00 ],
					],
					'federal_tax_maximum' => [
							'10' => 5000.00,
							'20' => 5000.00,
							'30' => 10000.00,
							'40' => 5000.00,
					],
			],
			20060101 => [
					'standard_deduction'  => [
							'10' => 5150.00,
							'20' => 5150.00,
							'30' => 10300.00,
							'40' => 7550.00,
					],
					'allowance'           => [
							'10' => [ 1200.00, 1200.00, 1200.00 ],
							'20' => [ 1200.00, 1200.00, 1200.00 ],
							'30' => [ 1200.00, 1200.00, 1200.00 ],
							'40' => [ 3500.00, 1200.00, 1200.00 ],
					],
					'federal_tax_maximum' => [
							'10' => 5000.00,
							'20' => 5000.00,
							'30' => 10000.00,
							'40' => 5000.00,
					],

			],
	];

	var $state_ui_options = [
			20240101 => [ 'wage_base' => 10000, 'new_employer_rate' => 2.376 ],
			20230101 => [ 'wage_base' => 10500, 'new_employer_rate' => 2.376 ],
			20220101 => [ 'wage_base' => 11000, 'new_employer_rate' => 2.376 ],
			20210101 => [ 'wage_base' => 11000, 'new_employer_rate' => 2.376 ],
			20200101 => [ 'wage_base' => 11500, 'new_employer_rate' => 2.376 ],
			20190101 => [ 'wage_base' => 12000, 'new_employer_rate' => 2.376 ],
	];

	function isFederalTaxRequired() {
		return true;
	}

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		Debug::text( 'State Federal Tax: ' . $federal_tax, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getDate() < 20190101 ) { //Removed for 2019
			if ( $federal_tax > $this->getStateFederalTaxMaximum() ) {
				$federal_tax = $this->getStateFederalTaxMaximum();
			}
		} else {
			$federal_tax = 0;
		}

		$income = TTMath::sub( TTMath::sub( TTMath::sub( $annual_income, $federal_tax ), $state_deductions ), $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$maximum = $retarr['federal_tax_maximum'][$this->getStateFilingStatus()];

		Debug::text( 'Maximum State allowed Federal Tax: ' . $maximum, __FILE__, __LINE__, __METHOD__, 10 );

		return $maximum;
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

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getDate() < 20180101 ) { //Removed for 2018
			$allowance_arr = $retarr['allowance'][$this->getStateFilingStatus()];

			if ( $this->getStateAllowance() == 0 ) {
				$retval = 0;
			} else if ( $this->getStateAllowance() == 1 ) {
				$retval = $allowance_arr[0];
			} else if ( $this->getStateAllowance() == 2 ) {
				$retval = TTMath::add( $allowance_arr[0], $allowance_arr[1] );
			} else {
				$retval = TTMath::add( $allowance_arr[0], TTMath::add( $allowance_arr[1], TTMath::mul( TTMath::sub( $this->getStateAllowance(), 2 ), $allowance_arr[2] ) ) );
			}
		} else {
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
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );

			$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $prev_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
