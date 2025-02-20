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
 * @package API\Core
 */
class APIProgressBar {
	protected $main_class = 'ProgressBar';

	protected $obj = null;

	/**
	 * APIProgressBar constructor.
	 */
	public function __construct() {
		$this->obj = new ProgressBar();

		return true;
	}

	/**
	 * Alias for get(), because now that TimeTrexClientAPI is a proxy method, it can't call an API method that is just named "get".
	 * @param bool $key
	 * @return bool
	 */
	function getProgressBar( $key = false ) {
		return $this->get( $key );
	}

	/**
	 * @param bool $key
	 * @return bool
	 */
	function get( $key = false ) {
		if ( $key != '' ) {
			return $this->obj->get( $key );
		}

		return false;
	}

	/**
	 * @param $key
	 * @param int $total_iterations
	 * @return bool
	 */
	function test( $key, $total_iterations = 10 ) {
		return $this->obj->test( $key, $total_iterations = 10 );
	}
}

?>
