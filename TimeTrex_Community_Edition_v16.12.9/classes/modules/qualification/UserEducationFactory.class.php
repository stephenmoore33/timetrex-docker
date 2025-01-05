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
class UserEducationFactory extends Factory {
	protected $table = 'user_education';
	protected $pk_sequence_name = 'user_education_id_seq'; //PK Sequence name
	protected $qualification_obj = null;
	//protected $grade_score_validator_regex = '/^[0-9]{1,250}$/i';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'qualification_id' )->setFunctionMap( 'Qualification' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'institute' )->setFunctionMap( 'Institute' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'major' )->setFunctionMap( 'Major' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'minor' )->setFunctionMap( 'Minor' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'graduate_date' )->setFunctionMap( 'GraduateDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'grade_score' )->setFunctionMap( 'GradeScore' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'end_date' )->setFunctionMap( 'EndDate' )->setType( 'integer' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_education' )->setLabel( TTi18n::getText( 'Education' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Employee' ) ),
											TTSField::new( 'qualification_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Course' ) ),
											TTSField::new( 'institute' )->setType( 'text' )->setLabel( TTi18n::getText( 'Institute' ) ),
											TTSField::new( 'major' )->setType( 'text' )->setLabel( TTi18n::getText( 'Major/Specialization' ) ),
											TTSField::new( 'minor' )->setType( 'text' )->setLabel( TTi18n::getText( 'Minor' ) ),
											TTSField::new( 'grade_score' )->setType( 'text' )->setLabel( TTi18n::getText( 'Grade/Score' ) ),
											TTSField::new( 'graduate_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Graduation Date' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )
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
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'qualification_id' )->setType( 'uuid_list' )->setColumn( 'a.qualification_id' )->setMulti( true ),
							TTSSearchField::new( 'qualification' )->setType( 'text' )->setColumn( 'qf.name' ),
							TTSSearchField::new( 'proficiency_id' )->setType( 'numeric_list' )->setColumn( 'usf.proficiency_id' )->setMulti( true ),
							TTSSearchField::new( 'fluency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.fluency_id' )->setMulti( true ),
							TTSSearchField::new( 'competency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.competency_id' )->setMulti( true ),
							TTSSearchField::new( 'ownership_id' )->setType( 'numeric_list' )->setColumn( 'umf.ownership_id' )->setMulti( true ),
							TTSSearchField::new( 'institute' )->setType( 'text' )->setColumn( 'a.institute' ),
							TTSSearchField::new( 'major' )->setType( 'text' )->setColumn( 'a.major' ),
							TTSSearchField::new( 'minor' )->setType( 'text' )->setColumn( 'a.minor' ),
							TTSSearchField::new( 'grade_score' )->setType( 'numeric' )->setColumn( 'a.grade_score' ),
							TTSSearchField::new( 'source_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.source_type_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'qf.group_id' )->setMulti( true ),
							TTSSearchField::new( 'group' )->setType( 'text' )->setColumn( 'qgf.name' ),
							TTSSearchField::new( 'qualification_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'start_date' )->setType( 'date_range' )->setColumn( 'a.start_date' ),
							TTSSearchField::new( 'end_date' )->setType( 'date_range' )->setColumn( 'a.end_date' ),
							TTSSearchField::new( 'graduate_date' )->setType( 'date_range' )->setColumn( 'a.graduate_date' ),
							TTSSearchField::new( 'education_graduate_start_date' )->setType( 'start_date' )->setColumn( 'a.graduate_date' ),
							TTSSearchField::new( 'education_graduate_end_date' )->setType( 'end_date' )->setColumn( 'a.graduate_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserEducation' )->setMethod( 'getUserEducation' )
									->setSummary( 'Get user education records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserEducation' )->setMethod( 'setUserEducation' )
									->setSummary( 'Add or edit user education records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserEducation' )->setMethod( 'deleteUserEducation' )
									->setSummary( 'Delete user education records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserEducation' )->setMethod( 'getUserEducation' ) ),
											   ) ),
							TTSAPI::new( 'APIUserEducation' )->setMethod( 'getUserEducationDefaultData' )
									->setSummary( 'Get default user education data used for creating new user education records. Use this before calling setUserEducation to get the correct default data.' ),
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
						'-1010-first_name' => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'  => TTi18n::gettext( 'Last Name' ),

						'-2050-qualification' => TTi18n::gettext( 'Course' ),

						'-2040-group' => TTi18n::gettext( 'Group' ),

						'-3030-institute'     => TTi18n::gettext( 'Institute' ),
						'-3040-major'         => TTi18n::gettext( 'Major/Specialization' ),
						'-3050-minor'         => TTi18n::gettext( 'Minor' ),
						'-3060-graduate_date' => TTi18n::gettext( 'Graduation Date' ),
						'-3070-grade_score'   => TTi18n::gettext( 'Grade/Score' ),
						'-1170-start_date'    => TTi18n::gettext( 'Start Date' ),
						'-1180-end_date'      => TTi18n::gettext( 'End Date' ),

						'-1300-tag' => TTi18n::gettext( 'Tags' ),

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
						'institute',
						'major',
						'minor',
						'graduate_date',
						'grade_score',
						'start_date',
						'end_date',
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
				'institute'        => 'Institute',
				'major'            => 'Major',
				'minor'            => 'Minor',
				'graduate_date'    => 'GraduateDate',
				'grade_score'      => 'GradeScore',
				'start_date'       => 'StartDate',
				'end_date'         => 'EndDate',

				'tag' => 'Tag',

				'default_branch'     => false,
				'default_department' => false,
				'user_group'         => false,
				'title'              => false,

				'deleted' => 'Deleted',
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
	 * @param $value
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
	 * @param $value
	 * @return bool
	 */
	function setQualification( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'qualification_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getInstitute() {
		return $this->getGenericDataValue( 'institute' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setInstitute( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'institute', $value );
	}


	/**
	 * @return bool
	 */
	function getMajor() {
		return $this->getGenericDataValue( 'major' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMajor( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'major', $value );
	}

	/**
	 * @return bool
	 */
	function getMinor() {
		return $this->getGenericDataValue( 'minor' );
	}


	/**
	 * @param $value
	 * @return bool
	 */
	function setMinor( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'minor', $value );
	}

	/**
	 * @return bool|int
	 */
	function getGraduateDate() {
		return (int)$this->getGenericDataValue( 'graduate_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setGraduateDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'graduate_date', $value );
	}

	/**
	 * @return bool
	 */
	function getGradeScore() {
		return $this->getGenericDataValue( 'grade_score' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setGradeScore( $value ) {
		$value = trim( $value );

		// $grade_score = $this->Validator->stripNonFloat( $grade_score );
		return $this->setGenericDataValue( 'grade_score', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStartDate() {
		return (int)$this->getGenericDataValue( 'start_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getEndDate() {
		return (int)$this->getGenericDataValue( 'end_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
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
		} else if ( is_object( $this->getQualificationObject() )
				&& TTUUID::isUUID( $this->getQualificationObject()->getCompany() ) && $this->getQualificationObject()->getCompany() != TTUUID::getZeroID() && $this->getQualificationObject()->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 252, $this->getID() );
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
												   TTi18n::gettext( 'Course must be specified' )
			);
		}

//		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing, but must check when adding a new record.
//			if ( $this->getInstitute() == '' ) {
//				$this->Validator->isTRUE( 'institute',
//										  FALSE,
//										  TTi18n::gettext( 'Please specify a Institute' ) );
//			}
//		}

		// Institute
		if ( $this->getInstitute() != '' && $this->Validator->isError( 'institute' ) == false ) {
			$this->Validator->isLength( 'institute',
										$this->getInstitute(),
										TTi18n::gettext( 'Institute is too short or too long' ),
										2, 255
			);
		}
		// Major/Specialization
		if ( $this->getMajor() != '' && $this->Validator->isError( 'major' ) == false ) {
			$this->Validator->isLength( 'major',
										$this->getMajor(),
										TTi18n::gettext( 'Major/Specialization is too short or too long' ),
										2, 255
			);
		}
		// Minor
		if ( $this->getMinor() != '' && $this->Validator->isError( 'minor' ) == false ) {
			$this->Validator->isLength( 'minor',
										$this->getMinor(),
										TTi18n::gettext( 'Minor is too short or too long' ),
										2, 255
			);
		}
		// Graduation date
		if ( $this->getGraduateDate() != '' ) {
			$this->Validator->isDate( 'graduate_date',
									  $this->getGraduateDate(),
									  TTi18n::gettext( 'Incorrect graduation date' )
			);
		}
		// Grade/Score
		if ( $this->getGradeScore() != '' ) {
			$this->Validator->isNumeric( 'grade_score',
										 $this->getGradeScore(),
										 TTi18n::gettext( 'Grade/Score must only be digits' )
			);
			if ( $this->Validator->isError( 'grade_score' ) == false ) {
				$this->Validator->isLengthAfterDecimal( 'grade_score',
														$this->getGradeScore(),
														TTi18n::gettext( 'Invalid Grade/Score' ),
														0,
														2
				);
			}
		}
		// Start date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}
		// End date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
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
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 252, $this->getID(), $this->getTag() );
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
						case 'start_date':
							$this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
							break;
						case 'end_date':
							$this->setEndDate( TTDate::parseDateTime( $data['end_date'] ) );
							break;
						case 'graduate_date':
							$this->setGraduateDate( TTDate::parseDateTime( $data['graduate_date'] ) );
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
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
							break;
						case 'end_date':
							$data['end_date'] = TTDate::getAPIDate( 'DATE', $this->getEndDate() );
							break;
						case 'graduate_date':
							$data['graduate_date'] = TTDate::getAPIDate( 'DATE', $this->getGraduateDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Education' ), null, $this->getTable(), $this );
	}

}

?>
