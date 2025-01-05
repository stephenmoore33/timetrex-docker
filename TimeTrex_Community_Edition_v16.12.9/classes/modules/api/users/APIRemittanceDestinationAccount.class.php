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
 * @package API\Users
 */
class APIRemittanceDestinationAccount extends APIFactory {
	protected $main_class = 'RemittanceDestinationAccountFactory';

	/**
	 * APIRemittanceDestinationAccount constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param bool|string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent     Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return bool|array
	 */
	function getOptions( $name = false, $parent = null ) {
		if ( $name == 'columns'
				&& ( !$this->getPermissionObject()->Check( 'remittance_destination_account', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'remittance_destination_account', 'view' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'view_own' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default remittance destination account data for creating new remittance destination accounts.
	 * @param null $ach_transaction_type
	 * @return array
	 */
	function getRemittanceDestinationAccountDefaultData( $ach_transaction_type = null ) {
		$company_obj = $this->getCurrentCompanyObject();
		Debug::Text( 'Getting remittance destination account default data...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $ach_transaction_type == '' ) {
			$ach_transaction_type = 22; //Checking
		}

		if ( $ach_transaction_type == 32 ) { //Savings
			$name = TTi18n::getText( 'Savings Account' );
		} else {
			$name = TTi18n::getText( 'Checking Account' );
		}

		$data = [
				'company_id'     => $company_obj->getId(),
				//			'user_id' => $user_obj->getId(), // As the employee edit tab sub view, the user_id should be the current editing employee, rather than the login one.
				'status_id'      => 10, //enabled
				'name'           => $name,
				'priority'       => 5,
				'amount_type'    => 10, //percent
				'percent_amount' => 100,
		];

		//Get New Hire Defaults.
		//  Currency isn't used for destination accounts from here anymore. Plus there can be multiple new hire defaults now too.
		//  If anything we should try to get the currency from the user record instead?
		//$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
		//$udlf->getByCompanyId( $company_obj->getId() );
		//if ( $udlf->getRecordCount() > 0 ) {
		//	Debug::Text( 'Using User Defaults, as they exist...', __FILE__, __LINE__, __METHOD__, 10 );
		//	$udf_obj = $udlf->getCurrent();
		//	$data['currency_id'] = $udf_obj->getCurrency();
		//}

		return $this->returnHandler( $data );
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportRemittanceDestinationAccount( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getRemittanceDestinationAccount( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_payment_methods', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get remittance destination account data for one or more accounts.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getRemittanceDestinationAccount( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_destination_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_destination_account', 'view' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'view_own' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'remittance_destination_account', 'view' );

		//Allow getting users from other companies, so we can change admin contacts when using the master company.
		if ( isset( $data['filter_data']['company_id'] )
				&& TTUUID::isUUID( $data['filter_data']['company_id'] ) && $data['filter_data']['company_id'] != TTUUID::getZeroID() && $data['filter_data']['company_id'] != TTUUID::getNotExistID()
				&& ( $this->getPermissionObject()->Check( 'company', 'enabled' ) && $this->getPermissionObject()->Check( 'company', 'view' ) ) ) {
			$company_id = $data['filter_data']['company_id'];
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
		$rdalf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $rdalf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $rdalf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $rdalf->getRecordCount() );

			$this->setPagerObject( $rdalf );

			$retarr = [];
			foreach ( $rdalf as $ut_obj ) {
				$retarr[] = $ut_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $rdalf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonRemittanceDestinationAccountData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getRemittanceDestinationAccount( $data, true ) ) );
	}

	/**
	 * Validate remittance destination account data for one or more accounts.
	 * @param array $data remittance destination account data
	 * @return array
	 */
	function validateRemittanceDestinationAccount( $data ) {
		return $this->setRemittanceDestinationAccount( $data, true );
	}

	/**
	 *
	 * Set remittance destination account data for one or more accounts.
	 * @param array $data remittance destination account data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setRemittanceDestinationAccount( $data, $validate_only = false, $ignore_warning = true ) {
		global $authentication;

		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_destination_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit_own' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit_child' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = false;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Remittance destination accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get remittance destination account object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'remittance_destination_account', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
										|| ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
								) ) {

							Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Edit permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'remittance_destination_account', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );
					unset( $lf->data['legal_entity_id'] );
					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );

					if ( $validate_only == false && $is_valid == true
							&& $lf->getIsRequiredCurrentPassword() == true && $authentication->isSessionReauthenticated( true ) === false ) { //Allow impersonation without reauthenticating, so support staff can import without needing a password.
						$lf->FailTransaction(); //Always fail the transaction before returning, so its not left hanging.
						$lf->CommitTransaction();

						return $this->getPermissionObject()->ReauthenticationRequired( $this->getCurrentUserObject() );
					}

					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				} else if ( $validate_only == true ) {
					$lf->FailTransaction();
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
			$authentication->reauthenticationActionCompleted();

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more account.
	 * @param array $data remittance destination account data
	 * @return array|bool
	 */
	function deleteRemittanceDestinationAccount( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_destination_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete_own' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' Remittance destination accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get remittance destination account object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
								|| ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true ) ) {
							Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}
				} else {
					$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
					$lf->setDeleted( true );

					$is_valid = $lf->isValid();
					if ( $is_valid == true ) {
						Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Copy one or more remittance destination accounts.
	 * @param array $data remittance destination account IDs
	 * @return array
	 */
	function copyRemittanceDestinationAccount( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' remittance destination accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getRemittanceDestinationAccount( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'] );                                   //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name

				//Must get decrypted account number, otherwise it will fail trying to save the record.
				//  This can be helpful if the customer needs to do a mass edit of routing numbers because a bank was bought out or something.
				//NOTE: This never gets returned to the end-user so its still secure.
				$rdalf = TTnew('RemittanceDestinationAccountListFactory'); /** @var RemittanceDestinationAccountFactory $rdlf */
				$rdalf->getByIdAndCompanyId( TTUUID::castUUID( $row['id'] ), $this->getCurrentUserObject()->getCompany() );
				if ( $rdalf->getRecordCount() == 1 ) {
					$src_rows[$key]['value3'] = $rdalf->getCurrent()->getValue3();
				}
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setRemittanceDestinationAccount( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}
}

?>
