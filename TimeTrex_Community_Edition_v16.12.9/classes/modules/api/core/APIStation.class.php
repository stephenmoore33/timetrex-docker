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
class APIStation extends APIFactory {
	protected $main_class = 'StationFactory';

	/**
	 * APIStation constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Returns available Station Type IDs to help determine if KIOSK mode is available or not.
	 * @return array|string
	 */
	function getAvailableStationTypeIDs() {
		//Check if the user is a supervisor or not, so we determine if KIOSK mode is available.
		$retarr = [ 28, 60 ]; //Single user mode.

		//Check if user is supervisor or not.
		if ( $this->getPermissionObject()->Check( 'user', 'view' ) || $this->getPermissionObject()->Check( 'user', 'view_child' ) ) {
			$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
			$station_type_ids = $sf->getOptions( 'type' );

			if ( isset( $station_type_ids[61] ) ) {
				$retarr[] = 61; //PC - KIOSK
			}
			if ( isset( $station_type_ids[65] ) ) {
				$retarr[] = 65; //Tablet - KIOSK
			}
		}

		Debug::Arr( $retarr, 'Available Station Type IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $retarr );
	}

	/**
	 * Get or create current PC/Phone station.
	 * @param string $station_id UUID
	 * @param int $type_id
	 * @param string $description
	 * @return array|string
	 */
	function getCurrentStation( $station_id = null, $type_id = 10, $description = null ) {
		//This is normally just called from the main web interface, so if it is try to detect a mobile web browser and switch the type automatically.
		if ( $type_id == 10 && Misc::detectMobileBrowser() == true ) {
			$type_id = 26; //Mobile device web browser
			Debug::text( 'Mobile Station device...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$sf = TTNew( 'StationFactory' ); /** @var StationFactory $sf */
		$retval = $sf->getOrCreateStation( $station_id, $this->getCurrentCompanyObject()->getID(), $type_id, $description, $this->getPermissionObject(), $this->getCurrentUserObject() );

		if ( is_object( $retval ) && isset( $retval->Validator ) && $retval->Validator->isValid() == false ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $retval->Validator->getErrorsArray(), [ 'total_records' => 1, 'valid_records' => 0 ] );
		} else {
			Debug::text( 'Returning Station ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( $retval );
		}
	}

	/**
	 * Get default station data for creating new stations.
	 * @return array
	 */
	function getStationDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting station default data...', __FILE__, __LINE__, __METHOD__, 10 );
		$time_zone = false;
		if ( is_object( $company_obj->getUserDefaultObject() ) ) {
			$time_zone = $company_obj->getUserDefaultObject()->getTimeZone();
		}
		$data = [
				'company_id'     => $company_obj->getId(),
				'status_id'      => 20,
				'poll_frequency' => 600,
				'time_zone'      => $time_zone,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get station data for one or more stations.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getStation( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'station', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'station', 'view' ) || $this->getPermissionObject()->Check( 'station', 'view_own' ) || $this->getPermissionObject()->Check( 'station', 'view_child' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			//Edit Punch view needs to display the name of the station, so people who can edit punches must also be able to view stations.
			//Rather then permission denied, restrict to just 'list_view' columns.
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		//View/Edit Punch looks for stations by ID, but they can't be returned if its a supervisor who has subordinates only permissions and it didn't happen to match.
		if ( !isset( $data['filter_data']['id'] ) ) {
			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'station', 'view' );
		} else {
			$data['filter_data']['permission_children_ids'] = [];
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $slf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $slf->getRecordCount() );

			$this->setPagerObject( $slf );

			$retarr = [];
			foreach ( $slf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $slf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//Debug::Arr($retarr, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

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
	function exportStation( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getStation( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_station', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonStationData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getStation( $data, true ) ) );
	}

	/**
	 * Validate station data for one or more stations.
	 * @param array $data station data
	 * @return array
	 */
	function validateStation( $data ) {
		return $this->setStation( $data, true );
	}

	/**
	 * Set station data for one or more stations.
	 * @param array $data station data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setStation( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'station', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'station', 'edit' ) || $this->getPermissionObject()->Check( 'station', 'edit_own' ) || $this->getPermissionObject()->Check( 'station', 'edit_child' ) || $this->getPermissionObject()->Check( 'station', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Stations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'StationListFactory' ); /** @var StationListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get station object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'station', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'station', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'station', 'add' ), TTi18n::gettext( 'Add permission denied' ) );

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					if ( !isset( $row['company_id'] ) || !$this->getPermissionObject()->Check( 'company', 'add' ) ) {
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					}

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save( true, true ); //Force lookup on isNew()
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				} else if ( $validate_only == true ) {
					//Always fail transaction when valididate only is used, as employee criteria is saved to different tables immediately.
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
	 * Delete one or more stations.
	 * @param array $data station data
	 * @return array|bool
	 */
	function deleteStation( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'station', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'station', 'delete' ) || $this->getPermissionObject()->Check( 'station', 'delete_own' ) || $this->getPermissionObject()->Check( 'station', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Stations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'StationListFactory' ); /** @var StationListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get station object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'station', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'station', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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
	 * Copy one or more stations.
	 * @param array $data station IDs
	 * @return array
	 */
	function copyStation( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Stations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getStation( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'] ); //Clear fields that can't be copied
				$src_rows[$key]['station_id'] = 'ANY';
				$src_rows[$key]['description'] = Misc::generateCopyName( $row['description'] ); //Generate unique name
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setStation( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}

	/**
	 * Run manual commands on timeclocks
	 * @param string $command command name
	 * @param array $data     station IDs
	 * @return array
	 */
	function runManualCommand( $command, $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		Debug::Text( 'Time Clock Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );

		Debug::Text( 'Received data for: ' . count( $data ) . ' Stations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getStation( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $row ) {
				//Skip any non-timeclock types.
				if ( $row['type_id'] < 100 ) {
					continue;
				}

				$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
				$slf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
				if ( $slf->getRecordCount() == 1 ) {
					$s_obj = $slf->getCurrent();
				}

				if ( isset( $s_obj ) && is_object( $s_obj ) ) {
					$s_obj->setLastPunchTimeStamp( $s_obj->getLastPunchTimeStamp() );

					if ( $s_obj->getTimeZone() != '' && !is_numeric( $s_obj->getTimeZone() ) ) {
						Debug::text( 'Setting Station TimeZone To: ' . $s_obj->getTimeZone(), __FILE__, __LINE__, __METHOD__, 10 );
						TTDate::setTimeZone( $s_obj->getTimeZone() );
					}

					try { //Catch exception here, otherwisw the api.php catches it and causes other problems.
						Debug::Text( ' Type: ' . $row['type_id'] . ' Source: ' . $row['source'] . ' Port: ' . $row['port'] . ' Password: ' . $row['password'], __FILE__, __LINE__, __METHOD__, 10 );
						$tc = new TimeClock( $row['type_id'] );
						$tc->setIPAddress( $row['source'] );
						$tc->setPort( $row['port'] );
						//$tc->setUsername( $row['user_name'] );
						$tc->setPassword( $row['password'] );

						$result_str = null;
						switch ( $command ) {
							case 'test_connection':
								if ( $tc->testConnection() == true ) {
									$result_str = TTi18n::gettext( 'Connection Succeeded!' );
								} else {
									$result_str = TTi18n::gettext( 'Connection Failed!' );
								}
								break;
							case 'set_date':
								TTDate::setTimeZone( $row['time_zone_id'], $s_obj->getTimeZone() );

								if ( $tc->setDate( time() ) == true ) {
									$result_str = TTi18n::gettext( 'Date Successfully Set To' ) . ': ' . TTDate::getDate( 'DATE+TIME', time() );
								} else {
									$result_str = TTi18n::gettext( 'Setting Date Failed!' );
								}
								break;
							case 'download':
								if ( isset( $s_obj ) && $tc->Poll( $this->getCurrentCompanyObject(), $s_obj ) == true ) {
									$result_str = TTi18n::gettext( 'Download Data Succeeded!' );
									if ( $s_obj->isValid() ) {
										$s_obj->Save( false );
									}
								} else {
									$result_str = TTi18n::gettext( 'Download Data Failed!' );
								}
								break;
							case 'upload':
								if ( isset( $s_obj ) && $tc->Push( $this->getCurrentCompanyObject(), $s_obj ) == true ) {
									$result_str = TTi18n::gettext( 'Upload Data Succeeded!' );
									if ( $s_obj->isValid() ) {
										$s_obj->Save( false );
									}
								} else {
									$result_str = TTi18n::gettext( 'Upload Data Failed!' );
								}
								break;
							case 'update_config':
								if ( isset( $s_obj ) && $tc->setModeFlag( $s_obj->getModeFlag() ) == true ) {
									$result_str = TTi18n::gettext( 'Update Configuration Succeeded' );
								} else {
									$result_str = TTi18n::gettext( 'Update Configuration Failed' );
								}
								break;
							case 'delete_data':
								if ( isset( $s_obj ) && $tc->DeleteAllData() == true ) {
									$result_str = TTi18n::gettext( 'Delete Data Succeeded!' );
									if ( $s_obj->isValid() ) {
										$s_obj->Save( false );
									}
								} else {
									$result_str = TTi18n::gettext( 'Delete Data Failed!' );
								}
								break;
							case 'reset_last_punch_time_stamp':
								$s_obj->setLastPunchTimeStamp( time() );
								if ( $s_obj->isValid() ) {
									$s_obj->Save( false );
								}
								$result_str = TTi18n::gettext( 'Reset Last Punch Time Succeeded!' );
								break;
							case 'clear_last_punch_time_stamp':
								$s_obj->setLastPunchTimeStamp( 1 );
								if ( $s_obj->isValid() ) {
									$s_obj->Save( false );
								}
								$result_str = TTi18n::gettext( 'Clear Last Punch Time Succeeded!' );
								break;
							case 'restart':
								$tc->restart();
								$result_str = TTi18n::gettext( 'Restart Succeeded!' );
								break;
							case 'firmware':
								if ( $tc->setFirmware() == true ) {
									$result_str = TTi18n::gettext( 'Firmware Update Succeeded!' );
								} else {
									$result_str = TTi18n::gettext( 'Firmware Update Failed!' );
								}
								break;
							default:
								$result_str = TTi18n::gettext( 'Invalid manual command!' );
								break;
						}

						if ( isset( $s_obj ) ) {
							$row['last_poll_date'] = $s_obj->getLastPollDate();
							$row['last_push_date'] = $s_obj->getLastPushDate();
						}
					} catch ( Exception $e ) {
						$result_str = $e->getMessage();
					}

					TTLog::addEntry( $s_obj->getId(), 500, TTi18n::getText( 'TimeClock Manual Command' ) . ': ' . ucwords( str_replace( '_', ' ', $command ) ) . ' ' . TTi18n::getText( 'Result' ) . ': ' . $result_str, null, $s_obj->getTable() );
				} else {
					Debug::text( 'ERROR: Station not found... ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $s_obj, $slf );
			}

			return $this->returnHandler( $result_str );
		}

		return $this->returnHandler( false );
	}
}

?>