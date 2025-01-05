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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_NL extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20240101 => [
					[ 'income' => 43198, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 86395, 'rate' => 14.5, 'constant' => 2505 ],
					[ 'income' => 154244, 'rate' => 15.8, 'constant' => 3629 ],
					[ 'income' => 215943, 'rate' => 17.8, 'constant' => 6713 ],
					[ 'income' => 275870, 'rate' => 19.8, 'constant' => 11032 ],
					[ 'income' => 551739, 'rate' => 20.8, 'constant' => 13791 ],
					[ 'income' => 1103478, 'rate' => 21.3, 'constant' => 16550 ],
					[ 'income' => 1103478, 'rate' => 21.8, 'constant' => 22067 ],
			],
			20230101 => [
					[ 'income' => 41457, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 82913, 'rate' => 14.50, 'constant' => 2405 ],
					[ 'income' => 148027, 'rate' => 15.80, 'constant' => 3482 ],
					[ 'income' => 207239, 'rate' => 17.80, 'constant' => 6443 ],
					[ 'income' => 264750, 'rate' => 19.80, 'constant' => 10588 ],
					[ 'income' => 529500, 'rate' => 20.80, 'constant' => 13235 ],
					[ 'income' => 1059000, 'rate' => 21.30, 'constant' => 15883 ],
					[ 'income' => 1059000, 'rate' => 21.80, 'constant' => 21178 ],
			],
			20220101 => [
					[ 'income' => 39147, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 78294, 'rate' => 14.50, 'constant' => 2271 ],
					[ 'income' => 139780, 'rate' => 15.80, 'constant' => 3288 ],
					[ 'income' => 195693, 'rate' => 17.80, 'constant' => 6084 ],
					[ 'income' => 250000, 'rate' => 19.80, 'constant' => 9998 ],
					[ 'income' => 500000, 'rate' => 20.80, 'constant' => 12498 ],
					[ 'income' => 1000000, 'rate' => 21.30, 'constant' => 14998 ],
					[ 'income' => 1000000, 'rate' => 21.80, 'constant' => 19998 ],
			],
			20210101 => [
					[ 'income' => 38081, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 76161, 'rate' => 14.5, 'constant' => 2209 ],
					[ 'income' => 135973, 'rate' => 15.8, 'constant' => 3199 ],
					[ 'income' => 190363, 'rate' => 17.3, 'constant' => 5238 ],
					[ 'income' => 190363, 'rate' => 18.3, 'constant' => 7142 ],
			],
			20200101 => [
					[ 'income' => 37929, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 75858, 'rate' => 14.5, 'constant' => 2200 ],
					[ 'income' => 135432, 'rate' => 15.8, 'constant' => 3186 ],
					[ 'income' => 189604, 'rate' => 17.3, 'constant' => 5218 ],
					[ 'income' => 189604, 'rate' => 18.3, 'constant' => 7114 ],
			],
			20190101 => [
					[ 'income' => 37591, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 75181, 'rate' => 14.5, 'constant' => 2180 ],
					[ 'income' => 134224, 'rate' => 15.8, 'constant' => 3158 ],
					[ 'income' => 187913, 'rate' => 17.3, 'constant' => 5171 ],
					[ 'income' => 187913, 'rate' => 18.3, 'constant' => 7050 ],
			],
			20180101 => [
					[ 'income' => 36923, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 73852, 'rate' => 14.5, 'constant' => 2142 ],
					[ 'income' => 131850, 'rate' => 15.8, 'constant' => 3102 ],
					[ 'income' => 184590, 'rate' => 17.3, 'constant' => 5080 ],
					[ 'income' => 184590, 'rate' => 18.3, 'constant' => 6925 ],
			],
			20170101 => [
					[ 'income' => 35851, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 71701, 'rate' => 14.5, 'constant' => 2079 ],
					[ 'income' => 128010, 'rate' => 15.8, 'constant' => 3011 ],
					[ 'income' => 179214, 'rate' => 17.3, 'constant' => 4932 ],
					[ 'income' => 179214, 'rate' => 18.3, 'constant' => 6724 ],
			],
			20160701 => [
					[ 'income' => 35148, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 70295, 'rate' => 14.5, 'constant' => 2039 ],
					[ 'income' => 125500, 'rate' => 15.8, 'constant' => 2952 ],
					[ 'income' => 175700, 'rate' => 17.3, 'constant' => 4835 ],
					[ 'income' => 175700, 'rate' => 18.3, 'constant' => 6592 ],
			],
			20160101 => [
					[ 'income' => 35148, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 70295, 'rate' => 12.5, 'constant' => 1687 ],
					[ 'income' => 125500, 'rate' => 13.3, 'constant' => 2249 ],
					[ 'income' => 175700, 'rate' => 14.3, 'constant' => 3504 ],
					[ 'income' => 175700, 'rate' => 15.3, 'constant' => 5261 ],
			],
			20150701 => [
					[ 'income' => 35008, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 70015, 'rate' => 12.5, 'constant' => 1680 ],
					[ 'income' => 125000, 'rate' => 13.3, 'constant' => 2241 ],
					[ 'income' => 175000, 'rate' => 14.3, 'constant' => 3491 ],
					[ 'income' => 175000, 'rate' => 15.3, 'constant' => 5241 ],
			],
			20150101 => [
					[ 'income' => 35008, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 70015, 'rate' => 12.5, 'constant' => 1680 ],
					[ 'income' => 70015, 'rate' => 13.3, 'constant' => 2241 ],
			],
			20140101 => [
					[ 'income' => 34254, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 68508, 'rate' => 12.5, 'constant' => 1644 ],
					[ 'income' => 68508, 'rate' => 13.3, 'constant' => 2192 ],
			],
			20130101 => [
					[ 'income' => 33748, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 67496, 'rate' => 12.5, 'constant' => 1620 ],
					[ 'income' => 67496, 'rate' => 13.3, 'constant' => 2160 ],
			],
			20120101 => [
					[ 'income' => 32893, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 65785, 'rate' => 12.5, 'constant' => 1579 ],
					[ 'income' => 65785, 'rate' => 13.3, 'constant' => 2105 ],
			],
			20110101 => [
					[ 'income' => 31904, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 63807, 'rate' => 12.5, 'constant' => 1531 ],
					[ 'income' => 63807, 'rate' => 13.3, 'constant' => 2042 ],
			],
			20100701 => [
					[ 'income' => 31278, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 62556, 'rate' => 12.5, 'constant' => 1501 ],
					[ 'income' => 62556, 'rate' => 13.3, 'constant' => 2002 ],
			],
			20100101 => [
					[ 'income' => 31278, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 62556, 'rate' => 12.8, 'constant' => 1595 ],
					[ 'income' => 62556, 'rate' => 15.5, 'constant' => 3284 ],
			],
			20090101 => [
					[ 'income' => 31061, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 62121, 'rate' => 12.8, 'constant' => 1584 ],
					[ 'income' => 62121, 'rate' => 15.5, 'constant' => 3261 ],
			],
			20080701 => [
					[ 'income' => 30215, 'rate' => 7.7, 'constant' => 0 ],
					[ 'income' => 60429, 'rate' => 12.8, 'constant' => 1541 ],
					[ 'income' => 60429, 'rate' => 15.5, 'constant' => 3173 ],
			],
			20080101 => [
					[ 'income' => 30215, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 60429, 'rate' => 13.8, 'constant' => 1541 ],
					[ 'income' => 60429, 'rate' => 16.5, 'constant' => 3173 ],
			],
			20070701 => [
					[ 'income' => 30182, 'rate' => 8.7, 'constant' => 0 ],
					[ 'income' => 60364, 'rate' => 13.8, 'constant' => 1539 ],
					[ 'income' => 60364, 'rate' => 16.5, 'constant' => 3169 ],
			],
			20070101 => [
					[ 'income' => 29590, 'rate' => 10.57, 'constant' => 0 ],
					[ 'income' => 59180, 'rate' => 16.16, 'constant' => 1654 ],
					[ 'income' => 59180, 'rate' => 18.02, 'constant' => 2755 ],
			],
	];

	function getAdditionalProvincialSurtax() {
		/*
			V2 =

			Where A < 20,000
			V2 = 0

			Where A >
		*/

		$V2 = 0;

		if ( $this->getDate() >= 20160701 && $this->getDate() < 20200101 ) { //V2 was removed for NL as of 2020.
			$A = $this->getAnnualTaxableIncome();

			$tmp_V2_threshold = 1000;

			if ( $A < 50000 ) {
				//This should result in V2 = 0
				$tmp_A_threshold = 0;
				$tmp_V2_constant = 0;
			} else if ( $A > 50000 && $A <= 55000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 50000 );
				$tmp_V2_constant = 0;
			} else if ( $A > 55000 && $A <= 60000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 55000 );
				$tmp_V2_constant = 100;
			} else if ( $A > 60000 && $A <= 65000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 60000 );
				$tmp_V2_constant = 200;
			} else if ( $A > 65000 && $A <= 70000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 65000 );
				$tmp_V2_constant = 300;
			} else if ( $A > 70000 && $A <= 75000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 70000 );
				$tmp_V2_constant = 400;
			} else if ( $A > 75000 && $A <= 80000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 75000 );
				$tmp_V2_constant = 500;
			} else if ( $A > 80000 && $A <= 100000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 80000 );
				$tmp_V2_constant = 600;
			} else if ( $A > 100000 && $A <= 125000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 100000 );
				$tmp_V2_constant = 700;
			} else if ( $A > 125000 && $A <= 175000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 125000 );
				$tmp_V2_constant = 800;
			} else if ( $A > 175000 && $A <= 250000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 175000 );
				$tmp_V2_constant = 900;
			} else if ( $A > 250000 && $A <= 300000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 250000 );
				$tmp_V2_constant = 1000;
			} else if ( $A > 300000 && $A <= 350000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 300000 );
				$tmp_V2_constant = 1100;
			} else if ( $A > 350000 && $A <= 400000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 350000 );
				$tmp_V2_constant = 1200;
			} else if ( $A > 400000 && $A <= 450000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 400000 );
				$tmp_V2_constant = 1300;
			} else if ( $A > 450000 && $A <= 500000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 450000 );
				$tmp_V2_constant = 1400;
			} else if ( $A > 500000 && $A <= 550000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 500000 );
				$tmp_V2_constant = 1500;
			} else if ( $A > 550000 && $A <= 600000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 550000 );
				$tmp_V2_constant = 1600;
			} else if ( $A > 600000 ) {
				$tmp_A_threshold = TTMath::sub( $A, 600000 );
				$tmp_V2_constant = 1700;
			}

			if ( $tmp_A_threshold < $tmp_V2_threshold ) {
				$V2 = TTMath::mul( 0.10, $tmp_A_threshold );
			} else {
				$V2 = TTMath::mul( 0.10, $tmp_V2_threshold );
			}

			if ( $tmp_V2_constant > 0 ) {
				$V2 += $tmp_V2_constant;
			}
		}
		Debug::text( 'V2: ' . $V2, __FILE__, __LINE__, __METHOD__, 10 );

		return $V2;
	}
}

?>
