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
class CurrencyRateFactory extends Factory {
	protected $table = 'currency_rate';
	protected $pk_sequence_name = 'currency_rate_id_seq'; //PK Sequence name

	protected $currency_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'date_stamp' )->setFunctionMap( 'DateStamp' )->setType( 'date' )->setIsNull( false ),
							TTSCol::new( 'conversion_rate' )->setFunctionMap( 'ConversionRate' )->setType( 'numeric' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_currency_rate' )->setLabel( TTi18n::getText( 'Currency Rate' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'currency_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APICurrency' )->setMethod( 'getCurrency' ) ),
											TTSField::new( 'date_stamp' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) )
									)
							),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'currency_id' )->setType( 'uuid' )->setColumn( 'a.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'iso_code' )->setType( 'text_list' )->setColumn( 'cf.iso_code' ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'cf.name' ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'cf.status_id' ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'cf.status_id' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APICurrencyRate' )->setMethod( 'getCurrencyRate' )
									->setSummary( 'Get currency rate records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APICurrencyRate' )->setMethod( 'setCurrencyRate' )
									->setSummary( 'Add or edit currency rate records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APICurrencyRate' )->setMethod( 'deleteCurrencyRate' )
									->setSummary( 'Delete currency rate records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APICurrencyRate' )->setMethod( 'getCurrencyRate' ) ),
											   ) ),
							TTSAPI::new( 'APICurrencyRate' )->setMethod( 'getCurrencyRateDefaultData' )
									->setSummary( 'Get default currency rate data used for creating new currency rates. Use this before calling setCurrencyRate to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
					//'-1010-iso_code' => TTi18n::gettext('ISO Code'),
					'-1020-date_stamp'      => TTi18n::gettext( 'Date' ),
					'-1030-conversion_rate' => TTi18n::gettext( 'Conversion Rate' ),

					'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
					'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
					'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
					'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( [ 'date_stamp', 'conversion_rate' ], Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'date_stamp',
						'conversion_rate',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'date_stamp',
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
				'id'              => 'ID',
				'currency_id'     => 'Currency',
				//'status_id' => FALSE,
				//'status' => FALSE,
				//'name' => FALSE,
				//'symbol' => FALSE,
				//'iso_code' => FALSE,
				'date_stamp'      => 'DateStamp',
				'conversion_rate' => 'ConversionRate',
				'deleted'         => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrency( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				if ( !is_numeric( $value ) ) {                                         //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::getMiddleDayEpoch( TTDate::strtotime( $value ) ); //Make sure we use middle day epoch when pulling the value from the DB the first time, to match setDateStamp() below. Otherwise setting the datestamp then getting it again before save won't match the same value after its saved to the DB.
					$this->setGenericDataValue( 'date_stamp', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = (int)$value;

		if ( $value > 0 ) {
			//Use middle day epoch to help avoid confusion with different timezones/DST. -- getDateStamp() needs to use middle day epoch too then.
			//See comments about timezones in CalculatePolicy->_calculate().
			$retval = $this->setGenericDataValue( 'date_stamp', TTDate::getMiddleDayEpoch( $value ) );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isUnique() {
		$ph = [
				'currency_id' => TTUUID::castUUID( $this->getCurrency() ),
				'date_stamp'  => $this->db->BindDate( $this->getDateStamp() ),
		];

		$query = 'select id from ' . $this->getTable() . ' where currency_id = ? AND date_stamp = ?';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Currency Rate: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getReverseConversionRate() {
		$rate = $this->getConversionRate();
		if ( $rate != 0 ) { //Prevent division by 0.
			return TTMath::div( 1, $rate );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getConversionRate() {
		return $this->getGenericDataValue( 'conversion_rate' );//Don't cast to (float) as it may strip some precision.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConversionRate( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'conversion_rate', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Currency
		if ( $this->Validator->getValidateOnly() == false ) { //Don't do the follow validation checks during Mass Edit.
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}

		// Date
		if ( $this->Validator->getValidateOnly() == false && $this->getDateStamp() != false ) {
			$this->Validator->isDate( 'date_stamp',
									  $this->getDateStamp(),
									  TTi18n::gettext( 'Incorrect date' )
			);
		}

		// Conversion rate
		if ( $this->Validator->getValidateOnly() == false ) { //Don't do the follow validation checks during Mass Edit.
			$this->Validator->isTrue( 'conversion_rate',
									  $this->getConversionRate(),
									  TTi18n::gettext( 'Conversion rate not specified' )
			);
		}

		if ( $this->getConversionRate() !== false ) {
			if ( $this->Validator->isError( 'conversion_rate' ) == false ) {
				$this->Validator->isFloat( 'conversion_rate',
										   $this->getConversionRate(),
										   TTi18n::gettext( 'Incorrect Conversion Rate' )
				);
			}
			if ( $this->Validator->isError( 'conversion_rate' ) == false ) {
				$this->Validator->isLessThan( 'conversion_rate',
											  $this->getConversionRate(),
											  TTi18n::gettext( 'Conversion Rate is too high' ),
											  99999999
				);
			}
			if ( $this->Validator->isError( 'conversion_rate' ) == false ) {
				$this->Validator->isGreaterThan( 'conversion_rate',
												 $this->getConversionRate(),
												 TTi18n::gettext( 'Conversion Rate is too low' ),
												 -99999999
				);
			}
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == false ) {
			if ( $this->Validator->isError( 'date_stamp' ) == false ) {
				if ( $this->Validator->getValidateOnly() == false && $this->getDateStamp() == false ) {
					$this->Validator->isTrue( 'date_stamp',
											  false,
											  TTi18n::gettext( 'Date not specified' ) );
				} else {
					if ( $this->Validator->getValidateOnly() == false && $this->isUnique() == false ) {
						$this->Validator->isTrue( 'date_stamp',
												  false,
												  TTi18n::gettext( 'Currency rate already exists for this date' ) );
					}
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		return true;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
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
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'conversion_rate':
							$this->$function( TTi18n::parseFloat( $data[$key], 10 ) );
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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		/*
		$include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'date_stamp':
							$data[$variable] = $this->$function( true );
							break;
//						case 'conversion_rate':
//							$data[$variable] = TTi18n::formatNumber( $this->$function(), TRUE, 10, 10 ); //Don't format numbers here, as it could break scripts using the API.
//							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Currency Rate' ) . ': ' . $this->getCurrencyObject()->getISOCode() . ' ' . TTi18n::getText( 'Rate' ) . ': ' . $this->getConversionRate(), null, $this->getTable(), $this );
	}

}

?>
