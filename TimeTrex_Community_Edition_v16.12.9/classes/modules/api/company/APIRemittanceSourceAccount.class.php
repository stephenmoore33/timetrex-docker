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
 * @package API\Company
 */
class APIRemittanceSourceAccount extends APIFactory {
	protected $main_class = 'RemittanceSourceAccountFactory';

	/**
	 * APIRemittanceSourceAccount constructor.
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
				&& ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'view' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'view_own' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default remittance source account data for creating new remittance source account.
	 * @return array
	 */
	function getRemittanceSourceAccountDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting remittance source account default data...', __FILE__, __LINE__, __METHOD__, 10 );
		$data = [
				'company_id'              => $company_obj->getId(),
				'status_id'               => 10,
				'type_id'                 => 3000,
				'last_transaction_number' => 0,
		];

		//Get New Hire Defaults.
		$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
		$udlf->getByCompanyId( $company_obj->getId() );
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::Text( 'Using User Defaults, as they exist...', __FILE__, __LINE__, __METHOD__, 10 );
			$udf_obj = $udlf->getCurrent();

			$data['legal_entity_id'] = $udf_obj->getLegalEntity();
			$data['currency_id'] = $udf_obj->getCurrency();
			$data['country'] = $udf_obj->getCountry();
		}

		//Handle default data formats based on the country.
		if ( isset( $data['country'] ) && strtoupper( $data['country'] ) == 'CA' ) {
			$data['data_format_id'] = 20; //EFT 1464
		} else {
			$data['data_format_id'] = 10; //ACH
		}

		return $this->returnHandler( $data );
	}

	/**
	 * Get remittance source account data for one or more remittance source accounts.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getRemittanceSourceAccount( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'view' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'view_own' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			//Rather then permission denied, restrict to just 'list_view' columns.
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		$blf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $blf->getCurrentRow() );
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
	function getCommonRemittanceSourceAccountData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getRemittanceSourceAccount( $data, true ) ) );
	}

	/**
	 * Validate remittance source account data for one or more remittance source accounts.
	 * @param array $data remittance source account data
	 * @return array
	 */
	function validateRemittanceSourceAccount( $data ) {
		return $this->setRemittanceSourceAccount( $data, true );
	}

	/**
	 * Set remittance source account data for one or more remittance source accounts.
	 * @param array $data remittance source account data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setRemittanceSourceAccount( $data, $validate_only = false, $ignore_warning = true ) {
		global $authentication;

		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'edit' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'edit_own' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Remittance source accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'remittance_source_account', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'remittance_source_account', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'remittance_source_account', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );

					if ( $validate_only == false && $is_valid == true && $authentication->isSessionReauthenticated( true ) === false ) { //Allow impersonation without reauthenticating, so support staff can import without needing a password.
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
	 * Delete one or more remittance source accounts.
	 * @param array $data remittance source account data
	 * @return array|bool
	 */
	function deleteRemittanceSourceAccount( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'delete' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'delete_own' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Remittance source accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'remittance_source_account', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'remittance_source_account', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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
	 * Copy one or more remittance source accounts.
	 * @param array $data remittance source account IDs
	 * @return array
	 */
	function copyRemittanceSourceAccount( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Remittance source accounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getRemittanceSourceAccount( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'] );                                   //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setRemittanceSourceAccount( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}

	/**
	 * Download a test file for $0.01 post dated for 2 days in the future for each provided source account ID.
	 * @param $ids
	 * @return array|bool
	 */
	function testExport( $ids ) {
		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'edit' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'edit_own' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		require_once( Environment::getBasePath() . '/classes/ChequeForms/ChequeForms.class.php' );

		$output = [];

		$filter_data = [
				'id' => $ids,
		];

		$rsalf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rsalf */
		$rsalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $filter_data );
		if ( $rsalf->getRecordCount() > 0 ) {
			foreach ( $rsalf as $rs_obj ) {
				if ( $rs_obj->getDataFormat() != 5 ) { //5=TimeTrex Payment Services
					$pstf = TTnew( 'PayStubTransactionFactory' ); /** @var PayStubTransactionFactory $pstf */
					$pstf->setAmount( 0.01 );
					$pstf->setCurrency( $rs_obj->getCurrency() );
					$pstf->setType( $rs_obj->getType() );
					$pstf->setRemittanceSourceAccount( $rs_obj->getId() );

					$ps_obj = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $ps_obj */
					$ps_obj->setTransactionDate( TTDate::getBeginDayEpoch( time() ) );
					$ps_obj->setStartDate( mktime( 0, 0, 0, TTDate::getMonth( time() ), TTDate::getDayOfMonth( TTDate::incrementDate( time(), -14, 'day' ) ), TTDate::getYear( time() ) ) );
					$ps_obj->setEndDate( mktime( 0, 0, 0, TTDate::getMonth( time() ), TTDate::getDayOfMonth( TTDate::incrementDate( time(), -1, 'day' ) ), TTDate::getYear( time() ) ) );
					$ps_obj->setCurrency( $rs_obj->getCurrency() );

					//This mirrors PayStubTransaction::exportPayStubTransaction()
					if ( $rs_obj->getType() == 3000 ) {
						$next_transaction_number = $rs_obj->getNextTransactionNumber();
						$eft = $pstf->startEFTFile( $rs_obj );
						$confirmation_number = strtoupper( substr( sha1( TTUUID::generateUUID() ), -8 ) );
						$record = $pstf->getEFTRecord( $eft, $pstf, $ps_obj, $rs_obj, $this->getCurrentUserObject(), $confirmation_number );

						//Make the destination the same as the source for the sample file, at least then its not 0's and the bank is likely to verify more information.
						$record->setInstitution( $rs_obj->getValue1() );
						$record->setTransit( $rs_obj->getValue2() );
						$record->setAccount( $rs_obj->getValue3() );

						$eft->setRecord( $record );
						$output = $pstf->endEFTFile( $eft, $rs_obj, $this->getCurrentUserObject(), $ps_obj, $this->getCurrentCompanyObject()->getId(), $pstf->getAmount(), $next_transaction_number, $output );
					}

					if ( $rs_obj->getType() == 2000 ) {
						$data_format_types = $rs_obj->getOptions( 'data_format_check_form' );

						$data_format_type_id = $rs_obj->getDataFormat();
						$check_file_obj = TTnew( 'ChequeForms' ); /** @var ChequeForms $check_file_obj */
						$check_obj = $check_file_obj->getFormObject( strtoupper( $data_format_types[$data_format_type_id] ) );
//						if ( PRODUCTION == FALSE AND Debug::getEnable() == TRUE ) {
//							$check_obj->setDebug( TRUE );
//						}

						$check_obj->setPageOffsets( $rs_obj->getValue6(), $rs_obj->getValue5() ); //Value5=Vertical, Value6=Horizontal

						$transaction_number = $rs_obj->getNextTransactionNumber();
						$ps_data = $pstf->getChequeData( $ps_obj, $pstf, $rs_obj, $this->getCurrentUserObject(), $transaction_number, true ); //Draw alignment grid when testing check format.
						$check_obj->addRecord( $ps_data );
						$check_file_obj->addForm( $check_obj );
						$transaction_number++;
						$output = $pstf->endChequeFile( $rs_obj, $ps_obj, $transaction_number, $output, $check_file_obj );
					}
				}
			}
		}

		if ( is_array( $output ) && count( $output ) > 0 ) {
			$filename = 'sample_transaction_file_' . TTDate::getDate( 'DATE', time() ) . '.zip';
			$zip_file = Misc::zip( $output, $filename, true );

			return Misc::APIFileDownload( $zip_file['file_name'], $zip_file['mime_type'], $zip_file['data'] );
		} else {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: No data to export...' ) );
		}
	}


	/**
	 * @param $id
	 * @return bool
	 */
	function deleteImage( $id ) {
		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		//permissions match setRemittanceSourceAccount()
		if ( !$this->getPermissionObject()->Check( 'remittance_source_account', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'remittance_source_account', 'edit' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'edit_own' ) || $this->getPermissionObject()->Check( 'remittance_source_account', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}


		$result = $this->stripReturnHandler( $this->getRemittanceSourceAccount( [ 'filter_data' => [ 'id' => $id ] ] ) );
		if ( isset( $result[0] ) && count( $result[0] ) > 0 ) {
			$uf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $uf */
			$file_name = $uf->getSignatureFileName( $this->current_company->getId(), $id );

			if ( file_exists( $file_name ) ) {
				unlink( $file_name );
			}
		}

		return $this->returnHandler( true );
	}
}

?>
