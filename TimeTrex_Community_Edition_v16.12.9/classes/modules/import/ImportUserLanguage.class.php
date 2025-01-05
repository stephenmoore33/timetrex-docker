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
class ImportUserLanguage extends Import {

	public $class_name = 'APIUserLanguage';
	protected int $qualification_type_id = 40;
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
				$ulf = TTNew( 'UserLanguageFactory' ); /** @var UserLanguageFactory $ulf */
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( [ 'qualification', 'fluency', 'competency', 'description' ], Misc::trimSortPrefix( $ulf->getOptions( 'columns' ) ) ) );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'fluency'       => 'fluency_id',
						'competency'    => 'competency_id',
						'qualification' => 'qualification_id',
				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match'          => TTi18n::getText( 'Enable smart matching.' ),
						'-1050-create_qualification' => TTi18n::getText( 'Create languages that don\'t already exist.' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

				$retval = [];
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
		$user_language_default_data = $this->getObject()->stripReturnHandler( $this->getObject()->getUserLanguageDefaultData() ); //Get default data.
		$user_language_default_data['fluency_id'] = ''; //If the user doesn't specify fluency this will trigger a friendly validation error stating that it must be.
		$user_language_default_data['competency_id'] = ''; //If the user doesn't specify competency this will trigger a friendly validation error stating that it must be.
		$retval = $user_language_default_data;

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
		return $this->getObject()->setUserLanguage( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_competency( $input, $default_value = null, $parse_hint = null ) {
		$ulf = TTnew( 'UserLanguageFactory' );/** @var UserLanguageFactory $umf */
		$options = Misc::trimSortPrefix( $ulf->getOptions( 'competency' ) );

		if ( isset( $options[$input] ) ) {
			$retval = $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				$retval = $this->findClosestMatch( $input, $options, 50 );
			} else {
				$retval = array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_fluency( $input, $default_value = null, $parse_hint = null ) {
		$ulf = TTnew( 'UserLanguageFactory' );/** @var UserLanguageFactory $umf */
		$options = Misc::trimSortPrefix( $ulf->getOptions( 'fluency' ) );

		if ( isset( $options[$input] ) ) {
			$retval = $input;
		} else {
			if ( $this->getImportOptions( 'fuzzy_match' ) == true ) {
				$retval = $this->findClosestMatch( $input, $options, 50 );
			} else {
				$retval = array_search( strtolower( $input ), array_map( 'strtolower', $options ) );
			}
		}

		return $retval;
	}
}

?>
