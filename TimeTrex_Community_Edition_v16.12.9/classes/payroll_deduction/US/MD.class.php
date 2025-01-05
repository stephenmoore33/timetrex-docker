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
class PayrollDeduction_US_MD extends PayrollDeduction_US {
	/*
								10 => TTi18n::gettext('Single'),
								20 => TTi18n::gettext('Married (Filing Jointly)'),
								30 => TTi18n::gettext('Married (Filing Separately)'),
								40 => TTi18n::gettext('Head of Household'),
	*/

	var $state_income_tax_rate_options = [
			20130101 => [
					10 => [
							[ 'income' => 100000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 125000, 'rate' => 5.00, 'constant' => 4750 ],
							[ 'income' => 150000, 'rate' => 5.25, 'constant' => 6000 ],
							[ 'income' => 250000, 'rate' => 5.50, 'constant' => 7312.50 ],
							[ 'income' => 250000, 'rate' => 5.75, 'constant' => 12812.50 ],
					],
					20 => [
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 175000, 'rate' => 5.00, 'constant' => 7125 ],
							[ 'income' => 225000, 'rate' => 5.25, 'constant' => 8375 ],
							[ 'income' => 300000, 'rate' => 5.50, 'constant' => 11000 ],
							[ 'income' => 300000, 'rate' => 5.75, 'constant' => 15125 ],
					],
					30 => [
							[ 'income' => 100000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 125000, 'rate' => 5.00, 'constant' => 4750 ],
							[ 'income' => 150000, 'rate' => 5.25, 'constant' => 6000 ],
							[ 'income' => 250000, 'rate' => 5.50, 'constant' => 7312.50 ],
							[ 'income' => 250000, 'rate' => 5.75, 'constant' => 12812.50 ],
					],
					40 => [
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 175000, 'rate' => 5.00, 'constant' => 7125 ],
							[ 'income' => 225000, 'rate' => 5.25, 'constant' => 8375 ],
							[ 'income' => 300000, 'rate' => 5.50, 'constant' => 11000 ],
							[ 'income' => 300000, 'rate' => 5.75, 'constant' => 15125 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 300000, 'rate' => 5, 'constant' => 7125 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 14625 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 25125 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52625 ],
					],
					20 => [
							[ 'income' => 200000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 350000, 'rate' => 5, 'constant' => 9500 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 19500 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 30000 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 57500 ],
					],
					30 => [
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 300000, 'rate' => 5, 'constant' => 7125 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 14625 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 25125 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52625 ],
					],
					40 => [
							[ 'income' => 200000, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 350000, 'rate' => 5, 'constant' => 9500 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 19500 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 30000 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 57500 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2000, 'rate' => 3, 'constant' => 20 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 50 ],
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 90 ],
							[ 'income' => 300000, 'rate' => 5, 'constant' => 7072.50 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 14572.50 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 25072.50 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52572.50 ],
					],
					20 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2000, 'rate' => 3, 'constant' => 20 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 50 ],
							[ 'income' => 200000, 'rate' => 4.75, 'constant' => 90 ],
							[ 'income' => 350000, 'rate' => 5, 'constant' => 9447.50 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 16947.50 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 24822.50 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52322.50 ],
					],
					30 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2000, 'rate' => 3, 'constant' => 20 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 50 ],
							[ 'income' => 150000, 'rate' => 4.75, 'constant' => 90 ],
							[ 'income' => 300000, 'rate' => 5, 'constant' => 7072.50 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 14572.50 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 25072.50 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52572.50 ],
					],
					40 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2000, 'rate' => 3, 'constant' => 20 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 50 ],
							[ 'income' => 200000, 'rate' => 4.75, 'constant' => 90 ],
							[ 'income' => 350000, 'rate' => 5, 'constant' => 9447.50 ],
							[ 'income' => 500000, 'rate' => 5.25, 'constant' => 16947.50 ],
							[ 'income' => 1000000, 'rate' => 5.5, 'constant' => 24822.50 ],
							[ 'income' => 1000000, 'rate' => 6.25, 'constant' => 52322.50 ],
					],
			],
	];

	//
	//I don't think will ever be 100% accurate, because the tax brackets completely change for each county, based on the county percent.
	//We will need to have the county tax rate passed into this class so the proper calculations can be made.
	//
	var $state_options = [
			20240101 => [
					'standard_deduction' => [
							'minimum' => 1800,
							'maximum' => 2700,
							'rate'    => 0.15, //percent
					],
					'allowance'          => 3200,
			],
			20230101 => [
					'standard_deduction' => [
							'minimum' => 1600,
							'maximum' => 2400,
							'rate'    => 0.15, //percent
					],
					'allowance'          => 3200,
			],
			20200101 => [
						  'standard_deduction' => [
								  'minimum' => 1550,
								  'maximum' => 2300,
								  'rate'    => 0.15, //percent
						  ],
						  'allowance'          => 3200,
			],
			20180601 => [ //01-Jun-2018
						  'standard_deduction' => [
								  'minimum' => 1500,
								  'maximum' => 2250,
								  'rate'    => 0.15, //percent
						  ],
						  'allowance'          => 3200,
			],

			//01-Jan-13: No Changes
			//01-Jan-12: No Changes
			//01-Jan-11: No Changes
			//01-Jan-10: No Changes
			//01-Jan-09: No Changes
			20080101 => [ //2008
						  'standard_deduction' => [
								  'minimum' => 1500,
								  'maximum' => 2000,
								  'rate'    => 0.15, //percent
						  ],
						  'allowance'          => 3200,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 8500, 'new_employer_rate' => 2.6 ],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deduction = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		Debug::text( 'Standard Deduction: ' . $standard_deduction, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'State Allowance: ' . $state_allowance, __FILE__, __LINE__, __METHOD__, 10 );

		$income = TTMath::sub( TTMath::sub( $annual_income, $standard_deduction ), $state_allowance );

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

	function adjustData( $rate_adjustment, $type = 'state' ) {
		if ( isset( $this->getData()->income_tax_rates[$type] ) ) {
			$rates = $this->getData()->income_tax_rates[$type];

			$cumulative_constant = 0;
			$prev_constant = 0;

			//Calculate proper rate/constant based on new rates.
			foreach( $rates as $bracket ) {
				$new_bracket = $bracket;
				$new_bracket['prev_rate'] = ( ( $new_bracket['prev_rate'] > 0 ) ? TTMath::add( $new_bracket['prev_rate'], $rate_adjustment ) : $new_bracket['prev_rate'] );
				$new_bracket['rate'] = TTMath::add( $new_bracket['rate'], $rate_adjustment );
				$new_bracket['constant'] = $cumulative_constant;
				$new_bracket['prev_constant'] = $prev_constant;
				$county_rates[] = $new_bracket;

				$adjusted_constant = TTMath::mul( TTMath::sub( $new_bracket['income'], $new_bracket['prev_income'] ), $new_bracket['rate'] );

				$cumulative_constant = TTMath::add( $cumulative_constant, $adjusted_constant ); //Must come after the above new constant calculation as its "delayed" by one bracket always.
				$prev_constant = $bracket['constant'];

				Debug::text( '  County: Rate: ' . $rate_adjustment . ' County Constant: ' . $new_bracket['constant'] .' Previous Constant: '. $bracket['constant'], __FILE__, __LINE__, __METHOD__, 10 );
			}

			$this->getData()->income_tax_rates['state'] = $county_rates;
		}

		return true;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		$county_rate = TTMath::div( (float)$this->getUserValue3(), 100 );
		if ( !is_numeric( $county_rate ) || $county_rate < 0 ) {
			$county_rate = 0;
		}
		Debug::text( 'County Rate: ' . $county_rate, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $annual_income > 0 ) {
			//Modify rate/constant based on county rate, since it affects each tax bracket.
			$data = $this->getData();
			$data->adjustData( $county_rate ); //Adjust the tax formula by the county rate.

			$rate = $data->getStateRate( $annual_income );
			$state_constant = $data->getStateConstant( $annual_income );
			$state_rate_income = $data->getStateRatePreviousIncome( $annual_income );

			Debug::text( 'Rate: ' . $rate . ' Constant: ' . $state_constant . ' Rate Income: ' . $state_rate_income, __FILE__, __LINE__, __METHOD__, 10 );
			$retval = TTMath::add( TTMath::mul( TTMath::sub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$deduction_arr = $retarr['standard_deduction'];

		$retval = TTMath::mul( $this->getAnnualTaxableIncome(), $deduction_arr['rate'] );

		if ( $retval < $deduction_arr['minimum'] ) {
			$retval = $deduction_arr['minimum'];
		}

		if ( $retval > $deduction_arr['maximum'] ) {
			$retval = $deduction_arr['maximum'];
		}

		Debug::text( 'State Standard Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
