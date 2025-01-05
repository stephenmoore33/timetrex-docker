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

class TTMath {
	static public $scale = 10;

	static function __constuct() {
		if ( function_exists( 'bcscale' ) ) {
			self::$scale = bcscale();
		}

		return true;
	}

	/**
	 * Takes any input and converts it to a numeric string for input into bcmath functions without causing: Argument #1 ($num1) is not well-formed
	 * @param mixed $value
	 * @return string
	 */
	static function getNumericAsString( $value ) {
		if ( !is_numeric( $value ) ) {
			$value = 0;
		}

		if ( is_string( $value ) ) {
			$retval = $value;
		} else if ( is_int( $value ) ) {
			//Detect integers and return them as a string immediately, as this avoids putting them through number_format() which casts them to float and loses precision.
			$retval = strval( $value );
		} else if ( $value == 0 ) {
			$retval = '0';
		} else {
			//Detect if scientific notation is being used and if the number of significant digits exceeds that which is defined in PHP.
			//  If so, we can be sure precision has been lost and just return 0 instead as we can't rely on the value anyways.
			if ( strpos( $value, 'E' ) !== false && (int)substr( $value, -2 ) > ( ini_get( 'precision' ) + 1 ) ) {
				return '0';
				//throw new Exception( 'Scientific notation detected with more significant digits than PHP can handle: '. $value );
			}

			//$retval = sprintf( '%.'. self::$scale .'f', $value );
			$retval = number_format( (float)$value, self::$scale, '.', '' ); //This seems to be marginally faster than sprintf().
		}

		return $retval;
	}

	static function multiAdd( ...$nums ) {
		$sum = 0;
		foreach ( $nums as $num ) {
			if ( is_array( $num ) ) {
				$sum = TTMath::multiAdd( $sum, ...$num );
			} else {
				$sum = TTMath::add( $sum, $num );
			}
		}

		return $sum;
	}

	static function add( $num1, $num2, $scale = null ) {
		return bcadd( TTMath::getNumericAsString( $num1 ), TTMath::getNumericAsString( $num2 ), $scale );
	}

	static function multiSub( ...$nums ) {
		$sum = null;
		foreach ( $nums as $num ) {
			if ( is_array( $num ) ) {
				$sum = TTMath::multiSub( $sum, ...$num );
			} else {
				if ( $sum === null ) {
					$sum = $num;
					continue;
				}
				$sum = TTMath::sub( $sum, $num );
			}
		}

		return $sum;
	}

	static function sub( $num1, $num2, $scale = null ) {
		return bcsub( TTMath::getNumericAsString( $num1 ), TTMath::getNumericAsString( $num2 ), $scale );
	}

	static function mul( $num1, $num2, $scale = null ) {
		return bcmul( TTMath::getNumericAsString( $num1 ), TTMath::getNumericAsString( $num2 ), $scale );
	}

	static function div( $num1, $num2, $scale = null ) {
		$num2 = TTMath::getNumericAsString( $num2 );
		if ( $num2 == 0 ) {
			return 0;
		}

		return bcdiv( TTMath::getNumericAsString( $num1 ), $num2, $scale );
	}

	static function mod( $num1, $num2, $scale = null ) {
		$num2 = TTMath::getNumericAsString( $num2 );
		if ( $num2 == 0 ) {
			return 0;
		}

		return bcmod( TTMath::getNumericAsString( $num1 ), $num2, $scale );
	}

	/**
	 * Returns 0=Equal, 1=Num1 is larger, -1=Num1 is smaller
	 * @param $num1
	 * @param $num2
	 * @return int
	 */
	static function comp( $num1, $num2 ) {
		return bccomp( TTMath::getNumericAsString( $num1 ), TTMath::getNumericAsString( $num2 ) );
	}

	/**
	 * Compares two float values for equality and greater/less than. Required because floats should never be compared directly due to epsilon differences
	 *   For example: (float)845.92 + (float)14.3 != (float)860.22 -- Yet it does as far as a human is concerned.
	 * @param $float1
	 * @param $float2
	 * @param string $operator
	 * @return bool
	 */
	public static function compareFloat( $float1, $float2, $operator = '==' ) {
		$retval = false;

		$bc_comp_result = TTMath::comp( $float1, $float2 );
		switch ( $operator ) {
			case '==':
				if ( $bc_comp_result === 0 ) {
					$retval = true;
				}
				break;
			case '>=':
				if ( $bc_comp_result >= 0 ) {
					$retval = true;
				}
				break;
			case '<=':
				if ( $bc_comp_result <= 0 ) {
					$retval = true;
				}
				break;
			case '>':
				if ( $bc_comp_result === 1 ) {
					$retval = true;
				}
				break;
			case '<':
				if ( $bc_comp_result === -1 ) {
					$retval = true;
				}
				break;
		}

		return $retval;
	}

	/**
	 * @param $amount
	 * @param $limit
	 * @return int
	 */
	public static function getAmountToLimit( $amount, $limit ) {
		if ( $amount == 0 || $amount === '' || $amount === null || $amount === false || $amount === true || TTMath::compareFloat( $amount, 0, '==' ) ) {
			return 0;
		}

		//If no limit is specified, just return the amount.
		if ( $limit == 0 || $limit === '' || $limit === null || $limit === false || $limit === true || TTMath::compareFloat( $limit, 0, '==' ) ) {
			return $amount;
		}

		//Cases:
		// Positive Amount, 0 Limit 		-- Always return the amount as if there is no limit. (handled above)
		// Positive Amount, Positive Limit 	-- Handle up to limit
		// Positive Amount, Negative Limit 	-- Always return 0 as they cross 0 and by definition have already crossed the limit.
		//
		// Negative Amount, 0 Limit 		-- Always return the amount as if there is no limit. (handled above)
		// Negative Amount, Positive Limit 	-- Always return 0 as they cross 0 and by definition have already crossed the limit.
		// Negative Amount, Negative Limit 	-- Handle down to limit

		//$retval = 0;
		if ( $amount > 0 && $limit < 0 ) {
			$retval = 0;
		} else if ( $amount < 0 && $limit > 0 ) {
			$retval = 0;
		} else {
			if ( $amount >= 0 ) {
				if ( $amount >= $limit ) {
					//Amount is greater than limit, just use limit.
					$retval = $limit;
				} else {
					$retval = $amount;
				}
			} else {
				if ( $amount <= $limit ) {
					//Amount is less than limit, just use limit.
					$retval = $limit;
				} else {
					$retval = $amount;
				}
			}
		}

		return $retval;
	}

	/**
	 * Returns an adjusted current amount, and the amount under a limit and over a limit.
	 *    Ideal for calculating wages up to a maximum limit and increasing the YTD amount in a loop. For example social security wage limits.
	 * @param $current_amount   float Current amount
	 * @param $ytd_amount       float Running balance leading up to maximum limit
	 * @param $ytd_amount_limit float Overall maximum limit
	 * @return array|int[]
	 */
	public static function getAmountAroundLimit( $current_amount, $ytd_amount, $ytd_amount_limit ) {
		if ( $ytd_amount < $ytd_amount_limit ) {
			$ytd_amount_over_ytd_amount_limit = TTMath::add( $current_amount, $ytd_amount );
			if ( $ytd_amount_over_ytd_amount_limit > $ytd_amount_limit ) {
				$retarr = [ 'adjusted_amount' => TTMath::sub( $ytd_amount_limit, $ytd_amount ), 'under_limit' => 0, 'over_limit' => TTMath::sub( TTMath::add( $ytd_amount, $current_amount ), $ytd_amount_limit ) ];
			} else {
				$retarr = [ 'adjusted_amount' => $current_amount, 'under_limit' => TTMath::sub( $ytd_amount_limit, TTMath::add( $ytd_amount, $current_amount ) ), 'over_limit' => 0 ];
			}
		} else if ( $ytd_amount == $ytd_amount_limit ) {
			if ( $current_amount >= 0 ) {
				$retarr = [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => $current_amount ];
			} else {
				$retarr = [ 'adjusted_amount' => $current_amount, 'under_limit' => abs( $current_amount ), 'over_limit' => 0 ];
			}
		} else if ( $ytd_amount > $ytd_amount_limit ) {
			if ( $current_amount >= 0 ) {
				$retarr = [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => TTMath::sub( $ytd_amount, $ytd_amount_limit ) ];
			} else {
				$ytd_amount_under_ytd_amount_limit = TTMath::add( $current_amount, $ytd_amount );
				if ( $ytd_amount_under_ytd_amount_limit < $ytd_amount_limit ) {
					$retarr = [ 'adjusted_amount' => TTMath::sub( TTMath::add( $ytd_amount, $current_amount ), $ytd_amount_limit ), 'under_limit' => abs( TTMath::sub( TTMath::add( $ytd_amount, $current_amount ), $ytd_amount_limit ) ), 'over_limit' => 0 ];
				} else {
					$retarr = [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => TTMath::sub( TTMath::add( $ytd_amount, $current_amount ), $ytd_amount_limit ) ];
				}
			}
		}

		return $retarr;
	}

	/**
	 * This is can be used to handle YTD amounts.
	 * @param $amount
	 * @param $limit
	 * @return string
	 */
	public static function getAmountDifferenceToLimit( $amount, $limit ) {
		//If no limit is specified, just return the amount.
		if ( $limit === '' || $limit === null || $limit === false || $limit === true ) {
			return $amount;
		}


		if ( $amount < 0 && $limit > 0 ) {
			$retval = TTMath::add( abs( $amount ), $limit ); //Return value that gets the amount to the limit.
		} else if ( $amount > 0 && $limit < 0 ) {
			$retval = TTMath::add( TTMath::mul( $amount, -1 ), $limit ); //Return value that gets the amount to the limit.
		} else {
			$tmp_amount = TTMath::getAmountToLimit( $amount, $limit );
			$retval = TTMath::sub( $limit, $tmp_amount );
		}

		return $retval;
	}

	/**
	 * @param float|int|string $float
	 * @return int
	 */
	public static function getBeforeDecimal( $float ) {
		$float = TTMath::getNumericAsString( $float );

		//Locale agnostic, so we can handle decimal separators that are commas.
		if ( strpos( $float, ',' ) !== false ) {
			$separator = ',';
		} else {
			$separator = '.';
		}

		$split_float = explode( $separator, $float );

		//Cast to INT, this will max out at 64bit integer values.
		$retval = (int)$split_float[0];

		//Return false if it overflows.
		if ( (int)$retval >= 9223372036854775807 || (int)$retval <= -9223372036854775808 ) {
			return false;
		}

		return $retval;
	}

	/**
	 * @param float|int|string $float
	 * @param bool $format_number
	 * @return int
	 */
	public static function getAfterDecimal( $float, $format_number = true ) {
		if ( $format_number == true ) {
			$float = TTMath::MoneyRound( $float );
		}

		//Locale agnostic, so we can handle decimal separators that are commas.
		if ( strpos( $float, ',' ) !== false ) {
			$separator = ',';
		} else {
			$separator = '.';
		}

		$split_float = explode( $separator, $float );
		if ( isset( $split_float[1] ) ) {
			//Cast to INT, this will max out at 64bit integer values.
			$retval = (int)$split_float[1];

			//Return false if it overflows.
			if ( (int)$retval >= 9223372036854775807 || (int)$retval <= -9223372036854775808 ) {
				return false;
			}

			return $retval;
		} else {
			return 0;
		}
	}

	/**
	 * This function totals arrays where the data wanting to be totaled is deep in a multi-dimentional array.
	 * Usually a row array just before its passed to smarty.
	 * @param $array
	 * @param null $element
	 * @param null $decimals
	 * @param bool $include_non_numeric
	 * @return array|bool
	 */
	public static function ArrayAssocSum( $array, $element = null, $decimals = null, $include_non_numeric = false ) {
		if ( !is_array( $array ) ) {
			return false;
		}

		$retarr = [];
		$totals = [];

		foreach ( $array as $value ) {
			if ( isset( $element ) && isset( $value[$element] ) ) {
				foreach ( $value[$element] as $sum_key => $sum_value ) {
					if ( !isset( $totals[$sum_key] ) ) {
						$totals[$sum_key] = 0;
					}
					$totals[$sum_key] += $sum_value;
				}
			} else {
				//Debug::text(' Array Element not set: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach ( $value as $sum_key => $sum_value ) {
					if ( !isset( $totals[$sum_key] ) ) {
						$totals[$sum_key] = 0;
					}

					//Both $totals[$sum_key] and $sum_value need to be numeric to add them to each other.
					if ( !is_numeric( $sum_value ) || !is_numeric( $totals[$sum_key] ) ) {
						if ( $include_non_numeric == true && $sum_value != '' ) {
							$totals[$sum_key] = $sum_value;
						}
					} else {
						$totals[$sum_key] = TTMath::add( $totals[$sum_key], $sum_value );
					}
					//Debug::text(' Sum: '. $totals[$sum_key] .' Key: '. $sum_key .' This Value: '. $sum_value, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		//format totals
		if ( $decimals !== null ) {
			foreach ( $totals as $retarr_key => $retarr_value ) {
				//Debug::text(' Number Formatting: '. $retarr_value, __FILE__, __LINE__, __METHOD__, 10);
				$retarr[$retarr_key] = number_format( $retarr_value, $decimals, '.', '' );
			}
		} else {
			return $totals;
		}
		unset( $totals );

		return $retarr;
	}

	/**
	 * @param $value
	 * @param int $decimals
	 * @param null $currency_obj
	 * @return string
	 */
	public static function MoneyRoundDifference( $value, $decimals = 2, $currency_obj = null ) {
		$rounded_value = TTMath::MoneyRound( $value, $decimals, $currency_obj );
		$rounding_diff = TTMath::sub( $rounded_value, $value );

		Debug::Text( 'Input Value: ' . $value . ' Rounded Value: ' . $rounding_diff . ' Diff: ' . $rounding_diff, __FILE__, __LINE__, __METHOD__, 10 );

		return $rounding_diff;
	}

	/**
	 * Takes an array with columns, and a 2nd array with column names to sum.
	 * @param $data
	 * @param $sum_elements
	 * @return bool|int|string
	 */
	public static function sumMultipleColumns( $data, $sum_elements ) {
		if ( !is_array( $data ) ) {
			return false;
		}

		if ( !is_array( $sum_elements ) ) {
			return false;
		}

		$retval = 0;

		foreach ( $sum_elements as $sum_element ) {
			if ( isset( $data[$sum_element] ) ) {
				$retval = TTMath::add( $retval, $data[$sum_element] );
				//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return $retval;
	}

	/**
	 * Add numeric only elements in an array using TTMath::add()
	 * @param $arr
	 * @param $decimals
	 * @return int|string
	 */
	public static function ArraySum( $arr, $decimals = null ) {
		$retval = 0;
		if ( is_array( $arr ) && !empty( $arr ) ) {
			foreach ( $arr as $value ) {
				if ( is_numeric( $value ) ) {
					$retval = TTMath::add( $retval, $value );
				}
			}
		}

		if ( $decimals !== null ) {
			$retval = number_format( $retval, $decimals, '.', '' );
		}

		return $retval;
	}

	/**
	 * @param $value
	 * @param int $old_min
	 * @param int $old_max
	 * @param int $new_min
	 * @param int $new_max
	 * @return float|int
	 */
	public static function reScaleRange( $value, $old_min = 1, $old_max = 5, $new_min = 1, $new_max = 10 ) {
		if ( $value === '' || $value === null ) {
			return $value;
		} else {
			$retval = ( ( ( ( $value - $old_min ) * ( $new_max - $new_min ) ) / ( $old_max - $old_min ) ) + $new_min );

			return $retval;
		}
	}

	/**
	 * Takes a score within a range, and inverts it to the opposite side of the range.
	 * @param $value
	 * @param $min_range
	 * @param $max_range
	 * @return mixed
	 */
	static function invertRange( $value, $min_range, $max_range ) {
		// Inverting the score
		$inverted_score = ( $max_range - ( $value + $min_range ) );

		// Ensuring the inverted score stays within the range
		$inverted_score = max( $min_range, min( $inverted_score, $max_range ) );

		return $inverted_score;
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	public static function removeDecimal( $value ) {
		return str_replace( '.', '', number_format( $value, 2, '.', '' ) );
	}

	/**
	 * Adds '#' to beginning and end of columns so they can be used as variables in a formula.
	 * @param $columns
	 * @return array
	 */
	static public function formatFormulaColumns( $columns ) {
		if ( !is_array( $columns ) ) {
			$columns = (array)$columns;
		}

		$retval = [];
		foreach ( $columns as $key => $val ) {
			$new_key =  '#'. Misc::trimSortPrefix( $key ) .'#';

			$retval[$new_key] = $val;
		}

		return $retval;
	}

	/**
	 * Round currency value without formatting it. In most cases where Misc::MoneyFormat( $var, FALSE ) is used, this should be used instead.
	 *
	 * @param float|int|null $value
	 * @param int $decimals
	 * @param null|CurrencyFactory $currency_obj
	 * @return float|int
	 */
	public static function MoneyRound( $value, $decimals = 2, $currency_obj = null ) {
		if ( is_object( $currency_obj ) ) {
			$retval = $currency_obj->round( $value );
		} else {
			//When using round() it returns a float, so large values like 100000000000000000000.00 get converted to scientific notation when passed to bcmath() due to the string conversion. Use number_format() instead.
			//$retval = round( $value, $decimals );
			//Could use TTMath::add( $value, 0, $decimals ) to round larger values perhaps?
			$retval = number_format( (float)$value, $decimals, '.', '' );
		}

		return $retval;
	}

	/**
	 * value should be a float and not a string. be sure to run this before TTi18n currency or number formatter due to foreign numeric formatting for decimal being a comma.
	 * @param float|int|string $value float
	 * @param int $minimum_decimals
	 * @return string
	 */
	public static function removeTrailingZeros( $value, $minimum_decimals = 2 ) {
		//Remove trailing zeros after the decimal, leave a minimum of X though.
		//*NOTE: This should always be passed in a float, so we don't need to worry about locales or TTi18n::getDecimalSymbol(), since we don't set LC_NUMERIC anymore.
		//       If you are running into problems traced to here, try casting to float first.
		//		 If a casted float value is float(50), there won't be a decimal place, so make sure we handle those cases too.
		if ( is_float( $value ) || strpos( $value, '.' ) !== false ) {
			$trimmed_value = (float)$value;
			if ( strpos( $trimmed_value, '.' ) !== false ) {
				$tmp_minimum_decimals = strlen( (int)strrev( $trimmed_value ) );
			} else {
				$tmp_minimum_decimals = 0;
			}

			if ( $tmp_minimum_decimals > $minimum_decimals ) {
				$minimum_decimals = $tmp_minimum_decimals;
			}

			return number_format( $trimmed_value, $minimum_decimals, '.', '' );
		}

		return $value;
	}

}

?>
