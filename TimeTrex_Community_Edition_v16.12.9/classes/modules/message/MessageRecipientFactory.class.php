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
 * @package Modules\Message
 */
class MessageRecipientFactory extends Factory {
	protected $table = 'message_recipient';
	protected $pk_sequence_name = 'message_recipient_id_seq'; //PK Sequence name
	protected $obj_handler = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'message_sender_id' )->setFunctionMap( 'MessageSender' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status_date' )->setFunctionMap( 'StateDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'ack' )->setFunctionMap( 'Ack' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'ack_date' )->setFunctionMap( 'AckDate' )->setType( 'integer' )->setIsNull( true ),
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
			//No API Methods.
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
						10 => TTi18n::gettext( 'UNREAD' ),
						20 => TTi18n::gettext( 'READ' ),
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
				'id' => 'ID',

				'user_id'            => 'User',
				'message_sender_id'  => 'MessageSender',
				'status_id'          => 'Status',
				'status_date'        => 'StateDate',
				'ack'                => 'Ack',
				'ack_date'           => 'AckDate',
				'message_control_id' => false,
				'deleted'            => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return mixed
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
	function getMessageSender() {
		return $this->getGenericDataValue( 'message_sender_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setMessageSender( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'message_sender_id', $value );
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
		$this->setStatusDate();

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStatusDate() {
		return $this->getGenericDataValue( 'status_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStatusDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'status_date', $value );
	}

	/**
	 * @return bool
	 */
	function isAck() {
		if ( $this->getRequireAck() == true && $this->getAckDate() == '' ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function getAck() {
		return $this->fromBool( $this->getGenericDataValue( 'ack' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAck( $value ) {
		$this->setGenericDataValue( 'ack', $this->toBool( $value ) );
		if ( $this->getAck() == true ) {
			$this->setAckDate();
			$this->setAckBy();
		}

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getAckDate() {
		return $this->getGenericDataValue( 'ack_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setAckDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'ack_date', $value );
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getUser(), 'message_control' ); //Clear cache so badge counts can be updated immediately. See MessageControlListFactory->getNewMessagesByCompanyIdAndUserId()

		return true;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Employee
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}
		// Message Sender
		if ( $this->isNew() == true ) { //If the sender deletes their sent message, this validation will fail if the receiving tries to view/mark the message as read.
			if ( $this->getMessageSender() !== false ) {
				$mslf = TTnew( 'MessageSenderListFactory' ); /** @var MessageSenderListFactory $mslf */
				$this->Validator->isResultSetWithRows( 'message_sender_id',
													   $mslf->getByID( $this->getMessageSender() ),
													   TTi18n::gettext( 'Message Sender is invalid' )
				);
			}
		}

		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Date
		if ( $this->getStatusDate() !== false ) {
			$this->Validator->isDate( 'status_date',
									  $this->getStatusDate(),
									  TTi18n::gettext( 'Incorrect Date' )
			);
		}
		// Acknowledge Date
		if ( $this->getAckDate() !== false ) {
			$this->Validator->isDate( 'ack_date',
									  $this->getAckDate(),
									  TTi18n::gettext( 'Invalid Acknowledge Date' )
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
		if ( $this->getStatus() == false ) {
			$this->setStatus( 10 ); //UNREAD
		}

		return true;
	}
}

?>
