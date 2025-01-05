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
class ImportUserDeduction extends Import {

	public $class_name = 'APIUserDeduction';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), [ 'company_deduction_id' => TTi18n::getText( 'Tax / Deduction' ), 'start_date' => TTi18n::getText( 'Start Date' ), 'end_date' => TTi18n::getText( 'End Date' ), ] );

				global $current_user;

				//Get a Tax/Deductions that can be imported.
				$cdlf = TTNew( 'CompanyDeductionListFactory' );
				$cdlf->getByCompanyId( $current_user->getCompany() );
				if ( $cdlf->getRecordCount() > 0 ) {
					foreach( $cdlf as $cd_obj ) { /** @var CompanyDeductionFactory $cd_obj */
						$calculation_type_column_meta_data = $cd_obj->getOptions( 'calculation_type_column_meta_data', [ 'calculation_id' => $cd_obj->getCalculation(), 'country' => $cd_obj->getCountry(), 'province' => $cd_obj->getProvince() ] );
						if ( is_array( $calculation_type_column_meta_data ) ) {
							foreach ( $calculation_type_column_meta_data as $key => $meta_data ) {
								$column_key = $cd_obj->getUniqueKeyPrefix( $key );
								$column_name = $cd_obj->getUniqueNamePrefix( $meta_data['name'] );
								$retval[$column_key] = $column_name;
							}
						}
					}
				}

				//Debug::Arr( $retval, 'Import Columns: ', __FILE__, __LINE__, __METHOD__, 10);
				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match' => TTi18n::getText( 'Enable smart matching.' ),
						'-1015-update'      => TTi18n::getText( 'Update existing records based on Employee and Tax / Deduction' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

				$retval = [
						'start_date' => $upf->getOptions( 'date_format' ),
						'end_date'   => $upf->getOptions( 'date_format' ),
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
		//$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getUserDeductionDefaultData() );

		return $raw_row;
	}

	function getCompanyDeductionObject( $id ) {
		$cdlf = TTnew('CompanyDeductionListFactory');
		$cdlf->getByIdAndCompanyId( $id, $this->company_id );
		if ( $cdlf->getRecordCount() == 1 ) {
			return $cdlf->getCurrent();
		}

		return false;
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

		if ( $this->getImportOptions( 'update' ) == true ) {
			Debug::Text( 'Updating existing records, try to find record... ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( isset( $raw_row['user_id'] ) && isset( $raw_row['company_deduction_id'] ) ) {
				$udlf = TTnew( 'UserDeductionListFactory' );
				$udlf->getByUserIdAndCompanyDeductionId( $raw_row['user_id'], $raw_row['company_deduction_id'] );
				if ( $udlf->getRecordCount() == 1 ) {
					$raw_row['id'] = $udlf->getCurrent()->getId();
					if ( $raw_row['id'] == false ) {
						unset( $raw_row['id'] );
					}
				}
			} else {
				Debug::Text( '  NOTICE: UserID/CompanyDeductionID not specified... Unable to find existing record to update.', __FILE__, __LINE__, __METHOD__, 10 );
				unset( $raw_row['id'] );
			}
		} else {
			Debug::Text( 'NOT updating existing records... ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( isset( $raw_row['company_deduction_id'] ) ) {
			$cd_obj = $this->getCompanyDeductionObject( TTUUID::castUUID( $raw_row['company_deduction_id'] ) );
			if ( is_object( $cd_obj ) ) {
				//Strip column prefix so it can be parsed.
				foreach ( $raw_row as $key => $value ) {
					if ( strpos( $key, $cd_obj->getUniqueKeyPrefix() ) !== false ) {
						if ( strpos( $key, 'user_value' ) !== false || strpos( $key, 'company_value' ) !== false ) {
							preg_match( '/((user_value|company_value)[0-9]{1,2})/i', $key, $matches );
							if ( isset( $matches[0] ) ) {
								$parsed_value = $this->parse_user_values( $cd_obj, $matches[0], $value );
								if ( $parsed_value !== -1 ) { //-1 = Failed.
									$raw_row[$matches[0]] = $parsed_value;
								}
							}
							unset( $raw_row[$key] );
						} else {
							Debug::Text( 'Column is not a CompanyDeduction dynamic field: ' . $key . ' Tax/Deduction: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( 'Column is not part of this CompanyDeduction record: ' . $key . ' Tax/Deduction: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}
		}

		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setUserDeduction( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	function getUserDeductionByUserIdAndCompanyDeductionId( $user_id, $company_deduction_id ) {
		$udlf = TTnew('UserDeductionListFactory');
		$udlf->getByUserIdAndCompanyDeductionId( $user_id, $company_deduction_id );
		if ( $udlf->getRecordCount() == 1 ) {
			return $udlf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @return array
	 */
	function getCompanyDeductionOptions( $user_id ) {
		//Get legal entity of user.
		$ulf = TTnew('UserListFactory');
		$ulf->getByIdAndCompanyId( $user_id, $this->company_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();

			//Get Tax/Deduction records assocaited with that legal entity, and any not associated with any legal entity.
			$cdlf = TTNew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndLegalEntityId( $this->company_id, [ $u_obj->getLegalEntity(), TTUUID::getZeroID(), TTUUID::getNotExistID() ] );

			$retval = (array)$cdlf->getArrayByListFactory( $cdlf, false, false );

			return $retval;
		}

		return [];
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_company_deduction_id( $input, $default_value = null, $parse_hint = null, $map_data = null, $raw_row = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID();
		}

		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == false ) {
			return -1; //Make sure this fails.
		}

		$company_deduction_options = $this->getCompanyDeductionOptions( $raw_row['user_id'] ); //Can't really be cached, as its specific to each legal entity, which is linked to the user, and can be different for every row.

		$retval = $this->findClosestMatch( $input, $company_deduction_options, 80 ); //Because we could be matching on "CA - State Income Tax" or "CT - State Income Tax", the match percent must be high.
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function parse_user_values( $cd_obj, $column, $input ) {
		$retval = $input;

		$calculation_type_column_meta_data = $cd_obj->getOptions( 'calculation_type_column_meta_data', [ 'calculation_id' => $cd_obj->getCalculation(), 'country' => $cd_obj->getCountry(), 'province' => $cd_obj->getProvince() ] );
		if ( isset( $calculation_type_column_meta_data[$column] ) ) {
			Debug::Text( 'Parsing Column: '. $column .' Raw Value: '. $input .' Tax/Deduction: '. $cd_obj->getName() .'('. $cd_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);

			$val = new Validator();
			switch ( $calculation_type_column_meta_data[$column]['type_id'] ) {
				case 2100:
					if ( isset( $calculation_type_column_meta_data[$column]['multi_select_items'][$input] ) ) { //Exact match on the option key.
						$retval = $input;
					} else {
						$retval = $this->findClosestMatch( $input, $calculation_type_column_meta_data[$column]['multi_select_items'] );
						if ( $retval === false ) {
							$retval = -1; //Make sure this fails.

							if ( $cd_obj->getCalculation() == 100 && $cd_obj->getCountry() == 'US' ) { //US - Federal Income Tax
								switch ( $column ) {
									case 'user_value1': //Filing Status
										if ( strtolower( $input ) == 's'
												|| strtolower( $input ) == 'single' ) {
											$retval = 10;
										} else if ( strtolower( $input ) == 'm'
												|| strtolower( $input ) == 'married' ) {
											$retval = 20;
										} else if ( strtolower( $input ) == 'h'
												|| strtolower( $input ) == 'h' || strtolower( $input ) == 'hoh' ) {
											$retval = 40;
										}
										break;
									case 'user_value3': //Multiple Jobs: Yes/No
										if ( strtolower( $input ) == 'y'
												|| strtolower( $input ) == 'yes' ) {
											$retval = 1;
										} else {
											$retval = 0;
										}
										break;
									case 'user_value9': //Form W-4 Version
										if ( strtolower( $input ) == 'y'
												|| strtolower( $input ) == 'yes' ) { //2020 or Later: Yes/No
											$retval = 2020;
										}
										break;
								}
							}
						}
					}
					break;
				case 400: //Integer
					$retval = $val->stripNonNumeric( $input );
					break;
				case 410: //Decimal
					$retval = $val->stripNonFloat( $input );
					break;
				case 420: //Currency
					$retval = $val->stripNonFloat( $input );
					break;
				default:
					$retval = $input;
					break;
			}
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_start_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_end_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

}

?>
