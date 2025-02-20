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
 * @package Modules\Accrual
 * @implements IteratorAggregate<AccrualBalanceFactory>
 */
class AccrualBalanceListFactory extends AccrualBalanceFactory implements IteratorAggregate {
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
	 * @return AccrualBalanceListFactory|bool
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
	 * @return AccrualBalanceListFactory|bool
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
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
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
	 * @return AccrualBalanceListFactory|bool
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
					select	*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualBalanceListFactory|bool
	 */
	function getByUserIdAndCompanyId( $user_id, $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.balance', 'c.name' ];
		if ( $order == null ) {
			$order = [ 'c.name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();
		$apaf = new AccrualPolicyAccountFactory();
		$af = new AccrualFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select
							a.*
							from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b,
							' . $apaf->getTable() . ' as c
					where	a.user_id = b.id
						AND a.accrual_policy_account_id = c.id
						AND b.company_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND EXISTS ( select 1 from ' . $af->getTable() . ' as af WHERE af.accrual_policy_account_id = a.accrual_policy_account_id AND a.user_id = af.user_id AND af.deleted = 0 )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param $enable_pay_stub_balance_display
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualBalanceListFactory|bool
	 */
	function getByUserIdAndCompanyIdAndStartDateAndEndDateAndEnablePayStubBalanceDisplay( $user_id, $company_id, $start_date, $end_date, $enable_pay_stub_balance_display, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.balance', 'c.name' ];
		if ( $order == null ) {
			$order = [ 'c.name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();
		$apaf = new AccrualPolicyAccountFactory();
		$af = new AccrualFactory();

		$ph = [
				'end_date'   => $this->db->BindTimeStamp( TTDate::getEndDayEpoch( $end_date ) ),
				'start_date2' => $this->db->BindTimeStamp( TTDate::getBeginDayEpoch( $start_date ) ),
				'end_date2'   => $this->db->BindTimeStamp( TTDate::getEndDayEpoch( $end_date ) ),
				'start_date_ytd' => $this->db->BindTimeStamp( TTDate::getBeginYearEpoch( $start_date ) ),
				'end_date_ytd'   => $this->db->BindTimeStamp( TTDate::getEndDayEpoch( $end_date ) ), //YTD up to End Date.
				'start_date_accrual_records' => $this->db->BindTimeStamp( TTDate::getBeginYearEpoch( TTDate::getBeginYearEpoch( $start_date ) - 86400 ) ), //The begininng of the previous year, so we can show balances of any accrual account in the last two years essentially. This needs to catch accruals that only happen once per year.
				'company_id'                      => TTUUID::castUUID( $company_id ),
				'enable_pay_stub_balance_display' => (int)$enable_pay_stub_balance_display,
		];

		//Don't show balances that are 0, or don't have any records in the last two years.
		//  This allows customers to enable showing accrual balances for each account, but it some employees don't use some accruals, they won't see the balances.
		$query = '
					select	a.*,
							c.name as name,
							accrual_future_detail.future_balance_amount,
							accrual_detail_sum.accrued_amount as accrued_amount,
							accrual_detail_sum.used_amount as used_amount,
							accrual_detail_sum_ytd.accrued_amount as accrued_amount_ytd,
							accrual_detail_sum_ytd.used_amount as used_amount_ytd,							
							accrual_record_count_sum.total_accrual_records as total_accrual_records
					from	' . $this->getTable() . ' AS a
							LEFT JOIN ' . $uf->getTable() . ' AS b ON ( a.user_id = b.id )
							LEFT JOIN ' . $apaf->getTable() . ' AS c ON ( a.accrual_policy_account_id = c.id )
							LEFT JOIN (
								SELECT 	af.user_id, 
										af.accrual_policy_account_id,
										sum( amount ) as future_balance_amount
								FROM '. $af->getTable() .' as af 
								WHERE af.time_stamp > ?
									AND af.deleted = 0
								GROUP BY af.user_id, af.accrual_policy_account_id		
							) as accrual_future_detail ON ( a.user_id = accrual_future_detail.user_id AND a.accrual_policy_account_id = accrual_future_detail.accrual_policy_account_id )							
							LEFT JOIN (
								SELECT 	accrual_details.user_id, 
										accrual_details.accrual_policy_account_id,
										sum( accrual_details.accrued_amount ) as accrued_amount, 
										sum( accrual_details.used_amount ) as used_amount
								FROM 
									( 
									SELECT 
										af.user_id,
										af.accrual_policy_account_id,									
										CASE WHEN amount > 0 THEN amount ELSE 0 END as accrued_amount, 
										CASE WHEN amount < 0 THEN amount ELSE 0 END as used_amount 
									FROM '. $af->getTable() .' as af 
									WHERE 
										af.time_stamp >= ? 
										AND af.time_stamp <= ? 
										AND af.deleted = 0
									) as accrual_details
								GROUP BY accrual_details.user_id, accrual_details.accrual_policy_account_id									 
							) as accrual_detail_sum ON ( a.user_id = accrual_detail_sum.user_id AND a.accrual_policy_account_id = accrual_detail_sum.accrual_policy_account_id )
							LEFT JOIN (
								SELECT 	accrual_details.user_id, 
										accrual_details.accrual_policy_account_id,
										sum( accrual_details.accrued_amount ) as accrued_amount, 
										sum( accrual_details.used_amount ) as used_amount
								FROM 
									( 
									SELECT 
										af.user_id,
										af.accrual_policy_account_id,									
										CASE WHEN amount > 0 THEN amount ELSE 0 END as accrued_amount, 
										CASE WHEN amount < 0 THEN amount ELSE 0 END as used_amount 
									FROM '. $af->getTable() .' as af 
									WHERE 
										af.time_stamp >= ? 
										AND af.time_stamp <= ? 
										AND af.deleted = 0
									) as accrual_details
								GROUP BY accrual_details.user_id, accrual_details.accrual_policy_account_id									 
							) as accrual_detail_sum_ytd ON ( a.user_id = accrual_detail_sum_ytd.user_id AND a.accrual_policy_account_id = accrual_detail_sum_ytd.accrual_policy_account_id )														
							LEFT JOIN (
								SELECT 	accrual_record_count.user_id, 
										accrual_record_count.accrual_policy_account_id,
										count(*) as total_accrual_records 
								FROM 
									( 
									SELECT 
										af.user_id,
										af.accrual_policy_account_id,									
										CASE WHEN amount > 0 THEN amount ELSE 0 END as accrued_amount, 
										CASE WHEN amount < 0 THEN amount ELSE 0 END as used_amount 
									FROM '. $af->getTable() .' as af 
									WHERE 
										af.time_stamp >= ? 
										AND af.deleted = 0
									) as accrual_record_count
								GROUP BY accrual_record_count.user_id, accrual_record_count.accrual_policy_account_id									 
							) as accrual_record_count_sum ON ( a.user_id = accrual_record_count_sum.user_id AND a.accrual_policy_account_id = accrual_record_count_sum.accrual_policy_account_id )															
					where 	b.company_id = ?
						AND c.enable_pay_stub_balance_display = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.balance != 0 OR accrual_record_count_sum.total_accrual_records > 0 )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return AccrualBalanceListFactory|bool
	 */
	function getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $accrual_policy_account_id == '' ) {
			return false;
		}

		$ph = [
				'user_id'                   => TTUUID::castUUID( $user_id ),
				'accrual_policy_account_id' => TTUUID::castUUID( $accrual_policy_account_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND accrual_policy_account_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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
	 * @return AccrualBalanceListFactory|bool
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

		$additional_order_fields = [ 'accrual_policy_account', 'first_name', 'last_name', 'name', 'default_branch', 'default_department', 'title' ];
		$sort_column_aliases = [
			//'accrual_policy_type' => 'accrual_policy_type_id',
			'group' => 'e.name',
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'last_name' => 'asc', 'first_name' => 'asc', 'accrual_policy_account_id' => 'asc', 'a.created_date' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by last name, first name after other columns
			/*
			if ( !isset($order['effective_date']) ) {
				$order['effective_date'] = 'desc';
			}
			*/
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$apaf = new AccrualPolicyAccountFactory();
		$af = new AccrualFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							ab.name as accrual_policy_account,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as "group",
							f.id as title_id,
							f.name as title
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $apaf->getTable() . ' as ab ON ( a.accrual_policy_account_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN ' . $bf->getTable() . ' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as f ON ( b.title_id = f.id AND f.deleted = 0 )

					where	b.company_id = ?
						AND EXISTS ( select 1 from ' . $af->getTable() . ' as af WHERE af.accrual_policy_account_id = a.accrual_policy_account_id AND a.user_id = af.user_id AND af.deleted = 0 )
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['accrual_policy_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.accrual_policy_account_id', $filter_data['accrual_policy_account_id'], 'uuid_list', $ph ) : null;

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['group'] ) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['group'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch'] ) ) ? $this->getWhereClauseSQL( 'c.name', $filter_data['default_branch'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department'] ) ) ? $this->getWhereClauseSQL( 'd.name', $filter_data['default_department'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title'] ) ) ? $this->getWhereClauseSQL( 'f.name', $filter_data['title'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['first_name'] ) ) ? $this->getWhereClauseSQL( 'b.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : null;
		$query .= ( isset( $filter_data['last_name'] ) ) ? $this->getWhereClauseSQL( 'b.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : null;

		$query .= ( isset( $filter_data['country'] ) ) ? $this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['province'] ) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : null;

		$query .= '
						AND ( a.deleted = 0 AND ab.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>