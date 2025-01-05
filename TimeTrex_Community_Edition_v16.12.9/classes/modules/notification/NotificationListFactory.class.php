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
 * @package Modules\Notification
 * @implements IteratorAggregate<NotificationFactory>
 */
class NotificationListFactory extends NotificationFactory implements IteratorAggregate {

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
	 * @return bool|NotificationListFactory
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
	 * @param string $id         UUID
	 * @param string $user_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByIdAndUserId( $id, $user_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
				'id'      => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND	id = ?
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserIdAndStatus( $company_id, $id, $status, $where = null, $order = null ) {
		// 10 unread, 20 read
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
				'status_id'  => (int)$status,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	a.status_id = ?
						AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserIdAndSentStatus( $company_id, $id, $sent_status, $where = null, $order = null ) {
		// 10=Pending, 50=Fail 100 = success
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $sent_status == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         	  => TTUUID::castUUID( $id ),
				'company_id' 	  => TTUUID::castUUID( $company_id ),
				'sent_status_id'  => (int)$sent_status,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	a.sent_status_id = ?
						AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getBySentStatus( $sent_status, $where = null, $order = null ) {
		// 10=Pending, 50=Fail 100 = success
		if ( $sent_status == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'sent_status_id'  => (int)$sent_status,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.sent_status_id = ?
						AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserIdAndType( $company_id, $id, $type, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $type == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
				'type_id'  => (int)$type,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	a.type_id = ?
						AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserIdAndDate( $company_id, $id, $date = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			$date = 0;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id )
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						';

					if ( isset( $date ) && $date > 0 ) {
						//Append the same date twice for created and updated.
						$ph[] = $date;
						$ph[] = $date;
						$query .= ' AND ( a.created_date >= ? OR a.updated_date >= ? )';
					}

		$query .= ' AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getUnreadCountByUserIdAndCompanyId($user_id, $company_id, $where = null) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'user_id'      => TTUUID::castUUID( $user_id ),
				'company_id'   => TTUUID::castUUID( $company_id ),
				'current_time' => $this->db->bindTimeStamp( TTDate::getTime() ),
		];

		//Be sure to ignore notifications in the future.
		$query = '
					select	COUNT(*)
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND a.status_id = 10
						AND	a.user_id = ?
						AND b.company_id = ?
						AND (a.effective_date <= ?) 
						AND (a.deleted = 0 AND b.deleted = 0)';

		$query .= $this->getWhereSQL( $where );

		$total = $this->db->GetOne( $query, $ph );

		return $total;
	}


	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByUserId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $company_id
	 * @return $this|false
	 */
	function getPending( $epoch = null ) {
		if ( empty( $epoch ) ) {
			$epoch = time();
		}

		$uf = new UserFactory();
		$cf = new CompanyFactory();

		$ph = [
				'epoch' => $this->db->bindTimeStamp( (int)$epoch ),
		];

		//Make sure we only return pending notifications for active companies, and active users.
		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON a.user_id = b.id
						LEFT JOIN ' . $cf->getTable() . ' as c ON b.company_id = c.id
					WHERE
							a.sent_status_id = 10							
							AND a.status_id = 10
							AND ( a.effective_date < ? )
							AND b.status_id = 10
							AND c.status_id = 10							 
							AND a.deleted = 0
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}


	/**
	 * @param $user_ids
	 * @param $type_id
	 * @param null $epoch
	 * @param null $object_id
	 * @return $this|false
	 */
	function getPendingByUserIdsAndTypeIdAndObjectId( $user_ids, $type_id, $epoch = null, $object_id = null ) {
		if ( $user_ids == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [];

		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
					WHERE
							a.user_id in (' . $this->getListSQL( $user_ids, $ph, 'uuid' ) . ')
							AND a.type_id in (' . $this->getListSQL( $type_id, $ph, 'string' ) . ')
							AND a.sent_status_id = 10
							AND a.status_id = 10						
					';

		$query .= ( isset( $object_id ) && TTUUID::isUUID( $object_id ) ) ? $this->getWhereClauseSQL( 'a.object_id', $object_id, 'uuid_list', $ph ) : null;
		if (  isset( $epoch ) && !empty( $epoch ) && is_numeric( $epoch ) ) {
			$query .= ' AND ( a.effective_date >= ' . $this->db->qstr( $this->db->bindTimeStamp( (int)$epoch ) ) . ' )  ';
		}

		$query .= ' AND a.deleted = 0';
		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param $object_id
	 * @param $type_id
	 * @param null $epoch
	 * @return $this|false
	 */
	function getPendingByObjectIdAndTypeId( $object_id, $type_id, $epoch = null ) {
		if ( $object_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$ph = [];

		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
					WHERE
							a.status_id = 10
							AND a.sent_status_id = 10																		
							AND a.type_id in (' . $this->getListSQL( $type_id, $ph, 'string' ) . ')
					';

		$query .= ( isset( $object_id ) && TTUUID::isUUID( $object_id ) ) ? $this->getWhereClauseSQL( 'a.object_id', $object_id, 'uuid_list', $ph ) : null;
		if (  isset( $epoch ) && !empty( $epoch ) && is_numeric( $epoch ) ) {
			$query .= ' AND ( a.effective_date >= ' . $this->db->qstr( $this->db->bindTimeStamp( (int)$epoch ) ) . ' )  ';
		}

		$query .= ' AND a.deleted = 0';
		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param $user_id
	 * @param $object_id
	 * @param $created_before
	 * @return $this|false
	 */
	function getRecentSystemNotificationByUserIdAndObjectAndCreatedBefore( $user_id, $object_id, $created_before ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $object_id == '' ) {
			return false;
		}

		$ph = [
				'user_id'        => TTUUID::castUUID( $user_id ),
				'object_id'      => TTUUID::castUUID( $object_id ),
				'created_before' => (int)$created_before,
		];

		//Do we ignore deleted notifications or not?
		// If the user deletes one, then another one could appear immediately, but if they just mark it as read then it wouldn't.
		$query = '
					SELECT *
					FROM ' . $this->getTable() . '
					WHERE
							user_id = ?
							AND object_id = ?
							AND created_date > ?
							AND deleted = 0
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $status_id
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserIdAndIdAndStatus( $company_id, $user_id, $id, $status_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
				'status_id'  => (int)$status_id,
		];

		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON a.user_id = b.id
					WHERE
							b.company_id = ?
							AND a.user_id = ?
							AND a.status_id = ?
							AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
							AND a.deleted = 0
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getByCompanyIdAndUserID( $company_id, $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND (a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|NotificationListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( !isset( $filter_data['current_user_id'] ) ) {
			return false;
		}

		if ( !isset( $filter_data['effective_date'] ) ) {
			$filter_data['effective_date'] = TTDate::getTime(); //Always hide notifications that are future dated.
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'type_id' ];

		$sort_column_aliases = [
				'notification_type'        => 'a.type_id',
				'type'                     => 'a.type_id',
				'sent_status'         	   => 'a.sent_status_id',
				'status'                   => 'a.status_id',
				'priority'                 => 'a.priority_id',
				'acknowledged_status'      => 'a.acknowledged_status_id',
				'created_date'			   => 'a.created_date',
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'a.effective_date' => 'desc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();

		$ph = [
				'user_id'    => $filter_data['current_user_id'],
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
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where a.user_id = ?	
						  AND b.company_id = ?';

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['object_type_id'] ) ) ? $this->getWhereClauseSQL( 'a.object_type_id', $filter_data['object_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['object_id'] ) ) ? $this->getWhereClauseSQL( 'a.object_id', $filter_data['object_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['title_short'] ) ) ? $this->getWhereClauseSQL( 'a.title_short', $filter_data['title_short'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['title_long'] ) ) ? $this->getWhereClauseSQL( 'a.title_long', $filter_data['title_long'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['sent_status_id'] ) ) ? $this->getWhereClauseSQL( 'a.sent_status_id', $filter_data['sent_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['sent_device_id'] ) ) ? $this->getWhereClauseSQL( 'a.sent_device_id', $filter_data['sent_device_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'text_list', $ph ) : null;

		$query .= ( isset( $filter_data['acknowledged_status_id'] ) ) ? $this->getWhereClauseSQL( 'a.acknowledged_status_id', $filter_data['acknowledged_status_id'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['effective_date'] ) && !is_array( $filter_data['effective_date'] ) && trim( $filter_data['effective_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)TTDate::parseDateTime( $filter_data['effective_date'] ) );
			$query .= ' AND a.effective_date <= ?';
		}

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}