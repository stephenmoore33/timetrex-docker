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
 * @package Core
 */
class Validator {
	private $num_errors = 0;   //Number of errors.
	private $num_warnings = 0; //Number of errors.
	private $errors = [];      //Array of errors.
	private $warnings = [];    //Array of errors.
	private $verbosity = 8;

	public $validate_only = false;

	/**
	 * Checks a result set for one or more rows.
	 * @param $label
	 * @param $rs
	 * @param null $msg
	 * @return bool
	 */
	function isResultSetWithRows( $label, $rs, $msg = null ) {
		//Debug::Arr($rs, 'ResultSet: ', __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( is_object( $rs ) ) {
			if ( isset( $rs->rs ) && is_object( $rs->rs ) && isset( $rs->rs->_numOfRows ) && $rs->rs->_numOfRows > 0 ) {
				return true;
			}
		}

		$this->Error( $label, $msg );

		return false;
	}

	/**
	 * @param $label
	 * @param $rs
	 * @param null $msg
	 * @return bool
	 */
	function isNotResultSetWithRows( $label, $rs, $msg = null ) {
		//Debug::Arr($rs, 'ResultSet: ', __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( is_object( $rs ) ) {
			if ( isset( $rs->rs ) && is_object( $rs->rs ) && isset( $rs->rs->_numOfRows ) && $rs->rs->_numOfRows > 0 ) {
				$this->Error( $label, $msg );

				return false;
			}
			//foreach($rs as $result) {
			//	$this->Error($label, $msg);
			//	unset($result); // code standards
			//	return FALSE;
			//}
		}

		return true;
	}

	/**
	 * Function to simple set an error.
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isTrue( $label, $value, $msg = null ) {
		if ( $value == true ) {
			return true;
		}

		$this->Error( $label, $msg, (int)$value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isFalse( $label, $value, $msg = null ) {
		if ( $value == false ) {
			return true;
		}

		$this->Error( $label, $msg, (int)$value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isNull( $label, $value, $msg = null ) {
		//Debug::text('Value: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $value == null ) {
			return true;
		}

		$this->Error( $label, $msg, (int)$value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isNotNull( $label, $value, $msg = null ) {
		//Debug::text('Value: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $value != null ) { //== NULL matches on (float)0.0
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $array
	 * @return bool
	 */
	function inArrayValue( $label, $value, $msg, $array ) {
		//Debug::text('Value: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( is_array( $array ) && in_array( $value, array_values( $array ) ) ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $key
	 * @param $msg
	 * @param $array
	 * @return bool
	 */
	function inArrayKey( $label, $key, $msg, $array ) {
		//Debug::text('Key: '. $key, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		//Debug::Arr($array, 'isArrayKey Array:', __FILE__, __LINE__, __METHOD__, $this->verbosity);
		if ( is_array( $array ) && in_array( $key, array_keys( $array ) ) ) {
			return true;
		}

		$this->Error( $label, $msg, $key );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isDigits( $label, $value, $msg = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( ctype_digit( trim( $value ) ) == true ) { //Must be *only* digits.
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isNumeric( $label, $value, $msg = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		//if ( preg_match('/^[-0-9]+$/', $value) ) {
		if ( is_numeric( $value ) == true ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isAlphaNumeric( $label, $value, $msg = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( preg_match('/^[A-Za-z0-9]+$/', $value) ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isUUID( $label, $value, $msg = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		//Benchmarking proved that this method is faster than ctype_alnum()
		//this regex is duplicated into Factory::setID()
		if ( TTUUID::isUUID( $value ) == true ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param null $max
	 * @return bool
	 */
	function isLessThan( $label, $value, $msg = null, $max = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $max === null || $max === '' ) {
			$max = PHP_INT_MAX;
		}

		if ( $value <= $max ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param null $min
	 * @return bool
	 */
	function isGreaterThan( $label, $value, $msg = null, $min = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $min === null || $min === '' ) {
			$min = ( -1 * PHP_INT_MAX );
		}

		if ( $value >= $min ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}


	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isFloat( $label, $value, $msg = null ) {
		//Debug::Text('Value:'. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		//Don't use TTi18n::parseFloat() here, as if we are going to be doing that we should do it as early as possible to the user input, like in setObjectFromArray()
		//  We do need to check if the value passed in is already cast to float/int and just accept it in that case.
		//    Because in other locales preg_match() casts $value to a string, which means decimal could become a comma, then it won't match.
		//    Allow commas and decimals to be accepted as floats, as parseFloat() should almost always be called after this function, and it accepts both in all locales.
		if ( ( is_float( $value ) == true || is_int( $value ) === true ) || preg_match( '/^(([\.,][0-9]+)|([-0-9]+([\.,\ 0-9]*)?))$/', trim( $value ) ) === 1 ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $regex
	 * @return bool
	 */
	function isRegEx( $label, $value, $msg, $regex ) {
		$value = (string)$value;

		//Debug::text('Value: '. $value .' RegEx: '. $regex, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		if ( preg_match( $regex, $value ) ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $regex
	 * @return bool
	 */
	function isNotRegEx( $label, $value, $msg, $regex ) {
		//Debug::text('Value: '. $value .' RegEx: '. $regex, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		if ( preg_match( $regex, $value ) == false ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	function isLength( $label, $value, $msg = null, $min = 1, $max = 255 ) {
		$value = (string)$value;

		$len = strlen( $value );

		//Debug::text('Value: '. $value .' Length: '. $len .' Min: '. $min .' Max: '. $max, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $len < $min || $len > $max ) {
			$this->Error( $label, $msg, $value );

			return false;
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	function isLengthBeforeDecimal( $label, $value, $msg = null, $min = 1, $max = 255 ) {
		$before_decimal = TTMath::getBeforeDecimal( $value );
		if ( $before_decimal !== false ) {
			$len = strlen( TTMath::getBeforeDecimal( $value ) );
		} else {
			$len = false;
		}

		//Debug::text('Value: '. $value .' Length: '. $len .' Min: '. $min .' Max: '. $max, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $len === false || $len < $min || $len > $max ) {
			$this->Error( $label, $msg, $value );

			return false;
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	function isLengthAfterDecimal( $label, $value, $msg = null, $min = 1, $max = 255 ) {
		$len = strlen( TTMath::getAfterDecimal( $value, false ) );

		//Debug::text('Value: '. $value .' Length: '. $len .' Min: '. $min .' Max: '. $max, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( $len < $min || $len > $max ) {
			$this->Error( $label, $msg, $value );

			return false;
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isUniqueCharacters( $label, $value, $msg = null ) {
		//Check for unique characters and not consecutive characters.
		//This will fail on:
		// aaaaaaa
		// bbbbbbb
		// abc
		// xyz
		if ( strlen( $value ) > 2 ) {
			$char_arr = str_split( strtolower( $value ) );
			$prev_char_int = ord( $char_arr[0] );
			foreach ( $char_arr as $char ) {
				$curr_char_int = ord( $char );
				if ( abs( $prev_char_int - $curr_char_int ) > 1 ) {
					return true;
				}
				$prev_char_int = $curr_char_int;
			}

			$this->Error( $label, $msg, $value );

			return false;
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param bool $max_duplicate_percent
	 * @param bool $consecutive_only
	 * @return bool
	 */
	function isDuplicateCharacters( $label, $value, $msg = null, $max_duplicate_percent = false, $consecutive_only = false ) {
		if ( strlen( $value ) > 2 && $max_duplicate_percent != false ) {
			$duplicate_chars = 0;

			$char_arr = str_split( strtolower( $value ) );
			$prev_char_int = ord( $char_arr[0] );
			foreach ( $char_arr as $char ) {
				$curr_char_int = ord( $char );
				if ( abs( $prev_char_int - $curr_char_int ) > 1 ) {
					if ( $consecutive_only == true ) {
						$duplicate_chars = 0; //Reset duplicate count.
					}
				} else {
					$duplicate_chars++;
				}
				$prev_char_int = $curr_char_int;
			}

			$duplicate_percent = ( ( $duplicate_chars / strlen( $value ) ) * 100 );
			Debug::text( 'Duplicate Chars: ' . $duplicate_chars . ' Percent: ' . $duplicate_percent . ' Max Percent: ' . $max_duplicate_percent . ' Consec: ' . (int)$consecutive_only, __FILE__, __LINE__, __METHOD__, $this->verbosity );

			if ( $duplicate_percent < $max_duplicate_percent ) {
				return true;
			}

			$this->Error( $label, $msg, $value );

			return false;
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $bad_words
	 * @return bool
	 */
	function isAllowedWords( $label, $value, $msg, $bad_words ) {
		$words = explode( ' ', $value );
		if ( is_array( $words ) ) {
			foreach ( $words as $word ) {
				foreach ( $bad_words as $bad_word ) {
					if ( strtolower( $word ) == strtolower( $bad_word ) ) {
						$this->Error( $label, $msg, $value );

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $bad_words
	 * @return bool
	 */
	function isAllowedValues( $label, $value, $msg, $bad_words ) {
		foreach ( $bad_words as $bad_word ) {
			if ( strtolower( $value ) == strtolower( $bad_word ) ) {
				$this->Error( $label, $msg, $value );

				return false;
			}
		}

		return true;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isPhoneNumber( $label, $value, $msg = null ) {

		//Strip out all non-numeric characters.
		$phone = $this->stripNonNumeric( $value );

		//Debug::text('Raw Phone: '. $value .' Phone: '. $phone, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		if ( strlen( $phone ) >= 6 && strlen( $phone ) <= 20 && preg_match( '/^[0-9\(\)\-\.\+\ ]{6,20}$/i', $value ) ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param null $country
	 * @param null $province
	 * @return bool
	 */
	function isPostalCode( $label, $value, $msg = null, $country = null, $province = null ) {
		//Debug::text('Raw Postal Code: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		//Remove any spaces, keep dashes for US extended ZIP.
		$value = str_replace( [ ' ' ], '', trim( $value ) );

		$province = strtolower( trim( $province ) );

		switch ( strtolower( trim( $country ) ) ) {
			case 'us':
				//US zip code
				if ( preg_match( '/^[0-9]{5}$/i', $value ) || preg_match( '/^[0-9]{5}\-[0-9]{4}$/i', $value ) ) {

					if ( $province != '' ) {
						$province_postal_code_map = [
								'ak' => [ '9950099929' ],
								'al' => [ '3500036999' ],
								'ar' => [ '7160072999', '7550275505' ],
								'az' => [ '8500086599' ],
								'ca' => [ '9000096199' ],
								'co' => [ '8000081699' ],
								'ct' => [ '0600006999' ],
								'dc' => [ '2000020099', '2020020599' ],
								'de' => [ '1970019999' ],
								'fl' => [ '3200033999', '3410034999' ],
								'ga' => [ '3000031999' ],
								'hi' => [ '9670096798', '9680096899' ],
								'ia' => [ '5000052999' ],
								'id' => [ '8320083899' ],
								'il' => [ '6000062999' ],
								'in' => [ '4600047999' ],
								'ks' => [ '6600067999' ],
								'ky' => [ '4000042799', '4527545275' ],
								'la' => [ '7000071499', '7174971749' ],
								'ma' => [ '0100002799' ],
								'md' => [ '2033120331', '2060021999' ],
								'me' => [ '0380103801', '0380403804', '0390004999' ],
								'mi' => [ '4800049999' ],
								'mn' => [ '5500056799' ],
								'mo' => [ '6300065899' ],
								'ms' => [ '3860039799' ],
								'mt' => [ '5900059999' ],
								'nc' => [ '2700028999' ],
								'nd' => [ '5800058899' ],
								'ne' => [ '6800069399' ],
								'nh' => [ '0300003803', '0380903899' ],
								'nj' => [ '0700008999' ],
								'nm' => [ '8700088499' ],
								'nv' => [ '8900089899' ],
								'ny' => [ '0040000599', '0639006390', '0900014999' ],
								'oh' => [ '4300045999' ],
								'ok' => [ '7300073199', '7340074999' ],
								'or' => [ '9700097999' ],
								'pa' => [ '1500019699' ],
								'ri' => [ '0280002999', '0637906379' ],
								'sc' => [ '2900029999' ],
								'sd' => [ '5700057799' ],
								'tn' => [ '3700038599', '7239572395' ],
								'tx' => [ '7330073399', '7394973949', '7500079999', '8850188599' ],
								'ut' => [ '8400084799' ],
								'va' => [ '2010520199', '2030120301', '2037020370', '2200024699' ],
								'vt' => [ '0500005999' ],
								'wa' => [ '9800099499' ],
								'wi' => [ '4993649936', '5300054999' ],
								'wv' => [ '2470026899' ],
								'wy' => [ '8200083199' ],
						];

						if ( isset( $province_postal_code_map[$province] ) ) {
							$zip5 = substr( $value, 0, 5 );
							//Debug::text('Checking ZIP code range, short zip: '. $zip5, __FILE__, __LINE__, __METHOD__, $this->verbosity);
							foreach ( $province_postal_code_map[$province] as $postal_code_range ) {
								//Debug::text('Checking ZIP code range: '. $postal_code_range, __FILE__, __LINE__, __METHOD__, $this->verbosity);
								if ( ( $zip5 >= substr( $postal_code_range, 0, 5 ) ) && ( $zip5 <= substr( $postal_code_range, 5 ) ) ) {
									return true;
								}
							}
						} // else { //Debug::text('Postal Code does not match province!', __FILE__, __LINE__, __METHOD__, $this->verbosity);
					} else {
						return true;
					}
				}
				break;
			case 'ca':
				//Canada postal code
				if ( preg_match( '/^[a-zA-Z]{1}[0-9]{1}[a-zA-Z]{1}[-]?[0-9]{1}[a-zA-Z]{1}[0-9]{1}$/i', $value ) ) {
					if ( $province != '' ) {
						//Debug::text('Verifying postal code against province!', __FILE__, __LINE__, __METHOD__, $this->verbosity);
						$province_postal_code_map = [
								'ab' => [ 't' ],
								'bc' => [ 'v' ],
								'sk' => [ 's' ],
								'mb' => [ 'r' ],
								'qc' => [ 'g', 'h', 'j' ],
								'on' => [ 'k', 'l', 'm', 'n', 'p' ],
								'nl' => [ 'a' ],
								'nb' => [ 'e' ],
								'ns' => [ 'b' ],
								'pe' => [ 'c' ],
								'nt' => [ 'x' ],
								'yt' => [ 'y' ],
								'nu' => [ 'x' ],
						];

						//Debug::Arr($province_postal_code_map[$province], 'Valid Postal Codes for Province', __FILE__, __LINE__, __METHOD__, $this->verbosity);
						if ( isset( $province_postal_code_map[$province] ) && in_array( substr( strtolower( $value ), 0, 1 ), $province_postal_code_map[$province] ) ) {
							return true;
						} // else { //Debug::text('Postal Code does not match province!', __FILE__, __LINE__, __METHOD__, $this->verbosity);
					} else {
						return true;
					}
				}
				break;
			default:
				//US
				if ( preg_match( '/^[0-9]{5}$/i', $value ) || preg_match( '/^[0-9]{5}\-[0-9]{4}$/i', $value ) ) {
					return true;
				}

				//CA
				if ( preg_match( '/^[a-zA-Z]{1}[0-9]{1}[a-zA-Z]{1}[-]?[0-9]{1}[a-zA-Z]{1}[0-9]{1}$/i', $value ) ) {
					return true;
				}

				//Other
				if ( preg_match( '/^[a-zA-Z0-9]{1,10}$/i', $value ) ) {
					return true;
				}

				break;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isEmail( $label, $value, $msg = null ) {
		//Debug::text('Raw Email: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) !== false ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param $msg
	 * @param $check_response_code
	 * @return bool
	 */
	function isURL( $label, $value, $msg = null, $check_response_code = false ) {
		//Debug::text('Raw URL: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		$retval = false;
		if ( filter_var( $value, FILTER_VALIDATE_URL ) !== false ) {
			$retval = true;
		}

		if ( $check_response_code == true && $retval == true ) {
			$headers = @get_headers( $value, 1 ); //This follows redirects and includes the headers of each.
			if ( is_array( $headers ) ) {
				$last_http_code = null;
				foreach ( array_reverse( $headers ) as $key => $value ) {
					if ( is_int( $key ) && stripos( $value, 'HTTP/' ) === 0 ) { //Key will be the numeric based on the HTTP response and how many redirects there were.
						$last_http_code = intval( explode( ' ', $value )[1] );
						break;
					}
				}

				if ( is_int( $last_http_code ) && ( ( $last_http_code >= 500 && $last_http_code <= 599 ) || in_array( $last_http_code, [ 401, 403, 404, 408 ] ) == true ) ) { //Only check for 404, as Microsoft Azure SAML appears to return "400 Bad Request" when a SAML payload is not returned, but the URL is valid.
					Debug::Arr( $headers, 'Response Headers for URL: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity );
					$retval = false;
				}
			}
		}

		if ( $retval == true ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param bool $error_level
	 * @return bool
	 * @noinspection PhpUndefinedConstantInspection
	 */
	function isEmailAdvanced( $label, $value, $msg = null, $error_level = true, $enable_smtp_verification = false ) {
		//Debug::text('Raw Email: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);
		global $config_vars;

		if ( isset( $config_vars['mail']['disable_smtp_email_validation'] ) && $config_vars['mail']['disable_smtp_email_validation'] == true ) {
			Debug::text('  NOTICE: Disabling email SMTP validation due to .ini setting...', __FILE__, __LINE__, __METHOD__, $this->verbosity);
			$enable_smtp_verification = false;
		}

		$retval = Misc::isEmail( $value, true, $error_level, true, $enable_smtp_verification );
		if ( $retval === ISEMAIL_VALID ) {
			return true;
		}

		if ( is_array( $msg ) ) {
			if ( isset( $msg[$retval] ) ) {
				$msg = $msg[$retval];
			} else {
				$msg = $msg[0];
			}
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isIPAddress( $label, $value, $msg = null ) {
		//Debug::text('Raw IP: '. $value, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		$ip = explode( '.', $value );

		if ( count( $ip ) == 4 ) {
			$valid = true;

			foreach ( $ip as $block ) {
				if ( !is_numeric( $block ) || $block >= 255 || $block < 0 ) {
					$valid = false;
				}
			}

			if ( $valid == true ) {
				return true;
			}
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isDate( $label, $value, $msg = null ) {
		//Because most epochs are stored as 4-byte integers, make sure we are within range.
		if ( TTDate::isValidDate( $value ) ) {
			$date = gmdate( 'U', $value );
			//Debug::text('Raw Date: '. $value .' Converted Value: '. $date, __FILE__, __LINE__, __METHOD__, $this->verbosity);

			if ( $date == $value ) {
				return true;
			}
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @param null $country
	 * @return bool
	 */
	function isSIN( $label, $value, $msg = null, $country = null ) {
		$sin = $this->stripNonAlphaNumeric( trim( $value ) ); //UK National Insurance Number (NINO) has letters, so we can only strip spaces.
		Debug::text( 'Validating SIN/SSN: ' . $value . ' Country: ' . $country, __FILE__, __LINE__, __METHOD__, $this->verbosity );

		//$retval = false;
		switch ( strtolower( trim( $country ) ) ) {
			case 'ca':
				//As of around 2015, SINs starting with 0 can not be valid rather than just reserved for fictitious purposes.
				if ( is_numeric( $sin ) && strlen( $sin ) == 9 && $sin >= 10000000 && $sin < 999999999 ) {
					$split_sin = str_split( $sin );

					if ( ( $split_sin[1] *= 2 ) >= 10 ) {
						$split_sin[1] -= 9;
					}
					if ( ( $split_sin[3] *= 2 ) >= 10 ) {
						$split_sin[3] -= 9;
					}
					if ( ( $split_sin[5] *= 2 ) >= 10 ) {
						$split_sin[5] -= 9;
					}
					if ( ( $split_sin[7] *= 2 ) >= 10 ) {
						$split_sin[7] -= 9;
					}

					if ( ( array_sum( $split_sin ) % 10 ) != 0 ) {
						$retval = false;
					} else {
						$retval = true;
					}
				} else {
					if ( $sin == 999999999 || $sin == '000000000' ) { //Allow all 9/0's for a SIN in case its an out of country employee that doesn't have one.
						$retval = true;
					} else {
						$retval = false;
					}
				}
				break;
			case 'us':
				if ( strlen( $sin ) == 9 && is_numeric( $sin ) ) {
					//Due to highgroup randomization, we can no longer validate SSN's without querying the IRS database.
					$retval = true;
				} else {
					$retval = false;
				}
				break;
			default:
				//Allow all foriegn countries to utilize
				$retval = self::isLength( $label, $value, $msg, 1, 255 );
				break;
		}

		if ( $retval === true ) {
			return true;
		}

		Debug::text( 'Invalid SIN/SSN: ' . $value . ' Country: ' . $country, __FILE__, __LINE__, __METHOD__, $this->verbosity );
		$this->Error( $label, $msg, $value );

		return false;
	}

	/**
	 * Checks if input contains HTML or not.
	 * @param $label
	 * @param $value
	 * @param null $msg
	 * @return bool
	 */
	function isHTML( $label, $value, $msg = null, ) {
		if ( $value == strip_tags( $value ) ) {
			return true;
		}

		$this->Error( $label, $msg, $value );

		return false;
	}

	/*
	 * String manipulation functions.
	 */
	/**
	 * @param $value
	 * @return int
	 */
	function stripNon32bitInteger( $value ) {
		if ( (int)$value >= 2147483647 || (int)$value <= -2147483648 ) { //Make sure we cast $value to integer, otherwise its a string comparison which is not as intended.
			return 0;
		}

		return $value;
	}

	/**
	 * @param $value
	 * @return int
	 */
	function stripNon64bitInteger( $value ) {
		if ( (int)$value >= 9223372036854775807 || (int)$value <= -9223372036854775808 ) {
			return 0;
		}

		return $value;
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	function stripSpaces( $value ) {
		return str_replace( ' ', '', trim( $value ) );
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	function stripNumeric( $value ) {
		$retval = preg_replace( '/[0-9]/', '', $value );

		return $retval;
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	function stripNonNumeric( $value ) {
		$retval = preg_replace( '/[^0-9]/', '', $value );

		return $retval;
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	function stripNonAlphaNumeric( $value ) {
		$retval = preg_replace( '/[^A-Za-z0-9]/', '', $value );

		//Debug::Text('Alpha Numeric String:'. $retval, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		return $retval;
	}

	/**
	 * @param $value int|float|string
	 * @return int|float|string
	 */
	function stripNonFloat( $value ) {
		//Don't use TTi18n::parseFloat() here, as if we are going to be doing that we should do it as early as possible to the user input, like in setObjectFromArray().
		//  Then this function would often be called afterwards, so it should always return a float value or something that can be used in math.
		//  TTi18n::parseFloat() parses out non-float characters itself.
		//We do need to check if the value passed in is already cast to float/int and just accept it in that case.
		//    Because in other locales preg_match() casts $value to a string, which means decimal could become a comma, then it won't match.
		if ( is_float( $value ) === true || is_int( $value ) === true ) {
			return $value;
		} else if ( is_string( $value ) ) {
			//Strips repeating "." and "-" characters that might slip in due to typos. Then strips non-float valid characters.
			//This is also done in TTi18n::parseFloat() and Validator->stripNonFloat()
			//$retval = preg_replace( '/([\-\.,])(?=.*?\1)|[^-0-9\.,]/', '', $value );

			//This should return a float value, or at least something that can be used in math functions.
			// TTi18n::parseFloat() should be called in setObjectFromArray() well before this would ever be called, which is typically in set*() functions.
			// CompanyDeductionFactory requires this, as Advanced Percent calculations have values entered as '124,000' which are run through stripNonFloat() prior to performing math on them.
			// ([\D]*$) -- This removes any trailing non-digits or signs. (ie: '123.91-')
			$retval = preg_replace( '/([\-\.])(?=.*?\1)|(?<!^)-|[^-0-9\.]|([\D]*$)/', '', (string)$value );
		} else if ( is_bool( $value ) ) {
			$retval = 0;
		} else {
			Debug::Arr( $value, 'ERROR: Value is not numeric or string!', __FILE__, __LINE__, __METHOD__, $this->verbosity);
			$retval = 0;
		}

		//Debug::Text('Float String:'. $retval, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		return $retval;
	}

	/**
	 * Suitable for passing to parseTimeUnit() after.
	 * @param $value
	 * @return mixed
	 */
	function stripNonTimeUnit( $value ) {
		$retval = preg_replace( '/[^-0-9\.:]/', '', $value );

		//Debug::Text('Float String:'. $retval, __FILE__, __LINE__, __METHOD__, $this->verbosity);

		return $retval;
	}

	/**
	 * @param $value
	 * @return string
	 */
	function stripHTML( $value ) {
		return strip_tags( $value );
	}

	/**
	 * @param $value
	 * @return string
	 */
	function escapeHTML( $value ) {
		return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * @param $value
	 * @return string
	 */
	function purifyHTML( $value ) {
		global $config_vars;

		//Require inside this function as HTMLPurifier is a huge file.
		require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'ezyang' . DIRECTORY_SEPARATOR . 'htmlpurifier' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'HTMLPurifier.auto.php' );

		$config = HTMLPurifier_Config::createDefault();
		if ( isset( $config_vars['cache']['enable'] ) && $config_vars['cache']['enable'] == true
				&& $config_vars['cache']['dir'] != '' && is_writable( $config_vars['cache']['dir'] ) ) {
			$config->set( 'Cache.SerializerPath', $config_vars['cache']['dir'] );
			//Debug::Text('Caching HTMLPurifier...', __FILE__, __LINE__, __METHOD__, $this->verbosity);
		} else {
			$config->set( 'Cache.DefinitionImpl', null );
			Debug::Text( 'NOT caching HTMLPurifier...', __FILE__, __LINE__, __METHOD__, $this->verbosity );
		}

		$purifier = new HTMLPurifier( $config );

		return $purifier->purify( $value );
	}

	/**
	 * @param $value
	 * @return bool|string
	 */
	function getPhoneNumberAreaCode( $value ) {
		$phone_number = $this->stripNonNumeric( $value );
		if ( strlen( $phone_number ) > 7 ) {
			$retval = substr( $phone_number, -10, 3 ); //1 555 555 5555

			return $retval;
		}

		return false;
	}

	/*
	 * Class standard functions.
	 */

	/**
	 * @param $string
	 * @param $var_array
	 * @return mixed
	 */
	function varReplace( $string, $var_array ) {
		//var_array = arary('var1' => 'blah1', 'var2' => 'blah2');
		$keys = [];
		$values = [];
		if ( is_array( $var_array ) && count( $var_array ) > 0 ) {
			foreach ( $var_array as $key => $value ) {
				$keys[] = '#' . $key;
				$values[] = $value;
			}
		}

		$retval = str_replace( $keys, $values, $string );

		return $retval;
	}

	/**
	 * @param int $validate_only EPOCH
	 */
	function setValidateOnly( $validate_only ) {
		$this->validate_only = $validate_only;
	}

	/**
	 * @return bool
	 */
	function getValidateOnly() {
		return $this->validate_only;
	}

	/**
	 * Returns both Errors and Warnings combined.
	 * @return array
	 */
	function getErrorsAndWarningsArray() {
		return [ 'errors' => $this->getErrorsArray(), 'warnings' => $this->getWarningsArray() ];
	}

	/**
	 * Merges all errors/warnings from the passed $validator object to this one.
	 * @param object $validator
	 * @return bool
	 */
	function merge( $validator ) {
		if ( is_object( $validator ) && $validator->isValid() == false ) {
			$this->errors = array_merge( $this->errors, $validator->getErrorsArray() );
			$this->num_errors += count( $validator->getErrorsArray() );

			$this->warnings = array_merge( $this->warnings, $validator->getWarningsArray() );
			$this->num_warnings += count( $validator->getWarningsArray() );
		}

		return true;
	}

	/**
	 * @param string $record_label_prefix
	 * @return array
	 */
	private function addRecordLabelPrefixToArray( $arr, $record_label_prefix = null ) {
		$retarr = [];

		if ( $record_label_prefix != '' ) {
			$record_label_prefix .= ': '; //Add a colon separator.
		}

		if ( count( $arr ) > 0 ) {
			foreach ( $arr as $label => $msgs ) {
				foreach ( $msgs as $msg ) {
					$retarr[$label][] = $record_label_prefix . $msg;
				}
			}
		}

		return $retarr;
	}

	/**
	 * @param string $record_label_prefix
	 * @return array
	 */
	function getErrorsArray( $record_label_prefix = null ) {
		return $this->addRecordLabelPrefixToArray( $this->errors, $record_label_prefix );
	}

	/**
	 * @return int
	 */
	function getTotalErrors() {
		return $this->num_errors;
	}

	/**
	 * @return bool|string
	 */
	function getErrors() {
		if ( count( $this->errors ) > 0 ) {
			$output = "<ol>\n";
			foreach ( $this->errors as $label ) {
				foreach ( $label as $msg ) {
					$output .= '<li>' . $msg . ".</li>";
				}
			}
			$output .= "</ol>\n";

			return $output;
		}

		return false;
	}

	/**
	 * @param bool $numbered_list
	 * @param array $errors_arr Pass in other error array to be converted to text.
	 * @return bool|string
	 */
	function getTextErrors( $numbered_list = true, $errors_arr = null ) {
		if ( $errors_arr == null ) {
			$errors_arr = $this->errors;
		}

		if ( is_array( $errors_arr ) && count( $errors_arr ) > 0 ) {
			$output = '';
			$number_prefix = null;
			$i = 1;
			foreach ( $errors_arr as $label ) {
				foreach ( $label as $msgs ) {
					if ( $numbered_list == true ) {
						$number_prefix = $i . '. ';
					}

					if ( is_array( $msgs ) ) {
						foreach ( $msgs as $msg ) {
							$output .= $number_prefix . $msg . "\n";

							$i++;
						}
					} else {
						$output .= $number_prefix . $msgs . "\n";

						$i++;
					}
				}
			}

			return $output;
		}

		return false;
	}

	/**
	 * @param null $label
	 * @return bool
	 */
	final function isValid( $label = null ) {
		if ( $this->isError( $label ) || $this->isWarning( $label ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @param null $label
	 * @return bool
	 */
	final function isError( $label = null ) {
		if ( is_string( $label ) && $label != '' ) {
			return $this->hasError( $label );
		} else if ( $this->num_errors > 0 ) {
			Debug::Arr( $this->errors, 'Errors', __FILE__, __LINE__, __METHOD__, $this->verbosity );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function resetErrors() {
		$this->errors = []; //Set to blank array rather than use unset() as that will cause PHP warnings in hasError().
		$this->num_errors = 0;

		return true;
	}

	/**
	 * @param $label
	 * @return bool
	 */
	function hasError( $label ) {
		if ( in_array( $label, array_keys( $this->errors ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $label
	 * @param $msg
	 * @param string $value
	 * @return bool
	 */
	function Error( $label, $msg, $value = '' ) {
		Debug::text( 'Validation Error: Label: ' . $label . ' Value: "' . $value . '" Msg: ' . $msg, __FILE__, __LINE__, __METHOD__, $this->verbosity );

		//If label is NULL, assume we don't actually want to trigger an error.
		//This is good for just using the check functions for other purposes.
		if ( (string)$label != '' ) {
			$this->errors[$label][] = $msg;

			$this->num_errors++;

			return true;
		}

		return false;
	}

	//
	// Warning functions below here
	//

	/**
	 * @param string $record_label_prefix
	 * @return array
	 */
	function getWarningsArray( $record_label_prefix = null ) {
		return $this->addRecordLabelPrefixToArray( $this->warnings, $record_label_prefix );
	}

	/**
	 * @param null $label
	 * @return bool
	 */
	final function isWarning( $label = null ) {
		if ( is_string( $label ) && $label != '' ) {
			return $this->hasWarning( $label );
		} else if ( $this->num_warnings > 0 ) {
			Debug::Arr( $this->warnings, 'Warnings', __FILE__, __LINE__, __METHOD__, $this->verbosity );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function resetWarnings() {
		$this->warnings = []; //Set to blank array rather than use unset() as that will cause PHP warnings in hasWarning().
		$this->num_warnings = 0;

		return true;
	}

	/**
	 * @param $label
	 * @return bool
	 */
	function hasWarning( $label ) {
		if ( in_array( $label, array_keys( $this->warnings ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $label
	 * @param $msg
	 * @param string $value
	 * @return bool
	 */
	function Warning( $label, $msg, $value = '' ) {
		Debug::text( 'Validation Warning: Label: ' . $label . ' Value: "' . $value . '" Msg: ' . $msg, __FILE__, __LINE__, __METHOD__, $this->verbosity );

		if ( (string)$label != '' ) {
			$this->warnings[$label][] = $msg;

			$this->num_warnings++;

			return true;
		}

		return false;
	}


}

?>
