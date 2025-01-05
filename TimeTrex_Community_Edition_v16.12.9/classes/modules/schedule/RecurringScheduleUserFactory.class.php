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
 * @package Modules\Schedule
 */
class RecurringScheduleUserFactory extends Factory {
	protected $table = 'recurring_schedule_user';
	protected $pk_sequence_name = 'recurring_schedule_user_id_seq'; //PK Sequence name

	var $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			//TODO: No database table exists for this factory?
			//$schema_data->setColumns(
			//		TTSCols::new(
			//				TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
			//				TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
			//				TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
			//				TTSCol::new( 'status' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),
			//
			//				TTSCol::new( 'manual_id' )->setFunctionMap( 'ManualID' )->setType( 'integer' ),
			//				TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' ),
			//				TTSCol::new( 'name_metaphone' )->setFunctionMap( 'NameMetaphone' )->setType( 'varchar' )->setIsUserVisible( false ),
			//
			//				TTSCol::new( 'geo_fence_ids' )->setFunctionMap( 'GEOFenceIds' )->setType( 'uuid' ),
			//
			//				TTSCol::new( 'user_group_selection_type_id' )->setFunctionMap( 'UserGroupSelectionType' )->setType( 'smallint' ),
			//				TTSCol::new( 'user_group_ids' )->setFunctionMap( 'UserGroup' )->setType( 'uuid' )->setIsSynthetic( true ),
			//				TTSCol::new( 'include_user_ids' )->setFunctionMap( 'IncludeUser' )->setType( 'uuid' )->setIsSynthetic( true ),
			//				TTSCol::new( 'exclude_user_ids' )->setFunctionMap( 'ExcludeUser' )->setType( 'uuid' )->setIsSynthetic( true ),
			//
			//				TTSCol::new( 'user_title_selection_type_id' )->setFunctionMap( 'UserTitleSelectionType' )->setType( 'smallint' ),
			//				TTSCol::new( 'user_title_ids' )->setFunctionMap( 'UserTitle' )->setType( 'uuid' )->setIsSynthetic( true ),
			//
			//				TTSCol::new( 'user_punch_branch_selection_type_id' )->setFunctionMap( 'UserPunchBranchSelectionType' )->setType( 'smallint' ),
			//				TTSCol::new( 'user_punch_branch_ids' )->setFunctionMap( 'UserPunchBranch' )->setType( 'uuid' )->setIsSynthetic( true ),
			//
			//				TTSCol::new( 'user_default_department_selection_type_id' )->setFunctionMap( 'UserDefaultDepartmentSelectionType' )->setType( 'smallint' ),
			//				TTSCol::new( 'user_default_department_ids' )->setFunctionMap( 'UserDefaultDepartment' )->setType( 'uuid' )->setIsSynthetic( true ),
			//				TTSCol::new( 'include_user_default_department_id' )->setFunctionMap( 'IncludeUserDefaultDepartment' )->setType( 'uuid' )->setIsSynthetic( true ),
			//
			//				TTSCol::new( 'tag' )->setFunctionMap( 'Tag' )->setType( 'string' )->setIsSynthetic( true ),
			//		)->addCreatedAndUpdated()->addDeleted()
			//);
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
	 * @return bool|mixed
	 */
	function getRecurringScheduleControl() {
		return $this->getGenericDataValue( 'recurring_schedule_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringScheduleControl( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'recurring_schedule_control_id', $value );
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
		// Recurring Schedule
		$this->Validator->isUUID( 'recurring_schedule_control_id',
								  $this->getRecurringScheduleControl(),
								  TTi18n::gettext( 'Recurring Schedule is invalid' )
		/*
		$this->Validator->isResultSetWithRows(			'recurring_schedule',
														$rsclf->getByID($id),
														TTi18n::gettext('Recurring Schedule is invalid')
		*/
		);
		// Selected Employee
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Selected Employee is invalid' )
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
			return TTLog::addEntry( $this->getRecurringScheduleControl(), $log_action, TTi18n::getText( 'Employee' ) . ': ' . $u_obj->getFullName( false, true ), null, $this->getTable() );
		}

		return false;
	}
}

?>
