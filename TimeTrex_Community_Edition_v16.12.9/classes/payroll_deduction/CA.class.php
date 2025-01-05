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
class PayrollDeduction_CA extends PayrollDeduction_CA_Data {
	function setFederalTotalClaimAmount( $value ) {
		//TC
		$this->data['federal_total_claim_amount'] = $value;

		return true;
	}

	function getFederalTotalClaimAmount() {
		//Check to make sure the claim amount is at the minimum,
		//as long as it is NOT 0. (outside country)

		//Check claim amount from the previous year, so if the current year setting matches
		//that exactly, we know to use the current year value instead.
		//This helps when the claim amount decreases.
		//Also check next years amount in case the amount gets increased then they try to calculate pay stubs in the previous year.
		if ( $this->getForceExactClaimAmount() == false && $this->data['federal_total_claim_amount'] > 0 ) {
			$previous_year = $this->getISODate( ( TTDate::getBeginYearEpoch( $this->getDateEpoch() ) - 86400 ) );
			$next_year = $this->getISODate( ( TTDate::getEndYearEpoch( $this->getDateEpoch() ) + 86400 ) );

			if ( $this->getBasicFederalClaimCodeAmount() > 0
					&& (
							$this->data['federal_total_claim_amount'] < $this->getBasicFederalClaimCodeAmount()
							||
							$this->data['federal_total_claim_amount'] == $this->getBasicFederalClaimCodeAmount( $previous_year )
							||
							$this->data['federal_total_claim_amount'] == $this->getBasicFederalClaimCodeAmount( $next_year )
					)
			) {
				Debug::text( 'Using Basic Federal Claim Code Amount: ' . $this->getBasicFederalClaimCodeAmount() . ' (Previous Amount: ' . $this->data['federal_total_claim_amount'] . ') Date: ' . $this->getDate(), __FILE__, __LINE__, __METHOD__, 10 );

				//Return BPAF which is calculated based on income.
				//  However CompanyDeductionFactory->updateCompanyDeductionForTaxYear() doesn't know annual income when it updates claim amounts each year, so in that case just the basic amount is used.
				$retval = $this->getBasicPersonalAmount( 'CA' ); //Federal;
				//return $this->getBasicFederalClaimCodeAmount();

				//**NOTE: PDOC will accept any value you enter for Claim Amount. It does not change it to another value like we do here.

				Debug::text( 'TC: ' . $retval . ' TD1 Claim Amount: '. $this->data['federal_total_claim_amount'], __FILE__, __LINE__, __METHOD__, 10 );
				return $retval;
			}
		}

		Debug::text( 'TC: ' . $this->data['federal_total_claim_amount'] . ' TD1 Claim Amount: '. $this->data['federal_total_claim_amount'], __FILE__, __LINE__, __METHOD__, 10 );
		return $this->data['federal_total_claim_amount'];
	}

	function setProvincialTotalClaimAmount( $value ) {
		//TCP
		$this->data['provincial_total_claim_amount'] = $value;

		return true;
	}

	function getProvincialTotalClaimAmount() {
		//Check to make sure the claim amount is at the minimum,
		//as long as it is NOT 0. (outside country)

		//Check claim amount from the previous year, so if the current year setting matches
		//that exactly, we know to use the current year value instead.
		//This helps when the claim amount decreases.
		//Also check next years amount in case the amount gets increased then they try to calculate pay stubs in the previous year.
		if ( $this->getForceExactClaimAmount() == false && $this->data['provincial_total_claim_amount'] > 0 ) {
			$previous_year = $this->getISODate( ( TTDate::getBeginYearEpoch( $this->getDateEpoch() ) - 86400 ) );
			$next_year = $this->getISODate( ( TTDate::getEndYearEpoch( $this->getDateEpoch() ) + 86400 ) );

			if ( $this->getBasicProvinceClaimCodeAmount() > 0
					&& (
							$this->data['provincial_total_claim_amount'] < $this->getBasicProvinceClaimCodeAmount()
							||
							$this->data['provincial_total_claim_amount'] == $this->getBasicProvinceClaimCodeAmount( $previous_year )
							||
							$this->data['provincial_total_claim_amount'] == $this->getBasicProvinceClaimCodeAmount( $next_year )
					)
			) {
				Debug::text( 'Using Basic Provincial Claim Code Amount: ' . $this->getBasicProvinceClaimCodeAmount() . ' (Previous Amount: ' . $this->data['provincial_total_claim_amount'] . ') Date: ' . $this->getDate(), __FILE__, __LINE__, __METHOD__, 10 );

				//Return BPAP which is calculated based on income.
				//  However CompanyDeductionFactory->updateCompanyDeductionForTaxYear() doesn't know annual income when it updates claim amounts each year, so in that case just the basic amount is used.
				$retval = $this->getBasicPersonalAmount( $this->getProvince() );
				//return $this->getBasicProvinceClaimCodeAmount();

				Debug::text( 'TC: ' . $retval . ' TD1 Province Claim Amount: '. $this->data['provincial_total_claim_amount'], __FILE__, __LINE__, __METHOD__, 10 );
				return $retval;
			}
		}

		Debug::text( 'TCP: ' . $this->data['provincial_total_claim_amount'] . ' TD1 Province Claim Amount: '. $this->data['provincial_total_claim_amount'], __FILE__, __LINE__, __METHOD__, 10 );
		return $this->data['provincial_total_claim_amount'];
	}

	function setFederalAdditionalDeduction( $value ) {
		if ( $value >= 0 ) {
			$this->data['additional_deduction'] = $value;

			return true;
		}

		return false;
	}

	function getFederalAdditionalDeduction() {
		if ( isset( $this->data['additional_deduction'] ) ) {
			return $this->data['additional_deduction'];
		}

		return false;
	}

	function setUnionDuesAmount( $value ) {
		$this->data['union_dues_amount'] = $value;

		return true;
	}

	function getUnionDuesAmount() {
		if ( isset( $this->data['union_dues_amount'] ) ) {
			return $this->data['union_dues_amount'];
		}

		return 0;
	}

	function setCPPExempt( $value ) {
		$this->data['cpp_exempt'] = $value;

		return true;
	}

	function getCPPExempt() {
		if ( isset( $this->data['cpp_exempt'] ) ) {
			return $this->data['cpp_exempt'];
		}

		return false;
	}

	function setYearToDateCPPContribution( $value ) {
		if ( $value >= 0 ) {
			$this->data['cpp_year_to_date_contribution'] = $value;

			return true;
		}

		return false;
	}

	function getYearToDateCPPContribution() {
		if ( isset( $this->data['cpp_year_to_date_contribution'] ) ) {
			return $this->data['cpp_year_to_date_contribution'];
		}

		return 0;
	}

	function setYearToDateCPP2Contribution( $value ) {
		if ( $value >= 0 ) {
			$this->data['cpp2_year_to_date_contribution'] = $value;

			return true;
		}

		return false;
	}

	function getYearToDateCPP2Contribution() {
		if ( isset( $this->data['cpp2_year_to_date_contribution'] ) ) {
			return $this->data['cpp2_year_to_date_contribution'];
		}

		return 0;
	}

	function setEIExempt( $value ) {
		$this->data['ei_exempt'] = $value;

		return true;
	}

	function getEIExempt() {
		//Default to true
		if ( isset( $this->data['ei_exempt'] ) ) {
			return $this->data['ei_exempt'];
		}

		return false;
	}

	function setYearToDateEIContribution( $value ) {
		if ( $value >= 0 ) {
			$this->data['ei_year_to_date_contribution'] = $value;

			return true;
		}

		return false;
	}

	function getYearToDateEIContribution() {
		if ( isset( $this->data['ei_year_to_date_contribution'] ) ) {
			return $this->data['ei_year_to_date_contribution'];
		}

		return 0;
	}

	function setFederalTaxExempt( $value ) {
		$this->data['federal_tax_exempt'] = $value;

		return true;
	}

	function getFederalTaxExempt() {
		//Default to true
		if ( isset( $this->data['federal_tax_exempt'] ) ) {
			return $this->data['federal_tax_exempt'];
		}

		return false;
	}

	function setProvincialTaxExempt( $value ) {
		$this->data['provincial_tax_exempt'] = $value;

		return true;
	}

	function getProvincialTaxExempt() {
		//Default to true
		if ( isset( $this->data['provincial_tax_exempt'] ) ) {
			return $this->data['provincial_tax_exempt'];
		}

		return false;
	}

	function setForceExactClaimAmount( $value ) {
		$this->data['force_exact_claim_amount'] = $value;

		return true;
	}

	function getForceExactClaimAmount() {
		//Default to false
		if ( isset( $this->data['force_exact_claim_amount'] ) ) {
			return $this->data['force_exact_claim_amount'];
		}

		return false;
	}

	function setEnableCPPAndEIDeduction( $value ) {
		$this->data['enable_cpp_and_ei_deduction'] = $value;

		return true;
	}

	function getEnableCPPAndEIDeduction() {
		//Default to true
		if ( isset( $this->data['enable_cpp_and_ei_deduction'] ) ) {
			return $this->data['enable_cpp_and_ei_deduction'];
		}

		return false;
	}


	function getPayPeriodTaxDeductions() {
		/*
			T = [(T1 + T2) / P] + L
		*/

		$T1 = $this->getFederalTaxPayable();
		$T2 = $this->getProvincialTaxPayable();
		$P = $this->getAnnualPayPeriods();
		$L = $this->getFederalAdditionalDeduction();

		//$T = (($T1 + $T2) / $P) + $L;
		$T = TTMath::add( TTMath::div( TTMath::add( $T1, $T2 ), $P ), $L );

		Debug::text( 'T: ' . $T, __FILE__, __LINE__, __METHOD__, 10 );

		return $T;
	}

	function getFederalPayPeriodDeductions() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' Annual Tax Payable: ' . $this->getFederalTaxPayable() . ' YTD Paid: ' . $this->getYearToDateDeduction() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicDeduction( $this->getFederalTaxPayable(), $this->getYearToDateDeduction() );

			//Ensure that the tax amount doesn't exceed the highest possible tax rate plus 25% for "catch-up" purposes.
			$highest_taxable_amount = TTMath::mul( $this->getGrossPayPeriodIncome(), TTMath::mul( $this->getFederalHighestRate(), 1.25 ) );
			if ( $highest_taxable_amount > 0 && $retval > $highest_taxable_amount ) {
				$retval = $highest_taxable_amount;
				Debug::text( 'Federal tax amount exceeds highest tax bracket rate, capping amount at: ' . $highest_taxable_amount, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			$retval = TTMath::div( $this->getFederalTaxPayable(), $this->getAnnualPayPeriods() );
		}

		return $retval;
	}

	function getProvincialPayPeriodDeductions() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' Annual Tax Payable: ' . $this->getProvincialTaxPayable() . ' YTD Paid: ' . $this->getYearToDateDeduction() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicDeduction( $this->getProvincialTaxPayable(), $this->getYearToDateDeduction() );

			//Ensure that the tax amount doesn't exceed the highest possible tax rate plus 25% for "catch-up" purposes.
			$highest_taxable_amount = TTMath::mul( $this->getGrossPayPeriodIncome(), TTMath::mul( $this->getProvincialHighestRate(), 1.25 ) );
			if ( $highest_taxable_amount > 0 && $retval > $highest_taxable_amount ) {
				$retval = $highest_taxable_amount;
				Debug::text( 'Provincial tax amount exceeds highest tax bracket rate, capping amount at: ' . $highest_taxable_amount, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			$retval = TTMath::div( $this->getProvincialTaxPayable(), $this->getAnnualPayPeriods() );
		}

		return $retval;
	}

	function getAnnualTaxableIncome( $type = null) {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' YTD Gross: ' . $this->getYearToDateGrossIncome() . ' This Gross: ' . $this->getGrossPayPeriodIncome() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$A = $this->calcNonPeriodicIncome( $this->getYearToDateGrossIncome(), $this->getGrossPayPeriodIncome() );
		} else {
			/*
			A = [P * (I - F - F2 - F5A - U1)] - HD - F1
				if the result is negative T = L

				//Take into account non-periodic payments such as one-time bonuses/vacation payout.
				//Must include bonus amount for pay period, as well as YTD bonus amount.
			*/

			$P = $this->getAnnualPayPeriods();
			$I = $this->getGrossPayPeriodIncome();

			if ( $type == 'BPA' ) {
				$A = TTMath::mul( $P, $I );
				Debug::text( 'P: ' . $P . ' I: ' . $I .' BPA Formula...', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				$F = 0;
				$F2 = 0;
				$F5A = $this->getCPPDeductionsForAdditionalContributionsForPeriodicIncome();
				$U1 = $this->getUnionDuesAmount();
				$HD = 0;
				$F1 = 0;

				$A = TTMath::sub( TTMath::sub( TTMath::mul( $P, TTMath::sub( TTMath::sub( TTMath::sub( TTMath::sub( $I, $F ), $F2 ), $F5A ), $U1 ) ), $HD ), $F1 );
				Debug::text( 'P: ' . $P . ' I: ' . $I . ' F5A: ' . $F5A . ' U1: ' . $U1 . ' A: ' . $A, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $A;
	}

	function getBasicPersonalAmount( $province = 'CA' ) { //Default to 'CA' for Federal.
		/*
		Where NI* ≤ $150,473, BPA = $13,229
		Where $150,473 < NI* ≤ $214,368, BPA** = $12,298 + [($13,229-$12,298) - ($13,229-$12,298) × Lesser of (1, (NI* – $150,473)/($214,368-$150,473))***]

		* Variable NI represents Net Income = A + HD

		** If the BPA has three or more digits after the decimal point, increase the second digit after the decimal point by one if
		the third digit is five or more, and drop the third digit. If the third digit after the decimal point is less than five, drop the third
		digit

		*** Note that there is no rounding on this division
		*/

		$BPA = 0;

		$basic_claim_code_data = $this->getBasicClaimCodeData( $this->getDate() ); // float OR after 01-Jan-2020: array( 'CA' => array( 'min' => 12298, 'max' => 13229, 'phase_out_start' => 150473, 'phase_out_end' => 214368 ) )
		if ( isset( $basic_claim_code_data[$province] ) ) {
			$basic_personal_amount_data = $basic_claim_code_data[$province];
			if ( is_array( $basic_personal_amount_data ) ) {
				$NI = $this->getAnnualTaxableIncome( 'BPA' ); //Net income for the year or ( A + HD )

				if ( $NI <= $basic_personal_amount_data['phase_out_start'] ) {
					$BPA = $basic_personal_amount_data['max'];
				} else if ( $NI >= $basic_personal_amount_data['phase_out_end'] ) {
					$BPA = $basic_personal_amount_data['min'];
				} else {
					$BPA = round( TTMath::sub( $basic_personal_amount_data['max'], TTMath::mul( TTMath::sub( $NI, $basic_personal_amount_data['phase_out_start'] ), TTMath::div( TTMath::sub( $basic_personal_amount_data['max'], $basic_personal_amount_data['min'] ), TTMath::sub( $basic_personal_amount_data['phase_out_end'], $basic_personal_amount_data['phase_out_start'] ) ) ) ), 2 );
					Debug::text( ' BPA('. $province .'): ' . $BPA . ' Min: ' . $basic_personal_amount_data['min'] . ' Max: ' . $basic_personal_amount_data['max'] . ' Phase Out: Start: ' . $basic_personal_amount_data['phase_out_start'] . ' End: ' . $basic_personal_amount_data['phase_out_end'] .' NI: '. $NI, __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else if ( is_numeric( $basic_personal_amount_data ) ) {
				$BPA = $basic_personal_amount_data; //Federal Basic Personal Amount
			}
		}
		unset( $basic_claim_code_data );

		Debug::text( ' BPA('. $province .'): ' . $BPA, __FILE__, __LINE__, __METHOD__, 10 );

		return $BPA;
	}

	function getFederalBasicTax() {
		/*
		T3 = (R * A) - K - K1 - K2 - K3 - K4
			if the result is negative, $0;

        R = Federal tax rate applicable to annual taxable income
		*/

//		$T3 = 0;
		$A = $this->getAnnualTaxableIncome();
		$R = $this->getData()->getFederalRate( $A );
		$K = $this->getData()->getFederalConstant( $A );
		$TC = $this->getFederalTotalClaimAmount();
		$K1 = TTMath::mul( $this->getData()->getFederalLowestRate(), $TC );
		if ( $this->getEnableCPPAndEIDeduction() == true ) {
			$K2 = $this->getFederalCPPAndEITaxCredit();
		} else {
			$K2 = 0; //Do the deduction at the Company Tax Deduction level instead.
		}

		$K3 = 0;

		if ( $this->getDate() >= 20060701 ) {
			$K4 = $this->getFederalEmploymentCredit();
		} else {
			$K4 = 0;
		}

		Debug::text( 'A: ' . $A . ' R: ' . $R . ' K: ' . $K . ' TC: ' . $TC . ' K1: ' . $K1 . ' K2: ' . $K2 . ' K3: ' . $K3 . ' K4: ' . $K4, __FILE__, __LINE__, __METHOD__, 10 );

		//$T3 = ($R * $A) - $K - $K1 - $K2 - $K3 - $K4;
		$T3 = TTMath::sub( TTMath::sub( TTMath::sub( TTMath::sub( TTMath::sub( TTMath::mul( $R, $A ), $K ), $K1 ), $K2 ), $K3 ), $K4 );

		if ( $T3 < 0 ) {
			$T3 = 0;
		}

		Debug::text( 'T3: ' . $T3, __FILE__, __LINE__, __METHOD__, 10 );

		return $T3;
	}

	function getFederalTaxPayable() {
		//If employee is federal tax exempt, return 0 dollars.
		if ( $this->getFederalTaxExempt() == true ) {
			Debug::text( 'Federal Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		}

		/*
		T1= (T3 - LCF)*
			* If the result is negative, substitute $0

        LCF = The lesser of:
			i) $750 and
            ii) 15% of amount deducted for the year of accusistion.
		*/

		$T3 = $this->getFederalBasicTax();
		$LCF = 0; //Ignore 15% for now.

		//$T1 = ($T3 - $LCF);
		$T1 = TTMath::sub( $T3, $LCF );

		if ( $T1 < 0 ) {
			$T1 = 0;
		}

		Debug::text( 'T1: ' . $T1, __FILE__, __LINE__, __METHOD__, 10 );

		return $T1;
	}

	function getFederalEmploymentCredit() {
		/*
		  K4 = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		//In 2020 they changed this formula somewhat, however based on the information we collect it seems to match the legacy formula exactly.
		$tmp1_K4 = TTMath::mul( $this->getData()->getFederalLowestRate(), $this->getAnnualTaxableIncome() );
		$tmp2_K4 = TTMath::mul( $this->getData()->getFederalLowestRate(), $this->getData()->getFederalEmploymentCreditAmount() );

		if ( $tmp2_K4 < $tmp1_K4 ) {
			$K4 = $tmp2_K4;
		} else {
			$K4 = $tmp1_K4;
		}

		Debug::text( 'K4: ' . $K4, __FILE__, __LINE__, __METHOD__, 10 );

		return $K4;
	}

	function getProvincialEmploymentCredit() {
		/*
		  K4P = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		//Yukon only currently.
		$K4P = 0;
		Debug::text( 'K4P: ' . $K4P, __FILE__, __LINE__, __METHOD__, 10 );

		return $K4P;
	}

	function getCPPTaxCredit( $type ) {
		if ( $type == 'provincial' ) {
			$rate = $this->getData()->getProvincialLowestRate();
		} else {
			$rate = $this->getData()->getFederalLowestRate();
		}

		/*
		  Previous formula: K2_CPP = [(0.16 * (P * C, max $1801.80))
		  New Formula:      K2_CPP = [(0.15 × (P × C × (0.0495/0.0595), maximum $3,123.45 × (PM/12) )  )]*
		*/
		$C = $this->getEmployeeCPP();
		$P = $this->getAnnualPayPeriods();

		if ( $this->getFormulaType() == 20 ) {
			//$PR = $this->getRemainingPayPeriods();
			//$P_times_C = TTMath::add( $this->getYearToDateCPPContribution(), TTMath::mul( $PR, $C ) );
			//Debug::text( 'PR: ' . $PR . ' C: ' . $C . ' YTD CPP: ' . $this->getYearToDateCPPContribution(), __FILE__, __LINE__, __METHOD__, 10 );

			//The official formula (above) doesn't estimate the CPP over the remaining pay periods very well, especially when small bonuses are given as the tax is calculated higher than it should be.
			// Therefore use annualizing factor with the YTD CPP and current pay stub CPP to estimate it better, just like we do with annual income.
			Debug::text( ' C: ' . $C . ' YTD CPP: ' . $this->getYearToDateCPPContribution(), __FILE__, __LINE__, __METHOD__, 10 );
			$P_times_C = TTMath::mul( TTMath::add( $this->getYearToDateCPPContribution(), $C ), $this->getAnnualizingFactor() );
		} else {
			$P_times_C = TTMath::mul( $P, $C );
		}

		$P_times_C = TTMath::mul( $P_times_C, TTMath::div( $this->getCPPBaseEmployeeRate(), $this->getCPPEmployeeRate() ) );

		if ( $P_times_C > $this->getCPPBaseEmployeeMaximumContribution() ) {
			Debug::text( 'P_times_C: ' . $P_times_C . ' Maximum Contribution: '. $this->getCPPBaseEmployeeMaximumContribution(), __FILE__, __LINE__, __METHOD__, 10 );
			$P_times_C = $this->getCPPBaseEmployeeMaximumContribution();
		}
		Debug::text( 'P_times_C: ' . $P_times_C . ' C: ' . $C . ' P: ' . $P, __FILE__, __LINE__, __METHOD__, 10 );

		if ( TTMath::add( $this->getYearToDateCPPContribution(), $this->getEmployeeCPPForPayPeriod() ) >= $this->getCPPBaseEmployeeMaximumContribution() ) {
			Debug::text( 'P_times_C in or after PP where maximum base contribution is reached: ' . ( $this->getYearToDateCPPContribution() + $this->getEmployeeCPPForPayPeriod() ), __FILE__, __LINE__, __METHOD__, 10 );
			$P_times_C = $this->getCPPBaseEmployeeMaximumContribution();
		}

		$K2_CPP = TTMath::mul( $rate, $P_times_C );
		Debug::text( 'K2_CPP: ' . $K2_CPP .' Type: '. $type .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10 );

		return $K2_CPP;
	}

	function getEITaxCredit( $type ) {
		if ( $type == 'provincial' ) {
			$rate = $this->getData()->getProvincialLowestRate();
		} else {
			$rate = $this->getData()->getFederalLowestRate();
		}

		/*
		  K2_EI = [(0.16 * (P * C, max $1002.45))
		*/
		$C = $this->getEmployeeEI();
		$P = $this->getAnnualPayPeriods();

		if ( $this->getFormulaType() == 20 ) {
			//$PR = $this->getRemainingPayPeriods();
			//$P_times_C = TTMath::add( $this->getYearToDateEIContribution(), TTMath::mul( $PR, $C ) );
			//Debug::text( 'PR: ' . $PR . ' C: ' . $C . ' YTD EI: ' . $this->getYearToDateEIContribution(), __FILE__, __LINE__, __METHOD__, 10 );

			//The official formula (above) doesn't estimate the EI over the remaining pay periods very well, especially when small bonuses are given as the tax is calculated higher than it should be.
			// Therefore use annualizing factor with the YTD EI and current pay stub CPP to estimate it better, just like we do with annual income.
			Debug::text( ' C: ' . $C . ' YTD EI: ' . $this->getYearToDateEIContribution(), __FILE__, __LINE__, __METHOD__, 10 );
			$P_times_C = TTMath::mul( TTMath::add( $this->getYearToDateEIContribution(), $C ), $this->getAnnualizingFactor() );
		} else {
			$P_times_C = TTMath::mul( $P, $C );
		}

		if ( $P_times_C > $this->getEIEmployeeMaximumContribution() ) {
			$P_times_C = $this->getEIEmployeeMaximumContribution();
		}
		Debug::text( 'P_times_C: ' . $P_times_C, __FILE__, __LINE__, __METHOD__, 10 );

		if ( TTMath::add( $this->getYearToDateEIContribution(), $this->getEmployeeEIForPayPeriod() ) >= $this->getEIEmployeeMaximumContribution() ) {
			Debug::text( 'P_times_C in or after PP where maximum contribution is reached: ' . ( $this->getYearToDateEIContribution() + $this->getEmployeeEIForPayPeriod() ), __FILE__, __LINE__, __METHOD__, 10 );
			$P_times_C = $this->getEIEmployeeMaximumContribution();
		}

		$K2_EI = TTMath::mul( $rate, $P_times_C );
		Debug::text( 'K2_EI: ' . $K2_EI, __FILE__, __LINE__, __METHOD__, 10 );

		return $K2_EI;
	}

	function getFederalCPPAndEITaxCredit() {
		$K2 = TTMath::add( $this->getCPPTaxCredit( 'federal' ), $this->getEITaxCredit( 'federal' ) );
		Debug::text( 'K2: ' . $K2, __FILE__, __LINE__, __METHOD__, 10 );

		return $K2;
	}

	function getProvincialTaxPayable() {
		//If employee is provincial tax exempt, return 0 dollars.
		if ( $this->getProvincialTaxExempt() == true ) {
			Debug::text( 'Provincial Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		}

		/*
		T2 = T4 + V1 + V2 - S - LCP
			if the result is negative, T2 = 0
		*/

		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$V2 = $this->getAdditionalProvincialSurtax();
		$S = $this->getProvincialTaxReduction();
		$LCP = 0;

		//$T2 = $T4 + $V1 + $V2 - $S - $LCP;
		$T2 = TTMath::sub( TTMath::sub( TTMath::add( TTMath::add( $T4, $V1 ), $V2 ), $S ), $LCP );

		if ( $T2 < 0 ) {
			$T2 = 0;
		}

//		Debug::text( 'T2: ' . $T2, __FILE__, __LINE__, __METHOD__, 10 );
//		Debug::text( 'T4: ' . $T4, __FILE__, __LINE__, __METHOD__, 10 );
//		Debug::text( 'V1: ' . $V1, __FILE__, __LINE__, __METHOD__, 10 );
//		Debug::text( 'V2: ' . $V2, __FILE__, __LINE__, __METHOD__, 10 );
//		Debug::text( 'S: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'T2: ' . $T2 . ' T4: ' . $T4 . ' V1: ' . $V1 . ' V2: ' . $V2 . ' S: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		return $T2;
	}

	function getProvincialBasicTax() {
		/*
		  	T4 = (V * A) - KP - K1P - K2P - K3P
		*/

		$A = $this->getAnnualTaxableIncome();
		$V = $this->getData()->getProvincialRate( $A );
		$KP = $this->getData()->getProvincialConstant( $A );
		$TCP = $this->getProvincialTotalClaimAmount();
		$K1P = TTMath::mul( $this->getData()->getProvincialLowestRate(), $TCP );
		if ( $this->getEnableCPPAndEIDeduction() == true ) {
			$K2P = $this->getProvincialCPPAndEITaxCredit();
		} else {
			$K2P = 0; //Use the Company Deduction Exclude funtionality instead.
		}
		$K3P = 0;
		$K4P = $this->getProvincialEmploymentCredit();

		Debug::text( 'A: ' . $A . ' V: ' . $V . ' KP: ' . $KP . ' TCP: ' . $TCP . ' K1P: ' . $K1P . ' K2P: ' . $K2P . ' K3P: ' . $K3P . ' K4P: ' . $K3P, __FILE__, __LINE__, __METHOD__, 10 );

		//$T4 = ($V * $A) - $KP - $K1P - $K2P - $K3P;
		$T4 = TTMath::sub( TTMath::sub( TTMath::sub( TTMath::sub( TTMath::sub( TTMath::mul( $V, $A ), $KP ), $K1P ), $K2P ), $K3P ), $K4P );

		if ( $T4 < 0 ) {
			$T4 = 0;
		}

		Debug::text( 'T4: ' . $T4, __FILE__, __LINE__, __METHOD__, 10 );

		return $T4;
	}

	//Should be overridden by provincial specific function.
	function getProvincialTaxReduction() {
		return 0; //$S
	}

	//Should be overridden by provincial specific function.
	function getProvincialSurtax() {
		return 0; // $V1
	}

	//Should be overridden by provincial specific function.
	function getAdditionalProvincialSurtax() {
		return 0; // $V2
	}

	function getProvincialCPPAndEITaxCredit() {
		$K2P = TTMath::add( $this->getCPPTaxCredit( 'provincial' ), $this->getEITaxCredit( 'provincial' ) );
		Debug::text( 'K2P: ' . $K2P, __FILE__, __LINE__, __METHOD__, 10 );

		return $K2P;
	}

	//The total number of months during which CPP and/or QPP contributions are required to be deducted (used in the proration of maximum contribution)
	function getCPPRequiredDeductionMonths() {
		$PM = 12;

		return $PM;
	}

	//Since CPP/EI amounts do not take into account deductions like RRSP/Child support and such and because we calculate them separately from Federal/Provincial income tax
	// we need to be able to pass their amounts in from a previous calculation rather than recalculating them based on different information.
	function setEmployeeCPPForPayPeriod( $value ) {
		$this->data['employee_cpp_for_pp'] = $value;

		return true;
	}

	//Since CPP2 amounts do not take into account deductions like RRSP/Child support and such and because we calculate them separately from Federal/Provincial income tax
	// we need to be able to pass their amounts in from a previous calculation rather than recalculating them based on different information.
	function setEmployeeCPP2ForPayPeriod( $value ) {
		$this->data['employee_cpp2_for_pp'] = $value;

		return true;
	}


	function getEmployeeCPPForPayPeriod() {
		/*
		 * This is part the formula to calculate "C ="
		 	(ii) 0.0595* × [PI – ($3,500 / P)]
				If the result is negative, C = $0.
		*/

		//If employee is CPP exempt, return 0 dollars.
		if ( $this->getCPPExempt() == true ) {
			return 0;
		}

		if ( isset( $this->data['employee_cpp_for_pp'] ) && $this->data['employee_cpp_for_pp'] != null ) {
			Debug::text( 'Using manually passed in Employee CPP for PP: ' . $this->data['employee_cpp_for_pp'], __FILE__, __LINE__, __METHOD__, 10 );
			$CII = $this->data['employee_cpp_for_pp'];
		} else {
			$P = $this->getAnnualPayPeriods();
			$I = $this->getGrossPayPeriodIncome();
			$exemption = TTMath::div( $this->getCPPBasicExemption(), $P );

			//We used to just check if its payroll_run_id > 1 and remove the exemption in that case, but that fails when the first in-cycle run is ID=4 or something.
			//  So switch this to just checking the formula type, and only remove the exemption if its a out-of-cycle run.
			//     That won't handle the case of the last pay stub being a out-of-cycle run and no in-cycle run is done for that employee though, but not sure we can do much about that.
			//  NOTE: If they always use the out-of-cycle formula, then their tax deductions will always be off by the CPP exemption amount.
			if ( $this->getFormulaType() == 20 ) {
				Debug::text( 'Out-of-Cycle formula, ignoring CPP exemption...', __FILE__, __LINE__, __METHOD__, 10 );
				$exemption = 0;
			}

			Debug::text( 'P: ' . $P . ' I: ' . $I .' Exemption: '. $exemption .' (Basic Exemption: '. $this->getCPPBasicExemption() .') Rate: '. $this->getCPPEmployeeRate(), __FILE__, __LINE__, __METHOD__, 10 );

			$CII = TTMath::mul( $this->getCPPEmployeeRate(), TTMath::sub( $I, $exemption ) );
		}

		if ( $CII > $this->getCPPEmployeeMaximumContribution() ) {
			$CII = $this->getCPPEmployeeMaximumContribution();
		}

		Debug::text( 'C.II: ' . $CII, __FILE__, __LINE__, __METHOD__, 10 );

		return $CII;
	}

	function getCPPDeductionsForAdditionalContributionsForPayPeriod() {
		//F5 = Deductions for Canada Pension Plan additional contributions for the pay period
		/*
			F5 =
				C × (0.0100/0.0595) + C2
				Only for employees in Quebec:
				F5Q = C × (0.0100/0.0640)
			Note:
			When C = 0, F5 = 0.
		 */

		$C = $this->getEmployeeCPP();
		$C2 = $this->getEmployeeCPP2();
		$F5 = TTMath::add( TTMath::mul( $C, TTMath::div( TTMath::sub( $this->getCPPEmployeeRate(), $this->getCPPBaseEmployeeRate() ), $this->getCPPEmployeeRate() ) ), $C2 );

		Debug::text( 'F5: ' . $F5, __FILE__, __LINE__, __METHOD__, 10 );
		return $F5;
	}

	function getCPPDeductionsForAdditionalContributionsForPeriodicIncome() {
		//F5A = Deductions for Canada (or Quebec) Pension Plan additional contributions for the pay period deducted from the **periodic income**
		/*
			F5* × ((PI – B)/PI)
			* When in the province of Quebec, replace F5 with F5Q.
		 */

		$F5A = 0;

		$F5 = $this->getCPPDeductionsForAdditionalContributionsForPayPeriod();
		$I = $this->getGrossPayPeriodIncome();
		$B = 0;

		if ( $I > 0 ) {
			$F5A = TTMath::mul( $F5, TTMath::div( TTMath::sub( $I, $B ), $I ) );
		}

		Debug::text( 'F5A: ' . $F5A, __FILE__, __LINE__, __METHOD__, 10 );
		return $F5A;
	}

	function getCPPDeductionsForAdditionalContributionsForNonPeriodicIncome() {
		//F5B = Deductions for Canada (or Quebec) Pension Plan additional contributions for the pay period deducted from the **non-periodic payment**
		/*
		 	F5* × (B/PI)
			When in the province of Quebec, replace F5 with F5Q.*
		 */

		$F5B = 0;

		$F5 = $this->getCPPDeductionsForAdditionalContributionsForPayPeriod();
		$I = $this->getGrossPayPeriodIncome();
		$B = 0;

		if ( $I > 0 ) {
			$F5B = TTMath::mul( $F5, TTMath::div( $B, $I ) );
		}
		Debug::text( 'F5B: ' . $F5A, __FILE__, __LINE__, __METHOD__, 10 );

		return $F5B;
	}

	function getEmployeeCPP() {
		/*
		 C = The lesser of:
				(i) $3,754.45* × (PM/12) – D*;
				(ii) 0.0595* × [PI – ($3,500 / P)]
				If the result is negative, C = $0
		*/

		//If employee is CPP exempt, return 0 dollars.
		if ( $this->getCPPExempt() == true ) {
			return 0;
		}

		$D = $this->getYearToDateCPPContribution();

		$CI = TTMath::sub( $this->getCPPEmployeeMaximumContribution(), $D );
		$CII = $this->getEmployeeCPPForPayPeriod();

		if ( $CI < $CII ) {
			$C = $CI;
		} else {
			$C = $CII;
		}

		if ( $C < 0 ) {
			$C = 0;
		}

		Debug::text( 'D: ' . $D . ' C.I: ' . $CI . ' C.II: ' . $CII . ' C: ' . $C, __FILE__, __LINE__, __METHOD__, 10 );

		return $C;
	}

	function getEmployerCPP() {
		//EmployerCPP is the same as EmployeeCPP
		return $this->getEmployeeCPP();
	}

	function getEmployeeCPP2() {
		/*
		 C2 = The lesser of:
			(i) $188.00 × (PM/12) – D2*;
 			(ii) (PIYTD + PI – W) × 0.04
 			If the result is negative, C2 = $0.
			* For employees employed in Quebec, use D2Q instead of D2.

		W = The greater of:
			(i) PIYTD;
			(ii) YMPE × (PM/12)
		*/

		if ( $this->getDate() < 20240101 ) {
			return 0;
		}

		//If employee is CPP exempt, return 0 dollars.
		if ( $this->getCPPExempt() == true ) {
			return 0;
		}

		$D2 = $this->getYearToDateCPP2Contribution();
		$C2I = TTMath::sub( TTMath::mul( $this->getCPP2EmployeeMaximumContribution(), TTMath::div( $this->getCPPRequiredDeductionMonths(), 12 ) ), $D2 );

		$PI = $this->getGrossPayPeriodIncome(); //PI or I, interchangeable for this case as include pay stub accounts are what is considered pensionable.
		$PI_YTD = $this->getYearToDateGrossIncome(); //PI or I, interchangeable for this case as include pay stub accounts are what is considered pensionable.

		$YMPE = $this->getCPPMaximumEarnings();
		$W = max( $PI_YTD, TTMath::mul( $YMPE, TTMath::div( $this->getCPPRequiredDeductionMonths(), 12 ) ) );

		$C2II = max( 0, TTMath::mul( TTMath::sub( TTMath::add( $PI_YTD, $PI ), $W ), $this->getCPP2EmployeeRate() ) ); //If result is negative, use 0.

		$C2 = min( $C2I, $C2II );
		if ( $C2 < 0 ) {
			$C2 = 0;
		}

		Debug::text( 'PI: ' . $PI . ' PI_YTD: ' . $PI_YTD . ' YMPE: ' . $YMPE . ' W: ' . $W, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'D2: ' . $D2 . ' C2.I: ' . $C2I . ' W: '. $W .' C2.II: ' . $C2II . ' C2: ' . $C2, __FILE__, __LINE__, __METHOD__, 10 );

		return $C2;
	}

	function getEmployerCPP2() {
		//EmployerCPP2 is the same as EmployeeCPP2
		return $this->getEmployeeCPP2();
	}


	//Since CPP/EI amounts do not take into account deductions like RRSP/Child support and such and because we calculate them separately from Federal/Provincial income tax
	// we need to be able to pass their amounts in from a previous calculation rather than recalculating them based on different information.
	function setEmployeeEIForPayPeriod( $value ) {
		$this->data['employee_ei_for_pp'] = $value;

		return true;
	}

	function getEmployeeEIForPayPeriod() {
		/*
                ii) 0.021 * I, maximum of 819
					round the resulting amount in ii) to the nearest $0.01
		*/
		//If employee is EI exempt, return 0 dollars.
		if ( $this->getEIExempt() == true ) {
			return 0;
		}

		$I = $this->getGrossPayPeriodIncome();

		Debug::text( 'Employee EI Rate: ' . $this->getEIEmployeeRate() . ' I: ' . $I, __FILE__, __LINE__, __METHOD__, 10 );
		$tmp2_EI = TTMath::mul( $this->getEIEmployeeRate(), $I );
		if ( isset( $this->data['employee_ei_for_pp'] ) && $this->data['employee_ei_for_pp'] != null ) {
			Debug::text( 'Using manually passed in Employee EI for PP: ' . $this->data['employee_ei_for_pp'], __FILE__, __LINE__, __METHOD__, 10 );
			$tmp2_EI = $this->data['employee_ei_for_pp'];
		}

		if ( $tmp2_EI > $this->getEIEmployeeMaximumContribution() ) {
			$tmp2_EI = $this->getEIEmployeeMaximumContribution();
		}

		return $tmp2_EI;
	}

	function getEmployeeEI() {
		/*
			EI = the lesser of
				i) 819 - D; and
                ii) 0.021 * I, maximum of 819
					round the resulting amount in ii) to the nearest $0.01
		*/

		//If employee is EI exempt, return 0 dollars.
		if ( $this->getEIExempt() == true ) {
			return 0;
		}

		$D = $this->getYearToDateEIContribution();
		Debug::text( 'Employee EI Rate: ' . $this->getEIEmployeeRate() . ' D: ' . $D, __FILE__, __LINE__, __METHOD__, 10 );
		$tmp1_EI = TTMath::sub( $this->getEIEmployeeMaximumContribution(), $D );
		$tmp2_EI = $this->getEmployeeEIForPayPeriod();

		if ( $tmp1_EI < $tmp2_EI ) {
			$EI = $tmp1_EI;
		} else {
			$EI = $tmp2_EI;
		}

		if ( $EI < 0 ) {
			$EI = 0;
		}

		Debug::text( 'Employee EI: ' . $EI, __FILE__, __LINE__, __METHOD__, 10 );

		return $EI;
	}

	function getEmployerEI() {
		$EI = TTMath::mul( $this->getEmployeeEI(), $this->getEIEmployerRate() );

		Debug::text( 'Employer EI: ' . $EI . ' Rate: ' . $this->getEIEmployerRate(), __FILE__, __LINE__, __METHOD__, 10 );

		return $EI;
	}

	function getPayPeriodEmployeeTotalDeductions() {
		return TTMath::add( TTMath::add( $this->getPayPeriodTaxDeductions(), $this->getEmployeeCPP() ), $this->getEmployeeEI() );
	}

	function getPayPeriodEmployeeNetPay() {
		return TTMath::sub( $this->getGrossPayPeriodIncome(), $this->getPayPeriodEmployeeTotalDeductions() );
	}

	/*
		Use this to get all useful values.
	*/
	function getArray() {
		$array = [
				'gross_pay'                    => $this->getGrossPayPeriodIncome(),
				'federal_tax'                  => $this->getFederalPayPeriodDeductions(),
				'provincial_tax'               => $this->getProvincialPayPeriodDeductions(),
				'total_tax'                    => $this->getPayPeriodTaxDeductions(),
				'employee_cpp'                 => $this->getEmployeeCPP(),
				'employer_cpp'                 => $this->getEmployerCPP(),
				'employee_ei'                  => $this->getEmployeeEI(),
				'employer_ei'                  => $this->getEmployerEI(),
				'federal_additional_deduction' => $this->getFederalAdditionalDeduction(),
				//'net_pay' => $this->getPayPeriodNetPay()
		];

		Debug::Arr( $array, 'Deductions Array:', __FILE__, __LINE__, __METHOD__, 10 );

		return $array;
	}
}

?>