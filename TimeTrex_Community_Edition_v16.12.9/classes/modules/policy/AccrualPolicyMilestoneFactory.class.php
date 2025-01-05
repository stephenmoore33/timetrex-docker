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
 * @package Modules\Policy
 */
class AccrualPolicyMilestoneFactory extends Factory {
	protected $table = 'accrual_policy_milestone';
	protected $pk_sequence_name = 'accrual_policy_milestone_id_seq'; //PK Sequence name

	protected $accrual_policy_obj = null;

	protected $length_of_service_multiplier = [
			0  => 0,
			10 => 1,
			20 => 7,
			30 => 30.4167,
			40 => 365.25,
			50 => 0.04166666666666666667, // 1/24th of a day.
	];

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'accrual_policy_id' )->setFunctionMap( 'AccrualPolicy' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'length_of_service' )->setFunctionMap( 'LengthOfService' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'length_of_service_unit_id' )->setFunctionMap( 'LengthOfServiceUnit' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'length_of_service_days' )->setFunctionMap( 'LengthOfServiceDays' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'accrual_rate' )->setFunctionMap( 'AccrualRate' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'annual_maximum_time' )->setFunctionMap( 'AnnualMaximumTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'maximum_time' )->setFunctionMap( 'MaximumTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'rollover_time' )->setFunctionMap( 'RolloverTime' )->setType( 'integer' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'accrual_policy_id' )->setType( 'uuid_list' )->setColumn( 'a.accrual_policy_id' )->setMulti( true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIAccrualPolicyMilestone' )->setMethod( 'getAccrualPolicyMilestone' )
									->setSummary( 'Get accrual policy milestone records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIAccrualPolicyMilestone' )->setMethod( 'setAccrualPolicyMilestone' )
									->setSummary( 'Add or edit accrual policy milestone records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIAccrualPolicyMilestone' )->setMethod( 'deleteAccrualPolicyMilestone' )
									->setSummary( 'Delete accrual policy milestone records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIAccrualPolicyMilestone' )->setMethod( 'getAccrualPolicyMilestone' ) ),
											   ) ),
							TTSAPI::new( 'APIAccrualPolicyMilestone' )->setMethod( 'getAccrualPolicyMilestoneDefaultData' )
									->setSummary( 'Get default accrual policy milestone data used for creating new milestones. Use this before calling setAccrualPolicyMilestone to get the correct default data.' ),
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
			case 'length_of_service_unit':
				$retval = [
						10 => TTi18n::gettext( 'Day(s)' ),
						20 => TTi18n::gettext( 'Week(s)' ),
						30 => TTi18n::gettext( 'Month(s)' ),
						40 => TTi18n::gettext( 'Year(s)' ),
						50 => TTi18n::gettext( 'Hour(s)' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-length_of_service'      => TTi18n::gettext( 'Length Of Service' ),
						'-1020-length_of_service_unit' => TTi18n::gettext( 'Units' ),
						'-1030-accrual_rate'           => TTi18n::gettext( 'Accrual Rate' ),
						'-1050-maximum_time'           => TTi18n::gettext( 'Maximum Time' ),
						'-1050-rollover_time'          => TTi18n::gettext( 'Rollover Time' ),

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
						'length_of_service',
						'length_of_service_unit',
						'accrual_rate',
						'maximum_time',
						'rollover_time',
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
				'id'                        => 'ID',
				'accrual_policy_id'         => 'AccrualPolicy',
				'length_of_service_days'    => 'LengthOfServiceDays',
				'length_of_service'         => 'LengthOfService',
				'length_of_service_unit_id' => 'LengthOfServiceUnit',
				//'length_of_service_unit' => FALSE,
				'accrual_rate'              => 'AccrualRate',
				'annual_maximum_time'       => 'AnnualMaximumTime',
				'maximum_time'              => 'MaximumTime',
				'rollover_time'             => 'RolloverTime',
				'deleted'                   => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|null
	 */
	function getAccrualPolicyObject() {
		if ( is_object( $this->accrual_policy_obj ) ) {
			return $this->accrual_policy_obj;
		} else {
			$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
			$aplf->getById( $this->getAccrualPolicyID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->accrual_policy_obj = $aplf->getCurrent();

				return $this->accrual_policy_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicy() {
		return $this->getGenericDataValue( 'accrual_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAccrualPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_id', $value );
	}

	/**
	 * If we just base LengthOfService on days, leap years and such can cause off-by-one errors.
	 * So we need to determine the exact dates when the milestones rollover and base it on that instead.
	 * @param int $milestone_rollover_date EPOCH
	 * @return bool|false|int
	 */
	function getLengthOfServiceDate( $milestone_rollover_date ) {
		switch ( $this->getLengthOfServiceUnit() ) {
			case 10: //Days
				$unit_str = 'day';
				break;
			case 20: //Weeks
				$unit_str = 'week';
				break;
			case 30: //Months
				$unit_str = 'month';
				break;
			case 40: //Years
				$unit_str = 'year';
				break;
		}

		if ( isset( $unit_str ) ) {
			//There appears to be a bug in PHP strtotime() where '+10.00 years' does not work, but '+10 years' or '+10.01 years' does.
			//Therefore to work around this issue always cast the length of service to a float.
			//$retval = TTDate::getBeginDayEpoch( strtotime( '+' . (float)$this->getLengthOfService() . ' ' . $unit_str, $milestone_rollover_date ) );
			$retval = TTDate::getBeginDayEpoch( TTDate::incrementDate( $milestone_rollover_date, (float)$this->getLengthOfService(), $unit_str ) );
			Debug::text( 'New MileStone Rollover Date: ' . TTDate::getDate( 'DATE+TIME', $retval ) .' Base on Length of Service: '. (float)$this->getLengthOfService() .' Unit: '. $unit_str .' Original Date: '. TTDate::getDate( 'DATE+TIME', $milestone_rollover_date ), __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getLengthOfServiceDays() {
		return $this->getGenericDataValue( 'length_of_service_days' );
	}

	/**
	 * @param $value float
	 * @return bool
	 */
	function setLengthOfServiceDays( $value ) {
		$value = (float)trim( $value ); //Must accept float because setLengthOfService() uses float, ie: 6.99 months.
		Debug::text( 'aLength of Service Days: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value >= 0 ) {
			$this->setGenericDataValue( 'length_of_service_days', round( $this->Validator->stripNon32bitInteger( TTMath::mul( $value, $this->length_of_service_multiplier[$this->getLengthOfServiceUnit()], 4 ) ) ) );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|float
	 */
	function getLengthOfService() {
		$value = $this->getGenericDataValue( 'length_of_service' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( (float)$value, 0 );
		}

		return false;
	}

	/**
	 * @param $value float
	 * @return bool
	 */
	function setLengthOfService( $value ) {
		$value = (float)trim( $value );

		Debug::text( 'bLength of Service: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value >= 0 ) {
			$this->setGenericDataValue( 'length_of_service', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getLengthOfServiceUnit() {
		return $this->getGenericDataValue( 'length_of_service_unit_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLengthOfServiceUnit( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'length_of_service_unit_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualRate() {
		return $this->getGenericDataValue( 'accrual_rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualRate( $value ) {
		$value = (float)trim( $value );

		return $this->setGenericDataValue( 'accrual_rate', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAnnualMaximumTime() {
		return $this->getGenericDataValue( 'annual_maximum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAnnualMaximumTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'annual_maximum_time', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumTime() {
		return $this->getGenericDataValue( 'maximum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_time', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getRolloverTime() {
		return $this->getGenericDataValue( 'rollover_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRolloverTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'rollover_time', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Accrual Policy
		if ( $this->getAccrualPolicy() !== false ) {
			$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows( 'accrual_policy',
												   $aplf->getByID( $this->getAccrualPolicy() ),
												   TTi18n::gettext( 'Accrual Policy is invalid' )
			);
		}
		// Length of service
		if ( $this->getLengthOfServiceDays() !== false && $this->getLengthOfServiceDays() >= 0 ) {
			$this->Validator->isFloat( 'length_of_service' . $this->getLabelID(),
									   $this->getLengthOfServiceDays(),
									   TTi18n::gettext( 'Length of service is invalid' )
			);
		}
		// Length of service
		if ( $this->getLengthOfService() !== false && $this->getLengthOfService() >= 0 ) {
			$this->Validator->isFloat( 'length_of_service' . $this->getLabelID(),
									   $this->getLengthOfService(),
									   TTi18n::gettext( 'Length of service is invalid' )
			);
			$this->Validator->isGreaterThan( 'length_of_service',
											 $this->getLengthOfService(),
											 TTi18n::gettext( 'Length of service cannot be less than 0' ),
											 0
			);
		}
		// Length of service unit
		$this->Validator->inArrayKey( 'length_of_service_unit_id' . $this->getLabelID(),
									  $this->getLengthOfServiceUnit(),
									  TTi18n::gettext( 'Incorrect Length of service unit' ),
									  $this->getOptions( 'length_of_service_unit' )
		);
		// Accrual Rate
		if ( $this->getAccrualRate() !== false ) {
			$this->Validator->isNumeric( 'accrual_rate' . $this->getLabelID(),
										 $this->getAccrualRate(),
										 TTi18n::gettext( 'Incorrect Accrual Rate' )
			);
		}
		// Accrual Annual Maximum
		if ( $this->getAnnualMaximumTime() != '' ) {
			$this->Validator->isNumeric( 'annual_maximum_time' . $this->getLabelID(),
										 $this->getAnnualMaximumTime(),
										 TTi18n::gettext( 'Incorrect Accrual Annual Maximum' )
			);
		}
		// Maximum Balance
		if ( $this->getMaximumTime() != '' ) {
			$this->Validator->isNumeric( 'maximum_time' . $this->getLabelID(),
										 $this->getMaximumTime(),
										 TTi18n::gettext( 'Incorrect Maximum Balance' )
			);
		}
		//  Rollover Time
		if ( $this->getRolloverTime() != '' ) {
			$this->Validator->isNumeric( 'rollover_time' . $this->getLabelID(),
										 $this->getRolloverTime(),
										 TTi18n::gettext( 'Incorrect Rollover Time' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->Validator->getValidateOnly() == false && $this->getAccrualPolicy() == false ) {
			$this->Validator->isTRUE( 'accrual_policy_id' . $this->getLabelID(),
									  false,
									  TTi18n::gettext( 'Accrual Policy is invalid' ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Set Length of service in days.
		$this->setLengthOfServiceDays( $this->getLengthOfService() );

		return true;
	}

	/**
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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						/*
						//This is not displayed anywhere that needs it in text rather then from the options.
						case 'length_of_service_unit':
							//$function = 'getLengthOfServiceUnit';
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->getLengthOfServiceUnit(), $this->getOptions( $variable ) );
							}
							break;
						*/
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
		return TTLog::addEntry( $this->getAccrualPolicy(), $log_action, TTi18n::getText( 'Accrual Policy Milestone' ) . ' (ID: ' . $this->getID() . ')', null, $this->getTable(), $this );
	}
}

?>
