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
 * @package Modules\Punch
 * @implements IteratorAggregate<PunchControlFactory>
 */
class PunchControlListFactory extends PunchControlFactory implements IteratorAggregate {

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
	 * @return bool|PunchControlListFactory
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
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PunchControlListFactory
	 */
	function getByCompanyId( $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND ( a.deleted = 0 AND c.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @return bool|PunchControlListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 AND c.deleted = 0 )
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $punch_id UUID
	 * @param array $order     Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PunchControlListFactory
	 */
	function getByPunchId( $punch_id, $order = null ) {
		if ( $punch_id == '' ) {
			return false;
		}

		$pf = new PunchFactory();

		$ph = [
				'punch_id' => TTUUID::castUUID( $punch_id ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $pf->getTable() . ' as b
					where	a.id = b.punch_control_id
						AND b.id = ?
						AND ( a.deleted = 0 AND b.deleted=0 )
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PunchControlListFactory
	 */
	function getByUserIdAndDateStamp( $user_id, $date_stamp, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $date_stamp == '' ) {
			return false;
		}

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'date_stamp' => $this->db->BindDate( $date_stamp ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where
						a.user_id = ?
						AND a.date_stamp = ?
						AND ( a.deleted = 0 )
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * This function grabs all the punches on the given day and determines where the epoch will fit in.
	 * @param string $user_id UUID
	 * @param int $epoch      EPOCH
	 * @param int $status_id
	 * @return bool|int|string
	 */
	function getInCompletePunchControlIdByUserIdAndEpoch( $user_id, $epoch, $status_id ) {
		Debug::text( ' Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_id == '' ) {
			return false;
		}

		if ( $epoch == '' ) {
			return false;
		}

		$plf = new PunchListFactory();
		$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch );
		if ( $plf->getRecordCount() > 0 ) {

			$punch_arr = [];
			$prev_punch_arr = [];
			//Check for gaps.
			$prev_time_stamp = 0;
			foreach ( $plf as $p_obj ) {
				if ( $p_obj->getStatus() == 10 ) {
					$punch_arr[$p_obj->getPunchControlId()]['in'] = $p_obj->getTimeStamp();
				} else {
					$punch_arr[$p_obj->getPunchControlId()]['out'] = $p_obj->getTimeStamp();
				}

				if ( $prev_time_stamp != 0 ) {
					$prev_punch_arr[$p_obj->getTimeStamp()] = $prev_time_stamp;
				}

				$prev_time_stamp = $p_obj->getTimeStamp();
			}
			unset( $prev_time_stamp );

			if ( isset( $prev_punch_arr ) ) {
				$next_punch_arr = array_flip( $prev_punch_arr );
			}

			//Debug::Arr( $punch_arr, ' Punch Array: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr( $next_punch_arr, ' Next Punch Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( empty( $punch_arr ) == false ) {
				$i = 0;
				foreach ( $punch_arr as $punch_control_id => $data ) {
					$found_gap = false;
					Debug::text( ' Iteration: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );

					//Skip complete punch control rows.
					if ( isset( $data['in'] ) && isset( $data['out'] ) ) {
						Debug::text( ' Punch Control ID is Complete: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						//Make sure we don't assign a In punch that comes AFTER an Out punch to the same pair.
						//As well the opposite, an Out punch that comes BEFORE an In punch to the same pair.
						if ( $status_id == 10 && !isset( $data['in'] ) && ( isset( $data['out'] ) && $epoch <= $data['out'] ) ) {
							Debug::text( ' aFound Valid Gap...', __FILE__, __LINE__, __METHOD__, 10 );
							$found_gap = true;
						} else if ( $status_id == 20 && !isset( $data['out'] ) && ( isset( $data['in'] ) && $epoch >= $data['in'] ) ) {
							Debug::text( ' bFound Valid Gap...', __FILE__, __LINE__, __METHOD__, 10 );
							$found_gap = true;
						} else {
							Debug::text( ' No Valid Gap Found...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}

					if ( $found_gap == true ) {
						if ( $status_id == 10 ) { //In Gap
							Debug::text( ' In Gap...', __FILE__, __LINE__, __METHOD__, 10 );
							if ( isset( $prev_punch_arr[$data['out']] ) ) {
								Debug::text( ' Punch Before In Gap... Range Start: ' . TTDate::getDate( 'DATE+TIME', $prev_punch_arr[$data['out']] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $data['out'] ), __FILE__, __LINE__, __METHOD__, 10 );
								if ( $prev_punch_arr[$data['out']] == $data['out'] || TTDate::isTimeOverLap( $epoch, $epoch, $prev_punch_arr[$data['out']], $data['out'] ) ) {
									Debug::text( ' Epoch overlaps, THIS IS GOOD!', __FILE__, __LINE__, __METHOD__, 10 );
									Debug::text( ' aReturning Punch Control ID: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
									$retval = $punch_control_id;
									break; //Without this adding mass punches fails in some basic circumstances because it loops and attaches to a later punch control
								} else {
									Debug::text( ' Epoch does not overlap, cant attach to this punch_control!', __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								//No Punch After
								Debug::text( ' NO Punch Before In Gap...', __FILE__, __LINE__, __METHOD__, 10 );
								$retval = $punch_control_id;
								break;
							}
						} else { //Out Gap
							Debug::text( ' Out Gap...', __FILE__, __LINE__, __METHOD__, 10 );
							//Start: $data['in']
							//End: $data['in']
							if ( isset( $next_punch_arr[$data['in']] ) ) {
								Debug::text( ' Punch After Out Gap... Range Start: ' . TTDate::getDate( 'DATE+TIME', $data['in'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $next_punch_arr[$data['in']] ), __FILE__, __LINE__, __METHOD__, 10 );
								if ( $data['in'] == $next_punch_arr[$data['in']] || TTDate::isTimeOverLap( $epoch, $epoch, $data['in'], $next_punch_arr[$data['in']] ) ) {
									Debug::text( ' Epoch overlaps, THIS IS GOOD!', __FILE__, __LINE__, __METHOD__, 10 );
									Debug::text( ' bReturning Punch Control ID: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
									$retval = $punch_control_id;
									break; //Without this adding mass punches fails in some basic circumstances because it loops and attaches to a later punch control
								} else {
									Debug::text( ' Epoch does not overlap, cant attach to this punch_control!', __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								//No Punch After
								Debug::text( ' NO Punch After Out Gap...', __FILE__, __LINE__, __METHOD__, 10 );
								$retval = $punch_control_id;
								break;
							}
						}
					}
					$i++;
				}
			}
		}

		if ( isset( $retval ) ) {
			Debug::text( ' Returning Punch Control ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		Debug::text( ' Returning FALSE No Valid Gaps Found...', __FILE__, __LINE__, __METHOD__, 10 );

		//FALSE means no gaps in punch control rows found.
		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|PunchControlListFactory
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

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = [ 'first_name', 'last_name', 'date_stamp', 'time_stamp', 'type_id', 'status_id', 'branch', 'department', 'default_branch', 'default_department', 'group', 'title' ];
		if ( $order == null ) {
			$order = [ 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['exclude_user_ids'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset( $filter_data['user_id'] ) ) {
			$filter_data['id'] = $filter_data['user_id'];
		}
		if ( isset( $filter_data['include_user_ids'] ) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset( $filter_data['user_status_ids'] ) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset( $filter_data['user_title_ids'] ) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset( $filter_data['group_ids'] ) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset( $filter_data['branch_ids'] ) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset( $filter_data['department_ids'] ) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset( $filter_data['punch_branch_ids'] ) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset( $filter_data['punch_department_ids'] ) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset( $filter_data['exclude_job_ids'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
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

		$uf = new UserFactory();
		$uwf = new UserWageFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select
							b.id as id,
							b.branch_id as branch_id,
							j.name as branch,
							b.department_id as department_id,
							k.name as department,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity,
							b.bad_quantity as bad_quantity,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.custom_field as custom_field,
							b.note as note,

							b.user_id as user_id,
							b.date_stamp as date_stamp,
							b.pay_period_id as pay_period_id,

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

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						x.name as job_name,
						x.name as job,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id,
						y.name as job_item ';
		}

		$query .= '
					from	' . $this->getTable() . ' as b
							LEFT JOIN ' . $uf->getTable() . ' as d ON b.user_id = d.id

							LEFT JOIN ' . $bf->getTable() . ' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN ' . $ugf->getTable() . ' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN ' . $utf->getTable() . ' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN ' . $bf->getTable() . ' as j ON ( b.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as k ON ( b.department_id = k.id AND k.deleted = 0)

							LEFT JOIN ' . $uwf->getTable() . ' as z ON z.id = (select z.id
																		from ' . $uwf->getTable() . ' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc LiMiT 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN ' . $jf->getTable() . ' as x ON b.job_id = x.id';
			$query .= '	LEFT JOIN ' . $jif->getTable() . ' as y ON b.job_item_id = y.id';
		}

		$query .= '	WHERE d.company_id = ?';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'b.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'd.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['punch_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.branch_id', $filter_data['punch_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['punch_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.department_id', $filter_data['punch_department_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_period_ids'] ) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_ids'], 'uuid_list', $ph ) : null;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= $this->getCustomFieldWhereSQL( $company_id, 'a.custom_field', $filter_data, $ph );
			$query .= ( isset( $filter_data['include_job_id'] ) ) ? $this->getWhereClauseSQL( 'b.job_id', $filter_data['include_job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['exclude_job_id'] ) ) ? $this->getWhereClauseSQL( 'b.job_id', $filter_data['exclude_job_id'], 'not_uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_group_id'] ) ) ? $this->getWhereClauseSQL( 'x.group_id', $filter_data['job_group_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'b.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['punch_tag_id'] ) ) ? $this->getWhereClauseSQL( 'b.punch_tag_id', $filter_data['punch_tag_id'], 'jsonb_uuid_array', $ph ) : null;
		}

		$query .= ( isset( $filter_data['has_note'] ) && $filter_data['has_note'] == true ) ? ' AND b.note != \'\'' : null;

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( (int)TTDate::parseDateTime( $filter_data['start_date'] ) );
			$query .= ' AND b.date_stamp >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( (int)TTDate::parseDateTime( $filter_data['end_date'] ) );
			$query .= ' AND b.date_stamp <= ?';
		}

		$query .= ' AND ( b.deleted = 0 AND d.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}
}

?>