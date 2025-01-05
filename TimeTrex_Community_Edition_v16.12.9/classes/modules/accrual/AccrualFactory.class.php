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
 * @package Modules\Accrual
 */
class AccrualFactory extends Factory {
	protected $table = 'accrual';
	protected $pk_sequence_name = 'accrual_id_seq'; //PK Sequence name

	var $user_obj = null;
	private $strict_validiation;

	protected $system_type_ids = [ 10, 20, 75, 76 ]; //These all special types reserved for system use only.

	private $calc_balance;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'accrual_policy_account_id' )->setFunctionMap( 'AccrualPolicyAccount' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'user_date_total_id' )->setFunctionMap( 'UserDateTotalID' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'time_stamp' )->setFunctionMap( 'TimeStamp' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'amount' )->setFunctionMap( 'Amount' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'accrual_policy_id' )->setFunctionMap( 'AccrualPolicy' )->setType( 'uuid' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_accrual' )->setLabel( TTi18n::getText( 'Accrual' ) )->setFields(
									new TTSFields(
											TTSField::new( 'full_name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Employee' ) )->setWidth( '100%' ),
											TTSField::new( 'accrual_policy_account' )->setType( 'text' )->setLabel( TTi18n::getText( 'Accrual Account' ) )->setWidth( '100%' ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIAccrual' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Amount' ) )->setWidth( '100%' ),
											TTSField::new( 'time_stamp' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) )->setWidth( '100%' ),
											TTSField::new( 'note' )->setType( 'text' )->setLabel( TTi18n::getText( 'Note' ) )->setWidth( '100%' )
									)
							)
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'type' )->setType( 'text' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'accrual_policy_account_id' )->setType( 'uuid_list' )->setColumn( 'a.accrual_policy_account_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' )->setMulti( true ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' )->setMulti( true ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid_list' )->setColumn( 'udtf.pay_period_id' )->setMulti( true ),
							TTSSearchField::new( 'start_date' )->setType( 'date' )->setColumn( 'a.time_stamp' )->setMulti( true ),
							TTSSearchField::new( 'end_date' )->setType( 'date' )->setColumn( 'a.time_stamp' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIAccrual' )->setMethod( 'getAccrual' )
									->setSummary( 'Get accrual records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIAccrual' )->setMethod( 'setAccrual' )
									->setSummary( 'Add or edit accrual records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIAccrual' )->setMethod( 'deleteAccrual' )
									->setSummary( 'Delete accrual records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIAccrual' )->setMethod( 'getAccrual' ) ),
											   ) ),
							TTSAPI::new( 'APIAccrual' )->setMethod( 'getAccrualDefaultData' )
									->setSummary( 'Get default accrual data used for creating new accruals. Use this before calling setAccrual to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Banked' ), //System: Can never be deleted/edited/added
						20 => TTi18n::gettext( 'Used' ), //System: Can never be deleted/edited/added
						30 => TTi18n::gettext( 'Awarded' ),
						40 => TTi18n::gettext( 'Un-Awarded' ),
						50 => TTi18n::gettext( 'Gift' ),
						55 => TTi18n::gettext( 'Paid Out' ),
						60 => TTi18n::gettext( 'Rollover Adjustment' ),
						65 => TTi18n::gettext( 'Excess Rollover Adjustment' ),
						70 => TTi18n::gettext( 'Initial Balance' ),
						75 => TTi18n::gettext( 'Calendar-Based Accrual Policy' ), //System: Can never be added or edited.
						76 => TTi18n::gettext( 'Hour-Based Accrual Policy' ), //System: Can never be added or edited.
						80 => TTi18n::gettext( 'Other' ),
				];
				break;
			case 'system_type':
				$retval = array_intersect_key( $this->getOptions( 'type' ), array_flip( $this->system_type_ids ) );
				break;
			case 'add_type':
			case 'edit_type':
			case 'user_type':
				$retval = array_diff_key( $this->getOptions( 'type' ), array_flip( $this->system_type_ids ) );
				break;
			case 'delete_type': //Types that can be deleted
				$retval = $this->getOptions( 'type' );
				unset( $retval[10], $retval[20] ); //Remove just Banked/Used as those can't be deleted.
				break;
			case 'accrual_policy_type':
				$apf = TTNew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $apf */
				$retval = $apf->getOptions( 'type' );
				break;
			case 'columns':
				$retval = [

						'-1010-first_name' => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'  => TTi18n::gettext( 'Last Name' ),

						'-1030-accrual_policy_account' => TTi18n::gettext( 'Accrual Account' ),
						'-1040-type'                   => TTi18n::gettext( 'Type' ),
						//'-1050-time_stamp' => TTi18n::gettext('Date'),
						'-1050-date_stamp'             => TTi18n::gettext( 'Date' ), //Date stamp is combination of time_stamp and user_date.date_stamp columns.
						'-1060-amount'                 => TTi18n::gettext( 'Amount' ),
						'-1070-note'                   => TTi18n::gettext( 'Note' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( [ 'accrual_policy_account', 'type', 'date_stamp', 'amount' ], Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'accrual_policy_account',
						'type',
						'amount',
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
				'id'                        => 'ID',
				'user_id'                   => 'User',
				'first_name'                => false,
				'last_name'                 => false,
				'default_branch'            => false,
				'default_department'        => false,
				'user_group'                => false,
				'title'                     => false,
				'accrual_policy_account_id' => 'AccrualPolicyAccount',
				'accrual_policy_account'    => false,
				'accrual_policy_id'         => 'AccrualPolicy',
				'accrual_policy'            => false,
				'accrual_policy_type'       => false,
				'type_id'                   => 'Type',
				'type'                      => false,
				'user_date_total_id'        => 'UserDateTotalID',
				'date_stamp'                => false,
				'time_stamp'                => 'TimeStamp',
				'amount'                    => 'Amount',
				'note'                      => 'Note',
				'deleted'                   => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicyAccount() {
		return $this->getGenericDataValue( 'accrual_policy_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicyAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_account_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicy() {
		return $this->getGenericDataValue( 'accrual_policy_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_id', $value );
	}

	/**
	 * @return int
	 */
	function getType() {
		return (int)$this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool
	 */
	function isSystemType() {
		if ( in_array( $this->getType(), $this->system_type_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserDateTotalID() {
		return $this->getGenericDataValue( 'user_date_total_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserDateTotalID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_date_total_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeStamp( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'time_stamp', $value );
	}

	/**
	 * @param $amount
	 * @return bool
	 */
	function isValidAmount( $amount ) {
		Debug::text( 'Type: ' . $this->getType() . ' Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );
		//Based on type, set Amount() pos/neg
		switch ( $this->getType() ) {
			case 10: // Banked
			case 30: // Awarded
			case 50: // Gifted
				if ( $amount >= 0 ) {
					return true;
				}
				break;
			case 20: // Used
			case 55: // Paid Out
			case 40: // Un Awarded
				if ( $amount <= 0 ) {
					return true;
				}
				break;
			default:
				return true;
				break;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getAmount() {
		return $this->getGenericDataValue( 'amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim( $value );
		if ( empty( $value ) ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim( (string)$value );

		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableCalcBalance() {
		if ( isset( $this->calc_balance ) ) {
			return $this->calc_balance;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcBalance( $bool ) {
		$this->calc_balance = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableStrictValidation() {
		if ( isset( $this->strict_validiation ) ) {
			return $this->strict_validiation;
		}

		return true; //Default to true. Currently only disabled in CalculatePolicy
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableStrictValidation( $bool ) {
		$this->setIsValid( false ); //Force revalidation when data is changed.
		$this->strict_validiation = $bool;

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {

		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Accrual Policy Account
		if ( $this->Validator->getValidateOnly() == false ) {
			if ( $this->getAccrualPolicyAccount() == false || $this->getAccrualPolicyAccount() == TTUUID::getZeroID() ) {
				$this->Validator->isTrue( 'accrual_policy_account_id',
										  false,
										  TTi18n::gettext( 'Please specify an accrual account' ) );
			}
		}

		if ( $this->getEnableStrictValidation() == true ) {
			//User
			if ( $this->Validator->getValidateOnly() == false ) {
				if ( $this->getUser() == false || $this->getUser() == TTUUID::getZeroID() ) {
					$this->Validator->isTrue( 'user_id',
											  false,
											  TTi18n::gettext( 'Please specify an employee' ) );
				}
			}
			if ( $this->getUser() != '' && $this->Validator->isError( 'user_id' ) == false ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user_id',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
			// Accrual Policy
			if ( $this->getAccrualPolicy() != '' && $this->getAccrualPolicy() != TTUUID::getZeroID() ) {
				$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
				$this->Validator->isResultSetWithRows( 'accrual_policy_id',
													   $aplf->getByID( $this->getAccrualPolicy() ),
													   TTi18n::gettext( 'Accrual Policy is invalid' )
				);
			}
			// UserDateTotal
			if ( $this->getUserDateTotalID() != '' && $this->getUserDateTotalID() != TTUUID::getZeroID() ) {
				$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
				$this->Validator->isResultSetWithRows( 'user_date_total',
													   $udtlf->getByID( $this->getUserDateTotalID() ),
													   TTi18n::gettext( 'User Date Total ID is invalid' )
				);
			}
			if ( $this->getAccrualPolicyAccount() != '' && $this->Validator->isError( 'accrual_policy_account_id' ) == false ) {
				$apalf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apalf */
				$this->Validator->isResultSetWithRows( 'accrual_policy_account_id',
													   $apalf->getByID( $this->getAccrualPolicyAccount() ),
													   TTi18n::gettext( 'Accrual Account is invalid' )
				);
			}
		}

		// Type
		if ( $this->Validator->getValidateOnly() == false ) { //Don't do the follow validation checks during Mass Edit.
			if ( $this->getType() == false || $this->getType() == 0 ) {
				$this->Validator->isTrue( 'type_id',
										  false,
										  TTi18n::gettext( 'Please specify accrual type' ) );
			}
		}
		if ( $this->getType() != '' && $this->Validator->isError( 'type_id' ) == false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}

		// Time stamp
		if ( $this->getTimeStamp() != '' ) {
			$this->Validator->isDate( 'times_tamp',
									  $this->getTimeStamp(),
									  TTi18n::gettext( 'Incorrect time stamp' )
			);
		}


		// Amount
		if ( $this->getAmount() !== false ) {
			$this->Validator->isNumeric( 'amount',
										 $this->getAmount(),
										 TTi18n::gettext( 'Incorrect Amount' )
			);
			if ( $this->Validator->isError( 'amount' ) == false ) {
				$this->Validator->isTrue( 'amount',
										  $this->isValidAmount( $this->getAmount() ),
										  TTi18n::gettext( 'Amounts of type "%1" must be a %2 value instead', [ Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ), ( ( $this->getAmount() < 0 && $this->isValidAmount( $this->getAmount() ) == false ) ? TTi18n::getText( 'positive' ) : TTi18n::getText( 'negative' ) ) ] )
				);
			}

			if ( $this->Validator->isError( 'amount' ) == false ) {
				$this->Validator->isGreaterThan( 'amount',
												 $this->getAmount(),
												 TTi18n::gettext( 'Amount is too low' ),
												 -35996400 //9999 hrs
				);

				$this->Validator->isLessThan( 'amount',
											  $this->getAmount(),
											  TTi18n::gettext( 'Amount is too high' ),
											  35996400 //9999 hrs
				);
			}
		}

		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too long' ),
										0,
										1024
			);

			$this->Validator->isHTML( 'note',
									  $this->getNote(),
									  TTi18n::gettext( 'Note contains invalid special characters' ),
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == true ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Unable to delete system accrual records, modify the employees schedule/timesheet instead' ),
										  $this->getOptions( 'delete_type' )
			);
		} else if ( $this->isNew( true ) == false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Unable to modify system accrual records' ),
										  $this->getOptions( 'user_type' )
			);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getTimeStamp() == false ) {
			$this->setTimeStamp( TTDate::getTime() );
		}

		//Delete duplicates before saving.
		//Or orphaned entries on Sum'ing?
		//Would have to do it on view as well though.
		if ( TTUUID::isUUID( $this->getUserDateTotalID() ) && $this->getUserDateTotalID() != TTUUID::getZeroID() && $this->getUserDateTotalID() != TTUUID::getNotExistID() ) {
			$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
			$alf->getByUserIdAndAccrualPolicyAccountAndAccrualPolicyAndUserDateTotalID( $this->getUser(), $this->getAccrualPolicyAccount(), $this->getAccrualPolicy(), $this->getUserDateTotalID() );
			Debug::text( 'Found Duplicate Records: ' . (int)$alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $alf->getRecordCount() > 0 ) {
				foreach ( $alf as $a_obj ) {
					if ( $a_obj->getId() != $this->getId() ) { //Make sure we don't delete the record we are currently editing.
						$a_obj->Delete( true );
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
		//Calculate balance
		if ( $this->getEnableCalcBalance() == true ) {
			Debug::text( 'Calculating Balance is enabled! ', __FILE__, __LINE__, __METHOD__, 10 );

			//If the user and/or the accrual policy account was changed, recalculate the old and new values.
			$data_diff = $this->getDataDifferences();
			if ( isset( $data_diff['user_id'] ) || isset( $data_diff['accrual_policy_account_id'] ) ) {
				AccrualBalanceFactory::calcBalance( ( ( isset( $data_diff['user_id'] ) ) ? $data_diff['user_id'] : $this->getUser() ), ( ( isset( $data_diff['accrual_policy_account_id'] ) ) ? $data_diff['accrual_policy_account_id'] : $this->getAccrualPolicyAccount() ) );
			}

			AccrualBalanceFactory::calcBalance( $this->getUser(), $this->getAccrualPolicyAccount() );
		}

		return true;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @return bool
	 */
	static function deleteOrphans( $user_id, $date_stamp ) {
		Debug::text( 'Attempting to delete Orphaned Records for User ID: ' . $user_id . ' Date: ' . TTDate::getDate( 'DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10 );
		//Remove orphaned entries
		$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
		$alf->getOrphansByUserIdAndDate( $user_id, $date_stamp );
		Debug::text( 'Found Orphaned Records: ' . $alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $alf->getRecordCount() > 0 ) {
			$accrual_policy_ids = [];
			foreach ( $alf as $a_obj ) {
				Debug::text( 'Orphan Record ID: ' . $a_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				$accrual_policy_ids[] = $a_obj->getAccrualPolicyAccount();
				$a_obj->Delete( true );
			}

			//ReCalc balances
			if ( empty( $accrual_policy_ids ) === false ) {
				foreach ( $accrual_policy_ids as $accrual_policy_id ) {
					AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );
				}
			}
		}

		return true;
	}

	/**
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @return bool|int
	 */
	function getCurrentAccrualBalance( $user_id = null, $accrual_policy_account_id = null ) {
		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getAccrualPolicyAccount();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text( '  Current Accrual Balance: ' . $accrual_balance, __FILE__, __LINE__, __METHOD__, 10 );

		return $accrual_balance;
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
						case 'user_date_total_id': //Skip this, as it should never be set from the API.
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'accrual_policy_account':
						case 'accrual_policy':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'accrual_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $this->getOptions( $variable ) );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'date_stamp': //This is a combination of the time_stamp and user_date.date_stamp columns.
							$data[$variable] = TTDate::getAPIDate( 'DATE', strtotime( $this->getColumn( $variable ) ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		//Debug::Arr($data, 'Data Object: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object( $u_obj ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Accrual' ) . ' - ' . TTi18n::getText( 'Employee' ) . ': ' . $u_obj->getFullName( false, true ) . ' ' . TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ) . ' ' . TTi18n::getText( 'Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getTimeStamp() ) . ' ' . TTi18n::getText( 'Total Time' ) . ': ' . TTDate::getTimeUnit( $this->getAmount() ), null, $this->getTable(), $this );
		}

		return false;
	}

}

?>
