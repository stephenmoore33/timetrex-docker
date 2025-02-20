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
 * @implements IteratorAggregate<RecurringScheduleTemplateFactory>
 */
class RecurringScheduleTemplateListFactory extends RecurringScheduleTemplateFactory implements IteratorAggregate {

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
	 * @return bool|RecurringScheduleTemplateListFactory
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
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleTemplateListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					where	b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleTemplateListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		// recurring_schedule_template_control_created_by is needed to pass back the creator of the recurring schedule template control record, as that is the value that is changed when ownership is passed to another supervisor.
		$query = '
					select	a.*,
							b.created_by as recurring_schedule_template_control_created_by
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					where	b.company_id = ?
						AND a.id = ?
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
	 * @return bool|RecurringScheduleTemplateListFactory
	 */
	function getByRecurringScheduleTemplateControlId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	*
					from	' . $this->getTable() . '
					where	recurring_schedule_template_control_id = ?
						AND deleted = 0
					ORDER BY week asc';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $recurring_schedule_control_id UUID
	 * @param int $start_date                       EPOCH
	 * @param int $end_date                         EPOCH
	 * @param int $limit                            Limit the number of records returned
	 * @param int $page                             Page number of records to return for pagination
	 * @param array $where                          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleTemplateListFactory
	 */
	function getByRecurringScheduleControlIdAndStartDateAndEndDate( $recurring_schedule_control_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $recurring_schedule_control_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$additional_order_fields = [ 'name', 'description', 'last_name', 'start_date', 'user_id' ];
		if ( $order == null ) {
			$order = [ 'c.start_date' => 'asc', 'c.user_id' => 'desc', 'a.week' => 'asc', 'a.sun' => 'desc', 'a.mon' => 'desc', 'a.tue' => 'desc', 'a.wed' => 'desc', 'a.thu' => 'desc', 'a.fri' => 'desc', 'a.sat' => 'desc', 'a.start_time' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		//Debug::Arr($order, 'bOrder Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$rscf = new RecurringScheduleControlFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$pguf = new PolicyGroupUserFactory();
		$filter_data = [];

		$ph = [
				'recurring_schedule_control_id' => TTUUID::castUUID( $recurring_schedule_control_id ),
		];

		$query = '
					SELECT	a.*,
							c.user_id as user_id,

							c.start_date as recurring_schedule_control_start_date,
							c.end_date as recurring_schedule_control_end_date,
							c.start_week as recurring_schedule_control_start_week,
							zz.max_week as max_week,
							( ( ( ( a.week - 1 ) + zz.max_week - ( c.start_week - 1 ) ) % zz.max_week ) + 1 ) as remapped_week,

							d.created_by as user_created_by,
							d.hire_date as hire_date,
							d.termination_date as termination_date,

							pguf.policy_group_id as policy_group_id,
							
							ppsf.shift_assigned_day_id as shift_assigned_day_id,
							c.created_by as recurring_schedule_control_created_by
							';

		$query .= '
					FROM	' . $this->getTable() . ' as a
						LEFT JOIN ( SELECT z.recurring_schedule_template_control_id, max(z.week) as max_week FROM recurring_schedule_template as z WHERE deleted = 0 GROUP BY z.recurring_schedule_template_control_id ) as zz ON a.recurring_schedule_template_control_id = zz.recurring_schedule_template_control_id
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
						LEFT JOIN ' . $rscf->getTable() . ' as c ON a.recurring_schedule_template_control_id = c.recurring_schedule_template_control_id
						LEFT JOIN ' . $uf->getTable() . ' as d ON c.user_id = d.id

						LEFT JOIN ' . $ppsuf->getTable() . ' as ppsuf ON d.id = ppsuf.user_id
						LEFT JOIN ' . $ppsf->getTable() . ' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )
						
						LEFT JOIN ' . $pguf->getTable() . ' as pguf ON ( c.user_id = pguf.user_id )						
						';

		$query .= ' WHERE c.id = ? ';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'c.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'c.user_id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['recurring_schedule_template_control_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_template_control_id', $filter_data['recurring_schedule_template_control_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		if ( isset( $start_date ) && trim( $start_date ) != ''
				&& isset( $end_date ) && trim( $end_date ) != '' ) {
			$start_date_stamp = $this->db->BindDate( $start_date );
			$end_date_stamp = $this->db->BindDate( $end_date );

			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;

			$ph[] = $this->db->BindDate( $end_date );
			$ph[] = $this->db->BindDate( $start_date );

			$query .= ' AND (
								(c.start_date >= ? AND c.start_date <= ? AND c.end_date IS NULL )
								OR
								(c.start_date <= ? AND c.end_date IS NULL )
								OR
								(c.start_date <= ? AND c.end_date >= ? )
								OR
								(c.start_date >= ? AND c.end_date <= ? )
								OR
								(c.start_date >= ? AND c.start_date <= ? )
								OR
								(c.end_date >= ? AND c.end_date <= ? )
								OR
								(c.start_date <= ? AND c.end_date >= ? )
							)
							AND
							(
								( d.hire_date is NULL OR d.hire_date <= ? )
								AND
								( d.termination_date is NULL OR d.termination_date >= ? )
							)
						';
		}

		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND ( ppsf.deleted IS NULL OR ppsf.deleted = 0 ) AND ( d.deleted is NULL OR d.deleted = 0 ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
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
	 * @return bool|RecurringScheduleTemplateListFactory
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

		$additional_order_fields = [ 'in_use' ];

		$sort_column_aliases = [
				'status'          => 'status_id',
				'schedule_policy' => 'schedule_policy_id',
				'branch'          => false,
				'department'      => false,
				'job'             => false,
				'job_item'        => false,
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'week' => 'asc', 'sun' => 'desc', 'mon' => 'desc', 'tue' => 'desc', 'wed' => 'desc', 'thu' => 'desc', 'fri' => 'desc', 'sat' => 'desc', 'start_time' => 'asc', 'end_time' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset( $order['week'] ) ) {
				$order['week'] = 'asc';
			}
			if ( !isset( $order['start_time'] ) ) {
				$order['start_time'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$rstcf = new RecurringScheduleTemplateControlFactory();
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
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		//permission_children_ids needs to check the recurring_schedule_template_control_id.created_by, otherwise when a recurring schedule template control record is switched to owned by someone else, they won't see the actual shifts (recurring schedule template records).
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'b.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['recurring_schedule_template_control_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_template_control_id', $filter_data['recurring_schedule_template_control_id'], 'uuid_list', $ph ) : null;

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
