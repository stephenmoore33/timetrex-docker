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
class PayrollDeduction_CA_ON extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20240101 => [
					[ 'income' => 51446, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 102894, 'rate' => 9.15, 'constant' => 2109 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 4177 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5677 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7877 ],
			],
			20230101 => [
					[ 'income' => 49231, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 98463, 'rate' => 9.15, 'constant' => 2018 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3998 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5498 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7698 ],
			],
			20220101 => [
					[ 'income' => 46226, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 92454, 'rate' => 9.15, 'constant' => 1895 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3754 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5254 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7454 ],
			],
			20210101 => [
					[ 'income' => 45142, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 90287, 'rate' => 9.15, 'constant' => 1851 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3666 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5166 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7366 ],
			],
			20200101 => [
					[ 'income' => 44740, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 89482, 'rate' => 9.15, 'constant' => 1834 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3633 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5133 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7333 ],
			],
			20190101 => [
					[ 'income' => 43906, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 87813, 'rate' => 9.15, 'constant' => 1800 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3565 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 5065 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7265 ],
			],
			20180101 => [
					[ 'income' => 42960, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 85923, 'rate' => 9.15, 'constant' => 1761 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3488 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 4988 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7188 ],
			],
			20170101 => [
					[ 'income' => 42201, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 84404, 'rate' => 9.15, 'constant' => 1730 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3427 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 4927 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7127 ],
			],
			20160101 => [
					[ 'income' => 41536, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 83075, 'rate' => 9.15, 'constant' => 1703 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3373 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 4873 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7073 ],
			],
			20150101 => [
					[ 'income' => 40922, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 81847, 'rate' => 9.15, 'constant' => 1678 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3323 ],
					[ 'income' => 220000, 'rate' => 12.16, 'constant' => 4823 ],
					[ 'income' => 220000, 'rate' => 13.16, 'constant' => 7023 ],
			],
			20140901 => [
					[ 'income' => 40120, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 80242, 'rate' => 9.15, 'constant' => 1645 ],
					[ 'income' => 150000, 'rate' => 11.16, 'constant' => 3258 ],
					[ 'income' => 220000, 'rate' => 14.16, 'constant' => 7758 ],
					[ 'income' => 514090, 'rate' => 13.16, 'constant' => -6206 ],
					[ 'income' => 514090, 'rate' => 17.16, 'constant' => 14358 ],
			],
			20140101 => [
					[ 'income' => 40120, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 80242, 'rate' => 9.15, 'constant' => 1645 ],
					[ 'income' => 514090, 'rate' => 11.16, 'constant' => 3258 ],
					[ 'income' => 514090, 'rate' => 13.16, 'constant' => 13540 ],
			],
			20130101 => [
					[ 'income' => 39723, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 79448, 'rate' => 9.15, 'constant' => 1629 ],
					[ 'income' => 509000, 'rate' => 11.16, 'constant' => 3226 ],
					[ 'income' => 509000, 'rate' => 13.16, 'constant' => 13406 ],
			],
			20120101 => [
					[ 'income' => 39020, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 78043, 'rate' => 9.15, 'constant' => 1600 ],
					[ 'income' => 78043, 'rate' => 11.16, 'constant' => 3168 ],
			],
			20110101 => [
					[ 'income' => 37774, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 75550, 'rate' => 9.15, 'constant' => 1549 ],
					[ 'income' => 75550, 'rate' => 11.16, 'constant' => 3067 ],
			],
			20100101 => [
					[ 'income' => 37106, 'rate' => 5.05, 'constant' => 0 ],
					[ 'income' => 74214, 'rate' => 9.15, 'constant' => 1521 ],
					[ 'income' => 74214, 'rate' => 11.16, 'constant' => 3013 ],
			],
			20090101 => [
					[ 'income' => 36848, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 73698, 'rate' => 9.15, 'constant' => 1142 ],
					[ 'income' => 73698, 'rate' => 11.16, 'constant' => 2624 ],
			],
			20080101 => [
					[ 'income' => 36020, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 72041, 'rate' => 9.15, 'constant' => 1117 ],
					[ 'income' => 72041, 'rate' => 11.16, 'constant' => 2565 ],
			],
			20070101 => [
					[ 'income' => 35488, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 70976, 'rate' => 9.15, 'constant' => 1100 ],
					[ 'income' => 70976, 'rate' => 11.16, 'constant' => 2527 ],
			],
			20060101 => [
					[ 'income' => 34758, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 69517, 'rate' => 9.15, 'constant' => 1077 ],
					[ 'income' => 69517, 'rate' => 11.16, 'constant' => 2475 ],
			],
	];

	/*
		Provincial surtax - V1
	*/
	var $provincial_surtax_options = [
			20240101 => [
					'income1' => 5554,
					'income2' => 7108,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20230101 => [
					'income1' => 5315,
					'income2' => 6802,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20220101 => [
					'income1' => 4991,
					'income2' => 6387,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20210101 => [
					'income1' => 4874,
					'income2' => 6237,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20200101 => [
					'income1' => 4830,
					'income2' => 6182,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20190101 => [
					'income1' => 4740,
					'income2' => 6067,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20180101 => [
					'income1' => 4638,
					'income2' => 5936,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20170101 => [
					'income1' => 4556,
					'income2' => 5831,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20160101 => [
					'income1' => 4484,
					'income2' => 5739,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20150101 => [
					'income1' => 4418,
					'income2' => 5654,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20140101 => [
					'income1' => 4331,
					'income2' => 5543,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20130101 => [
					'income1' => 4289,
					'income2' => 5489,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20120101 => [
					'income1' => 4213,
					'income2' => 5392,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20110101 => [
					'income1' => 4078,
					'income2' => 5219,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20100101 => [
					'income1' => 4006,
					'income2' => 5127,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20090101 => [
					'income1' => 4257,
					'income2' => 5370,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20080101 => [
					'income1' => 4162,
					'income2' => 5249,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20070101 => [
					'income1' => 4100,
					'income2' => 5172,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
			20060101 => [
					'income1' => 4016,
					'income2' => 5065,
					'rate1'   => 0.20,
					'rate2'   => 0.36,
			],
	];

	/*
		Provincial tax reduction - S2
	*/
	var $provincial_tax_reduction_options = [
			20240101 => [
					'amount' => 286,
			],
			20230101 => [
					'amount' => 274,
			],
			20220101 => [
					'amount' => 257,
			],
			20210101 => [
					'amount' => 251,
			],
			20200101 => [
					'amount' => 249,
			],
			20190101 => [
					'amount' => 244,
			],
			20180101 => [
					'amount' => 239,
			],
			20170101 => [
					'amount' => 235,
			],
			20160101 => [
					'amount' => 231,
			],
			20150101 => [
					'amount' => 228,
			],
			20140101 => [
					'amount' => 223,
			],
			20130101 => [
					'amount' => 221,
			],
			20120101 => [
					'amount' => 217,
			],
			20110101 => [
					'amount' => 210,
			],
			20100101 => [
					'amount' => 206,
			],
			20090101 => [
					'amount' => 205,
			],
			20080101 => [
					'amount' => 201,
			],
			20070101 => [
					'amount' => 198,
			],
			20060101 => [
					'amount' => 194,
			],
	];

	function getProvincialTaxReduction() {
//		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$Y = 0;
		$S = 0;

		Debug::text( 'ON Specific - Province: ' . $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10 );
		$tax_reduction_data = $this->getProvincialTaxReductionData( $this->getDate() );
		if ( is_array( $tax_reduction_data ) ) {
			$tmp_Sa = TTMath::add( $T4, $V1 );
			$tmp_Sb = TTMath::sub( TTMath::mul( 2, TTMath::add( $tax_reduction_data['amount'], $Y ) ), TTMath::add( $T4, $V1 ) );

			if ( $tmp_Sa < $tmp_Sb ) {
				$S = $tmp_Sa;
			} else {
				$S = $tmp_Sb;
			}
		}
		Debug::text( 'aS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text( 'bS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		return $S;
	}

	function getProvincialSurtax() {
		/*
			V1 =
			For Ontario
				Where T4 <= 4016
				V1 = 0

				Where T4 > 4016 <= 5065
				V1 = 0.20 * ( T4 - 4016 )

				Where T4 > 5065
				V1 = 0.20 * (T4 - 4016) + 0.36 * (T4 - 5065)

		*/

		$V1 = 0;
		$T4 = $this->getProvincialBasicTax();

		$surtax_data = $this->getProvincialSurTaxData( $this->getDate() );
		if ( is_array( $surtax_data ) ) {
			if ( $T4 < $surtax_data['income1'] ) {
				$V1 = 0;
			} else if ( $T4 > $surtax_data['income1'] && $T4 <= $surtax_data['income2'] ) {
				$V1 = TTMath::mul( $surtax_data['rate1'], TTMath::sub( $T4, $surtax_data['income1'] ) );
			} else if ( $T4 > $surtax_data['income2'] ) {
				$V1 = TTMath::add( TTMath::mul( $surtax_data['rate1'], TTMath::sub( $T4, $surtax_data['income1'] ) ), TTMath::mul( $surtax_data['rate2'], TTMath::sub( $T4, $surtax_data['income2'] ) ) );
			}
		}

		Debug::text( 'V1: ' . $V1, __FILE__, __LINE__, __METHOD__, 10 );

		return $V1;
	}

	function getAdditionalProvincialSurtax() {
		/*
			V2 =

			Where A < 20,000
			V2 = 0

			Where A >

		*/

		$A = $this->getAnnualTaxableIncome();
		$V2 = 0;

		if ( $this->getDate() >= 20060101 ) {
			if ( $A < 20000 ) {
				$V2 = 0;
			} else if ( $A > 20000 && $A <= 36000 ) {
				$tmp_V2 = TTMath::mul( 0.06, TTMath::sub( $A, 20000 ) );

				if ( $tmp_V2 > 300 ) {
					$V2 = 300;
				} else {
					$V2 = $tmp_V2;
				}
			} else if ( $A > 36000 && $A <= 48000 ) {
				$tmp_V2 = TTMath::add( 300, TTMath::mul( 0.06, TTMath::sub( $A, 36000 ) ) );

				if ( $tmp_V2 > 450 ) {
					$V2 = 450;
				} else {
					$V2 = $tmp_V2;
				}
			} else if ( $A > 48000 && $A <= 72000 ) {
				$tmp_V2 = TTMath::add( 450, TTMath::mul( 0.25, TTMath::sub( $A, 48000 ) ) );

				if ( $tmp_V2 > 600 ) {
					$V2 = 600;
				} else {
					$V2 = $tmp_V2;
				}
			} else if ( $A > 72000 && $A <= 200000 ) {
				$tmp_V2 = TTMath::add( 600, TTMath::mul( 0.25, TTMath::sub( $A, 72000 ) ) );

				if ( $tmp_V2 > 750 ) {
					$V2 = 750;
				} else {
					$V2 = $tmp_V2;
				}
			} else if ( $A > 200000 ) {
				$tmp_V2 = TTMath::add( 750, TTMath::mul( 0.25, TTMath::sub( $A, 200000 ) ) );

				if ( $tmp_V2 > 900 ) {
					$V2 = 900;
				} else {
					$V2 = $tmp_V2;
				}
			}
		}

		Debug::text( 'V2: ' . $V2, __FILE__, __LINE__, __METHOD__, 10 );

		return $V2;
	}
}

?>
