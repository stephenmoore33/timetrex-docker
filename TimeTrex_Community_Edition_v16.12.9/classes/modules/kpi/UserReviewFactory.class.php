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
class UserReviewFactory extends Factory {
	protected $table = 'user_review';
	protected $pk_sequence_name = 'user_review_id_seq'; //PK Sequence name
	protected $kpi_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_review_control_id' )->setFunctionMap( 'UserReviewControl' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'kpi_id' )->setFunctionMap( 'KPI' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'rating' )->setFunctionMap( 'Rating' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'text' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'user_review_control_id' )->setType( 'uuid' )->setColumn( 'a.user_review_control_id' )->setMulti( true ),
							TTSSearchField::new( 'kpi_id' )->setType( 'uuid' )->setColumn( 'a.kpi_id' )->setMulti( true ),
							TTSSearchField::new( 'term_id' )->setType( 'numeric' )->setColumn( 'urcf.term_id' )->setMulti( true ),
							TTSSearchField::new( 'severity_id' )->setType( 'numeric' )->setColumn( 'urcf.severity_id' )->setMulti( true ),
							TTSSearchField::new( 'rating' )->setType( 'numeric' )->setColumn( 'a.rating' ),
							TTSSearchField::new( 'note' )->setType( 'text' )->setColumn( 'a.note' ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserReview' )->setMethod( 'getUserReview' )
									->setSummary( 'Get user review records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserReview' )->setMethod( 'setUserReview' )
									->setSummary( 'Add or edit user review records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserReview' )->setMethod( 'deleteUserReview' )
									->setSummary( 'Delete user review records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserReview' )->setMethod( 'getUserReview' ) ),
											   ) ),
							TTSAPI::new( 'APIUserReview' )->setMethod( 'getUserReviewDefaultData' )
									->setSummary( 'Get default user review data used for creating new user reviews. Use this before calling setUserReview to get the correct default data.' ),
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
			case 'columns':
				$retval = [
						'-2050-rating'       => TTi18n::gettext( 'Rating' ),
						'-1200-note'         => TTi18n::gettext( 'Note' ),
						'-1300-tag'          => TTi18n::gettext( 'Tags' ),
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
						'rating',
						'note',
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
				'id'                     => 'ID',
				'user_review_control_id' => 'UserReviewControl',
				'kpi_id'                 => 'KPI',
				'display_order'          => false,
				'name'                   => false,
				'type_id'                => false,
				'status_id'              => false,
				'minimum_rate'           => false,
				'maximum_rate'           => false,
				'description'            => false,
				'rating'                 => 'Rating',
				'note'                   => 'Note',
				'tag'                    => 'Tag',
				'deleted'                => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getKPIObject() {
		return $this->getGenericObject( 'KPIListFactory', $this->getKPI(), 'kpi_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getKPI() {
		return $this->getGenericDataValue( 'kpi_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setKPI( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'kpi_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserReviewControl() {
		return $this->getGenericDataValue( 'user_review_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUserReviewControl( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_review_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRating() {
		return $this->getGenericDataValue( 'rating' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRating( $value ) {
		$value = trim( $value );
		if ( $value == '' ) {
			$value = null;
		}

		return $this->setGenericDataValue( 'rating', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'note', $value );
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
		} else if ( is_object( $this->getKPIObject() )
				&& TTUUID::isUUID( $this->getKPIObject()->getCompany() ) && $this->getKPIObject()->getCompany() != TTUUID::getZeroID() && $this->getKPIObject()->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID()
		) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getKPIObject()->getCompany(), 330, $this->getID() );
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
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// KPI
		$klf = TTnew( 'KPIListFactory' ); /** @var KPIListFactory $klf */
		$this->Validator->isResultSetWithRows( 'kpi_id',
											   $klf->getById( $this->getKPI() ),
											   TTi18n::gettext( 'Invalid KPI' )
		);
		// review control
		$urclf = TTnew( 'UserReviewControlListFactory' ); /** @var UserReviewControlListFactory $urclf */
		$this->Validator->isResultSetWithRows( 'user_review_control_id',
											   $urclf->getById( $this->getUserReviewControl() ),
											   TTi18n::gettext( 'Invalid review control' )
		);
		// Rating
		if ( $this->getRating() != null ) {
			$this->Validator->isNumeric( 'rating',
										 $this->getRating(),
										 TTi18n::gettext( 'Rating must only be digits' )
			);
			if ( $this->Validator->isError( 'rating' ) == false ) {
				$this->Validator->isLengthBeforeDecimal( 'rating',
														 $this->getRating(),
														 TTi18n::gettext( 'Invalid Rating' ),
														 0,
														 7
				);
			}
			if ( $this->Validator->isError( 'rating' ) == false ) {
				$this->Validator->isLengthAfterDecimal( 'rating',
														$this->getRating(),
														TTi18n::gettext( 'Invalid Rating' ),
														0,
														2
				);
			}
		}
		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too long' ),
										0, 4096
			);

			$this->Validator->isHTML( 'note',
									  $this->getNote(),
									  TTi18n::gettext( 'Note contains invalid special characters' ),
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

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getKPIObject()->getCompany(), 330, $this->getID(), $this->getTag() );
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
						case 'name':
						case 'type_id':
						case 'status_id':
						case 'minimum_rate':
						case 'maximum_rate':
						case 'description':
						case 'display_order':
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
		$kpi_obj = $this->getKPIObject();
		if ( is_object( $kpi_obj ) ) {
			return TTLog::addEntry( $this->getUserReviewControl(), $log_action, TTi18n::getText( 'Employee Review KPI' ) . ' - ' . TTi18n::getText( 'KPI' ) . ': ' . $kpi_obj->getName(), null, $this->getTable(), $this );
		}

		return false;
	}

}

?>
