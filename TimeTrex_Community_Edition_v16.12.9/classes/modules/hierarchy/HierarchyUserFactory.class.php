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
 * @package Modules\Hierarchy
 */
class HierarchyUserFactory extends Factory {
	protected $table = 'hierarchy_user';
	protected $pk_sequence_name = 'hierarchy_user_id_seq'; //PK Sequence name

	var $hierarchy_control_obj = null;
	var $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'hierarchy_control_id' )->setFunctionMap( 'HierarchyControl' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			//No API Methods.
		}

		return $schema_data;
	}

	/**
	 * @return null
	 */
	function getHierarchyControlObject() {
		if ( is_object( $this->hierarchy_control_obj ) ) {
			return $this->hierarchy_control_obj;
		} else {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$this->hierarchy_control_obj = $hclf->getById( $this->getHierarchyControl() )->getCurrent();

			return $this->hierarchy_control_obj;
		}
	}

	/**
	 * @return bool|null
	 */
	function getUserObject() {
		if ( is_object( $this->user_obj ) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getHierarchyControl() {
		return $this->getGenericDataValue( 'hierarchy_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHierarchyControl( $value ) {
		$value = TTUUID::castUUID( $value );

		//This is a sub-class, need to support setting HierachyControlID before its created.
		return $this->setGenericDataValue( 'hierarchy_control_id', $value );
	}

	/**
	 * @param string $id UUID
	 * @param int $exclude_id
	 * @return bool
	 */
	function isUniqueUser( $id, $exclude_id = 0 ) {
		if ( $exclude_id === 0 ) {
			$exclude_id = TTUUID::getZeroID();
		}
		$hcf = TTnew( 'HierarchyControlFactory' ); /** @var HierarchyControlFactory $hcf */
		$hotf = TTnew( 'HierarchyObjectTypeFactory' ); /** @var HierarchyObjectTypeFactory $hotf */

		$ph = [
				'hierarchy_control_id' => $this->getHierarchyControl(),
				'id'                   => $id,
				'exclude_id'           => TTUUID::castUUID( $exclude_id ),
		];

		//$query = 'select a.id from '. $this->getTable() .' as a, '. $pglf->getTable() .' as b where a.hierarchy_control_id = b.id AND a.user_id = ? AND b.deleted=0';
		$query = '
					select *
					from ' . $hotf->getTable() . ' as a
					LEFT JOIN ' . $this->getTable() . ' as b ON a.hierarchy_control_id = b.hierarchy_control_id
					LEFT JOIN ' . $hcf->getTable() . ' as c ON a.hierarchy_control_id = c.id
					WHERE a.object_type_id in (
							select object_type_id
							from hierarchy_object_type
							where hierarchy_control_id = ? )
					AND b.user_id = ?
					AND a.hierarchy_control_id != ?
					AND c.deleted = 0
				';
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
		$user_id = $this->db->GetOne( $query, $ph );

		if ( $user_id === false ) {
			return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Hierarchy Control
		if ( $this->getHierarchyControl() == '' || $this->getHierarchyControl() == TTUUID::getZeroID() ) {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$this->Validator->isResultSetWithRows( 'hierarchy_control_id',
												   $hclf->getByID( $this->getHierarchyControl() ),
												   TTi18n::gettext( 'Invalid Hierarchy Control' )
			);
		}

		// Selected Employee
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Selected Employee is invalid' )
		);
		/*
		//Allow superiors to be assigned as subordinates in the same hierarchy to make it easier to administer hierarchies
		//that have superiors sharing responsibility.
		//For example Super1 and Super2 look after 10 subordinates as well as each other. This would require 3 hierarchies normally,
		//but if we allow Super1 and Super2 to be subordinates in the same hierarchy, it can be done with a single hierarchy.
		//The key with this though is to have Permission->getPermissionChildren() *not* return the current user, even if they are a subordinates,
		//as that could cause a conflict with view_own and view_child permissions (as a child would imply view_own)
		AND
		$this->Validator->isNotResultSetWithRows(	'user',
													$hllf->getByHierarchyControlIdAndUserId( $this->getHierarchyControl(), $id ),
													TTi18n::gettext('Selected Employee is assigned as both a superior and subordinate')
													)
*/
		if ( $this->Validator->isError( 'user' ) == false ) {
			$this->Validator->isTrue( 'user',
									  $this->isUniqueUser( $this->getUser() ),
									  TTi18n::gettext( 'Selected Employee is already assigned to another hierarchy' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		return true;
	}

	//This table doesn't have any of these columns, so overload the functions.

	/**
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object( $u_obj ) ) {
			return TTLog::addEntry( $this->getHierarchyControl(), $log_action, TTi18n::getText( 'Subordinate' ) . ': ' . $u_obj->getFullName( false, true ), null, $this->getTable() );
		}

		return false;
	}
}

?>
