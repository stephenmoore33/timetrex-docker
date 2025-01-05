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
 * @package Modules\Qualification
 */
class UserLicenseFactory extends Factory {
	protected $table = 'user_license';
	protected $pk_sequence_name = 'user_license_id_seq'; //PK Sequence name
	protected $qualification_obj = null;

	protected $license_number_validator_regex = '/^[A-Z_\/:;\-\.\ 0-9]{1,50}$/i'; //This should match the same validation as JobApplicantLicenseFactory

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'qualification_id' )->setFunctionMap( 'Qualification' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'license_number' )->setFunctionMap( 'LicenseNumber' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'license_issued_date' )->setFunctionMap( 'LicenseIssuedDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'license_expiry_date' )->setFunctionMap( 'LicenseExpiryDate' )->setType( 'integer' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_license' )->setLabel( TTi18n::getText( 'License' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'user_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Employee' ) )->setWidth( '100%' ),
											TTSField::new( 'qualification_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setWidth( '100%' ),
											TTSField::new( 'license_number' )->setType( 'text' )->setLabel( TTi18n::getText( 'Number' ) )->setWidth( '100%' ),
											TTSField::new( 'license_issued_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Issued Date' ) )->setWidth( '100%' ),
											TTSField::new( 'license_expiry_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Expiry Date' ) )->setWidth( '100%' ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )->setWidth( '100%' )
									)
							),
					)->addAttachment()->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'qualification_id' )->setType( 'uuid_list' )->setColumn( 'a.qualification_id' )->setMulti( true ),
							TTSSearchField::new( 'qualification' )->setType( 'text' )->setColumn( 'qf.name' ),
							TTSSearchField::new( 'proficiency_id' )->setType( 'numeric_list' )->setColumn( 'usf.proficiency_id' )->setMulti( true ),
							TTSSearchField::new( 'fluency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.fluency_id' )->setMulti( true ),
							TTSSearchField::new( 'competency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.competency_id' )->setMulti( true ),
							TTSSearchField::new( 'ownership_id' )->setType( 'numeric_list' )->setColumn( 'umf.ownership_id' )->setMulti( true ),
							TTSSearchField::new( 'license_number' )->setType( 'numeric' )->setColumn( 'a.license_number' ),
							TTSSearchField::new( 'source_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.source_type_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'qf.group_id' )->setMulti( true ),
							TTSSearchField::new( 'group' )->setType( 'text' )->setColumn( 'qgf.name' ),
							TTSSearchField::new( 'qualification_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'license_issued_date' )->setType( 'date_range' )->setColumn( 'a.license_issued_date' ),
							TTSSearchField::new( 'license_expiry_date' )->setType( 'date_range' )->setColumn( 'a.license_expiry_date' ),
							TTSSearchField::new( 'license_expiry_start_date' )->setType( 'start_date' )->setColumn( 'a.license_expiry_date' ),
							TTSSearchField::new( 'license_expiry_end_date' )->setType( 'end_date' )->setColumn( 'a.license_expiry_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserLicense' )->setMethod( 'getUserLicense' )
									->setSummary( 'Get user license records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserLicense' )->setMethod( 'setUserLicense' )
									->setSummary( 'Add or edit user license records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserLicense' )->setMethod( 'deleteUserLicense' )
									->setSummary( 'Delete user license records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserLicense' )->setMethod( 'getUserLicense' ) ),
											   ) ),
							TTSAPI::new( 'APIUserLicense' )->setMethod( 'getUserLicenseDefaultData' )
									->setSummary( 'Get default user license data used for creating new user licenses. Use this before calling setUserLicense to get the correct default data.' ),
					),
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
			case 'source_type':
				$qf = TTnew( 'QualificationFactory' ); /** @var QualificationFactory $qf */
				$retval = $qf->getOptions( $name );
				break;
			case 'columns':
				$retval = [
						'-1010-first_name'          => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'           => TTi18n::gettext( 'Last Name' ),
						'-2050-qualification'       => TTi18n::gettext( 'License Type' ),
						'-2040-group'               => TTi18n::gettext( 'Group' ),
						'-3080-license_number'      => TTi18n::gettext( 'License Number' ),
						'-3090-license_issued_date' => TTi18n::gettext( 'Issued Date' ),
						'-4000-license_expiry_date' => TTi18n::gettext( 'Expiry Date' ),

						'-1300-tag'                => TTi18n::gettext( 'Tags' ),
						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Employee Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

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
						'first_name',
						'last_name',
						'qualification',
						'license_number',
						'license_issued_date',
						'license_expiry_date',
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
				'id'               => 'ID',
				'user_id'          => 'User',
				'first_name'       => false,
				'last_name'        => false,
				'qualification_id' => 'Qualification',
				'qualification'    => false,
				'group'            => false,

				'license_number'      => 'LicenseNumber',
				'license_issued_date' => 'LicenseIssuedDate',
				'license_expiry_date' => 'LicenseExpiryDate',

				'tag'                => 'Tag',
				'default_branch'     => false,
				'default_department' => false,
				'user_group'         => false,
				'title'              => false,
				'deleted'            => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getQualificationObject() {

		return $this->getGenericObject( 'QualificationListFactory', $this->getQualification(), 'qualification_obj' );
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
	 * @return bool
	 */
	function getQualification() {
		return $this->getGenericDataValue( 'qualification_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setQualification( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'qualification_id', $value );
	}


	/**
	 * @return bool
	 */
	function getLicenseNumber() {
		return $this->getGenericDataValue( 'license_number' );
	}


	/**
	 * @param $value
	 * @return bool
	 */
	function setLicenseNumber( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'license_number', $value );
	}


	/**
	 * @return bool|int
	 */
	function getLicenseIssuedDate() {
		return (int)$this->getGenericDataValue( 'license_issued_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLicenseIssuedDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'license_issued_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getLicenseExpiryDate() {
		return (int)$this->getGenericDataValue( 'license_expiry_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLicenseExpiryDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'license_expiry_date', $value );
	}

	/**
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== false ) {
			return $value;
		} else if ( is_object( $this->getQualificationObject() ) &&
				TTUUID::isUUID( $this->getQualificationObject()->getCompany() ) && $this->getQualificationObject()->getCompany() != TTUUID::getZeroID() && $this->getQualificationObject()->getCompany() != TTUUID::getNotExistID() &&
				TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 253, $this->getID() );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}

	function preValidate() {
		if ( $this->getLicenseNumber() === false ) {
			$this->setLicenseNumber( '' ); //Blank string, to avoid "null value in column "license_number" of relation "user_license" violates not-null constraint" during import when license number is not mapped.
		}

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
		// Employee
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Employee must be specified' )
			);
		}
		// Qualification
		if ( $this->getQualification() !== false ) {
			$qlf = TTnew( 'QualificationListFactory' ); /** @var QualificationListFactory $qlf */
			$this->Validator->isResultSetWithRows( 'qualification_id',
												   $qlf->getById( $this->getQualification() ),
												   TTi18n::gettext( 'License must be specified' )
			);
		}

		// License number -- DO NOT REQUIRE A LICENSE NUMBER, as many customers don't have one to enter. Force it to a blank string rather than NULL, see preValidate() for more information.
		//if ( $this->Validator->getValidateOnly() == false ) {
		//	if ( $this->getLicenseNumber() == '' ) {
		//		$this->Validator->isTRUE( 'license_number',
		//								  false,
		//								  TTi18n::gettext( 'Please specify a license number' )
		//		);
		//	}
		//}

		if ( $this->getLicenseNumber() != '' && $this->Validator->isError( 'license_number' ) == false ) {
			$this->Validator->isRegEx( 'license_number',
									   $this->getLicenseNumber(),
									   TTi18n::gettext( 'License number is invalid' ),
									   $this->license_number_validator_regex //This should match the same validation as JobApplicantLicenseFactory
			);
		}

		// License issued date
		if ( $this->getLicenseIssuedDate() != '' ) {
			$this->Validator->isDate( 'license_issued_date',
									  $this->getLicenseIssuedDate(),
									  TTi18n::gettext( 'Incorrect license issued date' )
			);
		}
		// License expiry date
		if ( $this->getLicenseExpiryDate() != '' ) {
			$this->Validator->isDate( 'license_expiry_date',
									  $this->getLicenseExpiryDate(),
									  TTi18n::gettext( 'Incorrect license expiry date' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getUser() . $this->getQualification() );

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 253, $this->getID(), $this->getTag() );
		}

		return true;
	}

	/**
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
						case 'license_issued_date':
							$this->setLicenseIssuedDate( TTDate::parseDateTime( $data['license_issued_date'] ) );
							break;
						case 'license_expiry_date':
							$this->setLicenseExpiryDate( TTDate::parseDateTime( $data['license_expiry_date'] ) );
							break;
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;

					switch ( $variable ) {
						case 'qualification':
						case 'group':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'license_issued_date':
							$data['license_issued_date'] = TTDate::getAPIDate( 'DATE', $this->getLicenseIssuedDate() );
							break;
						case 'license_expiry_date':
							$data['license_expiry_date'] = TTDate::getAPIDate( 'DATE', $this->getLicenseExpiryDate() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'License' ), null, $this->getTable(), $this );
	}

}

?>
