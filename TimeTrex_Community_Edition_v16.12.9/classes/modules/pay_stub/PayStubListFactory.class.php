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
 * @package Modules\PayStub
 * @implements IteratorAggregate<PayStubFactory>
 */
class PayStubListFactory extends PayStubFactory implements IteratorAggregate {

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
	 * @return bool|PayStubListFactory
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
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $id );
		}

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ulf = new UserListFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $ulf->getTable() . ' as b
					where	a.user_id = b.id
						AND a.id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id      UUID
	 * @param string $user_id UUID
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByIdAndUserId( $id, $user_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		$ph = [
				'id'      => TTUUID::castUUID( $id ),
				'user_id' => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
						AND user_id = ?
						AND deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
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
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndCompanyId( $user_id, $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where 	b.company_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string|string[] $user_id       UUID
	 * @param string $company_id             UUID
	 * @param string|string[] $pay_period_id UUID
	 * @param int $limit                     Limit the number of records returned
	 * @param int $page                      Page number of records to return for pagination
	 * @param array $where                   Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                   Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndCompanyIdAndPayPeriodId( $user_id, $company_id, $pay_period_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'a.user_id' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where 	b.company_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						';

		$query .= ( isset( $pay_period_id ) && $pay_period_id != '' ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $pay_period_id, 'uuid_list', $ph ) : null;

		$query .= ' AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id               UUID
	 * @param string $pay_stub_amendment_id UUID
	 * @param int $limit                    Limit the number of records returned
	 * @param int $page                     Page number of records to return for pagination
	 * @param array $where                  Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                  Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndPayStubAmendmentId( $user_id, $pay_stub_amendment_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $pay_stub_amendment_id == '' ) {
			return false;
		}

		$ulf = new UserListFactory();
		$pself = new PayStubEntryListFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
				'psa_id'  => TTUUID::castUUID( $pay_stub_amendment_id ),
		];

		$query = '
					select	distinct a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
						LEFT JOIN ' . $pself->getTable() . ' as c ON ( a.id = c.pay_stub_id )
					where a.user_id = ?
						AND c.pay_stub_amendment_id = ?
						';

		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id         UUID
	 * @param string $user_expense_id UUID
	 * @param int $limit              Limit the number of records returned
	 * @param int $page               Page number of records to return for pagination
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndUserExpenseId( $user_id, $user_expense_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $user_expense_id == '' ) {
			return false;
		}

		$ulf = new UserListFactory();
		$pself = new PayStubEntryListFactory();

		$ph = [
				'user_id'         => TTUUID::castUUID( $user_id ),
				'user_expense_id' => TTUUID::castUUID( $user_expense_id ),
		];

		$query = '
					select	distinct a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
						LEFT JOIN ' . $pself->getTable() . ' as c ON ( a.id = c.pay_stub_id )
					where a.user_id = ?
						AND c.user_expense_id = ?
						';

		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param null $limit
	 * @param null $page
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 * @throws DBError
	 */
	function getFirstPayStubByUserIdAndStartDate( $user_id, $start_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.start_date' => 'asc', 'a.run_id' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'start_date'  => $this->db->BindTimeStamp( $start_date ),
				'start_date2' => $this->db->BindTimeStamp( $start_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	( a.start_date >= ? OR a.end_date >= ? )
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $run_id
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getLastPayStubByUserIdAndStartDateAndRun( $user_id, $start_date, $run_id, $limit = null, $page = null, $where = null, $order = null  ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.start_date' => 'desc', 'a.run_id' => 'desc', 'c.start_date' => 'desc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'start_date'  => $this->db->BindTimeStamp( $start_date ),
				'run_id'      => (int)$this->castInteger( (int)$run_id, 'smallint' ),
				'start_date2' => $this->db->BindTimeStamp( $start_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id)
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	( ( a.start_date = ? AND a.run_id < ? ) OR a.start_date < ? )
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id       UUID
	 * @param int $transaction_date EPOCH
	 * @param int $run_id
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getLastPayStubByUserIdAndTransactionDateAndRun( $user_id, $transaction_date, $run_id, $limit = null, $page = null, $where = null, $order = null  ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $transaction_date == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'c.transaction_date' => 'desc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'start_date'  => $this->db->BindTimeStamp( $transaction_date ),
				'run_id'      => (int)$this->castInteger( (int)$run_id, 'smallint' ),
				'start_date2' => $this->db->BindTimeStamp( $transaction_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where
						( ( a.transaction_date = ? AND a.run_id < ? ) OR a.transaction_date < ? )
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')						
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $user_id       UUID
	 * @param int $transaction_date EPOCH
	 * @param int $run_id
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getNextPayStubByUserIdAndTransactionDateAndRun( $user_id, $transaction_date, $run_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'asc', 'a.run_id' => 'asc', 'c.transaction_date' => 'asc' ]; //Sort in ASC order as its getting the NEXT pay stub. This is required for PayStubFactory->reCalculateYTD()
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'transaction_date'  => $this->db->BindTimeStamp( $transaction_date ),
				'run_id'            => (int)$this->castInteger( (int)$run_id, 'smallint' ),
				'transaction_date2' => $this->db->BindTimeStamp( TTDate::getEndDayEpoch( $transaction_date ) ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where
						( ( a.transaction_date = ? AND a.run_id > ? ) OR a.transaction_date > ? )
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param null $limit
	 * @param null $page
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 * @throws DBError
	 */
	function getByUserIdAndStartDateAndEndDate( $user_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'asc', 'a.run_id' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'start_date' => $this->db->BindTimeStamp( $start_date ),
				'end_date'   => $this->db->BindTimeStamp( $end_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param null $limit
	 * @param null $page
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 * @throws DBError
	 */
	function getByCompanyIdAndStartDateAndEndDate( $company_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'asc', 'a.run_id' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'start_date' => $this->db->BindTimeStamp( $start_date ),
				'end_date'   => $this->db->BindTimeStamp( $end_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?						
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?						
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyId( $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndId( $company_id, $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $legal_entity_id UUID
	 * @param int $limit              Limit the number of records returned
	 * @param int $page               Page number of records to return for pagination
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndLegalEntityId( $company_id, $legal_entity_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $legal_entity_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $ulf->getTable() . ' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND b.legal_entity_id in (' . $this->getListSQL( $legal_entity_id, $ph, 'uuid' ) . ')
						AND a.deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $id      UUID
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndId( $user_id, $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.id = ?
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByPayPeriodId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [];
			$strict = false;
		} else {
			$strict = true;
		}

		$ulf = new UserListFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					LEFT JOIN ' . $ulf->getTable() . ' as uf ON ( a.user_id = uf.id )
					where	a.pay_period_id = ?
						AND ( a.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCurrencyId( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	currency_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndPayPeriodId( $company_id, $pay_period_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int $run_id
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndPayPeriodIdAndRun( $company_id, $pay_period_id, $run_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id'    => TTUUID::castUUID( $company_id ),
				'pay_period_id' => TTUUID::castUUID( $pay_period_id ),
				'run_id'        => (int)$this->castInteger( (int)$run_id, 'smallint' ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.pay_period_id = ?
						AND a.run_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $user_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int $run_id
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndUserIdAndPayPeriodIdAndRun( $company_id, $user_id, $pay_period_id, $run_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id'    => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
				'pay_period_id' => TTUUID::castUUID( $pay_period_id ),
				'run_id'        => (int)$this->castInteger( (int)$run_id, 'smallint' ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.user_id = ?
						AND a.pay_period_id = ?
						AND a.run_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int[] $status_id
	 * @param int $date             EPOCH
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndPayPeriodIdAndStatusIdAndTransactionDateBeforeDate( $company_id, $pay_period_id, $status_id, $date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id'       => TTUUID::castUUID( $company_id ),
				'pay_period_id'    => TTUUID::castUUID( $pay_period_id ),
				'transaction_date' => $this->db->BindTimeStamp( $date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.pay_period_id = ?
						AND a.transaction_date < ?
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param int $status_id
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndStatusIdAndTypeId( $company_id, $status_id, $type_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?						
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND a.type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int $status_id
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndPayPeriodIdAndStatusId( $company_id, $pay_period_id, $status_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string[] $pay_period_id UUID
	 * @param int[] $status_id
	 * @param int $run_id
	 * @param int $limit              Limit the number of records returned
	 * @param int $page               Page number of records to return for pagination
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByCompanyIdAndPayPeriodIdAndStatusIdAndNotRun( $company_id, $pay_period_id, $status_id, $run_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $run_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'run_id'     => (int)$this->castInteger( (int)$run_id, 'smallint' ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.run_id != ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id    UUID
	 * @param string $pay_period_id UUID
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getPayRunStatusByCompanyIdAndPayPeriodId( $company_id, $pay_period_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null || !is_array( $order ) ) {
			$order = [ 'a.run_id' => 'desc', 'a.status_id' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.run_id,a.status_id,count(*) as total_pay_stubs
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $ulf->getTable() . ' as b ON ( a.user_id = b.id )
							LEFT JOIN ' . $pplf->getTable() . ' as c ON ( a.pay_period_id = c.id AND c.deleted = 0 )
					where	b.company_id = ?
						AND a.pay_period_id in (' . $this->getListSQL( $pay_period_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
						GROUP BY a.run_id, a.status_id
						';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $user_id       UUID
	 * @param string $pay_period_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndPayPeriodId( $user_id, $pay_period_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		$ph = [
				'pay_period_id' => TTUUID::castUUID( $pay_period_id ),
				'user_id'       => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	pay_period_id = ?
						AND user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id       UUID
	 * @param string $pay_period_id UUID
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubListFactory
	 */
	function getByUserIdAndTypeIdAndPayPeriodId( $user_id, $type_id, $pay_period_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $pay_period_id == '' ) {
			return false;
		}

		$ph = [
				'pay_period_id' => TTUUID::castUUID( $pay_period_id ),
				'user_id'       => TTUUID::castUUID( $user_id ),
				'type_id'       => (int)$type_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	pay_period_id = ?
						AND user_id = ?
						AND type_id = ?
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
	 * @return bool|PayStubListFactory
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

		if ( isset( $filter_data['pay_stub_status_id'] ) ) {
			$filter_data['status_id'] = $filter_data['pay_stub_status_id'];
		}

		if ( isset( $filter_data['pay_stub_run_id'] ) ) {
			$filter_data['run_id'] = $filter_data['pay_stub_run_id'];
		}
		if ( isset( $filter_data['pay_stub_type_id'] ) ) {
			$filter_data['type_id'] = $filter_data['pay_stub_type_id'];
		}

		if ( isset( $filter_data['include_user_ids'] ) ) {
			$filter_data['include_user_id'] = $filter_data['include_user_ids'];
		}

		if ( isset( $filter_data['title_id'] ) ) {
			$filter_data['user_title_id'] = $filter_data['title_id'];
		}

		if ( isset( $filter_data['group_id'] ) ) {
			$filter_data['user_group_id'] = $filter_data['group_id'];
		}

		$additional_order_fields = [ 'user_status_id', 'last_name', 'first_name', 'b.employee_number', 'default_branch', 'default_department', 'user_group', 'title', 'country', 'province', 'currency', 'b.city' ];

		$sort_column_aliases = [
				'user_status' => 'user_status_id',
				'status'      => 'status_id',
				'type'        => 'type_id',
				'city'        => 'b.city',
		];
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			//Sort by end_date after run_id, so all else being equal later end dates come first.
			$order = [ 'a.transaction_date' => 'desc', 'a.run_id' => 'desc', 'a.end_date' => 'desc', 'a.start_date' => 'desc', 'a.pay_period_id' => 'desc', 'b.last_name' => 'asc', 'b.first_name' => 'asc', 'b.employee_number' => 'asc' ];
			$strict = false;
		} else {
			if ( isset( $order['transaction_date'] ) ) {
				$order['last_name'] = 'asc';
				$order['first_name'] = 'asc';
				$order['b.employee_number'] = 'asc';
			} else {
				$order['transaction_date'] = 'desc';
			}
			$order['run_id'] = 'desc';

			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							b.legal_entity_id as legal_entity_id,
							b.first_name as first_name,
							b.last_name as last_name,
							b.status_id as user_status_id,
							b.city as city,
							b.province as province,
							b.country as country,

							b.default_branch_id as default_branch_id,
							bf.name as default_branch,
							b.default_department_id as default_department_id,
							df.name as default_department,
							b.group_id as group_id,
							ugf.name as user_group,
							b.title_id as title_id,
							utf.name as title,

							cf.name as currency,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id AND b.deleted = 0 )
						LEFT JOIN ' . $bf->getTable() . ' as bf ON ( b.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as df ON ( b.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as ugf ON ( b.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as utf ON ( b.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN ' . $cf->getTable() . ' as cf ON ( a.currency_id = cf.id AND cf.deleted = 0 )

						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['include_user_id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['include_user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'b.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;

		if ( isset( $filter_data['include_subgroups'] ) && (bool)$filter_data['include_subgroups'] == true ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], true );
		}
		$query .= ( isset( $filter_data['user_group_id'] ) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['user_group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['first_name'] ) ) ? $this->getWhereClauseSQL( 'b.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : null;
		$query .= ( isset( $filter_data['last_name'] ) ) ? $this->getWhereClauseSQL( 'b.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : null;

		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_title_id'] ) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['user_title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['sex_id'] ) ) ? $this->getWhereClauseSQL( 'b.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'b.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_currency_id'] ) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['pay_stub_currency_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['country'] ) ) ? $this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['province'] ) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['city'] ) ) ? $this->getWhereClauseSQL( 'b.city', $filter_data['city'], 'text', $ph ) : null;

		//Pay Stub Status.
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['run_id'] ) ) ? $this->getWhereClauseSQL( 'a.run_id', $filter_data['run_id'], 'smallint_list', $ph ) : null;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)TTDate::parseDateTime( $filter_data['start_date'] ) );
			$query .= ' AND a.transaction_date >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)TTDate::parseDateTime( $filter_data['end_date'] ) );
			$query .= ' AND a.transaction_date <= ?';
		}

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id     UUID
	 * @param string $pay_period_ids UUID
	 * @return int
	 */
	static function getCurrentPayRun( $company_id, $pay_period_ids ) {
		if ( !is_array( $pay_period_ids ) && TTUUID::isUUID( $pay_period_ids ) ) {
			$pay_period_ids = (array)$pay_period_ids;
		}

		$retval = 1;
		if ( is_array( $pay_period_ids ) && count( $pay_period_ids ) > 0 ) {
			$pp_retval = $retval;
			foreach ( $pay_period_ids as $pay_period_id ) {
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getPayRunStatusByCompanyIdAndPayPeriodId( $company_id, $pay_period_id );
				if ( $pslf->getRecordCount() > 0 ) {
					//Current Pay Run is the highest run with open pay stubs.
					//If no open pay stubs exist, move on to the next run.
					foreach ( $pslf as $ps_obj ) {
						Debug::Text( 'Pay Period ID: ' . $pay_period_id . ' Run ID: ' . $ps_obj->getColumn( 'run_id' ) . ' Status ID: ' . $ps_obj->getColumn( 'status_id' ) . ' Total Pay Stubs: ' . $ps_obj->getColumn( 'total_pay_stubs' ), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $ps_obj->getColumn( 'status_id' ) == 25 ) {
							$pp_retval = (int)$ps_obj->getColumn( 'run_id' );
							break;
						} else if ( $ps_obj->getColumn( 'status_id' ) == 40 ) {
							$pp_retval = ( (int)$ps_obj->getColumn( 'run_id' ) + 1 );
							break;
						}
					}
				} else {
					Debug::Text( '  No pay stubs found in pay periods...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				if ( isset( $pp_retval ) && $pp_retval > $retval ) {
					$retval = $pp_retval;
				} else {
					Debug::Text( '  Skipping Run ID: ' . $pp_retval, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		Debug::Text( '  Current Run ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
