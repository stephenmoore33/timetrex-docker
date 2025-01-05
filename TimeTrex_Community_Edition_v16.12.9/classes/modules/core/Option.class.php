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
class Option {
	/**
	 * Retrieves the value associated with a specific key from an options array.
	 *
	 * This function is used to fetch a value from an associative array based on a key.
	 * If the key does not exist in the array, a default value can be returned instead.
	 *
	 * @param string $key The key to look for in the options array.
	 * @param array $options The associative array containing keys and values.
	 * @param mixed $false The default value to return if the key is not found. Defaults to false.
	 * @return mixed The value from the options array if the key is found; otherwise, the default value.
	 */
	static function getByKey( $key, $options, $false = false ) {
		if ( isset( $options[$key] ) ) {
			//Debug::text('Returning Value: '. $options[$key], __FILE__, __LINE__, __METHOD__, 9);

			return $options[$key];
		}

		return $false;
	}

	/**
	 * Retrieves a key from an options array that matches the given value.
	 *
	 * This function is designed to find the corresponding key for a given value within an associative array.
	 * It can handle translation of the value if needed, which is useful for matching localized strings.
	 * The search is case-insensitive to ensure flexibility in matching string values.
	 *
	 * @param mixed $value The value to search for in the options array.
	 * @param array $options The associative array containing keys and values.
	 * @param bool $value_is_translated Optional. Whether the value is already translated (localized). Defaults to true.
	 * @return mixed The key associated with the value if found; otherwise, false.
	 */
	static function getByValue( $value, $options, $value_is_translated = true ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//		 BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == true ) {
			$value = TTi18n::gettext( $value );
		}

		if ( is_array( $value ) ) {
			return false;
		}

		if ( !is_array( $options ) ) {
			return false;
		}

		$value = strtolower( $value ); //Use a case insensitive match so things like iButton matches iBUTTON.

		$flipped_options = array_flip( array_map( 'strtolower', $options ) );

		if ( isset( $flipped_options[$value] ) ) {
			//Debug::text('Returning Key: '. $flipped_options[$value], __FILE__, __LINE__, __METHOD__, 9);

			return $flipped_options[$value];
		}

		return false;
	}

	/**
	 * Performs a fuzzy search on the options array to find a match for the given value.
	 * 
	 * This method attempts to simulate the behavior of SQL's LIKE operator or metaphone-based matching
	 * within an array context. It is useful for finding approximate matches when the exact value may
	 * not be known or when dealing with potential variations in spelling or input. The function can
	 * handle translated values if specified. The search is case-insensitive and can handle wildcards.
	 *
	 * @param string $value The string value to search for, with optional SQL-like wildcards.
	 * @param array $options The array of strings to search within.
	 * @param bool $value_is_translated Optional. Specifies whether the value is already translated (localized). Defaults to true.
	 * @return mixed The key associated with the matched value if found; otherwise, false. If multiple matches are found, an array of keys is returned.
	 */
	static function getByFuzzyValue( $value, $options, $value_is_translated = true ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//		 BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == true ) {
			$value = TTi18n::gettext( $value );
		}
		if ( is_array( $value ) ) {
			return false;
		}

		if ( !is_array( $options ) ) {
			return false;
		}

		//
		//Try to replicate a SQL search from Factory::handleSQLSyntax().
		//

		$value = str_replace( '*', '%', $value ); //Switch to consistent more SQL like syntax with % wildcards.

		if ( $value != '' && strpos( $value, '%' ) === false && ( strpos( $value, '|' ) === false && strpos( $value, '"' ) === false ) ) {
			$value .= '%';
		}

		$flags_exact_match = false;
		if ( strpos( $value, '"' ) !== false ) {
			$flags_exact_match = true;
		}

		$flags_exact_end = false;
		if ( strpos( $value, '|' ) !== false || strpos( $value, '"' ) !== false ) {
			$flags_exact_end = true;
		}

		//Now that the flags are set above, get rid of special chars to prepare for regex.
		$value = str_replace( [ '"', '|' ], '', $value );

		//Help prevent regex attack vectors, like backtracking DDOS.
		// Don't allow any brackets (ie: (), [] ), as to avoid mismatched brackets causing regex compilation errors.
		$value = preg_replace( '/[^A-Za-z0-9-\.\ %\|]/', '', $value );

		$regex_retarr = preg_grep( '/^' . str_replace( [ '%' ], [ '.*' ], $value ) . ( ( $flags_exact_end == true ) ? '$' : '' ) . '/i', $options );
		if ( !is_array( $regex_retarr ) ) {
			$regex_retarr = []; //Empty array.
		}

		if ( $flags_exact_match === false ) { //Skip metaphone match when using exact match.
			//Metaphone match -- Need to strip all special operator characters as they are no good with metaphone anyways.
			$metaphone_retarr = preg_grep( '/^' . metaphone( $value ) . ( ( $flags_exact_end == true ) ? '$' : '' ) . '/i', array_map( 'metaphone', $options ) );
			if ( !is_array( $metaphone_retarr ) ) {
				$metaphone_retarr = []; //Empty array.
			}
		} else {
			$metaphone_retarr = []; //Empty array.
		}

		$retarr = ( $regex_retarr + $metaphone_retarr ); //Merge while keeping array keys.

		if ( empty( $retarr ) == false ) {
			arsort( $retarr );

			//Debug::Arr( $search_arr, 'Search Str: '. $search_str .' Search Array: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr( $retarr, 'Matches: ', __FILE__, __LINE__, __METHOD__, 10);

			return array_keys( $retarr );
		}

		return false;
	}

	/**
	 * Filters an associative array by keys provided in the needles array.
	 * This function is typically used to filter a list of options or settings,
	 * such as statuses, where the haystack represents all possible options and
	 * the needles array contains the keys of the options that should be returned.
	 *
	 * @param array $needles An array of keys to filter the haystack by.
	 * @param array $haystack An associative array from which to retrieve the key-value pairs.
	 * @return array|bool Returns an associative array of the filtered key-value pairs from the haystack,
	 *                    or false if the resulting array is empty.
	 */
	static function getByArray( $needles, $haystack ) {

		if ( !is_array( $needles ) ) {
			$needles = [ $needles ];
		}

		$needles = array_unique( $needles );

		$retval = [];
		foreach ( $needles as $needle ) {
			if ( isset( $haystack[$needle] ) ) {
				$retval[$needle] = $haystack[$needle];
			}
		}

		if ( empty( $retval ) == false ) {
			return $retval;
		}

		return false;
	}

	/**
	 * Converts a bitmask to an array of keys from the provided options.
	 *
	 * This function takes a bitmask and an associative array of options, then
	 * determines which keys from the options array are represented by the bitmask.
	 * Each bit in the bitmask corresponds to a key in the options array. If the bit
	 * is set, the corresponding key is included in the returned array.
	 *
	 * @param int $bitmask An integer representing the bitmask to be converted.
	 * @param array $options An associative array where keys are used as bit positions.
	 * @return array|bool An array of keys from the options that are set in the bitmask, or false if inputs are invalid.
	 */
	static function getArrayByBitMask( $bitmask, $options ) {
		$bitmask = (int)$bitmask;

		$retarr = [];
		if ( is_numeric( $bitmask ) && is_array( $options ) ) {
			foreach ( $options as $key => $value ) {
				//Debug::Text('Checking Bitmask: '. $bitmask .' mod '. $key .' != 0', __FILE__, __LINE__, __METHOD__, 10);
				if ( ( $bitmask & (int)$key ) !== 0 ) {
					//Debug::Text('Found Bit: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					$retarr[] = $key;
				}
			}
			unset( $value ); //code standards
		}

		if ( empty( $retarr ) == false ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * Converts an array of keys into a bitmask.
	 *
	 * Given an array of keys and an associative array of options, this function
	 * calculates the bitmask representing the keys in the context of the options.
	 * If a numeric value is passed as $keys, it is first converted to an array
	 * using getArrayByBitMask. The function iterates over each key and sets the
	 * corresponding bit in the bitmask.
	 *
	 * @param array|int $keys An array of keys or a numeric value to be converted.
	 * @param array $options An associative array of options where keys represent bit positions.
	 * @return int The calculated bitmask.
	 */
	static function getBitMaskByArray( $keys, $options ) {
		//If an integer is passed in try and convert it to an array automatically.
		if ( is_numeric( $keys ) == true ) {
			$keys = self::getArrayByBitMask( $keys, $options );
		}

		$retval = 0;
		if ( is_array( $keys ) && is_array( $options ) ) {
			foreach ( $keys as $key ) {
				if ( isset( $options[$key] ) ) {
					$retval |= $key;
				} else {
					Debug::Text( 'Key is not a valid bitmask int: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return $retval;
	}
}

?>
