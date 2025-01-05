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
class PayrollDeduction_CA_YT extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20240101 => [
					[ 'income' => 55867, 'rate' => 6.4, 'constant' => 0 ],
					[ 'income' => 111733, 'rate' => 9, 'constant' => 1453 ],
					[ 'income' => 173205, 'rate' => 10.9, 'constant' => 3575 ],
					[ 'income' => 500000, 'rate' => 12.8, 'constant' => 6866 ],
					[ 'income' => 500000, 'rate' => 15, 'constant' => 17866 ],
			],
			20230101 => [
					[ 'income' => 53359, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 106717, 'rate' => 9.00, 'constant' => 1387 ],
					[ 'income' => 165430, 'rate' => 10.90, 'constant' => 3415 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 6558 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 17558 ],
			],
			20220101 => [
					[ 'income' => 50197, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 100392, 'rate' => 9.00, 'constant' => 1305 ],
					[ 'income' => 155625, 'rate' => 10.90, 'constant' => 3213 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 6169 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 17169 ],
			],
			20210101 => [
					[ 'income' => 49020, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 98040, 'rate' => 9.00, 'constant' => 1275 ],
					[ 'income' => 151978, 'rate' => 10.90, 'constant' => 3137 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 6025 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 17025 ],
			],
			20200101 => [
					[ 'income' => 48535, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 97069, 'rate' => 9.00, 'constant' => 1262 ],
					[ 'income' => 150473, 'rate' => 10.90, 'constant' => 3106 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 5965 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 16965 ],
			],
			20190101 => [
					[ 'income' => 47630, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 95259, 'rate' => 9.00, 'constant' => 1238 ],
					[ 'income' => 147667, 'rate' => 10.90, 'constant' => 3048 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 5854 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 16854 ],
			],
			20180101 => [
					[ 'income' => 46605, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 93208, 'rate' => 9.00, 'constant' => 1212 ],
					[ 'income' => 144489, 'rate' => 10.90, 'constant' => 2983 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 5728 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 16728 ],
			],
			20170101 => [
					[ 'income' => 45916, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 91831, 'rate' => 9.00, 'constant' => 1194 ],
					[ 'income' => 142353, 'rate' => 10.90, 'constant' => 2939 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 5643 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 16643 ],
			],
			20160101 => [
					[ 'income' => 45282, 'rate' => 6.40, 'constant' => 0 ],
					[ 'income' => 90563, 'rate' => 9.00, 'constant' => 1177 ],
					[ 'income' => 140388, 'rate' => 10.90, 'constant' => 2898 ],
					[ 'income' => 500000, 'rate' => 12.80, 'constant' => 5565 ],
					[ 'income' => 500000, 'rate' => 15.00, 'constant' => 16565 ],
			],
			20150701 => [
					[ 'income' => 44701, 'rate' => 5.76, 'constant' => 0 ],
					[ 'income' => 89401, 'rate' => 8.32, 'constant' => 1144 ],
					[ 'income' => 138586, 'rate' => 10.36, 'constant' => 2968 ],
					[ 'income' => 500000, 'rate' => 12.84, 'constant' => 6405 ],
					[ 'income' => 500000, 'rate' => 17.24, 'constant' => 28405 ],
			],
			20150101 => [
					[ 'income' => 44701, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 89401, 'rate' => 9.68, 'constant' => 1180 ],
					[ 'income' => 138586, 'rate' => 11.44, 'constant' => 2754 ],
					[ 'income' => 138586, 'rate' => 12.76, 'constant' => 4583 ],
			],
			20140101 => [
					[ 'income' => 43953, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 87907, 'rate' => 9.68, 'constant' => 1160 ],
					[ 'income' => 136270, 'rate' => 11.44, 'constant' => 2708 ],
					[ 'income' => 136270, 'rate' => 12.76, 'constant' => 4506 ],
			],
			20130101 => [
					[ 'income' => 43561, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 87123, 'rate' => 9.68, 'constant' => 1150 ],
					[ 'income' => 135054, 'rate' => 11.44, 'constant' => 2683 ],
					[ 'income' => 135054, 'rate' => 12.76, 'constant' => 4466 ],
			],
			20120101 => [
					[ 'income' => 42707, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 85414, 'rate' => 9.68, 'constant' => 1127 ],
					[ 'income' => 132406, 'rate' => 11.44, 'constant' => 2631 ],
					[ 'income' => 132406, 'rate' => 12.76, 'constant' => 4379 ],
			],
			20110101 => [
					[ 'income' => 41544, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 83088, 'rate' => 9.68, 'constant' => 1097 ],
					[ 'income' => 128800, 'rate' => 11.44, 'constant' => 2559 ],
					[ 'income' => 128800, 'rate' => 12.76, 'constant' => 4259 ],
			],
			20100101 => [
					[ 'income' => 40970, 'rate' => 7.04, 'constant' => 0 ],
					[ 'income' => 81941, 'rate' => 9.68, 'constant' => 1082 ],
					[ 'income' => 127021, 'rate' => 11.44, 'constant' => 2524 ],
					[ 'income' => 127021, 'rate' => 12.76, 'constant' => 4200 ],
			],
	];

	function getProvincialSurtax() {
		/*
			V1 =
			For YU
				Where T4 <= 6000
				V1 = 0

				Where T4 > 6000
				V1 = 0.10 * ( T4 - 6000 )
		*/

		$T4 = $this->getProvincialBasicTax();
		$V1 = 0;

		//Repealed 01-Jul-2015 retroactively to 01-Jan-2015.
		if ( $this->getDate() >= 20080101 && $this->getDate() < 20150701 ) {
			if ( $T4 <= 6000 ) {
				$V1 = 0;
			} else if ( $T4 > 6000 ) {
				$V1 = TTMath::mul( 0.05, TTMath::sub( $T4, 6000 ) );
			}
		}

		Debug::text( 'V1: ' . $V1, __FILE__, __LINE__, __METHOD__, 10 );

		return $V1;
	}

	function getProvincialEmploymentCredit() {
		/*
		  K4P = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		$K4P = 0;
		if ( $this->getProvince() == 'YT' && $this->getDate() >= 20130101 ) { //Yukon only currently.
			$tmp1_K4P = TTMath::mul( $this->getData()->getProvincialLowestRate(), $this->getAnnualTaxableIncome() );
			$tmp2_K4P = TTMath::mul( $this->getData()->getProvincialLowestRate(), $this->getData()->getFederalEmploymentCreditAmount() ); //This matches the federal employment credit amount currently.

			if ( $tmp2_K4P < $tmp1_K4P ) {
				$K4P = $tmp2_K4P;
			} else {
				$K4P = $tmp1_K4P;
			}
		}

		Debug::text( 'K4P: ' . $K4P, __FILE__, __LINE__, __METHOD__, 10 );

		return $K4P;
	}
}

?>
