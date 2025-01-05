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
 * @package Modules\Accrual
 */
class AccrualBalanceFactory extends Factory {
	protected $table = 'accrual_balance';
	protected $pk_sequence_name = 'accrual_balance_id_seq'; //PK Sequence name

	var $user_obj = null;
	private $strict_validiation;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'accrual_policy_account_id' )->setFunctionMap( 'AccrualPolicyAccount' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'balance' )->setFunctionMap( 'Balance' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'banked_ytd' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'used_ytd' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'awarded_ytd' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'created_date' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'deleted' )->setType( 'smallint' )->setIsNull( false )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields?
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'user_status_id' )->setType( 'numeric_list' )->setColumn( 'b.status_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'accrual_policy_account_id' )->setType( 'uuid_list' )->setColumn( 'a.accrual_policy_account_id' )->setMulti( true ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'b.status_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'group' )->setType( 'text' )->setColumn( 'e.name' ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch' )->setType( 'text' )->setColumn( 'c.name' ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department' )->setType( 'text' )->setColumn( 'd.name' ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'title' )->setType( 'text' )->setColumn( 'f.name' ),
							TTSSearchField::new( 'first_name' )->setType( 'text_metaphone' )->setColumn( 'b.first_name' ),
							TTSSearchField::new( 'last_name' )->setType( 'text_metaphone' )->setColumn( 'b.last_name' ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIAccrualBalance' )->setMethod( 'getAccrualBalance' )
									->setSummary( 'Get accrual balance records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' )
					)
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1010-first_name' => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'  => TTi18n::gettext( 'Last Name' ),

						'-1030-accrual_policy_account' => TTi18n::gettext( 'Accrual Account' ),
						//'-1040-accrual_policy_type' => TTi18n::gettext('Accrual Policy Type'),
						'-1050-balance'                => TTi18n::gettext( 'Balance' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-group'              => TTi18n::gettext( 'Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( [ 'accrual_policy_account', 'balance' ], Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'accrual_policy_account',
						//'accrual_policy_type',
						'balance',
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'user_id'                   => 'User',
				'first_name'                => false,
				'last_name'                 => false,
				'accrual_policy_account_id' => 'AccrualPolicyAccount',
				'accrual_policy_account'    => false,
				//'accrual_policy_type_id' => FALSE,
				//'accrual_policy_type' => FALSE,
				'default_branch'            => false,
				'default_department'        => false,
				'group'                     => false,
				'title'                     => false,
				'balance'                   => 'Balance',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicyAccount() {
		return $this->getGenericDataValue( 'accrual_policy_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicyAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_account_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getBalance() {
		return $this->getGenericDataValue( 'balance' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBalance( $value ) {
		$value = trim( $value );
		if ( empty( $value ) ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'balance', $value );
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
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @return bool
	 */
	static function calcBalance( $user_id, $accrual_policy_account_id = null ) {
		global $profiler;

		$profiler->startTimer( "AccrualBalanceFactory::calcBalance()" );

		$retval = false;
		$update_balance = true;

		$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */

		$alf->StartTransaction();
		//$alf->db->SetTransactionMode( 'SERIALIZABLE' ); //Serialize balance transactions so concurrency issues don't corrupt the balance.

		$balance = $alf->getSumByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		Debug::text( 'Balance for User ID: ' . $user_id . ' Accrual Account ID: ' . $accrual_policy_account_id . ' Balance: ' . $balance, __FILE__, __LINE__, __METHOD__, 10 );

		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		Debug::text( 'Found balance records: ' . $ablf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $ablf->getRecordCount() > 1 ) { //In case multiple records exist, delete them all and re-insert.
			foreach ( $ablf as $ab_obj ) {
				$ab_obj->Delete( true );
			}
			$ab_obj = TTnew( 'AccrualBalanceFactory' ); /** @var AccrualBalanceFactory $ab_obj */
		} else if ( $ablf->getRecordCount() == 1 ) {
			$ab_obj = $ablf->getCurrent();
			if ( $balance == $ab_obj->getBalance() ) {
				Debug::text( 'Balance has not changed, not updating: ' . $balance, __FILE__, __LINE__, __METHOD__, 10 );
				$update_balance = false;
			}
		} else { //No balance record exists yet.
			$ab_obj = TTnew( 'AccrualBalanceFactory' ); /** @var AccrualBalanceFactory $ab_obj */
		}

		if ( $update_balance == true ) {
			Debug::text( 'Setting new balance to: ' . $balance, __FILE__, __LINE__, __METHOD__, 10 );
			$ab_obj->setUser( $user_id );
			$ab_obj->setAccrualPolicyAccount( $accrual_policy_account_id );
			$ab_obj->setBalance( $balance );
			if ( $ab_obj->isValid() ) {
				$retval = $ab_obj->Save();
			} else {
				$alf->FailTransaction();
				Debug::text( 'Setting new balance failed for User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		$alf->CommitTransaction();
		//$alf->db->SetTransactionMode(''); //Restore default transaction mode.

		$profiler->stopTimer( "AccrualBalanceFactory::calcBalance()" );

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getEnableStrictValidation() {
		if ( isset( $this->strict_validiation ) ) {
			return $this->strict_validiation;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableStrictValidation( $bool ) {
		$this->setIsValid( false ); //Force revalidation when data is changed.
		$this->strict_validiation = $bool;

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		if ( $this->getEnableStrictValidation() == true ) {
			// User
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);

			// Accrual Policy Account
			if ( $this->getAccrualPolicyAccount() != TTUUID::getZeroID() ) {
				$apalf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apalf */
				$this->Validator->isResultSetWithRows( 'accrual_policy_account_id',
													   $apalf->getByID( $this->getAccrualPolicyAccount() ),
													   TTi18n::gettext( 'Accrual Account is invalid' )
				);
			}
		}

		// Balance
		if ( $this->getBalance() != 0 ) {
			$this->Validator->isNumeric( 'balance',
										 $this->getBalance(),
										 TTi18n::gettext( 'Incorrect Balance' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		return true;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			//$apf = TTnew( 'AccrualPolicyFactory' );

			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'accrual_policy_account':
							//case 'accrual_policy_type_id':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						//case 'accrual_policy_type':
						//	$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $apf->getOptions( 'type' ) );
						//	break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), false, $permission_children_ids, $include_columns );
			//Accrual Balances are only created/modified by the system.
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

}

?>
