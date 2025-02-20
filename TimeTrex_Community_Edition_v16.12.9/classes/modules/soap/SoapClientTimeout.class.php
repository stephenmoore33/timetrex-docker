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
 * This class extends the built-in PHP SoapClient class to use CURL with proper timeout periods.
 * Class SoapClientTimeout
 */
class SoapClientTimeout extends SoapClient {
	private $timeout;

	/**
	 * @param $timeout
	 * @throws Exception
	 */
	public function __setTimeout( $timeout ) {
		if ( !is_int( $timeout ) && !is_null( $timeout ) ) {
			throw new Exception( "Invalid timeout value" );
		}
		$this->timeout = $timeout;
	}

	/**
	 * @param string $request
	 * @param string $location
	 * @param string $action
	 * @param int $version
	 * @param bool $one_way
	 * @return mixed|string
	 * @throws Exception
	 */
	#[\ReturnTypeWillChange]
	public function __doRequest( $request, $location, $action, $version, $one_way = false ) {
		if ( !$this->timeout ) {
			// Call via parent because we require no timeout
			$response = parent::__doRequest( $request, $location, $action, $version, $one_way );
		} else {
			// Call via Curl and use the timeout
			$curl = curl_init( $location );

			curl_setopt( $curl, CURLOPT_VERBOSE, false );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $request );
			curl_setopt( $curl, CURLOPT_HEADER, false );
			curl_setopt( $curl, CURLOPT_HTTPHEADER, [ "Content-Type: text/xml" ] );
			curl_setopt( $curl, CURLOPT_TIMEOUT, $this->timeout );

			$response = curl_exec( $curl );

			if ( curl_errno( $curl ) ) {
				throw new Exception( curl_error( $curl ) );
			}

			curl_close( $curl );
		}

		// Return?
		if ( !$one_way ) {
			return $response;
		}

		return false;
	}
}

?>
