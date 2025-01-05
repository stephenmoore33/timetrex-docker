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
 //
// See https://github.com/giftcards/FixedWidth or https://packagist.org/packages/devvoh/fixedwidth for handling fixed with files.
//
// https://support.na.sage.com/selfservice/viewContent.do?externalId=32877&sliceId=1
//  Florida and California have their own XML formats. Ohio supports ICESA and XML. So maybe start with ICESA?
//
// State Supplemental Information:
//  https://support.na.sage.com/selfservice/viewdocument.do?externalId=32880&sliceId=1&cmd=&ViewedDocsListHelper=com.kanisa.apps.common.BaseViewedDocsListHelperImpl&noCount=true
//
// SSA MMREF-1 format with unemployment info: (Record: RE, RS) https://edd.ca.gov/siteassets/files/pdf_pub_ctr/de8300.pdf
// ICESA Standard format for all states: https://esdorchardstorage.blob.core.windows.net/esdwa/Default/ESDWAGOV/employer-Taxes/EAMS-bulk-filing-specifications.pdf
// ICESA is now called NASWA
//
// Use Texas State "QuickFile" program to parse ICESA and MMREF files for testing: https://www.twc.texas.gov/quickfile-wage-reporting-program
//   This also has some specifics for the file formats: https://www.highlinecorp.com/Wiki/Wiki.jsp?page=Tax%20Reporting%20-%20ME#section-Tax+Reporting+-+ME-RecordNameCodeATransmitterRecord
//
//ICESA File Format Requirements
//The following are the ICESA fle format requirements:
//• IBM compatible.
//• Must be recorded in American Standard Code for Information Interchange (ASCII) format.
//• Uncompressed mode.
//• Data must be written in UPPERCASE letters only.
//• Filename: ICESA.
//• 275 position record length.
//
//
// There is also the FSET/TIGERS standard that uses XML. Apparently these states use it: AZ, CA, CO, CT, FL, GA, HI, IA, IL, IN, LA, MI, MS, MT, NY, OH, OR, PA, and WI.
//
*/

include_once( 'US.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_State_UI extends GovernmentForms_US {
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

	function _getStateNumericCode( $state ) {
		$map = [
				'AL' => '01',
				'AK' => '02',
				'AZ' => '04',
				'AR' => '05',
				'CA' => '06',
				'CO' => '08',
				'CT' => '09',
				'DE' => '10',
				'DC' => '11',
				'FL' => '12',
				'GA' => '13',
				'HI' => '15',
				'ID' => '16',
				'IL' => '17',
				'IN' => '18',
				'IA' => '19',
				'KS' => '20',
				'KY' => '21',
				'LA' => '22',
				'ME' => '23',
				'MD' => '24',
				'MA' => '25',
				'MI' => '26',
				'MN' => '27',
				'MS' => '28',
				'MO' => '29',
				'MT' => '30',
				'NE' => '31',
				'NV' => '32',
				'NH' => '33',
				'NM' => '34',
				'NJ' => '35',
				'NY' => '36',
				'NC' => '37',
				'ND' => '38',
				'OH' => '39',
				'OK' => '40',
				'OR' => '41',
				'PA' => '42',
				'RI' => '44',
				'SC' => '45',
				'SD' => '46',
				'TN' => '47',
				'TX' => '48',
				'UT' => '49',
				'VT' => '50',
				'VA' => '51',
				'WA' => '53',
				'WV' => '54',
				'WI' => '55',
				'WY' => '56',
		];

		if ( isset( $map[strtoupper( $state )] ) ) {
			return $map[strtoupper( $state )];
		}

		return false;
	}

	function _getTexasCountyCodes( $name ) {
		$name = strtoupper( trim( $name ) );

		//List from: https://www.twc.texas.gov/files/businesses/icesa-tape-format-with-c3-c4-mwr-allocation-data-information-twc.pdf -- Last page.
		$map = [
				'ANDERSON' => '001',
				'ANDREWS' => '003',
				'ANGELINA' => '005',
				'ARANSAS' => '007',
				'ARCHER' => '009',
				'ARMSTRONG' => '011',
				'ATASCOSA' => '013',
				'AUSTIN' => '015',
				'BAILEY' => '017',
				'BANDERA' => '019',
				'BASTROP' => '021',
				'BAYLOR' => '023',
				'BEE' => '025',
				'BELL' => '027',
				'BEXAR' => '029',
				'BLANCO' => '031',
				'BORDEN' => '033',
				'BOSQUE' => '035',
				'BOWIE' => '037',
				'BRAZORIA' => '039',
				'BRAZOS' => '041',
				'BREWSTER' => '043',
				'BRISCOE' => '045',
				'BROOKS' => '047',
				'BROWN' => '049',
				'BURLESON' => '051',
				'BURNET' => '053',
				'CALDWELL' => '055',
				'CALHOUN' => '057',
				'CALLAHAN' => '059',
				'CAMERON' => '061',
				'CAMP' => '063',
				'CARSON' => '065',
				'CASS' => '067',
				'CASTRO' => '069',
				'CHAMBERS' => '071',
				'CHEROKEE' => '073',
				'CHILDRESS' => '075',
				'CLAY' => '077',
				'COCHRAN' => '079',
				'COKE' => '081',
				'COLEMAN' => '083',
				'COLLIN' => '085',
				'COLLINGSWORTH' => '087',
				'COLORADO' => '089',
				'COMAL' => '091',
				'COMANCHE' => '093',
				'CONCHO' => '095',
				'COOKE' => '097',
				'CORYELL' => '099',
				'COTTLE' => '101',
				'CRANE' => '103',
				'CROCKETT' => '105',
				'CROSBY' => '107',
				'CULBERSON' => '109',
				'DALLAM' => '111',
				'DALLAS' => '113',
				'DAWSON' => '115',
				'DEAF SMITH' => '117',
				'DELTA' => '119',
				'DENTON' => '121',
				'DEWITT' => '123',
				'DICKENS' => '125',
				'DIMMIT' => '127',
				'DONLEY' => '129',
				'DUVAL' => '131',
				'EASTLAND' => '133',
				'ECTOR' => '135',
				'EDWARDS' => '137',
				'ELLIS' => '139',
				'EL PASO' => '141',
				'ERATH' => '143',
				'FALLS' => '145',
				'FANNIN' => '147',
				'FAYETTE' => '149',
				'FISHER' => '151',
				'FLOYD' => '153',
				'FOARD' => '155',
				'FORT BEND' => '157',
				'FRANKLIN' => '159',
				'FREESTONE' => '161',
				'FRIO' => '163',
				'GAINES' => '165',
				'GALVESTON' => '167',
				'GARZA' => '169',
				'GILLESPIE' => '171',
				'GLASSCOCK' => '173',
				'GOLIAD' => '175',
				'GONZALES' => '177',
				'GRAY' => '179',
				'GRAYSON' => '181',
				'GREGG' => '183',
				'GRIMES' => '185',
				'GUADALUPE' => '187',
				'HALE' => '189',
				'HALL' => '191',
				'HAMILTON' => '193',
				'HANSFORD' => '195',
				'HARDEMAN' => '197',
				'HARDIN' => '199',
				'HARRIS' => '201',
				'HARRISON' => '203',
				'HARTLEY' => '205',
				'HASKELL' => '207',
				'HAYS' => '209',
				'HEMPHILL' => '211',
				'HENDERSON' => '213',
				'HIDALGO' => '215',
				'HILL' => '217',
				'HOCKLEY' => '219',
				'HOOD' => '221',
				'HOPKINS' => '223',
				'HOUSTON' => '225',
				'HOWARD' => '227',
				'HUDSPETH' => '229',
				'HUNT' => '231',
				'HUTCHINSON' => '233',
				'IRION' => '235',
				'JACK' => '237',
				'JACKSON' => '239',
				'JASPER' => '241',
				'JEFF DAVIS' => '243',
				'JEFFERSON' => '245',
				'JIM HOGG' => '247',
				'JIM WELLS' => '249',
				'JOHNSON' => '251',
				'JONES' => '253',
				'KARNES' => '255',
				'KAUFMAN' => '257',
				'KENDALL' => '259',
				'KENEDY' => '261',
				'KENT' => '263',
				'KERR' => '265',
				'KIMBLE' => '267',
				'KING' => '269',
				'KINNEY' => '271',
				'KLEBERG' => '273',
				'KNOX' => '275',
				'LAMAR' => '277',
				'LAMB' => '279',
				'LAMPASAS' => '281',
				'LA SALLE' => '283',
				'LAVACA' => '285',
				'LEE' => '287',
				'LEON' => '289',
				'LIBERTY' => '291',
				'LIMESTONE' => '293',
				'LIPSCOMB' => '295',
				'LIVE OAK' => '297',
				'LLANO' => '299',
				'LOVING' => '301',
				'LUBBOCK' => '303',
				'LYNN' => '305',
				'MCCULLOCH' => '307',
				'MCLENNAN' => '309',
				'MCMULLEN' => '311',
				'MADISON' => '313',
				'MARION' => '315',
				'MARTIN' => '317',
				'MASON' => '319',
				'MATAGORDA' => '321',
				'MAVERICK' => '323',
				'MEDINA' => '325',
				'MENARD' => '327',
				'MIDLAND' => '329',
				'MILAM' => '331',
				'MILLS' => '333',
				'MITCHELL' => '335',
				'MONTAGUE' => '337',
				'MONTGOMERY' => '339',
				'MOORE' => '341',
				'MORRIS' => '343',
				'MOTLEY' => '345',
				'NACOGDOCHES' => '347',
				'NAVARRO' => '349',
				'NEWTON' => '351',
				'NOLAN' => '353',
				'NUECES' => '355',
				'OCHILTREE' => '357',
				'OLDHAM' => '359',
				'ORANGE' => '361',
				'PALO PINTO' => '363',
				'PANOLA' => '365',
				'PARKER' => '367',
				'PARMER' => '369',
				'PECOS' => '371',
				'POLK' => '373',
				'POTTER' => '375',
				'PRESIDIO' => '377',
				'RAINS' => '379',
				'RANDALL' => '381',
				'REAGAN' => '383',
				'REAL' => '385',
				'RED RIVER' => '387',
				'REEVES' => '389',
				'REFUGIO' => '391',
				'ROBERTS' => '393',
				'ROBERTSON' => '395',
				'ROCKWALL' => '397',
				'RUNNELS' => '399',
				'RUSK' => '401',
				'SABINE' => '403',
				'SAN AUGUSTINE' => '405',
				'SAN JACINTO' => '407',
				'SAN PATRICIO' => '409',
				'SAN SABA' => '411',
				'SCHLEICHER' => '413',
				'SCURRY' => '415',
				'SHACKELFORD' => '417',
				'SHELBY' => '419',
				'SHERMAN' => '421',
				'SMITH' => '423',
				'SOMERVELL' => '425',
				'STARR' => '427',
				'STEPHENS' => '429',
				'STERLING' => '431',
				'STONEWALL' => '433',
				'SUTTON' => '435',
				'SWISHER' => '437',
				'TARRANT' => '439',
				'TAYLOR' => '441',
				'TERRELL' => '443',
				'TERRY' => '445',
				'THROCKMORTON' => '447',
				'TITUS' => '449',
				'TOM GREEN' => '451',
				'TRAVIS' => '453',
				'TRINITY' => '455',
				'TYLER' => '457',
				'UPSHUR' => '459',
				'UPTON' => '461',
				'UVALDE' => '463',
				'VAL VERDE' => '465',
				'VAN ZANDT' => '467',
				'VICTORIA' => '469',
				'WALKER' => '471',
				'WALLER' => '473',
				'WARD' => '475',
				'WASHINGTON' => '477',
				'WEBB' => '479',
				'WHARTON' => '481',
				'WHEELER' => '483',
				'WICHITA' => '485',
				'WILBARGER' => '487',
				'WILLACY' => '489',
				'WILLIAMSON' => '491',
				'WILSON' => '493',
				'WINKLER' => '495',
				'WISE' => '497',
				'WOOD' => '499',
				'YOAKUM' => '501',
				'YOUNG' => '503',
				'ZAPATA' => '505',
				'ZAVALA' => '507',
		];

		if ( isset( $map[$name] ) ) {
			return $map[$name];
		}

		return false;
	}

	function formatDateStamp( $epoch ) {
		if ( !empty( $epoch ) ) {
			return date( 'm', $epoch ) . date( 'd', $epoch ) . date( 'Y', $epoch );
		}

		return null;
	}

	function formatMonthAndYear( $epoch ) {
		if ( !empty( $epoch ) ) {
			return date( 'm', $epoch ) . date( 'Y', $epoch );
		}

		return null;
	}

	function _compileRA() { //RA (Submitter) Record
		$line[] = 'RA';                                                                                                                                                        //RA Record

		Debug::Text( 'RA Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'ne': //https://dol.nebraska.gov/webdocs/Resources/Items/512_Byte_File_Specifications_Document-Single_Employer.pdf
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(3-11)[9] EIN
				$line[] = $this->padRecord( '', 20, 'AN' );                                                                                                                     //(12-31)Blank
				$line[] = $this->padRecord( '', 185, 'AN' );                                                                                                                    //(32-216)Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->trade_name ), 57, 'AN' );                                                                       //(217-273)Submitter name/organization.
				$line[] = $this->padRecord( '', 42, 'AN' );                                                                                                                     //(274-315)Blank
				$line[] = $this->padRecord( '', 197, 'AN' );                                                                                                                    //(316-512)Blank
				break;
			default:
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(3-11)[9] EIN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->efile_user_id ), 8, 'AN' );                                                                     //(12-19)User ID
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //(20-23)Software Vendor code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(24-28)Blank
				$line[] = $this->padRecord( 0, 1, 'AN' );                                                                                                               		//(29)Resub
				$line[] = $this->padRecord( '', 6, 'AN' );                                                                                                                      //(30-35)Resub WFID
				$line[] = $this->padRecord( '98', 2, 'AN' );                                                                                                                    //(36-37)Software Code
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                                                                                      //(38-94)[57]Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' );                                                                 //(95-116)[22]Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );                                                                 //(117-138)[22]Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                                                                     //(139-160)Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //(161-162)Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //(163-167)Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //(168-171)Company Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(172-176)Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //(177-199)Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //(200-214)Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //(215-216)Company Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->trade_name ), 57, 'AN' );                                                                       //(217-273)Submitter name/organization.
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( ( $this->company_address2 != '' ) ? $this->company_address2 : $this->company_address1 ), 22, 'AN' );   //(274-295)Submitter Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );                                                                 //(296-317)Submitter Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                                                                     //(318-339)Submitter City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //(340-341)Submitter State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //(342-346)Submitter Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //(347-350)Submitter Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(351-355)Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //(356-378)Submitter Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //(379-393)Submitter Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //(394-395)Submitter Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );                                                                     //(396-422)Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 15, 'AN' );                                                                         //(423-437)Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 5, 'AN' );                                                                      //(438-442)Contact Phone Ext
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //(443-445)Blank
				$line[] = $this->padRecord( $this->contact_email, 40, 'AN' );                                                                                                   //(446-485)Contact Email
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //(486-488)Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_fax ), 10, 'AN' );                                                                           //(489-498)Contact Fax
				$line[] = $this->padRecord( '', 1, 'AN' );                                                                                                                      //(499)Blank
				$line[] = $this->padRecord( 'L', 1, 'AN' );                                                                                                                     //(500)PreParers Code
				$line[] = $this->padRecord( '', 12, 'AN' );                                                                                                                     //(501-512)Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RA Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RA Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRE() {  //RE (Employer) Record
		$line[] = 'RE';                                                                                 //(1-2) RE Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'ne': //https://dol.nebraska.gov/webdocs/Resources/Items/512_Byte_File_Specifications_Document-Single_Employer.pdf
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(3-6) Tax Year
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(7) Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(8-16 ) EIN
				$line[] = $this->padRecord( '', 23, 'AN' );                                                     //(17-39) Blank
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                      //(40-96) Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' ); //(97-118) Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' ); //(119-140) Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );     //(141-162) Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(163-164) Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(165-169) Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(170-173) Company Zip Code Extension
				$line[] = $this->padRecord( '', 48, 'AN' );                  									//(174-221)[48] Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year ), 2, 'N' ); 	        //(222-223)[2]: Reporting Period (Last month of calendar quater)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->stripSpaces( $this->state_primary_id ) ), 10, 'N' ); //(224-233)[10]: Empoyer Account Number (numeric 10-digits)
				$line[] = $this->padRecord( '', 279, 'AN' );                  									//(234-512) Blank
				break;
			case 'la': //https://www.laworks.net/downloads/ui/wageuploadinstructions.pdf
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(3-6) Tax Year
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(7) Agent Indicator
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(8-16 ) EIN
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(17-25) Agent for EIN
				$line[] = $this->padRecord( '0', 1, 'N' );                                                      //(26) Terminating Business
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(27-30) Establishment Number
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(31-39) Other EIN
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                      //(40-96) Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' ); //(97-118) Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' ); //(119-140) Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );     //(141-162) Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(163-164) Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(165-169) Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(170-173) Company Zip Code Extension
				$line[] = $this->padRecord( '', 1, 'AN' );                  									//(174) Kind of Employer
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(175-178) Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                     //(179-201) Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                     //(202-216) Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                      //(217-218) Country, fill with blanks if its the US
				$line[] = $this->padRecord( 'R', 1, 'AN' );                                                     //(219) Employment Code - 941 Form
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(220) Tax Jurisdiction

				$line[] = $this->padRecord( 'B', 1, 'AN' );                                                                          //(221) Tax Type (always "B")
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' );                             //(222-223)[2]: State Code Identifier
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year . $this->year ), 6, 'N' );                  //(224-229)[6]: Reporting Period
				$line[] = $this->padRecord( 0, 2, 'N' );                                                                             //(230-231)[2]: Blocking Factor
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                           //(232-234)[3]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->stripSpaces( $this->state_primary_id ) ), 12, 'AN' );     //(235-246)[12]: State UI Empoyer Account Number (numeric 7-digits) - Left justify and fill with blanks.
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                           //(247-239)[3]: Blank
				$line[] = $this->padRecord( (int)$this->is_multiple_county_industry, 1, 'N' );                                      //(250-250)[1]: Multiple County Industry - REQUIRED – IF EMPLOYING ENTITY IS CURRENTLY A MULTIPLE WORKSITE REPORTER AND HAS CHOSEN TO SUBMIT FORM BLS 3020 (MULTIPLE WORKSITE REPORT) AS A FIXED FILE FORMAT VIA ELECTRONIC UPLOAD. ENTER “1” IF THIS FIRM HAS EMPLOYEES IN MORE THAN ONE COUNTY/INDUSTRY INCLUDED IN THIS REPORT; OTHERWISE, ENTER “0”.
				$line[] = $this->padRecord( (int)$this->is_multiple_worksite_location, 1, 'N' );                                     //(251-251)[1]: Multiple Worksite Location - REQUIRED – IF EMPLOYING ENTITY IS CURRENTLY A MULTIPLE WORKSITE REPORTER AND HAS CHOSEN TO SUBMIT FORM BLS 3020 (MULTIPLE WORKSITE REPORT) AS A FIXED FILE FORMAT VIA ELECTRONIC UPLOAD.  ENTER “1” IF THIS FIRM HAS EMPLOYEES AT MORE THAN ONE COUNTY INCLUDED IN THIS REPORT;OTHERWISE, ENTER “0”
				$line[] = $this->padRecord( (int)$this->is_multiple_worksite_indicator, 1, 'N' );                                    //(252-252)[1]: Multiple Worksite Indicator REQUIRED – ENTER “1” IF THIS FIRM INCLUDING MULTIPLE WORKSITE DATA ON FIXED FILE FORMAT IN LIEU OF FORM BLS 3020; OTHERWISE, ENTER “0”
				$line[] = $this->padRecord( 0, 1, 'N' );                                                                             //(253-253)[1]: OPTIONAL – ENTER “1” IF THIS FIRM PARTICIPATES IN ELECTRONIC FUNDS TRANSFER OF QUARTERLY UI PAYROLL TAXES; OTHERWISE, ENTER “0”
				$line[] = $this->padRecord( 'N', 1, 'AN' );                                                                          //(254-254)[1]: REQUIRED – IF ANY OF YOUR EMPLOYEES REPORT WAGES TO LOUISIANA AND TO OTHER STATES; ENTER A “Y” IF YES; ENTER A “N”
				$line[] = $this->padRecord( '', 258, 'AN' );                                                                         //(255-512) Blank
				break;
			default:
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(3-6) Tax Year
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(7) Agent Indicator
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(8-16 ) EIN
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(17-25) Agent for EIN
				$line[] = $this->padRecord( '0', 1, 'N' );                                                      //(26) Terminating Business
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(27-30) Establishment Number
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(31-39) Other EIN
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                      //(40-96) Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' ); //(97-118) Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' ); //(119-140) Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );     //(141-162) Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(163-164) Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(165-169) Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(170-173) Company Zip Code Extension
				$line[] = $this->padRecord( '', 1, 'AN' );                  									//(174) Kind of Employer
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(175-178) Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                     //(179-201) Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                     //(202-216) Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                      //(217-218) Country, fill with blanks if its the US
				$line[] = $this->padRecord( 'R', 1, 'AN' );                                                     //(219) Employment Code - 941 Form
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(220) Tax Jurisdiction

				$line[] = $this->padRecord( '', 1, 'N' );                                                        //(221) Third Party Sick Pay
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );      //(222-248) Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 15, 'AN' );          //(249-263) Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 5, 'AN' );       //(264-268) Contact Phone Ext
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_fax ), 10, 'AN' );            //(269-278) Contact Fax
				$line[] = $this->padRecord( $this->contact_email, 40, 'AN' );                                    //(279-318) Contact Email
				$line[] = $this->padRecord( '', 194, 'AN' );                                                     //(319-512) Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RE Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RE Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRS() { //RS (State) Record
		if ( $this->efile_state == '' ) { //Federal filing does not need any RS record at all.
			return false;
		}

		//Skip records without taxable wages. LA specifically requires this.
		if ( empty( (float)$this->subject_wages ) && empty( (float)$this->taxable_wages ) && empty( (float)$this->tax_withheld ) ) {
			return false;
		}

		$optional_code = ''; //Different for most states.
		$state_other_data = '';

		$line[] = 'RS';                                                                                    //(1-2)[2]: RS Record

		Debug::Text( 'RS Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );

		//Pre-Process for each state.
		switch ( strtolower( $this->efile_state ) ) {
			case 'nc':
				$optional_code = 'N'; //N=Not Seasonal EE, S=Seasonal SS
				$state_other_data = 999996; //Remitter Number. Use 999996 unless otherwise specified.
				break;
			case 'la':
				$this->subject_wages = $this->MoneyFormat( $this->RoundNearestDollar( $this->subject_wages ) ); //Must keep the trailing cents. Decimal will be removed later.
				break;
		}

		switch ( strtolower( $this->efile_state ) ) {
			case 'ne':
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		   //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                        //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );          //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );         //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );           //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 128, 'AN' );                                                       //(69-196)[128]: Blank

				//Unemployment reporting
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' );  //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 11, 'N' );               //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( $this->stripNonNumeric( floor( $this->subject_units ) ), 4, 'N' );     //(214-217)[4]: Total hours paid to the employee. Include regular, vacation, and sick hours paid. Up to four digits between 0-9999. Round down to nearest whole number.
				$line[] = $this->padRecord( '', 30, 'AN' );                                                         //(218-247)[30]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 10, 'N' );    //(248-257)[10]: State Employer Account Number
				$line[] = $this->padRecord( '', 125, 'AN' );                                                         //(258-382)[125]: Primary Job Title of Employee
				$line[] = $this->padRecord( '', 130, 'AN' );                                                         //(383-512)[130]: Blank
				break;
			case 'la': //https://www.laworks.net/downloads/ui/wageuploadinstructions.pdf
				//One "RS" record is required for each employee for whom wages were paid in Louisiana during the report quarter

				//Withholding Number for State format is the State ID number.
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		   //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                        //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );           //(19-38)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );          //(39-53)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );         //(54-68)[15]: Middle Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );            //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );            //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );             //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                        //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                        //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( $optional_code, 2, 'AN' );                                             //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' );  //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 11, 'N' );               //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 11, 'N' );               //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' ); 														   //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( $this->formatDateStamp( $this->hire_date ), 8, 'AN' );                 //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( $this->formatDateStamp( $this->termination_date), 8, 'AN' );           //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 20, 'AN' );    //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                         //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' );           //(274-275)[2]: State Code - **Documentation says its optional, but when uploading it gives error if missing.
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(276-286)[11]: State Taxable Wages - Right justify and zero fill.
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //(287-297)[11]: State income tax - Fill with blanks
				$line[] = $this->padRecord( '', 10, 'N' );                                         			       //(298-307)[10]: State Excess Wages - Right justify and zero fill.
				$line[] = $this->padRecord( '', 1, 'AN' );                                                         //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other] - Fill with blanks
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //(309-319)[11]: Local Wages - Fill with blanks
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //(320-330)[11]: Local Income Tax - Fill with blanks
				$line[] = $this->padRecord( '', 7, 'AN' );                                                         //(331-337)[7]: State Control Number - Fill with blanks

				$line[] = $this->padRecord( 0, 10, 'N' );                                                           //(338-347)[10]: REPORTING UNIT NUMBER - OPTIONAL‐ ENTER THE STATE ASSIGNED REPORTING UNIT NUMBER OF THE WORKSITE WHERE THE EMPLOYEE WORKED DURING THE QUARTER. RIGHT JUSTIFY AND ZERO FILL.
				$line[] = $this->padRecord( 0, 3, 'N' );                                                            //(348-350)[3]: COUNTY CODE  - OPTIONAL‐ ENTER THE THREE‐DIGIT NUMERIC FIPS COUNTY CODE OF THE EMPLOYEE’S WORK SITE
				$line[] = $this->padRecord( 0, 6, 'N' );                                                            //(351-356)[6]: INDUSTRY CODE - OPTIONAL‐ ENTER THE SIX‐DIGIT NORTH AMERICAN INDUSTRIAL CLASSIFICAITON SYSTEM CODE (NAICS).
				$line[] = $this->padRecord( $this->paid_12th_day_month1, 1, 'N' );                                  //(357-357)[1]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( $this->paid_12th_day_month2, 1, 'N' );                                  //(358-358)[1]: Month 2 employment for employer
				$line[] = $this->padRecord( $this->paid_12th_day_month3, 1, 'N' );                                  //(359-359)[1]: Month 3 employment for employer
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->MoneyFormat( $this->subject_rate ) ), 7, 'N' ); //(360-366)[7]: HOURLY RATE - REQUIRED‐ ENTER ONLY NUMERIC CHARACTERS. ENTER THE AMOUNT OF WAGES (DOLLARS & CENTS) WHICH ARE THE HOURLY WAGE AMOUNT. RIGHT JUSTIFY AND ZERO FILL. FORMAT: 3 DIGITS DOLLARS AND 4 DIGITS CENTS.  I.E. $112.56 SHOULD BE “1125600” IF HOURLY RATE IS OVER $999.9999 THEN ENTER ALL NINES. I.E. “9999999”
				$line[] = $this->padRecord( $this->occupation_classification_code, 80, 'AN' ); 						//(367-446)[80]: SOC CODE/JOB TITLE - OPTIONAL ‐ ENTER EITHER 6 DIGIT SOC CODE OR ENTER JOB TITLE DESCRIPTION. LEFT JUSTIFY AND DO NOT ZERO FILL.
				$line[] = $this->padRecord( '', 41, 'AN' );                                                         //(447-487)[41]: Blank
				$line[] = $this->padRecord( '', 25, 'AN' );                                                         //(488-512)[25]: Blank
				break;
			default: //Federal
				//Withholding Number for State format is the State ID number.
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		   //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                        //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );          //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );         //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );           //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );            //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );            //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );             //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                        //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                        //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( $optional_code, 2, 'AN' );                                             //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' );  //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 11, 'N' );               //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 11, 'N' );               //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->pay_period_taxable_wages_weeks ), 2, 'AN' ); //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( $this->formatDateStamp( $this->hire_date ), 8, 'AN' );                 //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( $this->formatDateStamp( $this->termination_date), 8, 'AN' );           //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 20, 'AN' );    //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                         //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( '', 2, 'N' );                                                          //(274-275)[2]: State Code
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(287-297)[11]: State income tax
				$line[] = $this->padRecord( $state_other_data, 10, 'AN' );                                         //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                         //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                         //(331-337)[7]: State Control Number

				$line[] = $this->padRecord( '', 75, 'AN' );                                                        //(338-412)[75]: Supplemental Data 1
				$line[] = $this->padRecord( '', 75, 'AN' );                                                        //(413-487)[75]: Supplemental Data 2
				$line[] = $this->padRecord( '', 25, 'AN' );                                                        //(488-512)[25]: Blank			}
				break;
		}

		if ( isset( $line ) ) {
			$retval = implode( ( $this->debug == true ) ? ',' : '', $line );
			if ( $this->debug == false && strlen( $retval ) != 512 ) {
				Debug::Text( 'ERROR! RS Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			Debug::Text( 'RS Record: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else {
			Debug::Text( 'Skipping RS Record... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	function _compileRT( $total ) { //RF (Final) Record - Total number of RW (Employee) Records reported on the entire file.
		if ( !in_array( strtolower( $this->efile_state ), [ 'ne' ] ) ) { //Only NE requires this so far.
			return null;
		}

		$line[] = 'RT';                                          //RF Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'ne':
				$tax_rate = $this->tax_rate;

				$line[] = $this->padRecord( $total->total, 7, 'N' );                                    //(3-9)[7] Total RS records.
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 15, 'N' );   //(10-24)[15] State Unemployment Insurance Wages
				$line[] = $this->padRecord( '', 375, 'AN' );                                            //(25-399)[375] Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 15, 'N' );    //(400-414)[15] Total Excess Wages
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 15, 'N' );   //(415-429)[15] Total Taxable Wages
				$line[] = $this->padRecord( $tax_rate, 5, 'N' );                                        //(430-434)[5]: Combined Tax Rate (No idea if this includes decimal or not?)
				$line[] = $this->padRecord( $this->removeDecimal( $total->tax_withheld ), 9, 'N' );     //(435-443)[9]: Combined Tax Due
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 5, 'N' );            //(444-448)[5]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 5, 'N' );            //(449-453)[5]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 5, 'N' );            //(454-458)[5]: Month 3 employment for employer
				$line[] = $this->padRecord( '', 54, 'AN' );                                             //(459-512)[54]: Blank
				break;
			default:
				//Most states don't need this.
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RT Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'RT Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRF( $total ) { //RF (Final) Record - Total number of RW (Employee) Records reported on the entire file.
		$line[] = 'RF';                                          //RF Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'ne':
				$line[] = $this->padRecord( '', 5, 'AN' );            //(3-7)[5] Blank
				$line[] = $this->padRecord( $total->total, 9, 'N' );  //(8-16)[9] Total RS records.
				$line[] = $this->padRecord( 1, 9, 'N' );  //(17-25)[9] Total RE records. **We currently only support 1.
				$line[] = $this->padRecord( '', 487, 'AN' );          //Blank
				break;
			default:
				$line[] = $this->padRecord( '', 5, 'AN' );            //(3-7)[5] Blank
				$line[] = $this->padRecord( $total->total, 9, 'N' );  //(8-16)[9] Total RW records.
				$line[] = $this->padRecord( '', 496, 'AN' );          //Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RF Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'RF Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	// CSV File Format
	function _compileCSV( $record ) {

		$separator = ',';

		Debug::Text( 'CSV Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'az':
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       										//A =  SSN
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 30, 'AN' ) );          								//B = Last Name
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' ) );         								//C = First Name
				$line[] = $this->subject_wages;               																							//D = State Quarter Total Gross Wages
				$line[] = $this->excess_wages;               																							//E = State Quarter Total Excess Wages
				break;
			case 'ia':
				$line[] = ( $record + 1 ); 																												//A = Sequence Number (Maximum 4 digits, no leading 0. Limit 9,999 per file)
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'N' ); 					//B = UI Account Number (Must be 8 digits with leading 0's)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                             //C = Transmitter EIN
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year . $this->padRecord( $this->quarter_of_year, 2, 'N' ) ), 6, 'N' ); 		//D = Year and Quarter, ie: 202201
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       										//E =  SSN
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 30, 'AN' ) );          								//F = Last Name
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' ) );         								//G = First Name
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' ) );         								//H = Middle Initial
				$line[] = $this->subject_wages;               																							//I = State Quarter Total Gross Wages
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->reporting_unit_number ), 4, 'N' );       									//J = Reporting Unit Number
				break;
			case 'wi': //Tab-Delimited Text File Format Alternative 4 - https://dwd.wisconsin.gov/ui201/w32014.htm
				$separator = "\t"; //Tab separated

				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 10, 'N' ); 					//A = UI Account Number (Must be 10 digits)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->quarter_of_year . $this->year ), 3, 'N' ); 									//B = QYY - Quarter and Year, ie: 322
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       										//C =  SSN
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 10, 'AN' ) );          								//D = Last Name
				$line[] = trim( $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 8, 'AN' ) );         								//E = First Name
				$line[] = $this->subject_wages;               																							//F = State Quarter Total Gross Wages
				$line[] = '01';               																											//G = Record Code, should always be '01'
				break;
			default:
				$line[] = null;
				break;
		}

		$retval = implode( $separator, $line );

		Debug::Text( 'CSV Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	// ICESA file format
	//   Records: A, B, E, S, T, F
	function _ICESAcompileA() {
		if ( in_array( strtolower( $this->efile_state ), [ 'ct', 'ga', 'ca' ] ) ) { //CT doesn't use this format.
			return null;
		}

		$line[] = 'A';                                                                                                                                                        //A Record

		if ( defined( 'UNIT_TEST_MODE' ) && UNIT_TEST_MODE == true ) {
			$media_creation_time = strtotime('31-Dec-2022');
		} else {
			$media_creation_time = time();
		}

		$max_line_length = 275;

		Debug::Text( 'A Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'co': //https://cdle.colorado.gov/sites/cdle/files/documents/WageFTPInstructions.pdf
				$max_line_length = 276; //Non-Standard line lengths.

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                                                                    //(2-5)[4] Year
				$line[] = $this->padRecord( '', 9, 'AN' );                                                                                    									 //(6-14)[9] Blank
				$line[] = 'UTAX';																																				//(15-18)[4] UTAX Constant
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(19-23)[5] Blank

				$line[] = $this->padRecord( $this->trade_name, 50, 'AN' );		                                                                                                //(24-73)[50] Transmitter Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 40, 'AN' );                                                                 //(74-113)[40] Tranmisster street address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 25, 'AN' );                                                                     //(114-138)[25] Transmitter City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //(139-140)[2] Transmitter State abbreviation (alpha)
				$line[] = $this->padRecord( '', 13, 'AN' );                                                                                                                     //(141-153)[13] Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //(154-158)[5] Transmitter Zip Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(159-163)[5] Transmitter Zip Code Extension
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 30, 'AN' );                                                                     //(164-193)[30] Transmitter Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 10, 'AN' );                                                                         //(194-203)[10] Transmitter Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 4, 'AN' );                                                                      //(204-207)[4] Transmitter Contact Phone Ext

				$line[] = $this->padRecord( $this->contact_email, 69, 'AN' );                                                                                                   //(208-276)[69] Email Address
				break;
			default:
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                                                                    //(2-5)[4] Year
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(6-14)[9] Transmitter EIN
				$line[] = 'UTAX';																																				//(15-18)[4] UTAX Constant
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(19-23)[5] Blank

				$line[] = $this->padRecord( $this->trade_name, 50, 'AN' );		                                                                                                //(24-73)[50] Transmitter Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 40, 'AN' );                                                                 //(74-113)[40] Tranmisster street address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 25, 'AN' );                                                                     //(114-138)[25] Transmitter City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //(139-140)[2] Transmitter State abbreviation (alpha)
				$line[] = $this->padRecord( '', 13, 'AN' );                                                                                                                     //(141-153)[13] Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //(154-158)[5] Transmitter Zip Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //(159-163)[5] Transmitter Zip Code Extension
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 30, 'AN' );                                                                     //(164-193)[30] Transmitter Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 10, 'AN' );                                                                         //(194-203)[10] Transmitter Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 4, 'AN' );                                                                      //(204-207)[4] Transmitter Contact Phone Ext

				$line[] = $this->padRecord( '', 35, 'AN' );                                                                                                                     //(208-242)[35] Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->formatDateStamp( $media_creation_time ) ), 8, 'N' );                                                 //(243-250)[8] Media Creation Date
				$line[] = $this->padRecord( '', 25, 'AN' );                                                                                                                     //(251-275)[25] Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
			Debug::Text( 'ERROR! A Record length is incorrect, should be '. $max_line_length .' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'A Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ICESAcompileN( $total ) {
		if ( in_array( strtolower( $this->efile_state ), [ 'nc', 'ga' ] ) === false ) { //Only used for these states currently.
			return null;
		}

		$line[] = 'N';                                                                                                                                                  //N Record

		Debug::Text( 'N Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'nc':
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' ); 											//(2-8)[7]: Employer Account number
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->quarter_of_year ), 1, 'N' ); 																		//(8-9)[1]: Reporting Quarter and Year (1,2,3,4)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                                                                    //(10-13)[4] Reporting Year
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 5, 'N' ); 																					//(14-18)[5]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 5, 'N' ); 																					//(19-23)[5]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 5, 'N' ); 																					//(24-28)[5]: Month 3 employment for employer

				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 11, 'N' ); 																			//(29-39)[11]: State Quarterly Gross Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 11, 'N' ); 																			//(40-50)[11]: State Quarterly Unemployment Insurance Excess for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 11, 'N' ); 																			//(51-61)[11]: State Quarterly Unemployment Insurance Taxable Wages for Employer

				$line[] = $this->padRecord( '999996', 6, 'N' ); 																												//(62-67)[6]: Remitter number if filing for others. Otherwise enter 999996.
				$line[] = $this->padRecord( 'F', 1, 'AN' ); 																												    //(68)[1]: Constant: F
				$line[] = $this->padRecord( '', 207, 'AN' ); 																												    //(69-275)[207]: Blank
				break;
			case 'ga':
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'N' ); 											//(2-9)[8]: Employer Account number
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->quarter_of_year ), 1, 'N' ); 																		//(10)[1]: Reporting Quarter and Year (1,2,3,4)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                                                                    //(11-14)[4] Reporting Year
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 5, 'N' ); 																					//(15-19)[5]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 5, 'N' ); 																					//(20-24)[5]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 5, 'N' ); 																					//(25-29)[5]: Month 3 employment for employer

				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 11, 'N' ); 																			//(30-40)[11]: State Quarterly Gross Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 11, 'N' ); 																			//(41-51)[11]: State Quarterly Unemployment Insurance Excess for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 11, 'N' ); 																			//(52-62)[11]: State Quarterly Unemployment Insurance Taxable Wages for Employer

				$line[] = $this->padRecord( $this->removeDecimal( $total->tax_withheld ), 9, 'N' );  																			//(63-71)[9]: Remittance Amount
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(72-80)[9]: Employers EIN
				$line[] = $this->padRecord( '', 195, 'AN' ); 																												    //(81-275)[195]: Blank
				break;
			default:
				return ''; //exit early with blank string for states that don't use the 'N' record.
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 275 ) {
			Debug::Text( 'ERROR! N Record length is incorrect, should be 275 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'N Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ICESAcompileB() {
		if ( in_array( strtolower( $this->efile_state ), [ 'ct', 'ga', 'co', 'ca' ] ) ) { //CT doesn't use this format.
			return null;
		}

		$line[] = 'B';                                                                                                                                                        //A Record

		Debug::Text( 'B Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			default:
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                                                                                    //(2-5)[4] Year
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(6-14)[9] Transmitter EIN
				$line[] = $this->padRecord( '', 5, 'AN' );																														//(15-22)[8] Computer
				$line[] = $this->padRecord( 'NL', 5, 'AN' );                                                                                                                    //(23-24)[2] Internal Label

				$line[] = $this->padRecord( '', 201, 'AN' );                                                                                                					//(25-225)[201] Blank
				$line[] = $this->padRecord( $this->contact_email, 50, 'AN' );                                                                 									//(226-275)[50] Transmisster Contact Email Address

				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 275 ) {
			Debug::Text( 'ERROR! B Record length is incorrect, should be 275 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'B Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ICESAcompileE() {
		if ( in_array( strtolower( $this->efile_state ), [ 'ct', 'ga', 'co' ] ) ) { //CT doesn't use this format.
			return null;
		}

		$line[] = 'E';                                                                                  //(1)[1] E Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'tn': //Custom format for TN: https://tnpaws.tn.gov/StaticPages/FileUploadHelp.aspx
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(2-5)[4] Tax Year
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(6-14)[9] EIN
				$line[] = $this->padRecord( '', 146, 'AN' );                                                    //(15-160)[146] Blank
				$line[] = $this->padRecord( '25', 2, 'AN' );                                                  //(161-162)[2] Blank
				$line[] = $this->padRecord( '', 8, 'AN' );                                                      //(163-170)[8] Blank
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		//(171-172)[2]: State Code (numeric)
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'AN' );    //(173-180)[8]: 8-digit employer account number found on quarterly premium report.
				$line[] = $this->padRecord( '', 7, 'AN' );                                                      //(181-187)[7] Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year ), 2, 'N' ); 	        //(188-189)[2]: Reporting Period (Last month of calendar quater)
				$line[] = $this->padRecord( 0, 1, 'N' );                                                        //(190)[1] 0=E record will not be followed by 1 or more employees. 1=E record will be followed by 1 or more employees.
				$line[] = $this->padRecord( '', 85, 'AN' );                                                     //(191-275)[85] Blank
				break;
			case 'ct': //CT switched format in July 2022: https://www.ctdol.state.ct.us/uitax/FTPFileFormatforReEmploy.pdf -- Old format was: https://www.ctdol.state.ct.us/uitax/magnetic.htm#IV.%20RECORD%20FORMAT%20TABLE
				//E record is not required for CT.
				//$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(2-5)[4] Tax Year
				//$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(6-14)[9] EIN
				//$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(15-23)[9] Blank
				//$line[] = $this->padRecord( $this->trade_name, 50, 'AN' );                                      //(24-73)[50] Company Name
				//$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 40, 'AN' ); //(74-113)[40] Company Street Address
				//$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 25, 'AN' );     //(114-138)[25] Company City
				//$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(139-140)[2] Company State
				//$line[] = $this->padRecord( '', 8, 'AN' );                                                      //(141-148)[8] Blank
				//$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(149-153)[5] Company Zip Code
				//$line[] = $this->padRecord( '', 5, 'AN' );                                                      //(154-158)[5] Company Zip Code Extension
				//$line[] = $this->padRecord( 'S', 1, 'AN' );                                                      //(159)[1] For CT S=Surname first in "S" record.
				//$line[] = $this->padRecord( 'R', 1, 'AN' );                                                     //(160)[1] Type of Employment: A=Argriculture, H=Household, M=Military, Q-Medicare Qualified Gov. Emp, X=Railroad, R=Regular
				//$line[] = $this->padRecord( '', 2, 'AN' );                                                      //(161-162)[2] Blocking Factor. Enter blanks.
				//$line[] = $this->padRecord( '', 6, 'AN' );                                                      //(163-168)[6] Blank
				//$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		//(169-170)[2]: State Code (numeric)
				//$line[] = $this->padRecord( '', 6, 'AN' );                                                      //(171-175)[5] Blank
				//$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' );    //(176-182)[7]: Employer Account number (CT-NUMBER)
				//$line[] = $this->padRecord( '', 94, 'AN' );                                                      //(183-276)[94] Blank
				break;
			default:
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(2-5)[4] Tax Year
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(6-14)[9] EIN
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(15-23)[9] Blank
				$line[] = $this->padRecord( $this->trade_name, 50, 'AN' );                                      //(24-73)[50] Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 40, 'AN' ); //(74-113)[40] Company Street Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 25, 'AN' );     //(114-138)[25] Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(139-140)[2] Company State
				$line[] = $this->padRecord( '', 8, 'AN' );                                                      //(141-148)[8] Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(149-153)[5] Company Zip Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                      //(154-158)[5] Company Zip Code Extension
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(159)[1] Blank.
				$line[] = $this->padRecord( 'R', 1, 'AN' );                                                     //(160)[1] Type of Employment: A=Argriculture, H=Household, M=Military, Q-Medicare Qualified Gov. Emp, X=Railroad, R=Regular
				$line[] = $this->padRecord( '', 2, 'AN' );                                                      //(161-162)[2] Blocking Factor. Enter blanks.
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(163-166)[4] Establishment number of coverage group/PRU. Otherwise blanks.
				$line[] = 'UTAX';																				//(167-170)[4] UTAX Constant
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 		//(171-172)[2]: State Code (numeric)
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 15, 'AN' );    //(173-187)[15]: ES Reference NUmber of employer for wages being reported
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year ), 2, 'N' ); 	        //(188-189)[2]: Reporting Period (Last month of calendar quater)
				$line[] = $this->padRecord( 1, 1, 'N' );                                                        //(190)[1] 0=E record will not be followed by 1 or more employees. 1=E record will be followed by 1 or more employees.

				switch ( strtolower( $this->efile_state ) ) {
					case 'wa':
						$line[] = $this->padRecord( '', 65, 'AN' );                                                     //(191-255)[65] Blank
						$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(256)[1] Foreign indicator:If data in positions 74-158 (Employer address fields) isfor a foreign address, enter the letter “X”, otherwise, space fill
						$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(257)[1] Blank
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_secondary_id ) ), 12, 'AN' ); //(258-269)[12]: Employer Unified Business Identifier (UBI) Number
						$line[] = $this->padRecord( '', 6, 'AN' );                                                      //(270-275)[6] Blank
						break; //WA
					default:
						$line[] = $this->padRecord( 'T', 1, 'AN' );                                                     //(191)[1] Tax Type Code (T=Taxable, R=Reimbursable)
						$line[] = $this->padRecord( '', 5, 'AN' );                                                       //(192-196)[5] Taxing Entity Code
						$line[] = $this->padRecord( '', 7, 'AN' );                                                       //(197-203)[7] State Control Number
						$line[] = $this->padRecord( '', 5, 'AN' );                                                       //(204-208)[5] Unit Number
						$line[] = $this->padRecord( '', 46, 'AN' );                                                      //(209-254)[46] Blank
						$line[] = $this->padRecord( '', 1, 'AN' );                                                       //(255)[1] Limitation of Liability Indicator
						$line[] = $this->padRecord( '', 1, 'AN' );                                                       //(256)[1] Foreign indicator:If data in positions 74-158 (Employer address fields) isfor a foreign address, enter the letter “X”, otherwise, space fill
						$line[] = $this->padRecord( '', 1, 'AN' );                                                       //(257)[1] Blank
						$line[] = $this->padRecord( '', 9, 'AN' );                                                       //(258-266)[9] Other FEIN
						$line[] = $this->padRecord( '', 1, 'AN' );                                                       //(267)[1] Report Type (O=Original,S=Supplemental, A=Amendment)
						$line[] = $this->padRecord( '', 1, 'AN' );                                                       //(268-269)[2] Report Number (for supplmental reports)
						$line[] = $this->padRecord( '', 7, 'AN' );                                                       //(270-276)[7] Blank
						break;
				}
				break;

		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 275 ) {
			Debug::Text( 'ERROR! E Record length is incorrect, should be 275 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'E Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
	function _ICESAcompileS() {
		Debug::Text( 'S Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );

		$max_line_length = 275;

		$line[] = 'S';                                                                                    //(1)[1]: S Record
		switch ( strtolower( $this->efile_state ) ) {
			case 'tn': //Custom format for TN: https://tnpaws.tn.gov/StaticPages/FileUploadHelp.aspx
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                        //(46-49)[4]: Blank
				$line[] = $this->padRecord( 0, 14, 'N' );               										  //(50-63)[14]: Zeros
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );              //(64-77)[14]: State Quarter Unemployment Insurance Total Wages
				$line[] = $this->padRecord( '', 198, 'AN' );                                                      //(78-275)[198]: Blank
				break;
			case 'ca': //https://edd.ca.gov/siteassets/files/pdf_pub_ctr/de8300.pdf
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code
				$line[] = $this->padRecord( '', 18, 'AN' );                                                       //(46-63)[18]: Blank

				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );              //(64-77)[14]: State Quarterly Unemployment Insurance (UI)/State Disability Insurance (SDI) Total Wages
				$line[] = $this->padRecord( '', 69, 'AN' );                  									  //(78-146)[69]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'N' ); //(147-154[8]: State Employer Account Number (SEAN)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->branch_code ), 3, 'N' );				  //(155-157)[3]: If registered with the Department as a branch coded employer, enter the applicable branch code for each employee. If not a branch coded employer, zero fill. Do not leave blank
				$line[] = $this->padRecord( '', 19, 'AN' );                  									  //(158-176)[19]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->state_income_tax_subject_wages ), 14, 'N' ); //(177-190)[14]: NUMERIC CHARACTERS ONLY. Enter the amount of employee’s quarterly PIT wages paid during the period that are subject to California PIT even if they were not subject to PIT withholding. Include dollars and cents. Omit commas and  decimals. Right justify and zero fill.
				$line[] = $this->padRecord( $this->removeDecimal( $this->state_income_tax_tax_withheld ), 14, 'N' );  //(191-204)[14]: NUMERIC CHARACTERS ONLY. Enter the amount of employee’s quarterly PIT withheld. Include dollars and cents. Omit commas and decimals. Right justify and zero fill.
				$line[] = $this->padRecord( '', 6, 'AN' );                                                        //(205-210)[6]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->wage_plan_code ), 1, 'AN' );      //(211)[1]: Wage Plan Code -- https://edd.ca.gov/siteassets/files/pdf_pub_ctr/de8300.pdf (Page 30)
				$line[] = $this->padRecord( '', 3, 'AN' );                                                        //(212-214)[3]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' ); //(215-220)[6]: Reporting Quarter and Year
				$line[] = $this->padRecord( '', 55, 'AN' );							                              //(221-275)[55]: Blank
				break;
			case 'co':
				$max_line_length = 276; //Non-Standard line lengths.

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN

				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code

				$line[] = $this->padRecord( '', 18, 'AN' );                                                        //(46-63)[18]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );               //(64-77)[14]: State Quarter Unemployment Insurance Total Wages
				$line[] = $this->padRecord( '', 65, 'AN' );                                                        //(78-142)[65]: Blank
				$line[] = 'UTAX';																				   //(143-146)[4] UTAX Constant

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->state_primary_id ), 15, 'AN' ); 		   //(147-161[15]: State Unemployment Insurace Account Number (9-digit account number, left justify, then fill with blanks)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->branch_code ), 15, 'AN' );              //(162-176)[15]: Division/Plant Code (3-digit account number, left justify, then fill with blanks. Use '000' if only a single location)
				$line[] = $this->padRecord( '', 28, 'AN' );                                                        //(177-204)[28]: Blank
				$line[] = $this->padRecord( ( ( $this->is_seasonal == true ) ? 'S' : '' ), 2, 'AN' );              //(205-206)[2]: Season Indicator - Enter: 'S' for seasonal, or leave blank.
				$line[] = $this->padRecord( '', 8, 'AN' );                                                         //(207-214)[8]: Blank

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ) . $this->padRecord( $this->quarter_of_year, 2, 'N' ), 6, 'N' ); //(215-220)[6]: Reporting Quarter and Year (4-digit year, then 2 digit quarter.)
				$line[] = $this->padRecord( '', 56, 'AN' );                                                        //(221-276)[56]: Blank
				break;
			case 'ct': //CT switched format in July 2022: https://www.ctdol.state.ct.us/uitax/FTPFileFormatforReEmploy.pdf -- Old format was: https://www.ctdol.state.ct.us/uitax/magnetic.htm#IV.%20RECORD%20FORMAT%20TABLE
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' ); //(46-51)[6]: Reporting Quarter and Year
				$line[] = $this->padRecord( '', 12, 'AN' );               										  //(52-63)[12]: Blank

				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );               //(64-77)[14]: State Quarter Unemployment Insurance Total Wages
				$line[] = $this->padRecord( '', 65, 'AN' );                                                        //(78-142)[65]: Blank
				$line[] = 'WAGE';																				   //(143-146)[4] WAGE Constant
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 10, 'N' ); //(147-156[10]: State Unemployment Insurace Account Number
				$line[] = $this->padRecord( '', 119, 'AN' );                                                      //(157-275)[119]: Blank
				break;
			case 'in': //https://www.in.gov/dwd/indiana-unemployment/employers/faqs/ess-enhancement-faq/file-specifications/
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code
				$line[] = $this->padRecord( date('mdY', $this->hire_date ), 8, 'N' );                              //(46-53)[8]: Employee Start Date: MMDDYYYY
				$line[] = $this->padRecord( $this->company_zip_code, 5, 'N' );                              	   //(54-58)[5]: Zip code of primary work location.
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(59-63)[5]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );               //(64-77)[14]: State Quarter Unemployment Insurance Total Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->excess_wages ), 14, 'N' );                //(78-91)[14]: State Quarter Unemployment Insurance Excess Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 14, 'N' );               //(92-105)[14]: State Quarter Unemployment Insurance Taxable Wages
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->occupation_classification_code ), 6, 'N' ); //(106-111[6]: Standard Occupation Classification Code (map from Form Setup?) - https://www.bls.gov/oes/current/oes_stru.htm
				$line[] = $this->padRecord( '', 35, 'AN' );							                               //(112-146)[35]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 6, 'N' ); //(147-152[6]: State Unemployment Insurace Account Number
				$line[] = $this->padRecord( '', 52, 'AN' );							                               //(153-204)[52]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->designation ), 2, 'AN' );	       //(205-206)[2]: Full-time=FT, Part-time=PT, Seasonal must be two digits numeric. (Map from Form Setup?)
				$line[] = $this->padRecord( '', 5, 'AN' );							                               //(207-211)[5]: Blank
				$line[] = $this->padRecord( $this->paid_12th_day_month1, 1, 'AN' );                               //(212)[1]: Month 1 Employment
				$line[] = $this->padRecord( $this->paid_12th_day_month2, 1, 'AN' );                               //(213)[1]: Month 2 Employment
				$line[] = $this->padRecord( $this->paid_12th_day_month3, 1, 'AN' );                               //(214)[1]: Month 3 Employment
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' ); //(215-220)[6]: Reporting Quarter and Year
				$line[] = $this->padRecord( '', 55, 'AN' );							                               //(221-275)[55]: Blank
				break;
			case 'ga': //Georgia - NASWA Y2K Wage Record Format - https://dol.georgia.gov/document/unemployment-tax/electronic-filing-requirements-dol-4606/download
				//Withholding Number for State format is the State ID number.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code

				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(46-49)[4]: Blank
				$line[] = $this->padRecord( '', 14, 'AN' );											               //(50-63)[14]: Blank
				$line[] = $this->padRecord( '', 5, 'AN' );               //(64-68)[5]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 9, 'N' );               //(69-77)[9]: Total Reportable Gross Wages minus 125 Cafeteria Plan. Include Tip Wages
				$line[] = $this->padRecord( '', 76, 'N' );                //(78-153)[76]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'N' ); //(154-161[8]: State Unemployment Insurace Account Number
				$line[] = $this->padRecord( '', 53, 'N' );                //(162-214)[53]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' ); //(215-220)[6]: Reporting Quarter and Year
				$line[] = $this->padRecord( '', 55, 'N' );                //(221-276)[55]: Blank
				break;
			default: //Federal
				//Withholding Number for State format is the State ID number.
				//$line[] = $this->padRecord( ( ( $this->ssn != '' ) ? $this->stripNonNumeric( $this->ssn ) : 'I' ), 9, 'AN' ); //(2-10)[9]: SSN -- Texas: If unknown, enter 'I' followed by blanks. This doesn't seem to be accepted by the Quick File though.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(2-10)[9]: SSN

				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(11-30)[20]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(31-42)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(43)[1]: Middle Initial
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' ); 	      //(44-45)[2]: State Code

				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(46-49)[4]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );               //(50-63)[14]: State Quarter Total Gross Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 14, 'N' );               //(64-77)[14]: State Quarter Unemployment Insurance Total Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->excess_wages ), 14, 'N' );                //(78-91)[14]: State Quarter Unemployment Insurance Excess Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 14, 'N' );               //(92-105)[14]: State Quarter Unemployment Insurance Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( '0.00' ), 15, 'N' );                             //(106-120)[15]: State Quarter Disability Insurance Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( '0.00' ), 9, 'N' );                              //(121-129)[9]: State Quarter Tip Wages

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->pay_period_taxable_wages_weeks ), 2, 'AN' ); //(130-131)[2]: Number of weeks worked
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->subject_units ), 3, 'AN' );             //(132-134)[3]: Number of hours worked
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(135-138)[4]: Date First employed (Doesn't seem to be used)
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(139-142)[4]: Date of Separation (Doesn't seem to be used)
				$line[] = 'UTAX';																				   //(143-146)[4] UTAX Constant

				//State specific
				switch ( strtolower( $this->efile_state ) ) {
					case 'tx':
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 9, 'N' ); //(147-155[9]: State Unemployment Insurace Account Number
						$line[] = $this->padRecord( '', 6, 'AN' );                                                       //(156-161)[6]: 6-digit NAICS code for site where employee works.
						$line[] = $this->padRecord( '', 48, 'AN' );                                                      //(162-209)[48]: Blank
						break;
					case 'il':
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' ); //(147-153[7]: State Unemployment Insurace Account Number
						$line[] = $this->padRecord( '', 56, 'AN' );                                                      //(154-209)[56]: Blank
						break;
					case 'mn':
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 8, 'N' ); //(147-154[8]: 8-digit State ID Registration Account Number (right justify and zero fill)
						$line[] = $this->padRecord( '', 7, 'AN' );                                                      //(155-161)[7]: Blank
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->reporting_unit_number ) ), 4, 'N' ); //(162-165[4]: The 4-digit ID assigned to identify Wages by Work Site. (Right justify and zero fill)
						$line[] = $this->padRecord( '', 44, 'AN' );                                                   //(166-209)[44]: Blank
						break;
					default:
						$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 15, 'N' ); //(147-161)[15]: State Unemployment Insurace Account Number
						$line[] = $this->padRecord( '', 48, 'AN' );                                                      //(162-209)[48]: Blank
						break;
				}

				//$line[] = $this->padRecord( '', 3, 'N' );                                                          //(162-164)[3]: Division/Plant Code
				//$line[] = $this->padRecord( '', 12, 'AN' );                                                        //(165-176)[12]: Blank
				//$line[] = $this->padRecord( '', 14, 'N' );                                                        //(177-190)[14]: State Taxable Wages
				//$line[] = $this->padRecord( '', 14, 'N' );                                                        //(191-204)[14]: State Tax Withheld
				//$line[] = $this->padRecord( '', 2, 'AN' );                                                        //(205-206)[2]: Season Indicator
				//$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(207)[1]: Employer Health Insurance Code
				//$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(208)[1]: Employee Health Insurance Code
				//$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(209)[1]: Probationary Code


				$line[] = $this->padRecord( 0, 1, 'AN' );                                                         //(210)[1]: Officer Code - For employees who are exempt officiers of the coproration, enter 1, otherwise enter 0.
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(211)[1]: Wage Plan Code
				$line[] = $this->padRecord( $this->paid_12th_day_month1, 1, 'AN' );                               //(212)[1]: Month 1 Employment
				$line[] = $this->padRecord( $this->paid_12th_day_month2, 1, 'AN' );                               //(213)[1]: Month 2 Employment
				$line[] = $this->padRecord( $this->paid_12th_day_month3, 1, 'AN' );                               //(214)[1]: Month 3 Employment

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year.$this->year ), 6, 'N' ); //(215-220)[6]: Reporting Quarter and Year

				$line[] = $this->padRecord( $this->formatMonthAndYear( $this->hire_date ), 6, 'AN' );              //(221-226)[6]: Month and Year First Employed
				$line[] = $this->padRecord( $this->formatMonthAndYear( $this->termination_date), 6, 'AN' );        //(227-232)[6]: Month and Year of Separation

				//State specific
				switch ( strtolower( $this->efile_state ) ) {
					case 'mn':
						$line[] = $this->padRecord( '', 42, 'AN' );                                                        //(233-274)[42]: Blank
						$line[] = $this->padRecord( 'X', 1, 'AN' );                                                        //(275)[1]: Constant “X”. (Required for MN processing.)
						break;
					default:
						$line[] = $this->padRecord( '', 43, 'AN' );                                                        //(233-275)[43]: Blank
						break;
				}

				break;
		}

		if ( isset( $line ) ) {
			$retval = implode( ( $this->debug == true ) ? ',' : '', $line );
			if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
				Debug::Text( 'ERROR! S Record length is incorrect, should be '. $max_line_length .' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			Debug::Text( 'S Record: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else {
			Debug::Text( 'Skipping RS Record... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	function _ICESAcompileT( $total ) {
		if ( in_array( strtolower( $this->efile_state ), [ 'ga', 'co' ] ) ) { //GA doesn't use this format.
			return null;
		}

		$line[] = 'T';                                          //T Record

		//Tax rate can be different for each state.
		// TX=The employer’s tax rate for this reporting period. Decimal point followed by 5 digits, e.g., 2.8% = .02800
		// WA=Decimal is assumed e.g., 2.8% = 028000.
		switch ( strtolower( $this->efile_state ) ) {
			case 'wa':
			case 'ok':
				$tax_rate = $this->getAfterDecimal( TTMath::div( $this->tax_rate, 100, 6 ), false );
				break;
			default: //Decimal place is required: 'tx', 'ky'
				$tax_rate = '.'. $this->getAfterDecimal( TTMath::div( $this->tax_rate, 100, 6 ), false ); //Add decimal back in.
				break;
		}

		switch ( strtolower( $this->efile_state ) ) {
			case 'ct': //CT switched format in July 2022: https://www.ctdol.state.ct.us/uitax/FTPFileFormatforReEmploy.pdf -- Old format was: https://www.ctdol.state.ct.us/uitax/magnetic.htm#IV.%20RECORD%20FORMAT%20TABLE
				$line[] = $this->padRecord( $total->total, 7, 'N' );  //(2-8)[7] Total S records.
				$line[] = 'WAGE';									  //(9-12)[4] WAGE Constant
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 10, 'N' ); //(13-22[10]: State Unemployment Insurace Account Number
				$line[] = $this->padRecord( '', 4, 'AN' ); 											  //(23-26)[4]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 14, 'N' ); //(27-40)[14]: State Quarterly Unemployment Insurance Total Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 14, 'N' ); //(41-54)[14]: State Quarterly Unemployment Insurance Excess for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 14, 'N' ); //(55-68)[14]: State Quarterly Unemployment Insurance Taxable Wages for Employer
				$line[] = $this->padRecord( '', 19, 'N' );            								  //(69-87)[19]: State Quarterly Tip Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->tax_withheld ), 13, 'N' );  //(88-100)[13]: Tax Withheld: If paying by a bulk ACH Debit Payment, this field is required and is the amount CTDOL is authorized to debit for this Employer’s Tax Filing. (If paying by ACH Credit, this field will be ignored.)
				$line[] = $this->padRecord( '', 126, 'N' );            								  //(101-226)[126]: Blank
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 7, 'N' ); //(227-233)[7]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 7, 'N' ); //(234-240)[7]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 7, 'N' ); //(241-247)[7]: Month 3 employment for employer
				$line[] = $this->padRecord( '', 28, 'N' );            								  //(248-275)[28]: Blank
				break;
			case 'ca':
				$line[] = $this->padRecord( $total->total, 7, 'N' );  //(2-8)[7] Total S records.
				$line[] = $this->padRecord( '', 18, 'N' );            //(9-26)[18]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ?? 0 ), 14, 'N' ); //(27-40)[14]: State Quarterly Unemployment Insurance Total Wages for Employer
				$line[] = $this->padRecord( '', 158, 'N' );            //(41-198)[158]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax_subject_wages ?? 0 ), 14, 'N' ); //(199-212)[14]: Enter the total of amounts in positions 177-190 (PIT wages) of Code S records from the preceding Code E record. Include dollars and cents.
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax_tax_withheld ?? 0 ), 14, 'N' ); //(213-226)[14]: Enter the total of amounts in positions 191-204 (PIT withheld) of Code S records from the preceding Code E record. Include dollars and cents.
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ?? 0 ), 7, 'N' ); //(227-233)[7]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ?? 0 ), 7, 'N' ); //(234-240)[7]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ?? 0 ), 7, 'N' ); //(241-247)[7]: Month 3 employment for employer
				$line[] = $this->padRecord( '', 28, 'N' );            //(248-275)[28]: Blank
				break;
			default:
				$line[] = $this->padRecord( $total->total, 7, 'N' );  //(2-8)[7] Total S records.
				$line[] = 'UTAX';									  //(9-12)[4] UTAX Constant
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 14, 'N' ); //(13-26)[14]: State Quarterly Gross Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 14, 'N' ); //(27-40)[14]: State Quarterly Unemployment Insurance Total Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 14, 'N' ); //(41-54)[14]: State Quarterly Unemployment Insurance Excess for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 14, 'N' ); //(55-68)[14]: State Quarterly Unemployment Insurance Taxable Wages for Employer
				$line[] = $this->padRecord( '', 13, 'N' );            //(69-81)[13]: State Quarterly Tip Wages for Employer
				$line[] = $this->padRecord( $tax_rate, 6, 'AN' );            //(82-87)[6]: UI Tax Rate for this quarter. Decimal point followed by 5 digits, ie: 3.1% = .03100

				switch ( strtolower( $this->efile_state ) ) {
					case 'ky':
						$line[] = $this->padRecord( '', 57, 'N' );            //(88-144)[57]: Blank
						$line[] = $this->padRecord( '', 5, 'N' );             //(145-149)[5]: SURCHARGE RATE or SCUF RATE* - .22% = 00220 or 0.075% = 00075). -- Last used in 2016.
						$line[] = $this->padRecord( '', 11, 'N' );            //(150-160)[11]: SURCHARGE DUE or SCUF DUE* - Taxable wages multiplied by the above rate.  -- Last used in 2016.
						$line[] = $this->padRecord( '', 66, 'AN' );           //(161-226)[66]: Blank
						break;
					default:
						$line[] = $this->padRecord( $this->removeDecimal( TTMath::mul( TTMath::div( $this->tax_rate, 100 ), $total->taxable_wages ) ), 13, 'N' );            //(88-100)[13]: State Quarterly Contribution Due - Calculate it as a total to avoid rounding issues and such when just sum'ing on a per employee basis.
						$line[] = $this->padRecord( '', 11, 'N' );            //(101-111)[11]: Previous Quarters Underpayment
						$line[] = $this->padRecord( '', 11, 'N' );            //(112-122)[11]: Interest
						$line[] = $this->padRecord( '', 11, 'N' );            //(123-133)[11]: Penalty Due
						$line[] = $this->padRecord( '', 11, 'N' );            //(134-144)[11]: Credit/Overpayment. Previous over payment being applied to balance due.
						$line[] = $this->padRecord( '', 4, 'AN' );            //(145-148)[4]: Employer Assessment Rate
						$line[] = $this->padRecord( '', 11, 'N' );            //(149-159)[11]: Employer Assessment Amount
						$line[] = $this->padRecord( '', 4, 'AN' );            //(160-163)[4]: Employee Assessment Rate
						$line[] = $this->padRecord( '', 11, 'N' );            //(164-174)[11]: Employee Assessment Amount
						$line[] = $this->padRecord( '', 11, 'N' );            //(175-185)[11]: Total Payment Due
						$line[] = $this->padRecord( '', 13, 'N' );            //(186-198)[13]: Allocation Amount
						$line[] = $this->padRecord( '', 14, 'N' );            //(199-212)[14]: Wages Subject to State Income Tax
						$line[] = $this->padRecord( '', 14, 'N' );            //(213-226)[14]: State Income Tax Withheld
						break;
				}

				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 7, 'N' ); //(227-233)[7]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 7, 'N' ); //(234-240)[7]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 7, 'N' ); //(241-247)[7]: Month 3 employment for employer

				switch ( strtolower( $this->efile_state ) ) {
					case 'tx':
						$line[] = $this->padRecord( ( ( !is_numeric( $this->county_code ) ) ? $this->_getTexasCountyCodes( $this->county_code ) : $this->county_code ), 3, 'AN' );            //(248-250)[3]: County Code of the county in which you had the greatest number of employees.
						$line[] = $this->padRecord( 0, 7, 'N' ); 							  //(251-257)[7]: Outside County Employees
						$line[] = $this->padRecord( '', 10, 'AN' ); 						  //(258-267)[10]: Document Control Number (blank)
						$line[] = $this->padRecord( '', 8, 'AN' ); 							  //(268-275)[8]: Blank
						break;
					case 'wa':
						$line[] = $this->padRecord( '', 20, 'AN' );            //(248-267)[20]: Blank
						$line[] = $this->padRecord( 0, 1, 'AN' );            //(268)[1]: Enter "1" if Total Excess Wage Amount includes Out-of-State Wages.
						$line[] = $this->padRecord( '', 7, 'AN' ); 			 //(269-275)[7]: Blank
						break;
					default:
						$line[] = $this->padRecord( '', 28, 'AN' );            //(248-275)[28]: Blank
						break;
				}
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 275 ) {
			Debug::Text( 'ERROR! T Record length is incorrect, should be 275 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'T Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
	function _ICESAcompileF( $total ) {
		if ( in_array( strtolower( $this->efile_state ), [ 'ct', 'ga', 'co', 'ca' ] ) ) { //CT doesn't use this format.
			return null;
		}

		$line[] = 'F';                                          //F Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'tn': //Custom format for TN: https://tnpaws.tn.gov/StaticPages/FileUploadHelp.aspx
				$line[] = $this->padRecord( '', 274, 'N' );            //(2-275)[274]: Blank
				break;
			default:
				$line[] = $this->padRecord( $total->total, 10, 'N' );  //(2-11)[10] Total S records.
				$line[] = $this->padRecord( 1, 10, 'N' );             //(12-21)[10]: Total Number of Employers
				$line[] = 'UTAX';									  //(22-25)[4] UTAX Constant

				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 15, 'N' ); //(26-40)[15]: State Quarterly Gross Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 15, 'N' ); //(41-55)[15]: State Quarterly Unemployment Insurance Total Wages for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 15, 'N' ); //(56-70)[15]: State Quarterly Unemployment Insurance Excess for Employer
				$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 15, 'N' ); //(71-85)[15]: State Quarterly Unemployment Insurance Taxable Wages for Employer
				$line[] = $this->padRecord( '', 15, 'N' );            //(86-100)[15]: Quarterly State Disability Insurance Taxable Wages in File
				$line[] = $this->padRecord( '', 15, 'N' );            //(101-115)[15]: Quarterly Tip Wages

				$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 8, 'N' ); //(116-123)[8]: Month 1 employment for employer (total number of employees)
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 8, 'N' ); //(124-131)[8]: Month 2 employment for employer
				$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 8, 'N' ); //(132-139)[8]: Month 3 employment for employer

				$line[] = $this->padRecord( '', 136, 'N' );            //(140-275)[136]: Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 275 ) {
			Debug::Text( 'ERROR! F Record length is incorrect, should be 275 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'F Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ARcompile1E() {
		$max_line_length = 155;

		$line[] = '1E';                                                                                 //*[1-2][2] Record Identifier - Constant “1E”
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //*[3-6][4] Payment Year - Enter the year for which the report is being prepared. Enter numeric characters only. e.g. 2009 is listed as ‘2009’.
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //*[7-15][9] Federal ID Number - Enter only numeric characters. Enter your federal id (EIN) Do NOT list “Applied For”. The DWS nine digit account number is entered in the 2S record 3-14.
		$line[] = $this->padRecord( '', 9, 'AN' );                                                      //[16-24][9] State/Local 69 Number - If not applicable, enter blanks.
		$line[] = $this->padRecord( $this->trade_name, 50, 'AN' );                                      //*[25-74][50] Employer Name - Left justify and fill with blanks.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 40, 'AN' ); //*[75-114][40] Street Address - Left justify and fill with blanks.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                      //[115][1] Foreign Address - If the information shown in positions 75-114 of the Code 1E record and in positions 3-47 of the Code 2E record is for a foreign address (i.e. outside of the U.S. and U.S. territories and possessions, and not APO or FF enter the letter ‘X’ in this field. Otherwise, enter a blank.
		$line[] = $this->padRecord( '', 13, 'AN' );                                                     //[116-128][13] Blank - Enter blanks. Reserved for SSA use.
		$line[] = $this->padRecord( '', 9, 'AN' );                                                      //[129-137][9] Blank - Blank.
		$line[] = $this->padRecord( '', 18, 'AN' );                                                     //[138-155][18] Blank - Blank.

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
			Debug::Text( 'ERROR! 1E Record length is incorrect, should be ' . $max_line_length . ' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '1E Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ARcompile2E() {
		$max_line_length = 155;

		$line[] = '2E';                                                                                //*[1-2][2] Record Identifier - Constant “2E”
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 25, 'AN' );    //*[3-27][25] City - Left justly and fill with blanks. If this is a foreign address, also include the name of the foreign ‘state’, province, etc., e.g., Ontario.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 10, 'AN' );   //*[28-37][10] State - USE standard USPS postal alphabetical abbreviation. If this is a foreign address, include the two-character country code, e.g., CN for Canada. Left justify and fill with blanks.
		$line[] = $this->padRecord( '', 5, 'AN' );                                                     //[38-42][5] ZIP Code Extension - Use this field as necessary for the four-digit extension of the ZIP Code, being sure to include the hyphen in position 38. If this is a foreign address, use this field as necessary for overflow for a foreign postal code begun in positions 43-47; left justify and fill with blanks. If this field is not applicable, enter blanks.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' ); //*[43-47][5] ZIP Code or Foreign Postal Code - Enter a valid ZIP Code. For a foreign address, however, use this field for the Foreign Postal Code, if applicable; left justify and fill with blanks; if necessary, continue the Foreign Postal Code in positions 38-42 above.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                     //[48][1] Name Code - Enter blanks.
		$line[] = $this->padRecord( 'R', 1, 'AN' );                                                    //[49][1] Type of Employment - Enter the appropriate code:A-Agriculture, X-Household, M-Military, Q-Medicare Qualified Government Employment (MQGE),  X-Railroad, R-Regular (All others)   NOTE: This code must correspond to the rate of withholding for social security tax in the associated Code 1W/2W records.
		$line[] = $this->padRecord( '', 2, 'AN' );                                                     //[50-51][2] Blank - Enter blanks. Reserved for SSA use.
		$line[] = $this->padRecord( '', 4, 'AN' );                                                     //[52-55][4] Establishment Number OR Coverage Group (CG)/Payroll Record unit (PRU) - Enter either the Establishment Number or the Coverage Group/Payroll Record Unit number, whichever is applicable. Otherwise, enter blanks.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                     //[56][1] Limitation of Liability (L) Indicator - For Section 218 State/local entities only: If applicable, enter the letter “L”. Otherwise, enter a blank. Refer to SSA Glossary.
		$line[] = $this->padRecord( '', 72, 'AN' );                                                    //[57-128][72] Blank - Enter blanks. Reserved for SSA use.
		$line[] = $this->padRecord( '', 27, 'AN' );                                                    //[129-155][27] Blank - Blank.

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
			Debug::Text( 'ERROR! 2E Record length is incorrect, should be ' . $max_line_length . ' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '2E Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ARcompile1S() {
		$max_line_length = 155;

		$line[] = '1S';                                                                            //*[1-2][2] Record Identifier - Constant “1S”
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //*[3-11][9] Social Security Number - Enter the employee’s social security number.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //*[12-31][20] Employee Last Name - Enter employee’s Last name.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //*[32-46][15] Employee First Name - Enter employee’s First name.
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //[47-61][15] Employee Middle Name - Enter employee’s middle Name.
		$line[] = $this->padRecord( '', 4, 'AN' );                                                 //[62-65][4] Employee Suffix - Enter employee’s Suffix.
		$line[] = $this->padRecord( '', 40, 'AN' );                                                //[66-105][40] Street Address - Left justify and fill with blanks.
		$line[] = $this->padRecord( '', 25, 'AN' );                                                //[106-130][25] City - Left justify and fill with blanks. If this is a foreign address, also include the name of the foreign ‘state’, province, etc., e.g., Ontario.
		$line[] = $this->padRecord( $this->efile_state, 10, 'AN' );                                //[131-140][10] State - Enter the standard USPS postal alphabetical abbreviation. Left justify and fill with blanks. If this is a foreign address, enter the two-character county code, e.g., CN for Canada.
		$line[] = $this->padRecord( '', 5, 'AN' );                                                 //[141-145][5] ZIP Code Extension - Use this field as necessary for the four-digit extension of the ZIP Code, being sure to include the hyphen. If this is a foreign address, use this field as necessary for overflow for a foreign postal code; left justify and fill with blanks. If this field is not applicable, enter blanks.
		$line[] = $this->padRecord( '', 5, 'AN' );                                                 //[146-150][5] ZIP Code or Foreign Postal Code - Enter a valid ZIP Code. For a foreign address, however, use this field for the Foreign Postal Code, if applicable; left justify and fill with blanks; if necessary.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                 //[151][1] Blank - Enter a blank.
		$line[] = $this->padRecord( '05', 2, 'N' );                                                //*[152-153][2] State Code - Enter 05 (Arkansas).
		$line[] = $this->padRecord( '', 2, 'AN' );                                                 //*[154-155][2] Optical Code - Seasonal designation (assigned by DWS). If not seasonal, do not fill – enter blanks.

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
			Debug::Text( 'ERROR! 1S Record length is incorrect, should be ' . $max_line_length . ' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '1S Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _ARcompile2S() {
		$max_line_length = 155;

		$line[] = '2S';                                                                                 					 //*[1-2][2] Record Identifier - Constant “2S”
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 12, 'AN' ); //*[3-14][12] State DWS Account Number - Left justify alignment, enter the nine digit DWS account number (Example: 000123456) and leave last three spaces blank.
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->month_of_year . substr( $this->year, -2 ) ), 4, 'N' );    //*[15-18][4] Reporting Period - Enter the last month and year for the calendar quarter for which this report applies; e.g., ‘0306’ for January-March of 2006; ‘0607’ for April-June of 2007.
		$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 9, 'N' );                                  //*[19-27][9] State Quarterly Unemployment Insurance Total Wages - Right justify and zero fill. More than 7 figures will require breakdown-each set of numbers totaling the full amount. Must be different numbers e.g. 500,000.00 would be listed as 99999.99, 99999.98, 99999.97, 99999.96, 99999.95, &.15.
		$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 9, 'N' );                                  //[28-36][9] State Quarterly Unemployment Insurance Taxable Wages - Right justify and zero fill.
		$line[] = $this->padRecord( '', 2, 'AN' );                                                                           //[37-38][2] Number of Weeks Worked - To be defined by user.
		$line[] = $this->padRecord( '', 4, 'AN' );                                                                           //[39-42][4] Date First Employed - Enter the month and year, e.g.,”0189.”
		$line[] = $this->padRecord( '', 4, 'AN' );                                                                           //[43-46][4] Date Of Separation - Enter the month and year, e.g.,”0599 or 0500.”
		$line[] = $this->padRecord( '', 5, 'AN' );                                                                           //[47-51][5] Taxing Entity Code - To be defined by user.
		$line[] = $this->padRecord( $this->_getStateNumericCode( $this->efile_state ), 2, 'N' );                             //[52-53][2] State Code - Enter the appropriate FIPS Postal NUMERIC code.
		$line[] = $this->padRecord( '', 9, 'AN' );                                                                           //[54-62][9] State Taxable Wages - Right justify and zero fill.
		$line[] = $this->padRecord( '', 8, 'AN' );                                                                           //[63-70][8] State Income Tax Withheld - Right justify and zero fill.
		$line[] = $this->padRecord( '', 10, 'AN' );                                                                          //[71-80][10] Other State Data - To be defined by individual taxing agencies.
		$line[] = $this->padRecord( '', 1, 'AN' );                                                                           //[81][1] Tax Type Code - Enter the appropriate code for entries in positions 87-95 and 96-102: C-City Income Tax, D-County Income Tax, E-School District Income Tax, F-Other Income Tax
		$line[] = $this->padRecord( '', 5, 'AN' );                                                                           //[82-86][5] Taxing Entity Code - To be defined by individual taxing agencies.
		$line[] = $this->padRecord( '', 9, 'AN' );                                                                           //[87-95][9] Local Taxable Wages - To be defined by individual taxing agencies.
		$line[] = $this->padRecord( '', 7, 'AN' );                                                                           //[96-102][7] Local Income Tax Withheld - To be defined by individual taxing agencies.
		$line[] = $this->padRecord( '', 7, 'AN' );                                                                           //[103-109][7] State Control Number - Optional.
		$line[] = $this->padRecord( '', 16, 'AN' );                                                                          //[110-125][16] Blank - Enter blanks OR for employer use. (See note below)
		$line[] = $this->padRecord( $this->paid_12th_day_month1, 1, 'AN' );                                                  //*[126][1] Month 1 Employment - Enter “1” if worked during or received pay for pay period including the 12th of the month; or Enter “O” if did not work and received no pay for pay period including the 12th of the month.
		$line[] = $this->padRecord( $this->paid_12th_day_month2, 1, 'AN' );                                                  //*[127][1] Month 2 Employment - See month 1 instructions.
		$line[] = $this->padRecord( $this->paid_12th_day_month3, 1, 'AN' );                                                  //*[128][1] Month 3 Employment - See month 1 instructions.
		$line[] = $this->padRecord( '', 9, 'AN' );                                                                           //[129-137][9] Individual Out Of State Wages - Right justify and zero fill. Maximum amount is $9,999,999.99. i.e.: If the out of state excess wages is 9,845.00, the value should be 000984500.
		$line[] = $this->padRecord( '', 2, 'AN' );                                                                           //[138-139][2] Individual Out Of State Wages State - Required when Individual Out Of State Excess Wages is provided. Enter the standard USPS postal alphabetical abbreviation. Left justify and fill with blanks. If this is a foreign address, enter the two-character county code, e.g., CN for Canada.
		$line[] = $this->padRecord( '', 16, 'AN' );                                               						     //[140-155][16] Blank - Enter a blank.

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != $max_line_length ) {
			Debug::Text( 'ERROR! 2S Record length is incorrect, should be ' . $max_line_length . ' is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '2S Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//MI eFile Format: https://www.michigan.gov/-/media/Project/Websites/leo/Documents/UIA/Publications/MiWAM_Employer_Toolkit__Wage_File_Formatssecured.pdf?rev=b6d646279b1344caa45d27d3d19696ac
	function _MIcompileE( $total ) {
		$line[] = 'E';                                                                                  //(1)[1] E Record

		switch ( strtolower( $this->efile_state ) ) {
			default:
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' );    //(2-8)[7]: UIA Account Number.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->multi_unit_number ), 3, 'N' );	//(9-11)[3]: UIA Multi Unit Number. May be all zeros.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );    			//(12-15)[4]: Filing year in CCYY form. CCYY is the year with century, ie: 2012
				$line[] = $this->padRecord( $this->quarter_of_year, 1, 'N' ); 								//(16)[1]: Filing Quarter
				$line[] = $this->padRecord( '', 24, 'AN' );                                                 //(17-40)[24] Blank
				$line[] = $this->padRecord( $total->total, 7, 'N' );  										//(41-47)[7] Total number of wage detail records. (S, O and V) Can be zero.
				$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 11, 'N' ); 		//(48-60)[11]: Total Gross Wages from wage detail records (S, O, and V)
				$line[] = $this->padRecord( '', 12, 'AN' );                                                 //(61-72)[12] Blank
				break;

		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 70 ) {
			Debug::Text( 'ERROR! E Record length is incorrect, should be 70 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'E Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
	function _MIcompileS() {
		Debug::Text( 'S Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );

		$line[] = 'S';                                                                                    //(1)[1]: S Record
		switch ( strtolower( $this->efile_state ) ) {
			default:
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' );    //(2-8)[7]: UIA Account Number.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->multi_unit_number ), 3, 'N' );   //(9-11)[3]: UIA Multi Unit Number. May be all zeros.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );    			//(12-15)[4]: Filing year in CCYY form. CCYY is the year with century, ie: 2012
				$line[] = $this->padRecord( $this->quarter_of_year, 1, 'N' ); 								//(16)[1]: Filing Quarter
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                 //(17-25)[9]: SSN
				$line[] = $this->padRecord( '', 7, 'AN' );                                                         //(26-32)[7]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 16, 'AN' );          //(33-48)[16]: Last Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 12, 'AN' );         //(49-60)[12]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(61)[1]: Middle Initial
				$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 8, 'N' );               //(62-71)[8]: State Quarter Total Gross Wages
				$line[] = $this->padRecord( ' ', 1, 'AN' );               //(72)[1]: Family Status indicator. 'F' if family member, blank otherwise. Leave blank unless you are family owned business in which the majority interest is owned by the employee, spouse, child, or parent.
				break;
		}

		if ( isset( $line ) ) {
			$retval = implode( ( $this->debug == true ) ? ',' : '', $line );
			if ( $this->debug == false && strlen( $retval ) != 70 ) {
				Debug::Text( 'ERROR! S Record length is incorrect, should be 70 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			Debug::Text( 'S Record: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else {
			Debug::Text( 'Skipping RS Record... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}


	// FL file format
	//   Records: 00, 01, 02, 03, 99
	function _FLcompile00() {
		$line[] = '00';                                                                                                                                                  //00 Record
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //(3-11)[9] Transmitter EIN
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' );    											//(12-18)[7]: Account Number assigned by State of Florida

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 18 ) {
			Debug::Text( 'ERROR! 00 Record length is incorrect, should be 18 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '00 Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _FLcompile01( $total ) {
		$line[] = '01';                                                                                  //(1-2)[2] 01 Record
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(3-11)[9] EIN
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->stripSpaces( $this->state_primary_id ) ), 7, 'N' );    											//(12-18)[7]: Account Number assigned by State of Florida
		$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                      //(19-75)[57] Company Name
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' ); //(76-97)[22] Company Street Address
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );     //(98-119)[22] Company City
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(120-121)[2] Company State
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ).'9999', 9, 'AN' );  //(122-130)[9] Company Zip Code Must contain nine digits (ZIP+4). May be padded with 9s if the last four digits are not available.
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' ); 	        //(131-134)[4]: Tax Year
		$line[] = $this->padRecord( $this->quarter_of_year, 2, 'N' ); //(135-136)[2]: Filing Quarter

		$line[] = $this->padRecord( $this->removeDecimal( $total->subject_wages ), 15, 'N' ); //(137-151)[15]: State Quarterly Gross Wages for Employer
		$line[] = $this->padRecord( $this->removeDecimal( $total->excess_wages ), 11, 'N' ); //(152-162)[11]: State Quarterly Unemployment Insurance Excess for Employer
		$line[] = $this->padRecord( $this->removeDecimal( $total->taxable_wages ), 15, 'N' ); //(163-177)[15]: State Quarterly Unemployment Insurance Taxable Wages for Employer
		$line[] = $this->padRecord( $this->removeDecimal( $total->tax_withheld ), 11, 'N' );  //(178-188)[11]: Tax Withheld
		$line[] = $this->padRecord( 0, 9, 'N' );  //(189-197)[9]: Interest Due
		$line[] = $this->padRecord( 0, 9, 'N' );  //(198-206)[9]: Penalty Due
		$line[] = $this->padRecord( $this->removeDecimal( $total->tax_withheld ), 11, 'N' );  //(207-217)[11]: Total Tax Due with interest and penalty
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );                                                                     //(218-244)[27] Contact Name
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 10, 'AN' );                                                                         //(245-254)[10] Contact Phone
		$line[] = $this->padRecord( $this->contact_email, 50, 'AN' );                                                                                                   //(255-304)[50] Contact Email

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 304 ) {
			Debug::Text( 'ERROR! 01 Record length is incorrect, should be 304 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '01 Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
	function _FLcompile02() {
		Debug::Text( '02 Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );

		$line[] = '02';                                                                                    //(1-2)[2]: 02 Record
		$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                       //(3-11)[9]: SSN
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );         //(12-26)[15]: First Name
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 1, 'AN' );         //(27)[1]: Middle Initial
		$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );          //(28-47)[20]: Last Name
		$line[] = $this->padRecord( $this->removeDecimal( $this->subject_wages ), 11, 'N' );               //(48-58)[11]: State Quarter Total Gross Wages
		$line[] = $this->padRecord( $this->removeDecimal( $this->taxable_wages ), 11, 'N' );               //(59-69)[11]: State Quarter Unemployment Insurance Taxable Wages
		$line[] = $this->padRecord( 0, 11, 'N' );               //(70-80)[11]: TODO: Out-of-State State Quarter Total Gross Wages
		$line[] = $this->padRecord( 0, 11, 'N' );               //(81-91)[11]: TODO: Out-of-State Quarter Unemployment Insurance Taxable Wages
		$line[] = $this->padRecord( 'FL', 2, 'AN' );            //(92-93)[2]: TODO: Tax State. If no out of state wages were provided, use 'FL'. If multiple states were provided use 'MU'.
		$line[] = $this->padRecord( 0, 11, 'N' );               //(94-104)[11]: Total wages paid under a contract to an educational institution.

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );
		if ( $this->debug == false && strlen( $retval ) != 104 ) {
			Debug::Text( 'ERROR! 02 Record length is incorrect, should be 104 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( '02 Record: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _FLcompile03( $total ) {
		$line[] = '03';                                          //03 Record
		$line[] = $this->padRecord( floor( $total->paid_12th_day_month1 ), 7, 'N' ); //(3-9)[7]: Month 1 employment for employer (total number of employees)
		$line[] = $this->padRecord( floor( $total->paid_12th_day_month2 ), 7, 'N' ); //(10-16)[7]: Month 2 employment for employer
		$line[] = $this->padRecord( floor( $total->paid_12th_day_month3 ), 7, 'N' ); //(17-23)[7]: Month 3 employment for employer

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 23 ) {
			Debug::Text( 'ERROR! 03 Record length is incorrect, should be 23 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( '03 Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
	function _FLcompile99( $total ) {
		if ( defined( 'UNIT_TEST_MODE' ) && UNIT_TEST_MODE == true ) {
			$media_creation_time = strtotime('31-Dec-2022');
		} else {
			$media_creation_time = time();
		}

		$line[] = '99';                                          //99 Record
		$line[] = $this->padRecord( $this->stripNonNumeric( TTDate::getISODateStamp( $media_creation_time ) ), 8, 'N' );                                                       //(3-10)[8] Date the file is submitted in YYYYMMDD

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 10 ) {
			Debug::Text( 'ERROR! 99 Record length is incorrect, should be 10 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( '99 Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}


	function _outputEFILE( $type = null ) {
		/*
		 Submitter Record (RA)
		 Employer Record (RE)
		 State Wage Record (RS)
		 Final Record (RF)

		 Publication 42-007: http://www.ssa.gov/employer/EFW2&EFW2C.htm

		 Download: AccuWage from the bottom of this website for testing: http://www.socialsecurity.gov/employer/accuwage/index.html
		 */

		$records = $this->getRecords();

		//Debug::Arr($records, 'Output EFILE Records: ',__FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $records ) && count( $records ) > 0 ) {

			if ( in_array( strtolower( $this->efile_state ), [ 'la', 'md', 'mo', 'ms', 'ne', 'or', 'sc', 'ut', 'va' ] ) == true ) { //MMREF states
				$retval = $this->padLine( $this->_compileRA() );
				$retval .= $this->padLine( $this->_compileRE() );

				$state_total = (object)TTMath::ArrayAssocSum( $records, null, 8 );
				$state_total->total = 0;

				$i = 0;
				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object

					$compile_rs_retval = $this->padLine( $this->_compileRS() ); //This also excludes RS records for other states, but we need make sure we only consider totals for just this state too.

					if ( $compile_rs_retval != '' ) {
						$retval .= $compile_rs_retval;

						$state_total->total += 1;
					}

					$this->revertToOriginalDataState();

					$i++;
				}

				$retval .= $this->padLine( $this->_compileRT( $state_total ) );
				$retval .= $this->padLine( $this->_compileRF( $state_total ) );
				//CSV: 'ca', 'il'
				//XML: 'fl'
				//TIB-4: 'ny'
				//Custom to state: 'ak', 'ar', 'ma', 'me', 'mi', 'nd', 'nh', 'nj', 'nm', 'nv', 'pr', 'ri', 'vt'
			} else if ( in_array( strtolower( $this->efile_state ), [ 'fl' ] ) == true ) { //Custom fixed width format: https://floridarevenue.com/taxes/Documents/flRtImpSpecs.pdf
				$retval = $this->padLine( $this->_FLcompile00() );

				//$state_total = Misc::preSetArrayValues( new stdClass(), [ 'total', 'subject_wages', 'excess_wages', 'taxable_wages', 'paid_12th_day_month1', 'paid_12th_day_month2', 'paid_12th_day_month3' ], 0 );
				$state_total = (object)TTMath::ArrayAssocSum( $records, null, 8 );
				$state_total->total = 0;


				$employee_retval = '';

				$i = 0;
				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object

					$compile_rs_retval = $this->padLine( $this->_FLcompile02() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.

					if ( $compile_rs_retval != '' ) {
						$employee_retval .= $compile_rs_retval;

						$state_total->total += 1;
					}

					$this->revertToOriginalDataState();

					$i++;
				}

				$retval .= $this->padLine( $this->_FLcompile01( $state_total ) ) . $employee_retval; //Requires totals to be generated

				$retval .= $this->padLine( $this->_FLcompile03( $state_total ) );
				$retval .= $this->padLine( $this->_FLcompile99( $state_total ) );
			} else if ( in_array( strtolower( $this->efile_state ), [ 'az', 'ia', 'wi' ] ) == true ) { //CSV
				$retval = '';

				$i = 0;
				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object
					$retval .= $this->_compileCSV( $i );
					$this->revertToOriginalDataState();

					$i++;
				}
			} else if ( in_array( strtolower( $this->efile_state ), [ 'mi' ] ) == true ) { //Custom fixed width format: Page 27 of MiWAM Tooplkit for Employers - https://www.michigan.gov/leo/-/media/Project/Websites/leo/Agencies/UIA/Publications/MiWAM-Toolkit-for-Employers-Part-1-9-30-21.pdf?rev=bdc01ff13781491998881cc36a2aa2c2&hash=1064D512496F2056814F61E3ABCFDCC0
				$retval = '';

				$state_total = (object)TTMath::ArrayAssocSum( $records, null, 8 );
				$state_total->total = 0;

				$i = 0;
				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object

					$compile_rs_retval = $this->padLine( $this->_MIcompileS() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.

					if ( $compile_rs_retval != '' ) {
						$retval .= $compile_rs_retval;

						$state_total->total += 1;
					}

					$this->revertToOriginalDataState();

					$i++;
				}

				$retval = $this->padLine( $this->_MIcompileE( $state_total ) ).$retval; //Specific to NC and must go at the beginning, but requires totals to be included, as handle it at the end.
			} else if ( in_array( strtolower( $this->efile_state ), [ 'ar' ] ) == true ) { //AR - https://dws.arkansas.gov/workforce-services/employers/magnetic-media-for-ui-wage-reporting-diskette/
				$retval = $this->padLine( $this->_ARcompile1E() );
				$retval .= $this->padLine( $this->_ARcompile2E() );

				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object

					$compile_1s_retval = $this->padLine( $this->_ARcompile1S() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.
					$compile_2s_retval = $this->padLine( $this->_ARcompile2S() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.

					if ( $compile_1s_retval != '' && $compile_2s_retval != '' ) {
						$retval .= $compile_1s_retval;
						$retval .= $compile_2s_retval;
					}

					$this->revertToOriginalDataState();
				}
			} else { //ICESA states: 'al', 'co', 'ct', 'dc', 'ga', 'in', 'ks', 'ky', 'me', 'mn', 'nc', 'oh', 'ok', 'pa', 'tn', 'tx' (TX only supports ICESA through their Quick File app. Its CSV only through the web interface)
				$retval = $this->padLine( $this->_ICESAcompileA() );
				$retval .= $this->padLine( $this->_ICESAcompileB() );
				$retval .= $this->padLine( $this->_ICESAcompileE() );

				//$state_total = Misc::preSetArrayValues( new stdClass(), [ 'total', 'subject_wages', 'excess_wages', 'taxable_wages', 'paid_12th_day_month1', 'paid_12th_day_month2', 'paid_12th_day_month3' ], 0 );
				$state_total = (object)TTMath::ArrayAssocSum( $records, null, 8 );
				$state_total->total = 0;


				$i = 0;
				foreach ( $records as $ui_data ) {
					$this->arrayToObject( $ui_data ); //Convert record array to object

					$compile_rs_retval = $this->padLine( $this->_ICESAcompileS() ); //This also excludes RS records for other states, but we need make we only consider totals for just this state too.

					if ( $compile_rs_retval != '' ) {
						$retval .= $compile_rs_retval;

						$state_total->total += 1;
					}

					$this->revertToOriginalDataState();

					$i++;
				}

				$retval .= $this->padLine( $this->_ICESAcompileT( $state_total ) );
				$retval .= $this->padLine( $this->_ICESAcompileF( $state_total ) );

				$retval = $this->padLine( $this->_ICESAcompileN( $state_total ) ).$retval; //Specific to NC and must go at the beginning, but requires totals to be included, as handle it at the end.
			}
		}

		if ( isset( $retval ) ) {
			return $retval;
		}

		return false;
	}
}

?>