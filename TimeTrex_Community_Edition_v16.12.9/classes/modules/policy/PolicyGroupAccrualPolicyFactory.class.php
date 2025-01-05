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
 * @package Modules\Policy
 */
class PolicyGroupAccrualPolicyFactory extends Factory {
	protected $table = 'policy_group_accrual_policy';
	protected $pk_sequence_name = 'policy_group_accrual_policy_id_seq'; //PK Sequence name

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			//TODO: No database table exists for this factory?
			//$schema_data->setColumns(
			//		TTSCols::new(
			//				TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
			//				TTSCol::new( 'policy_group_id' )->setFunctionMap( 'PolicyGroup' )->setType( 'uuid' )->setIsNull( false ),
			//				TTSCol::new( 'accrual_policy_id' )->setFunctionMap( 'AccrualPolicy' )->setType( 'uuid' )->setIsNull( false ),
			//				TTSCol::new( 'priority' )->setFunctionMap( 'Priority' )->setType( 'integer' )->setIsNull( false ),
			//				TTSCol::new( 'is_default' )->setFunctionMap( 'IsDefault' )->setType( 'boolean' )->setIsNull( false ),
			//				TTSCol::new( 'is_active' )->setFunctionMap( 'IsActive' )->setType( 'boolean' )->setIsNull( false ),
			//				TTSCol::new( 'is_deleted' )->setFunctionMap( 'IsDeleted' )->setType( 'boolean' )->setIsNull( false ),
			//				TTSCol::new( 'created_at' )->setFunctionMap( 'CreatedAt' )->setType( 'datetime' )->setIsNull( false ),
			//				TTSCol::new( 'updated_at' )->setFunctionMap( 'UpdatedAt' )->setType( 'datetime' )->setIsNull( false )
			//		)
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
	function getPolicyGroup() {
		return $this->getGenericDataValue( 'policy_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPolicyGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'policy_group_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getAccrualPolicy() {
		return $this->getGenericDataValue( 'accrual_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAccrualPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_id', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Policy Group
		$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
		$this->Validator->isResultSetWithRows( 'policy_group',
											   $pglf->getByID( $this->getPolicyGroup() ),
											   TTi18n::gettext( 'Policy Group is invalid' )
		);
		// Accrual Policy
		if ( $this->getAccrualPolicy() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows( 'over_time_policy',
												   $aplf->getByID( $this->getAccrualPolicy() ),
												   TTi18n::gettext( 'Selected Accrual Policy is invalid' )
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
}

?>
