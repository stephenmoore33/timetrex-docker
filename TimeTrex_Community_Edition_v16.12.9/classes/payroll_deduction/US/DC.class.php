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
class PayrollDeduction_US_DC extends PayrollDeduction_US {
	/*
															10 => TTi18n::gettext('Single'),
															20 => TTi18n::gettext('Married (Filing Jointly)'),
															30 => TTi18n::gettext('Married (Filing Separately)'),
															40 => TTi18n::gettext('Head of Household'),
	*/

	var $state_income_tax_rate_options = [
			20221001 => [
					10 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 250000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 500000, 'rate' => 9.25, 'constant' => 19650 ],
							[ 'income' => 1000000, 'rate' => 9.75, 'constant' => 42775 ],
							[ 'income' => 1000000, 'rate' => 10.75, 'constant' => 91525 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 250000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 500000, 'rate' => 9.25, 'constant' => 19650 ],
							[ 'income' => 1000000, 'rate' => 9.75, 'constant' => 42775 ],
							[ 'income' => 1000000, 'rate' => 10.75, 'constant' => 91525 ],
					],
					30 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 250000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 500000, 'rate' => 9.25, 'constant' => 19650 ],
							[ 'income' => 1000000, 'rate' => 9.75, 'constant' => 42775 ],
							[ 'income' => 1000000, 'rate' => 10.75, 'constant' => 91525 ],
					],
					40 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 250000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 500000, 'rate' => 9.25, 'constant' => 19650 ],
							[ 'income' => 1000000, 'rate' => 9.75, 'constant' => 42775 ],
							[ 'income' => 1000000, 'rate' => 10.75, 'constant' => 91525 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 1000000, 'rate' => 8.75, 'constant' => 28150 ],
							[ 'income' => 1000000, 'rate' => 8.95, 'constant' => 85025 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 1000000, 'rate' => 8.75, 'constant' => 28150 ],
							[ 'income' => 1000000, 'rate' => 8.95, 'constant' => 85025 ],
					],
					30 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 1000000, 'rate' => 8.75, 'constant' => 28150 ],
							[ 'income' => 1000000, 'rate' => 8.95, 'constant' => 85025 ],
					],
					40 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 6.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3500 ],
							[ 'income' => 1000000, 'rate' => 8.75, 'constant' => 28150 ],
							[ 'income' => 1000000, 'rate' => 8.95, 'constant' => 85025 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 7.0, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3600 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28250 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 7.0, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3600 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28250 ],
					],
					30 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 7.0, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3600 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28250 ],
					],
					40 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 60000, 'rate' => 7.0, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 3600 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28250 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28550 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28550 ],
					],
					30 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28550 ],
					],
					40 => [
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 400 ],
							[ 'income' => 350000, 'rate' => 8.5, 'constant' => 2200 ],
							[ 'income' => 350000, 'rate' => 8.95, 'constant' => 28550 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 240 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2040 ],
					],
					20 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 240 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2040 ],
					],
					30 => [
							[ 'income' => 2000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 320 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2120 ],
					],
					40 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 240 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2040 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 4200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 232 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2032 ],
					],
					20 => [
							[ 'income' => 4200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 232 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2032 ],
					],
					30 => [
							[ 'income' => 2100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 316 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2116 ],
					],
					40 => [
							[ 'income' => 4200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 6.0, 'constant' => 232 ],
							[ 'income' => 40000, 'rate' => 8.5, 'constant' => 2032 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 7.0, 'constant' => 337.50 ],
							[ 'income' => 40000, 'rate' => 8.7, 'constant' => 2437.50 ],
					],
					20 => [
							[ 'income' => 2500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 7.0, 'constant' => 337.50 ],
							[ 'income' => 40000, 'rate' => 8.7, 'constant' => 2437.50 ],
					],
					30 => [
							[ 'income' => 1250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 7.0, 'constant' => 393.75 ],
							[ 'income' => 40000, 'rate' => 8.7, 'constant' => 2493.75 ],
					],
					40 => [
							[ 'income' => 2500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 4.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 7.0, 'constant' => 337.50 ],
							[ 'income' => 40000, 'rate' => 8.7, 'constant' => 2437.50 ],
					],
			],
	];

	var $state_options = [
			20221001 => [
					'allowance' => 0, //Suspended as per: https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/release_content/attachments/Important_Information_Regarding_the_District_of_Columbia_Withholding_for_Tax_Year_2022_updated_10_17_2022.pdf
			],
			20180101 => [
					'allowance' => 4150,
			],
			20150101 => [
					'allowance' => 1775,
			],
			//01-Jan-2014 - No Changes.
			//01-Jan-2013 - No Changes.
			//01-Jan-2012 - No Changes.
			//01-Jan-2011 - No Changes.
			20100101 => [
					'allowance' => 1675,
			],
			20090101 => [
					'allowance' => 1750,
			],
			20060101 => [
					'allowance' => 1500,
			],
	];

	var $state_ui_options = [
			20060101 => [ 'wage_base' => 9000, 'new_employer_rate' => 2.7 ],
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
