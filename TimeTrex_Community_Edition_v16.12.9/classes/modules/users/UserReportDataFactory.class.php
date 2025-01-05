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
 * @package Modules\Users
 */
class UserReportDataFactory extends Factory {
	protected $table = 'user_report_data';
	protected $pk_sequence_name = 'user_report_data_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $obj_handler = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'script' )->setFunctionMap( 'Script' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'is_default' )->setFunctionMap( 'Default' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'data' )->setFunctionMap( 'Data' )->setType( 'text' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'script' )->setType( 'text' )->setColumn( 'a.script' ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'is_default' )->setType( 'boolean' )->setColumn( 'a.is_default' ),
							TTSSearchField::new( 'created_by' )->setType( 'user_id_or_name' )->setColumn( [ 'a.created_by', 'y.first_name', 'y.last_name' ] ),
							TTSSearchField::new( 'updated_by' )->setType( 'user_id_or_name' )->setColumn( [ 'a.updated_by', 'z.first_name', 'z.last_name' ] )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserReportData' )->setMethod( 'getUserReportData' )
									->setSummary( 'Get user report data records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserReportData' )->setMethod( 'setUserReportData' )
									->setSummary( 'Add or edit user report data records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserReportData' )->setMethod( 'deleteUserReportData' )
									->setSummary( 'Delete user report data records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserReportData' )->setMethod( 'getUserReportData' ) ),
											   ) ),
					)
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1010-name'         => TTi18n::gettext( 'Name' ),
						'-1020-description'  => TTi18n::gettext( 'Description' ),
						'-1030-script_name'  => TTi18n::gettext( 'Report' ),
						'-1040-is_default'   => TTi18n::gettext( 'Default' ),
						'-1050-is_scheduled' => TTi18n::gettext( 'Scheduled' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'script_name',
						'description',
						'is_default',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
						'description',
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
				'id'           => 'ID',
				'company_id'   => 'Company',
				'user_id'      => 'User',
				'script'       => 'Script',
				'script_name'  => false,
				'name'         => 'Name',
				'is_default'   => 'Default',
				'is_scheduled' => false,
				'description'  => 'Description',
				'data'         => 'Data',
				'deleted'      => 'Deleted',
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
	 * @return bool|null
	 */
	function getObjectHandler() {
		if ( is_object( $this->obj_handler ) ) {
			return $this->obj_handler;
		} else {
			$class = $this->getScript();
			if ( class_exists( $class, true ) ) {
				$this->obj_handler = new $class();

				return $this->obj_handler;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
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
	 * @return bool|mixed
	 */
	function getScript() {
		return $this->getGenericDataValue( 'script' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScript( $value ) {
		//Strip out double slashes, as sometimes those occur and they cause the saved settings to not appear.
		$value = self::handleScriptName( trim( $value ) );

		return $this->setGenericDataValue( 'script', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		if ( $this->getCompany() == false ) {
			return false;
		}

		//Allow no user_id to be set yet, as that would be company generic data.

		if ( $this->getScript() == false ) {
			return false;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'script'     => $this->getScript(),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where
						company_id = ?
						AND script = ?
						AND lower(name) = ? ';
		if ( $this->getUser() != '' ) {
			$query .= ' AND user_id = \'' . TTUUID::castUUID( $this->getUser() ) . '\'';
		} else {
			$query .= ' AND user_id is NULL ';
		}

		$query .= ' AND deleted = 0';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool
	 */
	function getDefault() {
		return $this->fromBool( $this->getGenericDataValue( 'is_default' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefault( $value ) {
		return $this->setGenericDataValue( 'is_default', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return mixed
	 */
	function getData() {
		$retval = json_decode( $this->getGenericDataValue( 'data' ), true );

		//Handle legacy sort_ column. As of 06-Sep-23 this is still needed as some reports got this saved into the database. This work around has been removed from Javascript.
		//  **NOTE: This can't be removed without going through all saved report data in the database and doing a search&replace.
		if ( isset( $retval['config']['sort_']) ) {
			$retval['config']['sort'] = $retval['config']['sort_'];
			unset( $retval['config']['sort_'] );
		}

		return $retval;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setData( $value ) {
		//Handle legacy 'sort_' column. As of 06-Sep-23 this is still needed as some reports got this saved into the database. This work around has been removed from Javascript.
		//  **NOTE: This can't be removed without going through all saved report data in the database and doing a search&replace.
		if ( isset( $value['config']['sort_']) ) {
			$value['config']['sort'] = $value['config']['sort_'];
			unset( $value['config']['sort_'] );
		}

		$this->setGenericDataValue( 'data', json_encode( $value ) );

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		if ( $this->getDeleted() == false ) {
			//
			// BELOW: Validation code moved from set*() functions.
			//

			// Company
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Invalid Company' )
			);

			// User must always be specified, don't allow a zero UUID either.
			if ( $this->getUser() !== false ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}

			// Script
			$this->Validator->isLength( 'script',
										$this->getScript(),
										TTi18n::gettext( 'Invalid script' ),
										1, 250
			);

			// Name
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										1, 100
			);
			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name already exists' )
				);
			}

			// Description
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										0, 1024
			);

			//
			// ABOVE: Validation code moved from set*() functions.
			//

			if ( $this->Validator->hasError( 'name' ) == false && $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  false,
										  TTi18n::gettext( 'Name must be specified' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDefault() == true ) {
			//Remove default flag from all other entries.
			$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
			if ( $this->getUser() == TTUUID::getZeroID() || $this->getUser() == '' ) {
				$urdlf->getByCompanyIdAndScriptAndDefault( $this->getCompany(), $this->getScript(), true );
			} else {
				$urdlf->getByUserIdAndScriptAndDefault( $this->getUser(), $this->getScript(), true );
			}
			if ( $urdlf->getRecordCount() > 0 ) {
				foreach ( $urdlf as $urd_obj ) {
					if ( $urd_obj->getId() != $this->getId() ) { //Don't remove default flag from ourselves when editing an existing record.
						Debug::Text( 'Removing Default Flag From: ' . $urd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$urd_obj->setDefault( false );
						if ( $urd_obj->isValid() ) {
							$urd_obj->Save();
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param $script_name
	 * @return mixed
	 */
	static function handleScriptName( $script_name ) {
		return str_replace( '//', '/', $script_name );
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'is_scheduled':
							$data[$variable] = $this->getColumn( 'is_scheduled' );
							break;
						case 'script_name':
							$report_obj = $this->getObjectHandler();
							if ( is_object( $report_obj ) ) {
								$data[$variable] = $report_obj->title;
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( $this->getUser() == false && $this->getDefault() == true ) {
			//Bypass logging on Company Default Save.
			return true;
		}

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Saved Report Data' ), null, $this->getTable() );
	}
}

?>
