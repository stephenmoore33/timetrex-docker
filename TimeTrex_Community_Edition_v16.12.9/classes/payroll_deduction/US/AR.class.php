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
class PayrollDeduction_US_AR extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
		//As of 20200301 use the tax brackets verbatim, where the "constant" is minused rather than added.
		20240701 => [
				0 => [  //They changed the brackets again to almost be like a tax table. Just using every $1000 over 84500.
						[ 'income' => 5299, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 10599, 'rate' => 2.0, 'constant' => 105.98 ],
						[ 'income' => 15099, 'rate' => 3.0, 'constant' => 211.97 ],
						[ 'income' => 24999, 'rate' => 3.4, 'constant' => 272.37 ],
						[ 'income' => 89600, 'rate' => 3.9, 'constant' => 397.36 ],
						[ 'income' => 89700, 'rate' => 3.9, 'constant' => 395.50 ],
						[ 'income' => 89800, 'rate' => 3.9, 'constant' => 385.50 ],
						[ 'income' => 89900, 'rate' => 3.9, 'constant' => 375.50 ],
						[ 'income' => 90000, 'rate' => 3.9, 'constant' => 365.50 ],
						[ 'income' => 90100, 'rate' => 3.9, 'constant' => 355.50 ],
						[ 'income' => 90200, 'rate' => 3.9, 'constant' => 345.50 ],
						[ 'income' => 90300, 'rate' => 3.9, 'constant' => 335.50 ],
						[ 'income' => 90400, 'rate' => 3.9, 'constant' => 325.50 ],
						[ 'income' => 90500, 'rate' => 3.9, 'constant' => 315.50 ],
						[ 'income' => 90600, 'rate' => 3.9, 'constant' => 305.50 ],
						[ 'income' => 90700, 'rate' => 3.9, 'constant' => 295.50 ],
						[ 'income' => 90800, 'rate' => 3.9, 'constant' => 285.50 ],
						[ 'income' => 90900, 'rate' => 3.9, 'constant' => 275.50 ],
						[ 'income' => 91000, 'rate' => 3.9, 'constant' => 265.50 ],
						[ 'income' => 91100, 'rate' => 3.9, 'constant' => 255.50 ],
						[ 'income' => 91200, 'rate' => 3.9, 'constant' => 245.50 ],
						[ 'income' => 91300, 'rate' => 3.9, 'constant' => 235.50 ],
						[ 'income' => 91400, 'rate' => 3.9, 'constant' => 225.50 ],
						[ 'income' => 91500, 'rate' => 3.9, 'constant' => 215.50 ],
						[ 'income' => 91600, 'rate' => 3.9, 'constant' => 205.50 ],
						[ 'income' => 91700, 'rate' => 3.9, 'constant' => 195.50 ],
						[ 'income' => 91800, 'rate' => 3.9, 'constant' => 185.50 ],
						[ 'income' => 91900, 'rate' => 3.9, 'constant' => 175.50 ],
						[ 'income' => 92000, 'rate' => 3.9, 'constant' => 165.50 ],
						[ 'income' => 92100, 'rate' => 3.9, 'constant' => 155.50 ],
						[ 'income' => 92200, 'rate' => 3.9, 'constant' => 145.50 ],
						[ 'income' => 92300, 'rate' => 3.9, 'constant' => 135.50 ],
						[ 'income' => 92400, 'rate' => 3.9, 'constant' => 125.50 ],
						[ 'income' => 92500, 'rate' => 3.9, 'constant' => 115.50 ],
						[ 'income' => 92600, 'rate' => 3.9, 'constant' => 105.50 ],
						[ 'income' => 92700, 'rate' => 3.9, 'constant' => 95.50 ],
						[ 'income' => 100000, 'rate' => 3.9, 'constant' => 85.50 ],
						[ 'income' => 100000, 'rate' => 3.9, 'constant' => 85.50 ]
				],
		],
		20240101 => [
				0 => [  //They changed the brackets again to almost be like a tax table. Just using every $1000 over 84500.
						[ 'income' => 5299, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 10599, 'rate' => 2.0, 'constant' => 105.98 ],
						[ 'income' => 15099, 'rate' => 3.0, 'constant' => 211.97 ],
						[ 'income' => 24999, 'rate' => 3.4, 'constant' => 272.37 ],
						[ 'income' => 89600, 'rate' => 4.4, 'constant' => 522.36 ],
						[ 'income' => 89700, 'rate' => 4.4, 'constant' => 506.40 ],
						[ 'income' => 89800, 'rate' => 4.4, 'constant' => 496.40 ],
						[ 'income' => 89900, 'rate' => 4.4, 'constant' => 486.40 ],
						[ 'income' => 90000, 'rate' => 4.4, 'constant' => 476.40 ],
						[ 'income' => 90200, 'rate' => 4.4, 'constant' => 466.40 ],
						[ 'income' => 90300, 'rate' => 4.4, 'constant' => 456.40 ],
						[ 'income' => 90400, 'rate' => 4.4, 'constant' => 446.40 ],
						[ 'income' => 90500, 'rate' => 4.4, 'constant' => 436.40 ],
						[ 'income' => 90600, 'rate' => 4.4, 'constant' => 426.40 ],
						[ 'income' => 90700, 'rate' => 4.4, 'constant' => 416.40 ],
						[ 'income' => 90800, 'rate' => 4.4, 'constant' => 406.40 ],
						[ 'income' => 90900, 'rate' => 4.4, 'constant' => 396.40 ],
						[ 'income' => 91100, 'rate' => 4.4, 'constant' => 386.40 ],
						[ 'income' => 91200, 'rate' => 4.4, 'constant' => 376.40 ],
						[ 'income' => 91300, 'rate' => 4.4, 'constant' => 366.40 ],
						[ 'income' => 91400, 'rate' => 4.4, 'constant' => 356.40 ],
						[ 'income' => 91500, 'rate' => 4.4, 'constant' => 346.40 ],
						[ 'income' => 91600, 'rate' => 4.4, 'constant' => 336.40 ],
						[ 'income' => 91700, 'rate' => 4.4, 'constant' => 326.40 ],
						[ 'income' => 91800, 'rate' => 4.4, 'constant' => 316.40 ],
						[ 'income' => 91900, 'rate' => 4.4, 'constant' => 306.40 ],
						[ 'income' => 92000, 'rate' => 4.4, 'constant' => 296.40 ],
						[ 'income' => 92100, 'rate' => 4.4, 'constant' => 286.40 ],
						[ 'income' => 92200, 'rate' => 4.4, 'constant' => 276.40 ],
						[ 'income' => 92300, 'rate' => 4.4, 'constant' => 266.40 ],
						[ 'income' => 92400, 'rate' => 4.4, 'constant' => 256.40 ],
						[ 'income' => 92500, 'rate' => 4.4, 'constant' => 246.40 ],
						[ 'income' => 92600, 'rate' => 4.4, 'constant' => 236.40 ],
						[ 'income' => 92700, 'rate' => 4.4, 'constant' => 226.40 ],
						[ 'income' => 92800, 'rate' => 4.4, 'constant' => 216.40 ],
						[ 'income' => 92900, 'rate' => 4.4, 'constant' => 206.40 ],
						[ 'income' => 93000, 'rate' => 4.4, 'constant' => 196.40 ],
						[ 'income' => 93100, 'rate' => 4.4, 'constant' => 186.40 ],
						[ 'income' => 93200, 'rate' => 4.4, 'constant' => 176.40 ],
						[ 'income' => 93300, 'rate' => 4.4, 'constant' => 166.40 ],
						[ 'income' => 93400, 'rate' => 4.4, 'constant' => 156.40 ],
						[ 'income' => 93500, 'rate' => 4.4, 'constant' => 146.40 ],
						[ 'income' => 93600, 'rate' => 4.4, 'constant' => 136.40 ],
						[ 'income' => 100000, 'rate' => 4.4, 'constant' => 126.40 ],
						[ 'income' => 100000, 'rate' => 4.4, 'constant' => 126.40 ],
				],
		],
		20230601 => [
				0 => [  //They changed the brackets again to almost be like a tax table. Just using every $1000 over 84500.
						[ 'income' => 5099, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 10299, 'rate' => 2.0, 'constant' => 101.98 ],
						[ 'income' => 14699, 'rate' => 3.0, 'constant' => 204.97 ],
						[ 'income' => 24299, 'rate' => 3.4, 'constant' => 263.77 ],

						[ 'income' => 87000, 'rate' => 4.7, 'constant' => 579.65 ],
						[ 'income' => 87100, 'rate' => 4.7, 'constant' => 579.60 ],
						[ 'income' => 87200, 'rate' => 4.7, 'constant' => 569.60 ],
						[ 'income' => 87300, 'rate' => 4.7, 'constant' => 559.60 ],
						[ 'income' => 87400, 'rate' => 4.7, 'constant' => 549.60 ],
						[ 'income' => 87500, 'rate' => 4.7, 'constant' => 539.60 ],
						[ 'income' => 87600, 'rate' => 4.7, 'constant' => 529.60 ],
						[ 'income' => 87700, 'rate' => 4.7, 'constant' => 519.60 ],
						[ 'income' => 87800, 'rate' => 4.7, 'constant' => 509.60 ],
						[ 'income' => 87900, 'rate' => 4.7, 'constant' => 499.60 ],

						[ 'income' => 88000, 'rate' => 4.7, 'constant' => 489.60 ],
						[ 'income' => 88100, 'rate' => 4.7, 'constant' => 479.60 ],
						[ 'income' => 88200, 'rate' => 4.7, 'constant' => 469.60 ],
						[ 'income' => 88300, 'rate' => 4.7, 'constant' => 459.60 ],
						[ 'income' => 88400, 'rate' => 4.7, 'constant' => 449.60 ],
						[ 'income' => 88500, 'rate' => 4.7, 'constant' => 439.60 ],
						[ 'income' => 88600, 'rate' => 4.7, 'constant' => 429.60 ],
						[ 'income' => 88700, 'rate' => 4.7, 'constant' => 419.60 ],
						[ 'income' => 88800, 'rate' => 4.7, 'constant' => 409.60 ],
						[ 'income' => 88900, 'rate' => 4.7, 'constant' => 399.60 ],

						[ 'income' => 89000, 'rate' => 4.7, 'constant' => 389.60 ],
						[ 'income' => 89100, 'rate' => 4.7, 'constant' => 379.60 ],
						[ 'income' => 89200, 'rate' => 4.7, 'constant' => 369.60 ],
						[ 'income' => 89300, 'rate' => 4.7, 'constant' => 359.60 ],
						[ 'income' => 89400, 'rate' => 4.7, 'constant' => 349.60 ],
						[ 'income' => 89500, 'rate' => 4.7, 'constant' => 339.60 ],
						[ 'income' => 89600, 'rate' => 4.7, 'constant' => 329.60 ],
						[ 'income' => 89700, 'rate' => 4.7, 'constant' => 319.60 ],
						[ 'income' => 89800, 'rate' => 4.7, 'constant' => 309.60 ],
						[ 'income' => 89900, 'rate' => 4.7, 'constant' => 299.60 ],

						[ 'income' => 90000, 'rate' => 4.7, 'constant' => 289.60 ],
						[ 'income' => 90100, 'rate' => 4.7, 'constant' => 279.60 ],
						[ 'income' => 90200, 'rate' => 4.7, 'constant' => 269.60 ],
						[ 'income' => 90300, 'rate' => 4.7, 'constant' => 259.60 ],
						[ 'income' => 90400, 'rate' => 4.7, 'constant' => 249.60 ],
						[ 'income' => 90500, 'rate' => 4.7, 'constant' => 239.60 ],
						[ 'income' => 90600, 'rate' => 4.7, 'constant' => 229.60 ],
						[ 'income' => 90700, 'rate' => 4.7, 'constant' => 219.60 ],
						[ 'income' => 90800, 'rate' => 4.7, 'constant' => 209.60 ],
						[ 'income' => 90900, 'rate' => 4.7, 'constant' => 199.60 ],

						[ 'income' => 91000, 'rate' => 4.7, 'constant' => 189.60 ],
						[ 'income' => 91100, 'rate' => 4.7, 'constant' => 179.60 ],
						[ 'income' => 91200, 'rate' => 4.7, 'constant' => 169.60 ],
						[ 'income' => 91300, 'rate' => 4.7, 'constant' => 159.60 ],
						[ 'income' => 91300, 'rate' => 4.7, 'constant' => 149.60 ],
				],
		],
		20221001 => [
				0 => [  //They changed the brackets again to almost be like a tax table. Just using every $1000 over 84500.
						[ 'income' => 5099, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 10299, 'rate' => 2.0, 'constant' => 101.98 ],
						[ 'income' => 14699, 'rate' => 3.0, 'constant' => 204.97 ],
						[ 'income' => 24299, 'rate' => 3.4, 'constant' => 263.77 ],

						[ 'income' => 87000, 'rate' => 4.9, 'constant' => 628.25 ],
						[ 'income' => 87100, 'rate' => 4.9, 'constant' => 627.20 ],
						[ 'income' => 87200, 'rate' => 4.9, 'constant' => 617.20 ],
						[ 'income' => 87300, 'rate' => 4.9, 'constant' => 607.20 ],
						[ 'income' => 87400, 'rate' => 4.9, 'constant' => 597.20 ],
						[ 'income' => 87600, 'rate' => 4.9, 'constant' => 587.20 ],
						[ 'income' => 87700, 'rate' => 4.9, 'constant' => 577.20 ],
						[ 'income' => 87800, 'rate' => 4.9, 'constant' => 567.20 ],
						[ 'income' => 87900, 'rate' => 4.9, 'constant' => 557.20 ],

						[ 'income' => 88000, 'rate' => 4.9, 'constant' => 547.20 ],
						[ 'income' => 88100, 'rate' => 4.9, 'constant' => 537.20 ],
						[ 'income' => 88200, 'rate' => 4.9, 'constant' => 527.20 ],
						[ 'income' => 88300, 'rate' => 4.9, 'constant' => 517.20 ],
						[ 'income' => 88400, 'rate' => 4.9, 'constant' => 507.20 ],
						[ 'income' => 88500, 'rate' => 4.9, 'constant' => 497.20 ],
						[ 'income' => 88600, 'rate' => 4.9, 'constant' => 487.20 ],
						[ 'income' => 88700, 'rate' => 4.9, 'constant' => 477.20 ],
						[ 'income' => 88800, 'rate' => 4.9, 'constant' => 467.20 ],
						[ 'income' => 88900, 'rate' => 4.9, 'constant' => 457.20 ],

						[ 'income' => 89000, 'rate' => 4.9, 'constant' => 447.20 ],
						[ 'income' => 89100, 'rate' => 4.9, 'constant' => 437.20 ],
						[ 'income' => 89200, 'rate' => 4.9, 'constant' => 427.20 ],
						[ 'income' => 89300, 'rate' => 4.9, 'constant' => 417.20 ],
						[ 'income' => 89400, 'rate' => 4.9, 'constant' => 407.20 ],
						[ 'income' => 89500, 'rate' => 4.9, 'constant' => 397.20 ],
						[ 'income' => 89600, 'rate' => 4.9, 'constant' => 387.20 ],
						[ 'income' => 89700, 'rate' => 4.9, 'constant' => 377.20 ],
						[ 'income' => 89800, 'rate' => 4.9, 'constant' => 367.20 ],
						[ 'income' => 89900, 'rate' => 4.9, 'constant' => 357.20 ],

						[ 'income' => 90000, 'rate' => 4.9, 'constant' => 347.20 ],
						[ 'income' => 90100, 'rate' => 4.9, 'constant' => 337.20 ],
						[ 'income' => 90200, 'rate' => 4.9, 'constant' => 327.20 ],
						[ 'income' => 90300, 'rate' => 4.9, 'constant' => 317.20 ],
						[ 'income' => 90400, 'rate' => 4.9, 'constant' => 307.20 ],
						[ 'income' => 90500, 'rate' => 4.9, 'constant' => 297.20 ],
						[ 'income' => 90600, 'rate' => 4.9, 'constant' => 287.20 ],
						[ 'income' => 90700, 'rate' => 4.9, 'constant' => 277.20 ],
						[ 'income' => 90800, 'rate' => 4.9, 'constant' => 267.20 ],
						[ 'income' => 90900, 'rate' => 4.9, 'constant' => 257.20 ],

						[ 'income' => 91100, 'rate' => 4.9, 'constant' => 247.20 ],
						[ 'income' => 91200, 'rate' => 4.9, 'constant' => 237.20 ],
						[ 'income' => 91300, 'rate' => 4.9, 'constant' => 227.20 ],
						[ 'income' => 91400, 'rate' => 4.9, 'constant' => 217.20 ],
						[ 'income' => 91500, 'rate' => 4.9, 'constant' => 207.20 ],
						[ 'income' => 91600, 'rate' => 4.9, 'constant' => 197.20 ],
						[ 'income' => 91700, 'rate' => 4.9, 'constant' => 187.20 ],
						[ 'income' => 91800, 'rate' => 4.9, 'constant' => 177.20 ],
						[ 'income' => 91800, 'rate' => 4.9, 'constant' => 167.20 ],
				],
		],
		20220101 => [
				0 => [  //They changed the brackets again to almost be like a tax table. Just using every $1000 over 84500.
						[ 'income' => 4999, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 9999, 'rate' => 2.0, 'constant' => 99.98 ],
						[ 'income' => 14299, 'rate' => 3.0, 'constant' => 199.97 ],
						[ 'income' => 23599, 'rate' => 3.4, 'constant' => 257.17 ],
						[ 'income' => 39699, 'rate' => 5.0, 'constant' => 634.75 ],
						[ 'income' => 84500, 'rate' => 5.5, 'constant' => 833.25 ],
						[ 'income' => 85000, 'rate' => 5.5, 'constant' => 783.50 ],
						[ 'income' => 86000, 'rate' => 5.5, 'constant' => 683.50 ],
						[ 'income' => 87000, 'rate' => 5.5, 'constant' => 583.50 ],
						[ 'income' => 88000, 'rate' => 5.5, 'constant' => 483.50 ],
						[ 'income' => 89000, 'rate' => 5.5, 'constant' => 383.50 ],
						[ 'income' => 90000, 'rate' => 5.5, 'constant' => 283.50 ],
						[ 'income' => 90600, 'rate' => 5.5, 'constant' => 213.50 ],
				],
		],
		20210101 => [
				0 => [
						[ 'income' => 4699, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 9199, 'rate' => 2.0, 'constant' => 93.98 ],
						[ 'income' => 13899, 'rate' => 3.0, 'constant' => 185.97 ],
						[ 'income' => 22899, 'rate' => 3.4, 'constant' => 241.57 ],
						[ 'income' => 38499, 'rate' => 5.0, 'constant' => 427.71 ],
						[ 'income' => 82000, 'rate' => 5.9, 'constant' => 774.20 ],
						[ 'income' => 83000, 'rate' => 5.9, 'constant' => 681.70 ],
						[ 'income' => 84000, 'rate' => 5.9, 'constant' => 581.70 ],
						[ 'income' => 85300, 'rate' => 5.9, 'constant' => 481.70 ],
						[ 'income' => 86400, 'rate' => 5.9, 'constant' => 381.70 ],
						[ 'income' => 87500, 'rate' => 5.9, 'constant' => 281.70 ],
						[ 'income' => 87500, 'rate' => 5.9, 'constant' => 241.70 ],
				],
		],
		20200301 => [
				0 => [
						[ 'income' => 4599, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 9099, 'rate' => 2.0, 'constant' => 91.98 ],
						[ 'income' => 13699, 'rate' => 3.0, 'constant' => 182.97 ],
						[ 'income' => 22599, 'rate' => 3.4, 'constant' => 237.77 ],
						[ 'income' => 37899, 'rate' => 5.0, 'constant' => 421.46 ],
						[ 'income' => 80800, 'rate' => 5.9, 'constant' => 762.55 ],
						[ 'income' => 81800, 'rate' => 6.6, 'constant' => 1243.40 ],
						[ 'income' => 82800, 'rate' => 6.6, 'constant' => 1143.40 ],
						[ 'income' => 84100, 'rate' => 6.6, 'constant' => 1043.40 ],
						[ 'income' => 85200, 'rate' => 6.6, 'constant' => 943.40 ],
						[ 'income' => 86200, 'rate' => 6.6, 'constant' => 843.40 ],
						[ 'income' => 86200, 'rate' => 6.6, 'constant' => 803.40 ],
				],
		],
		20150101 => [
				0 => [
						[ 'income' => 4300, 'rate' => 0.9, 'constant' => 0 ],
						[ 'income' => 8400, 'rate' => 2.4, 'constant' => 38.70 ],
						[ 'income' => 12600, 'rate' => 3.4, 'constant' => 137.10 ],
						[ 'income' => 21000, 'rate' => 4.4, 'constant' => 279.90 ],
						[ 'income' => 35100, 'rate' => 5.90, 'constant' => 649.50 ],
						[ 'income' => 35100, 'rate' => 6.90, 'constant' => 1481.40 ],
				],
		],
		20060101 => [
				0 => [
						[ 'income' => 3000, 'rate' => 1.0, 'constant' => 0 ],
						[ 'income' => 6000, 'rate' => 2.5, 'constant' => 30 ],
						[ 'income' => 9000, 'rate' => 3.5, 'constant' => 105 ],
						[ 'income' => 15000, 'rate' => 4.5, 'constant' => 210 ],
						[ 'income' => 25000, 'rate' => 6.0, 'constant' => 480 ],
						[ 'income' => 25000, 'rate' => 7.0, 'constant' => 1080 ],
				],
		],
	];

	var $state_options = [
			20240101 => [ //01-Jan-24
						  'standard_deduction' => 2340,
						  'allowance'          => 29,
			],
			20221001 => [ //01-Oct-22
						  'standard_deduction' => 2270,
						  'allowance'          => 29,
			],
			//20220101 - No Change
			20210101 => [
						  'standard_deduction' => 2200,
						  'allowance'          => 29,
			],
			20150101 => [
						  'standard_deduction' => 2200,
						  'allowance'          => 26,
			],
			20060101 => [
						  'standard_deduction' => 2000,
						  'allowance'          => 20,
			],
	];

	var $state_ui_options = [
			20230101 => [ 'wage_base' => 7000, 'new_employer_rate' => 3.1 ],
			20220101 => [ 'wage_base' => 10000, 'new_employer_rate' => 3.1 ],
			20210101 => [ 'wage_base' => 10000, 'new_employer_rate' => 3.1 ],
			20200101 => [ 'wage_base' => 7000, 'new_employer_rate' => 3.1 ],
			20190101 => [ 'wage_base' => 10000, 'new_employer_rate' => 3.1 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deduction = $this->getStateStandardDeduction();

		$income = TTMath::sub( $annual_income, $standard_deduction );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$retval = $retarr['standard_deduction'];

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
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
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			//Switch to using actual government formula with minus rather than addition so we don't have to calculate the brackets manually.
			if ( $this->getDate() >= 20200301 ) {
				$retval = TTMath::sub( TTMath::mul( $annual_income, $rate ), $state_constant );
			} else {
				$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $prev_income ), $rate ), $state_constant );
			}
		}

		Debug::text( 'State Annual Tax Payable before allowance: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = TTMath::sub( $retval, $this->getStateAllowanceAmount() );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
