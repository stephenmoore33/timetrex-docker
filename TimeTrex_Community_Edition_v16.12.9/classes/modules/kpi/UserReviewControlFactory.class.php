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
class UserReviewControlFactory extends Factory {
	protected $table = 'user_review_control';
	protected $pk_sequence_name = 'user_review_control_id_seq'; //PK Sequence name
	protected $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'reviewer_user_id' )->setFunctionMap( 'ReviewerUser' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'term_id' )->setFunctionMap( 'Term' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'severity_id' )->setFunctionMap( 'Severity' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'end_date' )->setFunctionMap( 'EndDate' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'due_date' )->setFunctionMap( 'DueDate' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'rating' )->setFunctionMap( 'Rating' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'tag' )->setFunctionMap( 'Tag' )->setType( 'string' )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type' )->setType( 'text' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'term' )->setType( 'text' )->setColumn( 'a.term_id' )->setMulti( true ),
							TTSSearchField::new( 'severity' )->setType( 'text' )->setColumn( 'a.severity_id' )->setMulti( true ),
							TTSSearchField::new( 'reviewer_user_id' )->setType( 'uuid_list' )->setColumn( 'a.reviewer_user_id' )->setMulti( true ),
							TTSSearchField::new( 'reviewer_user' )->setType( 'user_id_or_name' )->setColumn( [ 'a.reviewer_user_id', 'uf.first_name', 'uf.last_name' ] ),
							TTSSearchField::new( 'user' )->setType( 'user_id_or_name' )->setColumn( [ 'a.user_id', 'uf.first_name', 'uf.last_name' ] ),
							TTSSearchField::new( 'exclude_reviewer_user_id' )->setType( 'not_uuid_list' )->setColumn( 'a.reviewer_user_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'term_id' )->setType( 'numeric_list' )->setColumn( 'a.term_id' )->setMulti( true ),
							TTSSearchField::new( 'severity_id' )->setType( 'numeric_list' )->setColumn( 'a.severity_id' )->setMulti( true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'due_date' )->setType( 'date_range' )->setColumn( 'a.due_date' ),
							TTSSearchField::new( 'kpi_id' )->setType( 'uuid_list' )->setColumn( 'urf.kpi_id' )->setMulti( true ),
							TTSSearchField::new( 'rating' )->setType( 'numeric' )->setColumn( 'a.rating' ),
							TTSSearchField::new( 'note' )->setType( 'text' )->setColumn( 'a.note' ),
							TTSSearchField::new( 'start_date' )->setType( 'date_range' )->setColumn( 'a.start_date' ),
							TTSSearchField::new( 'end_date' )->setType( 'date_range' )->setColumn( 'a.end_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserReviewControl' )->setMethod( 'getUserReviewControl' )
									->setSummary( 'Get user review control records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserReviewControl' )->setMethod( 'setUserReviewControl' )
									->setSummary( 'Add or edit user review control records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserReviewControl' )->setMethod( 'deleteUserReviewControl' )
									->setSummary( 'Delete user review control records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserReviewControl' )->setMethod( 'getUserReviewControl' ) ),
											   ) ),
							TTSAPI::new( 'APIUserReviewControl' )->setMethod( 'getUserReviewControlDefaultData' )
									->setSummary( 'Get default user review control data used for creating new user review controls. Use this before calling setUserReviewControl to get the correct default data.' ),
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
			case 'type':
				$retval = [
						10  => TTi18n::gettext( 'Accolade' ),
						15  => TTi18n::gettext( 'Discipline' ),
						20  => TTi18n::gettext( 'Review (General)' ),
						25  => TTi18n::gettext( 'Review (Wage)' ),
						30  => TTi18n::gettext( 'Review (Performance)' ),
						33  => TTi18n::gettext( 'Incident' ), //Something not resulting in an Accident/Injury
						35  => TTi18n::gettext( 'Accident/Injury' ),
						37  => TTi18n::gettext( 'Background Check' ),
						38  => TTi18n::gettext( 'Drug Test' ),
						39  => TTi18n::gettext( 'Health/Immunization' ),
						40  => TTi18n::gettext( 'Entrance Interview' ),
						45  => TTi18n::gettext( 'Exit Interview' ),
						90  => TTi18n::gettext( 'Benefits' ), //Tracking health/dental benefit changes
						100 => TTi18n::gettext( 'Miscellaneous' ),
				];
				break;
			case 'term':
				$retval = [
						10 => TTi18n::gettext( 'Positive' ),
						20 => TTi18n::gettext( 'Neutral' ),
						30 => TTi18n::gettext( 'Negative' ),
				];
				break;
			case 'severity':
				$retval = [
						10 => TTi18n::gettext( 'Normal' ),
						20 => TTi18n::gettext( 'Low' ),
						30 => TTi18n::gettext( 'Medium' ),
						40 => TTi18n::gettext( 'High' ),
						50 => TTi18n::gettext( 'Critical' ),
				];
				break;
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Scheduled' ),
						20 => TTi18n::gettext( 'Being Reviewed' ),
						30 => TTi18n::gettext( 'Complete' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-user_first_name'  => TTi18n::gettext( 'First Name' ),
						'-1015-user_middle_name' => TTi18n::gettext( 'Middle Name' ),
						'-1020-user_last_name'   => TTi18n::gettext( 'Last Name' ),
						'-1030-user'             => TTi18n::gettext( 'Full Name' ),
						'-1040-employee_number'  => TTi18n::gettext( 'Employee #' ),
						'-1050-reviewer_user' => TTi18n::gettext( 'Reviewer Name' ),
						'-1060-start_date'    => TTi18n::gettext( 'Start Date' ),
						'-1070-end_date'      => TTi18n::gettext( 'End Date' ),
						'-1080-due_date'      => TTi18n::gettext( 'Due Date' ),
						'-1090-type'          => TTi18n::gettext( 'Type' ),
						'-1100-term'          => TTi18n::gettext( 'Terms' ),
						'-1110-severity'      => TTi18n::gettext( 'Severity/Importance' ),
						'-1120-status'        => TTi18n::gettext( 'Status' ),
						'-1130-rating'        => TTi18n::gettext( 'Overall Rating' ),
						'-1200-note'          => TTi18n::gettext( 'Notes' ),
						'-1300-tag'           => TTi18n::gettext( 'Tags' ),

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
						'user',
						'reviewer_user',
						'type',
						'term',
						'severity',
						'start_date',
						'end_date',
						'due_date',
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
				'user'             => false,
				'user_first_name'  => false,
				'user_middle_name' => false,
				'user_last_name'   => false,
				'employee_number'  => false,
				'reviewer_user_id' => 'ReviewerUser',
				'reviewer_user'    => false,
				'type_id'          => 'Type',
				'type'             => false,
				'term_id'          => 'Term',
				'term'             => false,
				'severity_id'      => 'Severity',
				'severity'         => false,
				'status_id'        => 'Status',
				'status'           => false,
				'start_date'       => 'StartDate',
				'end_date'         => 'EndDate',
				'due_date'         => 'DueDate',
				'rating'           => 'Rating',
				'note'             => 'Note',
				'tag'              => 'Tag',

				'deleted' => 'Deleted',
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

		//$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getReviewerUser() {
		return $this->getGenericDataValue( 'reviewer_user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setReviewerUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'reviewer_user_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getTerm() {
		return $this->getGenericDataValue( 'term_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTerm( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'term_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getSeverity() {
		return $this->getGenericDataValue( 'severity_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSeverity( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'severity_id', $value );
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
	function getStartDate() {
		return (int)$this->getGenericDataValue( 'start_date' );
	}

	/**
	 * @param int $value EPOCH
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDueDate() {
		return (int)$this->getGenericDataValue( 'due_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDueDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'due_date', $value );
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
		} else if ( is_object( $this->getUserObject() )
				&& TTUUID::isUUID( $this->getUserObject()->getCompany() ) && $this->getUserObject()->getCompany() != TTUUID::getZeroID() && $this->getUserObject()->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID()
		) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getUserObject()->getCompany(), 320, $this->getID() );
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

		// employee
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user_id',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid employee' )
		);
		// reviewer
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'reviewer_user_id',
											   $ulf->getByID( $this->getReviewerUser() ),
											   TTi18n::gettext( 'Invalid reviewer' )
		);
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);
		// Terms
		$this->Validator->inArrayKey( 'term',
									  $this->getTerm(),
									  TTi18n::gettext( 'Incorrect Terms' ),
									  $this->getOptions( 'term' )
		);
		// Severity
		$this->Validator->inArrayKey( 'severity',
									  $this->getSeverity(),
									  TTi18n::gettext( 'Incorrect Severity' ),
									  $this->getOptions( 'severity' )
		);
		// Status
		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status' ),
									  $this->getOptions( 'status' )
		);
		// start date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}
		// end date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
			);
		}
		// due date
		if ( $this->getDueDate() != '' ) {
			$this->Validator->isDate( 'due_date',
									  $this->getDueDate(),
									  TTi18n::gettext( 'Incorrect due date' )
			);
		}

		// Rating
		if ( $this->getRating() != null ) {
			$this->Validator->isNumeric( 'rating',
										 $this->getRating(),
										 TTi18n::gettext( 'Rating must only be digits' )
			);
			if ( $this->Validator->isError( 'rating' ) == false ) {
				$this->Validator->isLengthAfterDecimal( 'rating',
														$this->getRating(),
														TTi18n::gettext( 'Invalid Rating' ),
														0,
														2
				);
			}
			if ( $this->Validator->isError( 'rating' ) == false ) {
				$this->Validator->isLessThan( 'rating',
											  $this->getRating(),
											  TTi18n::gettext( 'Rating must be less than 99999' ),
											  99999
				);
			}
			if ( $this->Validator->isError( 'rating' ) == false ) {
				$this->Validator->isGreaterThan( 'rating',
											  $this->getRating(),
											  TTi18n::gettext( 'Rating must be higher than -99999' ),
											  -99999
				);
			}
		}
		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too short or too long' ),
										2, 10240
			);

			$this->Validator->isHTML( 'note',
									  $this->getNote(),
									  TTi18n::gettext( 'Note contains invalid special characters' ),
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		$start_date = $this->getStartDate();
		$end_date = $this->getEndDate();
		$due_date = $this->getDueDate();
		if ( $start_date != '' && $end_date != '' && $due_date != '' ) {
			if ( $end_date < $start_date ) {
				$this->Validator->isTrue( 'end_date',
										  false,
										  TTi18n::gettext( 'End date should be after start date' )
				);
			}
			if ( $due_date < $start_date ) {
				$this->Validator->isTrue( 'due_date',
										  false,
										  TTi18n::gettext( 'Due date should be after start date' )
				);
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//SQL schema has NOT NULL constraints on start_date, end_date and due_date, which causes problem when trying to copy these records. So force them to always be set here.
		if ( empty( $this->getStartDate() ) ) {
			$this->setStartDate( 0 );
		}

		if ( empty( $this->getEndDate() ) ) {
			$this->setEndDate( 0 );
		}

		if ( empty( $this->getDueDate() ) ) {
			$this->setDueDate( 0 );
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
			CompanyGenericTagMapFactory::setTags( $this->getUserObject()->getCompany(), 320, $this->getID(), $this->getTag() );
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
						case 'start_date':
							$this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
							break;
						case 'end_date':
							$this->setEndDate( TTDate::parseDateTime( $data['end_date'] ) );
							break;
						case 'due_date':
							$this->setDueDate( TTDate::parseDateTime( $data['due_date'] ) );
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
						case 'type':
						case 'term':
						case 'severity':
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_first_name':
						case 'user_middle_name':
						case 'user_last_name':
						case 'employee_number':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user':
							$data[$variable] = Misc::getFullName( $this->getColumn( 'user_first_name' ), null, $this->getColumn( 'user_last_name' ), true, true );
							break;
						case 'reviewer_user':
							$data[$variable] = Misc::getFullName( $this->getColumn( 'reviewer_user_first_name' ), null, $this->getColumn( 'reviewer_user_last_name' ), true, true );
							break;
						case 'start_date':
							$data['start_date'] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
							break;
						case 'end_date':
							$data['end_date'] = TTDate::getAPIDate( 'DATE', $this->getEndDate() );
							break;
						case 'due_date':
							$data['due_date'] = TTDate::getAPIDate( 'DATE', $this->getDueDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Review' ) . ' - ' . TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ) . ', ' . TTi18n::getText( 'Status' ) . ': ' . Option::getByKey( $this->getStatus(), $this->getOptions( 'status' ) ), null, $this->getTable(), $this );
	}
}

?>
