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

use GeoIp2\Database\Reader;

class GeoIP {

	protected $reader_obj = null;
	protected $geoip_record = null;

	function __construct( $ip_address = false, $db_file_name = null ) {
		try {
			//Sources:
			//  https://db-ip.com/db/download/ip-to-city-lite -- https://download.db-ip.com/free/dbip-city-lite-2023-08.mmdb.gz
			//  https://www.maxmind.com/en/account/login
			if ( $db_file_name == null ) {
				$db_file_name = __DIR__ . DIRECTORY_SEPARATOR . 'geoip.mmdb';
			}

			Debug::Text( 'MMDB File: ' . $db_file_name, __FILE__, __LINE__, __METHOD__, 10 );
			$this->reader_obj = new Reader( $db_file_name );

			if ( $ip_address != '' ) {
				$this->getDataForIP( $ip_address );
			}

			return true;
		} catch ( Exception $e ) {
			Debug::Text( 'ERROR: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	function getGEOIPObject() {
		return $this->geoip_record;
	}

	function getDataForIP( $ip_address ) {
		try {
			if ( is_object( $this->reader_obj ) ) {
				$this->geoip_record = $this->reader_obj->city( $ip_address );

				//Debug::Arr($this->geoip_record, 'Raw GEOIP Data for: '. $ip_address, __FILE__, __LINE__, __METHOD__,10);
				return $this->geoip_record;
			}
		} catch ( Exception $e ) {
			Debug::Text( 'Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return false;
	}

	function getCountryCode( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->country->isoCode ) ) {
			$country = $this->getGEOIPObject()->country->isoCode;

			//Check for invalid countries that may be returned, like those used for proxies or such.
			if ( !in_array( strtoupper( $country ), [ 'EU', 'AP', 'A1', 'A2', 'O1' ] ) ) {
				return $country;
			}
		}

		return false;
	}

	function getCountryName( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->country->isoCode ) ) {
			$country = $this->getGEOIPObject()->country->isoCode;

			//Check for invalid countries that may be returned, like those used for proxies or such.
			if ( !in_array( strtoupper( $country ), [ 'EU', 'AP', 'A1', 'A2', 'O1' ] ) ) {
				return $this->getGEOIPObject()->country->name;
			}
		}

		return false;
	}

	function getProvince( $ip_address ) {
		return $this->getRegionCode( $ip_address );
	}

	function getRegionCode( $ip_address ) {
		//if ( $this->getDataForIP( $ip_address ) !== FALSE AND isset($this->getGEOIPObject()->mostSpecificSubdivision->isoCode) ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->subdivisions[0] ) ) {
			$country = $this->getCountryCode( $ip_address );

			$cf = new CompanyFactory();
			$province_options = $cf->getOptions( 'province', strtoupper( $country ) );
			if ( is_array( $province_options ) && !empty( $province_options ) ) {
				return array_search( strtolower( $this->getGEOIPObject()->subdivisions[0]->name ), array_map( 'strtolower', $province_options ) );
			}
			//return $this->getGEOIPObject()->subdivisions[0]->StateProvCode;
			//return $this->getGEOIPObject()->subdivisions[0]->name; //DB-IP does not appear to return isoCode or abbreviations currently.
		}

		return false;
	}

	function getCity( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->city->name ) ) {
			$retval = trim( preg_replace( '/\(.*?\)/', '', $this->getGEOIPObject()->city->name ) ); //Strip everything within brackets. ie: Toronto (West) => Toronto

			return $retval;
		}

		return false;
	}

	function getPostalCode( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->postal->code ) ) {
			return $this->getGEOIPObject()->postal->code;
		}

		return false;
	}


	function getLatitude( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->location->latitude ) ) {
			return $this->getGEOIPObject()->location->latitude;
		}

		return false;
	}

	function getLongitude( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->location->longitude ) ) {
			return $this->getGEOIPObject()->location->longitude;
		}

		return false;
	}

	function getMetroCode( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->location->metroCode ) ) {
			return $this->getGEOIPObject()->location->metroCode;
		}

		return false;
	}

	function getAreaCode( $ip_address ) {
		return $this->getMetroCode( $ip_address );
	}

	function getTimeZone( $ip_address ) {
		if ( $this->getDataForIP( $ip_address ) !== false and isset( $this->getGEOIPObject()->location->timeZone ) ) {
			return $this->getGEOIPObject()->location->timeZone;
		}

		return false;
	}

	function getContinent( $country_code ) {
		$country_code = strtoupper( $country_code );

		$map = [
				'A1' => false,
				'A2' => false,
				'AD' => 'EU',
				'AE' => 'AS',
				'AF' => 'AS',
				'AG' => 'NA',
				'AI' => 'NA',
				'AL' => 'EU',
				'AM' => 'AS',
				'AN' => 'NA',
				'AO' => 'AF',
				'AP' => 'AS',
				'AQ' => 'AN',
				'AR' => 'SA',
				'AS' => 'OC',
				'AT' => 'EU',
				'AU' => 'OC',
				'AW' => 'NA',
				'AX' => 'EU',
				'AZ' => 'AS',
				'BA' => 'EU',
				'BB' => 'NA',
				'BD' => 'AS',
				'BE' => 'EU',
				'BF' => 'AF',
				'BG' => 'EU',
				'BH' => 'AS',
				'BI' => 'AF',
				'BJ' => 'AF',
				'BL' => 'NA',
				'BM' => 'NA',
				'BN' => 'AS',
				'BO' => 'SA',
				'BR' => 'SA',
				'BS' => 'NA',
				'BT' => 'AS',
				'BV' => 'AN',
				'BW' => 'AF',
				'BY' => 'EU',
				'BZ' => 'NA',
				'CA' => 'NA',
				'CC' => 'AS',
				'CD' => 'AF',
				'CF' => 'AF',
				'CG' => 'AF',
				'CH' => 'EU',
				'CI' => 'AF',
				'CK' => 'OC',
				'CL' => 'SA',
				'CM' => 'AF',
				'CN' => 'AS',
				'CO' => 'SA',
				'CR' => 'NA',
				'CU' => 'NA',
				'CV' => 'AF',
				'CX' => 'AS',
				'CY' => 'AS',
				'CZ' => 'EU',
				'DE' => 'EU',
				'DJ' => 'AF',
				'DK' => 'EU',
				'DM' => 'NA',
				'DO' => 'NA',
				'DZ' => 'AF',
				'EC' => 'SA',
				'EE' => 'EU',
				'EG' => 'AF',
				'EH' => 'AF',
				'ER' => 'AF',
				'ES' => 'EU',
				'ET' => 'AF',
				'EU' => 'EU',
				'FI' => 'EU',
				'FJ' => 'OC',
				'FK' => 'SA',
				'FM' => 'OC',
				'FO' => 'EU',
				'FR' => 'EU',
				'FX' => 'EU',
				'GA' => 'AF',
				'GB' => 'EU',
				'GD' => 'NA',
				'GE' => 'AS',
				'GF' => 'SA',
				'GG' => 'EU',
				'GH' => 'AF',
				'GI' => 'EU',
				'GL' => 'NA',
				'GM' => 'AF',
				'GN' => 'AF',
				'GP' => 'NA',
				'GQ' => 'AF',
				'GR' => 'EU',
				'GS' => 'AN',
				'GT' => 'NA',
				'GU' => 'OC',
				'GW' => 'AF',
				'GY' => 'SA',
				'HK' => 'AS',
				'HM' => 'AN',
				'HN' => 'NA',
				'HR' => 'EU',
				'HT' => 'NA',
				'HU' => 'EU',
				'ID' => 'AS',
				'IE' => 'EU',
				'IL' => 'AS',
				'IM' => 'EU',
				'IN' => 'AS',
				'IO' => 'AS',
				'IQ' => 'AS',
				'IR' => 'AS',
				'IS' => 'EU',
				'IT' => 'EU',
				'JE' => 'EU',
				'JM' => 'NA',
				'JO' => 'AS',
				'JP' => 'AS',
				'KE' => 'AF',
				'KG' => 'AS',
				'KH' => 'AS',
				'KI' => 'OC',
				'KM' => 'AF',
				'KN' => 'NA',
				'KP' => 'AS',
				'KR' => 'AS',
				'KW' => 'AS',
				'KY' => 'NA',
				'KZ' => 'AS',
				'LA' => 'AS',
				'LB' => 'AS',
				'LC' => 'NA',
				'LI' => 'EU',
				'LK' => 'AS',
				'LR' => 'AF',
				'LS' => 'AF',
				'LT' => 'EU',
				'LU' => 'EU',
				'LV' => 'EU',
				'LY' => 'AF',
				'MA' => 'AF',
				'MC' => 'EU',
				'MD' => 'EU',
				'ME' => 'EU',
				'MF' => 'NA',
				'MG' => 'AF',
				'MH' => 'OC',
				'MK' => 'EU',
				'ML' => 'AF',
				'MM' => 'AS',
				'MN' => 'AS',
				'MO' => 'AS',
				'MP' => 'OC',
				'MQ' => 'NA',
				'MR' => 'AF',
				'MS' => 'NA',
				'MT' => 'EU',
				'MU' => 'AF',
				'MV' => 'AS',
				'MW' => 'AF',
				'MX' => 'NA',
				'MY' => 'AS',
				'MZ' => 'AF',
				'NA' => 'AF',
				'NC' => 'OC',
				'NE' => 'AF',
				'NF' => 'OC',
				'NG' => 'AF',
				'NI' => 'NA',
				'NL' => 'EU',
				'NO' => 'EU',
				'NP' => 'AS',
				'NR' => 'OC',
				'NU' => 'OC',
				'NZ' => 'OC',
				'O1' => false,
				'OM' => 'AS',
				'PA' => 'NA',
				'PE' => 'SA',
				'PF' => 'OC',
				'PG' => 'OC',
				'PH' => 'AS',
				'PK' => 'AS',
				'PL' => 'EU',
				'PM' => 'NA',
				'PN' => 'OC',
				'PR' => 'NA',
				'PS' => 'AS',
				'PT' => 'EU',
				'PW' => 'OC',
				'PY' => 'SA',
				'QA' => 'AS',
				'RE' => 'AF',
				'RO' => 'EU',
				'RS' => 'EU',
				'RU' => 'EU',
				'RW' => 'AF',
				'SA' => 'AS',
				'SB' => 'OC',
				'SC' => 'AF',
				'SD' => 'AF',
				'SE' => 'EU',
				'SG' => 'AS',
				'SH' => 'AF',
				'SI' => 'EU',
				'SJ' => 'EU',
				'SK' => 'EU',
				'SL' => 'AF',
				'SM' => 'EU',
				'SN' => 'AF',
				'SO' => 'AF',
				'SR' => 'SA',
				'ST' => 'AF',
				'SV' => 'NA',
				'SY' => 'AS',
				'SZ' => 'AF',
				'TC' => 'NA',
				'TD' => 'AF',
				'TF' => 'AN',
				'TG' => 'AF',
				'TH' => 'AS',
				'TJ' => 'AS',
				'TK' => 'OC',
				'TL' => 'AS',
				'TM' => 'AS',
				'TN' => 'AF',
				'TO' => 'OC',
				'TR' => 'EU',
				'TT' => 'NA',
				'TV' => 'OC',
				'TW' => 'AS',
				'TZ' => 'AF',
				'UA' => 'EU',
				'UG' => 'AF',
				'UM' => 'OC',
				'US' => 'NA',
				'UY' => 'SA',
				'UZ' => 'AS',
				'VA' => 'EU',
				'VC' => 'NA',
				'VE' => 'SA',
				'VG' => 'NA',
				'VI' => 'NA',
				'VN' => 'AS',
				'VU' => 'OC',
				'WF' => 'OC',
				'WS' => 'OC',
				'YE' => 'AS',
				'YT' => 'AF',
				'ZA' => 'AF',
				'ZM' => 'AF',
				'ZW' => 'AF',
		];
		if ( isset( $map[$country_code] ) ) {
			return $map[$country_code];
		}

		return false;
	}
}

?>
