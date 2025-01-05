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
 * @package Modules\SystemJobQueue
 * @implements IteratorAggregate<SystemJobQueueFactory>
 */
class SystemJobQueueListFactory extends SystemJobQueueFactory implements IteratorAggregate {

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
					from	' . $this->getTable() . '';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @return $this
	 */
	function getOldestPendingJob( $threshold = 3600 ) {
		$threshold_epoch = ( time() - (int)$threshold );

		//Must check the effective_date and queued_date, because when editing a schedule, its possible
		// the job gets queued now, with an effective date of 4 hours ago (start time of schedule).
		//This shouldn't trigger an old pending job of course.
		$query = '
					select	*
					from	' . $this->getTable() . '
					WHERE status_id = 10
						AND ( queued_date < '. $threshold_epoch .' AND effective_date < '. $threshold_epoch .' )
					ORDER BY effective_date ASC
					LIMIT 1';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query );
		//Debug::Query( $query, [], __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|SystemJobQueueListFactory
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
					where	id = ?';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $user_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|SystemJobQueueListFactory
	 */
	function getByIdAndUserId( $id, $user_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		$ph = [
				'user_id' => TTUUID::castUUID( $user_id ),
				'id'      => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND	id = ?
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id UUID
	 * @param int|int[] $status      INTEGER
	 * @param null $limit
	 * @param null $where        Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|SystemJobQueueListFactory
	 */
	function getByCompanyIdAndUserIdAndStatus( $company_id, $id, $status, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id )
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	a.status_id in (' . $this->getListSQL( $status, $ph, 'int' ) . ')
						AND (b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|SystemJobQueueListFactory
	 */
	function getByCompanyIdAndUserIdAndStatusAndClassAndMethodAndEffectiveDate( $company_id, $id, $status, $class, $method, $effective_date = 0, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		if ( $class == '' ) {
			return false;
		}

		if ( $method == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'company_id' => TTUUID::castUUID( $company_id ),
				'class'      => (string)strtolower( $class ),
				'method'     => (string)strtolower( $method ),
				'effective_date' => (int)$effective_date,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	lower( a.class ) = ?
						AND	lower( a.method ) = ?
						AND a.effective_date >= ?
						AND a.status_id in (' . $this->getListSQL( $status, $ph, 'int' ) . ')
						AND ( b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit  );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param string|string[] $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|SystemJobQueueListFactory
	 */
	function getByUserIdAndStatusAndClassAndMethodAndEffectiveDate( $id, $status, $class, $method, $effective_date = 0, $limit = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $status == '' ) {
			return false;
		}

		if ( $class == '' ) {
			return false;
		}

		if ( $method == '' ) {
			return false;
		}

		$ph = [
				'id'         => TTUUID::castUUID( $id ),
				'class'      => (string)strtolower( $class ),
				'method'     => (string)strtolower( $method ),
				'effective_date' => (int)$effective_date,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where
						a.user_id = ?
						AND	lower( a.class ) = ?
						AND	lower( a.method ) = ?
						AND a.effective_date >= ?
						AND a.status_id in (' . $this->getListSQL( $status, $ph, 'int' ) . ')';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit  );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param string|array $ids UUID
	 * @return bool
	 * @throws DBError
	 */
	function deletePending( $user_id, $class, $method, $batch_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		if ( empty( $class ) ) {
			return false;
		}

		if ( empty( $method ) ) {
			return false;
		}

		if ( empty( $batch_id ) ) {
			return false;
		}

		$ph = [
				'user_id' => (string)$user_id,
				'class'   => (string)strtolower( $class ),
				'method'  => (string)strtolower( $method ),
				'batch_id' => (string)$batch_id,
		];

		$query = 'DELETE FROM ' . $this->getTable() . ' as a
			WHERE
				a.status_id = 10
				AND a.user_id = ?
				AND lower( a.class ) = ?
				AND lower( a.method ) = ?
				AND a.batch_id = ?';

		$this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Delete pending queue records... Affected Rows: ' . $this->getAffectedRows(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @param string|array $ids UUID
	 * @return bool
	 * @throws DBError
	 */
	function deletePendingDuplicates( $user_id, $class, $method ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		if ( empty( $class ) ) {
			return false;
		}

		if ( empty( $method ) ) {
			return false;
		}

		$ph = [
			'user_id' => (string)$user_id,
			'class'   => strtolower( (string)$class ),
			'method'  => strtolower( (string)$method ),
		];

		$query = 'DELETE FROM ' . $this->getTable() . ' as a
				USING ' . $this->getTable() . ' as b
			WHERE
				a.status_id = 10
				AND a.user_id = ?
				AND lower( a.class ) = ?
				AND lower( a.method ) = ?
				AND a.user_id = b.user_id
				AND lower( a.class ) = lower( b.class )
				AND lower( a.method ) = lower( b.method )
				AND a.effective_date = b.effective_date
				AND ( lower( a.arguments::text ) = lower( b.arguments::text ) )
				AND a.id < b.id';

		$this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Delete duplicate queue records... Affected Rows: ' . $this->getAffectedRows(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @return bool|SystemJobQueueListFactory
	 */
	function getPendingSystemJobsByCompanyIdAndUserid( $company_id, $user_id, $where = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'id'         => TTUUID::castUUID( $user_id ),
				'company_id' => TTUUID::castUUID( $company_id )
		];

		$query = '
					select	COUNT(*)
					from	' . $this->getTable() . ' as a,
							' . $uf->getTable() . ' as b
					where	a.user_id = b.id
						AND	a.user_id = ?
						AND b.company_id = ?
						AND	a.status_id in ( 10, 20 )
						AND a.effective_date < ' . time() . '
						AND (b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );

		$total = $this->db->GetOne( $query, $ph );

		return $total;
	}

	/**
	 * When using this it needs to be wrapped in a transaction and ->lock() needs to be called before committing the transaction. See getPendingAndLock() for a faster method without the need for transactions.
	 * @param $company_id
	 * @return $this|false
	 */
	function getPending( $epoch = null ) {
		if ( empty( $epoch ) ) {
			$epoch = time();
		}

		$ph = [
				'epoch' => (int)$epoch,
		];

		//Return pending jobs FOR UPDATE SKIP LOCKED.
		//  Order by batch_id so batches are handled together, and effective_date so its FIFO within the same priority.
		$query = '
					SELECT a.*
					FROM ' . $this->getTable() . ' as a
					WHERE a.status_id = 10
							AND ( a.effective_date < ? )
					ORDER BY batch_id ASC, priority ASC, effective_date ASC
					FOR UPDATE
					SKIP LOCKED
					LIMIT 1
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * Returns a count of running jobs.
	 * @param $company_id
	 * @return $this|false
	 */
	function getRunning() {
		$query = '
					SELECT count( a.* )
					FROM ' . $this->getTable() . ' as a
					WHERE a.status_id = 20
					';
		$total = $this->db->GetOne( $query );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( '  Total Running Jobs: '. $total, __FILE__, __LINE__, __METHOD__, 10 );

		return $total;
	}

	/**
	 * Transaction-less way of getting the next job out of the queue and marking it as being processed. (status_id=20)
	 * @param $company_id
	 * @return $this|false
	 */
	function getPendingAndLock( $epoch = null ) {
		if ( empty( $epoch ) ) {
			$epoch = time();
		}

		$ph = [
				'epoch' => (int)$epoch,
		];

		//Return pending jobs FOR UPDATE SKIP LOCKED.
		//  Order by batch_id so batches are handled together, and effective_date so its FIFO within the same priority.

		//'
		$query = '
					UPDATE system_job_queue SET status_id = 20, run_date = '. microtime( true ) .' 
					WHERE id = (				
						SELECT a.id
						FROM ' . $this->getTable() . ' as a
						WHERE a.status_id = 10
								AND ( a.effective_date < ? )
						ORDER BY batch_id ASC, priority ASC, effective_date ASC
						FOR UPDATE
						SKIP LOCKED
						LIMIT 1
					)
					RETURNING *
					';
		$this->rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}

	/**
	 * @param $company_id
	 * @return bool|array
	 */
	function getBatchStatus( $batch_id ) {
		if ( $batch_id == '' ) {
			return false;
		}

		$ph = [
				'batch_id' => TTUUID::castUUID( $batch_id ),
		];

		$query = '
					SELECT status_id, count(*) as total
					FROM ' . $this->getTable() . '
					WHERE batch_id = ?
					GROUP BY status_id
					ORDER BY status_id
					';
		$rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $rs->RecordCount() > 0 ) {
			$retarr['status_count'] = Misc::preSetArrayValues( [], array_keys( $this->getOptions('status') ), 0 );
			$retarr['is_completed'] = false;
			foreach ( $rs as $row ) {
				$retarr['status_count'][(int)$row['status_id']] = (int)$row['total'];
			}

			$retarr['total_iterations'] = array_sum( $retarr['status_count'] );
			$retarr['current_iteration'] = ( $retarr['status_count'][50] + $retarr['status_count'][100] );

			if ( $retarr['status_count'][10] == 0 && $retarr['status_count'][20] == 0 ) {
				$retarr['is_completed'] = true;
			}

			//Debug::Arr( $retarr, 'Job Queue Batch Status: ', __FILE__, __LINE__, __METHOD__, 10 );
			return $retarr;
		}

		return false;
	}

	/**
	 * @param $company_id
	 * @return $this|false
	 */
	function getQueueStatus() {

		$ph = [];

		$query = '
					SELECT status_id, count(*) as total
					FROM ' . $this->getTable() . '
					GROUP BY status_id
					ORDER BY status_id
					';
		$rs = $this->ExecuteSQL( $query, $ph );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $rs->RecordCount() > 0 ) {
			$retarr = Misc::preSetArrayValues( [], array_keys( $this->getOptions('status') ), 0 );
			foreach ( $rs as $row ) {
				$retarr[(int)$row['status_id']] = (int)$row['total'];
			}

			//Debug::Arr( $retarr, 'Job Queue Status: ', __FILE__, __LINE__, __METHOD__, 10 );
			return $retarr;
		}

		return false;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'status_id' => 'desc', 'effective_date' => 'desc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id )
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( a.user_id = b.id)
					WHERE b.company_id = ?
						AND a.effective_date < ' . time();

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;

		$query .= ' AND b.deleted = 0 ';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

		return $this;
	}
}