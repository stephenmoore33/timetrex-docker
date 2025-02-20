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
 * @package API\Core
 */
class APILog extends APIFactory {
	protected $main_class = 'LogFactory';

	/**
	 * APILog constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get log data for one or more logs.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getLog( $data = null, $disable_paging = false ) {
		//Check permissions based on the filter table_name.
		//Its important that regular employees can't view the entire log as some sensitive information may be contained within.
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$blf = TTnew( 'LogListFactory' ); /** @var LogListFactory $blf */

		if ( isset( $data['filter_data']['log_table_name_id'] ) ) {
			$data['filter_data']['table_name'] = $data['filter_data']['log_table_name_id'];
			unset( $data['filter_data']['log_table_name_id'] );
		}

		if ( isset( $data['filter_data']['table_name_object_id'] ) ) {
			if ( !is_array( $data['filter_data']['table_name_object_id'] ) ) {
				$data['filter_data']['table_name_object_id'] = [ $data['filter_data']['table_name_object_id'] ];
			}

			$filter_table_names = array_keys( $data['filter_data']['table_name_object_id'] );
		} else if ( isset( $data['filter_data']['table_name'] ) ) {
			if ( !is_array( $data['filter_data']['table_name'] ) ) {
				$data['filter_data']['table_name'] = [ $data['filter_data']['table_name'] ];
			}

			$filter_table_names = $data['filter_data']['table_name'];
		} else {
			Debug::Text( 'ERROR: No filter table names specified...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( isset( $filter_table_names ) && count( $filter_table_names ) > 0 ) {
			$table_name_permission_map = $blf->getOptions( 'table_name_permission_map' );
			foreach ( $filter_table_names as $key => $filter_table_name ) {
				if ( isset( $table_name_permission_map[$filter_table_name] ) ) {
					foreach ( $table_name_permission_map[$filter_table_name] as $permission_section ) {
						if ( !( $this->getPermissionObject()->Check( $permission_section, 'enabled' )
								&& ( $this->getPermissionObject()->Check( $permission_section, 'edit' )
										|| $this->getPermissionObject()->Check( $permission_section, 'edit_child' )
								) ) ) {
							//By default administrators have company,edit_own permissions, which means they can't see their own companies audit tab. This is just to be on the safe side.

							//If permission checks fail, force the filter to include the currently logged in user_id, assuming that they can always see audit records created by themselves.
							//This is needed so they can see the audit tab for saved/scheduled reports and at least view when they were sent out.
							if ( $this->getPermissionObject()->Check( $permission_section, 'view_own' ) || $this->getPermissionObject()->Check( $permission_section, 'edit_own' ) ) {
								Debug::Text( 'Forcing filter to currently logged in user due to audit log table permissions: ' . $filter_table_name . ' Permission Section: ' . $permission_section . ' Key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
								$data['filter_data']['user_id'] = $this->getCurrentUserObject()->getId();
							} else {
								Debug::Text( 'Skipping table name due to permissions: ' . $filter_table_name . ' Permission Section: ' . $permission_section . ' Key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
								unset( $data['filter_data']['table_name'][$key], $data['filter_data']['table_name_object_id'][$filter_table_name] );
							}
						} else {
							Debug::Text( 'Allowing table name due to permissions: ' . $filter_table_name, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				} else {
					Debug::Text( 'Skipping undefined table name: ' . $filter_table_name, __FILE__, __LINE__, __METHOD__, 10 );
					unset( $data['filter_data']['table_name'][$key], $data['filter_data']['table_name_object_id'][$filter_table_name] );
				}
			}
		}

		//Debug::Arr($data, 'Filter data: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( ( isset( $data['filter_data']['table_name'] ) && count( $data['filter_data']['table_name'] ) == 0 )
				|| ( isset( $data['filter_data']['table_name_object_id'] ) && count( $data['filter_data']['table_name_object_id'] ) == 0 ) ) {
			Debug::Text( 'ERROR: No filter table names specified, not returning any records... (b)', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( true ); //No records returned.
		}

		//Debug::Arr($data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
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
	 * Validate log data for one or more logs.
	 * @param array $data log data
	 * @return array
	 */
	function validateLog( $data ) {
		return $this->setLog( $data, true );
	}

	/**
	 * Set log data for one or more logs.
	 * @param array $data log data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array
	 */
	function setLog( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}
		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Logs', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'LogListFactory' ); /** @var LogListFactory $lf */
				$lf->StartTransaction();

				//Can add log entries only.
				unset( $row['id'] );
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

				$lf->setObjectFromArray( $row );

				//Force Company ID to current company.
				$lf->setUser( $this->getCurrentUserObject()->getId() );


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
}

?>
