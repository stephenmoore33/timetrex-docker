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
 * @package Modules\Import
 */
class ImportUserContact extends Import {

	public $class_name = 'APIUserContact';
	public $ethnic_group_options = false;

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				global $current_company;

				$ucf = TTNew( 'UserContactFactory' ); /** @var UserContactFactory $ucf */
				$retval = $ucf->getOptions( 'columns' );

				$retval = Misc::trimSortPrefix( $retval );
				Debug::Arr( $retval, 'ImportUserContactColumns: ', __FILE__, __LINE__, __METHOD__, 10 );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'status'       => 'status_id',
						'type'         => 'type_id',
						'user'         => 'user_id',
						'sex'          => 'sex_id',
						'ethnic_group' => 'ethnic_group_id',
				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match'         => TTi18n::getText( 'Enable smart matching.' ),
						'-1020-create_ethnic_group' => TTi18n::getText( 'Create ethnic groups that don\'t already exist.' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
				$retval = [
						'first_name'  => [
								'-1010-first_name'             => TTi18n::gettext( 'First Name' ),
								'-1020-first_last_name'        => TTi18n::gettext( 'FirstName LastName' ),
								'-1030-last_first_name'        => TTi18n::gettext( 'LastName, FirstName' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'last_name'   => [
								'-1010-last_name'              => TTi18n::gettext( 'Last Name' ),
								'-1020-first_last_name'        => TTi18n::gettext( 'FirstName LastName' ),
								'-1030-last_first_name'        => TTi18n::gettext( 'LastName, FirstName' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'middle_name' => [
								'-1010-middle_name'            => TTi18n::gettext( 'Middle Name' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'birth_date'  => $upf->getOptions( 'date_format' ),
				];
				$retval = $upf->getCustomFieldsParseHints( $retval, null, 'user_contact' );
				break;
		}

		return $retval;
	}


	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function _preParseRow( $row_number, $raw_row ) {
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getUserContactDefaultData() );

		return $retval;
	}

	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function _postParseRow( $row_number, $raw_row ) {
		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == false ) {
			//User Contact does not check validation if this is false. This is because during mass edit the error needs to be ignored
			//Howver, a user_id is actually required for the user contact to be saved, so we must be able to trigger that exception.
			$raw_row['user_id'] = TTUUID::getNotExistID(); //Some factories won't validate the user if its not specified at all, so mass edit works properly. Therefore set this to a not exists UUID.
			//$raw_row['user_id'] = TTUUID::getZeroID();
		}

		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setUserContact( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @return bool
	 */
	function getEthnicGroupOptions() {
		//Get groups
		$uglf = TTNew( 'EthnicGroupListFactory' ); /** @var EthnicGroupListFactory $uglf */
		$uglf->getByCompanyId( $this->company_id );
		$this->ethnic_group_options = (array)$uglf->getArrayByListFactory( $uglf, false );
		unset( $uglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_ethnic_group( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No group
		}

		if ( !is_array( $this->ethnic_group_options ) ) {
			$this->getEthnicGroupOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->ethnic_group_options );

		if ( $retval === false ) {
			if ( $this->getImportOptions( 'create_ethnic_group' ) == true ) {
				$egf = TTnew( 'EthnicGroupFactory' ); /** @var EthnicGroupFactory $egf */
				$egf->setCompany( $this->company_id );
				$egf->setName( $input );

				if ( $egf->isValid() ) {
					$new_group_id = $egf->Save();
					$this->getEthnicGroupOptions(); //Update group records after we've added a new one.
					Debug::Text( 'Created new ethnic group name: ' . $input . ' ID: ' . $new_group_id, __FILE__, __LINE__, __METHOD__, 10 );

					return $new_group_id;
				}
				unset( $egf, $new_group_id );
			}

			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return int
	 */
	function parse_status( $input, $default_value = null, $parse_hint = null ) {
		if ( strtolower( $input ) == 'e'
				|| strtolower( $input ) == 'enabled' ) {
			$retval = 10;
		} else if ( strtolower( $input ) == 'd'
				|| strtolower( $input ) == 'disabled' ) {
			$retval = 20;
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_type( $input, $default_value = null, $parse_hint = null ) {
		$uf = TTnew( 'UserContactFactory' ); /** @var UserContactFactory $uf */
		$options = $uf->getOptions( 'type' );

		if ( isset( $options[$input] ) ) {
			return $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}
	}

}

?>
