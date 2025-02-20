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
 * @package Modules\Qualification
 * @implements IteratorAggregate<UserMembershipFactory>
 */
class UserMembershipListFactory extends UserMembershipFactory implements IteratorAggregate {

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
	 * @return bool|UserMembershipListFactory
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
	 * @param string $user_id UUID
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserMembershipListFactory
	 */
	function getByUserId( $user_id, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserMembershipListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$qf = new QualificationFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN  ' . $qf->getTable() . ' as b on a.qualification_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserMembershipListFactory
	 */
	function getByCompanyId( $company_id, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$qf = new QualificationFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN  ' . $qf->getTable() . ' as b on a.qualification_id = b.id
					where	b.company_id = ?
						AND a.deleted = 0';
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
	 * @return bool|UserMembershipListFactory
	 */
	function getByIdAndUserId( $id, $user_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$cache_id = $id . $user_id;
		$this->rs = $this->getCache( $cache_id );
		if ( $this->rs === false ) {
			$ph = [
					'id'      => TTUUID::castUUID( $id ),
					'user_id' => TTUUID::castUUID( $user_id ),
			];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where	id = ?
							AND user_id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $cache_id );
		}

		return $this;
	}

	/**
	 * @param string $user_id          UUID
	 * @param string $qualification_id UUID
	 * @param array $where             Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order             Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserMembershipListFactory
	 */
	function getByUserIdAndQualificationId( $user_id, $qualification_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		$cache_id = $user_id . $qualification_id;
		$this->rs = $this->getCache( $cache_id );
		if ( $this->rs === false ) {
			$ph = [
					'user_id'          => TTUUID::castUUID( $user_id ),
					'qualification_id' => TTUUID::castUUID( $qualification_id ),
			];

			$query = '
						select	*
						from	' . $this->getTable() . '
						where	user_id = ?
							AND qualification_id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache( $this->rs, $cache_id );
		}

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserMembershipListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {

		if ( $company_id == '' ) {
			return false;
		}

		if ( isset( $filter_data['include_user_id'] ) ) {
			$filter_data['user_id'] = $filter_data['include_user_id'];
		}
		if ( isset( $filter_data['exclude_user_id'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		if ( isset( $filter_data['qualification_group_id'] ) ) {
			$filter_data['group_id'] = $filter_data['qualification_group_id'];
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'uf.first_name', 'uf.last_name', 'qf.name', 'ownership_id', 'cf.name', 'qgf.name', 'default_branch', 'default_department', 'user_group', 'title' ];

		$sort_column_aliases = [
				'ownership' => 'ownership_id',
		];

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == null ) {
			$order = [ 'qf.name' => 'asc', 'qgf.name' => 'asc' ];
			$strict = false;
		} else {
			if ( isset( $order['first_name'] ) ) {
				$order['uf.first_name'] = $order['first_name'];
				unset( $order['first_name'] );
			}
			if ( isset( $order['last_name'] ) ) {
				$order['uf.last_name'] = $order['last_name'];
				unset( $order['last_name'] );
			}
			if ( isset( $order['qualification'] ) ) {
				$order['qf.name'] = $order['qualification'];
				unset( $order['qualification'] );
			}
			if ( isset( $order['currency'] ) ) {
				$order['cf.name'] = $order['currency'];
				unset( $order['currency'] );
			}
			if ( isset( $order['group'] ) ) {
				$order['qgf.name'] = $order['group'];
				unset( $order['group'] );
			}
			$strict = true;
		}

		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$qf = new QualificationFactory();
		$cf = new CurrencyFactory();
		$usf = new UserSkillFactory();
		$ulf = new UserLanguageFactory();
		$qgf = new QualificationGroupFactory();
		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							uf.first_name as first_name,
							uf.last_name as last_name,
							qf.name as qualification,
							qgf.name as "group",

							bf.id as default_branch_id,
							bf.name as default_branch,
							df.id as default_department_id,
							df.name as default_department,
							ugf.id as user_group_id,
							ugf.name as user_group,
							utf.id as user_title_id,
							utf.name as title,

							cf.name as currency,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0)
						LEFT JOIN ' . $usf->getTable() . ' as usf ON	( a.qualification_id = usf.qualification_id AND usf.deleted = 0)
						LEFT JOIN ' . $ulf->getTable() . ' as ulf ON ( a.qualification_id = ulf.qualification_id AND ulf.deleted =0 )
						LEFT JOIN ' . $cf->getTable() . ' as cf ON ( a.currency_id = cf.id AND cf.deleted = 0 )
						LEFT JOIN ' . $qf->getTable() . ' as qf ON ( a.qualification_id = qf.id  AND qf.deleted = 0 )
						LEFT JOIN ' . $bf->getTable() . ' as bf ON ( uf.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as df ON ( uf.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as ugf ON ( uf.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as utf ON ( uf.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN ' . $qgf->getTable() . ' as qgf ON ( qf.group_id = qgf.id AND qgf.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )

					where	qf.company_id = ?';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		if ( isset( $filter_data['ownership'] ) && !is_array( $filter_data['ownership'] ) && trim( $filter_data['ownership'] ) != '' && !isset( $filter_data['ownership_id'] ) ) {
			$filter_data['ownership_id'] = Option::getByFuzzyValue( $filter_data['ownership'], $this->getOptions( 'ownership' ) );
		}

		$query .= ( isset( $filter_data['qualification_id'] ) ) ? $this->getWhereClauseSQL( 'a.qualification_id', $filter_data['qualification_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['qualification'] ) ) ? $this->getWhereClauseSQL( 'qf.name', $filter_data['qualification'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['proficiency_id'] ) ) ? $this->getWhereClauseSQL( 'usf.proficiency_id', $filter_data['proficiency_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['fluency_id'] ) ) ? $this->getWhereClauseSQL( 'ulf.fluency_id', $filter_data['fluency_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['competency_id'] ) ) ? $this->getWhereClauseSQL( 'ulf.competency_id', $filter_data['competency_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['ownership_id'] ) ) ? $this->getWhereClauseSQL( 'a.ownership_id', $filter_data['ownership_id'], 'numeric_list', $ph ) : null;

		$query .= ( isset( $filter_data['currency_id'] ) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['currency'] ) ) ? $this->getWhereClauseSQL( 'cf.name', $filter_data['currency'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['source_type_id'] ) ) ? $this->getWhereClauseSQL( 'qf.source_type_id', $filter_data['source_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'qf.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['group'] ) ) ? $this->getWhereClauseSQL( 'qgf.name', $filter_data['group'], 'text', $ph ) : null;

		$query .= ( isset( $filter_data['qualification_type_id'] ) ) ? $this->getWhereClauseSQL( 'qf.type_id', $filter_data['qualification_type_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['tag'] ) ) ? $this->getWhereClauseSQL( 'a.id', [ 'company_id' => TTUUID::castUUID( $company_id ), 'object_type_id' => 255, 'tag' => $filter_data['tag'] ], 'tag', $ph ) : null;

		$query .= ( isset( $filter_data['renewal_date'] ) ) ? $this->getWhereClauseSQL( 'a.renewal_date', $filter_data['renewal_date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.start_date', $filter_data['start_date'], 'date_range', $ph ) : null;

		$query .= ( isset( $filter_data['membership_renewal_start_date'] ) ) ? $this->getWhereClauseSQL( 'a.renewal_date', $filter_data['membership_renewal_start_date'], 'start_date', $ph ) : null;
		$query .= ( isset( $filter_data['membership_renewal_end_date'] ) ) ? $this->getWhereClauseSQL( 'a.renewal_date', $filter_data['membership_renewal_end_date'], 'end_date', $ph ) : null;
		/*
				if ( isset($filter_data['membership_renewal_start_date']) AND !is_array($filter_data['membership_renewal_start_date']) AND trim($filter_data['membership_renewal_start_date']) != '' ) {
					$ph[] = (int)$filter_data['membership_renewal_start_date'];
					$query	.=	' AND a.renewal_date >= ?';
				}
				if ( isset($filter_data['membership_renewal_end_date']) AND !is_array($filter_data['membership_renewal_end_date']) AND trim($filter_data['membership_renewal_end_date']) != '' ) {
					$ph[] = (int)$filter_data['membership_renewal_end_date'];
					$query	.=	' AND a.renewal_date <= ?';
				}
		*/
		$query .= ( isset( $filter_data['created_date'] ) ) ? $this->getWhereClauseSQL( 'a.created_date', $filter_data['created_date'], 'date_range', $ph ) : null;
		$query .= ( isset( $filter_data['updated_date'] ) ) ? $this->getWhereClauseSQL( 'a.updated_date', $filter_data['updated_date'], 'date_range', $ph ) : null;

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
