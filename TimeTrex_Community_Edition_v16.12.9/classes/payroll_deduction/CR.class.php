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
 * @package PayrollDeduction\CR
 */
class PayrollDeduction_CR extends PayrollDeduction_CR_Data {
	//
	// Federal
	//
	function setFederalFilingStatus( $value ) {
		$this->data['federal_filing_status'] = $value;

		return true;
	}

	function getFederalFilingStatus() {
		if ( isset( $this->data['federal_filing_status'] ) ) {
			return $this->data['federal_filing_status'];
		}

		return 10; //Single
	}

	function setFederalAllowance( $value ) {
		$this->data['federal_allowance'] = $value;

		return true;
	}

	function getFederalAllowance() {
		if ( isset( $this->data['federal_allowance'] ) ) {
			return $this->data['federal_allowance'];
		}

		return false;
	}

	//
	// Calculation Functions
	//
	function getAnnualTaxableIncome() {

		$retval = TTMath::mul( $this->getGrossPayPeriodIncome(), $this->getAnnualPayPeriods() );

		Debug::text( 'Annual Taxable Income: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//
	// Federal Tax
	//
	function getFederalPayPeriodDeductions() {
		return $this->convertToUserCurrency( TTMath::div( $this->getFederalTaxPayable(), $this->getAnnualPayPeriods() ) );
	}

	function getFederalTaxPayable() {

		$annual_taxable_income = $this->getAnnualTaxableIncome();
		$annual_allowance = TTMath::mul( $this->getFederalAllowanceAmount( $this->getDate() ), $this->getFederalAllowance() );

		Debug::text( 'Annual Taxable Income: ' . $annual_taxable_income, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Allowance: ' . $annual_allowance, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getFederalFilingStatus() == 20 ) {
			$annual_filing = $this->getFederalFilingAmount( $this->getData() );
		} else {
			$annual_filing = 0;
		}

		Debug::text( 'Filing: ' . $annual_filing, __FILE__, __LINE__, __METHOD__, 10 );

		$taxTable = $this->getData()->getFederalTaxTable( $annual_taxable_income );

		/*
		 *	T = Total Income Tax calculated for that employee
		 *	TT1= Tax Tier 1, ranging from CRC 0 to ~ CRC 6MM
		 *	TT2 = Tax Tier 2, ranging from ~ CRC 6MM to ~ CRC 9MM
		 *	TT3 = Tax Tier 3, above ~ CRC 9MM
		 *	AD = Total Income Tax Adjustments
		 *
		 *	T =  (TT1 + TT2 + TT3)  – AD
		*/

		$AD = $annual_allowance + $annual_filing;
		$tax = 0;
		if ( $annual_taxable_income > $AD ) {
			$tmp_prev_income = [];
			$i = 0;

			foreach ( $taxTable as $taxTier ) {
				$prev_income = $taxTier['prev_income'];
				$prev_rate = $taxTier['prev_rate'];
				$income = $taxTier['income'];
				$rate = $taxTier['rate'];

				if ( $prev_income != 0 && $prev_income > 0 ) {

					if ( $annual_taxable_income > $prev_income && $annual_taxable_income <= $income ) {
						$tax = TTMath::add( $tax, ( TTMath::mul( $rate, TTMath::sub( $annual_taxable_income, $prev_income ) ) ) );
					} else {
						$tmp_prev_income[$i] = $prev_income;
						if ( $i >= 2 && $i < 3 ) {
							if ( $annual_taxable_income > $income ) {
								$tax = TTMath::add( $tax, TTMath::mul( $prev_rate, TTMath::sub( $prev_income, $tmp_prev_income[$i - 1] ) ) );
								$tax = TTMath::add( $tax, TTMath::mul( $rate, TTMath::sub( $annual_taxable_income, $income ) ) );
							}
						}
					}
				}

				$i++;
			}

			$tax = TTMath::sub( $tax, $AD );
		} else {
			Debug::text( 'Income is less then Total Income Tax Adjustments: ', __FILE__, __LINE__, __METHOD__, 10 );

			$tax = 0;
		}

		if ( $tax < 0 ) {
			$tax = 0;
		}

		Debug::text( 'RetVal: ' . $tax, __FILE__, __LINE__, __METHOD__, 10 );

		return $tax;
	}
}

?>