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
class URLBuilder {
	static protected $data = [];
	static protected $script = 'index.php';

	/**
	 * Recursively convert an array to a URL.
	 * @param $var
	 * @param null $varName
	 * @param bool $sub_array
	 * @return string
	 */
	static function urlencode_array( $var, $varName = null, $sub_array = false ) {
		$separator = '&';
		$toImplode = [];
		foreach ( $var as $key => $value ) {
			if ( is_array( $value ) ) {

				if ( $sub_array == false ) {
					$toImplode[] = self::urlencode_array( $value, $key, true );
				} else {
					$toImplode[] = self::urlencode_array( $value, $varName . '[' . $key . ']', true );
				}
			} else {
				if ( $sub_array == true ) {
					//$toImplode[] = $varName.'['.$key.']='.urlencode($value);
					$toImplode[] = $varName . '[' . $key . ']=' . $value;
				} else {
					//$toImplode[] = $key.'='.urlencode($value);
					$toImplode[] = $key . '=' . $value;
				}
			}
		}

		return implode( $separator, $toImplode );
	}

	/**
	 * @param $script
	 * @param null $array
	 * @return bool
	 */
	static function setURL( $script, $array = null ) {
		//Debug::Arr(self::$data, 'Before: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $array ) && count( $array ) > 0 ) {
			self::$data = array_merge( self::$data, $array );
		}
		//Debug::Arr(self::$data, 'After: ', __FILE__, __LINE__, __METHOD__, 10);

		self::$script = $script;

		return true;
	}

	/**
	 * @param null $array
	 * @param null $script
	 * @param bool $merge
	 * @return null|string
	 */
	static function getURL( $array = null, $script = null, $merge = true ) {
		//Debug::Arr($array, 'Passed Array', __FILE__, __LINE__, __METHOD__, 10);

		//Debug::Arr(self::$data, 'bSelf Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($array, 'bArray: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $array ) && count( $array ) > 0 && $merge == true ) {
			$array = array_merge( self::$data, $array );
		} else if ( $array == null && $merge == true ) {
			$array = self::$data;
		} //else Use $array as is.

		//Debug::Arr($array, 'bAfter: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $script == null ) {
			//$script = Environment::getBaseURL().self::$script;
			$script = self::$script;
		}

		//Debug::Arr($array, 'Final Array', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $array ) && count( $array ) > 0 ) {
			$url_values = self::urlencode_array( $array );
			//Debug::Text('URL Values: '. $url_values, __FILE__, __LINE__, __METHOD__, 10);

			//if (isset($url_values) AND is_array($url_values)) {
			if ( isset( $url_values ) && $url_values != '' ) {
				$url = '?' . $url_values;
			} else {
				$url = '?';
			}
		}

		if ( isset( $url ) ) {
			$retval = $script . $url;
		} else {
			$retval = $script;
		}

		//Debug::Text('URL: '. $retval, __FILE__, __LINE__, __METHOD__, 11);

		return $retval;
	}
}

?>
