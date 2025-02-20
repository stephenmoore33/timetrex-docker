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
 * @implements IteratorAggregate<LogFactory>
 */
class LogListFactory extends LogFactory implements IteratorAggregate {

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
	 * @return bool|LogListFactory
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
					';
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
	 * @return bool|LogListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
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
					from	' . $this->getTable() . ' as a
						LEFT JOIN  ' . $uf->getTable() . ' as b on a.user_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND ( b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|LogListFactory
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
					from	' . $this->getTable() . ' as a
						LEFT JOIN  ' . $uf->getTable() . ' as b on a.user_id = b.id
					where	b.company_id = ?
						AND ( b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param $action
	 * @param $table_name
	 * @return bool|LogListFactory
	 */
	function getLastEntryByUserIdAndActionAndTable( $user_id, $action, $table_name ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $action == '' ) {
			return false;
		}

		if ( $table_name == '' ) {
			return false;
		}

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'table_name' => $table_name,
				'action_id'  => $action,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND table_name = ?
						AND action_id = ?
					ORDER BY date desc
					LIMIT 1
					';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @return bool|LogListFactory
	 */
	function getByPhonePunchDataByCompanyIdAndStartDateAndEndDate( $company_id, $start_date, $end_date ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
			//'company_id' => TTUUID::castUUID($company_id),
			'start_date' => $start_date,
			'end_date'   => $end_date,
		];

		$query = 'select	m.*,
							CASE WHEN m.calls > m.minutes THEN m.calls ELSE m.minutes END as billable_units
							from (
								select	company_id,
										product,
										sum(seconds)/60 as minutes,
										count(*) as calls,
										count(distinct(user_id)) as unique_users
								from
										(	select	company_id,
													user_id,
													CASE WHEN seconds < 60 THEN 60 ELSE seconds END as seconds,
													product from
													(	select	a.id,
																b.company_id,
																a.user_id,
																a.description,
																array_to_string( regexp_matches(a.description, \'([0-9]{1,3})s$\',\'i\'),\'\')::int as seconds,
																CASE WHEN ( a.description ~* \'Destination: (8(00|44|55|66|77|88)[2-9]\d{6})\' ) THEN \'tollfree\' ELSE \'local\' END as product
														from system_log as a
															LEFT JOIN users as b ON a.user_id = b.id
														where a.table_name = \'punch\'
															AND ( a.description ILIKE \'Telephone Punch End%\' )
															AND (a.date >= ? AND a.date < ? ) ';

		$query .= ( isset( $company_id ) && $company_id != '' ) ? $this->getWhereClauseSQL( 'company_id', $company_id, 'uuid_list', $ph ) : null;

		$query .= '									) as tmp
										) as tmp2
								group by company_id, product ) as m
							LEFT JOIN company as n ON m.company_id = n.id
							order by product, name;
					';

		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );
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
	 * @return bool|LogListFactory
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

		$additional_order_fields = [ 'action_id', 'object_id', 'last_name', 'first_name' ];

		$sort_column_aliases = [
				'action' => 'action_id',
				'object' => 'table_name',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'date' => 'desc', 'id' => 'desc', 'table_name' => 'asc', 'object_id' => 'asc' ]; //Order by ID after date, so the multiple actions in the same second still get ordered correctly.
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['date'] ) ) {
				$order['date'] = 'desc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['user_ids'] ) ) {
			$filter_data['user_id'] = $filter_data['user_ids'];
		}
		if ( isset( $filter_data['log_action_id'] ) ) {
			$filter_data['action_id'] = $filter_data['log_action_id'];
		}
		if ( isset( $filter_data['log_table_name_id'] ) ) {
			$filter_data['table_name'] = $filter_data['log_table_name_id'];
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//
		//**NOTE: Any changes here should likely be made to getAPISearchWithDetailsByCompanyIdAndArrayCriteria() as well.
		//
		$query = '
					select	a.*,
							uf.first_name as first_name,
							uf.middle_name as middle_name,
							uf.last_name as last_name

					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['action'] ) && !is_array( $filter_data['action'] ) && trim( $filter_data['action'] ) != '' && !isset( $filter_data['action_id'] ) ) {
			$filter_data['action_id'] = Option::getByFuzzyValue( $filter_data['action'], $this->getOptions( 'action' ) );
		}
		$query .= ( isset( $filter_data['action_id'] ) ) ? $this->getWhereClauseSQL( 'a.action_id', $filter_data['action_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['object_id'] ) ) ? $this->getWhereClauseSQL( 'a.object_id', $filter_data['object_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['table_name'] ) ) ? $this->getWhereClauseSQL( 'a.table_name', $filter_data['table_name'], 'text_list', $ph ) : null;
		$query .= ( isset( $filter_data['date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['start_date'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['end_date'], 'end_date', $ph ) : null;

		if ( isset( $filter_data['first_name'] ) && !is_array( $filter_data['first_name'] ) && trim( $filter_data['first_name'] ) != '' ) {
			$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $filter_data['first_name'] ) ) );
			$query .= ' AND (lower(uf.first_name) LIKE ? ) ';
		}
		if ( isset( $filter_data['last_name'] ) && !is_array( $filter_data['last_name'] ) && trim( $filter_data['last_name'] ) != '' ) {
			$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $filter_data['last_name'] ) ) );
			$query .= ' AND (lower(uf.last_name) LIKE ? ) ';
		}

		//Need to support table_name -> object_id pairs for including log entires from different tables/objects.
		if ( isset( $filter_data['table_name_object_id'] ) && is_array( $filter_data['table_name_object_id'] ) && count( $filter_data['table_name_object_id'] ) > 0 ) {
			$sub_query = [];
			foreach ( $filter_data['table_name_object_id'] as $table_name => $object_id ) {
				$ph[] = strtolower( trim( $table_name ) );
				$sub_query[] = '(a.table_name = ? AND a.object_id in (' . $this->getListSQL( $object_id, $ph, 'uuid' ) . ') )';
			}

			if ( empty( $sub_query ) == false ) {
				$query .= ' AND ( ' . implode( ' OR ', $sub_query ) . ' ) ';
			}
			unset( $table_name, $object_id, $sub_query );
		}

		$query .= ( isset( $filter_data['description'] ) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : null;

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
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
	 * @return bool|LogListFactory
	 */
	function getAPISearchWithDetailsByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'action_id', 'object_id', 'last_name', 'first_name' ];

		$sort_column_aliases = [
				'action' => 'action_id',
				'object' => 'table_name',
				'display_field' => 'field',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'date' => 'desc', 'table_name' => 'asc', 'object_id' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['date'] ) ) {
				$order['date'] = 'desc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['user_ids'] ) ) {
			$filter_data['user_id'] = $filter_data['user_ids'];
		}
		if ( isset( $filter_data['log_action_id'] ) ) {
			$filter_data['action_id'] = $filter_data['log_action_id'];
		}
		if ( isset( $filter_data['log_table_name_id'] ) ) {
			$filter_data['table_name'] = $filter_data['log_table_name_id'];
		}

		$uf = new UserFactory();
		$ldf = new LogDetailFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							ldf.field as field,
							ldf.old_value as old_value,
							ldf.new_value as new_value,							
							uf.first_name as first_name,
							uf.middle_name as middle_name,
							uf.last_name as last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN '. $ldf->getTable()	. ' as ldf ON ( a.id = ldf.system_log_id )
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['action'] ) && !is_array( $filter_data['action'] ) && trim( $filter_data['action'] ) != '' && !isset( $filter_data['action_id'] ) ) {
			$filter_data['action_id'] = Option::getByFuzzyValue( $filter_data['action'], $this->getOptions( 'action' ) );
		}
		$query .= ( isset( $filter_data['action_id'] ) ) ? $this->getWhereClauseSQL( 'a.action_id', $filter_data['action_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['object_id'] ) ) ? $this->getWhereClauseSQL( 'a.object_id', $filter_data['object_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['table_name'] ) ) ? $this->getWhereClauseSQL( 'a.table_name', $filter_data['table_name'], 'text_list', $ph ) : null;
		$query .= ( isset( $filter_data['date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['start_date'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['end_date'], 'end_date', $ph ) : null;

		if ( isset( $filter_data['first_name'] ) && !is_array( $filter_data['first_name'] ) && trim( $filter_data['first_name'] ) != '' ) {
			$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $filter_data['first_name'] ) ) );
			$query .= ' AND (lower(uf.first_name) LIKE ? ) ';
		}
		if ( isset( $filter_data['last_name'] ) && !is_array( $filter_data['last_name'] ) && trim( $filter_data['last_name'] ) != '' ) {
			$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $filter_data['last_name'] ) ) );
			$query .= ' AND (lower(uf.last_name) LIKE ? ) ';
		}

		//Need to support table_name -> object_id pairs for including log entires from different tables/objects.
		if ( isset( $filter_data['table_name_object_id'] ) && is_array( $filter_data['table_name_object_id'] ) && count( $filter_data['table_name_object_id'] ) > 0 ) {
			$sub_query = [];
			foreach ( $filter_data['table_name_object_id'] as $table_name => $object_id ) {
				$ph[] = strtolower( trim( $table_name ) );
				$sub_query[] = '(a.table_name = ? AND a.object_id in (' . $this->getListSQL( $object_id, $ph, 'uuid' ) . ') )';
			}

			if ( empty( $sub_query ) == false ) {
				$query .= ' AND ( ' . implode( ' OR ', $sub_query ) . ' ) ';
			}
			unset( $table_name, $object_id, $sub_query );
		}

		$query .= ( isset( $filter_data['description'] ) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : null;

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>
