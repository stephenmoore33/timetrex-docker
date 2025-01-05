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


/*
* Other tax calculators:
    https://www.symmetry.com/calculators-by-symmetry/try-it-free **This is one is updated quickly.
	http://www.paycheckcity.com/calculator/salary/ **This is powered by Symmetry above.
	http://payroll.intuit.com/paycheck_calculators/legacy/
	http://www.yourmoneypage.com/withhold/fedwh1.php

	- List of tax table changes*****:
		http://legacy.americanpayroll.org/paystate/paystateupdate.html
		https://www.tax-tables.org/
		http://www.payroll-taxes.com/federal-tax
		https://www.aatrix.com/news/

	- Federal/State tax information: http://www.payroll-taxes.com/state-tax.htm

	- QuickBooks payroll updates: https://community.intuit.com/articles/1434289-intuit-quickbooks-desktop-payroll-news-updates

//
//***Update PayrollDeduction.class.php with updated date/version
//

//CHANGED-* means document was updated and did change.
//NOCHANGE-* means document was updated for the year, but no changes affected the formulas.
//*CHECKAGAIN-* means document hasn't been updated and needs to be rechecked.

//Change every year usually
*CHECKAGAIN-*:22-Dec-22			State UI Wage Bases		- https://workforce.equifax.com/all-blogs/-/post/outlook-for-sui-tax-rates-in-2023-and-beyond/ or https://www.americanpayroll.org/compliance/compliance-overview/state-unemployment-wage-bases
CHANGED:28-Dec-24 				Federal          		- https://www.irs.gov/pub/irs-pdf/p15t.pdf or Draft: https://www.irs.gov/pub/irs-dft/p15t--dft.pdf

CHANGED:10-Jun-24				'ID' => 'Idaho',		- https://tax.idaho.gov/taxes/income-tax/withholding/computing/
CHANGED:09-Jul-24				'AR' => 'Arkansas'		- https://www.dfa.arkansas.gov/income-tax/withholding-tax-branch/withholding-tax-forms-and-instructions/ *Witholding Tax Formula ***They use a minus calculation, so we have changed the calculation to use their exact formula without recalculating the brackets ourself.
CHANGED:07-Dec-23				'CA' => 'California' 	- https://edd.ca.gov/en/Payroll_Taxes/Rates_and_Withholding *PIT Withholding schedules
CHANGED:07-Dec-23				'KY' => 'Kentucky', 	- http://revenue.ky.gov/Business/Pages/Employer-Payroll-Withholding.aspx *Search: Withholding Computer Formula -- Standard Deduction adjusted each year in Computer Formula (Optional Withholding Method) - 2018 switched to flat rate 5%.
CHANGED:07-Dec-23		   		'MI' => 'Michigan',		- https://www.michigan.gov/taxes/biz-forms/withholding/2024-withholding-tax-forms *Michigan Income Tax Withholding Guide 446-I
CHANGED:07-Dec-23				'MO' => 'Missouri',		- http://dor.mo.gov/business/withhold/ *Click on Withholding Formula to see update for each year.
CHANGED:07-Dec-23				'NM' => 'New Mexico', 	- https://www.tax.newmexico.gov/businesses/wage-withholding-tax/ *FYI-104 ***Often changes in Jan.
CHANGED:07-Dec-23				'OR' => 'Oregon',		- https://www.oregon.gov/DOR/programs/businesses/Pages/payroll-updates.aspx *Search: Withholdings Tax Formulas 2013
CHANGED:07-Dec-23				'RI' => 'Rhode Island', - https://tax.ri.gov/resources/software-developers *Withholding Tax Tables Booklet
CHANGED:07-Dec-23				'SC' => 'South Carolina'- https://dor.sc.gov/tax/withholding/forms *Formula for Computing SC Withholding Tax WH-1603F
CHANGED:13-Dec-23				'IA' => 'Iowa',			- https://tax.iowa.gov/withholding-tax-information *Iowa Withholding Tax Guide
CHANGED:13-Dec-23				'ME' => 'Maine',		- https://www.maine.gov/revenue/tax-return-forms/employment-tax-returns-2023 -- Check each year on the right of the page.
CHANGED:13-Dec-23				'MN' => 'Minnesota',	- https://www.revenue.state.mn.us/withholding-tax *2013 Minnesota Withholding Computer Formula - Calculator: https://www.mndor.state.mn.us/tp/withholdingtaxcalc/_/
CHANGED:13-Dec-23 				'MS' => 'Mississippi',	- https://www.dor.ms.gov/business/withholding-tax *Pub 89-700 - Also see: Flowchart - Withholding Computer Payroll Accounting
CHANGED:13-Dec-23				'NC' => 'North Carolina'- https://www.ncdor.gov/documents/income-tax-withholding-tables-and-instructions-employers *Income Tax Withholding Tables & Instructions for Employers, NC30
CHANGED:19-Dec-23				'VT' => 'Vermont',		- http://tax.vermont.gov/business-and-corp/withholding-tax *Income Tax Withholding  Instructions, Tables, and Charts.
CHANGED:28-Dec-23				'IL' => 'Illinois',		- https://tax.illinois.gov/forms/withholding/currentyear.html *Booklet IL-700-T
CHANGED:28-Dec-23				'ND' => 'North Dakota', - https://www.tax.nd.gov/business/income-tax-withholding *Income Tax Withholding Rates & Instructions
NOCHANGE:22-Dec-23				'NY' => 'New York',		- http://www.tax.ny.gov/forms/withholding_cur_forms.htm *WATCH NYS=New York State, NYC=New York City. NYS-50-T.1 or .2


//Change less often
CHANGED:10-Jun-24				'GA' => 'Georgia',		- https://dor.georgia.gov/employers-tax-guide *Employers Tax Guide
CHANGED:10-Jun-24			    'OH' => 'Ohio',			- https://tax.ohio.gov/business/resources/employer-withholding *Withholding Tables/Income Tax Withholding Instructions - Optional Computer Formula2
CHANGED:10-Jan-24				'MD' => 'Maryland',		- https://marylandtaxes.gov/business/income/withholding/index.php *Maryland Withholding Guide* - (https://www.marylandtaxes.gov/online-services/tax-calculators.php) Use 2.25% LOCAL INCOME TAX tables, *minus 2.25%*, manually calculate each bracket.  **PAY ATTENTION TO FILING STATUS AND WHICH SIDE ITS ON** Use tax_table_bracket_calculator.ods. See MD.class.php for more information.
NOCHANGE:03-Jan-24 (Updated but no change) 'MA' => 'Massachusetts' - http://www.mass.gov/dor/individuals/taxpayer-help-and-resources/tax-guides/withholding-tax-guide.html#calculate *Circular M
CHECKAGAIN:09-Dec-22			'DC' => 'D.C.', 		- http://otr.cfo.dc.gov/page/income-tax-withholding-instructions-and-tables *Form: FR-230
CHECKAGAIN:11-Dec-21			'HI' => 'Hawaii',		- http://tax.hawaii.gov/forms/a1_b1_5whhold/ *Employers Tax Guide (Booklet A)
CHANGED:07-Dec-23				'OK' => 'Oklahoma',		- https://oklahoma.gov/tax/businesses/withholding.html *OW-2, Oklahoma Income Tax Withholding Tables
CHANGED:07-Dec-23				'NE' => 'Nebraska',		- https://revenue.nebraska.gov/businesses/circular-en-nebraska-income-tax-withholding-wages-pensions-and-annuities-and-gambling *Nebraska  Circular EN, Income Tax Withholding on Wages
CHANGED:19-Dec-23				'IN' => 'Indiana',		- https://www.in.gov/dor/tax-forms/withholding-tax-forms/ *Departmental Notice #1 DN01
NOCHANGE:19-Dec-23				'CO' => 'Colorado',		- https://www.colorado.gov/pacific/tax/withholding-forms *Form: DR 1098


//Rarely change
CHANGED:06-Aug-24				'KS' => 'Kansas',		- http://www.ksrevenue.org/forms-btwh.html *Form: KW-100
CHANGED:10-Jun-24				'UT' => 'Utah',			- http://tax.utah.gov/withholding *PUB 14, Withholding Tax Guide
CHECKAGAIN:07-Jun-23			'WV' => 'West Virginia',- http://tax.wv.gov/Business/Withholding/Pages/WithholdingTaxForms.aspx *IT-100.1A
CHECKAGAIN:14-Jan-22			'LA' => 'Louisiana',	- http://revenue.louisiana.gov/WithholdingTax *R-1210 or R-1306
CHECKAGAIN:15-Apr-24		    'VA' => 'Virginia',		- https://www.tax.virginia.gov/forms/search?category=6 *Employer Withholding Instructions
CHECKAGAIN:21-Dec-22			'WI' => 'Wisconsin',	- https://www.revenue.wi.gov/Pages/ISE/with-Home.aspx *Pub W-166, Method "B" calculation
CHECKAGAIN:11-Dec-21			'PA' => 'Pennsylvania', - https://www.revenue.pa.gov/TaxTypes/EmployerWithholding/Pages/default.aspx *Rev 415 - Employer Withholding Information
CHECKAGAIN:11-Dec-21			'NJ' => 'New Jersey',	- http://www.state.nj.us/treasury/taxation/freqqite.shtml *Withholding Rate Tables
CHECKAGAIN:11-Dec-21			'DE' => 'Delaware',		- http://revenue.delaware.gov/services/WITBk.shtml *http://revenue.delaware.gov/services/wit_folder/section17.shtml
NOCHANGE:27-Dec-23 (Updated but no change)    'AL' => 'Alabama' 		- https://revenue.alabama.gov/individual-corporate/withholding-tax/ *Withholding Tax Tables and Instructions **Includes federal withholding, so difficult to compare with other calculators or even their own tables.
CHANGED:07-Dec-23				'CT' => 'Connecticut'	- https://portal.ct.gov/DRS/DRS-Forms/Current-Year-Forms/Withholding-Forms *May have to search for the latest year... Form TPG-211 Withholding Calculation Rules Effective
CHANGED:07-Dec-23			    'MT' => 'Montana',		- https://mtrevenue.gov/taxes/#WageWithholdingTax *Montana Withholding Tax Table and Guide link at the top.
NOCHANGE:22-Dec-23		   		'AZ' => 'Arizona',		- https://azdor.gov/forms/withholding-forms/arizona-withholding-percentage-election *Form A-4: Employees choose a straight percent to pick.


	(9 States without taxes)
	'AK' => 'Alaska',		- NO STATE TAXES
	'FL' => 'Florida',		- NO STATE TAXES
	'NV' => 'Nevada',		- NO STATE TAXES
	'NH' => 'New Hampshire' - NO STATE TAXES
	'SD' => 'South Dakota',	- NO STATE TAXES
	'TN' => 'Tennessee',	- NO STATE TAXES
	'TX' => 'Texas',		- NO STATE TAXES
	'WA' => 'Washington',	- NO STATE TAXES
	'WY' => 'Wyoming'		- NO STATE TAXES

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_Data extends PayrollDeduction_Base {
	var $db = null;
	var $income_tax_rates = [];
	var $country_primary_currency = 'USD';

	var $federal_allowance = [
			//No changes after W4 was redesigned in 2020.
			20200101 => 4300.00,
			20190101 => 4200.00,
			20180101 => 4150.00,
			//01-Jan-17 - No Change.
			20160101 => 4050.00,
			20150101 => 4000.00,
			20140101 => 3950.00,
			20130101 => 3900.00,
			20120101 => 3800.00,
			20110101 => 3700.00,
			//01-Jan-10 - No Change
			20090101 => 3650.00,
			20080101 => 3500.00,
			20070101 => 3400.00,
			20060101 => 3300.00,
	];

	//https://www.ssa.gov/news/press/factsheets/colafacts2021.pdf
	var $social_security_options = [
			20240101 => [ //2024
						  'maximum_earnings' => 168600,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20230101 => [ //2023
						  'maximum_earnings' => 160200,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20220101 => [ //2022
						  'maximum_earnings' => 147000,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20210101 => [ //2021
						  'maximum_earnings' => 142800,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20200101 => [ //2020
						  'maximum_earnings' => 137700,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20190101 => [ //2019
						  'maximum_earnings' => 132900,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20180101 => [ //2018
						  'maximum_earnings' => 128400,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20170101 => [ //2017
						  'maximum_earnings' => 127200,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20150101 => [ //2015
						  'maximum_earnings' => 118500,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20140101 => [ //2014
						  'maximum_earnings' => 117000,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20130101 => [ //2013
						  'maximum_earnings' => 113700,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20120101 => [ //2012
						  'maximum_earnings' => 110100,
						  'employee_rate'    => 4.2,
						  'employer_rate'    => 6.2,
			],
			20110101 => [ //2011 - Employer is still 6.2%
						  'maximum_earnings' => 106800,
						  'employee_rate'    => 4.2,
						  'employer_rate'    => 6.2,
			],
			//2010 - No Change.
			20090101 => [ //2009
						  'maximum_earnings' => 106800,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20080101 => [ //2008
						  'maximum_earnings' => 102000,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20070101 => [ //2007
						  'maximum_earnings' => 97500,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
			],
			20060101 => [ //2006
						  'maximum_earnings' => 94200,
						  'employee_rate'    => 6.2,
						  'employer_rate'    => 6.2,
						  //'maximum_contribution' => 5840.40 //Employee
			],
	];

	var $federal_ui_options = [
			20110701 => [ //2011 (July 1st)
						  'maximum_earnings' => 7000,
						  'rate'             => 6.0,
						  'minimum_rate'     => 0.6,
			],
			20060101 => [ //2006
						  'maximum_earnings' => 7000,
						  'rate'             => 6.2,
						  'minimum_rate'     => 0.8,
			],
	];

	var $medicare_options = [
		//No changes in 2015.
		20130101 => [ //2013
					  'employee_rate'           => 1.45,
					  'employee_threshold_rate' => 0.90, //Additional Medicare Rate
					  'employer_rate'           => 1.45,
					  'employer_threshold'      => 200000, //Additional Medicare Threshold for Form 941 - Actual rate varies from 125,000 to 250,000, but employers are only required to use and report based on 200,000
		],
		20060101 => [ //2006
					  'employee_rate'           => 1.45,
					  'employee_threshold_rate' => 0,
					  'employer_rate'           => 1.45,
					  'employer_threshold'      => 0, //Threshold for Form 941
		],
	];

	/*
		Federal Income Tax Rates
	*/
	var $federal_income_tax_rate_options = [
			20240101 => [
					0 => [ //2019 W4 *OR* 2020 W4 and One Job (Step 2 *NOT* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 6000, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 17600, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 53150, 'rate' => 12, 'constant' => 1160 ],
								   [ 'income' => 106525, 'rate' => 22, 'constant' => 5426 ],
								   [ 'income' => 197950, 'rate' => 24, 'constant' => 17168.50 ],
								   [ 'income' => 249725, 'rate' => 32, 'constant' => 39110.50 ],
								   [ 'income' => 615350, 'rate' => 35, 'constant' => 55678.50 ],
								   [ 'income' => 615350, 'rate' => 37, 'constant' => 183647.25 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 16300, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 39500, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 110600, 'rate' => 12, 'constant' => 2320 ],
								   [ 'income' => 217350, 'rate' => 22, 'constant' => 10852 ],
								   [ 'income' => 400200, 'rate' => 24, 'constant' => 34337 ],
								   [ 'income' => 503750, 'rate' => 32, 'constant' => 78221 ],
								   [ 'income' => 747500, 'rate' => 35, 'constant' => 111357 ],
								   [ 'income' => 747500, 'rate' => 37, 'constant' => 196669.50 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 13300, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 29850, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 76400, 'rate' => 12, 'constant' => 1655 ],
								   [ 'income' => 113800, 'rate' => 22, 'constant' => 7241 ],
								   [ 'income' => 205250, 'rate' => 24, 'constant' => 15469 ],
								   [ 'income' => 257000, 'rate' => 32, 'constant' => 37417 ],
								   [ 'income' => 622650, 'rate' => 35, 'constant' => 53977 ],
								   [ 'income' => 622650, 'rate' => 37, 'constant' => 181954.50 ],
						   ],
					],
					1 => [ //2020 W4 *AND* Two or more jobs. (Step 2 *IS* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 7300, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 13100, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 30875, 'rate' => 12, 'constant' => 580 ],
								   [ 'income' => 57563, 'rate' => 22, 'constant' => 2713 ],
								   [ 'income' => 103275, 'rate' => 24, 'constant' => 8584.25 ],
								   [ 'income' => 129163, 'rate' => 32, 'constant' => 19555.25 ],
								   [ 'income' => 311975, 'rate' => 35, 'constant' => 27839.25 ],
								   [ 'income' => 311975, 'rate' => 37, 'constant' => 91823.63 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 14600, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 26200, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 61750, 'rate' => 12, 'constant' => 1160 ],
								   [ 'income' => 115125, 'rate' => 22, 'constant' => 5426 ],
								   [ 'income' => 206550, 'rate' => 24, 'constant' => 17168.50 ],
								   [ 'income' => 258325, 'rate' => 32, 'constant' => 39110.50 ],
								   [ 'income' => 380200, 'rate' => 35, 'constant' => 55678.50 ],
								   [ 'income' => 380200, 'rate' => 37, 'constant' => 98334.75 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 10950, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 19225, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 42500, 'rate' => 12, 'constant' => 827.50 ],
								   [ 'income' => 61200, 'rate' => 22, 'constant' => 3620.50 ],
								   [ 'income' => 106925, 'rate' => 24, 'constant' => 7734.50 ],
								   [ 'income' => 132800, 'rate' => 32, 'constant' => 18708.50 ],
								   [ 'income' => 315625, 'rate' => 35, 'constant' => 26988.50 ],
								   [ 'income' => 315625, 'rate' => 37, 'constant' => 90977.25 ],
						   ],
					],
			],
			20230101 => [
					0 => [ //2019 W4 *OR* 2020 W4 and One Job (Step 2 *NOT* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 5250, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 16250, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 49975, 'rate' => 12, 'constant' => 1100 ],
								   [ 'income' => 100625, 'rate' => 22, 'constant' => 5147 ],
								   [ 'income' => 187350, 'rate' => 24, 'constant' => 16290 ],
								   [ 'income' => 236500, 'rate' => 32, 'constant' => 37104 ],
								   [ 'income' => 583375, 'rate' => 35, 'constant' => 52832 ],
								   [ 'income' => 583375, 'rate' => 37, 'constant' => 174238.25 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 14800, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 36800, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 104250, 'rate' => 12, 'constant' => 2200 ],
								   [ 'income' => 205550, 'rate' => 22, 'constant' => 10294 ],
								   [ 'income' => 379000, 'rate' => 24, 'constant' => 32580 ],
								   [ 'income' => 477300, 'rate' => 32, 'constant' => 74208 ],
								   [ 'income' => 708550, 'rate' => 35, 'constant' => 105664 ],
								   [ 'income' => 708550, 'rate' => 37, 'constant' => 186601.50 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 12200, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 27900, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 72050, 'rate' => 12, 'constant' => 1570 ],
								   [ 'income' => 107550, 'rate' => 22, 'constant' => 6868 ],
								   [ 'income' => 194300, 'rate' => 24, 'constant' => 14678 ],
								   [ 'income' => 243450, 'rate' => 32, 'constant' => 35498 ],
								   [ 'income' => 590300, 'rate' => 35, 'constant' => 51226 ],
								   [ 'income' => 590300, 'rate' => 37, 'constant' => 172623.50 ],
						   ],
					],
					1 => [ //2020 W4 *AND* Two or more jobs. (Step 2 *IS* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 6925, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 12425, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 29288, 'rate' => 12, 'constant' => 550 ],
								   [ 'income' => 54613, 'rate' => 22, 'constant' => 2573.50 ],
								   [ 'income' => 97975, 'rate' => 24, 'constant' => 8145 ],
								   [ 'income' => 122550, 'rate' => 32, 'constant' => 18552 ],
								   [ 'income' => 295988, 'rate' => 35, 'constant' => 26416 ],
								   [ 'income' => 295988, 'rate' => 37, 'constant' => 87119.13 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 13850, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 24850, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 58575, 'rate' => 12, 'constant' => 1100 ],
								   [ 'income' => 109225, 'rate' => 22, 'constant' => 5147 ],
								   [ 'income' => 195950, 'rate' => 24, 'constant' => 16290 ],
								   [ 'income' => 245100, 'rate' => 32, 'constant' => 37104 ],
								   [ 'income' => 360725, 'rate' => 35, 'constant' => 52832 ],
								   [ 'income' => 360725, 'rate' => 37, 'constant' => 93300.75 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 10400, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 18250, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 40325, 'rate' => 12, 'constant' => 785 ],
								   [ 'income' => 58075, 'rate' => 22, 'constant' => 3434 ],
								   [ 'income' => 101450, 'rate' => 24, 'constant' => 7339 ],
								   [ 'income' => 126025, 'rate' => 32, 'constant' => 17749 ],
								   [ 'income' => 299450, 'rate' => 35, 'constant' => 25613 ],
								   [ 'income' => 299450, 'rate' => 37, 'constant' => 86311.75 ],
						   ],
					],
			],
			20220101 => [
					0 => [ //2019 W4 *OR* 2020 W4 and One Job (Step 2 *NOT* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 4350, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 14625, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 46125, 'rate' => 12, 'constant' => 1027.50 ],
								   [ 'income' => 93425, 'rate' => 22, 'constant' => 4807.50 ],
								   [ 'income' => 174400, 'rate' => 24, 'constant' => 15213.50 ],
								   [ 'income' => 220300, 'rate' => 32, 'constant' => 34647.50 ],
								   [ 'income' => 544250, 'rate' => 35, 'constant' => 49335.50 ],
								   [ 'income' => 544250, 'rate' => 37, 'constant' => 162718 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 13000, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 33550, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 96550, 'rate' => 12, 'constant' => 2055 ],
								   [ 'income' => 191150, 'rate' => 22, 'constant' => 9615 ],
								   [ 'income' => 353100, 'rate' => 24, 'constant' => 30427 ],
								   [ 'income' => 444900, 'rate' => 32, 'constant' => 69295 ],
								   [ 'income' => 660850, 'rate' => 35, 'constant' => 98671 ],
								   [ 'income' => 660850, 'rate' => 37, 'constant' => 174253.50 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 10800, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 25450, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 66700, 'rate' => 12, 'constant' => 1465 ],
								   [ 'income' => 99850, 'rate' => 22, 'constant' => 6415 ],
								   [ 'income' => 180850, 'rate' => 24, 'constant' => 13708 ],
								   [ 'income' => 226750, 'rate' => 32, 'constant' => 33148 ],
								   [ 'income' => 550700, 'rate' => 35, 'constant' => 47836 ],
								   [ 'income' => 550700, 'rate' => 37, 'constant' => 161218.50 ],
						   ],
					],
					1 => [ //2020 W4 *AND* Two or more jobs. (Step 2 *IS* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 6475, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 11613, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 27363, 'rate' => 12, 'constant' => 513.75 ],
								   [ 'income' => 51013, 'rate' => 22, 'constant' => 2403.75 ],
								   [ 'income' => 91500, 'rate' => 24, 'constant' => 7606.75 ],
								   [ 'income' => 114450, 'rate' => 32, 'constant' => 17323.75 ],
								   [ 'income' => 276425, 'rate' => 35, 'constant' => 24667.75 ],
								   [ 'income' => 276425, 'rate' => 37, 'constant' => 81359 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 12950, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 23225, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 54725, 'rate' => 12, 'constant' => 1027.50 ],
								   [ 'income' => 102025, 'rate' => 22, 'constant' => 4807.50 ],
								   [ 'income' => 183000, 'rate' => 24, 'constant' => 15213.50 ],
								   [ 'income' => 228900, 'rate' => 32, 'constant' => 34647.50 ],
								   [ 'income' => 336875, 'rate' => 35, 'constant' => 49335.50 ],
								   [ 'income' => 336875, 'rate' => 37, 'constant' => 87126.75 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 9700, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 17025, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 37650, 'rate' => 12, 'constant' => 732.50 ],
								   [ 'income' => 54225, 'rate' => 22, 'constant' => 3207 ],
								   [ 'income' => 94725, 'rate' => 24, 'constant' => 6854 ],
								   [ 'income' => 117675, 'rate' => 32, 'constant' => 16574 ],
								   [ 'income' => 279650, 'rate' => 35, 'constant' => 23918 ],
								   [ 'income' => 279650, 'rate' => 37, 'constant' => 80609.25 ],
						   ],
					],
			],
			20210101 => [
					0 => [ //2019 W4 *OR* 2020 W4 and One Job (Step 2 *NOT* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 3950, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 13900, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 44475, 'rate' => 12, 'constant' => 995 ],
								   [ 'income' => 90325, 'rate' => 22, 'constant' => 4664 ],
								   [ 'income' => 168875, 'rate' => 24, 'constant' => 14751 ],
								   [ 'income' => 213375, 'rate' => 32, 'constant' => 33603 ],
								   [ 'income' => 527550, 'rate' => 35, 'constant' => 47843 ],
								   [ 'income' => 527550, 'rate' => 37, 'constant' => 157804.25 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 12200, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 32100, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 93250, 'rate' => 12, 'constant' => 1990 ],
								   [ 'income' => 184950, 'rate' => 22, 'constant' => 9328 ],
								   [ 'income' => 342050, 'rate' => 24, 'constant' => 29502 ],
								   [ 'income' => 431050, 'rate' => 32, 'constant' => 67206 ],
								   [ 'income' => 640500, 'rate' => 35, 'constant' => 95686 ],
								   [ 'income' => 640500, 'rate' => 37, 'constant' => 168993.50 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 10200, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 24400, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 64400, 'rate' => 12, 'constant' => 1420 ],
								   [ 'income' => 96550, 'rate' => 22, 'constant' => 6220 ],
								   [ 'income' => 175100, 'rate' => 24, 'constant' => 13293 ],
								   [ 'income' => 219600, 'rate' => 32, 'constant' => 32145 ],
								   [ 'income' => 533800, 'rate' => 35, 'constant' => 46385 ],
								   [ 'income' => 533800, 'rate' => 37, 'constant' => 156355 ],
						   ],
					],
					1 => [ //2020 W4 *AND* Two or more jobs. (Step 2 *IS* checked)
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 6275, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 11250, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 26538, 'rate' => 12, 'constant' => 497.50 ],
								   [ 'income' => 49463, 'rate' => 22, 'constant' => 2332 ],
								   [ 'income' => 88738, 'rate' => 24, 'constant' => 7375.50 ],
								   [ 'income' => 110988, 'rate' => 32, 'constant' => 16801.50 ],
								   [ 'income' => 268075, 'rate' => 35, 'constant' => 23921.50 ],
								   [ 'income' => 268075, 'rate' => 37, 'constant' => 78902.13 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 12550, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 22500, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 53075, 'rate' => 12, 'constant' => 995 ],
								   [ 'income' => 98925, 'rate' => 22, 'constant' => 4664 ],
								   [ 'income' => 177475, 'rate' => 24, 'constant' => 14751 ],
								   [ 'income' => 221975, 'rate' => 32, 'constant' => 33603 ],
								   [ 'income' => 326700, 'rate' => 35, 'constant' => 47843 ],
								   [ 'income' => 326700, 'rate' => 37, 'constant' => 84496.75 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 9400, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 16500, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 36500, 'rate' => 12, 'constant' => 710 ],
								   [ 'income' => 52575, 'rate' => 22, 'constant' => 3110 ],
								   [ 'income' => 91850, 'rate' => 24, 'constant' => 6646.50 ],
								   [ 'income' => 114100, 'rate' => 32, 'constant' => 16072.50 ],
								   [ 'income' => 271200, 'rate' => 35, 'constant' => 23192.50 ],
								   [ 'income' => 271200, 'rate' => 37, 'constant' => 78177.50 ],
						   ],
					],
			],
			20200101 => [
					0 => [ //2019 W4 *OR* 2020 W4 and One Job
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 3800, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 13675, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 43925, 'rate' => 12, 'constant' => 987.50 ],
								   [ 'income' => 89325, 'rate' => 22, 'constant' => 4617.50 ],
								   [ 'income' => 167100, 'rate' => 24, 'constant' => 14605.50 ],
								   [ 'income' => 211150, 'rate' => 32, 'constant' => 33271.50 ],
								   [ 'income' => 522200, 'rate' => 35, 'constant' => 47367.50 ],
								   [ 'income' => 522200, 'rate' => 37, 'constant' => 156235 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 11900, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 31650, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 92150, 'rate' => 12, 'constant' => 1975 ],
								   [ 'income' => 182950, 'rate' => 22, 'constant' => 9235 ],
								   [ 'income' => 338500, 'rate' => 24, 'constant' => 29211 ],
								   [ 'income' => 426600, 'rate' => 32, 'constant' => 66543 ],
								   [ 'income' => 633950, 'rate' => 35, 'constant' => 94735 ],
								   [ 'income' => 633950, 'rate' => 37, 'constant' => 167307.50 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 10050, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 24150, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 63750, 'rate' => 12, 'constant' => 1410 ],
								   [ 'income' => 95550, 'rate' => 22, 'constant' => 6162 ],
								   [ 'income' => 173350, 'rate' => 24, 'constant' => 13158 ],
								   [ 'income' => 217400, 'rate' => 32, 'constant' => 31830 ],
								   [ 'income' => 528450, 'rate' => 35, 'constant' => 45926 ],
								   [ 'income' => 528450, 'rate' => 37, 'constant' => 154793.50 ],
						   ],
					],
					1 => [ //2020 W4 *AND* Two or more jobs.
						   10 => [ //Single or Married Filing Separately
								   [ 'income' => 6200, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 11138, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 26263, 'rate' => 12, 'constant' => 493.75 ],
								   [ 'income' => 48963, 'rate' => 22, 'constant' => 2308.75 ],
								   [ 'income' => 87850, 'rate' => 24, 'constant' => 7302.75 ],
								   [ 'income' => 109875, 'rate' => 32, 'constant' => 16635.75 ],
								   [ 'income' => 265400, 'rate' => 35, 'constant' => 23683.75 ],
								   [ 'income' => 265400, 'rate' => 37, 'constant' => 78117.50 ],
						   ],
						   20 => [ //Married Filing Jointly
								   [ 'income' => 12400, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 22275, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 52525, 'rate' => 12, 'constant' => 987.50 ],
								   [ 'income' => 97925, 'rate' => 22, 'constant' => 4617.50 ],
								   [ 'income' => 175700, 'rate' => 24, 'constant' => 14605.50 ],
								   [ 'income' => 219750, 'rate' => 32, 'constant' => 33271.50 ],
								   [ 'income' => 323425, 'rate' => 35, 'constant' => 47367.50 ],
								   [ 'income' => 323425, 'rate' => 37, 'constant' => 83653.75 ],
						   ],
						   40 => [ //Head of Household
								   [ 'income' => 9325, 'rate' => 0, 'constant' => 0 ],
								   [ 'income' => 16375, 'rate' => 10, 'constant' => 0 ],
								   [ 'income' => 36175, 'rate' => 12, 'constant' => 705 ],
								   [ 'income' => 52075, 'rate' => 22, 'constant' => 3081 ],
								   [ 'income' => 90975, 'rate' => 24, 'constant' => 6579 ],
								   [ 'income' => 113000, 'rate' => 32, 'constant' => 15915 ],
								   [ 'income' => 268525, 'rate' => 35, 'constant' => 22963 ],
								   [ 'income' => 268525, 'rate' => 37, 'constant' => 77396.75 ],
						   ],
					],
			],
			20190101 => [
					10 => [ //Single
							[ 'income' => 3800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 13500, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 43275, 'rate' => 12, 'constant' => 970 ],
							[ 'income' => 88000, 'rate' => 22, 'constant' => 4543 ],
							[ 'income' => 164525, 'rate' => 24, 'constant' => 14382.50 ],
							[ 'income' => 207900, 'rate' => 32, 'constant' => 32748.50 ],
							[ 'income' => 514100, 'rate' => 35, 'constant' => 46628.50 ],
							[ 'income' => 514100, 'rate' => 37, 'constant' => 153798.50 ],
					],
					20 => [ //Married
							[ 'income' => 11800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 31200, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 90750, 'rate' => 12, 'constant' => 1940 ],
							[ 'income' => 180200, 'rate' => 22, 'constant' => 9086 ],
							[ 'income' => 333250, 'rate' => 24, 'constant' => 28765 ],
							[ 'income' => 420000, 'rate' => 32, 'constant' => 65497 ],
							[ 'income' => 624150, 'rate' => 35, 'constant' => 93257 ],
							[ 'income' => 624150, 'rate' => 37, 'constant' => 164709.50 ],
					],
			],
			20180101 => [
					10 => [ //Single
							[ 'income' => 3700, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 13225, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 42400, 'rate' => 12, 'constant' => 952.50 ],
							[ 'income' => 86200, 'rate' => 22, 'constant' => 4453.50 ],
							[ 'income' => 161200, 'rate' => 24, 'constant' => 14089.50 ],
							[ 'income' => 203700, 'rate' => 32, 'constant' => 32089.50 ],
							[ 'income' => 503700, 'rate' => 35, 'constant' => 45689.50 ],
							[ 'income' => 503700, 'rate' => 37, 'constant' => 150689.50 ],
					],
					20 => [ //Married
							[ 'income' => 11550, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 30600, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 88950, 'rate' => 12, 'constant' => 1905 ],
							[ 'income' => 176550, 'rate' => 22, 'constant' => 8907 ],
							[ 'income' => 326550, 'rate' => 24, 'constant' => 28179 ],
							[ 'income' => 411550, 'rate' => 32, 'constant' => 64179 ],
							[ 'income' => 611550, 'rate' => 35, 'constant' => 91379 ],
							[ 'income' => 611550, 'rate' => 37, 'constant' => 161379 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11625, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 40250, 'rate' => 15, 'constant' => 932.50 ],
							[ 'income' => 94200, 'rate' => 25, 'constant' => 5226.25 ],
							[ 'income' => 193950, 'rate' => 28, 'constant' => 18713.75 ],
							[ 'income' => 419000, 'rate' => 33, 'constant' => 46643.75 ],
							[ 'income' => 420700, 'rate' => 35, 'constant' => 120910.25 ],
							[ 'income' => 420700, 'rate' => 39.6, 'constant' => 121505.25 ],
					],
					20 => [
							[ 'income' => 8650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27300, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 84550, 'rate' => 15, 'constant' => 1865.00 ],
							[ 'income' => 161750, 'rate' => 25, 'constant' => 10452.50 ],
							[ 'income' => 242000, 'rate' => 28, 'constant' => 29752.50 ],
							[ 'income' => 425350, 'rate' => 33, 'constant' => 52222.50 ],
							[ 'income' => 479350, 'rate' => 35, 'constant' => 112728.00 ],
							[ 'income' => 479350, 'rate' => 39.6, 'constant' => 131628.00 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11525, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 39900, 'rate' => 15, 'constant' => 927.50 ],
							[ 'income' => 93400, 'rate' => 25, 'constant' => 5183.75 ],
							[ 'income' => 192400, 'rate' => 28, 'constant' => 18558.75 ],
							[ 'income' => 415600, 'rate' => 33, 'constant' => 46278.75 ],
							[ 'income' => 417300, 'rate' => 35, 'constant' => 119934.75 ],
							[ 'income' => 417300, 'rate' => 39.6, 'constant' => 120529.75 ],
					],
					20 => [
							[ 'income' => 8550, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27100, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 83850, 'rate' => 15, 'constant' => 1855.00 ],
							[ 'income' => 160450, 'rate' => 25, 'constant' => 10367.50 ],
							[ 'income' => 240000, 'rate' => 28, 'constant' => 29517.50 ],
							[ 'income' => 421900, 'rate' => 33, 'constant' => 51791.50 ],
							[ 'income' => 475500, 'rate' => 35, 'constant' => 111818.50 ],
							[ 'income' => 475500, 'rate' => 39.6, 'constant' => 130578.50 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11525, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 39750, 'rate' => 15, 'constant' => 922.50 ],
							[ 'income' => 93050, 'rate' => 25, 'constant' => 5156.25 ],
							[ 'income' => 191600, 'rate' => 28, 'constant' => 18481.25 ],
							[ 'income' => 413800, 'rate' => 33, 'constant' => 46075.25 ],
							[ 'income' => 415500, 'rate' => 35, 'constant' => 119401.25 ],
							[ 'income' => 415500, 'rate' => 39.6, 'constant' => 119996.25 ],
					],
					20 => [
							[ 'income' => 8600, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27050, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 83500, 'rate' => 15, 'constant' => 1845.00 ],
							[ 'income' => 159800, 'rate' => 25, 'constant' => 10312.50 ],
							[ 'income' => 239050, 'rate' => 28, 'constant' => 29387.50 ],
							[ 'income' => 420100, 'rate' => 33, 'constant' => 51577.50 ],
							[ 'income' => 473450, 'rate' => 35, 'constant' => 111324.00 ],
							[ 'income' => 473450, 'rate' => 39.6, 'constant' => 129996.50 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11325, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 39150, 'rate' => 15, 'constant' => 907.50 ],
							[ 'income' => 91600, 'rate' => 25, 'constant' => 5081.25 ],
							[ 'income' => 188600, 'rate' => 28, 'constant' => 18193.75 ],
							[ 'income' => 407350, 'rate' => 33, 'constant' => 45353.75 ],
							[ 'income' => 409000, 'rate' => 35, 'constant' => 112683.50 ],
							[ 'income' => 409000, 'rate' => 39.6, 'constant' => 118118.75 ],
					],
					20 => [
							[ 'income' => 8450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 26600, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 82250, 'rate' => 15, 'constant' => 1815.00 ],
							[ 'income' => 157300, 'rate' => 25, 'constant' => 10162.50 ],
							[ 'income' => 235300, 'rate' => 28, 'constant' => 28925.00 ],
							[ 'income' => 413550, 'rate' => 33, 'constant' => 50765.00 ],
							[ 'income' => 466050, 'rate' => 35, 'constant' => 109587.50 ],
							[ 'income' => 466050, 'rate' => 39.6, 'constant' => 127962.50 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11125, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 38450, 'rate' => 15, 'constant' => 892.50 ],
							[ 'income' => 90050, 'rate' => 25, 'constant' => 4991.25 ],
							[ 'income' => 185450, 'rate' => 28, 'constant' => 17891.25 ],
							[ 'income' => 400550, 'rate' => 33, 'constant' => 44603.25 ],
							[ 'income' => 402200, 'rate' => 35, 'constant' => 115586.25 ],
							[ 'income' => 402200, 'rate' => 39.6, 'constant' => 116163.75 ],
					],
					20 => [
							[ 'income' => 8300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 26150, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 80800, 'rate' => 15, 'constant' => 1785.00 ],
							[ 'income' => 154700, 'rate' => 25, 'constant' => 9982.50 ],
							[ 'income' => 231350, 'rate' => 28, 'constant' => 28457.50 ],
							[ 'income' => 406650, 'rate' => 33, 'constant' => 49919.50 ],
							[ 'income' => 458300, 'rate' => 35, 'constant' => 107768.50 ],
							[ 'income' => 458300, 'rate' => 39.6, 'constant' => 125846.00 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 2150, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10850, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 37500, 'rate' => 15, 'constant' => 870.00 ],
							[ 'income' => 87800, 'rate' => 25, 'constant' => 4867.50 ],
							[ 'income' => 180800, 'rate' => 28, 'constant' => 17442.50 ],
							[ 'income' => 390500, 'rate' => 33, 'constant' => 43482.50 ],
							[ 'income' => 390500, 'rate' => 35, 'constant' => 112683.50 ],
					],
					20 => [
							[ 'income' => 8100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 25500, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 78800, 'rate' => 15, 'constant' => 1740.00 ],
							[ 'income' => 150800, 'rate' => 25, 'constant' => 9735.00 ],
							[ 'income' => 225550, 'rate' => 28, 'constant' => 27735.00 ],
							[ 'income' => 396450, 'rate' => 33, 'constant' => 48665.00 ],
							[ 'income' => 396450, 'rate' => 35, 'constant' => 105062.00 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 2100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10600, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 36600, 'rate' => 15, 'constant' => 850.00 ],
							[ 'income' => 85700, 'rate' => 25, 'constant' => 4750.00 ],
							[ 'income' => 176500, 'rate' => 28, 'constant' => 17025.00 ],
							[ 'income' => 381250, 'rate' => 33, 'constant' => 42449.00 ],
							[ 'income' => 381250, 'rate' => 35, 'constant' => 110016.50 ],
					],
					20 => [
							[ 'income' => 7900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24900, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 76900, 'rate' => 15, 'constant' => 1700.00 ],
							[ 'income' => 147250, 'rate' => 25, 'constant' => 9500.00 ],
							[ 'income' => 220200, 'rate' => 28, 'constant' => 27087.50 ],
							[ 'income' => 387050, 'rate' => 33, 'constant' => 47513.50 ],
							[ 'income' => 387050, 'rate' => 35, 'constant' => 102574.00 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 6050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10425, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 36050, 'rate' => 15, 'constant' => 437.50 ],
							[ 'income' => 67700, 'rate' => 25, 'constant' => 4281.25 ],
							[ 'income' => 84450, 'rate' => 27, 'constant' => 12193.75 ],
							[ 'income' => 87700, 'rate' => 30, 'constant' => 16716.25 ],
							[ 'income' => 173900, 'rate' => 28, 'constant' => 17691.25 ],
							[ 'income' => 375700, 'rate' => 33, 'constant' => 41827.25 ],
							[ 'income' => 375700, 'rate' => 35, 'constant' => 108421.25 ],
					],
					20 => [
							[ 'income' => 13750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24500, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 75750, 'rate' => 15, 'constant' => 1075.00 ],
							[ 'income' => 94050, 'rate' => 25, 'constant' => 8762.50 ],
							[ 'income' => 124050, 'rate' => 27, 'constant' => 13337.50 ],
							[ 'income' => 145050, 'rate' => 25, 'constant' => 21437.50 ],
							[ 'income' => 217000, 'rate' => 28, 'constant' => 26687.50 ],
							[ 'income' => 381400, 'rate' => 33, 'constant' => 46833.50 ],
							[ 'income' => 381400, 'rate' => 35, 'constant' => 101085.50 ],
					],
			],
			20090401 => [
					10 => [
							[ 'income' => 7180, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10400, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 36200, 'rate' => 15, 'constant' => 322 ],
							[ 'income' => 66530, 'rate' => 25, 'constant' => 4192 ],
							[ 'income' => 173600, 'rate' => 28, 'constant' => 11774.50 ],
							[ 'income' => 375000, 'rate' => 33, 'constant' => 41754.10 ],
							[ 'income' => 375000, 'rate' => 35, 'constant' => 108216.10 ],
					],
					20 => [
							[ 'income' => 15750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24450, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 75650, 'rate' => 15, 'constant' => 870 ],
							[ 'income' => 118130, 'rate' => 25, 'constant' => 8550 ],
							[ 'income' => 216600, 'rate' => 28, 'constant' => 19170 ],
							[ 'income' => 380700, 'rate' => 33, 'constant' => 46741.60 ],
							[ 'income' => 380700, 'rate' => 35, 'constant' => 100894.60 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10400, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 35400, 'rate' => 15, 'constant' => 775 ],
							[ 'income' => 84300, 'rate' => 25, 'constant' => 4525 ],
							[ 'income' => 173600, 'rate' => 28, 'constant' => 16750 ],
							[ 'income' => 375000, 'rate' => 33, 'constant' => 41754 ],
							[ 'income' => 375000, 'rate' => 35, 'constant' => 108216 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 23950, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 75650, 'rate' => 15, 'constant' => 1595 ],
							[ 'income' => 144800, 'rate' => 25, 'constant' => 9350 ],
							[ 'income' => 216600, 'rate' => 28, 'constant' => 26637.50 ],
							[ 'income' => 380700, 'rate' => 33, 'constant' => 46741.50 ],
							[ 'income' => 380700, 'rate' => 35, 'constant' => 100894.50 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10300, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 33960, 'rate' => 15, 'constant' => 765.00 ],
							[ 'income' => 79725, 'rate' => 25, 'constant' => 4314.00 ],
							[ 'income' => 166500, 'rate' => 28, 'constant' => 15755.25 ],
							[ 'income' => 359650, 'rate' => 33, 'constant' => 4052.25 ],
							[ 'income' => 359650, 'rate' => 35, 'constant' => 103791.75 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 23550, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 72150, 'rate' => 15, 'constant' => 1555.00 ],
							[ 'income' => 137850, 'rate' => 25, 'constant' => 8845.00 ],
							[ 'income' => 207700, 'rate' => 28, 'constant' => 25270.00 ],
							[ 'income' => 365100, 'rate' => 33, 'constant' => 44828.00 ],
							[ 'income' => 365100, 'rate' => 35, 'constant' => 96770.00 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10120, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 33520, 'rate' => 15, 'constant' => 747 ],
							[ 'income' => 77075, 'rate' => 25, 'constant' => 4257 ],
							[ 'income' => 162800, 'rate' => 28, 'constant' => 15145.75 ],
							[ 'income' => 351650, 'rate' => 33, 'constant' => 39148.75 ],
							[ 'income' => 351650, 'rate' => 35, 'constant' => 101469.25 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 23350, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 70700, 'rate' => 15, 'constant' => 1535 ],
							[ 'income' => 133800, 'rate' => 25, 'constant' => 8637.50 ],
							[ 'income' => 203150, 'rate' => 28, 'constant' => 24412.50 ],
							[ 'income' => 357000, 'rate' => 33, 'constant' => 43830 ],
							[ 'income' => 357000, 'rate' => 35, 'constant' => 94601 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10000, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 32240, 'rate' => 15, 'constant' => 735 ],
							[ 'income' => 73250, 'rate' => 25, 'constant' => 4071 ],
							[ 'income' => 156650, 'rate' => 28, 'constant' => 14323.50 ],
							[ 'income' => 338400, 'rate' => 33, 'constant' => 37675.50 ],
							[ 'income' => 338400, 'rate' => 35, 'constant' => 97653 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 22900, 'rate' => 10, 'constant' => 0 ],
							[ 'income' => 68040, 'rate' => 15, 'constant' => 1490 ],
							[ 'income' => 126900, 'rate' => 25, 'constant' => 8261 ],
							[ 'income' => 195450, 'rate' => 28, 'constant' => 22976 ],
							[ 'income' => 343550, 'rate' => 33, 'constant' => 42170 ],
							[ 'income' => 343550, 'rate' => 35, 'constant' => 91043 ],
					],
			],
	];

	function __construct() {
		global $db;

		$this->db = $db;

		return true;
	}

	/*
	 * Clears calculated income tax rates so they can be re-calculated. Automatically called when data changes, like setFederalFilingStatus(), setStateFilingStatus(), etc...
	 */
	function clearIncomeTaxRates() {
		$this->income_tax_rates = [];
	}

	function getData() {
		//If the rates have already been calculated, just return them immediately.
		//  This is requied for MD->adjustRate() to function properly, as it modifies the rates, and therefore they can't be recalculated.
		//  Use $this->clearIncomeTaxRates() to force a recalculation.
		if ( !empty( $this->income_tax_rates ) ) {
			return $this;
		}

		$epoch = $this->getDate();

		$federal_status = $this->getFederalFilingStatus();
		if ( empty( $federal_status ) ) {
			$federal_status = 10;
		}
		$state_status = $this->getStateFilingStatus();
		if ( empty( $state_status ) ) {
			$state_status = 10;
		}
		$district_status = $this->getDistrictFilingStatus();

		if ( $epoch == null || $epoch == '' ) {
			$epoch = $this->getISODate( TTDate::getTime() );
		}

		//Debug::text( 'Using (' . $state . '/' . $district . ') values from: ' . TTDate::getDate( 'DATE+TIME', $this->getDateEpoch( $epoch ) ) . ' Status: State: ' . $state_status, __FILE__, __LINE__, __METHOD__, 10 );

		$this->income_tax_rates = [];
		if ( isset( $this->federal_income_tax_rate_options ) && count( $this->federal_income_tax_rate_options ) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$federal_income_tax_rate_options = $this->getDataFromRateArray( $epoch, $this->federal_income_tax_rate_options );

			$federal_multiple_jobs = (int)$this->getFederalMultipleJobs();
			if ( isset( $federal_income_tax_rate_options[$federal_multiple_jobs] ) && is_array( $federal_income_tax_rate_options[$federal_multiple_jobs] ) ) {
				Debug::text( '  Found tax tables split by one or more jobs... Multiple Jobs setting: ' . $federal_multiple_jobs, __FILE__, __LINE__, __METHOD__, 10 );
				$federal_income_tax_rate_options = $federal_income_tax_rate_options[$federal_multiple_jobs];
			}

			//Since 2020 when the W4's changed, the tax tables were split based on if the employee has one or more jobs. So to keep pre-2020 unit tests working, if we don't find a tax table for the filing status revert back to Single filing status as that definitely does exist.
			if ( !isset( $federal_income_tax_rate_options[$federal_status] ) ) {
				$federal_status = 10; //Single
			}

			if ( !isset( $federal_income_tax_rate_options[$federal_status] ) && isset( $federal_income_tax_rate_options[0] ) ) {
				$federal_status = 0;
			}

			if ( isset( $federal_income_tax_rate_options[$federal_status] ) ) {
				foreach ( $federal_income_tax_rate_options[$federal_status] as $data ) {
					$this->income_tax_rates['federal'][] = [
							'prev_income'   => $prev_income,
							'income'        => $data['income'],
							'prev_rate'     => TTMath::div( $prev_rate, 100 ),
							'rate'          => TTMath::div( $data['rate'], 100 ),
							'prev_constant' => $prev_constant,
							'constant'      => $data['constant'],
					];

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset( $prev_income, $prev_rate, $prev_constant, $data, $federal_income_tax_rate_options );
		}

		if ( isset( $this->state_income_tax_rate_options ) && count( $this->state_income_tax_rate_options ) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$state_income_tax_rate_options = $this->getDataFromRateArray( $epoch, $this->state_income_tax_rate_options );
			if ( !isset( $state_income_tax_rate_options[$state_status] ) && isset( $state_income_tax_rate_options[0] ) ) {
				$state_status = 0;
			}

			if ( isset( $state_income_tax_rate_options[$state_status] ) ) {
				foreach ( $state_income_tax_rate_options[$state_status] as $data ) {
					$this->income_tax_rates['state'][] = [
							'prev_income'   => $prev_income,
							'income'        => $data['income'],
							'prev_rate'     => TTMath::div( $prev_rate, 100 ),
							'rate'          => TTMath::div( $data['rate'], 100 ),
							'prev_constant' => $prev_constant,
							'constant'      => $data['constant'],
					];

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset( $prev_income, $prev_rate, $prev_constant, $data, $state_income_tax_rate_options );
		}

		if ( isset( $this->district_income_tax_rate_options ) && count( $this->district_income_tax_rate_options ) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$district_income_tax_rate_options = $this->getDataFromRateArray( $epoch, $this->district_income_tax_rate_options );
			if ( !isset( $district_income_tax_rate_options[$district_status] ) && isset( $district_income_tax_rate_options[0] ) ) {
				$district_status = 0;
			}

			if ( isset( $district_income_tax_rate_options[$district_status] ) ) {
				foreach ( $district_income_tax_rate_options[$district_status] as $data ) {
					$this->income_tax_rates['district'][] = [
							'prev_income'   => $prev_income,
							'income'        => $data['income'],
							'prev_rate'     => TTMath::div( $prev_rate, 100 ),
							'rate'          => TTMath::div( $data['rate'], 100 ),
							'prev_constant' => $prev_constant,
							'constant'      => $data['constant'],
					];

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset( $prev_income, $prev_rate, $prev_constant, $district_income_tax_rate_options );
		}

		if ( isset( $this->income_tax_rates ) && is_array( $this->income_tax_rates ) ) {
			foreach ( $this->income_tax_rates as $type => $brackets ) {
				$i = 0;
				$total_brackets = ( count( $brackets ) - 1 );
				foreach ( $brackets as $key => $bracket_data ) {
					if ( $i == 0 ) {
						$first = true;
					} else {
						$first = false;
					}

					if ( $i == $total_brackets ) {
						$last = true;
					} else {
						$last = false;
					}

					$this->income_tax_rates[$type][$key]['first'] = $first;
					$this->income_tax_rates[$type][$key]['last'] = $last;

					$i++;
				}
			}
		}

		//Debug::Arr($this->income_tax_rates, 'Income Tax Rates: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this;
	}

	function getRateArray( $income, $type ) {
		Debug::text( 'Calculating ' . $type . ' Taxes on: $' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		$blank_arr = [ 'rate' => null, 'constant' => null, 'prev_income' => null, ];

		if ( isset( $this->income_tax_rates[$type] ) ) {
			$rates = $this->income_tax_rates[$type];
		} else {
			Debug::text( 'aNO INCOME TAX RATES FOUND!!!!!! ' . $type . ' Taxes on: $' . $income, __FILE__, __LINE__, __METHOD__, 10 );

			return $blank_arr;
		}

		if ( count( $rates ) == 0 ) {
			Debug::text( 'bNO INCOME TAX RATES FOUND!!!!!! ' . $type . ' Taxes on: $' . $income, __FILE__, __LINE__, __METHOD__, 10 );

			return $blank_arr;
		}

		$prev_value = 0;
		$total_rates = ( count( $rates ) - 1 );
		$i = 0;
		foreach ( $rates as $key => $values ) {
			$value = $values['income'];

			if ( $income > $prev_value && $income <= $value ) {
				//Debug::text('Key: '. $key .' Value: '. $value .' Rate: '. $rate .' Constant: '. $constant .' Previous Value: '. $prev_value , __FILE__, __LINE__, __METHOD__, 10);
				return $this->income_tax_rates[$type][$key];
			} else if ( $i == $total_rates ) {
				//Debug::text('Last Key: '. $key .' Value: '. $value .' Rate: '. $rate .' Constant: '. $constant .' Previous Value: '. $prev_value , __FILE__, __LINE__, __METHOD__, 10);
				return $this->income_tax_rates[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return $blank_arr;
	}

	function getFederalHighestRate() {
		$arr = $this->getRateArray( 999999999, 'federal' );
		Debug::text( 'Federal Highest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getFederalRate( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getFederalPreviousRate( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Previous Rate: ' . $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_rate'];
	}

	function getFederalRatePreviousIncome( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Rate Previous Income: ' . $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_income'];
	}

	function getFederalRateIncome( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Rate Income: ' . $arr['income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['income'];
	}

	function getFederalConstant( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Constant: ' . $arr['constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['constant'];
	}

	function getFederalAllowanceAmount( $date ) {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->federal_allowance );
		if ( $retarr != false ) {
			return (float)$retarr;
		}

		return false;
	}


	function getStateHighestRate() {
		$arr = $this->getRateArray( 999999999, 'state' );
		Debug::text( 'State Highest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getStateRate( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getStatePreviousRate( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Previous Rate: ' . $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_rate'];
	}

	function getStateRatePreviousIncome( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Rate Previous Income: ' . $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_income'];
	}

	function getStateRateIncome( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Rate Income: ' . $arr['income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['income'];
	}

	function getStateConstant( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Constant: ' . $arr['constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['constant'];
	}

	function getStatePreviousConstant( $income ) {
		$arr = $this->getRateArray( $income, 'state' );
		Debug::text( 'State Previous Constant: ' . $arr['prev_constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_constant'];
	}

	function getDistrictHighestRate() {
		$arr = $this->getRateArray( 999999999, 'district' );
		Debug::text( 'District Highest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getDistrictRate( $income ) {
		$arr = $this->getRateArray( $income, 'district' );
		Debug::text( 'District Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getDistrictRatePreviousIncome( $income ) {
		$arr = $this->getRateArray( $income, 'district' );
		Debug::text( 'District Rate Previous Income: ' . $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['prev_income'];
	}

	function getDistrictRateIncome( $income ) {
		$arr = $this->getRateArray( $income, 'district' );
		Debug::text( 'District Rate Income: ' . $arr['income'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['income'];
	}

	function getDistrictConstant( $income ) {
		$arr = $this->getRateArray( $income, 'district' );
		Debug::text( 'District Constant: ' . $arr['constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['constant'];
	}

	//Social Security
	function getSocialSecurityMaximumEarnings() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->social_security_options );
		if ( $retarr != false ) {
			return (float)$retarr['maximum_earnings'];
		}

		return false;
	}

	function getSocialSecurityMaximumContribution( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->social_security_options );
		if ( $retarr != false ) {
			return TTMath::mul( $this->getSocialSecurityMaximumEarnings(), TTMath::div( $this->getSocialSecurityRate( $type ), 100 ) );
		}

		return false;
	}

	function getSocialSecurityRate( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->social_security_options );
		if ( $retarr != false ) {
			return (float)$retarr[$type . '_rate'];
		}

		return false;
	}

	//Medicare
	function getMedicareRate() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->medicare_options );
		if ( $retarr != false ) {
			return (array)$retarr;
		}

		return false;
	}

	function getMedicareAdditionalEmployerThreshold() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->medicare_options );
		if ( isset( $retarr['employer_threshold'] ) ) {
			return (float)$retarr['employer_threshold'];
		}

		return false;
	}


	//Federal UI
	function getFederalUIMinimumRate() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->federal_ui_options );
		if ( $retarr != false ) {
			return (float)$retarr['minimum_rate'];
		}

		return false;
	}

	function getFederalUIMaximumRate() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->federal_ui_options );
		if ( $retarr != false ) {
			return (float)$retarr['rate'];
		}

		return false;
	}

	function getFederalUIMaximumEarnings() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->federal_ui_options );
		if ( $retarr != false ) {
			return (float)$retarr['maximum_earnings'];
		}

		return false;
	}

	function getFederalUIMaximumContribution() {
		$retval = TTMath::mul( $this->getFederalUIMaximumEarnings(), TTMath::div( $this->getFederalUIRate(), 100 ) );
		if ( $retval != false ) {
			return (float)$retval;
		}

		return false;
	}
}

?>
