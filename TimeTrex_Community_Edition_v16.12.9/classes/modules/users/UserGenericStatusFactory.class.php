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
class UserGenericStatusFactory extends Factory {
	protected $table = 'user_generic_status';
	protected $pk_sequence_name = 'user_generic_status_id_seq';          //PK Sequence name
	protected $batch_sequence_name = 'user_generic_status_batch_id_seq'; //PK Sequence name

	protected $batch_id = null;
	protected $queue = null;
	static protected $static_queue = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'batch_id' )->setFunctionMap( 'Batch' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'label' )->setFunctionMap( 'Label' )->setType( 'varchar' ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' ),
							TTSCol::new( 'link' )->setFunctionMap( 'Link' )->setType( 'varchar' ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserGenericStatus' )->setMethod( 'getUserGenericStatus' )
									->setSummary( 'Get user generic status records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserGenericStatus' )->setMethod( 'deleteUserGenericStatus' )
									->setSummary( 'Delete user generic status records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserGenericStatus' )->setMethod( 'getUserGenericStatus' ) ),
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Failed' ),
						20 => TTi18n::gettext( 'Warning' ),
						//25 => TTi18n::gettext('Notice'), //Friendly than a warning.
						30 => TTi18n::gettext( 'Success' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-label'       => TTi18n::gettext( 'Label' ),
						'-1020-status'      => TTi18n::gettext( 'Status' ),
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
						'label',
						'status',
						'description',
				];
				break;
		}

		return $retval;
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
	 * @return null|string
	 */
	function getNextBatchID() {
		$this->batch_id = TTUUID::generateUUID();

		return $this->batch_id;
	}

	/**
	 * @return bool|mixed
	 */
	function getBatchID() {
		return $this->getGenericDataValue( 'batch_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBatchID( $value ) {
		//$val = trim($val);
		return $this->setGenericDataValue( 'batch_id', $value );
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

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLabel() {
		return $this->getGenericDataValue( 'label' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLabel( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'label', $value );
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
	 * @return bool|mixed
	 */
	function getLink() {
		return $this->getGenericDataValue( 'link' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLink( $value ) {
		$value = trim( (string)$value );

		return $this->setGenericDataValue( 'link', $value );
	}

	//Static Queue functions

	/**
	 * @return bool
	 */
	static function isStaticQueue() {
		if ( is_array( self::$static_queue ) && count( self::$static_queue ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	static function getStaticQueue() {
		return self::$static_queue;
	}

	/**
	 * @return bool
	 */
	static function clearStaticQueue() {
		self::$static_queue = null;

		return true;
	}

	/**
	 * @param $label
	 * @param $status
	 * @param null $description
	 * @param null $link
	 * @return bool
	 */
	static function queueGenericStatus( $label, $status, $description = null, $link = null ) {
		Debug::Text( 'Add Generic Status row to queue... Label: ' . $label . ' Status: ' . $status, __FILE__, __LINE__, __METHOD__, 10 );
		$arr = [
				'label'       => $label,
				'status'      => $status,
				'description' => $description,
				'link'        => $link,
		];

		self::$static_queue[] = $arr;

		return true;
	}


	//Non-Static Queue functions

	/**
	 * @param $queue
	 * @return bool
	 */
	function setQueue( $queue ) {
		$this->queue = $queue;

		UserGenericStatusFactory::clearStaticQueue();

		return true;
	}

	/**
	 * @return bool
	 */
	function saveQueue() {
		if ( is_array( $this->queue ) ) {
			Debug::Arr( $this->queue, 'Generic Status Queue', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $this->queue as $key => $queue_data ) {

				$ugsf = TTnew( 'UserGenericStatusFactory' ); /** @var UserGenericStatusFactory $ugsf */
				$ugsf->setUser( $this->getUser() );
				if ( TTUUID::isUUID( $this->getBatchId() ) && $this->getBatchID() != TTUUID::getZeroID() && $this->getBatchID() != TTUUID::getNotExistID() ) {
					$ugsf->setBatchID( $this->getBatchID() );
				} else {
					$this->setBatchId( $this->getNextBatchId() );
				}

				$ugsf->setLabel( $queue_data['label'] );
				$ugsf->setStatus( $queue_data['status'] );
				$ugsf->setDescription( $queue_data['description'] );
				$ugsf->setLink( $queue_data['link'] );

				if ( $ugsf->isValid() ) {
					$ugsf->Save();

					unset( $this->queue[$key] );
				}
			}

			return true;
		}

		Debug::Text( 'Generic Status Queue Empty', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/*
	function addGenericStatus($label, $status, $description = NULL, $link = NULL ) {
		$this->setLabel( $label );
		$this->setStatus( $status );
		$this->setDescription( $description );
		$this->setLink( $link );

		$batch_id = $this->getBatchId();
		$user_id = $this->getUser();

		if ( $this->isValid() ) {
			$this->Save();

			$this->setBatchId( $batch_id );
			$this->setUser( $user_id );

			return TRUE;
		}

		return FALSE;
	}
	*/
	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
		// Batch ID
		$this->Validator->isUUID( 'batch_id',
								  $this->getBatchID(),
								  TTi18n::gettext( 'Batch ID is invalid' )
		);
		// Status
		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Status is invalid' ),
									  $this->getOptions( 'status' )
		);
		// Label
		$this->Validator->isLength( 'label',
									$this->getLabel(),
									TTi18n::gettext( 'Label is too short or too long' ),
									1, 1024
		);
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										1, 1024
			);
		}
		// Link
		if ( $this->getLink() != '' ) {
			$this->Validator->isLength( 'link',
										$this->getLink(),
										TTi18n::gettext( 'Link is too short or too long' ),
										1, 1024
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
	function preSave() {
		return true;
	}
}

?>
