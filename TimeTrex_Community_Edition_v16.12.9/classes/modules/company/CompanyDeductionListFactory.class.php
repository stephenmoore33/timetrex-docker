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
 * @implements IteratorAggregate<CompanyDeductionFactory>
 */
class CompanyDeductionListFactory extends CompanyDeductionFactory implements IteratorAggregate {

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
					WHERE deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( is_array( $id ) ) {
			$this->rs = false;
		} else {
			$this->rs = $this->getCache( $id );
		}

		if ( $this->rs === false ) {

			$ph = [];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where	id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			if ( !is_array( $id ) ) {
				$this->saveCache( $this->rs, $id );
			}
		}

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'calculation_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $legal_entity_id UUID
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndLegalEntityId( $company_id, $legal_entity_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $legal_entity_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'calculation_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id'      => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND legal_entity_id in (' . $this->getListSQL( $legal_entity_id, $ph, 'uuid' ) . ')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id        UUID
	 * @param int|int[] $calculation_id INT
	 * @param array $where              Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order              Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 * @throws DBError
	 * @throws Exception
	 */
	function getByCompanyIdAndCalculationId( $company_id, $calculation_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'calculation_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id'      => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND calculation_id in (' . $this->getListSQL( $calculation_id, $ph, 'int' ) . ')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id        UUID
	 * @param string $legal_entity_id   UUID
	 * @param int|int[] $calculation_id INT
	 * @param int $status_id            INT
	 * @param array $where              Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order              Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 * @throws DBError
	 * @throws Exception
	 */
	function getByCompanyIdAndLegalEntityIdAndCalculationIdAndStatusId( $company_id, $legal_entity_id, $calculation_id, $status_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $legal_entity_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'calculation_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id'      => TTUUID::castUUID( $company_id ),
				'legal_entity_id' => TTUUID::castUUID( $legal_entity_id ),
				'status_id'       => (int)$status_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND legal_entity_id = ?
						AND status_id = ?
						AND calculation_id in (' . $this->getListSQL( $calculation_id, $ph, 'int' ) . ')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $company_id
	 * @param string $agency_id UUID
	 * @param int $limit        Limit the number of records returned
	 * @param int $page         Page number of records to return for pagination
	 * @param array $where      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 * @throws DBError
	 */
	function getByCompanyIdAndPayrollRemittanceAgencyId( $company_id, $agency_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $agency_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'legal_entity_id' => 'asc', 'name' => 'asc' ];
		}

		$ph = [
				'company_id'                   => TTUUID::castUUID( $company_id ),
				'payroll_remittance_agency_id' => TTUUID::castUUID( $agency_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND	payroll_remittance_agency_id = ?
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndName( $company_id, $name, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'name'       => $name,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND lower(name) LIKE lower(?)
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $legal_entity_id UUID
	 * @param $name
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByLegalEntityIdAndName( $legal_entity_id, $name, $where = null, $order = null ) {
		if ( $legal_entity_id == '' ) {
			return false;
		}

		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $legal_entity_id ),
				'name'       => $name,
		];

		$query = '	SELECT	*
					FROM	' . $this->getTable() . '
					WHERE	legal_entity_id = ?
						AND lower(name) LIKE LOWER(?)
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '.$query, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $ids        UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByIdAndCompanyId( $ids, $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $ids == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND id in (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $ids        UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndId( $company_id, $ids, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $ids == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND id in (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param int $type_id
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByTypeId( $type_id, $where = null, $order = null ) {
		if ( $type_id == '' ) {
			return false;
		}

		$ph = [];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndTypeId( $company_id, $type_id, $where = null, $order = null ) {
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
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $status_id
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndStatusId( $company_id, $status_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id                UUID
	 * @param string $pay_stub_entry_account_id UUID
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndPayStubEntryAccountId( $company_id, $pay_stub_entry_account_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_stub_entry_account_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND pay_stub_entry_account_id in (' . $this->getListSQL( $pay_stub_entry_account_id, $ph, 'uuid' ) . ')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id                UUID
	 * @param string $pay_stub_entry_account_id UUID
	 * @param array $where                      Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order                      Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndPayStubEntryAccountIdAndStatusIdAndTypeId( $company_id, $pay_stub_entry_account_id, $status_id, $type_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_stub_entry_account_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
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
						AND pay_stub_entry_account_id in (' . $this->getListSQL( $pay_stub_entry_account_id, $ph, 'uuid' ) . ')
						AND status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')						
						AND deleted = 0
					ORDER BY status_id ASC, calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int|int[] $status_id
	 * @param int|int[] $type_id
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndStatusIdAndTypeId( $company_id, $status_id, $type_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'calculation_order' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND status_id in (' . $this->getListSQL( $status_id, $ph, 'int' ) . ')
						AND type_id in (' . $this->getListSQL( $type_id, $ph, 'int' ) . ')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id  UUID
	 * @param string $pay_code_id UUID
	 * @param array $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order        Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIdAndContributingPayCodePolicyId( $company_id, $pay_code_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $pay_code_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	company_id = ?
						AND length_of_service_contributing_pay_code_policy_id in (' . $this->getListSQL( $pay_code_id, $ph, 'uuid' ) . ')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id     UUID
	 * @param string $user_id        UUID
	 * @param string $calculation_id UUID
	 * @param string $pse_account_id UUID
	 * @param array $where           Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order           Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
	 */
	function getByCompanyIDAndUserIdAndCalculationIdAndPayStubEntryAccountID( $company_id, $user_id, $calculation_id, $pse_account_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $calculation_id == '' ) {
			return false;
		}

		if ( $pse_account_id == '' ) {
			return false;
		}

		$udf = new UserDeductionFactory();

		$ph = [
				'company_id'     => TTUUID::castUUID( $company_id ),
				'user_id'        => TTUUID::castUUID( $user_id ),
				'calculation_id' => (int)$calculation_id,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $udf->getTable() . ' as b
					where
						a.company_id = ?
						AND a.id = b.company_deduction_id
						AND b.user_id = ?
						AND a.calculation_id = ?
						AND a.pay_stub_entry_account_id in (' . $this->getListSQL( $pse_account_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					ORDER BY calculation_order
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getByIdArray( $id, $include_blank = true ) {
		if ( $id == '' ) {
			return false;
		}

		$psenlf = new PayStubEntryNameListFactory();
		$psenlf->getById( $id );

		$entry_name_list = [];
		if ( $include_blank == true ) {
			$entry_name_list[TTUUID::getZeroID()] = '--';
		}

		$type_options = $this->getOptions( 'type' );

		foreach ( $psenlf as $entry_name ) {
			$entry_name_list[$entry_name->getID()] = $type_options[$entry_name->getType()] . ' - ' . $entry_name->getDescription();
		}

		return $entry_name_list;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $status_id
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getByCompanyIdAndStatusIdArray( $company_id, $status_id, $include_blank = true ) {
		if ( $status_id == '' ) {
			return false;
		}

		$cdlf = new CompanyDeductionListFactory();
		$cdlf->getByCompanyIdAndStatusId( $company_id, $status_id );
		//$psenlf->getByTypeId($type_id);

		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $cdlf as $obj ) {
			$list[$obj->getID()] = $obj->getName();
		}

		return $list;
	}

	/**
	 * @param $lf
	 * @param bool $include_blank
	 * @param bool $sort_prefix
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf, $include_blank = true, $sort_prefix = false ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $lf as $obj ) {
			$list[$obj->getId()] = $obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|CompanyDeductionListFactory
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

		$additional_order_fields = [ 'status_id', 'lef.legal_name', 'praf.name' ];

		$sort_column_aliases = [
				'status'                    => 'status_id',
				'type'                      => 'type_id',
				'calculation'               => 'calculation_id',
				'legal_entity_legal_name'   => 'lef.legal_name',
				'payroll_remittance_agency' => 'praf.name',
				'total_users'               => false, //Can't sort by this, so ignore it.
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'type_id' => 'asc', 'lef.legal_name' => 'asc', 'name' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['status_id'] ) ) {
				$order = Misc::prependArray( [ 'status_id' => 'asc' ], $order );
			}
			if ( !isset( $order['type_id'] ) ) {
				$order = Misc::prependArray( [ 'type_id' => 'asc' ], $order );
			}

			//Always sort by last name, first name after other columns
			if ( !isset( $order['name'] ) ) {
				$order['name'] = 'asc';
			}
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$praf = new PayrollRemittanceAgencyFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,	
							praf.id as payroll_remittance_agency_id,
							praf.name as payroll_remittance_agency,
							lef.legal_name as legal_entity_legal_name,
							
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $praf->getTable() . ' as praf ON ( a.payroll_remittance_agency_id = praf.id AND praf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( a.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		//$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : NULL;
		if ( isset( $filter_data['permission_children_ids'] ) ) {
			//Return rows that ONLY have this user assigned to them.
			$udf = new UserDeductionFactory();
			$query .= ' AND a.id IN ( select company_deduction_id from ' . $udf->getTable() . ' as udf where udf.user_id in (' . $this->getListSQL( $filter_data['permission_children_ids'], $ph, 'uuid' ) . ') AND udf.deleted = 0 )';
		}

		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['payroll_remittance_agency_id'] ) ) ? $this->getWhereClauseSQL( 'a.payroll_remittance_agency_id', $filter_data['payroll_remittance_agency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['include_user_id'] ) ) {
			//Return rows that ONLY have this user assigned to them.
			$udf = new UserDeductionFactory();
			$query .= ' AND a.id IN ( select company_deduction_id from ' . $udf->getTable() . ' as udf where udf.user_id in (' . $this->getListSQL( $filter_data['include_user_id'], $ph, 'uuid' ) . ') AND udf.deleted = 0 )';
		}

		if ( isset( $filter_data['exclude_user_id'] ) ) {
			//Return rows that DO NOT have this user assigned to them.
			$udf = new UserDeductionFactory();
			$query .= ' AND a.id NOT IN ( select company_deduction_id from ' . $udf->getTable() . ' as udf where udf.user_id in (' . $this->getListSQL( $filter_data['exclude_user_id'], $ph, 'uuid' ) . ') AND udf.deleted = 0 )';
		}

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['calculation_id'] ) ) ? $this->getWhereClauseSQL( 'a.calculation_id', $filter_data['calculation_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['pay_stub_entry_name_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_stub_entry_account_id', $filter_data['pay_stub_entry_name_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'a.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list_with_all', $ph ) : null;

		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['country'] ) ) ? $this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['province'] ) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= '
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}
}

?>
