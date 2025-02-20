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
 * @package Modules\Schedule
 * @implements IteratorAggregate<RecurringScheduleFactory>
 */
class RecurringScheduleListFactory extends RecurringScheduleFactory implements IteratorAggregate {

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
	 * @return bool|RecurringScheduleListFactory
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
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByCompanyID( $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.start_time' => 'asc', 'a.status_id' => 'desc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $uf->getTable() . ' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	 a.company_id = ?
						AND  a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id ) {
		return $this->getByCompanyIDAndId( $company_id, $id );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByCompanyIDAndId( $company_id, $id ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				'company_id2' => TTUUID::castUUID( $company_id ),
		];

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		//Always include the user_id, this is required for mass edit to function correctly and not assign schedules to OPEN employee all the time.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $uf->getTable() . ' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	( c.company_id = ? OR a.company_id = ? )
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					ORDER BY a.start_time asc, a.status_id desc
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $recurring_schedule_control_id UUID
	 * @param array $where                          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByRecurringScheduleControlID( $recurring_schedule_control_id, $where = null, $order = null ) {
		if ( $recurring_schedule_control_id == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.date_stamp' ];

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'recurring_schedule_control_id' => TTUUID::castUUID( $recurring_schedule_control_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.recurring_schedule_control_id = ?
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $recurring_schedule_control_id UUID
	 * @param array $where                          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @return bool|int
	 */
	function getMinimumStartTimeByRecurringScheduleControlID( $recurring_schedule_control_id, $where = null ) {
		if ( $recurring_schedule_control_id == '' ) {
			return false;
		}

		$order = null;
		$strict = true;

		$ph = [
				'recurring_schedule_control_id' => TTUUID::castUUID( $recurring_schedule_control_id ),
		];

		$query = '
					SELECT min(start_time)
					FROM (
						SELECT	a.user_id, max(start_time) as start_time
						FROM	' . $this->getTable() . ' as a
						WHERE	a.recurring_schedule_control_id = ?
							AND ( a.deleted = 0 )
						GROUP BY a.user_id
						) as tmp
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		$retval = $this->db->getOne( $query, $ph );
		if ( $retval != '' ) {
			$retval = TTDate::strtotime( $retval );
			Debug::Text( ' Minimum Start Time: ' . TTDate::getDate( 'DATE+TIME', $retval ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param array $where                                   Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                                   Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getOrphan( $where = null, $order = null ) {
		$additional_order_fields = [ 'a.date_stamp' ];

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$rscf = new RecurringScheduleControlFactory();

		$ph = [];

		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rscf->getTable() . ' as b ON ( a.recurring_schedule_control_id = b.id AND a.user_id = b.user_id )
					WHERE b.id IS NULL
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string|string[] $recurring_schedule_control_id UUID
	 * @param int $start_date                                EPOCH
	 * @param int $end_date                                  EPOCH
	 * @param array $where                                   Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                                   Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByRecurringScheduleControlIDAndStartDateAndEndDate( $recurring_schedule_control_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $recurring_schedule_control_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.date_stamp' ];

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
				//'recurring_schedule_control_id' => $recurring_schedule_control_id,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.recurring_schedule_control_id in (' . $this->getListSQL( $recurring_schedule_control_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $recurring_schedule_control_id UUID
	 * @param string $user_id                       UUID
	 * @param int $start_date                       EPOCH
	 * @param int $end_date                         EPOCH
	 * @param array $where                          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByRecurringScheduleControlIDAndUserIdAndStartDateAndEndDate( $recurring_schedule_control_id, $user_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $recurring_schedule_control_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.date_stamp' ];

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'start_date'                    => $this->db->BindDate( $start_date ),
				'end_date'                      => $this->db->BindDate( $end_date ),
				'recurring_schedule_control_id' => TTUUID::castUUID( $recurring_schedule_control_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.recurring_schedule_control_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 */
	function getByCompanyIDAndStartDateAndEndDateAndNoConflictingSchedule( $company_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'rsf.user_id' => 'asc', 'rsf.start_time' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();
		$spf = new SchedulePolicyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'start_date' => (int)$start_date,
				'end_date'   => (int)$end_date,
		];

		//
		// Base the start_date/end_date filter on start_time only.
		//
		$query = '

						SELECT
							NULL as id,
							rsf.id as schedule_id,
							rsf.company_id as company_id,

							rsf.user_id as user_id,
							rsf.recurring_schedule_control_id,
							rsf.date_stamp as date_stamp,
							NULL as pay_period_id,

							rsf.status_id as status_id,
							rsf.start_time as start_time,
							rsf.end_time as end_time,

							CASE WHEN rsf.branch_id = \'' . TTUUID::getNotExistID() . '\' THEN uf.default_branch_id ELSE rsf.branch_id END as branch_id,
							CASE WHEN rsf.department_id = \'' . TTUUID::getNotExistID() . '\' THEN uf.default_department_id ELSE rsf.department_id END as department_id,
							CASE WHEN rsf.job_id = \'' . TTUUID::getNotExistID() . '\' THEN uf.default_job_id ELSE rsf.job_id END as job_id,
							CASE WHEN rsf.job_item_id = \'' . TTUUID::getNotExistID() . '\' THEN uf.default_job_item_id ELSE rsf.job_item_id END as job_item_id,

							rsf.total_time as total_time,
							rsf.recurring_schedule_template_control_id,
							rsf.schedule_policy_id as schedule_policy_id,
							rsf.absence_policy_id as absence_policy_id,

							rsf.note as note,
							rsf.auto_fill as auto_fill,

							rsf.created_date as created_date,
							rsf.updated_date as updated_date,
							rsf.deleted as deleted
						FROM recurring_schedule as rsf
						LEFT JOIN ' . $uf->getTable() . ' as uf ON rsf.user_id = uf.id
						LEFT JOIN ' . $spf->getTable() . ' as spf ON rsf.schedule_policy_id = spf.id
						LEFT JOIN schedule as sf ON (
														( sf.user_id != \'' . TTUUID::getZeroID() . '\' AND sf.user_id = rsf.user_id )
														AND
														(
														sf.start_time >= rsf.start_time AND sf.end_time <= rsf.end_time
														OR
														sf.start_time >= rsf.start_time AND sf.start_time < rsf.end_time
														OR
														sf.end_time > rsf.start_time AND sf.end_time <= rsf.end_time
														OR
														sf.start_time <= rsf.start_time AND sf.end_time >= rsf.end_time
														OR
														sf.start_time = rsf.start_time AND sf.end_time = rsf.end_time
														)
														AND sf.deleted = 0
													)
						WHERE sf.id is NULL
							AND uf.company_id = ?
							AND ( ' . $this->getSQLToEpochFunction( 'rsf.start_time' ) . ' - ( CASE WHEN spf.start_stop_window > 0 THEN spf.start_stop_window ELSE 1 END ) ) >= ?
							AND ( ' . $this->getSQLToEpochFunction( 'rsf.start_time' ) . ' - ( CASE WHEN spf.start_stop_window > 0 THEN spf.start_stop_window ELSE 1 END ) ) <= ?
							AND ( rsf.deleted = 0 AND ( spf.id IS NULL OR spf.deleted = 0 ) )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param $company_id
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param string $id      UUID
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
	 * @throws DBError
	 */
	function getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $company_id, $user_id, $start_date, $end_date, $id = null, $where = null, $order = null ) {
		Debug::Text( 'User ID: ' . $user_id . ' Start Date: ' . $start_date . ' End Date: ' . $end_date, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $id == '' ) {
			$id = TTUUID::getZeroID(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$start_timestamp = $this->db->BindTimeStamp( $start_date );
		$end_timestamp = $this->db->BindTimeStamp( $end_date );

		$ph = [
				'company_id'   => TTUUID::castUUID( $company_id ),
				'user_id'      => TTUUID::castUUID( $user_id ),
				'start_date_a' => $this->db->BindDate( ( $start_date - 86400 ) ), //Need to expand the date_stamp restriction by at least a day to cover shifts that span midnight.
				'end_date_b'   => $this->db->BindDate( ( $end_date + 86400 ) ), //Need to expand the date_stamp restriction by at least a day to cover shifts that span midnight.
				'id'           => TTUUID::castUUID( $id ),
				'start_date1'  => $start_timestamp,
				'end_date1'    => $end_timestamp,
				'start_date2'  => $start_timestamp,
				'end_date2'    => $end_timestamp,
				'start_date3'  => $start_timestamp,
				'end_date3'    => $end_timestamp,
				'start_date4'  => $start_timestamp,
				'end_date4'    => $end_timestamp,
				'start_date5'  => $start_timestamp,
				'end_date5'    => $end_timestamp,
		];

		//Add filter on date_stamp for optimization
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where a.company_id = ?
						AND a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.id != ?
						AND
						(
							( a.start_time >= ? AND a.end_time <= ? )
							OR
							( a.start_time >= ? AND a.start_time < ? )
							OR
							( a.end_time > ? AND a.end_time <= ? )
							OR
							( a.start_time <= ? AND a.end_time >= ? )
							OR
							( a.start_time = ? AND a.end_time = ? )
						)
						AND ( a.deleted = 0 )
					ORDER BY start_time';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleListFactory
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

		$additional_order_fields = [ 'schedule_policy_id', 'schedule_policy', 'absence_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', 'j.name', 'k.name' ];

		$sort_column_aliases = [
				'status'       => 'a.status_id',
				'first_name'   => 'd.first_name',
				'last_name'    => 'd.last_name',
				'user_status'  => 'user_status_id',
				'branch'       => 'j.name',
				'department'   => 'k.name',
				'updated_date' => 'a.updated_date',
				'created_date' => 'a.created_date',
				'created_by'   => 'a.created_by',
				'updated_by'   => 'a.updated_by',
		];

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) { //Needed for unit tests to pass when doing pure edition tests.
			$additional_order_fields = array_merge( [ 'w.name', 'x.name' ], $additional_order_fields );

			$sort_column_aliases = array_merge( [
														'job'      => 'w.name',
														'job_item' => 'x.name',
												], $sort_column_aliases );
		}

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'a.user_id' => 'asc', 'a.start_time' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['exclude_user_ids'] ) ) {
			$filter_data['exclude_user_id'] = $filter_data['exclude_user_ids'];
		}

		if ( isset( $filter_data['include_user_ids'] ) ) {
			$filter_data['user_id'] = $filter_data['include_user_ids'];
		}
		if ( isset( $filter_data['user_status_ids'] ) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset( $filter_data['user_title_ids'] ) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset( $filter_data['group_ids'] ) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset( $filter_data['default_branch_ids'] ) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset( $filter_data['default_department_ids'] ) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset( $filter_data['branch_ids'] ) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset( $filter_data['department_ids'] ) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset( $filter_data['include_job_ids'] ) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset( $filter_data['job_group_ids'] ) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset( $filter_data['job_item_ids'] ) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}
		if ( isset( $filter_data['pay_period_ids'] ) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset( $filter_data['start_time'] ) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset( $filter_data['end_time'] ) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				'company_id2' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.note as note,

							i.name as schedule_policy,
							apf.name as absence_policy,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							NULL as pay_period_id,

							d.first_name as first_name,
							d.last_name as last_name,
							d.status_id as user_status_id,
							d.group_id as group_id,
							g.name as "group",
							d.title_id as title_id,
							h.name as title,
							d.default_branch_id as default_branch_id,
							e.name as default_branch,
							d.default_department_id as default_department_id,
							f.name as default_department,
							d.created_by as user_created_by,

							m.id as user_wage_id,
							m.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						w.name as job,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id,

						x.name as job_item,
						x.manual_id as job_item_manual_id,
						x.group_id as job_item_group_id
						';
		}

		$query .= '
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $spf->getTable() . ' as i ON a.schedule_policy_id = i.id
							LEFT JOIN ' . $uf->getTable() . ' as d ON ( a.user_id = d.id AND d.deleted = 0 )

							LEFT JOIN ' . $bf->getTable() . ' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN ' . $ugf->getTable() . ' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN ' . $utf->getTable() . ' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN ' . $bf->getTable() . ' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN ' . $apf->getTable() . ' as apf ON a.absence_policy_id = apf.id

							LEFT JOIN ' . $uwf->getTable() . ' as m ON m.id = (select m.id
																		from ' . $uwf->getTable() . ' as m
																		where m.user_id = a.user_id
																			and m.effective_date <= a.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN ' . $jf->getTable() . ' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN ' . $jif->getTable() . ' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE ( d.company_id = ? OR a.company_id = ? ) ';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'd.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['branch_id'] ) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['department_id'] ) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['absence_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'uuid_list', $ph ) : null;

		//$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : NULL;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset( $filter_data['include_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['exclude_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_group_id'] ) ) ? $this->getWhereClauseSQL( 'w.group_id', $filter_data['job_group_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;
		}

		$query .= ( isset( $filter_data['date_stamp'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date_stamp'], 'date_range_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['start_date'], 'start_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['end_date'], 'end_datestamp', $ph ) : null;

		$query .= ( isset( $filter_data['start_time'] ) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['start_time'], 'start_timestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_time'] ) ) ? $this->getWhereClauseSQL( 'a.end_time', $filter_data['end_time'], 'end_timestamp', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND ( a.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}

?>
