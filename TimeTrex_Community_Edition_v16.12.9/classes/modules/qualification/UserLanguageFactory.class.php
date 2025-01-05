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
class UserLanguageFactory extends Factory {
	protected $table = 'user_language';
	protected $pk_sequence_name = 'user_language_id_seq'; //PK Sequence name
	protected $qualification_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'qualification_id' )->setFunctionMap( 'Qualification' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'fluency_id' )->setFunctionMap( 'Fluency' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'competency_id' )->setFunctionMap( 'Competency' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_language' )->setLabel( TTi18n::getText( 'Language' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Employee' ) ),
											TTSField::new( 'qualification_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Language' ) )->setDataSource( TTSAPI::new( 'APIQualification' )->setMethod( 'getQualification' )->setArg( 'language' ) ),
											TTSField::new( 'fluency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Fluency' ) )->setDataSource( TTSAPI::new( 'APIUserLanguage' )->setMethod( 'getOptions' )->setArg( 'fluency' ) ),
											TTSField::new( 'competency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Competency' ) )->setDataSource( TTSAPI::new( 'APIUserLanguage' )->setMethod( 'getOptions' )->setArg( 'competency' ) ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )
									)
							),
							TTSTab::new( 'tab_attachment' )->setLabel( TTi18n::getText( 'Attachment' ) )->setInitCallback( 'initSubDocumentView' )->setDisplayOnMassEdit( false )->setSubView( true ),
							TTSTab::new( 'tab_audit' )->setLabel( TTi18n::getText( 'Audit' ) )->setInitCallback( 'initSubLogView' )->setDisplayOnMassEdit( false )
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new('user_id')->setType('uuid_list')->setColumn('a.user_id')->setMulti(true),
							TTSSearchField::new('id')->setType('uuid_list')->setColumn('a.id')->setMulti(true),
							TTSSearchField::new('exclude_id')->setType('not_uuid_list')->setColumn('a.user_id')->setMulti(true),
							TTSSearchField::new('fluency')->setType('text')->setColumn('a.fluency_id')->setMulti(true),
							TTSSearchField::new('competency')->setType('text')->setColumn('a.competency_id')->setMulti(true),
							TTSSearchField::new('qualification_id')->setType('uuid_list')->setColumn('a.qualification_id')->setMulti(true),
							TTSSearchField::new('qualification')->setType('text')->setColumn('qf.name'),
							TTSSearchField::new('proficiency_id')->setType('numeric_list')->setColumn('usf.proficiency_id')->setMulti(true),
							TTSSearchField::new('ownership_id')->setType('numeric_list')->setColumn('umf.ownership_id')->setMulti(true),
							TTSSearchField::new('fluency_id')->setType('numeric_list')->setColumn('a.fluency_id')->setMulti(true),
							TTSSearchField::new('competency_id')->setType('numeric_list')->setColumn('a.competency_id')->setMulti(true),
							TTSSearchField::new('description')->setType('text')->setColumn('a.description'),
							TTSSearchField::new('source_type_id')->setType('numeric_list')->setColumn('qf.source_type_id')->setMulti(true),
							TTSSearchField::new('group_id')->setType('uuid_list')->setColumn('qf.group_id')->setMulti(true),
							TTSSearchField::new('group')->setType('text')->setColumn('qgf.name'),
							TTSSearchField::new('qualification_type_id')->setType('numeric_list')->setColumn('qf.type_id')->setMulti(true),
							TTSSearchField::new('default_branch_id')->setType('uuid_list')->setColumn('uf.default_branch_id')->setMulti(true),
							TTSSearchField::new('default_department_id')->setType('uuid_list')->setColumn('uf.default_department_id')->setMulti(true),
							TTSSearchField::new('tag')->setType('tag')->setColumn('a.id')
					)
			);

		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new('APIUserLanguage')->setMethod('getUserLanguage')
									->setSummary( 'Get user language records.')
									->setArgs(['data' => ['filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields()]])
									->setArgsModelDescription('Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.'),
							TTSAPI::new('APIUserLanguage')->setMethod('setUserLanguage')
									->setSummary( 'Add or edit user language records. Will return the record UUID upon success, or a validation error if there is a problem.')
									->setArgs(['data' => $schema_data->getFields()]),
							TTSAPI::new('APIUserLanguage')->setMethod('deleteUserLanguage')
									->setSummary( 'Delete user language records by passing in an array of UUIDs.'),
							TTSAPI::new('APIUserLanguage')->setMethod('getUserLanguageDefaultData')
									->setSummary( 'Get default user language data used for creating new user languages. Use this before calling setUserLanguage to get the correct default data.'),
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
			case 'fluency':
				$retval = [
						10 => TTi18n::gettext( 'Speaking' ),
						20 => TTi18n::gettext( 'Writing' ),
						30 => TTi18n::gettext( 'Reading' ),
				];
				break;
			case 'competency':
				$retval = [
						10 => TTi18n::gettext( 'Native Language' ),
						20 => TTi18n::gettext( 'Good' ),
						30 => TTi18n::gettext( 'Basic' ),
						40 => TTi18n::gettext( 'Poor' ),
				];
				break;
			case 'source_type':
				$qf = TTnew( 'QualificationFactory' ); /** @var QualificationFactory $qf */
				$retval = $qf->getOptions( $name );
				break;
			case 'columns':
				$retval = [
						'-1010-first_name'    => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'     => TTi18n::gettext( 'Last Name' ),
						'-2050-qualification' => TTi18n::gettext( 'Language' ),
						'-2040-group'         => TTi18n::gettext( 'Group' ),
						'-4010-fluency'       => TTi18n::gettext( 'Fluency' ),
						'-4020-competency'    => TTi18n::gettext( 'Competency' ),
						'-1040-description'   => TTi18n::getText( 'Description' ),
						'-1300-tag'           => TTi18n::gettext( 'Tags' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Employee Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),
						'-2000-created_by'         => TTi18n::gettext( 'Created By' ),
						'-2010-created_date'       => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'         => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date'       => TTi18n::gettext( 'Updated Date' ),
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
						'fluency',
						'competency',
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
				'id'               => 'ID',
				'user_id'          => 'User',
				'first_name'       => false,
				'last_name'        => false,
				'qualification_id' => 'Qualification',
				'qualification'    => false,
				'group'            => false,
				'fluency_id'       => 'Fluency',
				'fluency'          => false,

				'competency_id' => 'Competency',
				'competency'    => false,

				'description'        => 'Description',
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
	 * @return bool|int
	 */
	function getFluency() {
		return $this->getGenericDataValue( 'fluency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFluency( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'fluency_id', $value );
	}


	/**
	 * @return bool|int
	 */
	function getCompetency() {
		return $this->getGenericDataValue( 'competency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompetency( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'competency_id', $value );
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
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 254, $this->getID() );
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
												   TTi18n::gettext( 'Language must be specified' )
			);
		}
		// Fluency
		if ( $this->getFluency() !== false ) {
			$this->Validator->inArrayKey( 'fluency_id',
										  $this->getFluency(),
										  TTi18n::gettext( 'Fluency is invalid' ),
										  $this->getOptions( 'fluency' )
			);
		}
		// Competency
		if ( $this->getCompetency() !== false ) {
			$this->Validator->inArrayKey( 'competency_id',
										  $this->getCompetency(),
										  TTi18n::gettext( 'Competency is invalid' ),
										  $this->getOptions( 'competency' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										2, 10000
			);

			$this->Validator->isHTML( 'description',
									  $this->getDescription(),
									  TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

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
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 254, $this->getID(), $this->getTag() );
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
						case 'fluency':
						case 'competency':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Language' ), null, $this->getTable(), $this );
	}

}

?>
