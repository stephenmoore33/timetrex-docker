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
 * @package API\SystemJobQueue
 */
class APISystemJobQueue extends APIFactory {
	protected $main_class = 'SystemJobQueueFactory';

	/**
	 * APISystemJobQueue constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get SystemJobQueue data for one or more SystemJobQueue.
	 * @return array|bool
	 */
	function getSystemJobQueue( $data = null, $disable_paging = false ) {
		if( !isset( $data['filter_items_per_page'] ) ) {
			$data['filter_items_per_page'] = 10;
		}

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$data['filter_data']['user_id'] = $this->getCurrentUserObject()->getId();

		$ndtlf = TTnew( 'SystemJobQueueListFactory' ); /** @var SystemJobQueueListFactory $ndtlf */
		$ndtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort']);
		Debug::Text( 'Record Count: ' . $ndtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $ndtlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $ndtlf );

			$retarr = [];
			foreach ( $ndtlf as $ndt_obj ) {
				$tmp_data = $ndt_obj->getObjectAsArray( $data['filter_columns'] );
				$tmp_data['elapsed_time'] = TTDate::getHumanTimeSince( $ndt_obj->getEffectiveDate() );
				$retarr[] = $tmp_data;
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Returns an int count of pending and running jobs for current user.
	 * @return int|bool
	 */
	function getPendingAndRunningSystemJobQueue() {
		$sjqlf = TTnew( 'SystemJobQueueListFactory' ); /** @var SystemJobQueueListFactory $sjqlf */
		$total = $sjqlf->getPendingSystemJobsByCompanyIdAndUserid( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );

		return $this->returnHandler( $total );
	}
}

?>
