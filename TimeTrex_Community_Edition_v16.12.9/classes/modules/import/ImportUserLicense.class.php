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
class ImportUserLicense extends Import {

	public $class_name = 'APIUserLicense';
	protected int $qualification_type_id = 30;
	public $qualification_options = false;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$ulf = TTNew( 'UserLicenseFactory' ); /** @var UserLicenseFactory $ulf */
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( [ 'qualification', 'license_number', 'license_issued_date', 'license_expiry_date', ], Misc::trimSortPrefix( $ulf->getOptions( 'columns' ) ) ) );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'qualification' => 'qualification_id',
				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match'			 => TTi18n::getText( 'Enable smart matching.' ),
						'-1050-create_qualification' => TTi18n::getText( 'Create licenses that don\'t already exist.' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

				$retval = [
						'license_issued_date' => $upf->getOptions( 'date_format' ),
						'license_expiry_date' => $upf->getOptions( 'date_format' )
				];
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
		$user_license_default_data = $this->getObject()->stripReturnHandler( $this->getObject()->getUserLicenseDefaultData() ); //Get default data.
		$retval = $user_license_default_data;

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
			$raw_row['user_id'] = TTUUID::getNotExistID(); //Some factories won't validate the user if its not specified at all, so mass edit works properly. Therefore set this to a not exists UUID.
			//unset( $raw_row['user_id'] );
		}

		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setUserLicense( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return bool|false|int
	 */
	function parse_license_issued_date( $input, $default_value = null, $parse_hint = null ) {
		if ( isset( $parse_hint ) && $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );

			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return bool|false|int
	 */
	function parse_license_expiry_date( $input, $default_value = null, $parse_hint = null ) {
		if ( isset( $parse_hint ) && $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );

			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}
}

?>
