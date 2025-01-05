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
 * @package Modules\Policy
 * @implements IteratorAggregate<CompanyGenericMapFactory>
 */
class CompanyGenericMapListFactory extends CompanyGenericMapFactory implements IteratorAggregate {

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
	 * @return bool|CompanyGenericMapListFactory
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
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyId( $id, $where = null, $order = null ) {
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
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectType( $company_id, $id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.object_type_id in (' . $this->getListSQL( $id, $ph, 'int' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $date          EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getIsModifiedByCompanyIdAndObjectTypeIdAndDate( $company_id, $object_type_id, $date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		$ph = [
				'company_id'   => TTUUID::castUUID( $company_id ),
				'created_date' => $date,
				'updated_date' => $date,
				'deleted_date' => $date,
		];

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	' . $this->getTable() . '
					where company_id = ?
						AND ( created_date >=  ? OR updated_date >= ? OR ( deleted = 1 AND deleted_date >= ? ) )
						AND object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')						
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text( 'Rows have been modified: ' . $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}
		Debug::text( 'Rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id     UUID
	 * @param string|array $object_type_id UUID
	 * @param array $where           Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order           Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectTypeIdAndDateAndValidIDs( $company_id, $object_type_id, $date = null, $valid_ids = [], $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = 0;
		}

		if ( $order == null ) {
			$order = [ 'a.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
						AND a.deleted = 0
						AND ( 
							1=1
					';

		if ( isset( $date ) && $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = $date;
			$ph[] = $date;
			$query .= ' AND ( a.created_date >= ? OR a.updated_date >= ? )';
		}

		if ( isset( $valid_ids ) && is_array( $valid_ids ) && count( $valid_ids ) > 0 ) {
			$query .= ' OR a.id in (' . $this->getListSQL( $valid_ids, $ph, 'uuid' ) . ') ';
		}

		$query .= ' ) ';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $object_id  UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $object_type_id == '' ) {
			return false;
		}

		if ( $object_id == '' ) {
			return false;
		}

		$cache_id = md5( $company_id . serialize( $object_type_id ) . serialize( $object_id ) );
		//Debug::Text('Cache ID: '. $cache_id .' Company ID: '. $company_id .' Object Type: '. $object_type_id .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->getCache( $cache_id );
		if ( $this->rs === false ) {
			$ph = [
					'company_id' => TTUUID::castUUID( $company_id ),
			];

			$query = '
						select	a.*
						from	' . $this->getTable() . ' as a
						where	a.company_id = ?
							AND a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
							AND a.object_id in (' . $this->getListSQL( $object_id, $ph, 'uuid' ) . ') ';

			//Schema 1126A adds 'deleted' column to this table. So when upgrading earlier schema versions we can't use in the WHERE clause.
			global $COMPANY_GENERIC_MAP_DELETED_COLUMN;
			if ( $COMPANY_GENERIC_MAP_DELETED_COLUMN == true ) {
				$query .= ' AND a.deleted = 0';
			}

			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $cache_id );
		}

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int|int[] $object_type_id
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectTypeAndMapID( $company_id, $object_type_id, $id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $object_type_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
						AND a.map_id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $id         UUID
	 * @param string $map_id     UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectTypeAndObjectIDAndMapID( $company_id, $object_type_id, $id, $map_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $object_type_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
						AND a.object_id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND a.map_id in (' . $this->getListSQL( $map_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $id         UUID
	 * @param string $map_id     UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByCompanyIDAndObjectTypeAndObjectIDAndNotMapID( $company_id, $object_type_id, $id, $map_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $object_type_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.company_id = ?
						AND a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
						AND a.object_id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND a.map_id not in (' . $this->getListSQL( $map_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByObjectType( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.object_type_id in (' . $this->getListSQL( $id, $ph, 'int' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param int $object_type_id
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyGenericMapListFactory
	 */
	function getByObjectTypeAndObjectID( $object_type_id, $id, $where = null, $order = null ) {
		if ( $object_type_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$ph = [];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.object_type_id in (' . $this->getListSQL( $object_type_id, $ph, 'int' ) . ')
						AND a.object_id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $lf
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf ) {
		if ( !is_object( $lf ) ) {
			return false;
		}
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getMapId();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $object_id  UUID
	 * @return array|bool
	 */
	static function getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $object_id ) {
		$cgmlf = new CompanyGenericMapListFactory();

		return $cgmlf->getArrayByListFactory( $cgmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id ) );
	}
}

?>