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
 * @package Modules\KPI
 */
class KPIFactory extends Factory {
	protected $table = 'kpi';
	protected $pk_sequence_name = 'kpi_id_seq'; //PK Sequence name
	protected $company_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'minimum_rate' )->setFunctionMap( 'Minimum Rating' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'maximum_rate' )->setFunctionMap( 'Maximum Rating' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'kpi_group' )->setFunctionMap( 'Group' )->setType( 'varchar' )->setIsSynthetic( true ),
							TTSCol::new( 'display_order' )->setFunctionMap( 'Display Order' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'tag' )->setFunctionMap( 'Tags' )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_key_performance_indicator' )->setLabel( TTi18n::getText( 'Key Performance Indicator' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIKPI' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'status_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIKPI' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'display_order' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Display Order' ) ),
											TTSField::new( 'group_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Group' ) )->setDataSource( TTSAPI::new( 'APIKPIGroup' )->setMethod( 'getKPIGroup' ) ),
											TTSField::new( 'minimum_rate' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Minimum Rating' ) ),
											TTSField::new( 'maximum_rate' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Maximum Rating' ) ),
											TTSField::new( 'description' )->setType( 'textarea' )->setLabel( TTi18n::getText( 'Description' ) ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )
									)
							),
					)->addAttachment()->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' ),
							TTSSearchField::new( 'minimum_rate' )->setType( 'numeric' )->setColumn( 'a.minimum_rate' ),
							TTSSearchField::new( 'maximum_rate' )->setType( 'numeric' )->setColumn( 'a.maximum_rate' ),
							TTSSearchField::new( 'status' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list_with_all' )->setColumn( 'b.map_id' )->setMulti( true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIKPI' )->setMethod( 'getKPI' )
									->setSummary( 'Get KPI records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIKPI' )->setMethod( 'setKPI' )
									->setSummary( 'Add or edit KPI records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIKPI' )->setMethod( 'deleteKPI' )
									->setSummary( 'Delete KPI records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIKPI' )->setMethod( 'getKPI' ) ),
											   ) ),
							TTSAPI::new( 'APIKPI' )->setMethod( 'getKPIDefaultData' )
									->setSummary( 'Get default KPI data used for creating new KPIs. Use this before calling setKPI to get the correct default data.' ),
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Enabled (Required)' ),
						15 => TTi18n::gettext( 'Enabled (Optional)' ),
						20 => TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Scale Rating' ),
						20 => TTi18n::gettext( 'Yes/No' ),
						30 => TTi18n::gettext( 'Text' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-status'       => TTi18n::gettext( 'Status' ),
						'-1020-type'         => TTi18n::getText( 'Type' ),
						'-1030-name'         => TTi18n::gettext( 'Name' ),
						'-1040-description'  => TTi18n::gettext( 'Description' ),
						'-1050-minimum_rate' => TTi18n::gettext( 'Minimum Rating' ),
						'-1060-maximum_rate' => TTi18n::gettext( 'Maximum Rating' ),
						'-1080-kpi_group'    => TTi18n::gettext( 'Group' ),
						'-1090-display_order'=> TTi18n::gettext( 'Display Order' ),
						'-1900-tag'          => TTi18n::gettext( 'Tags' ),
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
						'status',
						'type',
						'name',
						'minimum_rate',
						'maximum_rate',
						'kpi_group',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [ 'name', ];
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
				'id'            => 'ID',
				'company_id'    => 'Company',
				'name'          => 'Name',
				'display_order' => 'DisplayOrder',
				'group_id'      => 'Group',
				'kpi_group'     => false,
				'type_id'       => 'Type',
				'type'          => false,
				'tag'           => 'Tag',
				'description'   => 'Description',
				'minimum_rate'  => 'MinimumRate',
				'maximum_rate'  => 'MaximumRate',
				'status_id'     => 'Status',
				'status'        => false,
				'deleted'       => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {

		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
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
		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		Debug::Text( 'Setting company_id data...	   ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );
		Debug::Text( 'Setting status_id data...	  ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param int $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->table . '
					where company_id = ?
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
	 * @return bool
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
	 * @return mixed
	 */
	function getDisplayOrder() {
		return $this->getGenericDataValue( 'display_order' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDisplayOrder( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'display_order', $value );
	}

	/**
	 * @return array|bool
	 */
	function getGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 2020, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setGroup( $ids ) {
		Debug::text( 'Setting Groups IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $ids, 'Setting Group data... ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 2020, $this->getID(), $ids );
	}


	/**
	 * @return bool
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
		Debug::Text( 'Setting description data...	' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|string
	 */
	function getMinimumRate() {
		$value = $this->getGenericDataValue( 'minimum_rate' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( (float)$value, 2 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumRate( $value ) {
		$value = trim( $value );
		$value = $this->Validator->stripNonFloat( $value );
		if ( $this->getType() == 10 ) {
			Debug::Text( 'Setting minimum_rate data...	 ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

			return $this->setGenericDataValue( 'minimum_rate', $value );
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getMaximumRate() {
		$value = $this->getGenericDataValue( 'maximum_rate' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( (float)$value, 2 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumRate( $value ) {
		$value = trim( $value );
		$value = $this->Validator->stripNonFloat( $value );
		if ( $this->getType() == 10 ) {
			Debug::Text( 'Setting maximum_rate data...' . $value, __FILE__, __LINE__, __METHOD__, 10 );

			return $this->setGenericDataValue( 'maximum_rate', $value );
		}

		return false;
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
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 310, $this->getID() );
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
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Type is invalid' ),
										  $this->getOptions( 'type' )
			);
		}
		// Name
		if ( $this->getName() !== false ) {
			if ( $this->getName() == '' ) {
				$this->Validator->isTrue( 'name',
										  false,
										  TTi18n::gettext( 'Name must be specified' )
				);
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is too short' ),
											3,
											9999
				);
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is too long, consider using description instead' ),
											0,
											100
				);
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isHTML( 'name',
										  $this->getName(),
										  TTi18n::gettext( 'Name contains invalid special characters' ),
				);
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name is already taken' )
				);
			}
		}

		// Display Order
		if ( $this->getDisplayOrder() == '' ) {
			$this->Validator->isTRUE( 'display_order',
									  false,
									  TTi18n::gettext( 'Display Order must be specified' )
			);
		}
		if ( $this->getDisplayOrder() != '' && $this->Validator->isError( 'display_order' ) == false ) {
			$this->Validator->isNumeric( 'display_order',
										 $this->getDisplayOrder(),
										 TTi18n::gettext( 'Invalid Display Order' )
			);
		}

		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is too short or too long' ),
									0,
									255
		);

		$this->Validator->isHTML( 'description',
								  $this->getDescription(),
								  TTi18n::gettext( 'Description contains invalid special characters' ),
		);

		// Minimum Rating
		if ( $this->getType() == 10 ) {
			if ( $this->getMinimumRate() !== false ) {
				$this->Validator->isLength( 'minimum_rate',
											$this->getMinimumRate(),
											TTi18n::gettext( 'Invalid Minimum Rating' ),
											1
				);
				if ( $this->Validator->isError( 'minimum_rate' ) == false ) {
					$this->Validator->isNumeric( 'minimum_rate',
												 $this->getMinimumRate(),
												 TTi18n::gettext( 'Minimum Rating must only be digits' )
					);
				}
				if ( $this->Validator->isError( 'minimum_rate' ) == false ) {
					$this->Validator->isLengthAfterDecimal( 'minimum_rate',
															$this->getMinimumRate(),
															TTi18n::gettext( 'Invalid Minimum Rating' ),
															0,
															2
					);
				}
			}
		}
		// Maximum Rating
		if ( $this->getType() == 10 ) {
			if ( $this->getMaximumRate() !== false ) {
				$this->Validator->isLength( 'maximum_rate',
											$this->getMaximumRate(),
											TTi18n::gettext( 'Invalid Maximum Rating' ),
											1
				);
				if ( $this->Validator->isError( 'maximum_rate' ) == false ) {
					$this->Validator->isNumeric( 'maximum_rate',
												 $this->getMaximumRate(),
												 TTi18n::gettext( 'Maximum Rating must only be digits' )
					);
				}
				if ( $this->Validator->isError( 'maximum_rate' ) == false ) {
					$this->Validator->isLengthAfterDecimal( 'maximum_rate',
															$this->getMaximumRate(),
															TTi18n::gettext( 'Invalid Maximum Rating' ),
															0,
															2
					);
				}
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getType() == 10 && $this->getMinimumRate() != '' && $this->getMaximumRate() != '' ) {
			if ( $this->getMinimumRate() >= $this->getMaximumRate() ) {
				$this->Validator->isTrue( 'minimum_rate', false, TTi18n::gettext( 'Minimum Rating should be lesser than Maximum Rating' ) );
			}
		}
		if ( $this->getDeleted() == true ) {
			$urlf = TTnew( 'UserReviewListFactory' ); /** @var UserReviewListFactory $urlf */
			$urlf->getByKpiId( $this->getId() );
			if ( $urlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use', false, TTi18n::gettext( 'KPI is in use' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 310, $this->getID(), $this->getTag() );
		}

		return true;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {

		Debug::Arr( $data, 'setObjectFromArray...', __FILE__, __LINE__, __METHOD__, 10 );
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
						case 'type':
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'kpi_group':
							$data[$variable] = $this->getColumn( $variable );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getCreatedBy(), false, $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'KPI' ), null, $this->getTable(), $this );
	}
}

?>
