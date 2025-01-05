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
 * @package API\PayPeriod
 */
class APIPayPeriod extends APIFactory {
	protected $main_class = 'PayPeriodFactory';

	/**
	 * APIPayPeriod constructor.
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
				&& ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default pay_period data for creating new pay_periodes.
	 * @return array
	 */
	function getPayPeriodDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting pay_period default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'company_id' => $company_obj->getID(),
				'status_id'  => 10,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get pay_period data for the pay period that ended last. First pay period with a end date before today.
	 * @return array|bool
	 */
	function getLastPayPeriod() {

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getLastPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate( $this->getCurrentCompanyObject()->getId(), null, TTDate::getTime() );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				//Only show last pay period if its not closed for the Process Payroll Wizard, so it doesn't include pay periods from old pay period schedules that are no longer being used.
				if ( $pp_obj->getStatus() != 20 ) {
					$retarr[] = $pp_obj->getObjectAsArray();
				}
			}

			if ( isset( $retarr ) && count( $retarr ) > 0 ) {
				return $this->returnHandler( $retarr );
			}
		}

		return $this->returnHandler( true ); //No records returned.

	}


	/**
	 * Get pay_period data for one or more pay_periodes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getPayPeriod( $data = null, $disable_paging = false ) {

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_child' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_period_schedule', 'view' );

		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pplf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pplf->getRecordCount() );

			$this->setPagerObject( $pplf );

			$retarr = [];
			foreach ( $pplf as $pp_obj ) {
				$tmp_array = $pp_obj->getObjectAsArray( $data['filter_columns'] );
				$tmp_array['transaction_date_epoch'] = $pp_obj->getTransactionDate(); // Creating transaction_date_epoch for sorting as epoch instead of date string.
				$retarr[] = $tmp_array;

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $pplf->getCurrentRow() );
			}

			if ( isset( $data['filter_data']['show_future_pay_periods'] ) && $data['filter_data']['show_future_pay_periods'] ) {
				if ( isset( $data['filter_data']['pay_period_schedule_id'] ) && count( $data['filter_data']['pay_period_schedule_id'] ) > 0 ) {
					$pay_period_schedule_ids = $data['filter_data']['pay_period_schedule_id'];
				} else {
					$pay_period_schedule_ids = [];
				}

				$retarr = array_merge( $retarr, iterator_to_array( PayPeriodScheduleFactory::getNextPayPeriods( $this->getCurrentCompanyObject()->getId(), $pay_period_schedule_ids, ( 429 * 86400 ) ) ) ); //Show at least 64 days more than 1 year to cover monthly pay period schedules.

				$retarr = Sort::multiSort( $retarr, 'pay_period_schedule', 'transaction_date_epoch', 'asc', 'desc' );
				$this->getPagerObject()->addTotalRows( count( $retarr ) );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//Debug::Arr($retarr, 'Data: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportPayPeriod( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getPayPeriod( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_pay_period', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayPeriodData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayPeriod( $data, true ) ) );
	}

	/**
	 * Validate pay_period data for one or more pay_periodes.
	 * @param array $data pay_period data
	 * @return array
	 */
	function validatePayPeriod( $data ) {
		return $this->setPayPeriod( $data, true );
	}

	/**
	 * Set pay_period data for one or more pay_periodes.
	 * @param array $data pay_period data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayPeriod( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' PayPeriods', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get pay_period object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				if ( $validate_only == true ) {
					$lf->Validator->setValidateOnly( $validate_only );
				}

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );

					$lf->setEnableImportData( true ); //Make sure when editing pay periods we try to import data whenever possible.

					$is_valid = $lf->isValid( $ignore_warning );
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

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more pay_periods.
	 * @param array $data pay_period data
	 * @return array|bool
	 */
	function deletePayPeriod( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' PayPeriods', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get pay_period object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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
	 * Import data into pay periods.
	 * @param array $pay_period_ids pay_period_ids
	 * @return array|bool
	 */
	function importData( $pay_period_ids = [] ) {
		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !is_array( $pay_period_ids ) ) {
			$pay_period_ids = [ $pay_period_ids ];
		}
		sort( $pay_period_ids );

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $pay_period_ids ) );

		foreach ( $pay_period_ids as $key => $pay_period_id ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByIdAndCompanyId( $pay_period_id, $this->getCurrentCompanyObject()->getId() );
			if ( $pplf->getRecordCount() == 1 ) {
				$pay_period_obj = $pplf->getCurrent();
				$pay_period_obj->importData();
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

		return true;
	}

	/**
	 * Delete data from pay periods.
	 * @param array $pay_period_ids pay_period_ids
	 * @return array|bool
	 */
	function deleteData( $pay_period_ids = [] ) {
		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !is_array( $pay_period_ids ) ) {
			$pay_period_ids = [ $pay_period_ids ];
		}
		sort( $pay_period_ids );

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $pay_period_ids ) );

		foreach ( $pay_period_ids as $key => $pay_period_id ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByIdAndCompanyId( $pay_period_id, $this->getCurrentCompanyObject()->getId() );
			if ( $pplf->getRecordCount() == 1 ) {
				$pay_period_obj = $pplf->getCurrent();
				$pay_period_obj->deleteData();
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

		return true;
	}
}

?>
