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
class MessageSenderFactory extends Factory {
	protected $table = 'message_sender';
	protected $pk_sequence_name = 'message_sender_id_seq'; //PK Sequence name
	protected $obj_handler = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'parent_id' )->setFunctionMap( 'Parent' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'message_control_id' )->setFunctionMap( 'MessageControl' )->setType( 'uuid' )->setIsNull( false ),
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
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'parent_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMessageControl() {
		return $this->getGenericDataValue( 'message_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setMessageControl( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'message_control_id', $value );
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
		// Parent
		if ( $this->getParent() != TTUUID::getZeroID() ) {
			$mslf = TTnew( 'MessageSenderListFactory' ); /** @var MessageSenderListFactory $mslf */
			$this->Validator->isResultSetWithRows( 'parent',
												   $mslf->getByID( $this->getParent() ),
												   TTi18n::gettext( 'Parent is invalid' )
			);
		}
		// Message Control
		$mclf = TTnew( 'MessageControlListFactory' ); /** @var MessageControlListFactory $mclf */
		$this->Validator->isResultSetWithRows( 'message_control_id',
											   $mclf->getByID( $this->getMessageControl() ),
											   TTi18n::gettext( 'Message Control is invalid' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}
}

?>
