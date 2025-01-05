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
 * @package Core
 */
class SystemSettingFactory extends Factory {
	protected $table = 'system_setting';
	protected $pk_sequence_name = 'system_setting_id_seq'; //PK Sequence name

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'value' )->setFunctionMap( 'Value' )->setType( 'varchar' )->setIsNull( true )
					)
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
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$ph = [
				'name' => $name,
		];

		$query = 'select id from ' . $this->getTable() . ' where name = ?';
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

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue() {
		return $this->getGenericDataValue( 'value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									1, 250
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name already exists' )
			);
		}
		// Value
		$this->Validator->isLength( 'value',
									$this->getValue(),
									TTi18n::gettext( 'Value is too short or too long' ),
									1, 4096
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	//This table doesn't have any of these columns, so overload the functions.

	/**
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( 'all' );
		$this->removeCache( $this->getName() );

		return true;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool|int|string
	 */
	static function setSystemSetting( $key, $value ) {
		$retval = false;

		$sslf = new SystemSettingListFactory();
		$sslf->StartTransaction();
		$sslf->getByName( $key );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = new SystemSettingFactory();
		}
		$obj->setName( $key );
		$obj->setValue( $value );
		if ( $obj->isValid() ) {
			Debug::Text( 'Key: ' . $key . ' Value: ' . $value . ' isNew: ' . (int)$obj->isNew(), __FILE__, __LINE__, __METHOD__, 10 );

			$retval = $obj->Save();
		} else {
			Debug::Text( '  ERROR: Unable to set SystemSetting record! Key: ' . $key . ' Value: ' . $value . ' isNew: ' . (int)$obj->isNew(), __FILE__, __LINE__, __METHOD__, 10 );
			$sslf->FailTransaction();
		}

		$sslf->CommitTransaction();

		return $retval;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	static function getSystemSettingValueByKey( $key ) {
		global $db;
		if ( isset( $db ) && is_object( $db ) ) {
			$sslf = new SystemSettingListFactory();
			$sslf->getByName( $key );
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();

				return $obj->getValue();
			} else if ( $sslf->getRecordCount() > 1 ) {
				Debug::Text( 'ERROR: ' . $sslf->getRecordCount() . ' SystemSetting record(s) exists with key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'WARNING: No database connection exists when trying to get record with key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $key
	 * @return bool|mixed
	 */
	static function getSystemSettingObjectByKey( $key ) {
		$sslf = new SystemSettingListFactory();
		$sslf->getByName( $key );
		if ( $sslf->getRecordCount() == 1 ) {
			return $sslf->getCurrent();
		}

		return false;
	}

	/**
	 * Checks if system has valid install requirments and sends a notification to users that need to be notified.
	 * @return bool
	 */
	static function checkValidInstallRequirments( $failed_requirements = null ) {
		if ( DEMO_MODE == false && PRODUCTION == true ) {
			$sslf = new SystemSettingListFactory();
			$system_settings = $sslf->getAllArray();

			if ( isset( $system_settings['valid_install_requirements'] ) && DEPLOYMENT_ON_DEMAND == false && (int)$system_settings['valid_install_requirements'] == 0  ) {
				$notification_data = [
						'object_id'      => TTUUID::getNotExistID( 1030 ),
						'object_type_id' => 0,
						'priority_id'	 => 2, //High
						'type_id'        => 'system',
						'title_short'    => TTi18n::getText( 'WARNING: System requirement check failed.' ),
						'body_short'     => TTi18n::getText( '%1 system requirement check has failed! Please contact your %1 administrator immediately to re-run the %1 web installer to correct the issue.', APPLICATION_NAME ) ."\n\n". TTi18n::getText( 'Failed Requirements: %1', implode( ',', $failed_requirements ) ),
						'body_long_html' => TTi18n::getText( '%1 system requirement check has failed! Please contact your %1 administrator immediately to re-run the %1 web installer to correct the issue.', APPLICATION_NAME ) ."\n\n". TTi18n::getText( 'Failed Requirements: %1', implode( ',', $failed_requirements ) ), //Use this to append email footer.
				];

				Notification::sendNotificationToAllUsers( 80, true, true, $notification_data, ( 6 * 86400 ) ); //Send to all companies as its possibly an urgent issue that needs to be resolved ASAP.
			}
		}
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'System Setting - Name' ) . ': ' . $this->getName() . ' ' . TTi18n::getText( 'Value' ) . ': ' . $this->getValue(), null, $this->getTable() );
	}
}

?>
