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
class PayrollDeduction_CA_Data extends PayrollDeduction_Base {
	var $db = null;
	var $income_tax_rates = [];
	var $country_primary_currency = 'CAD';

	//***Update PayrollDeduction.class.php with updated date/version

	/*
		Claim Code Basic Amounts (BPAF)
	*/
	var $basic_claim_code_options = [ //From Claim Code tables.
			20240101 => [ //01-Jan-2024:
						  'CA' => [ 'min' => 13521, 'max' => 15705, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
						  'AB' => 21885,
						  'BC' => 12580,
						  'MB' => 15780,
						  'NB' => 13044,
						  'NL' => 10818,
						  'NS' => 11481, //**Not Indexed. See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 17373,
						  'NU' => 18767,
						  'ON' => 12399,
						  'PE' => 13500,
						  'SK' => 18491,
						  'YT' => [ 'min' => 13521, 'max' => 15705, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
						  'QC' => 0,
			],
			20230701 => [ //01-Jul-2023:
						  'CA' => [ 'min' => 13521, 'max' => 15000, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
						  'AB' => 21003,
						  'BC' => 11981,
						  'MB' => 19145,
						  'NB' => 12458,
						  'NL' => 10382,
						  'NS' => 11481, //**Not Indexed. See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 16593,
						  'NU' => 17925,
						  'ON' => 11865,
						  'PE' => 12000,
						  'QC' => 0,
						  'SK' => 17661,
						  'YT' => [ 'min' => 13521, 'max' => 15000, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
			],
			20230101 => [ //01-Jan-2023:
						  'CA' => [ 'min' => 13521, 'max' => 15000, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
						  'AB' => 21003,
						  'BC' => 11981,
						  'MB' => 10855,
						  'NB' => 12458,
						  'NL' => 10382,
						  'NS' => 11481, //**Not Indexed. See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 16593,
						  'NU' => 17925,
						  'ON' => 11865,
						  'PE' => 12000,
						  'QC' => 0,
						  'SK' => 17661,
						  'YT' => [ 'min' => 13521, 'max' => 15000, 'phase_out_start' => 165430, 'phase_out_end' => 235675 ], //Federal - This is now phased out if net income is ~$165K or more, see Federal Basic Personal Amount (BPAF)
			],
			20220701 => [ //01-Jul-2022:
						  'CA' => [ 'min' => 12719, 'max' => 14398, 'phase_out_start' => 155625, 'phase_out_end' => 221708 ], //Federal - This is now phased out if net income is ~$150K or more, see Federal Basic Personal Amount (BPAF)
						  'AB' => 19369, //**Not indexed.
						  'BC' => 11302,
						  'MB' => 10145,
						  'NB' => 12623,
						  'NL' => 9803,
						  'NS' => 11481, //**Not Indexed. See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 15609,
						  'NU' => 16862,
						  'ON' => 11141,
						  'PE' => 11250,
						  'QC' => 0,
						  'SK' => 16615,
						  'YT' => [ 'min' => 12719, 'max' => 14398, 'phase_out_start' => 155625, 'phase_out_end' => 221708 ], //Federal - This is now phased out if net income is ~$150K or more, see Federal Basic Personal Amount (BPAF)
			],
			20220101 => [ //01-Jan-2022:
						  'CA' => [ 'min' => 12719, 'max' => 14398, 'phase_out_start' => 155625, 'phase_out_end' => 221708 ], //Federal - This is now phased out if net income is ~$150K or more, see Federal Basic Personal Amount (BPAF)
						  'AB' => 19369, //**Not indexed.
						  'BC' => 11302,
						  'MB' => 10145,
						  'NB' => 10817,
						  'NL' => 9803,
						  'NS' => 11481, //**Not Indexed. See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 15609,
						  'NU' => 16862,
						  'ON' => 11141,
						  'PE' => 11250,
						  'QC' => 0,
						  'SK' => 16615,
						  'YT' => [ 'min' => 12719, 'max' => 14398, 'phase_out_start' => 155625, 'phase_out_end' => 221708 ], //Federal - This is now phased out if net income is ~$150K or more, see Federal Basic Personal Amount (BPAF)
			],
			20210101 => [ //01-Jan-2021:
						  'CA' => [ 'min' => 12421, 'max' => 13808, 'phase_out_start' => 151978, 'phase_out_end' => 216511 ], //Federal - This is now phased out if net income is ~$150K or less, see Federal Basic Personal Amount (BPAF)
						  'AB' => 19369,
						  'BC' => 11070,
						  'MB' => 9936,
						  'NB' => 10564,
						  'NL' => 9536,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 15243,
						  'NU' => 16467,
						  'ON' => 10880,
						  'PE' => 10500,
						  'QC' => 0,
						  'SK' => 16225,
						  'YT' => [ 'min' => 12421, 'max' => 13808, 'phase_out_start' => 151978, 'phase_out_end' => 216511 ], //Federal - This is now phased out if net income is ~$150K or less, see Federal Basic Personal Amount (BPAF)
			],
			20200701 => [ //01-Jul-2020:
						  'CA' => [ 'min' => 12298, 'max' => 13229, 'phase_out_start' => 150473, 'phase_out_end' => 214368 ], //Federal - This is now phased out if net income is ~$150K or less, see Federal Basic Personal Amount (BPAF)
						  'AB' => 19369,
						  'BC' => 10949,
						  'MB' => 9838,
						  'NB' => 10459,
						  'NL' => 9498,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 15093,
						  'NU' => 16304,
						  'ON' => 10783,
						  'PE' => 10000,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => [ 'min' => 12298, 'max' => 14160, 'phase_out_start' => 150473, 'phase_out_end' => 214368 ], //YT - This is now phased out if net income is ~$150K or less, see Yukon Basic Personal Amount (BPAYT)
			],
			20200101 => [ //01-Jan-2020:
						  'CA' => [ 'min' => 12298, 'max' => 13229, 'phase_out_start' => 150473, 'phase_out_end' => 214368 ], //Federal - This is now phased out if net income is ~$150K or less, see Federal Basic Personal Amount (BPAF)
						  'AB' => 19369,
						  'BC' => 10949,
						  'MB' => 9838,
						  'NB' => 10459,
						  'NL' => 9498,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 15093,
						  'NU' => 16304,
						  'ON' => 10783,
						  'PE' => 10000,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 12298,
			],
			20190101 => [ //01-Jan-2019:
						  'CA' => 12069, //Federal
						  'AB' => 19369,
						  'BC' => 10682,
						  'MB' => 9626,
						  'NB' => 10264,
						  'NL' => 9414,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 14811,
						  'NU' => 13618,
						  'ON' => 10582,
						  'PE' => 9160,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 12069,
			],
			20180701 => [ //01-Jul-2018:
						  'CA' => 11809, //Federal
						  'AB' => 18915,
						  'BC' => 10412,
						  'MB' => 9382,
						  'NB' => 10043,
						  'NL' => 9247,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 14492,
						  'NU' => 13325,
						  'ON' => 10354,
						  'PE' => 9160,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 11809,
			],
			20180101 => [ //01-Jan-2018:
						  'CA' => 11809, //Federal
						  'AB' => 18915,
						  'BC' => 10412,
						  'MB' => 9382,
						  'NB' => 10043,
						  'NL' => 9247,
						  'NS' => 11481, //See NS.class.php, as there are a low and high basic claim amounts now.
						  'NT' => 14492,
						  'NU' => 13325,
						  'ON' => 10354,
						  'PE' => 8160,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 11809,
			],
			20170701 => [ //01-Jul-2017:
						  'CA' => 11635, //Federal
						  'AB' => 18690,
						  'BC' => 10208,
						  'MB' => 9271,
						  'NB' => 9895,
						  'NL' => 8978,
						  'NS' => 8481,
						  'NT' => 14278,
						  'NU' => 13128,
						  'ON' => 10171,
						  'PE' => 8320,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 11635,
			],
			20170101 => [ //01-Jan-2017:
						  'CA' => 11635, //Federal
						  'AB' => 18690,
						  'BC' => 10208,
						  'MB' => 9271,
						  'NB' => 9895,
						  'NL' => 8978,
						  'NS' => 8481,
						  'NT' => 14278,
						  'NU' => 13128,
						  'ON' => 10171,
						  'PE' => 8000,
						  'QC' => 0,
						  'SK' => 16065,
						  'YT' => 11635,
			],
			20160701 => [ //01-Jul-2016:
						  'CA' => 11474, //Federal
						  'AB' => 18451,
						  'BC' => 10027,
						  'MB' => 9134,
						  'NB' => 9758,
						  'NL' => 8802,
						  'NS' => 8481,
						  'NT' => 14081,
						  'NU' => 12947,
						  'ON' => 10011,
						  'PE' => 8292,
						  'QC' => 0,
						  'SK' => 15843,
						  'YT' => 11474,
			],
			20160101 => [ //01-Jan-2016:
						  'CA' => 11474, //Federal
						  'AB' => 18451,
						  'BC' => 10027,
						  'MB' => 9134,
						  'NB' => 9758,
						  'NL' => 8802,
						  'NS' => 8481,
						  'NT' => 14081,
						  'NU' => 12947,
						  'ON' => 10011,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 15843,
						  'YT' => 11474,
			],
			20150101 => [ //01-Jan-2015:
						  'CA' => 11327, //Federal
						  'AB' => 18214,
						  'BC' => 9938,
						  'MB' => 9134,
						  'NB' => 9633,
						  'NL' => 8767,
						  'NS' => 8481,
						  'NT' => 13900,
						  'NU' => 12781,
						  'ON' => 9863,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 15639,
						  'YT' => 11327,
			],
			20140101 => [ //01-Jan-2014:
						  'CA' => 11138, //Federal
						  'AB' => 17787,
						  'BC' => 9869,
						  'MB' => 9134,
						  'NB' => 9472,
						  'NL' => 8578,
						  'NS' => 8481,
						  'NT' => 13668,
						  'NU' => 12567,
						  'ON' => 9670,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 15378,
						  'YT' => 11138,
			],
			20130101 => [ //01-Jan-2013:
						  'CA' => 11038, //Federal
						  'AB' => 17593,
						  'BC' => 10276,
						  'MB' => 8884,
						  'NB' => 9388,
						  'NL' => 8451,
						  'NS' => 8481,
						  'NT' => 13546,
						  'NU' => 12455,
						  'ON' => 9574,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 15241,
						  'YT' => 11038,
			],
			20120101 => [ //01-Jan-2012:
						  'CA' => 10822, //Federal
						  'AB' => 17282,
						  'BC' => 11354,
						  'MB' => 8634,
						  'NB' => 9203,
						  'NL' => 8237,
						  'NS' => 8481,
						  'NT' => 13280,
						  'NU' => 12211,
						  'ON' => 9405,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 14942,
						  'YT' => 10822,
			],
			20110701 => [ //01-Jul-2011: Some of these are only changed for the last 6mths in the year.
						  'CA' => 10527, //Federal
						  'AB' => 16977,
						  'BC' => 11088,
						  'MB' => 8634,
						  'NB' => 8953,
						  'NL' => 7989,
						  'NS' => 8731,
						  'NT' => 12919,
						  'NU' => 11878,
						  'ON' => 9104,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 14535,
						  'YT' => 10527,
			],
			20110101 => [ //01-Jan-2011
						  'CA' => 10527, //Federal
						  'AB' => 16977,
						  'BC' => 11088,
						  'MB' => 8134,
						  'NB' => 8953,
						  'NL' => 7989,
						  'NS' => 8231,
						  'NT' => 12919,
						  'NU' => 11878,
						  'ON' => 9104,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 13535,
						  'YT' => 10527,
			],
			20100101 => [ //01-Jan-2010
						  'CA' => 10382, //Federal
						  'AB' => 16825,
						  'BC' => 11000,
						  'MB' => 8134,
						  'NB' => 8777,
						  'NL' => 7833,
						  'NS' => 8231,
						  'NT' => 12740,
						  'NU' => 11714,
						  'ON' => 8943,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 13348,
						  'YT' => 10382,
			],
			20090401 => [ //01-Apr-09
						  'CA' => 10375, //Federal
						  'AB' => 16775,
						  'BC' => 9373,
						  'MB' => 8134,
						  'NB' => 8134,
						  'NL' => 7778,
						  'NS' => 7981,
						  'NT' => 12664,
						  'NU' => 11644,
						  'ON' => 8881,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 13269,
						  'YT' => 10375,
			],
			20090101 => [ //01-Jan-09
						  'CA' => 10100, //Federal
						  'AB' => 16775,
						  'BC' => 9373,
						  'MB' => 8134,
						  'NB' => 8134,
						  'NL' => 7778,
						  'NS' => 7981,
						  'NT' => 12664,
						  'NU' => 11644,
						  'ON' => 8881,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 13269,
						  'YT' => 10100,
			],
			20080101 => [ //01-Jan-08
						  'CA' => 9600, //Federal
						  'AB' => 16161,
						  'BC' => 9189,
						  'MB' => 8034,
						  'NB' => 8395,
						  'NL' => 7566,
						  'NS' => 7731,
						  'NT' => 12355,
						  'NU' => 11360,
						  'ON' => 8681,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 8945,
						  'YT' => 9600,
			],
			20070701 => [ //01-Jul-07
						  'CA' => 8929, //Federal
						  'AB' => 15435,
						  'BC' => 9027,
						  'MB' => 7834,
						  'NB' => 8239,
						  'NL' => 7558,
						  'NS' => 7481,
						  'NT' => 12125,
						  'NU' => 11149,
						  'ON' => 8553,
						  'PE' => 7708,
						  'QC' => 0,
						  'SK' => 8778,
						  'YT' => 8929,
			],
			20070101 => [ //01-Jan-07
						  'CA' => 8929, //Federal
						  'AB' => 15435,
						  'BC' => 9027,
						  'MB' => 7834,
						  'NB' => 8239,
						  'NL' => 7410,
						  'NS' => 7481,
						  'NT' => 12125,
						  'NU' => 11149,
						  'ON' => 8553,
						  'PE' => 7412,
						  'QC' => 0,
						  'SK' => 8778,
						  'YT' => 8929,
			],
			20060701 => [ //01-Jul-06
						  'CA' => 8639, //Federal
						  'AB' => 14999,
						  'BC' => 8858,
						  'MB' => 7734,
						  'NB' => 8061,
						  'NL' => 7410,
						  'NS' => 7231,
						  'NT' => 11864,
						  'NU' => 10909,
						  'ON' => 8377,
						  'PE' => 7412,
						  'QC' => 0,
						  'SK' => 8589,
						  'YT' => 8328,
			],
			20060101 => [ //01-Jan-06
						  'CA' => 9039, //Federal
						  'AB' => 14799,
						  'BC' => 8858,
						  'MB' => 7734,
						  'NB' => 8061,
						  'NL' => 7410,
						  'NS' => 7231,
						  'NT' => 11864,
						  'NU' => 10909,
						  'ON' => 8377,
						  'PE' => 7412,
						  'QC' => 0,
						  'SK' => 8589,
						  'YT' => 8328,
			],
	];

	/*
		CPP settings
	*/
	var $cpp_options = [
			20240101 => [ //2024
						  //Canada Pension Plan (CPP)
						  'maximum_pensionable_earnings'         => 68500, //YMPE: **NOTE: There is maximum pensionable earnings, and maximum contributory earnings. This must be **pensionable**
						  'basic_exemption'                      => 3500, //Basic Exemption
						  'employee_rate'                        => 0.0595, //Employee and Employer Total Contribution Rate
						  'employee_maximum_contribution'        => 3867.50, //Maximum Employee and Employer Total Contribution

						  //Base Canada Pension Plan
						  'base_employee_rate'                   => 0.0495,
						  'base_employee_maximum_contribution'   => 3217.50,

						  //First additional Canada Pension Plan (CPP) (Appears to only be used for commission employees?)

						  //Second additional Canada Pension Plan (CPP2)
						  'second_employee_rate'                 => 0.04,
						  'second_employee_maximum_contribution' => 188.00,
						  'second_employee_maximum_pensionable_earnings' => 73200,
			],
			20230101 => [ //2023
						  'maximum_pensionable_earnings'  => 66600, //**NOTE: There is maximum pensionable earnings, and maximum contributory earnings. This must be **pensionable**
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0595,
						  'employee_maximum_contribution' => 3754.45,
						  'base_employee_rate'            => 0.0495,
						  'base_employee_maximum_contribution' => 3123.45
			],
			20220101 => [ //2022
						  'maximum_pensionable_earnings'  => 64900, //**NOTE: There is maximum pensionable earnings, and maximum contributory earnings. This must be **pensionable**
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0570,
						  'employee_maximum_contribution' => 3499.80,
			],
			20210101 => [ //2021
						  'maximum_pensionable_earnings'  => 61600,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0545,
						  'employee_maximum_contribution' => 3166.45,
			],
			20200101 => [ //2020
						  'maximum_pensionable_earnings'  => 58700,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0525,
						  'employee_maximum_contribution' => 2898.00,
			],
			20190101 => [ //2019
						  'maximum_pensionable_earnings'  => 57400,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0510,
						  'employee_maximum_contribution' => 2748.90,
			],
			20180101 => [ //2018
						  'maximum_pensionable_earnings'  => 55900,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2593.80,
			],
			20170101 => [ //2017
						  'maximum_pensionable_earnings'  => 55300,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2564.10,
			],
			20160101 => [ //2016
						  'maximum_pensionable_earnings'  => 54900,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2544.30,
			],
			20150101 => [ //2015
						  'maximum_pensionable_earnings'  => 53600,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2479.95,
			],
			20140101 => [ //2014
						  'maximum_pensionable_earnings'  => 52500,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2425.50,
			],
			20130101 => [ //2013
						  'maximum_pensionable_earnings'  => 51100,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2356.20,
			],
			20120101 => [ //2012
						  'maximum_pensionable_earnings'  => 50100,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2306.70,
			],
			20110101 => [ //2011
						  'maximum_pensionable_earnings'  => 48300,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2217.60,
			],
			20100101 => [ //2010
						  'maximum_pensionable_earnings'  => 47200,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2163.15,
			],
			20090101 => [ //2009
						  'maximum_pensionable_earnings'  => 46300,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2118.60,
			],
			20080101 => [ //2008
						  'maximum_pensionable_earnings'  => 44900,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 2049.30,
			],
			20070101 => [ //2007
						  'maximum_pensionable_earnings'  => 43700,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 1989.90,
			],
			20060101 => [ //2006
						  'maximum_pensionable_earnings'  => 42100,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 1910.70,
			],
			20050101 => [ //2005
						  'maximum_pensionable_earnings'  => 41100,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 1861.20,
			],
			20040101 => [ //2004
						  'maximum_pensionable_earnings'  => 40500,
						  'basic_exemption'               => 3500,
						  'employee_rate'                 => 0.0495,
						  'employee_maximum_contribution' => 1831.50,
			],
	];

	/*
		EI settings
	*/
	var $ei_options = [
			20240101 => [ //2024
						  'maximum_insurable_earnings'    => 63200,
						  'employee_rate'                 => 0.0166,
						  'employee_maximum_contribution' => 1049.12,
						  'employer_rate'                 => 1.4,
			],
			20230101 => [ //2023
						  'maximum_insurable_earnings'    => 61500,
						  'employee_rate'                 => 0.0163,
						  'employee_maximum_contribution' => 1002.45,
						  'employer_rate'                 => 1.4,
			],
			20220101 => [ //2022
						  'maximum_insurable_earnings'    => 60300,
						  'employee_rate'                 => 0.0158,
						  'employee_maximum_contribution' => 952.74,
						  'employer_rate'                 => 1.4,
			],
			20210101 => [ //2021
						  'maximum_insurable_earnings'    => 56300,
						  'employee_rate'                 => 0.0158,
						  'employee_maximum_contribution' => 889.54,
						  'employer_rate'                 => 1.4,
			],
			20200101 => [ //2020
						  'maximum_insurable_earnings'    => 54200,
						  'employee_rate'                 => 0.0158,
						  'employee_maximum_contribution' => 856.36,
						  'employer_rate'                 => 1.4,
			],
			20190101 => [ //2019
						  'maximum_insurable_earnings'    => 53100,
						  'employee_rate'                 => 0.0162,
						  'employee_maximum_contribution' => 860.22,
						  'employer_rate'                 => 1.4,
			],
			20180101 => [ //2018
						  'maximum_insurable_earnings'    => 51700,
						  'employee_rate'                 => 0.0166,
						  'employee_maximum_contribution' => 858.22,
						  'employer_rate'                 => 1.4,
			],
			20170101 => [ //2017
						  'maximum_insurable_earnings'    => 51300,
						  'employee_rate'                 => 0.0163,
						  'employee_maximum_contribution' => 836.19,
						  'employer_rate'                 => 1.4,
			],
			20160101 => [ //2016
						  'maximum_insurable_earnings'    => 50800,
						  'employee_rate'                 => 0.0188,
						  'employee_maximum_contribution' => 955.04,
						  'employer_rate'                 => 1.4,
			],
			20150101 => [ //2015
						  'maximum_insurable_earnings'    => 49500,
						  'employee_rate'                 => 0.0188,
						  'employee_maximum_contribution' => 930.60,
						  'employer_rate'                 => 1.4,
			],
			20140101 => [ //2014
						  'maximum_insurable_earnings'    => 48600,
						  'employee_rate'                 => 0.0188,
						  'employee_maximum_contribution' => 913.68,
						  'employer_rate'                 => 1.4,
			],
			20130101 => [ //2013
						  'maximum_insurable_earnings'    => 47400,
						  'employee_rate'                 => 0.0188,
						  'employee_maximum_contribution' => 891.12,
						  'employer_rate'                 => 1.4,
			],
			20120101 => [ //2012
						  'maximum_insurable_earnings'    => 45900,
						  'employee_rate'                 => 0.0183,
						  'employee_maximum_contribution' => 839.97,
						  'employer_rate'                 => 1.4,
			],
			20110101 => [ //2011
						  'maximum_insurable_earnings'    => 44200,
						  'employee_rate'                 => 0.0178,
						  'employee_maximum_contribution' => 786.76,
						  'employer_rate'                 => 1.4,
			],
			20100101 => [ //2010
						  'maximum_insurable_earnings'    => 43200,
						  'employee_rate'                 => 0.0173,
						  'employee_maximum_contribution' => 747.36,
						  'employer_rate'                 => 1.4,
			],
			20090101 => [ //2009
						  'maximum_insurable_earnings'    => 42300,
						  'employee_rate'                 => 0.0173,
						  'employee_maximum_contribution' => 731.79,
						  'employer_rate'                 => 1.4,
			],
			20080101 => [ //2008
						  'maximum_insurable_earnings'    => 41100,
						  'employee_rate'                 => 0.0173,
						  'employee_maximum_contribution' => 711.03,
						  'employer_rate'                 => 1.4,
			],
			20070101 => [ //2007
						  'maximum_insurable_earnings'    => 40000,
						  'employee_rate'                 => 0.0180,
						  'employee_maximum_contribution' => 720.00,
						  'employer_rate'                 => 1.4,
			],
			20060101 => [ //2006
						  'maximum_insurable_earnings'    => 39000,
						  'employee_rate'                 => 0.0187,
						  'employee_maximum_contribution' => 729.30,
						  'employer_rate'                 => 1.4,
			],
			20050101 => [ //2005
						  'maximum_insurable_earnings'    => 39000,
						  'employee_rate'                 => 0.0195,
						  'employee_maximum_contribution' => 760.50,
						  'employer_rate'                 => 1.4,
			],
			20040101 => [ //2004
						  'maximum_insurable_earnings'    => 39900,
						  'employee_rate'                 => 0.0198,
						  'employee_maximum_contribution' => 722.20,
						  'employer_rate'                 => 1.4,
			],
	];

	/*
		Federal employment credit AKA: Canada Employment Amount (Variable: CEA)
	*/
	var $federal_employment_credit_options = [
			20240101 => [ 'credit' => 1433 ],
			20230101 => [ 'credit' => 1368 ],
			20220101 => [ 'credit' => 1287 ],
			20210101 => [ 'credit' => 1257 ],
			20200101 => [ 'credit' => 1245 ],
			20190101 => [ 'credit' => 1222 ],
			20180101 => [ 'credit' => 1195 ],
			20170101 => [ 'credit' => 1178 ],
			20160101 => [ 'credit' => 1161 ],
			20150101 => [ 'credit' => 1146 ],
			20140101 => [ 'credit' => 1127 ],
			20130101 => [ 'credit' => 1117 ],
			20120101 => [ 'credit' => 1095 ],
			20110101 => [ 'credit' => 1065 ],
			20100101 => [ 'credit' => 1051 ],
			20090101 => [ 'credit' => 1044 ],
			20080101 => [ 'credit' => 1019 ],
			20070101 => [ 'credit' => 1000 ],
			20060101 => [ 'credit' => 500 ],
	];

	/*
		Federal Income Tax Rates
	*/
	var $federal_income_tax_rate_options = [
			//Convert this table to match the required formats below:
			20240101 => [
					[ 'income' => 55867, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 111733, 'rate' => 20.5, 'constant' => 3073 ],
					[ 'income' => 173205, 'rate' => 26, 'constant' => 9218 ],
					[ 'income' => 246752, 'rate' => 29, 'constant' => 14414 ],
					[ 'income' => 246752, 'rate' => 33, 'constant' => 24284 ],
			],
			20230101 => [
					[ 'income' => 53359, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 106717, 'rate' => 20.5, 'constant' => 2935 ],
					[ 'income' => 165430, 'rate' => 26, 'constant' => 8804 ],
					[ 'income' => 235675, 'rate' => 29, 'constant' => 13767 ],
					[ 'income' => 235675, 'rate' => 33, 'constant' => 23194 ],
			],
			20220101 => [
					[ 'income' => 50197, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 100393, 'rate' => 20.5, 'constant' => 2761 ],
					[ 'income' => 155625, 'rate' => 26, 'constant' => 8282 ],
					[ 'income' => 221708, 'rate' => 29, 'constant' => 12951 ],
					[ 'income' => 221708, 'rate' => 33, 'constant' => 21819 ],
			],
			20210101 => [
					[ 'income' => 49020, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 98040, 'rate' => 20.5, 'constant' => 2696 ],
					[ 'income' => 151978, 'rate' => 26, 'constant' => 8088 ],
					[ 'income' => 216511, 'rate' => 29, 'constant' => 12648 ],
					[ 'income' => 216511, 'rate' => 33, 'constant' => 21308 ],
			],
			20200101 => [
					[ 'income' => 48535, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 97069, 'rate' => 20.5, 'constant' => 2669 ],
					[ 'income' => 150473, 'rate' => 26, 'constant' => 8008 ],
					[ 'income' => 214368, 'rate' => 29, 'constant' => 12522 ],
					[ 'income' => 214368, 'rate' => 33, 'constant' => 21097 ],
			],
			20190101 => [
					[ 'income' => 47630, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 95259, 'rate' => 20.5, 'constant' => 2620 ],
					[ 'income' => 147667, 'rate' => 26, 'constant' => 7859 ],
					[ 'income' => 210371, 'rate' => 29, 'constant' => 12289 ],
					[ 'income' => 210371, 'rate' => 33, 'constant' => 20704 ],
			],
			20180101 => [
					[ 'income' => 46605, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 93208, 'rate' => 20.5, 'constant' => 2563 ],
					[ 'income' => 144489, 'rate' => 26, 'constant' => 7690 ],
					[ 'income' => 205842, 'rate' => 29, 'constant' => 12024 ],
					[ 'income' => 205842, 'rate' => 33, 'constant' => 20258 ],
			],
			20170101 => [
					[ 'income' => 45916, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 91831, 'rate' => 20.5, 'constant' => 2525 ],
					[ 'income' => 142353, 'rate' => 26, 'constant' => 7576 ],
					[ 'income' => 202800, 'rate' => 29, 'constant' => 11847 ],
					[ 'income' => 202800, 'rate' => 33, 'constant' => 19959 ],
			],
			20160101 => [
					[ 'income' => 45282, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 90563, 'rate' => 20.5, 'constant' => 2491 ],
					[ 'income' => 140388, 'rate' => 26, 'constant' => 7471 ],
					[ 'income' => 200000, 'rate' => 29, 'constant' => 11683 ],
					[ 'income' => 200000, 'rate' => 33, 'constant' => 19683 ],
			],
			20150101 => [
					[ 'income' => 44701, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 89401, 'rate' => 22, 'constant' => 3129 ],
					[ 'income' => 138586, 'rate' => 26, 'constant' => 6705 ],
					[ 'income' => 138586, 'rate' => 29, 'constant' => 10863 ],
			],
			20140101 => [
					[ 'income' => 43953, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 87907, 'rate' => 22, 'constant' => 3077 ],
					[ 'income' => 136270, 'rate' => 26, 'constant' => 6593 ],
					[ 'income' => 136270, 'rate' => 29, 'constant' => 10681 ],
			],
			20130101 => [
					[ 'income' => 43561, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 87123, 'rate' => 22, 'constant' => 3049 ],
					[ 'income' => 135054, 'rate' => 26, 'constant' => 6534 ],
					[ 'income' => 135054, 'rate' => 29, 'constant' => 10586 ],
			],
			20120101 => [
					[ 'income' => 42707, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 85414, 'rate' => 22, 'constant' => 2989 ],
					[ 'income' => 132406, 'rate' => 26, 'constant' => 6406 ],
					[ 'income' => 132406, 'rate' => 29, 'constant' => 10378 ],
			],
			20110101 => [
					[ 'income' => 41544, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 83088, 'rate' => 22, 'constant' => 2908 ],
					[ 'income' => 128800, 'rate' => 26, 'constant' => 6232 ],
					[ 'income' => 128800, 'rate' => 29, 'constant' => 10096 ],
			],
			20100101 => [
					[ 'income' => 40970, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 81941, 'rate' => 22, 'constant' => 2868 ],
					[ 'income' => 127021, 'rate' => 26, 'constant' => 6146 ],
					[ 'income' => 127021, 'rate' => 29, 'constant' => 9956 ],
			],
			20090401 => [
					[ 'income' => 41200, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 82399, 'rate' => 22, 'constant' => 2884 ],
					[ 'income' => 126264, 'rate' => 26, 'constant' => 6180 ],
					[ 'income' => 126264, 'rate' => 29, 'constant' => 9968 ],
			],
			20090101 => [
					[ 'income' => 38832, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 77664, 'rate' => 22, 'constant' => 2718 ],
					[ 'income' => 126264, 'rate' => 26, 'constant' => 5825 ],
					[ 'income' => 126264, 'rate' => 29, 'constant' => 9613 ],
			],
			20080101 => [
					[ 'income' => 37885, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 75769, 'rate' => 22, 'constant' => 2652 ],
					[ 'income' => 123184, 'rate' => 26, 'constant' => 5683 ],
					[ 'income' => 123184, 'rate' => 29, 'constant' => 9378 ],
			],
			20070101 => [
					[ 'income' => 37178, 'rate' => 15.5, 'constant' => 0 ],
					[ 'income' => 74357, 'rate' => 22, 'constant' => 2417 ],
					[ 'income' => 120887, 'rate' => 26, 'constant' => 5391 ],
					[ 'income' => 120887, 'rate' => 29, 'constant' => 9017 ],
			],
			20060701 => [
					[ 'income' => 36378, 'rate' => 15.5, 'constant' => 0 ],
					[ 'income' => 72756, 'rate' => 22, 'constant' => 2365 ],
					[ 'income' => 118285, 'rate' => 26, 'constant' => 5275 ],
					[ 'income' => 118285, 'rate' => 29, 'constant' => 8823 ],
			],
			20060101 => [
					[ 'income' => 36378, 'rate' => 15, 'constant' => 0 ],
					[ 'income' => 72756, 'rate' => 22, 'constant' => 2546 ],
					[ 'income' => 118285, 'rate' => 26, 'constant' => 5457 ],
					[ 'income' => 118285, 'rate' => 29, 'constant' => 9005 ],
			],
			20050101 => [
					[ 'income' => 35595, 'rate' => 16, 'constant' => 0 ],
					[ 'income' => 71190, 'rate' => 22, 'constant' => 2136 ],
					[ 'income' => 115739, 'rate' => 26, 'constant' => 4983 ],
					[ 'income' => 115739, 'rate' => 29, 'constant' => 8455 ],
			],
			20040101 => [
					[ 'income' => 35000, 'rate' => 16, 'constant' => 0 ],
					[ 'income' => 70000, 'rate' => 22, 'constant' => 2100 ],
					[ 'income' => 113804, 'rate' => 26, 'constant' => 4900 ],
					[ 'income' => 113804, 'rate' => 29, 'constant' => 8314 ],
			],
			20030101 => [
					[ 'income' => 35000, 'rate' => 16, 'constant' => 0 ],
					[ 'income' => 70000, 'rate' => 22, 'constant' => 2100 ],
					[ 'income' => 113804, 'rate' => 26, 'constant' => 4900 ],
					[ 'income' => 113804, 'rate' => 29, 'constant' => 8314 ],
			],
			20020101 => [
					[ 'income' => 35000, 'rate' => 16, 'constant' => 0 ],
					[ 'income' => 70000, 'rate' => 22, 'constant' => 2100 ],
					[ 'income' => 113804, 'rate' => 26, 'constant' => 4900 ],
					[ 'income' => 113804, 'rate' => 29, 'constant' => 8314 ],
			],
			20010101 => [
					[ 'income' => 35000, 'rate' => 16, 'constant' => 0 ],
					[ 'income' => 70000, 'rate' => 22, 'constant' => 2100 ],
					[ 'income' => 113804, 'rate' => 26, 'constant' => 4900 ],
					[ 'income' => 113804, 'rate' => 29, 'constant' => 8314 ],
			],
	];

	function __construct() {
		global $db;

		$this->db = $db;

		return true;
	}

	/*
		Claim Code Functions
	*/
	function getBasicClaimCodeData( $date ) {
		foreach ( $this->basic_claim_code_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return false;
	}

	function getBasicFederalClaimCodeAmount( $date = false ) {
		if ( $date == '' ) {
			$date = $this->getDate();
		}

		$data = $this->getBasicClaimCodeData( $date );

		if ( isset( $data['CA'] ) ) {
			//After 01-Jan-2020, BPAF variable was introduced, so see if the data is returned as an array or not.
			//  Default to use the 'max' value unless otherwise specified as per the CPA formulas.
			if ( is_array( $data['CA'] ) ) {
				$retval = $data['CA']['max'];
			} else {
				$retval = $data['CA'];
			}

			return $retval;
		}

		return false;
	}

	function getBasicProvinceClaimCodeAmount( $date = false ) {
		if ( $date == '' ) {
			$date = $this->getDate();
		}

		$data = $this->getBasicClaimCodeData( $date );

		if ( isset( $data[$this->getProvince()] ) ) {
			//After 01-Jul-2020, BPAYT variable was introduced, so see if the data is returned as an array or not.
			//  Default to use the 'max' value unless otherwise specified as per the CPA formulas.
			if ( is_array( $data[$this->getProvince()] ) ) {
				$retval = $data[$this->getProvince()]['max'];
			} else {
				$retval = $data[$this->getProvince()];
			}

			return $retval;
		}

		return false;
	}

	/*
		Provincial tax/surtax reduction functions
	*/
	function getProvincialTaxReductionData( $date ) {
		if ( isset( $this->provincial_tax_reduction_options ) ) {
			foreach ( $this->provincial_tax_reduction_options as $effective_date => $data ) {
				if ( $date >= $effective_date ) {
					return $data;
				}
			}
		}

		return false;
	}

	function getProvincialSurTaxData( $date ) {
		if ( isset( $this->provincial_surtax_options ) ) {
			foreach ( $this->provincial_surtax_options as $effective_date => $data ) {
				if ( $date >= $effective_date ) {
					return $data;
				}
			}
		}

		return false;
	}

	/*
		Federal Employment Credit functions
	*/
	function getFederalEmploymentCreditData( $date ) {
		foreach ( $this->federal_employment_credit_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return false;
	}

	function getFederalEmploymentCreditAmount() {
		$data = $this->getFederalEmploymentCreditData( $this->getDate() );

		Debug::text( 'Date: ' . $this->getDate() . ' Credit: ' . $data['credit'], __FILE__, __LINE__, __METHOD__, 10 );

		return $data['credit'];
	}

	/*
		CPP functions
	*/
	function getCPPData( $date ) {
		foreach ( $this->cpp_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return false;
	}

	function getCPPMaximumEarnings() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['maximum_pensionable_earnings'] ?? 0;
	}

	function getCPPBasicExemption() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['basic_exemption'] ?? 0;
	}

	function getCPPEmployeeRate() {
		$data = $this->getCPPData( $this->getDate() );

		Debug::text( 'Date: ' . $this->getDate() . ' Rate: ' . $data['employee_rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return $data['employee_rate'] ?? 0;
	}

	function getCPPEmployeeMaximumContribution() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['employee_maximum_contribution'] ?? 0;
	}

	function getCPPBaseEmployeeRate() {
		$data = $this->getCPPData( $this->getDate() );

		if ( isset( $data['base_employee_rate'] ) ) {
			$retval = $data['base_employee_rate'];
	 	} else {
			$retval = $this->getCPPEmployeeRate();
		}

		Debug::text( 'Date: ' . $this->getDate() . ' Rate: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getCPPBaseEmployeeMaximumContribution() {
		$data = $this->getCPPData( $this->getDate() );

		if ( isset( $data['base_employee_maximum_contribution'] ) ) {
			$retval = $data['base_employee_maximum_contribution'];
		} else {
			$retval = $this->getCPPEmployeeMaximumContribution();
		}

		Debug::text( 'Date: ' . $this->getDate() . ' Maximum Contribution: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	function getCPP2EmployeeRate() {
		$data = $this->getCPPData( $this->getDate() );

		if ( isset( $data['second_employee_rate'] ) ) {
			$retval = $data['second_employee_rate'];
		} else {
			$retval = 0;
		}

		Debug::text( 'Date: ' . $this->getDate() . ' CPP2 Rate: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getCPP2EmployeeMaximumContribution() {
		$data = $this->getCPPData( $this->getDate() );

		if ( isset( $data['second_employee_maximum_contribution'] ) ) {
			$retval = $data['second_employee_maximum_contribution'];
		} else {
			$retval = 0;
		}

		Debug::text( 'Date: ' . $this->getDate() . ' CPP2 Maximum Contribution: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	function getCPP2EmployeeMaximumPensionableEarnings() {
		$data = $this->getCPPData( $this->getDate() );

		if ( isset( $data['second_employee_maximum_pensionable_earnings'] ) ) {
			$retval = $data['second_employee_maximum_pensionable_earnings'];
		} else {
			$retval = 0;
		}

		Debug::text( 'Date: ' . $this->getDate() . ' CPP2 Maximum Pensionable Earnings: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	/*
		EI functions
	*/
	function getEIData( $date ) {
		foreach ( $this->ei_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return false;
	}

	function getEIMaximumEarnings() {
		$data = $this->getEIData( $this->getDate() );

		return $data['maximum_insurable_earnings'] ?? 0;
	}

	function getEIEmployeeRate() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employee_rate'] ?? 0;
	}

	function getEIEmployeeMaximumContribution() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employee_maximum_contribution'] ?? 0;
	}

	function getEIEmployerRate() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employer_rate'] ?? 0;
	}

	function getData() {
//		global $cache;
//		$country = $this->getCountry();
//		$province = $this->getProvince();
		$epoch = $this->getDate();

		if ( $epoch == null || $epoch == '' ) {
			$epoch = $this->getISODate( TTDate::getTime() );
		}

		//Debug::text( 'bUsing (' . $province . ') values from: ' . TTDate::getDate( 'DATE+TIME', $this->getDateEpoch( $epoch ) ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->income_tax_rates = [];
		if ( isset( $this->federal_income_tax_rate_options ) && count( $this->federal_income_tax_rate_options ) > 0 ) {
			foreach ( $this->getDataFromRateArray( $epoch, $this->federal_income_tax_rate_options ) as $effective_date => $data ) {
				$this->income_tax_rates['federal'][] = [ 'income' => $data['income'], 'rate' => ( $data['rate'] / 100 ), 'constant' => $data['constant'] ];
			}
		}

		if ( isset( $this->provincial_income_tax_rate_options ) && count( $this->provincial_income_tax_rate_options ) > 0 ) {
			foreach ( $this->getDataFromRateArray( $epoch, $this->provincial_income_tax_rate_options ) as $effective_date => $data ) {
				$this->income_tax_rates['provincial'][] = [ 'income' => $data['income'], 'rate' => ( $data['rate'] / 100 ), 'constant' => $data['constant'] ];
			}
		}

		return $this;
	}

	private function getRateArray( $income, $type ) {
		Debug::text( 'Calculating ' . $type . ' Taxes on: $' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		$blank_arr = [ 'rate' => null, 'constant' => null ];

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
				//Debug::text( 'Value: ' . $value . ' Rate: ' . $values['rate'] . ' Constant: ' . $values['constant'] . ' Previous Value: ' . $prev_value, __FILE__, __LINE__, __METHOD__, 10 );
				return $this->income_tax_rates[$type][$key];
			} else if ( $i == $total_rates ) {
				//Debug::text( 'Last Value: ' . $value . ' Rate: ' . $values['rate'] . ' Constant: ' . $values['constant'] . ' Previous Value: ' . $prev_value, __FILE__, __LINE__, __METHOD__, 10 );
				return $this->income_tax_rates[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return $blank_arr;
	}

	function getFederalLowestRate() {
		$arr = $this->getRateArray( 1, 'federal' );
		Debug::text( 'Federal Lowest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
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

	function getFederalConstant( $income ) {
		$arr = $this->getRateArray( $income, 'federal' );
		Debug::text( 'Federal Constant: ' . $arr['constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['constant'];
	}

	function getProvincialLowestRate() {
		$arr = $this->getRateArray( 1, 'provincial' );
		Debug::text( 'Provincial Lowest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getProvincialHighestRate() {
		$arr = $this->getRateArray( 999999999, 'provincial' );
		Debug::text( 'Provincial Highest Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getProvincialRate( $income ) {
		$arr = $this->getRateArray( $income, 'provincial' );
		Debug::text( 'Provincial Rate: ' . $arr['rate'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['rate'];
	}

	function getProvincialConstant( $income ) {
		$arr = $this->getRateArray( $income, 'provincial' );
		Debug::text( 'Provincial Constant: ' . $arr['constant'], __FILE__, __LINE__, __METHOD__, 10 );

		return (float)$arr['constant'];
	}
}

?>
