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
class APITest extends APIFactory {
	protected $main_class = '';

	/**
	 * APITest constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * @param $test
	 * @return string
	 */
	function HelloWorld( $test ) {
		return "You said: $test";
	}

	/**
	 * @param int $seconds
	 * @return bool
	 */
	function delay( $seconds = 10 ) {
		Debug::text( 'delay: ' . $seconds, __FILE__, __LINE__, __METHOD__, 9 );

		sleep( $seconds );

		return true;
	}

	/**
	 * @return array
	 */
	function getDataGridData() {
		$retarr = [
				[
						'first_name' => 'Jane',
						'last_name'  => 'Doe',
				],
				[
						'first_name' => 'John',
						'last_name'  => 'Doe',
				],
				[
						'first_name' => 'Ben',
						'last_name'  => 'Smith',
				],

		];

		return $retarr;
	}

	/**
	 * Return large dataset to test performance.
	 * @param int $max_size
	 * @param int $delay
	 * @param string $progress_bar_id UUID
	 * @return array
	 */
	function getLargeDataSet( $max_size = 100, $delay = 100000, $progress_bar_id = null ) {
		if ( $max_size > 9999 ) {
			$max_size = 9999;
		}

		if ( $progress_bar_id == '' ) {
			$progress_bar_id = $this->getAPIMessageID();
		}

		$this->getProgressBarObject()->start( $progress_bar_id, $max_size );

		$retarr = [];
		for ( $i = 1; $i <= $max_size; $i++ ) {
			$retarr[] = [ 'foo1' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3' ];
			usleep( $delay );
			$this->getProgressBarObject()->set( $progress_bar_id, $i );
		}

		$this->getProgressBarObject()->stop( $progress_bar_id );

		return $retarr;
	}

	/**
	 * Date test, since Flex doesn't handle timezones very well, run tests to ensure things are working correctly.
	 * @param int $test
	 * @return array
	 */
	function dateTest( $test = 1 ) {

		$retarr = [];
		switch ( $test ) {
			case 1:
				$retarr = [
						strtotime( '30-Oct-09 5:00PM' ) => TTDate::getDBTimeStamp( strtotime( '30-Oct-09 5:00PM' ) ),
						strtotime( '31-Oct-09 5:00PM' ) => TTDate::getDBTimeStamp( strtotime( '31-Oct-09 5:00PM' ) ),
						strtotime( '01-Nov-09 5:00PM' ) => TTDate::getDBTimeStamp( strtotime( '01-Nov-09 5:00PM' ) ),
						strtotime( '02-Nov-09 5:00PM' ) => TTDate::getDBTimeStamp( strtotime( '02-Nov-09 5:00PM' ) ),
				];

				break;
			case 2:
				$retarr = [
						strtotime( '30-Oct-09 5:00PM' ) => TTDate::getFlexTimeStamp( strtotime( '30-Oct-09 5:00PM' ) ),
						strtotime( '31-Oct-09 5:00PM' ) => TTDate::getFlexTimeStamp( strtotime( '31-Oct-09 5:00PM' ) ),
						strtotime( '01-Nov-09 5:00PM' ) => TTDate::getFlexTimeStamp( strtotime( '01-Nov-09 5:00PM' ) ),
						strtotime( '02-Nov-09 5:00PM' ) => TTDate::getFlexTimeStamp( strtotime( '02-Nov-09 5:00PM' ) ),
				];

				break;
		}

		return $retarr;
	}
}

?>
