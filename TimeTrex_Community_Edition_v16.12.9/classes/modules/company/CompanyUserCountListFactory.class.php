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
 * @package Modules\Company
 * @implements IteratorAggregate<CompanyUserCountFactory>
 */
class CompanyUserCountListFactory extends CompanyUserCountFactory implements IteratorAggregate {

	/**
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = null, $page = null, $where = null, $order = null ) {
		$query = '
					select	*
					from	' . $this->getTable() . '
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$this->rs = $this->getCache( $id );
		if ( $this->rs === false ) {
			$ph = [
					'id' => TTUUID::castUUID( $id ),
			];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where	id = ?
						';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $id );
		}

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getByCompanyId( $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getActiveUsers( $limit = null, $page = null, $where = null, $order = null ) {

		$uf = new UserFactory();

		$query = '
					select	company_id,
							count(*) as total
					from	' . $uf->getTable() . '
					where
						status_id = 10
						AND deleted = 0
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getInActiveUsers( $limit = null, $page = null, $where = null, $order = null ) {

		$uf = new UserFactory();

		$query = '
					select	company_id,
							count(*) as total
					from	' . $uf->getTable() . '
					where
						status_id != 10
						AND deleted = 0
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getDeletedUsers( $limit = null, $page = null, $where = null, $order = null ) {

		$uf = new UserFactory();

		$query = '
					select	company_id,
							count(*) as total
					from	' . $uf->getTable() . '
					where
						deleted = 1
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id      UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getMinAvgMaxByCompanyIdAndStartDateAndEndDate( $id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $id ),
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	' . $this->getTable() . '
					where	company_id = ?
						AND date_stamp >= ?
						AND date_stamp <= ?
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * Returns data for multiple companies, used by the API.
	 * @param string $id      UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getMinAvgMaxByCompanyIDsAndStartDateAndEndDate( $id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
			//'company_id' => TTUUID::castUUID($id),
			'start_date' => $this->db->BindDate( $start_date ),
			'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select
							company_id,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	' . $this->getTable() . '
					where
						date_stamp >= ?
						AND date_stamp <= ? ';

		$query .= $this->getWhereClauseSQL( 'company_id', $id, 'uuid_list', $ph );

		$query .= ' group by company_id';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id      UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getMonthlyMinAvgMaxByCompanyIdAndStartDateAndEndDate( $id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $id ),
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select
							( to_char(date_stamp, \'YYYY-MM\') || \'-01\' ) as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	' . $this->getTable() . '
					where	company_id = ?
						AND date_stamp >= ?
						AND date_stamp <= ?
					GROUP BY ( to_char(date_stamp, \'YYYY-MM\') || \'-01\' )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getMonthlyMinAvgMaxByStartDateAndEndDate( $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select
							company_id,
							( to_char(date_stamp, \'YYYY-MM-01\') ) as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	' . $this->getTable() . '
					where
						date_stamp >= ?
						AND date_stamp <= ?
					GROUP BY company_id, ( to_char(date_stamp, \'YYYY-MM-01\') )
					ORDER BY company_id, ( to_char(date_stamp, \'YYYY-MM-01\') )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * Gets the totals across all companies.
	 * @param int $status_id
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getTotalMonthlyMinAvgMaxByCompanyStatusAndStartDateAndEndDate( $status_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$cf = TTNew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

		$ph = [
				'status_id'  => (int)$status_id,
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select
							date_stamp,
							sum(min_active_users) as min_active_users,
							sum(avg_active_users) as avg_active_users,
							sum(max_active_users) as max_active_users,

							sum(min_inactive_users) as min_inactive_users,
							sum(avg_inactive_users) as avg_inactive_users,
							sum(max_inactive_users) as max_inactive_users,

							sum(min_deleted_users) as min_deleted_users,
							sum(avg_deleted_users) as avg_deleted_users,
							sum(max_deleted_users) as max_deleted_users
					FROM (
							select
									company_id,
									( to_char(a.date_stamp, \'YYYY-MM-01\') ) as date_stamp,
									min(a.active_users) as min_active_users,
									ceil(avg(a.active_users)) as avg_active_users,
									max(a.active_users) as max_active_users,

									min(a.inactive_users) as min_inactive_users,
									ceil(avg(a.inactive_users)) as avg_inactive_users,
									max(a.inactive_users) as max_inactive_users,

									min(a.deleted_users) as min_deleted_users,
									ceil(avg(a.deleted_users)) as avg_deleted_users,
									max(a.deleted_users) as max_deleted_users

							from	' . $this->getTable() . ' as a
								LEFT JOIN ' . $cf->getTable() . ' as cf ON ( a.company_id = cf.id )
							where
								cf.status_id = ?
								AND a.date_stamp >= ?
								AND a.date_stamp <= ?
								AND ( cf.deleted = 0 )
							GROUP BY company_id, ( to_char(a.date_stamp, \'YYYY-MM-01\') )
						) as tmp
					GROUP BY date_stamp
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getLastDateByCompanyId( $company_id, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
					ORDER BY date_stamp desc
					LIMIT 1
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyUserCountListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	id = ?
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}


}

?>
