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
class Misc {

	/*
		this method assumes that the form has one or more
		submit buttons and that they are named according
		to this scheme:

		<input type="submit" name="submit:command" value="some value">

		This is useful for identifying which submit button actually
		submitted the form.
	*/
	/**
	 * @param string $prefix
	 * @return null|string
	 */
	static function findSubmitButton( $prefix = 'action' ) {
		// search post vars, then get vars.
		$queries = [ $_POST, $_GET ];
		foreach ( $queries as $query ) {
			foreach ( $query as $key => $value ) {
				//Debug::Text('Key: '. $key .' Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$newvar = explode( ':', $key, 2 );
				//Debug::Text('Explode 0: '. $newvar[0] .' 1: '. $newvar[1], __FILE__, __LINE__, __METHOD__, 10);
				if ( isset( $newvar[0] ) && isset( $newvar[1] ) && $newvar[0] === $prefix ) {
					$val = $newvar[1];

					// input type=image stupidly appends _x and _y.
					if ( substr( $val, ( strlen( $val ) - 2 ) ) === '_x' ) {
						$val = substr( $val, 0, ( strlen( $val ) - 2 ) );
					}

					//Debug::Text('Found Button: '. $val, __FILE__, __LINE__, __METHOD__, 10);
					return strtolower( $val );
				}
			}
			unset( $value ); //code standards
		}

		return null;
	}

	/**
	 * @param bool $text_keys
	 * @return array
	 */
	static function getSortDirectionArray( $text_keys = false ) {
		if ( $text_keys === true ) {
			return [ 'asc' => 'ASC', 'desc' => 'DESC' ];
		} else {
			return [ 1 => 'ASC', -1 => 'DESC' ];
		}
	}

	/**
	 * @param $prepend_arr
	 * @param $arr
	 * @return array|bool
	 */
	static function prependArray( $prepend_arr, $arr ) {
		if ( !is_array( $prepend_arr ) && is_array( $arr ) ) {
			return $arr;
		} else if ( is_array( $prepend_arr ) && !is_array( $arr ) ) {
			return $prepend_arr;
		} else if ( !is_array( $prepend_arr ) && !is_array( $arr ) ) {
			return false;
		}

		$retarr = $prepend_arr;

		foreach ( $arr as $key => $value ) {
			//Don't overwrite entries from the prepend array.
			if ( !isset( $retarr[$key] ) ) {
				$retarr[$key] = $value;
			}
		}

		return $retarr;
	}

	/**
	 * @param null $input
	 * @param null $columnKey
	 * @param null $indexKey
	 * @return array|bool|null
	 */
	static function arrayColumn( $input = null, $columnKey = null, $indexKey = null ) {
		return array_column( (array)$input, $columnKey, $indexKey );
	}

	/**
	 * @param $array
	 * @param bool $preserve
	 * @param array $r
	 * @return array
	 */
	static function flattenArray( $array, $preserve = false, $r = [] ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( is_array( $v ) ) {
						$tmp = $v;
						unset( $value[$k] );
					}
				}

				if ( $preserve == true ) {
					$r[$key] = $value;
				} else {
					$r[] = $value;
				}
			}

			$r = isset( $tmp ) ? self::flattenArray( $tmp, $preserve, $r ) : $r;
		}

		return $r;
	}

	/**
	 * Flattens an array by only a single level, ie: [ 'level1' => [ 0 => [ 'key' => 'val' ] ] ] becomes: [ 0 => [ 'key' => 'val' ] ]
	 * @param $array
	 * @return array
	 */
	static function flattenArrayOneLevel( $array ) {
		$retarr = [];

		if ( is_array( $array ) && !empty($array) ) {
			foreach( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$retarr = $retarr + $value; //Merge array and maintain indexes.
				}
			}
		}

		return $retarr;
	}

	/*
		When passed an array of input_keys, and an array of output_key => output_values,
		this function will return all the output_key => output_value pairs where
		input_key == output_key
	*/
	/**
	 * @param $keys
	 * @param $options
	 * @return array|null
	 */
	static function arrayIntersectByKey( $keys, $options ) {
		if ( is_array( $keys ) && is_array( $options ) ) {
			$retarr = [];
			foreach ( $keys as $key ) {
				if ( isset( $options[$key] ) && $key !== false ) { //Ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return null;
	}

	/*
		When passed an associative array from a ListFactory, ie:
		array(	0 => array( <...Data ..> ),
				1 => array( <...Data ..> ),
				2 => array( <...Data ..> ),
				... )
		this function will return an associative array of only the key=>value
		pairs that intersect across all rows.

	*/
	/**
	 * @param $rows
	 * @return bool|mixed
	 */
	static function arrayIntersectByRow( $rows ) {
		if ( !is_array( $rows ) ) {
			return false;
		}

		if ( count( $rows ) < 2 ) {
			return false;
		}

		//Debug::Arr($rows, 'Intersected/Common Data', __FILE__, __LINE__, __METHOD__, 10);
		$retval = false;
		if ( isset( $rows[0] ) ) {
			$retval = @call_user_func_array( 'array_intersect_assoc', $rows );
			// The '@' cannot be removed, Some of the array_* functions that compare elements in
			// multiple arrays do so by (string)$elem1 === (string)$elem2 If $elem1 or $elem2 is an
			// array, then the array to string notice is thrown, $rows is an array and its every
			// element is also an array, but its element may have one element is still an array, if
			// so, the array to string notice will be produced. this case may be like this:
			//	array(
			//		array('a'), array(
			//			array('a'),
			//		),
			//	);
			// Put a "@" in front to prevent the error, otherwise, the Flex will not work properly.

			//Debug::Arr($retval, 'Intersected/Common Data', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}

	/**
	 * Returns the most common values for each key (column) in the rows.
	 * @param $rows
	 * @param null $filter_columns Specific columns to get common data for.
	 * @return array|bool
	 */
	static function arrayCommonValuesForEachKey( $rows, $filter_columns = null ) {
		if ( !is_array( $rows ) ) {
			return false;
		}

		$retarr = [];

		if ( is_array( $filter_columns ) ) {
			$array_keys = $filter_columns;
		} else {
			$array_keys = array_keys( $rows[0] );
		}

		foreach ( $array_keys as $array_key ) {
			$counted_column_values = @array_count_values( Misc::arrayColumn( $rows, $array_key ) );
			arsort( $counted_column_values );

			$retarr[$array_key] = current( array_slice( array_keys( $counted_column_values ), 0, 1, true ) );
			unset( $counted_column_values );
		}

		Debug::Arr( $retarr, 'Most common values for each key: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * Returns all the output_key => output_value pairs where
	 * the input_keys are not present in output array keys.
	 *
	 * @param $keys
	 * @param $options
	 * @return array|null
	 */
	static function arrayDiffByKey( $keys, $options ) {
		if ( is_array( $keys ) && is_array( $options ) ) {
			$retarr = [];
			foreach ( $options as $key => $value ) {
				if ( !in_array( $key, $keys, true ) ) { //Use strict we ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}
			unset( $value ); //code standards

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return null;
	}

	/**
	 * This only merges arrays where the array keys must already exist.
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	static function arrayMergeRecursiveDistinct( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ) {
				$merged[$key] = self::arrayMergeRecursiveDistinct( $merged[$key], $value );
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Merges arrays with overwriting whereas PHP standard array_merge_recursive does not overwrites but combines.
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	static function arrayMergeRecursive( array $array1, array $array2 ) {
		foreach ( $array2 as $key => $value ) {
			if ( array_key_exists( $key, $array1 ) && is_array( $value ) ) {
				$array1[$key] = self::arrayMergeRecursive( $array1[$key], $array2[$key] );
			} else {
				$array1[$key] = $value;
			}
		}

		return $array1;
	}

	/**
	 * @param $array1
	 * @param $array2
	 * @return array|bool
	 */
	static function arrayDiffAssocRecursive( $array1, $array2 ) {
		$difference = [];
		if ( is_array( $array1 ) ) {
			foreach ( $array1 as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( !isset( $array2[$key] ) ) {
						$difference[$key] = $value;
					} else if ( !is_array( $array2[$key] ) ) {
						$difference[$key] = $value;
					} else {
						$new_diff = self::arrayDiffAssocRecursive( $value, $array2[$key] );
						if ( $new_diff !== false ) {
							$difference[$key] = $new_diff;
						}
					}
				} else if ( !isset( $array2[$key] ) || $array2[$key] != $value ) {
					$difference[$key] = $value;
				}
			}
		}

		if ( empty( $difference ) ) {
			return false;
		}

		return $difference;
	}

	/**
	 * @param $arr
	 * @return mixed
	 */
	static function arrayCommonValue( $arr ) {
		$arr_count = array_count_values( $arr );
		arsort( $arr_count );

		return key( $arr_count );
	}

	/**
	 * Case insensitive array_unique().
	 * @param $array
	 * @return array
	 */
	static function arrayIUnique( $array ) {
		return array_intersect_key( $array, array_unique( array_map( 'strtolower', $array ) ) );
	}

	/**
	 * Adds prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	 * @param $prefix
	 * @param $arr
	 * @param null $ignore_elements
	 * @return array
	 */
	static function addKeyPrefix( $prefix, $arr, $ignore_elements = null ) {
		if ( is_array( $arr ) ) {
			$retarr = [];
			foreach ( $arr as $key => $value ) {
				if ( !is_array( $ignore_elements ) || ( is_array( $ignore_elements ) && !in_array( $key, $ignore_elements ) ) ) {
					$retarr[$prefix . $key] = $value;
				} else {
					$retarr[$key] = $value;
				}
			}

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		//Don't return FALSE, as this can create array( 0 => FALSE ) arrays if we then cast it to an array, which corrupts some report data.
		// Instead just return the original variable that was passed in (likely NULL)
		return $arr;
	}

	/**
	 * Removes prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	 * @param $prefix
	 * @param $arr
	 * @param null $ignore_elements
	 * @return array|bool
	 */
	static function removeKeyPrefix( $prefix, $arr, $ignore_elements = null ) {
		if ( is_array( $arr ) ) {
			$retarr = [];
			foreach ( $arr as $key => $value ) {
				if ( !is_array( $ignore_elements ) || ( is_array( $ignore_elements ) && !in_array( $key, $ignore_elements ) ) ) {
					$retarr[self::strReplaceOnce( $prefix, '', $key )] = $value;
				} else {
					$retarr[$key] = $value;
				}
			}

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		return false;
	}

	/**
	 * Adds sort prefixes to an array maintaining the original order. Primarily used because Flex likes to reorded arrays with string keys.
	 * @param $arr
	 * @param int $begin_counter
	 * @return array
	 */
	static function addSortPrefix( $arr, $begin_counter = 1 ) {
		if ( is_array( $arr ) ) {
			$retarr = [];
			$i = $begin_counter;
			foreach ( $arr as $key => $value ) {
				$sort_prefix = null;
				if ( substr( $key, 0, 1 ) != '-' ) {
					$sort_prefix = '-' . str_pad( $i, 4, 0, STR_PAD_LEFT ) . '-';
				}
				$retarr[$sort_prefix . $key] = $value;
				$i++;
			}

			if ( empty( $retarr ) == false ) {
				return $retarr;
			}
		}

		//Return original variable if we can't add any sort prefixes. This prevents array_merge() from fatal erroring if we return false due to it not being an array.
		return $arr;
	}

	/**
	 * Removes sort prefixes from an array.
	 * @param $value
	 * @param bool $trim_arr_value
	 * @return array|mixed
	 */
	static function trimSortPrefix( $value, $trim_arr_value = false ) {
		if ( $value != '' ) {
			$trim_sort_prefix_regex = '/^-[0-9]{3,9}-/i';
			$retval = [];
			if ( is_array( $value ) && count( $value ) > 0 ) {
				foreach ( $value as $key => $val ) {
					if ( $trim_arr_value == true ) {
						$retval[$key] = preg_replace( $trim_sort_prefix_regex, '', $val );
					} else {
						$retval[preg_replace( $trim_sort_prefix_regex, '', $key )] = $val;
					}
				}
			} else {
				$retval = preg_replace( $trim_sort_prefix_regex, '', $value );
			}

			if ( empty( $retval ) == false ) {
				return $retval;
			}
		}

		return $value;
	}

	/**
	 * @param $str_pattern
	 * @param $str_replacement
	 * @param $string
	 * @return mixed
	 */
	static function strReplaceOnce( $str_pattern, $str_replacement, $string ) {
		if ( strpos( $string, $str_pattern ) !== false ) {
			return substr_replace( $string, $str_replacement, strpos( $string, $str_pattern ), strlen( $str_pattern ) );
		}

		return $string;
	}

	/**
	 * @param $file_name
	 * @param $type
	 * @param $size
	 * @return bool
	 */
	static function FileDownloadHeader( $file_name, $type, $size ) {
		if ( $file_name == '' || $size == '' ) {
			return false;
		}

		Debug::Text( 'Downloading File: '. $file_name .' Type: '. $type .' Size: '. $size, __FILE__, __LINE__, __METHOD__, 10 );

		Header( 'Content-Type: ' . $type );
		//Header('Content-disposition: inline; filename='.$file_name); //Displays document inline (in browser window) if available
		Header( 'Content-Disposition: attachment; filename="' . $file_name . '"' ); //Forces document to download
		Header( 'Content-Length: ' . $size );

		return true;
	}

	/**
	 * This function helps sending binary data to the client for saving/viewing as a file.
	 * @param $file_name
	 * @param $type
	 * @param $data
	 * @return bool
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	static function APIFileDownload( $file_name, $type, $data ) {
		if ( $file_name == '' || $data == '' ) {
			return false;
		}

		if ( is_array( $data ) ) {
			return false;
		}

		$size = strlen( $data );

		self::FileDownloadHeader( $file_name, $type, $size );
		echo $data;
		//Don't return any TRUE/FALSE here as it could end up in the file.
	}


	/**
	 * Removes vowels from the string always keeping the first and last letter.
	 * @param $str
	 * @return bool|string
	 */
	static function abbreviateString( $str ) {
		$vowels = [ 'a', 'e', 'i', 'o', 'u' ];

		$retarr = [];
		$words = explode( ' ', trim( $str ) );
		if ( is_array( $words ) ) {
			foreach ( $words as $word ) {
				$first_letter_in_word = substr( $word, 0, 1 );
				$last_letter_in_word = substr( $word, -1, 1 );
				$word = str_ireplace( $vowels, '', trim( $word ) );
				if ( substr( $word, 0, 1 ) != $first_letter_in_word ) {
					$word = $first_letter_in_word . $word;
				}
				if ( substr( $word, -1, 1 ) != $last_letter_in_word ) {
					$word .= $last_letter_in_word;
				}
				$retarr[] = $word;
			}

			return implode( ' ', $retarr );
		}

		return false;
	}

	/**
	 * @param $str
	 * @param $length
	 * @param int $start
	 * @param bool $abbreviate
	 * @return string
	 */
	static function TruncateString( $str, $length, $start = 0, $abbreviate = false ) {
		if ( strlen( $str ) > $length ) {
			if ( $abbreviate == true ) {
				//Try abbreviating it first.
				$retval = trim( substr( self::abbreviateString( $str ), $start, $length ) );
				if ( strlen( $retval ) > $length ) {
					$retval .= '...';
				}
			} else {
				$retval = trim( substr( trim( $str ), $start, $length ) ) . '...';
			}
		} else {
			$retval = $str;
		}

		return $retval;
	}

	/**
	 * @param $bool
	 * @return string
	 */
	static function HumanBoolean( $bool ) {
		if ( $bool == true ) {
			return 'Yes';
		} else {
			return 'No';
		}
	}

	/**
	 * Encode integer to a alphanumeric value that is reversible.
	 * @param $int
	 * @return string
	 */
	static function encodeInteger( $int ) {
		if ( $int != '' ) {
			return strtoupper( base_convert( strrev( str_pad( $int, 11, 0, STR_PAD_LEFT ) ), 10, 36 ) );
		}

		return $int;
	}

	/**
	 * @param $current
	 * @param $maximum
	 * @param int $precision
	 * @return float|int
	 */
	static function calculatePercent( $current, $maximum, $precision = 0 ) {
		if ( $maximum == 0 ) {
			return 100;
		}

		$percent = round( ( ( $current / $maximum ) * 100 ), (int)$precision );

		if ( $precision == 0 ) {
			$percent = (int)$percent;
		}

		return $percent;
	}

	//

	/**
	 * @param $amount
	 * @param $element
	 * @param array $include_elements
	 * @param array $exclude_elements
	 * @return int
	 */
	static function calculateIncludeExcludeAmount( $amount, $element, $include_elements = [], $exclude_elements = [] ) {
		//Make sure the element isnt in both include and exclude.
		if ( in_array( $element, $include_elements ) && !in_array( $element, $exclude_elements ) ) {
			return $amount;
		} else if ( in_array( $element, $exclude_elements ) && !in_array( $element, $include_elements ) ) {
			return ( $amount * -1 );
		} else {
			return 0;
		}
	}

	/**
	 * @param $data
	 * @param array $include_elements
	 * @param array $exclude_elements
	 * @return bool|int|string
	 */
	static function calculateMultipleColumns( $data, $include_elements = [], $exclude_elements = [] ) {
		if ( !is_array( $data ) ) {
			return false;
		}

		$retval = 0;

		if ( is_array( $include_elements ) ) {
			foreach ( $include_elements as $include_element ) {
				if ( isset( $data[$include_element] ) ) {
					$retval = TTMath::add( $retval, $data[$include_element] );
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		if ( is_array( $exclude_elements ) ) {
			foreach ( $exclude_elements as $exclude_element ) {
				if ( isset( $data[$exclude_element] ) ) {
					$retval = TTMath::sub( $retval, $data[$exclude_element] );
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		return $retval;
	}

	/**
	 * @param $array
	 * @param $element
	 * @param int $start
	 * @return mixed
	 */
	static function getPointerFromArray( $array, $element, $start = 1 ) {
		//Debug::Arr($array, 'Source Array: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text('Searching for Element: '. $element, __FILE__, __LINE__, __METHOD__, 10);
		$keys = array_keys( $array );
		//Debug::Arr($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__, 10);

		//Debug::Text($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__, 10);
		$key = array_search( $element, $keys );

		if ( $key !== false ) {
			$key = ( $key + $start );
		}

		//Debug::Arr($key, 'Result: ', __FILE__, __LINE__, __METHOD__, 10);
		return $key;
	}

	/**
	 * @param $coord
	 * @param $adjust_coord
	 * @return mixed
	 */
	static function AdjustXY( $coord, $adjust_coord ) {
		return ( $coord + $adjust_coord );
	}

	/**
	 * Mititage CSV Injection attacks: See below links for more information:
	 * [1] https://www.owasp.org/index.php/CSV_Excel_Macro_Injection
	 * [2] https://hackerone.com/reports/72785
	 * [3] https://hackerone.com/reports/90131
	 * @param $input
	 * @return mixed
	 */
	static function escapeCSVTriggerChars( $input ) {
		$input = trim( $input );
		$first_char = substr( $input, 0, 1 );
		$trigger_chars = [ '=', '-', '+', '|' ];

		//Be sure to ignore negative numbers/dollar amounts here, as its not expected when using the API to retrieve such data and there shouldn't be risk of injections attacks that are all numeric.
		if ( !is_numeric( $input ) && in_array( $first_char, $trigger_chars ) ) {
			$retval = '\'' . $input; //Prepend with single quote "'" to force it to text.
		} else {
			$retval = $input;
		}

		return str_replace( '|', '\|', $retval ); //Make sure pipes are escaped anywhere in the string.
	}

	/**
	 * @param $data
	 * @param null $columns
	 * @param bool $ignore_last_row
	 * @param bool $include_header
	 * @param string $eol
	 * @return bool|null|string
	 */
	static function Array2CSV( $data, $columns = null, $ignore_last_row = true, $include_header = true, $eol = "\n" ) {
		if ( is_array( $columns ) && count( $columns ) > 0 ) { //If data is FALSE or not an array, we still want to output some CSV encoded data.

			if ( $ignore_last_row === true ) {
				array_pop( $data );
			}

			//Header
			if ( $include_header == true ) {
				$row_header = [];
				foreach ( $columns as $column_name ) {
					$row_header[] = $column_name;
				}
				$out = '"' . implode( '","', $row_header ) . '"' . $eol;
			} else {
				$out = null;
			}

			if ( is_array( $data ) && count( $data ) > 0 ) {
				foreach ( $data as $rows ) {
					$row_values = [];
					foreach ( $columns as $column_key => $column_name ) {
						if ( isset( $rows[$column_key] ) ) {
							$row_values[] = str_replace( "\"", "\"\"", Misc::escapeCSVTriggerChars( $rows[$column_key] ) );
						} else {
							//Make sure we insert blank columns to keep proper order of values.
							$row_values[] = null;
						}
					}

					$out .= '"' . implode( '","', $row_values ) . '"' . $eol;
					unset( $row_values );
				}
			}

			return $out;
		}

		return false;
	}

	/**
	 * @param $data
	 * @param null $columns
	 * @return bool|null|string
	 */
	static function Array2JSON( $data, $columns = null ) {
		if ( is_array( $columns ) && count( $columns ) > 0 ) { //If data is FALSE or not an array, we still want to output some JSON encoded data.

			$out = [];

			if ( is_array( $data ) && count( $data ) > 0 ) {
				foreach ( $data as $rows ) {
					$row_values = [];
					foreach ( $columns as $column_key => $column_name ) {
						if ( isset( $rows[$column_key] ) ) {
							$row_values[$column_name] = $rows[$column_key];
						}
					}

					$out[] = $row_values;
					unset( $row_values );
				}
			}

			return json_encode( $out, JSON_PRETTY_PRINT );
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	static function isJSON( $data ) {
		if ( is_string( $data ) ) {
			json_decode( $data );
			return ( json_last_error() == JSON_ERROR_NONE );
		}

		return false;
	}

	/**
	 * @param $data
	 * @param null $columns
	 * @param null $column_format
	 * @param bool $ignore_last_row
	 * @param bool $include_xml_header
	 * @param string $root_element_name
	 * @param string $row_element_name
	 * @return bool|null|string
	 */
	static function Array2XML( $data, $columns = null, $column_format = null, $ignore_last_row = true, $include_xml_header = false, $root_element_name = 'data', $row_element_name = 'row' ) {
		if ( is_array( $columns ) && count( $columns ) > 0 ) { //If data is FALSE or not an array, we still want to output some XML encoded data.

			if ( $ignore_last_row === true ) {
				array_pop( $data );
			}

			//Debug::Arr($column_format, 'Column Format: ', __FILE__, __LINE__, __METHOD__, 10);

			$out = null;

			if ( $include_xml_header == true ) {
				$out .= '<?xml version=\'1.0\' encoding=\'ISO-8859-1\'?>' . "\n";
			}

			$out .= '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">' . "\n";
			$out .= '	 <xsd:element name="' . $root_element_name . '">' . "\n";
			$out .= '		 <xsd:complexType>' . "\n";
			$out .= '			 <xsd:sequence>' . "\n";
			$out .= '				 <xsd:element name="' . $row_element_name . '">' . "\n";
			$out .= '					 <xsd:complexType>' . "\n";
			$out .= '						 <xsd:sequence>' . "\n";
			foreach ( $columns as $column_key => $column_name ) {
				$data_type = 'string';
				if ( is_array( $column_format ) && isset( $column_format[$column_key] ) ) {
					switch ( $column_format[$column_key] ) {
						case 'report_date':
							$data_type = 'string';
							break;
						case 'currency':
						case 'percent':
						case 'numeric':
							$data_type = 'decimal';
							break;
						case 'time_unit':
							$data_type = 'decimal';
							break;
						case 'date_stamp':
							$data_type = 'date';
							break;
						case 'time':
							$data_type = 'time';
							break;
						case 'time_stamp':
							$data_type = 'dateTime';
							break;
						case 'boolean':
							$data_type = 'string';
							break;
						default:
							$data_type = 'string';
							break;
					}
				}
				$out .= '							 <xsd:element name="' . $column_key . '" type="xsd:' . $data_type . '"/>' . "\n";
			}
			unset( $column_name ); //code standards
			$out .= '						 </xsd:sequence>' . "\n";
			$out .= '					 </xsd:complexType>' . "\n";
			$out .= '				 </xsd:element>' . "\n";
			$out .= '			 </xsd:sequence>' . "\n";
			$out .= '		 </xsd:complexType>' . "\n";
			$out .= '	 </xsd:element>' . "\n";
			$out .= '</xsd:schema>' . "\n";

			if ( $root_element_name != '' ) {
				$out .= '<' . $root_element_name . '>' . "\n";
			}

			if ( is_array( $data ) && count( $data ) > 0 ) {
				foreach ( $data as $rows ) {
					$out .= '<' . $row_element_name . '>' . "\n";
					foreach ( $columns as $column_key => $column_name ) {
						if ( isset( $rows[$column_key] ) ) {
							$out .= '	 <' . $column_key . '>' . $rows[$column_key] . '</' . $column_key . '>' . "\n";
						}
					}
					$out .= '</' . $row_element_name . '>' . "\n";
				}
			}

			if ( $root_element_name != '' ) {
				$out .= '</' . $root_element_name . '>' . "\n";
			}

			//Debug::Arr($out, 'XML: ', __FILE__, __LINE__, __METHOD__, 10);

			return $out;
		}

		return false;
	}

	/**
	 * @param $arr
	 * @param $search_key
	 * @param $search_value
	 * @return bool
	 */
	static function inArrayByKeyAndValue( $arr, $search_key, $search_value ) {
		if ( !is_array( $arr ) && $search_key != '' && $search_value != '' ) {
			return false;
		}

		//Debug::Text('Search Key: '. $search_key .' Search Value: '. $search_value, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($arr, 'Hay Stack: ', __FILE__, __LINE__, __METHOD__, 10);

		foreach ( $arr as $arr_value ) {
			if ( isset( $arr_value[$search_key] ) ) {
				if ( $arr_value[$search_key] == $search_value ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * This function is used to quickly preset array key => value pairs so we don't
	 * have to have so many isset() checks throughout the code.
	 * @param $arr
	 * @param $keys
	 * @param null $preset_value
	 * @return array|bool
	 */
	static function preSetArrayValues( $arr, $keys, $preset_value = null ) {
		if ( ( $arr == '' || is_bool( $arr ) || is_null( $arr ) || is_array( $arr ) || is_object( $arr ) ) && is_array( $keys ) ) {
			if ( !is_array( $arr ) && !is_object( $arr ) ) { //Avoid PHP deprecation warning: Automatic conversion of false to array is deprecated
				$arr = [];
			}

 			foreach ( $keys as $key ) {
				if ( is_object( $arr ) ) {
					if ( !isset( $arr->$key ) ) {
						$arr->$key = $preset_value;
					}
				} else {
					if ( !isset( $arr[$key] ) ) {
						$arr[$key] = $preset_value;
					}
				}
			}
		} else {
			Debug::Arr( $arr, 'ERROR: Unable to initialize preset array values! Current variable is: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $arr;
	}

	/**
	 * @param $file_name
	 * @param bool $buffer
	 * @param bool $keep_charset
	 * @param string $unknown_type
	 * @return bool|mixed|string
	 */
	static function getMimeType( $file_name, $buffer = false, $keep_charset = false, $unknown_type = 'application/octet-stream' ) {
		if ( function_exists( 'finfo_buffer' ) ) { //finfo extension in PHP v5.3+
			if ( $buffer == false && file_exists( $file_name ) ) {
				//Its a filename passed in.
				$finfo = finfo_open( FILEINFO_MIME_TYPE ); // return mime type ala mimetype extension
				$retval = finfo_file( $finfo, $file_name );
				finfo_close( $finfo );
			} else if ( $buffer == true && $file_name != '' ) {
				//Its a string buffer;
				$finfo = new finfo( FILEINFO_MIME );
				$retval = $finfo->buffer( $file_name );
			}

			if ( isset( $retval ) ) {
				if ( $keep_charset == false ) {
					$split_retval = explode( ';', $retval );
					if ( is_array( $split_retval ) && isset( $split_retval[0] ) ) {
						$retval = $split_retval[0];
					}
				}
				Debug::text( 'MimeType: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

				return $retval;
			}
		} else {
			//Attempt to detect mime type with PEAR MIME class.
			if ( $buffer == false && file_exists( $file_name ) ) {
				$retval = MIME_Type::autoDetect( $file_name );
				if ( is_object( $retval ) ) { //MimeType failed.
					//Attempt to detect mime type manually when finfo extension and PEAR Mime Type is not installed (windows)
					$extension = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
					switch ( $extension ) {
						case 'jpg':
							$retval = 'image/jpeg';
							break;
						case 'png':
							$retval = 'image/png';
							break;
						case 'gif':
							$retval = 'image/gif';
							break;
						default:
							$retval = $unknown_type;
							break;
					}
				}

				return $retval;
			}
		}

		return false;
	}

	static function isValidUTF8Encoding( $file_name ) {
		if ( function_exists( 'mb_check_encoding' ) && file_exists( $file_name ) ) {
			$data = file_get_contents( $file_name );
			if ( mb_check_encoding( $data, 'UTF-8' ) === false ) {
				Debug::Text( 'ERROR: Bad UTF8 encoding in File: '. $file_name, __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $file
	 * @param bool $skip_blank_lines
	 * @return int
	 */
	static function countLinesInFile( $file, $skip_blank_lines = true ) {
		ini_set( 'auto_detect_line_endings', true ); //PHP can have problems detecting Mac/OSX line endings in some case, this should help solve that.

		$line_count = 0;
		$skipped_lines = 0;
		$handle = fopen( $file, 'r' );
		while ( !feof( $handle ) ) {
			$line = fgets( $handle, 4096 );
			if ( $skip_blank_lines == true && $line != '' && ( trim( $line ) == '' || trim( $line, ',' ) == "\n" ) ) { //Ignore lines that are all commas (ie: ",,,,,,,") which can often happen at the end of a CSV file that rows were deleted from.
				$skipped_lines++;
			} else {
				$line_count += substr_count( $line, "\n" );
			}
		}

		fclose( $handle );

		ini_set( 'auto_detect_line_endings', false );

		Debug::text( 'File has total lines: ' . $line_count . ' Blank Lines: ' . $skipped_lines, __FILE__, __LINE__, __METHOD__, 10 );

		return $line_count;
	}


	/**
	 * Converts the first sheet in an Excel file to CSV.
	 * @param $xls_file
	 * @return bool
	 */
	static function convertExcelToCSV( $xls_file ) {
		Debug::text( 'Attempting Excel to CSV conversion of: ' . $xls_file, __FILE__, __LINE__, __METHOD__, 10 );

		//mime_content_type might not work properly on Windows. So if its not available just accept any file type.
		if ( function_exists( 'mime_content_type' ) ) {
			$mime_type = mime_content_type( $xls_file );
			if ( $mime_type !== false && !in_array( $mime_type, [ 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'application/vnd.oasis.opendocument.spreadsheet' ] ) ) { //This should match upload_file.php
				Debug::text( 'Invalid MIME TYPE: ' . $mime_type, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		try {
			$orig_max_execution_time = ini_get( 'max_execution_time' );
			ini_set( 'max_execution_time', 120 ); //Force execution time to something small enough to just read the XLSX file, in case something blows up on us.

			$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load( $xls_file );

			// Export to CSV file.
			$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Csv' );
			$writer->setSheetIndex( $spreadsheet->getActiveSheetIndex() ?? 0 );   // Select which sheet to export, default to the active sheet, which is likely the one they are looking at when saving the document.
			$writer->setDelimiter( ',' );  // Set delimiter.
			$writer->save( $xls_file );

			unset( $spreadsheet, $writer );

			ini_set( 'max_execution_time', $orig_max_execution_time );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Detects the delimiter of a CSV file (can be semicolon, comma or pipe) by trying every delimiter, then
	 * counting how many potential columns could be found with this delimiter and removing the delimiter from array of
	 * only one columns could be created (without a working limiter you'll always have "one" column: the entire row).
	 * The delimiter that created the most columns is returned.
	 *
	 * @param string $file path to the CSV file
	 * @return string|null nullable delimiter
	 * @throws \Exception
	 */
	public static function detectFileDelimiter( $file ) {
		$delimiters = [
				',' => 0,
				';' => 0,
				':' => 0,
				'|' => 0,
				"\t" => 0, //Tab
				//Can't use space, as column headers will often have many spaces for multiple names.
		];

		$handle = fopen( $file, 'r' );
		$first_line = fgets( $handle );
		fclose( $handle );

		foreach ( $delimiters as $delimiter_character => $delimiter_count ) {
			$found_columns_with_this_delimiter = count( str_getcsv( $first_line, $delimiter_character ) );
			if ( $found_columns_with_this_delimiter > 1 ) {
				$delimiters[$delimiter_character] = $found_columns_with_this_delimiter;
			} else {
				unset( $delimiters[$delimiter_character] );
			}
		}

		if ( !empty( $delimiters ) ) {
			return array_search( max( $delimiters ), $delimiters );
		}

		return false; //The CSV delimiter could not been found. Should be semicolon, comma or pipe!
	}

	/**
	 * @param $file
	 * @param bool $head
	 * @param bool $first_column
	 * @param string $delim
	 * @param int $len
	 * @param null $max_lines
	 * @return array|bool
	 */
	static function parseCSV( $file, $head = false, $first_column = false, $delim = ',', $len = 9216, $max_lines = null ) {
		if ( !file_exists( $file ) ) {
			Debug::text( 'Files does not exist: ' . $file, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//mime_content_type might not work properly on Windows. So if its not available just accept any file type.
		if ( function_exists( 'mime_content_type' ) ) {
			$mime_type = mime_content_type( $file );
			if ( $mime_type !== false && !in_array( $mime_type, [ 'text/plain', 'plain/text', 'text/comma-separated-values', 'text/csv', 'application/csv', 'text/anytext', 'text/x-c', 'application/octet-stream' ] ) ) { //This should match upload_file.php
				Debug::text( 'Invalid MIME TYPE: ' . $mime_type, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		//If no delimiter is specified, try to detect it automatically.
		if ( empty( $delim ) ) {
			$delim = Misc::detectFileDelimiter( $file );
			Debug::text( '  Detected delimiter as: ' . $delim, __FILE__, __LINE__, __METHOD__, 10 );
			if ( $delim === false ) {
				Debug::text( '  Delimiter is empty, unable to parse file!', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}
		}

		$return = [];
		$handle = fopen( $file, 'r' );
		if ( $head !== false ) {
			if ( $first_column !== false ) {
				while ( ( $header = fgetcsv( $handle, $len, $delim ) ) !== false ) {
					if ( $header[0] == $first_column ) {
						$found_header = true;
						break;
					}
				}

				if ( $found_header !== true ) {
					return false;
				}
			} else {
				$header = fgetcsv( $handle, $len, $delim );
			}
		}

		//Excel adds a Byte Order Mark (BOM) to the beginning of files with UTF-8 characters. That needs to be stripped off otherwise it looks like a space and columns don't match up.
		if ( isset( $header ) && isset( $header[0] ) ) {
			$header[0] = str_replace( "\xEF\xBB\xBF", '', $header[0] );
		}

		$i = 1;
		while ( ( $data = fgetcsv( $handle, $len, $delim ) ) !== false ) {
			if ( $data !== [ null ] ) { // Ignore blank lines
				//Skip lines with commas (columns), but *all* columns are blank. The raw line would look like this: ,,,,,,,,,,,... OR "","","","","","",...
				if ( strlen( implode( '', $data ) ) == 0 ) {
					continue;
				}

				if ( $head == true && isset( $header ) ) {
					$row = [];
					foreach ( $header as $key => $heading ) {
						$row[trim( $heading )] = ( isset( $data[$key] ) ) ? $data[$key] : '';
					}
					$return[] = $row;
				} else {
					$return[] = $data;
				}

				if ( $max_lines !== null && $max_lines != '' && $i == $max_lines ) {
					break;
				}

				$i++;
			}
		}

		fclose( $handle );

		ini_set( 'auto_detect_line_endings', false );

		if ( empty( $return ) ) {
			$return = false;
		}

		return $return;
	}

	/**
	 * censor part of a string for purposes of displaying things like SIN, bank accounts, credit card numbers.
	 * @param $str
	 * @param string $censor_char
	 * @param int|null $min_first_chunk_size
	 * @param int|null $max_first_chunk_size
	 * @param int|null $min_last_chunk_size
	 * @param int|null $max_last_chunk_size
	 * @return bool|string
	 */
	static function censorString( $str, $censor_char = '*', $min_first_chunk_size = null, $max_first_chunk_size = null, $min_last_chunk_size = null, $max_last_chunk_size = null ) {
		$str = (string)$str;

		$length = strlen( $str );
		if ( $length == 0 ) {
			return $str;
		}

		if ( $str != '' ) {
			if ( $length < 3 || $length <= ( $min_first_chunk_size + $min_last_chunk_size + 2 ) ) {
				return str_repeat( $censor_char, $length ); //Default to all censored.
			} else {
				$first_chunk_size = ( floor( $length / 3 ) );
				$last_chunk_size = ( floor( $length / 3 ) );

				if ( $min_first_chunk_size !== null && $first_chunk_size < $min_first_chunk_size ) {
					$first_chunk_size = $min_first_chunk_size;
				}
				if ( $max_first_chunk_size !== null && $first_chunk_size > $max_first_chunk_size ) {
					$first_chunk_size = $max_first_chunk_size;
				}

				if ( $min_last_chunk_size !== null && $last_chunk_size < $min_last_chunk_size ) {
					$last_chunk_size = $min_last_chunk_size;
				}
				if ( $max_last_chunk_size !== null && $last_chunk_size > $max_last_chunk_size ) {
					$last_chunk_size = $max_last_chunk_size;
				}

				//Grab the first 1, and last 4 digits.
				$first_chunk = substr( $str, 0, $first_chunk_size );
				$last_chunk = substr( $str, ( $last_chunk_size * -1 ) );

				$middle_chunk_size = ( $length - ( $first_chunk_size + $last_chunk_size ) );

				$retval = $first_chunk . str_repeat( $censor_char, $middle_chunk_size ) . $last_chunk;

				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param $str
	 * @param null $salt
	 * @param null $key
	 * @return bool|string
	 */
	static function encrypt( $str, $salt = null, $key = null ) {
		if ( $str == '' || $str === false || empty( $str ) ) {
			return false;
		}

		if ( $key == null || $key == '' ) {
			$key = TTPassword::getPasswordSalt();
		}
		$key .= $salt;

		$strong_seed = true; //passed by ref so we need it as a variable.
		$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'AES-256-CTR' ), $strong_seed );

		$encrypted_data = '2:' . base64_encode( $iv ) . ':' . base64_encode( openssl_encrypt( trim( $str ), 'AES-256-CTR', $key, 0, $iv ) );

		return $encrypted_data;
	}

	/**
	 * We can't reliably test that decryption was successful, so type checking should be done in the calling function. (see RemittanceDestinationAccountFactory::getValue3())
	 * @param $str
	 * @param null $salt
	 * @param null $key
	 * @return bool|string
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
	 */
	static function decrypt( $str, $salt = null, $key = null ) {
		if ( $key == null || $key == '' ) {
			$key = TTPassword::getPasswordSalt();
		}

		$key .= $salt;

		if ( $str == '' ) {
			return false;
		}

		$version = 1;
		if ( strpos( $str, ':' ) !== false ) {
			$bits = explode( ':', $str );
			$version = $bits[0];
			$iv = base64_decode( $bits[1] );
			if ( isset( $bits[2] ) ) {
				$encrypted_string = $bits[2];
			} else {
				$encrypted_string = $str;
			}
			unset( $bits );

			if ( !isset( $version ) || $version == '' || !isset( $iv ) || $iv == '' ) {
				Debug::Arr( $encrypted_string, 'ERROR: Required encryption data is blank: ' . $str, __FILE__, __LINE__, __METHOD__, 10 );

				return $str; //allow for returning the unencrypted values that contain colons;
			}
		} else {
			$encrypted_string = $str;
		}

		//Check to make sure $encrypted_string is base64_encoded.
		if ( base64_encode( base64_decode( $encrypted_string, true ) ) !== $encrypted_string ) {
			Debug::Arr( $encrypted_string, 'ERROR: String is not base64_encoded...', __FILE__, __LINE__, __METHOD__, 10 );

			return $str; //allow for unencrypted values
		} else {
			$encrypted_string = base64_decode( $encrypted_string );

			switch ( $version ) {
				case 1:
					//backwards compatibility for v1 encryption.
					if ( function_exists( 'mcrypt_module_open' ) ) {
						$td = @mcrypt_module_open( 'tripledes', '', 'ecb', '' );
						$iv = @mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
						$max_key_size = @mcrypt_enc_get_key_size( $td );
						@mcrypt_generic_init( $td, substr( $key, 0, $max_key_size ), $iv );
						$unencrypted_data = rtrim( @mdecrypt_generic( $td, $encrypted_string ) );
						@mcrypt_generic_deinit( $td );
						@mcrypt_module_close( $td );
					} else {
						Debug::Text( 'ERROR: MCRYPT extension is not installed!', __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					}

					break;
				case 2: //'AES-256-CTR'
				default:
					$unencrypted_data = openssl_decrypt( $encrypted_string, 'AES-256-CTR', $key, 0, $iv );

					break;
			}
		}

		if ( $unencrypted_data != '' && ctype_print( $unencrypted_data ) === false ) { //Check for only ASCII characters.
			Debug::Text( '  ERROR: Unencrypted data is not ASCII characters, decryption likely failed! Raw Data: '. $unencrypted_data, __FILE__, __LINE__, __METHOD__, 10 );
			$unencrypted_data = null;
		}

		/**
		 * We can't reliably test that decryption was successful, so type checking should be done in the calling function. (see RemittanceDestinationAccountFactory::getValue3())
		 */
		return $unencrypted_data;
	}

	/**
	 * @param $values
	 * @param null $name
	 * @param bool $assoc
	 * @param bool $object
	 * @return string
	 */
	static function getJSArray( $values, $name = null, $assoc = false, $object = false ) {
		if ( $name != '' && (bool)$assoc == true ) {
			$retval = 'new Array();';
			if ( is_array( $values ) && count( $values ) > 0 ) {
				foreach ( $values as $key => $value ) {
					$retval .= $name . '[\'' . $key . '\']=\'' . $value . '\';';
				}
			}
		} else if ( $name != '' && (bool)$object == true ) { //For multidimensional objects.
			$retval = ' {';
			if ( is_array( $values ) && count( $values ) > 0 ) {
				foreach ( $values as $key => $value ) {
					$retval .= $key . ': ';
					if ( is_array( $value ) ) {
						$retval .= '{';
						foreach ( $value as $key2 => $value2 ) {
							$retval .= $key2 . ': \'' . $value2 . '\', ';
						}
						$retval .= '}, ';
					} else {
						$retval .= $key . ': \'' . $value . '\', ';
					}
				}
			}
			$retval .= '} ';
		} else {
			$retval = 'new Array("';
			if ( is_array( $values ) && count( $values ) > 0 ) {
				$retval .= implode( '","', $values );
			}
			$retval .= '");';
		}

		return $retval;
	}

	/**
	 * Uses the internal array pointer to get array neighnors.
	 * @param $arr
	 * @param $key
	 * @param string $neighbor
	 * @return array
	 */
	static function getArrayNeighbors( $arr, $key, $neighbor = 'both' ) {
		$neighbor = strtolower( $neighbor );
		//Neighor can be: Prev, Next, Both

		$retarr = [ 'prev' => false, 'next' => false ];

		$keys = array_keys( $arr );
		$key_indexes = array_flip( $keys );

		if ( $neighbor == 'prev' || $neighbor == 'both' ) {
			if ( isset( $keys[( $key_indexes[$key] - 1 )] ) ) {
				$retarr['prev'] = $keys[( $key_indexes[$key] - 1 )];
			}
		}

		if ( $neighbor == 'next' || $neighbor == 'both' ) {
			if ( isset( $keys[( $key_indexes[$key] + 1 )] ) ) {
				$retarr['next'] = $keys[( $key_indexes[$key] + 1 )];
			}
		}

		//next($arr);

		return $retarr;
	}

	/**
	 * @return string
	 */
	static function getURLProtocol() {
		$retval = 'http';
		if ( Misc::isSSL() == true ) {
			$retval .= 's';
		}

		return $retval;
	}

	/**
	 * @param $url
	 * @return bool|mixed
	 */
	static function getRemoteHTTPFileSize( $url ) {
		if ( function_exists( 'curl_exec' ) ) {
			Debug::Text( 'Using CURL for HTTP...', __FILE__, __LINE__, __METHOD__, 10 );

			$curl = curl_init();

			//Don't require SSL verification, as the SSL certs may be out-of-date: http://stackoverflow.com/questions/316099/cant-connect-to-https-site-using-curl-returns-0-length-content-instead-what-c
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );

			curl_setopt( $curl, CURLOPT_URL, $url );

			// Issue a HEAD request and follow any redirects.
			curl_setopt( $curl, CURLOPT_NOBODY, true );
			curl_setopt( $curl, CURLOPT_HEADER, true );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $curl, CURLOPT_USERAGENT, APPLICATION_NAME . ' v' . APPLICATION_VERSION );

			curl_exec( $curl );
			$size = curl_getinfo( $curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
			curl_close( $curl );

			return $size;
		} else {
			Debug::Text( 'Using PHP streams for HTTP...', __FILE__, __LINE__, __METHOD__, 10 );
			$headers = @get_headers( $url, 1 );
			if ( $headers === false ) { //Failure downloading headers from URL.
				return false;
			}

			$headers = array_change_key_case( $headers );
			if ( isset( $headers[0] ) && stripos( $headers[0], '404 Not Found' ) !== false ) {
				return false;
			}

			$retval = isset( $headers['content-length'] ) ? $headers['content-length'] : false;

			return $retval;
		}
	}

	/**
	 * @param $url
	 * @param $file_name
	 * @param $timeout int Seconds
	 * @return bool|int
	 */
	static function downloadHTTPFile( $url, $file_name, $timeout = 7200 ) {
		Debug::Text( 'Downloading: ' . $url . ' To: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( function_exists( 'curl_exec' ) ) {
			Debug::Text( 'Using CURL for HTTP...', __FILE__, __LINE__, __METHOD__, 10 );

			if ( is_writable( dirname( $file_name ) ) == true && ( file_exists( $file_name ) == false || ( file_exists( $file_name ) == true && is_writable( $file_name ) ) ) ) {
				// Open file to write
				$fp = @fopen( $file_name, 'w+' );
				if ( $fp !== false ) {
					$curl = curl_init();

					// Don't require SSL verification, as the SSL certs may be out-of-date: http://stackoverflow.com/questions/316099/cant-connect-to-https-site-using-curl-returns-0-length-content-instead-what-c
					curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );

					curl_setopt( $curl, CURLOPT_URL, $url );
					curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
					curl_setopt( $curl, CURLOPT_RETURNTRANSFER, false ); // Set return transfer to false
					curl_setopt( $curl, CURLOPT_BINARYTRANSFER, true );
					curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
					curl_setopt( $curl, CURLOPT_TIMEOUT, $timeout ); // 0 = never timeout.
					curl_setopt( $curl, CURLOPT_FILE, $fp );         // Write data to local file
					curl_setopt( $curl, CURLOPT_USERAGENT, APPLICATION_NAME . ' v' . APPLICATION_VERSION );

					// Execute CURL and check for errors
					if ( curl_exec( $curl ) === false ) {
						Debug::Text( '  ERROR: Download failed... CURL: ' . curl_error( $curl ), __FILE__, __LINE__, __METHOD__, 10 );
						fclose( $fp );
						curl_close( $curl );

						return false;
					}

					curl_close( $curl );
					fclose( $fp );

					if ( file_exists( $file_name ) ) {
						$file_size = filesize( $file_name );
						if ( $file_size > 0 ) {
							Debug::Text( 'Successfully downloaded... Size: ' . $file_size, __FILE__, __LINE__, __METHOD__, 10 );
							return (int)$file_size;
						} else {
							Debug::Text( 'ERROR: Downloaded file is empty: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
							return false;
						}
					} else {
						Debug::Text( 'ERROR: File does not exist after download attempt: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
						return false;
					}
				} else {
					Debug::Text( 'ERROR: Unable to open file for download/writing, likely permission problem?: ' . $file_name . ' Error: ' . Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
					return false;
				}
			} else {
				Debug::Text( 'ERROR: Download directory/file not writable, likely permission problem?: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		} else {
			Debug::Text( 'Using PHP streams for HTTP...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = @file_put_contents( $file_name, fopen( $url, 'r' ), LOCK_EX );
			if ( $retval === false ) {
				Debug::Text( 'ERROR: Unable to save/download file, likely permission or network access problem?: ' . $file_name .' Error: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			}

			return $retval;
		}
	}

	/**
	 * @return string
	 */
	static function getEmailDomain() {
		global $config_vars;

		if ( isset( $config_vars['mail']['email_domain'] ) && $config_vars['mail']['email_domain'] != '' ) {
			$domain = $config_vars['mail']['email_domain'];
		} else if ( isset( $config_vars['other']['email_domain'] ) && $config_vars['other']['email_domain'] != '' ) {
			$domain = $config_vars['other']['email_domain'];
		} else {
			$domain = self::getHostName( false );
			Debug::Text( 'No From Email Domain specified, falling back to regular hostname: '. $domain, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $domain;
	}

	/**
	 * @return string
	 */
	static function getEmailLocalPart() {
		global $config_vars;

		if ( isset( $config_vars['mail']['email_local_part'] ) && $config_vars['mail']['email_local_part'] != '' ) {
			$local_part = $config_vars['mail']['email_local_part'];
		} else if ( isset( $config_vars['other']['email_local_part'] ) && $config_vars['other']['email_local_part'] != '' ) {
			$local_part = $config_vars['other']['email_local_part'];
		} else {
			Debug::Text( 'No Email Local Part set, falling back to default...', __FILE__, __LINE__, __METHOD__, 10 );
			$local_part = 'DoNotReply';
		}

		return $local_part;
	}

	/**
	 * @param null $email
	 * @return string
	 */
	static function getEmailReturnPathLocalPart( $email = null ) {
		global $config_vars;

		if ( isset( $config_vars['other']['email_return_path_local_part'] ) && $config_vars['other']['email_return_path_local_part'] != '' ) {
			$local_part = $config_vars['other']['email_return_path_local_part'];
		} else {
			Debug::Text( 'No Email Local Part set, falling back to default...', __FILE__, __LINE__, __METHOD__, 10 );
			$local_part = self::getEmailLocalPart();
		}

		//In case we need to put the original TO address in the bounce local part.
		//This could be an array in some cases.
//		if ( $email != '' ) {
//			$local_part .= '+';
//		}

		return $local_part;
	}

	/**
	 * Detect if the visitor was referred by a search engine. If so, they likely found the wrong domain, so redirect back to a common page.
	 * @return bool
	 */
	static function isSearchEngineReferrer() {
		//Ignore this when resetting passwords, as Gmail app will often put google.com as the referer since link clicks are intercepted.
		if ( isset( $_SERVER['HTTP_REFERER'] ) && !isset( $_GET['resetpassword'] ) ) {
			$referer = $_SERVER['HTTP_REFERER'];
			if ( ( stripos( $referer, 'google.com' ) !== false || stripos( $referer, 'bing.com' ) !== false ) ) {
				Debug::Text( 'Search Engine Referrer: ' . $referer, __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $_SERVER, 'All Server Headers: ', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the domain the user is seeing in their browser matches the configured domain that should be used.
	 * If not we can then do a redirect.
	 * @return bool
	 */
	static function checkValidDomain() {
		global $config_vars;
		if ( PRODUCTION == true && isset( $config_vars['other']['enable_csrf_validation'] ) && $config_vars['other']['enable_csrf_validation'] == true ) {
			//Use HTTP_HOST rather than getHostName() as the same site can be referenced with multiple different host names
			//Especially considering on-site installs that default to 'localhost'
			//If deployment ondemand is set, then we assume SERVER_NAME is correct and revert to using that instead of HTTP_HOST which has potential to be forged.
			//Apache's UseCanonicalName On configuration directive can help ensure the SERVER_NAME is always correct and not masked.
			if ( DEPLOYMENT_ON_DEMAND == false && isset( $_SERVER['HTTP_HOST'] ) ) {
				$host_name = $_SERVER['HTTP_HOST'];
			} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$host_name = $_SERVER['SERVER_NAME'];
			} else if ( isset( $_SERVER['HOSTNAME'] ) ) {
				$host_name = $_SERVER['HOSTNAME'];
			} else {
				$host_name = '';
			}

			if ( isset( $config_vars['other']['hostname'] ) && $config_vars['other']['hostname'] != '' ) {
				$search_result = strpos( $config_vars['other']['hostname'], $host_name );
				if ( $search_result === false || (int)$search_result >= 8 ) { //Check to see if .ini hostname is found within SERVER_NAME in less than the first 8 chars, so we ignore https://.
					$redirect_url = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL();
					Debug::Text( 'Web Server Hostname: ' . $host_name . ' does not match .ini specified hostname: ' . $config_vars['other']['hostname'] . ' Redirect: ' . $redirect_url, __FILE__, __LINE__, __METHOD__, 10 );

					$rl = TTNew( 'RateLimit' ); /** @var RateLimit $rl */
					$rl->setID( 'authentication_' . Misc::getRemoteIPAddress() );
					$rl->setAllowedCalls( 5 );
					$rl->setTimeFrame( 60 ); //1 minute

					sleep( 1 ); //Help prevent fast redirect loops.
					if ( $rl->check() == false ) {
						Debug::Text( 'ERROR: Excessive redirects... sending to down for maintenance page to stop the loop: ' . Misc::getRemoteIPAddress() . ' for up to 1 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
						Redirect::Page( URLBuilder::getURL( [ 'exception' => 'domain_redirect_loop' ], Environment::getBaseURL() . 'html5/DownForMaintenance.php' ) );
					} else {
						Redirect::Page( URLBuilder::getURL( null, $redirect_url ) );
					}
				}
				//else {
				//	Debug::Text( 'Domain matches!', __FILE__, __LINE__, __METHOD__, 10);
				//}

			}
		}

		return true;
	}

	/**
	 * @param $host_name
	 * @return string
	 */
	static function getHostNameWithoutSubDomain( $host_name ) {
		$split_host_name = explode( '.', $host_name );
		if ( count( $split_host_name ) > 2 ) {
			unset( $split_host_name[0] );

			return implode( '.', $split_host_name );
		}

		return $host_name;
	}

	/**
	 * @param bool $include_port
	 * @return string
	 */
	static function getHostName( $include_port = true ) {
		global $config_vars;

		$server_port = null;

		//Can't use the SERVER_PORT if its going through a proxy, as the server port and proxy port could be different, but we don't know what the proxy port is in all cases.
		if ( isset( $_SERVER['SERVER_PORT'] )
				&& isset( $config_vars['other']['proxy_protocol_header_name'] ) && $config_vars['other']['proxy_protocol_header_name'] != '' && !isset($_SERVER[$config_vars['other']['proxy_protocol_header_name']]) ) {
			$server_port = ':' . (int)$_SERVER['SERVER_PORT'];
		}

		if ( defined( 'DEPLOYMENT_ON_DEMAND' ) && DEPLOYMENT_ON_DEMAND == true && isset( $config_vars['other']['hostname'] ) && $config_vars['other']['hostname'] != '' ) {
			$server_domain = $config_vars['other']['hostname'];
		} else {
			//Try server hostname/servername first, than fallback on .ini hostname setting.
			//If the admin sets the hostname in the .ini file, always use that, as the servers hostname from the CLI could be incorrect.
			if ( isset( $config_vars['other']['hostname'] ) && $config_vars['other']['hostname'] != '' ) {
				$server_domain = trim( $config_vars['other']['hostname'] ); //Trim gets rid of newlines that could get into the .ini file. WARNING(2): Header may not contain more than a single header, new line detected
				if ( strpos( $server_domain, ':' ) === false ) {
					//Add port if its not already specified.
					$server_domain .= $server_port;
				}
			} else if ( isset( $_SERVER['HTTP_HOST'] ) ) { //Use HTTP_HOST instead of SERVER_NAME first so it includes any custom ports.
				$server_domain = $_SERVER['HTTP_HOST'];
			} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$server_domain = $_SERVER['SERVER_NAME'] . $server_port;
			} else if ( isset( $_SERVER['HOSTNAME'] ) ) {
				$server_domain = $_SERVER['HOSTNAME'] . $server_port;
			} else {
				Debug::Text( 'Unable to determine hostname, falling back to localhost...', __FILE__, __LINE__, __METHOD__, 10 );
				$server_domain = 'localhost' . $server_port;
			}
		}

		if ( $include_port == false && strpos( $server_domain, ':' ) !== false ) {
			//strip off port, important for sending emails.
			$server_domain = substr( $server_domain, 0, strpos( $server_domain, ':' ) );
		}

		return $server_domain;
	}

	/**
	 * @param $database_host_string
	 * @return array
	 */
	static function parseDatabaseHostString( $database_host_string ) {
		$retarr = [];

		$db_hosts = explode( ',', str_replace( ' ', '', $database_host_string ) );
		if ( is_array( $db_hosts ) ) {
			$i = 0;
			foreach ( $db_hosts as $db_host ) {
				$db_host_split = explode( '#', $db_host );

				$db_host = $db_host_split[0];
				$weight = ( isset( $db_host_split[1] ) ) ? $db_host_split[1] : 1;

				$retarr[] = [ $db_host, ( $i == 0 ) ? 'write' : 'readonly', $weight ];

				$i++;
			}
		}

		//Debug::Arr( $retarr,  'Parsed Database Connections: ', __FILE__, __LINE__, __METHOD__, 1);
		return $retarr;
	}

	/**
	 * @param $address
	 * @param int $port
	 * @param int $timeout
	 * @return bool
	 */
	static function isOpenPort( $address, $port = 80, $timeout = 3 ) {
		$checkport = @fsockopen( $address, $port, $errnum, $errstr, $timeout ); //The 2 is the time of ping in secs

		//Check if port is closed or open...
		if ( $checkport == false ) {
			return false;
		}

		return true;
	}

	/**
	 * Accepts a search_str and key=>val array that it searches through, to return the array key of the closest fuzzy match.
	 * @param $search_str
	 * @param $search_arr
	 * @param int $minimum_percent_match
	 * @param bool $return_all_matches
	 * @return array|bool|mixed
	 */
	static function findClosestMatch( $search_str, $search_arr, $minimum_percent_match = 0, $return_all_matches = false ) {
		if ( $search_str == '' ) {
			return false;
		}

		if ( !is_array( $search_arr ) || count( $search_arr ) == 0 ) {
			return false;
		}

		$matches = [];
		foreach ( $search_arr as $key => $search_val ) {
			similar_text( strtolower( $search_str ), strtolower( $search_val ), $percent );
			if ( $percent >= $minimum_percent_match ) {
				$matches[$key] = $percent;
			}
		}

		if ( empty( $matches ) == false ) {
			arsort( $matches );

			if ( $return_all_matches == true ) {
				return $matches;
			}

			//Debug::Arr( $search_arr, 'Search Str: '. $search_str .' Search Array: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr( $matches, 'Matches: ', __FILE__, __LINE__, __METHOD__, 10);

			reset( $matches );

			return key( $matches );
		}

		//Debug::Text('No match found for: '. $search_str, __FILE__, __LINE__, __METHOD__, 10);

		return false;
	}

	/**
	 * @param $first_name
	 * @param $middle_name
	 * @param $last_name
	 * @param bool $reverse
	 * @param bool $include_middle
	 * @return bool|string
	 */
	static function getFullName( $first_name, $middle_name, $last_name, $reverse = false, $include_middle = true ) {
		if ( $first_name != '' && $last_name != '' ) {
			if ( $reverse === true ) {
				$retval = $last_name . ', ' . $first_name;
				if ( $include_middle == true && $middle_name != '' ) {
					$retval .= ' ' . $middle_name[0] . '.'; //Use just the middle initial.
				}
			} else {
				$retval = $first_name;
				if ( $include_middle == true && $middle_name != '' ) {
					$retval .= ' ' . $middle_name[0] . '.'; //Use just the middle initial.
				}
				$retval .= ' ' . $last_name;
			}

			return $retval;
		}

		return false;
	}

	/**
	 * @param $city
	 * @param $province
	 * @param $postal_code
	 * @return string
	 */
	static function getCityAndProvinceAndPostalCode( $city, $province, $postal_code ) {
		$retval = '';
		if ( $city != '' ) {
			$retval .= $city;
		}

		if ( $province != '' && $province != '00' ) {
			if ( $retval != '' ) {
				$retval .= ',';
			}
			$retval .= ' ' . $province;
		}

		if ( $postal_code != '' ) {
			$retval .= ' ' . strtoupper( $postal_code );
		}

		return $retval;
	}

	/**
	 * Caller ID numbers can come in in all sorts of forms:
	 * 2505551234
	 * 12505551234
	 * +12505551234
	 * (250) 555-1234
	 * Parse out just the digits, and use only the last 10 digits.
	 * Currently this will not support international numbers
	 * @param $number
	 * @return bool|string
	 */
	static function parseCallerID( $number ) {
		$validator = new Validator();

		$retval = substr( $validator->stripNonNumeric( $number ), -10, 10 );

		return $retval;
	}

	/**
	 * @param $name
	 * @param bool $strict
	 * @return bool|string
	 */
	static function generateCopyName( $name, $strict = false, $max_length = 49 ) {
		$name = str_replace( TTi18n::getText( 'Copy of ' ), '', $name );

		if ( $strict === true ) {
			$retval = TTi18n::getText( 'Copy of' ) . ' ' . $name;
		} else {
			$retval = TTi18n::getText( 'Copy of' ) . ' ' . $name . ' [' . rand( 1, 99 ) . ']';
		}

		$retval = substr( $retval, 0, $max_length ); //Make sure the name doesn't get too long.

		return $retval;
	}

	/**
	 * @param $from
	 * @param $name
	 * @param bool $strict
	 * @return bool|string
	 */
	static function generateShareName( $from, $name, $strict = false ) {
		if ( $strict === true ) {
			$retval = $name . ' (' . TTi18n::getText( 'Shared by' ) . ': ' . $from . ')';
		} else {
			$retval = $name . ' (' . TTi18n::getText( 'Shared by' ) . ': ' . $from . ') [' . rand( 1, 99 ) . ']';
		}

		$retval = substr( $retval, 0, 99 ); //Make sure the name doesn't get too long.

		return $retval;
	}

	/** Delete all files in directory
	 * @param $path                 string directory to clean
	 * @param $recursive            boolean delete files in subdirs
	 * @param bool $del_dirs
	 * @param bool $del_root
	 * @param $exclude_regex_filter string regex to exclude paths
	 * @return bool
	 * @access public
	 */
	static function cleanDir( $path, $recursive = false, $del_dirs = false, $del_root = false, $exclude_regex_filter = null ) {
		$result = true;

		if ( $path == '' ) {
			Debug::Text( 'Path is blank, unable to clean...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$dir = @dir( $path ); //Get directory class object.
		if ( !is_object( $dir ) ) {
			Debug::Text( 'Unable to open path for cleaning: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'Cleaning: ' . $path . ' Exclude Regex: ' . $exclude_regex_filter, __FILE__, __LINE__, __METHOD__, 10 );
		while ( $file = $dir->read() ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}

			$full_file_name = $dir->path . DIRECTORY_SEPARATOR . $file;

			if ( $exclude_regex_filter != '' && preg_match( '/' . $exclude_regex_filter . '/i', $full_file_name ) == 1 ) {
				continue;
			}

			if ( is_dir( $full_file_name ) && $recursive == true ) {
				$result = self::cleanDir( $full_file_name, $recursive, $del_dirs, $del_dirs, $exclude_regex_filter );
			} else if ( is_file( $full_file_name ) ) {
				//$result = @unlink( $full_file_name );
				//if ( $result == false ) {
				//	Debug::Text( '  Failed Deleting: ' . $full_file_name, __FILE__, __LINE__, __METHOD__, 10 );
				//}
				$result = Misc::unlink( $full_file_name ); //Use our own unlink function so it does a rename before delete on Windows.
			}
		}
		$dir->close();

		if ( $del_root == true ) {
			//Debug::Text('Deleting Dir: '. $dir->path, __FILE__, __LINE__, __METHOD__, 10);
			$result = @rmdir( $dir->path );
		}

		clearstatcache(); //Clear any stat cache when done.

		return $result;
	}

	/**
	 * Unlink (Delete) a file.
	 * @param $file_name
	 * @return bool
	 */
	static function unlink( $file_name ) {
		if ( !is_file( $file_name ) ) {
			return false;
		}

		//On Windows, unlink() is async, therefore rename the file first before deleting it,
		//That will allow a new file with the original file name to immediately be put in its place without triggering an "access denied" or "file exists" error.
		if ( PHP_OS == 'WINNT' ) {
			$tmp_file_name = $file_name . str_replace( '.', '', uniqid( '_pending_del_', true ) );
			$result = @rename( $file_name, $tmp_file_name );
			if ( $result == true ) {
				$file_name = $tmp_file_name; //If the rename was successfull, set the new file name so it can be deleted below.
			} else {
				Debug::Text( '  Failed renaming: ' . $file_name . ' Reason: ' . Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		$result = @unlink( $file_name );
		if ( $result == false ) {
			Debug::Text( '  Failed Deleting: ' . $file_name .' Reason: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $result;
	}

	/**
	 * Checks to see if the directory $path and $recurse_parent_levels number of parent paths are empty, and deletes them all going *UP* the tree. This will not go *DOWN* the tree.
	 * @param $path
	 * @param int $recurse_parent_levels
	 * @return bool
	 */
	static function deleteEmptyParentDirectory( $path, $recurse_parent_levels = 0 ) {
		if ( $path == '' ) {
			Debug::Text( 'Path is empty: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( !is_dir( $path ) ) {
			Debug::Text( 'Path is not a directory: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$fs_iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		if ( $fs_iterator->valid() == false ) {
			Debug::Text( 'Deleting Empty Directory: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );
			$parent_dir = realpath( $path . DIRECTORY_SEPARATOR . '..' ); //Need to get parent directory before its deleted, otherwise realpath() fails.
			rmdir( $path );
			if ( $recurse_parent_levels > 0 ) {
				return self::deleteEmptyParentDirectory( $parent_dir, ( $recurse_parent_levels - 1 ) );
			}
		} else {
			Debug::Text( 'Skipping Non-Empty Directory: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * Deletes all empty directories in $path and underneath (down) from it.
	 * @param $path
	 * @return bool
	 */
	static function deleteEmptyChildDirectory( $path ) {
		if ( $path == '' ) {
			Debug::Text( 'Path is empty: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( !is_dir( $path ) ) {
			Debug::Text( 'Path is not a directory: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file_obj ) {
			if ( $file_obj->isDir() == true ) {
				Misc::deleteEmptyParentDirectory( $file_obj->getPathName() );
			}
		}

		return true;
	}

	/**
	 * Renames a file or directory. If rename fails for some reason, attempt a copy instead as that might work, specifically on windows where if the file is in use.
	 *   Might fix possible "Access is denied. (code: 5)" errors on Windows when using PHP v5.2 (https://bugs.php.net/bug.php?id=43817)
	 *   On Windows a directory containing a file formerly opened within the same script may also cause access denied errors in some versions of PHP.
	 * @param string $old_name
	 * @param string $new_name
	 * @return bool
	 */
	static function rename( $old_name, $new_name ) {
		$new_dir = dirname( $new_name );
		if ( file_exists( $new_dir ) == false ) {
			@mkdir( $new_dir, 0755, true );
		}

		//Some operating systems (ie: Windows) may not allow renaming if the destination already exists, so check for that is the case and delete it first.
		// **NOTE: We can't delete/unlink the new file first if it exists, because if the later rename/copy fails due to access denied, the file will be gone.
		//         Instead we need rename it, so we can rename it back if needed.
		if ( file_exists( $new_name ) ) {
			$tmp_new_name = $new_name .'pending_delete';
			if ( @rename( $new_name, $tmp_new_name ) == false ) {
				Debug::Text( 'ERROR: Unable to rename existing destination file: ' . $new_name .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				unset( $tmp_new_name );
			}
		}

		if ( @rename( $old_name, $new_name ) == false ) {
			Debug::Text( 'ERROR: Unable to rename: ' . $old_name . ' to: ' . $new_name .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( is_dir( $old_name ) == false && @copy( $old_name, $new_name ) == true ) {
				if ( @unlink( $old_name ) == false ) {
					Debug::Text( 'ERROR: Unable to unlink after copy: ' . $old_name . ' to: ' . $new_name .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}

				$retval = true;
			} else {
				Debug::Text( 'ERROR: Unable to copy after rename failure: ' . $old_name . ' to: ' . $new_name .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				$retval = false;
			}
		} else {
			$retval = true;
		}

		if ( isset( $tmp_new_name ) ) {
			if ( $retval == true ) {
				//Delete the temporary "pending_delete" file
				@unlink( $tmp_new_name );
			} else {
				//Rename the temporary "pending_delete" file back to its original name so it doesn't disappear.
				if ( @rename( $tmp_new_name, $new_name ) == false ) {
					Debug::Text( 'ERROR: Unable to rename pending_delete file back to original: ' . $new_name .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return $retval;
	}

	/**
	 * @param $start_dir
	 * @param null $regex_filter
	 * @param bool $recurse
	 * @return array|bool
	 */
	static function getFileList( $start_dir, $regex_filter = null, $recurse = false ) {
		$files = [];
		if ( is_dir( $start_dir ) && is_readable( $start_dir ) ) {
			$fh = opendir( $start_dir );
			while ( ( $file = readdir( $fh ) ) !== false ) {
				// loop through the files, skipping . and .., and recursing if necessary
				// If for some reason $file is blank, it could cause an infinite loop like: "C:\TimeTrex\cache\upgrade_staging\latest_version\interface\html5\views\payroll\pay_stub_transaction\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\"
				if ( $file == '' || strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 ) {
					continue;
				}

				$filepath = $start_dir . DIRECTORY_SEPARATOR . $file;
				if ( is_dir( $filepath ) && $recurse == true ) {
					Debug::Text( ' Recursing into dir: ' . $filepath, __FILE__, __LINE__, __METHOD__, 10 );

					$tmp_files = self::getFileList( $filepath, $regex_filter, true );
					if ( $tmp_files != false && is_array( $tmp_files ) ) {
						$files = array_merge( $files, $tmp_files );
					}
					unset( $tmp_files );
				} else if ( !is_dir( $filepath ) ) {
					if ( $regex_filter == '*' || preg_match( '/' . $regex_filter . '/i', $file ) == 1 ) {
						//Debug::Text(' Match: Dir: '. $start_dir .' File: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);
						if ( is_readable( $filepath ) ) {
							array_push( $files, $filepath );
						} else {
							Debug::Text( ' Matching file is not read/writable: ' . $filepath, __FILE__, __LINE__, __METHOD__, 10 );
						}
					} // else { //Debug::Text(' NO Match: Dir: '. $start_dir .' File: '. $filepath, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			closedir( $fh );
			sort( $files );
		} else {
			// false if the function was called with an invalid non-directory argument
			$files = false;
		}

		//Debug::Arr( $files, 'Matching files: ', __FILE__, __LINE__, __METHOD__, 10);
		return $files;
	}

	/**
	 * @param $child_dir
	 * @param $parent_dir
	 * @return bool
	 */
	static function isSubDirectory( $child_dir, $parent_dir ) {
		//Make sure directories always end in trailing slash, otherwise paths like this will fail:
		//  Child: /var/www/TimeTrex Parent: /var/www/TimeTrexTest
		$child_dir = rtrim( $child_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		$parent_dir = rtrim( $parent_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		if ( strpos( $child_dir, $parent_dir ) === 0 ) {
			return true;
		} else {
			//When using realpath(), if the path does not exist it will return FALSE. In that case it can never be a sub directory.
			$real_child_dir = realpath( $child_dir );
			$real_parent_dir = realpath( $parent_dir );
			if ( $real_child_dir !== false && $real_parent_dir !== false && strpos( $real_child_dir . DIRECTORY_SEPARATOR, $real_parent_dir . DIRECTORY_SEPARATOR ) === 0 ) { //Test realpaths incase they are relative or have "../" in them.
				return true;
			}
		}

		return false;
	}

	/**
	 * @param object|array $obj
	 * @return array|object
	 */
	static function convertObjectToArray( $obj ) {
		if ( is_object( $obj ) ) {
			$obj = get_object_vars( $obj );
		}

		if ( is_array( $obj ) ) {
			return array_map( [ 'Misc', __FUNCTION__ ], $obj );
		} else {
			return $obj;
		}
	}

	/**
	 * @param $val
	 * @return int|string
	 */
	static function getBytesFromSize( $val ) {
		$val = trim( $val );

		switch ( strtolower( substr( $val, -1 ) ) ) {
			case 'm':
				$val = ( (int)substr( $val, 0, -1 ) * 1048576 );
				break;
			case 'k':
				$val = ( (int)substr( $val, 0, -1 ) * 1024 );
				break;
			case 'g':
				$val = ( (int)substr( $val, 0, -1 ) * 1073741824 );
				break;
			case 'b':
				switch ( strtolower( substr( $val, -2, 1 ) ) ) {
					case 'm':
						$val = ( (int)substr( $val, 0, -2 ) * 1048576 );
						break;
					case 'k':
						$val = ( (int)substr( $val, 0, -2 ) * 1024 );
						break;
					case 'g':
						$val = ( (int)substr( $val, 0, -2 ) * 1073741824 );
						break;
					default:
						break;
				}
				break;
			default:
				break;
		}

		return $val;
	}

	/**
	 * @return int|string
	 */
	static function getSystemMemoryInfo() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$memory_file = '/proc/meminfo';
			if ( @file_exists( $memory_file ) && is_readable( $memory_file ) ) {
				$buffer = file_get_contents( $memory_file );

				preg_match( '/MemFree:\s+([0-9]+) kB/im', $buffer, $mem_free_match );
				if ( isset( $mem_free_match[1] ) ) {
					$mem_free = Misc::getBytesFromSize( (int)$mem_free_match[1] . 'K' );
					unset( $mem_free_match );
				}

				preg_match( '/Cached:\s+([0-9]+) kB/im', $buffer, $mem_cached_match );
				if ( isset( $mem_cached_match[1] ) ) {
					$mem_cached = Misc::getBytesFromSize( (int)$mem_cached_match[1] . 'K' );
					unset( $mem_cached_match );
				}

				Debug::Text( ' Memory Info: Free: ' . $mem_free . 'b Cached: ' . $mem_cached . 'b', __FILE__, __LINE__, __METHOD__, 10 );

				return ( $mem_free + ( $mem_cached * ( 1 / 2 ) ) ); //Only allow up to 1/2 of cached memory to be used.
			}
		} else if ( OPERATING_SYSTEM == 'WIN' ) {
			//Windows can use the following commands:
			//wmic computersystem get TotalPhysicalMemory
			//wmic OS get FreePhysicalMemory /Value

			//This seems to take about 250ms on Windows 7.
			$command = 'wmic OS get FreePhysicalMemory';
			exec( $command, $output, $retcode );

			if ( isset( $output[1] ) && is_numeric( trim( $output[1] ) ) ) {
				$retval = ( $output[1] * 1024 ); //Convert from MB to bytes.
				Debug::Text( ' Memory Info: Total Physical: ' . $retval . 'b', __FILE__, __LINE__, __METHOD__, 10 );

				return $retval;
			}
		}

		return PHP_INT_MAX; //If not linux, return large number, this is in Bytes.
	}

	/**
	 * @return int|mixed
	 */
	static function getSystemLoad() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$loadavg_file = '/proc/loadavg';
			if ( @file_exists( $loadavg_file ) && is_readable( $loadavg_file ) ) {
				//$buffer = '0 0 0';
				$buffer = @file_get_contents( $loadavg_file ); //In rare cases this can fail to be read.
				if ( $buffer !== false ) {
					$load = explode( ' ', $buffer );

					//$retval = max((float)$load[0], (float)$load[1], (float)$load[2]);
					//$retval = max( (float)$load[0], (float)$load[1] ); //Only consider 1 and 5 minute load averages, so we don't block cron/reports for more than 5 minutes.
					$retval = (float)$load[0]; //Only consider 1 minute load averages, so we don't block cron/reports for more than 1 minute.
					//Debug::text(' Load Average: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

					return $retval;
				}
			}
		}
		//While the below works to get CPU LoadPercent (between 0-100) on Windows, its extremely slow and can take upwords of 2-5 seconds to complete.
		//else if ( OPERATING_SYSTEM == 'WIN' ) {
		//	$command = 'wmic cpu get LoadPercentage';
		//	exec( $command, $output, $exit_code );
		//	if ( $exit_code == 0 && is_array( $output ) ) {
		//		if ( isset( $output[1] ) ) {
		//			return (int)$output[1];
		//		}
		//	}
		//}

		return 0;
	}

	static function getMaxSystemLoad() {
		global $config_vars;
		if ( isset( $config_vars['other']['max_cron_system_load'] ) ) {
			$retval = $config_vars['other']['max_cron_system_load'];
		} else {
			$retval = 128;
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	static function isSystemLoadValid( $multiplier = 1, $disable_debug_log = false ) {
		$max_system_load = Misc::getMaxSystemLoad();
		$current_system_load = Misc::getSystemLoad();
		//The below works if we are getting CPU LoadPercent from Windows, but currently its too slow.
		//if ( OPERATING_SYSTEM == 'WIN' ) {
		//	//Convert Windows load percent to a max load similar to Linux.
		//	$current_system_load = ( $current_system_load * $config_vars['other']['max_cron_system_load'] / 100 );
		//}
		if ( $current_system_load <= ( $max_system_load * $multiplier ) ) {
			if ( $disable_debug_log == false ) {
				Debug::text( 'Load average within valid limits: Current: ' . $current_system_load . ' Max: ' . $max_system_load . ' Multiplier: ' . $multiplier, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return true;
		}

		Debug::text( '  WARNING: LIMIT REACHED... Load average NOT within valid limits: Current: ' . $current_system_load . ' Max: ' . $max_system_load .' Multiplier: '. $multiplier, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $email
	 * @param object $user_obj
	 * @return string
	 */
	static function formatEmailAddress( $email, $user_obj ) {
		if ( !is_object( $user_obj ) ) {
			return $email;
		}
		$email = '"' . $user_obj->getFirstName() . ' ' . $user_obj->getLastName() . '" <' . $email . '>';

		return $email;
	}

	/**
	 * Parses an RFC822 Email Address ( "John Doe" <john.doe@mydomain.com> ) into its separate components.
	 * @param $input
	 * @param string $return_just_key Name of component to return. ie: full_name, first_name, last_name, email
	 * @return array|bool
	 */
	static function parseRFC822EmailAddress( $input, $return_just_key = false ) {
		if ( strstr( $input, '<>' ) !== false || trim( $input ) == '' ) { //Check for <> together, as that means no email address is specified.
			return false;
		}

		if ( function_exists( 'imap_rfc822_parse_adrlist' ) ) {
			$parsed_data = @imap_rfc822_parse_adrlist( $input, 'unknown.local' );
			//Debug::Arr( $parsed_data, 'Parsed Email Data From: ' . $input, __FILE__, __LINE__, __METHOD__, 10 );
			if ( is_array( $parsed_data ) && count( $parsed_data ) > 0 ) {
				$parsed_data = $parsed_data[0];
				if ( isset( $parsed_data->host ) && $parsed_data->host != 'unknown.local' ) {
					$retarr = [];
					$retarr['email'] = $parsed_data->mailbox . '@' . $parsed_data->host;
					$retarr['mailbox'] = $parsed_data->mailbox;
					$retarr['host'] = $parsed_data->host;

					if ( isset( $parsed_data->personal ) ) {
						$retarr['full_name'] = $parsed_data->personal;

						$split_name = explode( ' ', $parsed_data->personal );
						if ( $split_name !== false ) {
							if ( isset( $split_name[0] ) ) {
								$retarr['first_name'] = $split_name[0];
							}
							if ( isset( $split_name[( count( $split_name ) - 1 )] ) ) {
								$retarr['last_name'] = $split_name[( count( $split_name ) - 1 )];
							}
						}
					}

					if ( $return_just_key != '' ) {
						if ( isset( $retarr[$return_just_key] ) ) {
							return $retarr[$return_just_key];
						}

						return false;
					} else {
						return $retarr;
					}
				} else {
					Debug::Text( 'WARNING: Email address host is unknown.local: ['. $input .']', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'WARNING: Unable to parse email address: ['. $input .']', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'ERROR: PHP IMAP extension is not installed...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $subject
	 * @param $body
	 * @param null $attachments
	 * @param bool $force
	 * @return bool
	 */
	static function sendSystemMail( $subject, $body, $attachments = null, $force = false ) {
		if ( $subject == '' || $body == '' ) {
			return false;
		}

		if ( function_exists( 'getTTProductEdition' ) == false || ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && DEPLOYMENT_ON_DEMAND == true ) ) {
			$allowed_calls = 500;
		} else if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$allowed_calls = 100;
		} else {
			$allowed_calls = 25;
		}

		$rl = new RateLimit;
		$rl->setID( 'system_mail_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( $allowed_calls );
		$rl->setTimeFrame( 86400 ); //24hrs
		if ( $rl->check() == false ) {
			Debug::Text( 'Excessive system emails... Preventing error reports from: ' . Misc::getRemoteIPAddress() . ' for up to 24hrs...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$registration_key = 'N/A';
		try {
			//If during an install/schema upgrade a SQL error has occurred, the transaction will be aborted and cause the below select to fail.
			//To avoid an infinite loop, always check that the transaction hasn't already failed.
			global $db, $disable_database_connection;
			if ( ( !isset( $disable_database_connection ) || ( isset( $disable_database_connection ) && $disable_database_connection != true ) ) && is_object( $db ) && $db->hasFailedTrans() == false ) {
				//Check to make sure "system_setting" table exists, as a PHP error being triggered and sending an email before the database schema is setup could prevent error reports from being sent.
				if ( class_exists( 'Install' ) == true ) { //Needed to prevent fatal error if being called from 3rd party applications (ie: PaymentServices)
					$install_obj = new Install();
					$install_obj->setDatabaseConnection( $db ); //Default Connection
					if ( $install_obj->checkTableExists( 'system_setting' ) == true ) {
						$registration_key = SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
					}
				}
			}
		} catch ( Exception $e ) {
			Debug::Text( 'Error getting registration key!', __FILE__, __LINE__, __METHOD__, 1 );
		}

		$to = 'errors@timetrex.com';

		global $config_vars;
		if ( isset( $config_vars['other']['system_admin_email'] ) ) {
			if ( $config_vars['other']['system_admin_email'] != '' ) {
				$to = $config_vars['other']['system_admin_email'];
			} else {
				return false;
			}
		}

		$from = APPLICATION_NAME . '@' . Misc::getHostName( false );

		$headers = [
				'From'            => $from,
				'Subject'         => $subject,
				'X-Relay-For-Key' => $registration_key,
		];

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );
		@$mail->getMIMEObject()->setTXTBody( $body );

		if ( is_array( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				if ( isset( $attachment['data'] ) && isset( $attachment['mime_type'] ) && isset( $attachment['file_name'] ) ) {
					@$mail->getMIMEObject()->addAttachment( $attachment['data'], $attachment['mime_type'], $attachment['file_name'], false );
				}
			}
		}

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );

		$retval = $mail->Send( $force );

		return $retval;
	}

	/**
	 * @return bool
	 */
	static function isCurrentOSUserRoot() {
		$user = self::getCurrentOSUser();
		if ( $user == 'root' ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	static function getCurrentOSUser() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( function_exists( 'posix_geteuid' ) && function_exists( 'posix_getpwuid' ) ) {
				$user = posix_getpwuid( posix_geteuid() );
				Debug::text( 'Running as OS User: ' . $user['name'], __FILE__, __LINE__, __METHOD__, 9 );

				return $user['name'];
			} else {
				Debug::text( 'POSIX extension not installed, unable to determine webserver user...', __FILE__, __LINE__, __METHOD__, 9 );
			}
		}

		return false;
	}

	/**
	 * @param $uid
	 * @return bool
	 */
	static function setProcessUID( $uid ) {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( function_exists( 'posix_setuid' ) ) {
				if ( $uid > 0 ) {
					Debug::text( 'WARNING: Downgrading process UID to: ' . $uid, __FILE__, __LINE__, __METHOD__, 9 );

					return posix_setuid( $uid );
				} else {
					Debug::text( 'UID is invalid or 0 (root), skipping...', __FILE__, __LINE__, __METHOD__, 9 );
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	static function findWebServerOSUser() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( function_exists( 'posix_getpwnam' ) ) {
				$users = [ 'www-data', 'apache', 'wwwrun' ]; //Debian/Ubuntu: www-data, CentOS/Fedora/RHEL: apache, SUSE: wwwrun
				foreach ( $users as $tmp_user ) {
					$user_data = posix_getpwnam( $tmp_user );
					if ( $user_data !== false && isset( $user_data['uid'] ) && isset( $user_data['name'] ) ) {
						//return array('uid' => $user_data['uid'], 'name' => $user_data['name']);
						Debug::text( 'Found web server user: ' . $tmp_user . ' UID: ' . $user_data['uid'], __FILE__, __LINE__, __METHOD__, 9 );

						return $user_data['uid'];
					}
				}
			}
		}

		Debug::text( 'No web server user found...', __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @param bool $email_notification
	 * @return bool
	 */
	static function disableCaching( $email_notification = true ) {
		//In case the cache directory does not exist, disabling caching can prevent errors from occurring or punches to be missed.
		//So this should be enabled even for ON-DEMAND services just in case.
		if ( PRODUCTION == true ) {
			$tmp_config_vars = [];
			//Disable caching to prevent stale cache data from being read, and further cache errors.
			$install_obj = new Install();
			$tmp_config_vars['cache']['enable'] = 'FALSE';
			$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
			unset( $install_obj );

			if ( $email_notification == true ) {
				if ( $write_config_result == true ) {
					$subject = APPLICATION_NAME . ' - Error!';
					$body = 'ERROR writing cache file, likely due to incorrect operating system permissions, disabling caching to prevent data corruption. This may result in ' . APPLICATION_NAME . ' performing slowly.' . "\n\n";
					$body .= Debug::getOutput();
				} else {
					$subject = APPLICATION_NAME . ' - Error!';
					$body = 'ERROR writing config file, likely due to incorrect operating system permissions conflicts. Please correct permissions so ' . APPLICATION_NAME . ' can operate correctly.' . "\n\n";
					$body .= Debug::getOutput();
				}

				return self::sendSystemMail( $subject, $body );
			}

			return true;
		}

		return false;
	}

	/**
	 * @param $address1
	 * @param $address2
	 * @param $city
	 * @param $province
	 * @param $postal_code
	 * @param $country
	 * @param string $service
	 * @return bool|string
	 */
	static function getMapURL( $address1, $address2, $city, $province, $postal_code, $country, $service = 'google' ) {
		if ( $address1 == '' && $address2 == '' ) {
			return false;
		}

		$url = null;

		//Expand the country code to the full country name?
		if ( strlen( $country ) == 2 ) {
			$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

			$long_country = Option::getByKey( $country, $cf->getOptions( 'country' ) );
			if ( $long_country != '' ) {
				$country = $long_country;
			}
		}

		if ( $service == 'google' ) {
			$base_url = 'maps.google.com/?z=16&q=';
			$url = $base_url . urlencode( $address1 . ' ' . $city . ' ' . $province . ' ' . $postal_code . ' ' . $country );
		}

		if ( $url != '' ) {
			return 'http://' . $url;
		}

		return false;
	}

	/**
	 * @param $email
	 * @param bool $check_dns
	 * @param bool $error_level
	 * @param bool $return_raw_result
	 * @return bool
	 * @noinspection PhpUndefinedConstantInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	static function isEmail( $email, $check_dns = true, $error_level = true, $return_raw_result = false, $enable_smtp_verification = false ) {
		if ( !function_exists( 'is_email' ) ) {
			require_once( Environment::getBasePath() . '/classes/misc/is_email.php' );
		}

		if ( $enable_smtp_verification == true ) {
			global $cache;
			$orig_cache_life_time = $cache->_lifeTime;

			$cache->setLifetime( 900 ); //Can't clear cache pro-actively, so force 15min cache time. Mostly helps speed up email checks when saving user records and other validation checks fail.
			$raw_result = $cache->get( $email, 'email_smtp_validation' );
			if ( $raw_result === false ) {
				$raw_result = is_email( $email, false, 7 ); //Check basic email address formatting and Skip DNS checks here, as the below SMTP checks will handle that for us.
				if ( $raw_result === ISEMAIL_VALID ) {
					Debug::Text( 'Attempting SMTP check of email address: ' . $email, __FILE__, __LINE__, __METHOD__, 10 );
					try {
						$smtp_verification_start = microtime( true );

						$smtp_validator = new SMTPValidateEmail\Validator();
						$smtp_validator->setConnectTimeout( 3 ); //3 seconds.
						$smtp_results = $smtp_validator->validate( $email, Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() );
						if ( isset( $smtp_results[$email] ) && $smtp_results[$email] === true ) {
							$raw_result = 0; //Success

							if ( ( microtime( true ) - $smtp_verification_start ) > 15 ) {
								Debug::Arr( $smtp_results, 'SMTP Email Verification Success, but slow: ', __FILE__, __LINE__, __METHOD__, 10 );
								Debug::Arr( $smtp_validator->getLog(), 'SMTP Email Verification Log: ', __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							$raw_result = 99; //Use validation message from key = 99.

							if ( Debug::getVerbosity() >= 10 ) {
								Debug::Arr( $smtp_results, 'SMTP Email Verification Failed: ', __FILE__, __LINE__, __METHOD__, 10 );
								// Get log data (log data is always collected)
								Debug::Arr( $smtp_validator->getLog(), 'SMTP Email Verification Log: ', __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
						unset( $smtp_validator, $smtp_results );
					} catch ( Exception $e ) {
						$raw_result = 99; //Use validation message from key = 99.

						Debug::Text( 'SMTP Email Verification Failed with exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						Debug::Arr( $smtp_validator->getLog(), 'SMTP Email Verification Log: ', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}

				if ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) {
					$cache->save( $raw_result, $email, 'email_smtp_validation' );
				}
			} else {
				Debug::Text( 'SMTP Email Verification result from cache...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$cache->setLifetime( $orig_cache_life_time );
		} else {
			$raw_result = is_email( $email, $check_dns, $error_level );
		}

		if ( $return_raw_result === true ) {
			if ( $raw_result !== ISEMAIL_VALID ) {
				Debug::Text( 'Result Code: ' . $raw_result, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return $raw_result;
		} else {
			if ( $raw_result === ISEMAIL_VALID ) {
				return true;
			} else {
				Debug::Text( 'Result Code: ' . $raw_result, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return false;
		}
	}

	/**
	 * @return int
	 */
	static function getCurrentCompanyProductEdition() {
		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = getTTProductEdition();
		if ( $product_edition_id >= TT_PRODUCT_PROFESSIONAL ) {
			global $current_company;
			if ( isset( $current_company ) && is_object( $current_company ) ) {
				$product_edition_id = $current_company->getProductEdition();
			}
		}

		return $product_edition_id;
	}

	/**
	 * @return bool
	 */
	static function redirectMobileBrowser() {
		$desktop = 0;
		extract( FormVariables::GetVariables( [ 'desktop' ] ) );

		if ( !isset( $desktop ) ) {
			$desktop = 0;
		}

		// ?desktop=1 must be sent in cases like password reset email links to prevent the user from being redirected to the QuickPunch login page when trying to reset passwords.
		// Unfortunately when using #!m=... we can't detect what page they are really trying to go to on the server side.
		// Don't redirect search engines either.
		if ( getTTProductEdition() != TT_PRODUCT_COMMUNITY && Misc::isSearchEngineBrowser() == false && $desktop != 1 ) {
			$browser = self::detectMobileBrowser();
			if ( $browser == true ) {
				Redirect::Page( URLBuilder::getURL( null, Environment::getBaseURL() . '/html5/quick_punch/' ) );
			}
		} else {
			Debug::Text( 'Desktop browser override: ' . (int)$desktop, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	static function redirectUnSupportedBrowser() {
		if ( self::isUnSupportedBrowser() == true ) {
			$registration_key = SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
			if ( $registration_key == '' ) {
				$registration_key = 'UNKNOWN';
			}

			Redirect::Page( URLBuilder::getURL( [ 'tt_version' => APPLICATION_VERSION, 'tt_edition' => getTTProductEdition(), 'registration_key' => $registration_key ], 'https://www.timetrex.com/supported-web-browsers' ) );
		}

		return true;
	}

	/**
	 * Get a fingerprint of the web browser based on the headers that are likely to be unique per user.
	 *    FUTURE: We may have customers that want to lock down their TimeTrex to a single browser, so we need to be able to identify the browser.
	 *    Only way to do this consistently would be to make a "TimeTrex" browser extension that get a device ID and always sends it with the request in a HTTP header or cookie.
	 *    We could require this extension in order to punch too? Would need one for Firefox too though.
	 * 	  **NOTE: We already parse "Station ID:" from UserAgent strings as a way of having consistent IDs. An extension would just be easier for the user is all.
	 *
	 * @return string
	 */
	static function getBrowserFingerprint( $ignore_browser_version = false, $ignore_ip_address = false ) {
		// Collect headers that are likely to be unique per user
		$headers_to_consider = [
				'HTTP_USER_AGENT' => true,
				'HTTP_ACCEPT_LANGUAGE' => true,
				'HTTP_ACCEPT_ENCODING' => true,
				'HTTP_ACCEPT_CHARSET' => true,
				'HTTP_ACCEPT' => true,
				//'HTTP_REFERER', //Likely a bookmarked, so there wouldn't be any useful referrer.
				'REMOTE_ADDR' => true, //Include IP address in the fingerprint. Unlikely to change every day. This will help differentiate users with the same browser but on different devices on the network.
		];

		$fingerprint_data = [];

		if ( $ignore_ip_address == true ) {
			unset( $headers_to_consider['REMOTE_ADDR'] );
		}

		if ( $ignore_browser_version == true ) {
			unset( $headers_to_consider['HTTP_USER_AGENT'] );

			$browser = new Browser( $_SERVER['HTTP_USER_AGENT'] ?? '' );
			if ( is_object( $browser ) ) {
				$fingerprint_data[] = $browser->getBrowser() .':'. (int)$browser->isMobile() .':'. $browser->getPlatform();
			}
		}

		foreach ( $headers_to_consider as $header => $value ) {
			if ( isset( $_SERVER[$header] ) ) {
				$fingerprint_data[] = $_SERVER[$header];
			}
		}

		// Combine all data into a single string
		$fingerprint_string = implode( '|', $fingerprint_data );
		Debug::Text( 'Browser Fingerprint: ' . $fingerprint_string .' Ignore Browser Version: '. (int)$ignore_browser_version .' Ignore IP: '. (int)$ignore_ip_address, __FILE__, __LINE__, __METHOD__, 10 );

		// Hash the data to create the fingerprint
		$fingerprint = 'FP-' . sha1( $fingerprint_string ); //Prefix with 'FP-' so we can easily identify it in the database. Must use '-' instead of ':' as Stations don't allow that character.

		return $fingerprint;
	}

	/**
	 * @param null $useragent
	 * @return bool
	 */
	static function isUnSupportedBrowser( $useragent = null ) {
		if ( $useragent == '' ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				return false;
			}
		}

		$retval = false;

		if ( !class_exists( 'Browser', false ) ) {
			require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
		}

		$browser = new Browser( $useragent );

		if ( $browser->isRobot() == true ) { //Never redirect robots, as GoogleBot sometimes appears as Chrome v41.
			Debug::Text( 'Detected Robot: ' . $browser->getBrowser() . ' Version: ' . $browser->getVersion() . ' User Agent: ' . $useragent, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//This is for the full web interface - Need full ES6 support: https://caniuse.com/#feat=es6 *and* Webpack Terser needs to fully support it too.
		//IE (All Versions)
		//Edge < 109 - https://en.wikipedia.org/wiki/Microsoft_Edge (116 is latest version which supports Windows 7)
		//Firefox < 102 (52 is latest version on Windows XP, 116 is latest on Windows 7)
		//Chrome < 109 (49 is latest version on Windows XP, 109 is latest on Windows 7)
		//Safari < 15.4 - https://en.wikipedia.org/wiki/Safari_version_history (v15.4 supports CSS layers required for PrimeVue)
		//Opera < 109 https://help.opera.com/en/opera-version-history/
		//
		// iOS v10 (Safari v10) (Webkit v603) [Oldest device is about iPhone 7] -- https://en.wikipedia.org/wiki/Safari_version_history
		if ( $browser->isMobile() == false && $browser->isTablet() == false ) { //Firefox on iOS has versions are much lower than on Android or iOS. ie: v28
			if ( $browser->getBrowser() == Browser::BROWSER_IE ) { //All versions of IE.
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_EDGE && version_compare( $browser->getVersion(), 109, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_FIREFOX && version_compare( $browser->getVersion(), 102, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_CHROME && version_compare( $browser->getVersion(), 109, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_SAFARI && version_compare( $browser->getVersion(), 15.4, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_OPERA && version_compare( $browser->getVersion(), 109, '<' ) ) {
				$retval = true;
			}
		} else {
			if ( $browser->getBrowser() == Browser::BROWSER_SAFARI && version_compare( $browser->getVersion(), 15.4, '<' ) ) { //Different Safari versions on iOS vs. Desktop.
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_CHROME && version_compare( $browser->getVersion(), 109, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_FIREFOX && version_compare( $browser->getVersion(), 102, '<' ) ) {
				$retval = true;
			}

			if ( $browser->getBrowser() == Browser::BROWSER_SAMSUNG && version_compare( $browser->getVersion(), 18, '<' ) ) {
				$retval = true;
			}

			//Example user agent strings that we can't get a version from:
			//  Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13G36
			//  Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1
			if ( ( $browser->getBrowser() == Browser::BROWSER_IPAD || $browser->getBrowser() == Browser::BROWSER_IPHONE || $browser->getBrowser() == Browser::BROWSER_IPOD ) ) {
				//If we can't determine the browser version, allow it to continue.
				//  This is required for some iOS devices using Facebook In-App browser.
				if ( $browser->getVersion() == 'unknown' ) {
					//Try to detect webkit version to fall back on.
					if ( preg_match( '/Webkit\/([0-9\.]+)/i', $useragent, $matches ) ) {
						if ( isset( $matches[1] ) ) {
							$webkit_version = $matches[1];
							Debug::Text( '  Detected Webkit Version: ' . $webkit_version, __FILE__, __LINE__, __METHOD__, 10 );
							if ( version_compare( $webkit_version, 603, '<' ) ) { //See comments at the top for why this version.
								$retval = true;
							}
						}
					}
					unset( $matches, $webkit_version );
				} else if ( version_compare( $browser->getVersion(), 14, '<' ) ) { //Different Safari versions on iOS vs. Desktop.
					$retval = true;
				}
			}
		}

		if ( $retval == true ) {
			Debug::Text( 'Unsupported Browser: ' . $browser->getBrowser() . ' Version: ' . $browser->getVersion() .' Agent String: '. $useragent, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param null $useragent
	 * @return bool
	 */
	static function isSearchEngineBrowser( $useragent = null ) {
		if ( $useragent == '' ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				return false;
			}
		}

		$retval = false;

		if ( !class_exists( 'Browser', false ) ) {
			require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
		}

		$browser = new Browser( $useragent );

		if ( $browser->getBrowser() == Browser::BROWSER_GOOGLEBOT || $browser->getBrowser() == Browser::BROWSER_BINGBOT || $browser->getBrowser() == Browser::BROWSER_SLURP ) {
			$retval = true;
		}

		return $retval;
	}

	/**
	 * @param null $useragent
	 * @return bool|string
	 */
	static function detectMobileBrowser( $useragent = null, $return_platform = false ) {
		if ( $useragent == '' ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			} else {
				return false;
			}
		}

		$retval = false;

		if ( !class_exists( 'Browser', false ) ) {
			require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
		}

		$browser = new Browser( $useragent );

		if ( $browser->isMobile() == true || $browser->isTablet() == true ) {
			if ( $return_platform == true ) {
				if ( $browser->getPlatform() == Browser::PLATFORM_IPHONE || $browser->getPlatform() == Browser::PLATFORM_IPAD || $browser->getPlatform() == Browser::PLATFORM_IPOD  ) {
					$retval = 'ios';
				} elseif ( $browser->getPlatform() == Browser::PLATFORM_ANDROID ) {
					$retval = 'android';
				} else {
					$retval = true;
				}
			} else {
				$retval = true;
			}
		}

		Debug::Text( 'User Agent: ' . $useragent . ' Retval: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * Take an amount and a distribution array of key => value pairs, value being a decimal percent (ie: 0.50 for 50%)
	 * return an array with the same keys and resulting distribution between them.
	 * Adding any remainder to the last key is the fastest.
	 * @param $amount
	 * @param $percent_arr
	 * @param string $remainder_operation
	 * @param int $precision
	 * @return array|bool
	 */
	static function PercentDistribution( $amount, $percent_arr, $remainder_operation = 'last', $precision = 2 ) {
		if ( !is_numeric( $amount ) ) {
			$amount = 0;
		}

		//$percent_arr = array(
		//					'key1' => 0.505,
		//					'key2' => 0.495,
		//);
		if ( is_array( $percent_arr ) && count( $percent_arr ) > 0 ) {
			$retarr = [];
			$total = 0;
			foreach ( $percent_arr as $key => $distribution_percent ) {
				$distribution_amount = TTMath::mul( $amount, $distribution_percent, $precision );
				$retarr[$key] = $distribution_amount;

				$total = TTMath::add( $total, $distribution_amount, $precision );
			}

			//Add any remainder to the last key.
			if ( $total != $amount ) {
				$remainder_amount = TTMath::sub( $amount, $total, $precision );
				//Debug::Text('Found remainder: '. $remainder_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $remainder_operation == 'first' ) {
					reset( $retarr );
					$key = key( $retarr );
				}
				$retarr[$key] = TTMath::add( $retarr[$key], $remainder_amount, $precision );
			}

			//Debug::Text('Amount: '. $amount .' Total (After Remainder): '. array_sum( $retarr ), __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		return false;
	}

	/**
	 * Change the case of all values in an array
	 * @param $input
	 * @param int $case
	 * @return array|bool
	 */
	static function arrayChangeValueCase( $input, $case = CASE_LOWER ) {
		switch ( $case ) {
			case CASE_LOWER:
				return array_map( 'strtolower', $input );
				break;
			case CASE_UPPER:
				return array_map( 'strtoupper', $input );
				break;
			default:
				trigger_error( 'Case is not valid, CASE_LOWER or CASE_UPPER only', E_USER_ERROR ); //E_USER_ERROR stops execution.
		}
	}

	/**
	 * Checks to see if a file/directory is writable.
	 * @param $path
	 * @return bool
	 */
	static function isWritable( $path ) {
		//Debug::text( 'File: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );
		if ( file_exists( $path ) ) {
			if ( substr( $path, -1 ) == DIRECTORY_SEPARATOR || substr( $path, -1 ) == '.' || is_dir( $path ) ) {
				//Debug::text( 'File is directory: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

				return self::isWritable( $path . DIRECTORY_SEPARATOR . uniqid( mt_rand() ) . '.tmp' ); //Try to write a temporary file to the directory to ensure it can be written and deleted.
			}

			$f = @fopen( $path, 'r+' );
			if ( $f == false ) {
				//Debug::text( 'File is NOT writable: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}
			fclose( $f );

			return true;
		} else {
			//Debug::text( 'File does not exists...', __FILE__, __LINE__, __METHOD__, 10 );
			$f = @fopen( $path, 'w' );
			if ( $f == false ) {
				Debug::text( 'File is NOT writable: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
			fclose( $f );

			if ( @unlink( $path ) == false ) { //This could error if create but not delete permission exists.
				Debug::text( 'File can be created, but not deleted: ' . $path, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			return true;
		}
	}

	/**
	 * @return bool
	 */
	static function isMobileAppUserAgent() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			if ( strpos( $useragent, 'TimeTrex Mobile App' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return false|mixed|string
	 */
	static function getMobileAppClientVersion() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] != '' ) {
			$parsed_user_agent = Misc::parseMobileAppUserAgent();
			if ( isset( $parsed_user_agent['app_version'] ) ) {
				return $parsed_user_agent['app_version'];
			}
		//Checking if GET &v=<version> breaks API clients that are passing protocol v2.
		//} else if ( isset( $_GET['v'] ) && $_GET['v'] != '' ) { //Is v=X the API version or the User Agent (App) version? This does not appear to be used on recent production app versions.
		//	return $_GET['v'];
		}

		return false;
	}

	/**
	 * @return false|mixed|string
	 */
	static function parseMobileAppUserAgent( $user_agent = null ) {
		if ( $user_agent == '' ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		$retarr = [];

		if ( $user_agent != '' && preg_match( '/App: v([\d]{1,3}(?:\.[\d]{1,3}){0,2})(; StationType: (\d{2}); OS: (.*); OSVersion: (.*); OSArch: (.*); DeviceModel: (.*))*/i', $user_agent, $matches ) == 1 ) {
			if ( isset($matches[1]) ) {
				$retarr['app_version'] = $matches[1];
			}

			if ( isset($matches[3]) ) {
				$retarr['station_type'] = $matches[3];
			}

			if ( isset($matches[4]) ) {
				$retarr['os_type'] = strtolower( $matches[4] );
			}

			if ( isset($matches[5]) ) {
				$retarr['os_version'] = $matches[5];
			}

			if ( isset($matches[6]) ) {
				$retarr['os_arch'] = strtolower( $matches[6] );
			}

			if ( isset($matches[7]) ) {
				$retarr['device_model'] = strtolower( $matches[7] );
			}
		}

		return $retarr;
	}

	/**
	 * Get city, province, country based on IP address.
	 * @param $ip_address
	 * @return false|string
	 */
	static function getLocationOfIPAddress( $ip_address = null ) {
		if ( $ip_address == '' ) {
			$ip_address = Misc::getRemoteIPAddress();
		}

		$geo_ip = new GeoIP();
		$city = $geo_ip->getCity( $ip_address );
		$province = $geo_ip->getRegionCode( $ip_address );
		$country = $geo_ip->getCountryName( $ip_address );

		//$retval = Misc::formatAddress( '', false, false, $city, $province, false, $country, 'oneline' );

		$retval = '';
		if ( $country != '' ) {
			$retval = $country;
		}

		if ( $province != '' ) {
			$retval = $province .', '. $retval;
		}

		if ( $city != '' ) {
			$retval = $city .', '. $retval;
		}

		if ( $retval == '' ) {
			$retval = 'Unknown';
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	static function getRemoteIPAddress() {
		global $config_vars;

		if ( isset( $config_vars['other']['proxy_ip_address_header_name'] ) && $config_vars['other']['proxy_ip_address_header_name'] != '' ) {
			$header_name = $config_vars['other']['proxy_ip_address_header_name'];
		}

		if ( isset( $header_name ) && isset( $_SERVER[$header_name] ) && $_SERVER[$header_name] != '' ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'] .' Behind Proxy IP: '. $_SERVER[$header_name], __FILE__, __LINE__, __METHOD__, 10);

			//Make sure we handle it if multiple IP addresses are returned due to multiple proxies.
			$comma_pos = strpos( $_SERVER[$header_name], ',' );
			if ( $comma_pos !== false ) {
				Debug::text('NOTICE: Found multiple IPs in proxy header: '. $_SERVER[$header_name], __FILE__, __LINE__, __METHOD__, 10);
				//$_SERVER[$header_name] = substr( $_SERVER[$header_name], 0, $comma_pos ); //Gets the first IP address, but that could be a internal/private IP.
				$proxy_ips = explode( ',', $_SERVER[$header_name] );
				$_SERVER[$header_name] = trim( end($proxy_ips) ); //Gets the last IP address, which is the one we want.
			}

			$retval = $_SERVER[$header_name];
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
			$retval = $_SERVER['REMOTE_ADDR'];
		}

		if ( isset( $retval ) && $retval != '' ) {
			//Ensure IP address is actually valid before returning it.
			//  This can help avoid X_FORWARDED_FOR header attacks being used for SQL injections and such.
			if ( filter_var( $retval, FILTER_VALIDATE_IP ) === false ) {
				Debug::text( 'ERROR: Invalid Remote IP Address: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param bool $ignore_force_ssl
	 * @return bool
	 */
	static function isSSL( $ignore_force_ssl = false ) {
		global $config_vars;

		if ( isset( $config_vars['other']['proxy_protocol_header_name'] ) && $config_vars['other']['proxy_protocol_header_name'] != '' ) {
			$header_name = $config_vars['other']['proxy_protocol_header_name']; //'HTTP_X_FORWARDED_PROTO'; //X-Forwarded-Proto;
		}

		//ignore_force_ssl is used for things like cookies where we need to determine if SSL is *currently* in use, vs. if we want it to be used or not.
		if ( $ignore_force_ssl == false && isset( $config_vars['other']['force_ssl'] ) && ( $config_vars['other']['force_ssl'] == true ) ) {
			return true;
		} else if (
				( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) == 'on' || $_SERVER['HTTPS'] == '1' ) )
				||
				//Handle load balancer/proxy forwarding with SSL offloading.
				//FIXME: Similar to X_FORWARDED_FOR, this can have a comma and contain multiple protocols.
				( isset( $header_name ) && isset( $_SERVER[$header_name] ) && strtolower( $_SERVER[$header_name] ) == 'https' )
		) {
			return true;
		} else if ( isset( $_SERVER['SERVER_PORT'] ) && ( $_SERVER['SERVER_PORT'] == '443' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $version1
	 * @param $version2
	 * @param $operator
	 * @return mixed
	 */
	static function MajorVersionCompare( $version1, $version2, $operator ) {
		$tmp_version1 = explode( '.', $version1 ); //Return first two dot versions.
		array_pop( $tmp_version1 );
		$version1 = implode( '.', $tmp_version1 );

		$tmp_version2 = explode( '.', $version2 ); //Return first two dot versions.
		array_pop( $tmp_version2 );
		$version2 = implode( '.', $tmp_version2 );

		//Debug::Text('Comparing: Version1: '. $version1 .' Version2: '. $version2 .' Operator: '. $operator, __FILE__, __LINE__, __METHOD__, 10);

		return version_compare( $version1, $version2, $operator );
	}

	/**
	 * @param $primary_company
	 * @param $system_settings
	 * @return string
	 */
	static function getInstanceIdentificationString( $primary_company, $system_settings ) {
		$version_string = [];
		$version_string[] = 'Company:';
		$version_string[] = ( is_object( $primary_company ) ) ? $primary_company->getName() : 'N/A';
		$version_string[] = 'Edition: ' . getTTProductEditionName();
		$version_string[] = 'Key:';
		$version_string[] = ( isset( $system_settings ) && isset( $system_settings['registration_key'] ) ) ? $system_settings['registration_key'] : 'N/A';
		$version_string[] = 'Version: ' . APPLICATION_VERSION;
		$version_string[] = '('. ( ( isset( $system_settings ) && isset( $system_settings['system_version_install_date'] ) ) ? TTDate::getDate('DATE+TIME', $system_settings['system_version_install_date'] ) : 'N/A' ) .')';
		$version_string[] = 'Production Mode: ' . (int)PRODUCTION;

		return implode( ' ', $version_string );
	}

	/**
	 * Removes the word "the" from the beginning of strings and optionally places it at the end.
	 * Primarily for client/company names like: The XYZ Company -> XYZ Company, The
	 * Should often be used to sanitize metaphones.
	 * @param $str
	 * @param bool $add_to_end
	 * @return bool|string
	 */
	static function stripThe( $str, $add_to_end = false ) {
		if ( stripos( $str, 'The ' ) === 0 ) {
			$retval = substr( $str, 4 );
			if ( $add_to_end == true ) {
				$retval .= ', The';
			}

			return $retval;
		}

		return $str;
	}

	/**
	 * Remove any HTML special char (before its encoded) from the string
	 * Useful for things like government forms submitted in XML.
	 * @param $str
	 * @return mixed
	 */
	static function stripHTMLSpecialChars( $str ) {
		return str_replace( [ '&', '"', '\'', '>', '<' ], '', $str );
	}

	/**
	 * Remove any BR html tag from the string and replace it with a space.
	 * @param $str
	 * @return mixed
	 */
	static function stripBR( $str ) {
		return str_ireplace( '<br>', ' ', $str );
	}

	/**
	 * Sanitizes a string or array of strings against XSS attacks. Used before passing data to HTML emails or back to the user in HTML.
	 *    This also exists in Validator class.
	 * @param string|array $string
	 * @return array|string
	 */
	static function escapeHTML( $string ) {
		if ( is_array( $string ) ) {
			foreach( $string as $key => $value ) {
				$retval[$key] = htmlspecialchars( (string)$value, ENT_QUOTES, 'UTF-8' );
			}
		} else {
			$retval = htmlspecialchars( (string)$string, ENT_QUOTES, 'UTF-8' );
		}

		return $retval;
	}

	static function formatPhoneNumber( $phone_number, $country, $prefix_country_code = null ) {
		$retval = $phone_number;

		//Strip phone to just digits.
		$sanitized_phone_number = preg_replace('/[^0-9]/', '', $phone_number );

		if ( strtoupper( $country ) == 'US' || strtoupper( $country ) == 'CA' ) {
			$retval = '('. substr($sanitized_phone_number, -10, -7) . ') ' . substr($sanitized_phone_number, -7, -4) . '-' . substr($sanitized_phone_number, -4);

			if ( $prefix_country_code === true ) {
				$retval = '+'.$prefix_country_code .' '. $retval;
			}
		}

		return $retval;
	}

	/**
	 * @param $name
	 * @param bool $address1
	 * @param bool $address2
	 * @param bool $city
	 * @param bool $province
	 * @param bool $postal_code
	 * @param bool $country
	 * @param string $format
	 * @return string
	 */
	static function formatAddress( $name, $address1 = false, $address2 = false, $city = false, $province = false, $postal_code = false, $country = false, $format = null ) {
		$format = ( is_string( $format ) ? strtolower( $format ) : $format );

		$retarr = [];
		$city_arr = [];
		if ( $name != '' ) {
			$retarr[] = $name;
		}


		if ( $format == 'multiline_condensed' ) { //Try to reduce the number of lines the address appears on for tight spaces like checks or windowed envelopes.
			$address = '';
			if ( $address1 != '' ) {
				$address = $address1;
			}
			if ( $address2 != '' ) {
				$address .= '  ' . $address2;
			}

			if ( $address != '' ) {
				$retarr[] = $address;
			}
		} else {
			if ( $address1 != '' ) {
				$retarr[] = $address1;
			}
			if ( $address2 != '' ) {
				$retarr[] = $address2;
			}
		}

		if ( $city != '' ) {
			if ( $province != '' ) {
				$city .= ',';
			}
			$city_arr[] = $city;
		}
		if ( $province != '' ) {
			$city_arr[] = $province;
		}
		if ( $postal_code != '' ) {
			$city_arr[] = $postal_code;
		}

		if ( empty( $city_arr ) == false ) {
			$retarr[] = implode( ' ', $city_arr );
		}

		if ( $country != '' ) {
			$retarr[] = $country;
		}

		if ( $format == 'oneline' ) {
			return implode( ' ', $retarr );
		} else {
			return implode( "\n", $retarr );
		}
	}

	/**
	 * @return string
	 */
	static function getUniqueID() {
		$salt = TTPassword::getPasswordSalt();

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$retval = $salt . bin2hex( openssl_random_pseudo_bytes( 128 ) );
		} else {
			$retval = uniqid( $salt . dechex( mt_rand() ), true );
		}

		return $retval;
	}

	/**
	 * Strips any mention of path and file names from a string to avoid information exposure vulnerabilities.
	 * @param $str string
	 * @return array|string|string[]|null
	 */
	static function stripPathAndFileNames( $str ) {
		$retval = preg_replace( '/(\/.*?\.\S*)/', '*CENSORED*', $str ); //Strip all non-alpha numeric characters or underscores.

		return $retval;
	}

	/**
	 * Sanitize strings to be used in file names by converting spaces to underscore and removing non-alpha numeric characters
	 * @param $file_name
	 * @return mixed|string|string[]|null
	 */
	static function sanitizeFileName( $file_name ) {
		$retval = str_replace( ' ', '_', strtolower( Misc::stripDirectoryTraversal( trim( $file_name ) ) ) ); //Switch all spaces to underscores

		//Strip off file extension so we can sanitize just the file name part then append it to the end later on.
		$extension = pathinfo( $retval, PATHINFO_EXTENSION );
		if ( $extension != '' ) {
			$retval = pathinfo( $retval, PATHINFO_FILENAME );
		}

		$retval = preg_replace( '/[^0-9a-z\-_]/', '', $retval ); //Strip all non-alpha numeric characters or underscores. Ensure that no shell escaping is required after the result of this too.

		if ( $extension != '' ) {
			$retval .= '.'.$extension;
		}

		return $retval;
	}

	/**
	 * Removes any sort of directory traversal attacks from a file name, ie: '../../myfile.pdf'
	 * @param $file_name
	 * @return mixed|string|string[]|null
	 */
	static function stripDirectoryTraversal( $file_name ) {
		$retval = str_replace( [ '/', '\\' ], '', $file_name );

		return $retval;
	}

	/**
	 * zips an array of files and returns a file array for download
	 * @param $file_array
	 * @param bool $zip_file_name
	 * @param bool $ignore_single_file
	 * @return array|bool
	 */
	static function zip( $file_array, $zip_file_name = false, $ignore_single_file = false ) {
		if ( !is_array( $file_array ) || count( $file_array ) == 0 ) {
			return $file_array;
		}

		if ( $ignore_single_file == true && ( count( $file_array ) == 1 || key_exists( 'file_name', $file_array ) ) ) {
			//if there's just one file don't bother zipping it.
			foreach ( $file_array as $file ) {
				return $file;
			}
		} else {
			if ( $zip_file_name == '' ) {
				$file_path_info = pathinfo( $file_array[key( $file_array )]['file_name'] );
				$zip_file_name = $file_path_info['filename'] . '.zip';
			}

			global $config_vars;
			$tmp_file = tempnam( $config_vars['cache']['dir'], 'zip_' );
			unlink( $tmp_file ); //tempnam() creates a zero byte file, and ZipArchive->open() does not like getting a zero byte file as its deprecated.
			$zip = new ZipArchive();
			$result = $zip->open( $tmp_file, ZIPARCHIVE::CREATE );
			Debug::Text( 'Creating new zip file for download: ' . $zip_file_name . ' File Open Result: ' . $result, __FILE__, __LINE__, __METHOD__, 10 );

			$total_zipped_files = 0;
			foreach ( $file_array as $file ) {
				if ( isset( $file['file_name'] ) && isset( $file['data'] ) ) {
					$zip->addFromString( $file['file_name'], $file['data'] );
					$total_zipped_files++;
				}
			}

			$zip->close();
			$zip_file_contents = file_get_contents( $tmp_file );
			unlink( $tmp_file );

			if ( $total_zipped_files > 0 ) {
				$ret_arr = [ 'file_name' => $zip_file_name, 'mime_type' => 'application/zip', 'data' => $zip_file_contents ];

				return $ret_arr;
			} else {
				return false; //No ZIP files to return...
			}
		}

		return false;
	}

	/**
	 * Generic Retry handler with closures.
	 * @param $function Closure
	 * @param int $retry_max_attempts
	 * @param int $retry_sleep
	 * @param bool $continue_on_error
	 * @return mixed
	 * @throws Exception
	 */
	static function Retry( $function, $retry_max_attempts = 3, $retry_sleep = 1, $continue_on_error = false ) { //When changing function definition, also see APIFactory->RetryTransaction()
		// Help mitigate function injection attacks due to the variable function call below $transaction_function() -- If we always insist on a closure its harder for an attacker to pass in phpinfo() as the function to call for example.
		if ( !$function instanceof Closure ) {
			Debug::text( 'ERROR: Retry function is not a closure, unable to execute!', __FILE__, __LINE__, __METHOD__, 10 );
			return null;
		}

		$tmp_sleep = ( $retry_sleep * 1000000 );
		$retry_attempts = 0;
		while ( $retry_attempts < $retry_max_attempts ) {
			try {
				unset( $e ); //Clear any exceptions on retry.

				Debug::text( '==================START: RETRY BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $function(); //This function should call StartTransaction() at the beginning, and CommitTransaction() at the end.
				Debug::text( '==================END: RETRY BLOCK=====================================', __FILE__, __LINE__, __METHOD__, 10 );
			} catch ( Exception $e ) {
				$random_sleep_interval = ( ceil( ( rand() / getrandmax() ) * ( ( $tmp_sleep * 0.33 ) * 2 ) - ( $tmp_sleep * 0.33 ) ) ); //+/- 33% of the sleep time.

				Debug::text( 'WARNING: Retry block failed: Retry Attempt: ' . $retry_attempts . ' Sleep: ' . ( $tmp_sleep + $random_sleep_interval ) . '(' . $tmp_sleep . ') Code: ' . $e->getCode() . ' Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				Debug::text( '==================END: RETRY BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );

				if ( $retry_attempts < ( $retry_max_attempts - 1 ) ) { //Don't sleep on the last iteration as its serving no purpose.
					usleep( $tmp_sleep + $random_sleep_interval );
				}

				$tmp_sleep = ( $tmp_sleep * 2 ); //Exponential back-off with 25% of retry sleep time as a random value.
				$retry_attempts++;

				continue;
			}
			break;
		}

		if ( isset( $e ) ) { //$retry_attempts >= $retry_max_attempts ) { //Allow retry_max_attempts to be set at 0 to prevent any retries and fail without an error.
			Debug::text( 'ERROR: RETRY block failed after max attempts: ' . $retry_attempts . ' Max: ' . $retry_max_attempts, __FILE__, __LINE__, __METHOD__, 10 );

			//If after all the retry attempts, if there is still an error, determine if should re-throw the exception or just continue.
			if ( $continue_on_error != true ) {
				throw $e;
			}
		}

		if ( isset( $retval ) ) {
			Debug::Arr( $retval, 'Returning Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return null;
	}

	/**
	 * Checks the max post size and file upload size, and returns the lower of the two.
	 * @return int
	 */
	static function getPHPMaxUploadSize() {
		$max_post_size = (int)convertHumanSizeToBytes( ini_get( 'post_max_size' ) );
		$max_upload_filesize = (int)convertHumanSizeToBytes( ini_get( 'upload_max_filesize' ) );

		$max_size = ( $max_upload_filesize < $max_post_size ) ? $max_upload_filesize : $max_post_size;

		return (int)$max_size;
	}

	/**
	 * @return bool
	 */
	static function doesRequestExceedPHPMaxPostSize() {
		$retval = false;
		if ( isset( $_SERVER['CONTENT_LENGTH'] ) && (int)$_SERVER['CONTENT_LENGTH'] > Misc::getPHPMaxUploadSize() ) {
			$retval = true;
		}

		return $retval;
	}

	/**
	 * Returns all parsed feature flags as an array.
	 * @return array
	 */
	static function parseFeatureFlags() {
		global $config_vars, $current_company;

		//Force default values here.
		$flag_arr = [
				'support_chat'         => true,
				'show_feedback'        => false,
				'job_queue'            => false,
				'custom_field'         => true,
				'custom_field_punch'   => false,
				'ldap_authentication'  => true,
				'assistant'            => true,
				'recruitment_analysis' => true,
		];

		//Community edition defaults custom fields to false.
		//  NOTE: These same defaults need to be added below in the $current_company check too.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) { //Installed edition is community.
			$flag_arr['custom_field'] = false;
			$flag_arr['ldap_authentication'] = false;
		}

		//Get global feature flags first, so we can append company specific feature flags after that overwrite them.
		$split_flags = [];
		if ( isset( $config_vars ) && isset( $config_vars['other']['feature_flags'] ) && $config_vars['other']['feature_flags'] != '' ) {
			$split_flags = explode( ',', $config_vars['other']['feature_flags'] );
		}

		if ( isset( $current_company ) && is_object( $current_company ) ) { //Currently logged in company edition.
			// NOTE: These defaults should match the above for Community edition as well.
			if ( $current_company->getProductEdition() == TT_PRODUCT_COMMUNITY ) {
				$flag_arr['custom_field'] = false;
				$flag_arr['ldap_authentication'] = false;
			}

			//Get company specific feature flags that can overwrite any global ones.
			if ( isset( $config_vars ) && isset( $config_vars['other']['company_feature_flags'] ) && is_array( $config_vars['other']['company_feature_flags'] ) && isset( $config_vars['other']['company_feature_flags'][$current_company->getId()] ) ) {
				$split_flags = array_merge( $split_flags, explode( ',', $config_vars['other']['company_feature_flags'][$current_company->getId()] ) );
			}
		}

		//Parse each individual feature flag, later ones  in the list (ie: company specific) take priority over earlier ones.
		if ( isset( $split_flags ) && is_array( $split_flags ) ) {
			foreach( $split_flags as $split_flag ) {
				$split_flag_values = explode( '=', $split_flag );

				$tmp_key = strtolower( trim( $split_flag_values[0] ) );
				$tmp_value = strtolower( trim( $split_flag_values[1] ) );
				if ( !is_numeric( $tmp_value ) ) {
					if ( $tmp_value == 'true' ) {
						$tmp_value = true;
					} else {
						$tmp_value = false;
					}
				}

				$flag_arr[ $tmp_key ] = $tmp_value;
			}
		}

		return $flag_arr;
	}

	/**
	 * Returns the value of a specific feature flag, and if none is set returns the default value.
	 * @param $flag
	 * @param $default_value
	 * @return mixed|null
	 */
	static function getFeatureFlag( $flag = null, $default_value = null ) {
		$flag_arr = Misc::parseFeatureFlags();

		if ( isset( $flag_arr[$flag] ) ) {
			return $flag_arr[$flag];
		} else {
			return $default_value;
		}
	}

	/**
	 * Generate a shell command to pass thru the TT_CONFIG_FILE environment variable through to executed cron jobs or system job queue commands.
	 * @return string
	 */
	static function getEnvironmentVariableConfigFile() {
		//Prepare environment prefix.
		if ( OPERATING_SYSTEM == 'LINUX' && isset( $_SERVER['TT_CONFIG_FILE'] ) && $_SERVER['TT_CONFIG_FILE'] != '' ) {
			$retval = 'export TT_CONFIG_FILE='. $_SERVER['TT_CONFIG_FILE'] .';';
		} else {
			$retval = '';
		}

		return $retval;
	}

	static function isPortOpen( $host, $port, $timeout = 3 ) {
		$fp = @fsockopen( $host, $port, $errno, $errstr, $timeout );
		if ( $fp == false ) {
			Debug::Text( 'Port: '. $port .' to: '. $host .' is NOT open: ' . $errstr . ' (' . $errno . ')', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		} else {
			fclose( $fp );
			$retval = true;
			Debug::Text( 'Port: '. $port .' to: '. $host .' is open!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	static function isTTAssistantEnabled(): bool {
		$retval = true;

		if ( !Misc::getFeatureFlag( 'assistant' ) ) {
			Debug::Text( 'TT Assistant is disabled by feature flag.', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}

		//Further checks can be done here such as how many tokens a company has left, etc.

		return $retval;
	}

	static function isIPinRange($current_ip, $trusted_ip, $netmask) {
		// Check IP type: IPv4 or IPv6
		$is_ipv6 = strpos($current_ip, ':') !== false;

		// Convert IPs to binary format
		$current_ip_bin = inet_pton($current_ip);
		$trusted_ip_bin = inet_pton($trusted_ip);

		// Subnet mask
		$subnet_mask_bin = str_repeat("\xFF", $netmask >> 3) . chr((0xFF << (8 - ($netmask % 8))) & 0xFF) . str_repeat("\x00", $is_ipv6 ? 16 : 4 - (($netmask + 7) >> 3));

		// Compare the IPs within the mask
		return substr_compare($current_ip_bin & $subnet_mask_bin, $trusted_ip_bin & $subnet_mask_bin, 0, $is_ipv6 ? 16 : 4) === 0;
	}

}

?>
