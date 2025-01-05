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


include_once( 'US.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_PERS extends GovernmentForms_US {
	private $payroll_deduction_obj = null; //Prevent __set() from sticking this into the data property.

	function getOptions( $name ) {
		$retval = null;

		return $retval;
	}

	function getPayrollDeductionObject() {
		if ( !isset( $this->payroll_deduction_obj ) ) {
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );
			$this->payroll_deduction_obj = new PayrollDeduction( 'US', null );
			$this->payroll_deduction_obj->setDate( TTDate::getTimeStamp( $this->year, 12, 31 ) );
		}

		return $this->payroll_deduction_obj;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterMiddleName( $value ) {
		//Return just initial
		$value = substr( $value, 0, 1 );

		return $value;
	}

	function formatMSDateStamp( $epoch ) {
		if ( !empty( $epoch ) ) {
			return date( 'm', $epoch ) . date( 'd', $epoch ) . date( 'Y', $epoch );
		}

		return null;
	}

	function formatIADateStamp( $epoch ) {
		if ( !empty( $epoch ) ) {
			return date( 'Y', $epoch ) . date( 'm', $epoch ) . date( 'd', $epoch );
		}

		return null;
	}

	function _compileMS() {
		//Basic business logic for the MS PERS wage codes.
		if ( $this->taxable_wages == 0 && in_array( (int)$this->wage_code, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] ) ) {
			Debug::Text( 'Skipping... Taxable wages is 0, but wage code is: ' . $this->wage_code, __FILE__, __LINE__, __METHOD__, 10);
			return false;
		}

		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 4, 'N' );                                 //[01-04][4] Agency Number
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->reporting_unit_number ), 3, 'N' );                            //[05-07][3] Unit Number
		$line[] = $this->padRecord( '', 1, 'AN' );                                                                               //[08-08][1] Reserved
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                                              //[09-17][9] Social Security Number
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );                                 //[18-37][20] Last Name - ABCDEFGHIJKLMNOPQRSTUVWXYZ
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );                                //[38-52][15] First Name - abcdefghijklmnopqrstuvwxyz-
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->filterMiddleName( $this->middle_name ) ), 1, 'AN' );     //[53-53][1] Middle Initial - ABCDEFGHIJKLMNOPQRSTUVWXYZ
		$line[] = $this->padRecord( '', 3, 'AN' );                                                                               //[54-56][3] Generation ID or Professional ID - BLANK or DDS, EDD, JR, MD, PHD, SR, I ,II, III, IV, V, VI, VII
		$line[] = $this->padRecord( $this->wage_code, 2, 'N' );                                                                  //[57-58][2] Wage Code
		$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 8, 'N' );                                     //[59-66][8] Reporting Wage
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year ), 2, 'N' );                                    //[67-68][2] Reporting Month - MM
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                             //[69-72][4] Reporting Year - YYYY
		$line[] = $this->padRecord( $this->removeDecimal( $this->employee_tax_withheld ), 8, 'N' );                             //[73-80][8] Employee Contribution - Employee rate multiplied by the reporting wage amount.
		$line[] = $this->padRecord( $this->removeDecimal( $this->employer_tax_withheld ), 8, 'N' );                             //[81-88][8] Employer Contribution - Employer rate multiplied by the reporting wage amount.
		$line[] = $this->padRecord( '01', 2, 'N' );                                                                              //[89-90][2] Credit Flag - 00=Does not receive retirement credit for month, 01=Receives retirement credit for month, 02=Deletes retirement credit for month.
		$line[] = $this->padRecord( $this->retirement_status_code, 2, 'N' );                                                     //[91-92][2] Status Code - 00=No change in status, 01=Hire Date/Reinstatement Date, 02=Employee is on Leave wihtout Pay, 03=Employee is terminated.
		$line[] = $this->padRecord( $this->formatMSDateStamp( $this->retirement_status_date ), 8, 'N' );                         //[93-100][8] Status Date
		$line[] = $this->padRecord( $this->title_code, 5, 'AN' );                                                                //[101-105][5] Position Codes
		$line[] = $this->padRecord( '', 8, 'N' );                                                                                //[106-113][8] Salary Amount - Leave blank for now, as it seems to only apply to schools or elected officials.
		$line[] = $this->padRecord( '', 2, 'N' );                                                                                //[114-115][2] Salary Code - Leave blank for now. 01=12/12 - The Salary Code should be used for 1) personnel hired at public schools, community colleges, orinstitutions of higher learning to work only for the academic term and 2) retired local elected officials when the Salary Amount is greater than 0. This field should be blank for all other positions.
		$line[] = $this->padRecord( ( ( $this->sex_id == 10 ) ? 'M' : 'F' ), 1, 'AN' );                                          //[116-116][1] Sex Code - M or F
		$line[] = $this->padRecord( $this->formatMSDateStamp( $this->birth_date ), 8, 'N' );                                     //[117-124][8] Birth Date - MMDDYYYY
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 30, 'AN' );                                  //[125-154][30] Address Line 1 - A-z0123456789#-./&
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 30, 'AN' );                                  //[155-184][30] Address Line 2
		$line[] = $this->padRecord( '', 30, 'AN' );                                                                              //[185-214][30] Address Line 3
		$line[] = $this->padRecord( '', 30, 'AN' );                                                                              //[215-244][30] Address Line 4
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 28, 'AN' );                                      //[245-272][28] City - A-z’
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 4, 'AN' );                                      //[273-276][4] State - A-Z
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'N' );                                    //[277-281][5] Zip Code
		$line[] = $this->padRecord( '', 50, 'AN' );                                                                              //[282-331][50] Filler Field - BLANK

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 331 ) {
			Debug::Text( 'ERROR! MS Record length is incorrect, should be 331 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'MS Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileNV() {
		$is_part_time = false;
		if ( strtoupper( substr( $this->employment_status, 0, 1 ) == 'P' ) ) {
			$is_part_time = true;
		}

		$is_employee_hourly = true;
		if ( $this->user_wage_type_id != 10 ) {
			$is_employee_hourly = false;
		}

		$hourly_rate = '';
		if ( $is_part_time == true || $is_employee_hourly == true ) {
			$hourly_rate = $this->user_wage_hourly_rate;
		}

		$base_hours = '';
		$actual_hours = '';
		if ( $is_employee_hourly == true ) {
			$base_hours = 160; //TODO: Could be different for each client, but just default it to 160 for now. Otherwise it has to be mapped to an Employee setting.
			$actual_hours = $this->subject_units;
		}


		$base_wages = '';
		$actual_wages = '';
		if ( $is_employee_hourly == false && $this->user_wage_annual_wage > 0 ) {
			//$base_wages = TTMath::div( $this->user_wage_annual_wage, 12 );
			//$actual_wages = $this->subject_wages;
			$base_wages = 0; //TODO: Currently its blank for most clients.
			$actual_wages = 0; //TODO: Currently its blank for most clients.

		}

		$employee_tax_withheld = 0; //Employee pays a portion
		$employer_tax_withheld = 0; //Employer pays a portion
		$employer_all_tax_withheld = 0; //Employer pays 100% of tax, employee contributions nothing.
		if ( $this->employee_tax_withheld > 0 ) { //If the employee pays any, it can't be employer pays 100%, so breakout what the employee and employer pay.
			$employee_tax_withheld = $this->employee_tax_withheld;
			$employer_tax_withheld = $this->employer_tax_withheld;
		} else {
			$employer_all_tax_withheld = $this->employer_tax_withheld;
		}


		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 30, 'AN' );                                            //[1-30][30] Last Name - Alpha Numeric
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 30, 'AN' );                                           //[31-60][30] First Name - Alpha Numeric
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 30, 'AN' );                                          //[61-90][30] Middle Name - Alpha Numeric
		$line[] = $this->padRecord( '', 3, 'AN' );                                                                                          //[91-93][3] Suffix - Alpha Numeric
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                                                         //[94-102][9] SSN - Numeric
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 3, 'N' );                                            //[103-105][3] Employer # - Numeric
		$line[] = $this->padRecord( $this->employment_status_code, 2, 'N' );                                                                //[106-107][2] Status Code - Numeric - 01=Monthly
		$line[] = $this->padRecord( $this->stripNonNumeric( str_pad( $this->month_of_year, 2, 0, STR_PAD_LEFT ) . $this->year ), 6, 'N' );  //[108-113][6] Processing Calendar Month/Year - Date
		$line[] = $this->padRecord( $this->stripNonNumeric( str_pad( $this->month_of_year, 2, 0, STR_PAD_LEFT ) . $this->year ), 6, 'N' );  //[114-119][6] Affected Reporting Month/Year - Date
		$line[] = $this->padRecord( $this->formatMSDateStamp( $this->start_date ), 8, 'N' );                                                //[120-127][8] Affected Month Begin Date - Date
		$line[] = $this->padRecord( $this->formatMSDateStamp( $this->end_date ), 8, 'N' );                                                  //[128-135][8] Affected Month End Date - Date
		$line[] = $this->padRecord( $this->removeDecimal( $base_hours ), 7, 'NN' );                                                         //[136-142][7] Base Hours - Signed Numeric
		$line[] = $this->padRecord( $this->removeDecimal( $actual_hours ), 7, 'NN' );                                                       //[143-149][7] Actual Hours - Signed Numeric
		$line[] = $this->padRecord( $this->removeDecimal( $base_wages ), 8, 'NN' );                                                         //[150-157][8] Base Wages - Signed Numeric
		$line[] = $this->padRecord( $this->removeDecimal( $actual_wages ), 8, 'AN' );                                                       //[158-165][8] Actual Wages - Signed Numeric
		$line[] = $this->padRecord( 0, 4, 'N' );                                                                                            //[166-169][4] Department/Location - Numeric -- Required only for Employer #100 and 193
		$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 8, 'N' );                                                 //[170-177][8] Wages Subject to Contribution - Signed Numeric
		$line[] = $this->padRecord( $this->removeDecimal( $employee_tax_withheld ), 8, 'N' );                                         		//[178-185][8] Employee Contribution - Signed Numeric - Deductions made from the employee's salary, paid to the System and credited to the employee's member account. Employees do not earn interest on any employee contributions credited to their member accounts.
		$line[] = $this->padRecord( $this->removeDecimal( $employer_tax_withheld ), 8, 'N' );                                        	    //[186-193][8] Employer Contribution - Signed Numeric - Payments made by employers to this System under the employee/employer contribution plan.
		$line[] = $this->padRecord( $this->removeDecimal( $employer_all_tax_withheld ), 8, 'N' );                                           //[194-201][8] ERPD Contributions - Signed Numeric - Employer Pay: Payments made by employers on behalf of those employees under the employer-pay contribution plan.
		$line[] = $this->padRecord( 'R', 1, 'AN' );                                                                                         //[202-202][1] Employment Type - Alpha Numeric
		$line[] = $this->padRecord( '', 2, 'AN' );                                                                                          //[203-204][2] ERPD Factor Code - Alpha Numeric
		$line[] = $this->padRecord( ( $is_part_time == true ) ? 'Y' : 'N', 1, 'AN' );                                                       //[205-205][1] Part Time Indicator - Alpha Numeric
		$line[] = $this->padRecord( '', 8, 'AN' );                                                                                          //[206-213][8] Contract Start Date - Date - School employers only.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                                                          //[214-214][1] Position Type - Numeric - School employers only.
		$line[] = $this->padRecord( 'N', 1, 'AN' );                                                                                         //[215-215][1] Promotion Within the Pay Period - Alphanumeric
		$line[] = $this->padRecord( ( $is_employee_hourly == true ) ? 'Y' : 'N', 1, 'AN' );                                                 //[216-216][1] Employee Hourly - Alphanumeric - Required, if "Base Hours" is not equal to zero, "Employee Hourly" must be 'Y'
		$line[] = $this->padRecord( $this->removeDecimal( $hourly_rate ), 8, 'N' );                                                         //[217-224][8] Employee Hourly Rate of Pay - Signed Numeric - Required if "Part Time Indicator" is 'Y'. Optional if "Employee Hourly" = 'Y' and "Part Time Indicator" is 'N'. Numbers only excluding the first character which may be a '-' or a leading 0. The last two digits are always cents for currency fields. Not required for status codes 30-38

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 224 ) {
			Debug::Text( 'ERROR! NV Record length is incorrect, should be 224 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'NV Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileNE() {
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 6, 'N' );                            //[1-6][6] Contract or Plan Number
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                                         //[7-15][9] Social Security Number
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ) . ', ' . $this->stripNonAlphaNumeric( $this->first_name ), 35, 'AN' ); //[16-50][35] Name field - d (comma separating last & first name)
		$line[] = $this->padRecord( '', 4, 'AN' );                                                                          //[51-54][4] (Not used)
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->agency_number ), 9, 'NN' );                              //[55-63][9] Agency Number, if applicable
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 30, 'AN' );                             //[64-93][30] Address Line 1
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 30, 'AN' );                             //[94-123][30] Address Line 2
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 18, 'AN' );                                 //[124-141][18] City
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                                 //[142-143][2] State Abbreviation
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 9, 'N' );                               //[144-152][9] Zip
		$line[] = $this->padRecord( '', 1, 'AN' );                                                                          //[153-153][1] (Not used)
		$line[] = $this->padRecord( '', 4, 'AN' );                                                                          //[154-157][4] (Not used)
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->birth_date ), 8, 'N' );                                //[158-165][8] Date of Birth - (CCYYMMDD)
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->hire_date ), 8, 'N' );                                 //[166-173][8] Date of Hire - (CCYYMMDD)
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->termination_date ), 8, 'NN' );                         //[174-181][8] Date of Termination, if applicable - (CCYYMMDD)
		$line[] = $this->padRecord( $this->removeDecimal( $this->employee_tax_withheld ), 9, 'N' );                         //[182-190][9] Member Pre-Tax Contribution
		$line[] = $this->padRecord( 0, 9, 'N' );                                                                            //[191-199][9] (Not used)
		$line[] = $this->padRecord( $this->removeDecimal( $this->employer_tax_withheld ), 9, 'N' );                         //[200-208][9] County Match Contribution
		$line[] = $this->padRecord( 0, 9, 'N' );                                                                            //[209-217][9] (Not used)
		$line[] = $this->padRecord( 0, 9, 'N' );                                                                            //[218-226][9] Member Make-up Contribution
		$line[] = $this->padRecord( 0, 9, 'N' );                                                                            //[227-235][9] County Match Make-up Contribution
		$line[] = $this->padRecord( 0, 36, 'N' );                                                                           //[236-271][36] (Not used) ** The file format specification is invalid here, it claims this field is a length of 9, but its actually 36.
		$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages_ytd ), 9, 'N' );  							//[272-280][9] Gross Year-to-Date Compensation
		$line[] = $this->padRecord( 0, 18, 'N' );                                                                           //[281-298][18] (Not used)
		$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 9, 'N' );                                 //[299-307][9] Period Gross Compensation
		$line[] = $this->padRecord( 0, 18, 'N' );                                                                           //[308-325][18] (Not used)
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->plan_participation_date ), 8, 'N' );                   //[326-333][8] Plan Participation Date
		$line[] = $this->padRecord( ( ( $this->sex_id == 10 ) ? 'M' : 'F' ), 1, 'AN' );                                     //[334-334][1] Sex
		$line[] = $this->padRecord( 0, 4, 'N' );                                                                            //[335-338][4] (Not used)
		$line[] = $this->padRecord( '', 7, 'AN' );                                                                          //[339-345][7] (Not used)
		$line[] = $this->padRecord( '', 1, 'AN' );                                                                          //[346-346][1] (Not used)
		$line[] = $this->padRecord( '', 24, 'AN' );                                                                         //[347-370][24] (Not used)
		$line[] = $this->padRecord( 'N', 1, 'AN' );                                                                         //[371-371][1] Elected Official

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 371 ) {
			Debug::Text( 'ERROR! NE Record length is incorrect, should be 371 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'NE Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileIAHeader( $total ) {
		$line[] = $this->padRecord( 1, 1, 'N' );                                                                                           //[1-1][1] Record Type - Numeric/9
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 5, 'N' );                                           //[2-6][5] Employer ID - Numeric/99999
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->year . str_pad( $this->month_of_year, 2, 0, STR_PAD_LEFT ) ), 6, 'N' ); //[7-12][6] Wage Report Month - Numeric/YYYYMM
		$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 11, 'N' );                                               //[13-23][11] Total Reported Wages - Numeric/999999999V99
		$line[] = $this->padRecord( '', 377, 'AN' );                                                                                       //[24-400][377] Filler - String

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 400 ) {
			Debug::Text( 'ERROR! IA Record length is incorrect, should be 400 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'IA Header Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileIADetail() {
		$line[] = $this->padRecord( 1, 1, 'N' );                                                                             //[1-1][1] Record Type - Numeric/9
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 5, 'N' );                             //[2-6][5] Employer ID - Numeric/99999
		$line[] = $this->padRecord( '', 5, 'N' );                                                                            //[7-11][5] State Agency Code - Numeric/99999
		$line[] = $this->padRecord( $this->title_code, 2, 'AN' );                                                            //[12-13][2] Occupation Code - Numeric/99
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                                          //[14-22][9] SSN - Numeric/999999999
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 50, 'AN' );                             //[23-72][50] Last Name - Alpha
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 50, 'AN' );                            //[73-122][50] First Name - Alpha
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->filterMiddleName( $this->middle_name ) ), 1, 'AN' ); //[123-123][1] Middle Initial - Alpha
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 50, 'AN' );                              //[124-173][50] Street Address - Alphanumeric
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 50, 'AN' );                              //[174-223][50] Address - Qualifier - Alphanumeric
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 50, 'AN' );                                  //[224-273][50] City - Alpha
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                                  //[274-275][2] State - Alpha
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'N' );                                //[276-280][5] Zip – 5 - Numeric
		$line[] = $this->padRecord( '', 4, 'N' );                                                                            //[281-284][4] Zip – 4 - Numeric
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->birth_date ), 8, 'AN' );                                //[285-292][8] Date of Birth - Numeric/YYYYMMDD
		$line[] = $this->padRecord( ( ( $this->sex_id == 10 ) ? 'M' : 'F' ), 1, 'AN' );                                      //[293-293][1] Gender - Alpha
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->hire_date ), 8, 'AN' );                                 //[294-301][8] First Date of Employment - Numeric/YYYYMMDD
		$line[] = $this->padRecord( $this->formatIADateStamp( $this->termination_date ), 8, 'AN' );                          //[302-309][8] Termination Date - Numeric/YYYYMMDD
		$line[] = $this->padRecord( $this->formatIADateStamp( ( $this->termination_date != '' ) ? $this->last_transaction_date : '' ), 8, 'AN' ); //[310-317][8] Last Check Date - Numeric/YYYYMMDD - Only if employee is terminated.
		$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 8, 'N' );                                  //[318-325][8] Period Wages - Numeric/999999V99
		$line[] = $this->padRecord( '', 75, 'AN' );                                                                          //[326-400][75] Filler - String

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 400 ) {
			Debug::Text( 'ERROR! IA Detail Record length is incorrect, should be 400 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'IA Detail Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileIATrailer( $total ) {
		$line[] = $this->padRecord( 3, 1, 'N' );                                                                                           //[1-1][1] Record Type - Numeric/9
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 5, 'N' );                                           //[2-6][5] Employer ID - Numeric/99999
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ) . str_pad( $this->month_of_year, 2, 0, STR_PAD_LEFT ), 6, 'N' ); //[7-12][6] Wage Report Month - Numeric/YYYYMM
		$line[] = $this->padRecord( $total->total, 10, 'N' );                                                                              //[13-22][10] Record Count - Numeric/9999999999
		$line[] = $this->padRecord( '', 378, 'AN' );                                                                                       //[23-400][378] Filler - String

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 400 ) {
			Debug::Text( 'ERROR! IA Trailer Record length is incorrect, should be 400 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'IA Trailer Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _outputEFILE( $type = null ) {
		/*
		 * Every state has a completely different file format.
		 */

		$records = $this->getRecords();

		//Debug::Arr($records, 'Output EFILE Records: ',__FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $records ) && count( $records ) > 0 ) {

			$retval = '';

			switch ( strtolower( $this->efile_state ) ) {
				case 'ms':
					$i = 0;
					foreach ( $records as $pers_data ) {
						$this->arrayToObject( $pers_data ); //Convert record array to object

						$retval .= $this->padLine( $this->_compileMS() );

						$this->revertToOriginalDataState();

						$i++;
					}

					break;
				case 'ne':
					$i = 0;
					foreach ( $records as $pers_data ) {
						$this->arrayToObject( $pers_data ); //Convert record array to object

						$retval .= $this->padLine( $this->_compileNE() );

						$this->revertToOriginalDataState();

						$i++;
					}

					break;
				case 'nv':
					$i = 0;
					foreach ( $records as $pers_data ) {
						$this->arrayToObject( $pers_data ); //Convert record array to object

						$retval .= $this->padLine( $this->_compileNV() );

						$this->revertToOriginalDataState();

						$i++;
					}

					break;
				case 'ia':
					$state_total = (object)TTMath::ArrayAssocSum( $records, null, 8 );
					$state_total->total = 0;

					$retval .= $this->padLine( $this->_compileIAHeader( $state_total ) ); //This also excludes RS records for other states, but we need make sure we only consider totals for just this state too.

					$i = 0;
					foreach ( $records as $pers_data ) {
						$this->arrayToObject( $pers_data ); //Convert record array to object

						$compile_rs_retval = $this->padLine( $this->_compileIADetail() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.

						if ( $compile_rs_retval != '' ) {
							$retval .= $compile_rs_retval;

							$state_total->total += 1;
						}

						$this->revertToOriginalDataState();

						$i++;
					}

					$retval .= $this->padLine( $this->_compileIATrailer( $state_total ) ); //This also excludes RS records for other states, but we need make sure we only consider totals for just this state too.

					break;
			}
		}

		if ( isset( $retval ) ) {
			return $retval;
		}

		return false;
	}
}

?>