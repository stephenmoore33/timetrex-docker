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
 * @implements IteratorAggregate<StationFactory>
 */
class StationListFactory extends StationFactory implements IteratorAggregate {

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
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getByCompanyId( $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$additional_order_fields = [ 'created_date', 'updated_date', 'updated_date_null' ];
		if ( $order == null ) {
			$order = [ 'a.type_id' => 'asc', 'a.status_id' => 'asc', 'updated_date_null' => 'asc', 'updated_date' => 'desc', 'a.created_date' => 'desc' ];
			$strict = false;
		} else {
			//Always sort by created/updated date last.
			if ( !isset( $order['update_date'] ) ) {
				$order['updated_date'] = 'desc';
			}
			if ( !isset( $order['created_date'] ) ) {
				$order['created_date'] = 'desc';
			}
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	a.*,
							CASE WHEN ( a.updated_date is NULL) THEN TRUE ELSE FALSE END as updated_date_null
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		//Because of the null updated date, we have to manually sort.
		if ( $order == null ) {
			//$order = array( 'type_id' => 'asc', 'status_id' => 'asc', 'updated_date_null' => 'asc', 'updated_date' => 'desc', 'created_date' => 'desc' );
			$query .= 'ORDER BY a.type_id asc, a.status_id asc, updated_date_null asc, updated_date desc, a.created_date desc';
		} else {
			$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );
		}

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
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
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int|int[] $type_id
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getByCompanyIdAndTypeId( $company_id, $type_id, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}


	/**
	 * @param string $station_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getByStationId( $station_id, $order = null ) {
		if ( $station_id == '' || strtolower( $station_id ) == 'any' ) {
			return false;
		}

		$this->rs = $this->getCache( $station_id );
		if ( $this->rs === false ) {
			if ( $order == null ) {
				$order = [ 'created_date' => 'asc' ]; //Order oldest station first incase conflicting stations get created the newest one doesn't cause problems for existing users.
				$strict = false;
			} else {
				$strict = true;
			}

			$ph = [
					'station_id' => (string)$station_id,
			];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where
							station_id = ?
							AND deleted = 0';
			$query .= $this->getSortSQL( $order, $strict );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $station_id );
		}

		return $this;
	}

	/**
	 * @param string $station_id UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getByStationIdAndCompanyId( $station_id, $company_id, $order = null ) {
		if ( $station_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'created_date' => 'asc' ]; //Order oldest station first incase conflicting stations get created the newest one doesn't cause problems for existing users.
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'station_id' => (string)$station_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	station_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $station_id UUID
	 * @param int|array $status_id
	 * @param int|array $type_id
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getByStationIdAndStatusIdAndTypeId( $station_id, $status_id, $type_id, $order = null ) {
		if ( $station_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [
				'station_id' => (string)$station_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	station_id = ?
						AND status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int|int[] $type_id
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getPendingSynchronizationByCompanyIdAndTypeId( $company_id, $type_id, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND status_id = 20
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND	(
								( last_poll_date is NULL OR last_poll_date < (' . time() . ' - poll_frequency) )
								OR
								( last_push_date is NULL OR last_push_date < (' . time() . ' - push_frequency) )
								OR
								( last_partial_push_date is NULL OR last_partial_push_date < (' . time() . ' - partial_push_frequency) )
							)
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param $status
	 * @param $type
	 * @param null $station_id
	 * @return bool|StationListFactory
	 * @throws DBError
	 */
	function getByUserIdAndStatusAndType( $user_id, $status, $type, $station_id = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		if ( $type == '' ) {
			return false;
		}

		$ulf = new UserListFactory();
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() != 1 ) {
			Debug::text( 'User ID does not exist: ' . $user_id . ' returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();
		$uf = new UserFactory();

		$ph = [
				'user_id_a'  => $user_id,
				'company_id' => $ulf->getCurrent()->getCompany(),
				'status'     => $status,
				'type'       => $type,
		];
		/*
				$query = '
							select	a.*
							from	'. $this->getTable() .' as a
								LEFT JOIN '. $uf->getTable() .' as z ON z.id = ?
							where a.company_id = ?
								AND a.status_id = ?
								AND a.type_id = ?
								AND
									(
										(
											(
												a.user_group_selection_type_id = 10
													OR ( a.user_group_selection_type_id = 20 AND z.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE a.id = b.station_id ) )
													OR ( a.user_group_selection_type_id = 30 AND z.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE a.id = b.station_id ) )
											)
											AND
											(
												a.branch_selection_type_id = 10
													OR ( a.branch_selection_type_id = 20 AND z.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE a.id = c.station_id ) )
													OR ( a.branch_selection_type_id = 30 AND z.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE a.id = c.station_id ) )
											)
											AND
											(
												a.department_selection_type_id = 10
													OR ( a.department_selection_type_id = 20 AND z.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE a.id = d.station_id ) )
													OR ( a.department_selection_type_id = 30 AND z.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE a.id = d.station_id ) )
											)
											AND z.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE a.id = f.station_id )
										)
										OR z.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE a.id = e.station_id )
									)
								AND ( a.deleted = 0 AND z.deleted = 0 )
								ORDER BY lower(a.source) = \'any\' desc, lower(station_id) = \'any\' desc
								';
		*/
		//Optimize query by using EXISTS/NOT EXISTS rather than IN/NOT IN. This cuts the time by about 1/3.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as z ON z.id = ?
					where a.company_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND
							(
								(
									(
										a.user_group_selection_type_id = 10
											OR ( a.user_group_selection_type_id = 20 AND EXISTS( select b.group_id from ' . $sugf->getTable() . ' as b WHERE a.id = b.station_id AND b.group_id = z.group_id ) )
											OR ( a.user_group_selection_type_id = 30 AND NOT EXISTS( select b.group_id from ' . $sugf->getTable() . ' as b WHERE a.id = b.station_id AND b.group_id = z.group_id ) )
									)
									AND
									(
										a.branch_selection_type_id = 10
											OR ( a.branch_selection_type_id = 20 AND EXISTS( select c.branch_id from ' . $sbf->getTable() . ' as c WHERE a.id = c.station_id AND c.branch_id = z.default_branch_id ) )
											OR ( a.branch_selection_type_id = 30 AND NOT EXISTS( select c.branch_id from ' . $sbf->getTable() . ' as c WHERE a.id = c.station_id AND c.branch_id = z.default_branch_id  ) )
									)
									AND
									(
										a.department_selection_type_id = 10
											OR ( a.department_selection_type_id = 20 AND EXISTS( select d.department_id from ' . $sdf->getTable() . ' as d WHERE a.id = d.station_id AND d.department_id = z.default_department_id ) )
											OR ( a.department_selection_type_id = 30 AND NOT EXISTS( select d.department_id from ' . $sdf->getTable() . ' as d WHERE a.id = d.station_id AND d.department_id = z.default_department_id ) )
									)
									AND NOT EXISTS( select f.user_id from ' . $seuf->getTable() . ' as f WHERE a.id = f.station_id AND f.user_id = z.id )
								)
								OR EXISTS( select e.user_id from ' . $siuf->getTable() . ' as e WHERE a.id = e.station_id AND e.user_id = z.id )
							)
						AND ( a.deleted = 0 AND z.deleted = 0 ) ';

		$query .= ' ORDER BY lower(a.source) = \'any\' desc, lower(station_id) = \'any\' desc'; //Try to order the SQL query to hit wildcard stations first.
		if ( $station_id != '' ) {
			$query .= ', station_id = \'' . preg_replace( '/[^A-Za-z0-9\.]/', '', $station_id ) . '\' desc'; //Try to order by the specific station_id next
		}
		$query .= ', last_punch_time_stamp desc'; //Finally order by stations that was used most recently.

		//$query .= $this->getSortSQL( $order, $strict );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @return array|bool
	 */
	function getByCompanyIdArray( $company_id ) {
		if ( $company_id == '' ) {
			return false;
		}

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $company_id );

		$branch_list = [];
		$branch_list[TTUUID::getZeroID()] = '--';

		foreach ( $blf as $branch ) {
			$branch_list[$branch->getID()] = $branch->getName();
		}

		return $branch_list;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getCountByCompanyIdAndTypeId( $company_id, $type_id, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [//'company_id' => TTUUID::castUUID($company_id),
		];

		//Only include ENABLED stations.
		$query = '
					select	company_id,
							type_id,
							count(*) as total
					from	' . $this->getTable() . '
					where
							status_id = 20
							AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ') ';

		$query .= ( isset( $company_id ) && $company_id != '' ) ? $this->getWhereClauseSQL( 'company_id', $company_id, 'uuid_list', $ph ) : null;

		$query .= ' AND deleted = 0 GROUP BY company_id, type_id';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $filter_data
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAPITimeClockStationsByArrayCriteria( $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [];

		$sort_column_aliases = [
				'type'   => 'type_id',
				'status' => 'status_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'type_id' => 'asc', 'source' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['status_id'] ) ) {
				$order = Misc::prependArray( [ 'status_id' => 'asc' ], $order );
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();

		$ph = [];

		$query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where 1=1
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['type'] ) && !is_array( $filter_data['type'] ) && trim( $filter_data['type'] ) != '' && !isset( $filter_data['type_id'] ) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions( 'type' ) );
		}
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['station_id'] ) ) ? $this->getWhereClauseSQL( 'a.station_id', $filter_data['station_id'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['source'] ) ) ? $this->getWhereClauseSQL( 'a.source', $filter_data['source'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['description'] ) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|StationListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [];

		$sort_column_aliases = [
				'type'   => 'type_id',
				'status' => 'status_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'type_id' => 'asc', 'source' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['status_id'] ) ) {
				$order = Misc::prependArray( [ 'status_id' => 'asc' ], $order );
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['type'] ) && !is_array( $filter_data['type'] ) && trim( $filter_data['type'] ) != '' && !isset( $filter_data['type_id'] ) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions( 'type' ) );
		}
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['station_id'] ) ) ? $this->getWhereClauseSQL( 'a.station_id', $filter_data['station_id'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['source'] ) ) ? $this->getWhereClauseSQL( 'a.source', $filter_data['source'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['description'] ) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}

?>
