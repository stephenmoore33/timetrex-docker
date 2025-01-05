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
 * @package Modules\PayStubAmendment
 * @implements IteratorAggregate<PayStubAmendmentFactory>
 */
class PayStubAmendmentListFactory extends PayStubAmendmentFactory implements IteratorAggregate {

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
	 * @return bool|PayStubAmendmentListFactory
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
	 * @param string $id      UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByIdAndStartDateAndEndDate( $id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'start_date' => $start_date,
				'end_date'   => $end_date,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
						AND effective_date >= ?
						AND effective_date <= ?
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
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByCompanyId( $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			//$order = array( 'a.effective_date' => 'desc', 'a.user_id' => 'asc' );
			$order = [ 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' ];
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
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $psen_id UUID
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByPayStubEntryNameID( $psen_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $psen_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$strict_order = false;
		}

		$ph = [
				'psen_id' => TTUUID::castUUID( $psen_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where
						a.pay_stub_entry_name_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id      UUID
	 * @param string $user_id UUID
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
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
	 * @return bool|PayStubAmendmentListFactory
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
	 * @param string $recurring_ps_amendment_id UUID
	 * @param int $limit                        Limit the number of records returned
	 * @param int $page                         Page number of records to return for pagination
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByRecurringPayStubAmendmentId( $recurring_ps_amendment_id, $limit = null, $page = null, $where = null, $order = null ) {

		if ( $recurring_ps_amendment_id == '' ) {
			return false;
		}

		$strict_order = true;
		if ( $order == null ) {
			$order = [ 'effective_date' => 'desc', 'user_id' => 'asc' ];
			$strict_order = false;
		}

		$ph = [
				'recurring_ps_amendment_id' => TTUUID::castUUID( $recurring_ps_amendment_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	recurring_ps_amendment_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	/**
	 * @param string $user_id                   UUID
	 * @param string $recurring_ps_amendment_id UUID
	 * @param int $start_date                   EPOCH
	 * @param int $end_date                     EPOCH
	 * @param int $limit                        Limit the number of records returned
	 * @param int $page                         Page number of records to return for pagination
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByUserIdAndRecurringPayStubAmendmentIdAndStartDateAndEndDate( $user_id, $recurring_ps_amendment_id, $start_date, $end_date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $recurring_ps_amendment_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
				'user_id'                   => TTUUID::castUUID( $user_id ),
				'recurring_ps_amendment_id' => TTUUID::castUUID( $recurring_ps_amendment_id ),
				'start_date'                => $start_date,
				'end_date'                  => $end_date,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND recurring_ps_amendment_id = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
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
			$order = [ 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' ];
			$strict_order = false;
		}

		$ulf = new UserListFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $ulf->getTable() . ' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND a.user_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param int $date       EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getIsModifiedByUserIdAndStartDateAndEndDateAndDate( $user_id, $start_date, $end_date, $date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		$ph = [
				'user_id'      => TTUUID::castUUID( $user_id ),
				'start_date'   => $start_date,
				'end_date'     => $end_date,
				'created_date' => $date,
				'updated_date' => $date,
		];

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	' . $this->getTable() . '
					where
							user_id = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND
							( created_date >= ? OR updated_date >= ? )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text( 'PS Amendment rows have been modified: ' . $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}
		Debug::text( 'PS Amendment rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $authorized
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByCompanyIdAndAuthorizedAndStartDateAndEndDate( $company_id, $authorized, $start_date, $end_date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $authorized == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'authorized' => $this->toBool( $authorized ),
				'start_date' => $start_date,
				'end_date'   => $end_date,
		];

		//CalculatePayStub uses this to find PS amendments.
		//Because of percent amounts, make sure we order by effective date FIRST,
		//Then FIXED amounts, then percents.


		//Pay period end dates never equal the start start date, so >= and <= are proper.

		//06-Oct-06: Start including YTD_adjustment entries for the new pay stub calculation system.
		//						AND ytd_adjustment = 0
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as uf
					where
						a.user_id = uf.id
						AND uf.company_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.deleted = 0
					ORDER BY a.effective_date asc, a.type_id asc
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param $authorized
	 * @param $status_id
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 * @throws DBError
	 */
	function getByUserIdAndAuthorizedAndStatusIDAndStartDateAndEndDate( $user_id, $authorized, $status_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $authorized == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
			//'status_id' => (int)$status_id, //Normally only 50=ACTIVE, unless correction pay stubs are being generated, then it needs to be an array.
			'authorized' => $this->toBool( $authorized ),
			'start_date' => $start_date,
			'end_date'   => $end_date,
		];

		//CalculatePayStub uses this to find PS amendments.
		//Because of percent amounts, make sure we order by effective date FIRST,
		//Then FIXED amounts, then percents.

		//Pay period end dates never equal the start start date, so >= and <= are proper.

		//06-Oct-06: Start including YTD_adjustment entries for the new pay stub calculation system.
		//						AND ytd_adjustment = 0

		//Make sure we ignore any pay stub amendments that happen to belong to deleted pay stub accounts.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as psea
					where
						a.pay_stub_entry_name_id = psea.id
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND psea.deleted = 0 )
					ORDER BY a.effective_date asc, a.type_id asc, psea.ps_order asc, a.id asc
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param $authorized
	 * @param $status_id
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 * @throws DBError
	 */
	function getByUserIdAndAuthorizedAndStatusIDAndStartDateAndEndDateAndIncludePayStubId( $user_id, $authorized, $status_id, $start_date, $end_date, $include_pay_stub_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $authorized == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$pseaf = new PayStubEntryAccountFactory();
		$psef = new PayStubEntryFactory();
		$psf = new PayStubFactory();

		$ph = [
			//'status_id' => (int)$status_id, //Normally only 50=ACTIVE, unless correction pay stubs are being generated, then it needs to be an array.
			'authorized' => $this->toBool( $authorized ),
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'include_pay_stub_id' => TTUUID::castUUID( (string)$include_pay_stub_id ),
		];

		//CalculatePayStub uses this to find PS amendments.
		//Because of percent amounts, make sure we order by effective date FIRST,
		//Then FIXED amounts, then percents.

		//Pay period end dates never equal the start start date, so >= and <= are proper.

		//Make sure we ignore any pay stub amendments that happen to belong to deleted pay stub accounts.
		//
		//  Also ensure we exclude pay stub amendments assigned to other out-of-cycle pay stubs in the same pay period.
		//  However if we are doing a post-adjustment carry-forward then we need to include pay stub amendments assigned to the pay stub we are correcting, otherwise the difference calculation will be incorrect of course.
		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $pseaf->getTable() . ' as psea ON ( a.pay_stub_entry_name_id = psea.id )
					WHERE a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?						
						AND a.id NOT IN (
								SELECT pay_stub_amendment_id 
								FROM ' . $psef->getTable() . ' as pse_b				 	
								LEFT JOIN ' . $psf->getTable() . ' as ps_b ON ( pse_b.pay_stub_id = ps_b.id )				
								WHERE ps_b.id != ?
									AND ps_b.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
									AND pse_b.pay_stub_amendment_id is NOT NULL
									AND ( ps_b.deleted = 0 AND ps_b.deleted = 0 )					
						)																	
						AND a.status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						 
						AND ( a.deleted = 0 AND psea.deleted = 0 )
					ORDER BY a.effective_date asc, a.type_id asc, psea.ps_order asc, a.id asc
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param $authorized
	 * @param $ytd_adjustment
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByUserIdAndAuthorizedAndYTDAdjustmentAndStartDateAndEndDate( $user_id, $authorized, $ytd_adjustment, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $authorized == '' ) {
			return false;
		}

		if ( $ytd_adjustment == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$ph = [
				'authorized'     => $this->toBool( $authorized ),
				'start_date'     => $start_date,
				'end_date'       => $end_date,
				'ytd_adjustment' => $this->toBool( $ytd_adjustment ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where
						authorized = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND ytd_adjustment = ?
						AND user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param $authorized
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|int
	 */
	function getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $user_id, $type_id, $authorized, $start_date, $end_date, $where = null, $order = null ) {
		$psalf = new PayStubAmendmentListFactory();
		$psalf->getByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $user_id, $type_id, $authorized, $start_date, $end_date, $where, $order );
		if ( $psalf->getRecordCount() > 0 ) {
			$sum = 0;
			Debug::text( 'Record Count: ' . $psalf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			foreach ( $psalf as $psa_obj ) {
				$amount = $psa_obj->getCalculatedAmount();
				Debug::text( 'PS Amendment Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
				$sum += $amount;
			}

			return $sum;
		}

		Debug::text( 'No PS Amendments found...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param $authorized
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 */
	function getByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $user_id, $type_id, $authorized, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'type_id'    => (int)$type_id,
				'authorized' => $this->toBool( $authorized ),
				'start_date' => $start_date,
				'end_date'   => $end_date,
		];

		//select	sum(amount)
		//						AND a.tax_exempt = \''. $this->toBool($tax_exempt) .'\'
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND	a.user_id = ?
						AND b.type_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.ytd_adjustment = 0
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $pay_period_schedule_id
	 * @param $status_id         INT
	 * @param $start_date        INT
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
	 * @throws DBError
	 */
	function getByCompanyIdAndPayPeriodScheduleIdAndStatusAndBeforeStartDate( $company_id, $pay_period_schedule_id, $status_id, $start_date, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$uf = new UserFactory();
		$ppf = new PayPeriodFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = [
				'company_id'             => TTUUID::castUUID( $company_id ),
				'status_id'              => (int)$status_id,
				'start_date'             => TTDate::getMiddleDayEpoch( (int)$start_date ),
				'pay_period_schedule_id' => TTUUID::castUUID( $pay_period_schedule_id ),
		];

		//Make sure we double check that the pay period each PSA is assigned too is closed.
		//  That way we don't delete PSA's in open pay periods that are in the past.
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

						LEFT JOIN ' . $ppsuf->getTable() . ' as ppsuf ON ( a.user_id = ppsuf.user_id )
						LEFT JOIN ' . $ppsf->getTable() . ' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( ppsuf.pay_period_schedule_id = ppf.pay_period_schedule_id AND ' . $this->getSQLToTimeStampFunction() . '(a.effective_date) >= ppf.start_date AND ' . $this->getSQLToTimeStampFunction() . '(a.effective_date) <= ppf.end_date AND ppf.deleted = 0 )						

						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?						
						AND a.status_id = ?												
						AND a.effective_date < ?
						AND ppsf.id = ?
						AND ppf.status_id = 20	
					';

		//Need to account for employees being assigned to deleted pay period schedules.
		$query .= ' AND ( ppsuf.id IS NULL OR ppsf.id IS NOT NULL ) AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $name_id UUID
	 * @param $authorized
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool
	 */
	function getAmountSumByUserIdAndNameIdAndAuthorizedAndStartDateAndEndDate( $user_id, $name_id, $authorized, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $name_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'authorized' => $this->toBool( $authorized ),
				'start_date' => $start_date,
				'end_date'   => $end_date,
		];

		$query = '
					select	sum(amount)
					from	' . $this->getTable() . ' as a,
							' . $psealf->getTable() . ' as b
					where	a.pay_stub_entry_name_id = b.id
						AND	a.user_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND b.id in (' . $this->getListSQL( $name_id, $ph, 'uuid' ) . ')
						AND a.ytd_adjustment = 0
						AND a.deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$sum = $this->db->GetOne( $query, $ph );

		if ( $sum !== false || $sum !== null ) {
			Debug::text( 'Amount Sum: ' . $sum, __FILE__, __LINE__, __METHOD__, 10 );

			return $sum;
		}

		Debug::text( 'Amount Sum is NULL', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PayStubAmendmentListFactory
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

		$additional_order_fields = [ 'pay_stub_entry_name', 'user_status_id', 'last_name', 'first_name', 'default_branch', 'default_department', 'bfb.name', 'dfb.name', 'jfb.name', 'jifb.name', 'user_group', 'title' ];

		$sort_column_aliases = [
				'user_status' => 'user_status_id',
				'status'      => 'status_id',
				'type'        => 'type_id',
				'branch'      => 'bfb.name',
				'department'  => 'dfb.name',
		];
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$sort_column_aliases['job'] = 'jfb.name';
			$sort_column_aliases['job_item'] = 'jifb.name';
		} else {
			$sort_column_aliases['job'] = false;
			$sort_column_aliases['job_item'] = false;
		}
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'effective_date' => 'desc', 'last_name' => 'asc' ];
			$strict = false;
		} else {
			//Always sort by effective_date, last name after other columns
			if ( !isset( $order['effective_date'] ) ) {
				$order['effective_date'] = 'desc';
			}

			if ( !isset( $order['last_name'] ) ) {
				$order['last_name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$pseaf = new PayStubEntryAccountFactory();
		$ppf = new PayPeriodFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.status_id as user_status_id,

							b.default_branch_id as default_branch_id,
							bf.name as default_branch,
							b.default_department_id as default_department_id,
							df.name as default_department,
							b.group_id as group_id,
							ugf.name as user_group,
							b.title_id as title_id,
							utf.name as title,
							a.branch_id as branch_id,
							bfb.name as branch,
							a.department_id as department_id,
							dfb.name as department,							 
							';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '							
							a.job_id as job_id,
							jfb.name as job,
							a.job_item_id as job_item_id,
							jifb.name as job_item, ';
		}

		$query .= '							
							pseaf.name as pay_stub_entry_name,

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
						LEFT JOIN ' . $bf->getTable() . ' as bfb ON ( a.branch_id = bfb.id AND bfb.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as dfb ON ( a.department_id = dfb.id AND dfb.deleted = 0)						 
						';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '				
						LEFT JOIN ' . $jf->getTable() . ' as jfb ON ( a.job_id = jfb.id AND jfb.deleted = 0)
						LEFT JOIN ' . $jif->getTable() . ' as jifb ON ( a.job_item_id = jifb.id AND jifb.deleted = 0) ';
		}

		$query .= '
						LEFT JOIN ' . $ugf->getTable() . ' as ugf ON ( b.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as utf ON ( b.title_id = utf.id AND utf.deleted = 0 )

						LEFT JOIN ' . $pseaf->getTable() . ' as pseaf ON ( a.pay_stub_entry_name_id = pseaf.id AND pseaf.deleted = 0 )
						';

		//This join is slow and only needed if filtering by pay_period_id. Below there is another section at the bottom of the WHERE clause that is conditional on this too.
		if ( isset( $filter_data['pay_period_id'] ) ) {
			$query .= '					
						LEFT JOIN ' . $ppsuf->getTable() . ' as ppsuf ON ( a.user_id = ppsuf.user_id )
						LEFT JOIN ' . $ppsf->getTable() . ' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )
						LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( ppsuf.pay_period_schedule_id = ppf.pay_period_schedule_id AND ' . $this->getSQLToTimeStampFunction() . '(a.effective_date) >= ppf.start_date AND ' . $this->getSQLToTimeStampFunction() . '(a.effective_date) <= ppf.end_date AND ppf.deleted = 0 )
					';
		}

		$query .= '
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_stub_entry_name_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_stub_entry_name_id', $filter_data['pay_stub_entry_name_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'b.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'ppf.id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['branch_id'] ) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['department_id'] ) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['recurring_ps_amendment_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_ps_amendment_id', $filter_data['recurring_ps_amendment_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['start_date'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['end_date'], 'end_date', $ph ) : null;
		$query .= ( isset( $filter_data['effective_date'] ) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['effective_date'], 'end_date', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		if ( isset( $filter_data['pay_period_id'] ) ) {
			//Need to account for employees being assigned to deleted pay period schedules.
			$query .= ' AND ( ppsuf.id IS NULL OR ppsf.id IS NOT NULL ) ';
		}

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}
}

?>
