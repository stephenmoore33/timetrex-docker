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
 * @implements IteratorAggregate<RemittanceDestinationAccountFactory>
 */
class RemittanceDestinationAccountListFactory extends RemittanceDestinationAccountFactory implements IteratorAggregate {

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
	 * @return bool|RemittanceDestinationAccountListFactory
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
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RemittanceDestinationAccountListFactory
	 */
	function getByRemittanceSourceAccountId( $id, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'remittance_source_account_id' => TTUUID::castUUID( $id ),
		];

		$uf = new UserFactory();

		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON ( uf.id = a.user_id )
					WHERE	a.remittance_source_account_id = ?
						AND ( a.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $status_id   Status
	 * @param int $limit   Limit the number of records returned
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RemittanceDestinationAccountListFactory
	 */
	function getByRemittanceSourceAccountIdAndStatusId( $id, $status_id, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		$ph = [
				'remittance_source_account_id' => TTUUID::castUUID( $id ),
				'status_id' => (int)$status_id,
		];

		$uf = new UserFactory();

		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON ( uf.id = a.user_id )
					WHERE	a.remittance_source_account_id = ?
						AND a.status_id = ?
						AND ( a.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $id              UUID
	 * @param string $legal_entity_id UUID
	 * @param int $limit              Limit the number of records returned
	 * @param array $where            Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order            Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RemittanceDestinationAccountListFactory
	 */
	function getByRemittanceSourceAccountIdAndNotUserLegalEntityId( $id, $legal_entity_id, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $legal_entity_id == '' ) {
			return false;
		}

		$ph = [
				'remittance_source_account_id' => TTUUID::castUUID( $id ),
				'legal_entity_id'              => TTUUID::castUUID( $legal_entity_id ),
		];

		$uf = new UserFactory();

		$query = '
					select	*
					from	' . $this->getTable() . ' as rdaf
					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( rdaf.user_id = uf.id ) 
					where	rdaf.remittance_source_account_id = ?
						AND uf.legal_entity_id != ?
						AND ( rdaf.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $status_id
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RemittanceDestinationAccountListFactory
	 */
	function getByUserIdAndStatusId( $user_id, $status_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			//Similar sort order at PayStubTransactionListFactory::getByPayStubId()
			$order = [ 'amount_type_id' => 'desc', 'priority' => 'asc', 'type_id' => 'desc', 'id' => 'asc' ]; //Fixed amounts first, then percent, always use ID as tie breaker so its consistent.
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'status_id' => (int)$status_id,
				'user_id'   => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	status_id = ?
						AND user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getByUserIdAndCompany( $user_id, $company_id, $where = null, $order = null ) {
		if ( $order == null ) {
			$order = [ 'amount_type_id' => 'desc', 'priority' => 'asc', 'id' => 'asc' ]; //Fixed amounts first, then percent, always use ID as tie breaker so its consistent.
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'user_id'    => TTUUID::castUUID( $user_id ),
		];

		$uf = new UserFactory();

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = ?
						AND a.user_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RemittanceDestinationAccountListFactory
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
					select a.*,
						uf.legal_entity_id
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	a.id = ?
						AND uf.company_id = ?
						AND ( a.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
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
	 * @return bool|RemittanceDestinationAccountListFactory
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

		$additional_order_fields = [ 'status_id', 'user_first_name', 'user_last_name', 'uf.employee_number', 'a.amount', 'in_use' ];

		$sort_column_aliases = [
				'status'                    => 'status_id',
				'type'                      => 'type_id',
				'amount_type'               => 'amount_type_id',
				'remittance_source_account' => 'remittance_source_account_id',
				'display_amount'            => 'a.amount',
				'user_employee_number'      => 'uf.employee_number',
				'ach_transaction_type'      => false,
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'status_id' => 'asc', 'amount_type_id' => 'desc', 'priority' => 'asc' ];
			$strict = false;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset( $order['status_id'] ) ) {
				$order = Misc::prependArray( [ 'status_id' => 'asc' ], $order );
			}
			//Always sort by last name, first name after other columns

			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$lef = new LegalEntityFactory();
		$rsaf = new RemittanceSourceAccountFactory();
		$pstf = new PayStubTransactionFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							(
								CASE WHEN EXISTS ( select 1 from ' . $pstf->getTable() . ' as w where w.remittance_destination_account_id = a.id and a.deleted = 0 ) THEN 1 ELSE 0 END
							) as in_use,					
							rsaf.name as remittance_source_account,						
							uf.legal_entity_id as legal_entity_id,
							rsaf.legal_entity_id as remittance_source_account_legal_entity_id,
							uf.employee_number as user_employee_number,
							uf.first_name as user_first_name,
							uf.last_name as user_last_name,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN ' . $rsaf->getTable() . ' as rsaf ON ( a.remittance_source_account_id = rsaf.id AND rsaf.deleted = 0 )
						LEFT JOIN ' . $lef->getTable() . ' as lef ON ( rsaf.legal_entity_id = lef.id AND lef.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	uf.company_id = ?';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'uf.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null; //Filter based on employee record, so when migrating employees to new legal entities, its easier to find destination accounts that haven't been migrated yet.
		$query .= ( isset( $filter_data['remittance_source_account_legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'rsaf.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['remittance_source_account_id'] ) ) ? $this->getWhereClauseSQL( 'a.remittance_source_account_id', $filter_data['remittance_source_account_id'], 'uuid_list', $ph ) : null;

		if ( isset( $filter_data['status'] ) && !is_array( $filter_data['status'] ) && trim( $filter_data['status'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions( 'status' ) );
		}
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		if ( isset( $filter_data['type'] ) && !is_array( $filter_data['type'] ) && trim( $filter_data['type'] ) != '' && !isset( $filter_data['status_id'] ) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions( 'type' ) );
		}
		$query .= ( isset( $filter_data['type_id'] ) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['name'] ) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['value2'] ) ) ? $this->getWhereClauseSQL( 'a.value2', $filter_data['value2'], 'text', $ph ) : null;
		$query .= ( isset( $filter_data['created_date'] ) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['updated_date'] ) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date'], 'date_range', $ph ) : null;


		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );


		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '.$query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

}

?>
