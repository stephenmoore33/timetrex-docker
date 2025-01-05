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
 * @package Modules\Company
 */
class CompanyGenericTagFactory extends Factory {
	protected $table = 'company_generic_tag';
	protected $pk_sequence_name = 'company_generic_tag_id_seq'; //PK Sequence name

	protected $name_validator_regex = '/^[a-z0-9-_\[\]\(\)=|\.@]{1,250}$/i'; //Deny +, -

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'object_type_id' )->setFunctionMap( 'ObjectType' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'name_metaphone' )->setFunctionMap( 'NameMetaphone' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted( true, true )
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'object_type_id' )->setType( 'numeric_list' )->setColumn( 'a.object_type_id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text_metaphone' )->setColumn( 'a.name' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APICompanyGenericTag' )->setMethod( 'getCompanyGenericTag' )
									->setSummary( 'Get company generic tag records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APICompanyGenericTag' )->setMethod( 'setCompanyGenericTag' )
									->setSummary( 'Add or edit company generic tag records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APICompanyGenericTag' )->setMethod( 'deleteCompanyGenericTag' )
									->setSummary( 'Delete company generic tag records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APICompanyGenericTag' )->setMethod( 'getCompanyGenericTag' ) ),
											   ) ),
							TTSAPI::new( 'APICompanyGenericTag' )->setMethod( 'getCompanyGenericTagDefaultData' )
									->setSummary( 'Get default company generic tag data used for creating new tags. Use this before calling setCompanyGenericTag to get the correct default data.' ),
					),
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
			case 'object_type':
				$retval = [
					//These could be names instead?
					//These need to match table names, so PurgeDatabase can properly purge them.
					100 => 'company',
					110 => 'branch',
					120 => 'department',
					130 => 'station',
					140 => 'hierarchy_control',
					150 => 'request',
					160 => 'message',
					170 => 'policy_group',

					200 => 'users',
					210 => 'user_wage',
					220 => 'user_title',
					230 => 'user_contact',

					250 => 'qualification',
					251 => 'user_skill',
					252 => 'user_education',
					253 => 'user_license',
					254 => 'user_language',
					255 => 'user_membership',

					300 => 'pay_stub_amendment',

					310 => 'kpi',
					320 => 'user_review_control',
					330 => 'user_review',

					350 => 'job_vacancy',
					360 => 'job_applicant',
					370 => 'job_applicant_location',
					380 => 'job_applicant_employment',
					390 => 'job_applicant_reference',

					391 => 'job_applicant_skill',
					392 => 'job_applicant_education',
					393 => 'job_applicant_language',
					394 => 'job_applicant_license',
					395 => 'job_applicant_membership',

					400 => 'schedule',
					410 => 'recurring_schedule_template',

					500 => 'user_report_data',
					510 => 'report_schedule',

					600 => 'job',
					610 => 'job_item',

					700 => 'document',

					800 => 'client',
					810 => 'client_contact',
					820 => 'client_payment',

					900 => 'product',
					910 => 'invoice',
					920 => 'invoice_transaction',

					930 => 'user_expense',

					950 => 'job_application',
				];
				break;
			case 'columns':
				$retval = [
						'-1010-object_type' => TTi18n::gettext( 'Object' ),
						'-1020-name'        => TTi18n::gettext( 'Name' ),
						'-1030-description' => TTi18n::gettext( 'Description' ),

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
						'description',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
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
				'id'             => 'ID',
				'company_id'     => 'Company',
				'object_type_id' => 'ObjectType',
				'object_type'    => false,
				'description'    => 'Description',
				'name'           => 'Name',
				'name_metaphone' => 'NameMetaphone',
				'deleted'        => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return int
	 */
	function getObjectType() {
		return (int)$this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'object_type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		Debug::Arr( $this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getCompany() == false ) {
			return false;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id'     => TTUUID::castUUID( $this->getCompany() ),
				'object_type_id' => (int)$this->getObjectType(),
				'name'           => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where company_id = ?
						AND object_type_id = ?
						AND lower(name) = ?
						AND deleted = 0';
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
		$this->setNameMetaphone( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNameMetaphone() {
		return $this->getGenericDataValue( 'name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );
		if ( $value != '' ) {
			return $this->setGenericDataValue( 'name_metaphone', $value );
		}

		return false;
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
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
		}
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  $this->getOptions( 'object_type' )
		);
		// Tag name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Tag is too short or too long' ),
									2,
									100
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isRegEx( 'name',
									   $this->getName(),
									   TTi18n::gettext( 'Incorrect characters in tag' ),
									   $this->name_validator_regex
			);
		}
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Tag already exists' )
			);
		}
		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is too short or too long' ),
									0, 255
		);
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

		//if ( $this->getDeleted() == TRUE ) {
		//Unassign all tagged objects.
		//}

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
						case 'object_type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
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
	 * Each tag needs a + or -. + Adds new tags, - deletes tags. Tags without these are ignores.
	 * Tags are separated by a comma.
	 * @param $tags
	 * @return array|bool
	 */
	static function parseTags( $tags ) {
		if ( $tags != '' && !is_array( $tags ) ) {
			$retarr = [
					'add'    => [],
					'delete' => [],
					'all'    => [],
			];
			$split_tags = explode( ',', str_replace( [ ' ', ';' ], ',', $tags ) ); //Support " " (space) and ";" and ", " as separators.
			if ( is_array( $split_tags ) && count( $split_tags ) > 0 ) {
				foreach ( $split_tags as $raw_tag ) {
					$raw_tag = trim( $raw_tag );
					$tag = trim( preg_replace( '/^[\+\-]/', '', $raw_tag ) );

					if ( $tag == '' ) {
						continue;
					}

					$retarr['all'][] = TTi18n::strtolower( $tag );
					if ( substr( $raw_tag, 0, 1 ) == '-' ) {
						$retarr['delete'][] = $tag;
					} else {
						$retarr['add'][] = $tag;
					}
				}
			}

			$retarr['all'] = array_unique( $retarr['all'] );
			$retarr['add'] = array_unique( $retarr['add'] );
			$retarr['delete'] = array_unique( $retarr['delete'] );

			//Debug::Arr($retarr, 'Parsed Tags: '. $tags, __FILE__, __LINE__, __METHOD__, 10);

			return $retarr;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param $parsed_tags
	 * @return array|bool
	 */
	static function getOrCreateTags( $company_id, $object_type_id, $parsed_tags ) {
		if ( is_array( $parsed_tags ) ) {
			$existing_tags = [];
			//Get the IDs for all tags
			$cgtlf = TTnew( 'CompanyGenericTagListFactory' ); /** @var CompanyGenericTagListFactory $cgtlf */
			$cgtlf->getByCompanyIdAndObjectTypeAndTags( $company_id, $object_type_id, $parsed_tags['all'] );
			if ( $cgtlf->getRecordCount() > 0 ) {
				foreach ( $cgtlf as $cgt_obj ) {
					$existing_tags[TTi18n::strtolower( $cgt_obj->getName() )] = $cgt_obj->getID();
				}
				//Debug::Arr($existing_tags, 'aExisting tags:', __FILE__, __LINE__, __METHOD__, 10);
				$tags_diff = array_diff( $parsed_tags['all'], array_keys( $existing_tags ) );
			} else {
				//Debug::Text('No Existing tags!', __FILE__, __LINE__, __METHOD__, 10);
				$tags_diff = array_values( $parsed_tags['add'] );
			}
			unset( $cgtlf, $cgt_obj );
			//Debug::Arr($tags_diff, 'Tags Diff: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $tags_diff ) && is_array( $tags_diff ) ) {
				//Add new tags.
				foreach ( $tags_diff as $new_tag ) {
					$new_tag = trim( $new_tag );
					$cgtf = TTnew( 'CompanyGenericTagFactory' ); /** @var CompanyGenericTagFactory $cgtf */
					$cgtf->setCompany( $company_id );
					$cgtf->setObjectType( $object_type_id );
					$cgtf->setName( $new_tag );
					if ( $cgtf->isValid() ) {
						$insert_id = $cgtf->Save();
						$existing_tags[TTi18n::strtolower( $new_tag )] = $insert_id;
					}
				}
				unset( $tags_diff, $new_tag, $cgtf, $insert_id );
			}

			//Debug::Arr($existing_tags, 'Existing Tags: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( empty( $existing_tags ) == false ) {
				return $existing_tags;
			}
		}

		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Tag' ) . ': ' . $this->getName(), null, $this->getTable(), $this );
	}

}

?>
