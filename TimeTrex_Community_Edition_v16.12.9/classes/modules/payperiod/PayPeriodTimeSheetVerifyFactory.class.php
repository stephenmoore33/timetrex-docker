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
 * @package Modules\PayPeriod
 */
class PayPeriodTimeSheetVerifyFactory extends Factory {
	protected $table = 'pay_period_time_sheet_verify';
	protected $pk_sequence_name = 'pay_period_time_sheet_verify_id_seq'; //PK Sequence name

	var $user_obj = null;
	var $pay_period_obj = null;

	private $authorize;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_period_id' )->setFunctionMap( 'PayPeriod' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'authorized' )->setFunctionMap( 'Authorized' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'authorization_level' )->setFunctionMap( 'AuthorizationLevel' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'user_verified' )->setFunctionMap( 'UserVerified' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'user_verified_date' )->setFunctionMap( 'UserVerifiedDate' )->setType( 'integer' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid_list' )->setColumn( 'a.pay_period_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' )->setMulti( true ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' )->setMulti( true ),
							TTSSearchField::new( 'authorized' )->setType( 'numeric_list' )->setColumn( 'a.authorized' )->setMulti( true ),
							TTSSearchField::new( 'hierarchy_level_map' )->setType( 'bool' )->setColumn( 'huf.id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayPeriodTimeSheetVerify' )->setMethod( 'getPayPeriodTimeSheetVerify' )
									->setSummary( 'Get pay period time sheet verify records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayPeriodTimeSheetVerify' )->setMethod( 'setPayPeriodTimeSheetVerify' )
									->setSummary( 'Add or edit pay period time sheet verify records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayPeriodTimeSheetVerify' )->setMethod( 'deletePayPeriodTimeSheetVerify' )
									->setSummary( 'Delete pay period time sheet verify records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayPeriodTimeSheetVerify' )->setMethod( 'getPayPeriodTimeSheetVerify' ) ),
											   ) ),
							TTSAPI::new( 'APIPayPeriodTimeSheetVerify' )->setMethod( 'getPayPeriodTimeSheetVerifyDefaultData' )
									->setSummary( 'Get default pay period time sheet verify data used for creating new records. Use this before calling setPayPeriodTimeSheetVerify to get the correct default data.' ),
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'INCOMPLETE' ),
						20 => TTi18n::gettext( 'OPEN' ),
						30 => TTi18n::gettext( 'PENDING AUTHORIZATION' ),
						40 => TTi18n::gettext( 'AUTHORIZATION OPEN' ),
						45 => TTi18n::gettext( 'PENDING EMPLOYEE VERIFICATION' ), //Fully authorized, waiting on employee verification.
						50 => TTi18n::gettext( 'Verified' ),
						55 => TTi18n::gettext( 'AUTHORIZATION DECLINED' ),
						60 => TTi18n::gettext( 'DISABLED' ),
				];
				break;
			case 'filter_report_status':
				//show values custom to report with the addition of not verified.
				$retval = [
						0  => TTi18n::gettext( 'Not Verified' ),
						30 => TTi18n::gettext( 'PENDING AUTHORIZATION' ),
						45 => TTi18n::gettext( 'PENDING EMPLOYEE VERIFICATION' ), //Fully authorized, waiting on employee verification.
						50 => TTi18n::gettext( 'Verified' ),
						55 => TTi18n::gettext( 'AUTHORIZATION DECLINED' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'          => TTi18n::gettext( 'Last Name' ),
						'-1060-title'              => TTi18n::gettext( 'Title' ),
						'-1070-user_group'         => TTi18n::gettext( 'Group' ),
						'-1080-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1090-default_department' => TTi18n::gettext( 'Department' ),

						'-1110-start_date'        => TTi18n::gettext( 'Start Date' ),
						'-1112-end_date'          => TTi18n::gettext( 'End Date' ),
						'-1115-transaction_date'  => TTi18n::gettext( 'Transaction Date' ),
						'-1118-window_start_date' => TTi18n::gettext( 'Window Start Date' ),
						'-1119-window_end_date'   => TTi18n::gettext( 'Window End Date' ),

						'-1120-status' => TTi18n::gettext( 'Status' ),

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
						'first_name',
						'last_name',
						'start_date',
						'end_date',
						'status',
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
				'id'                  => 'ID',
				'pay_period_id'       => 'PayPeriod',
				'start_date'          => false, //PayPeriod
				'end_date'            => false, //PayPeriod
				'transaction_date'    => false, //PayPeriod
				'window_start_date'   => false,
				'window_end_date'     => false,
				'user_id'             => 'User',
				'first_name'          => false,
				'last_name'           => false,
				'default_branch'      => false,
				'default_department'  => false,
				'user_group'          => false,
				'title'               => false,
				'status_id'           => 'Status',
				'status'              => false,
				'user_verified'       => 'UserVerified',
				'user_verified_date'  => 'UserVerifiedDate',
				'authorized'          => 'Authorized',
				'authorization_level' => 'AuthorizationLevel',
				'deleted'             => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|UserFactory
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriod( $value = null ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * Stores the current user in memory, so we can determine if its the employee verifying, or a superior.
	 * @return mixed
	 */
	function getCurrentUser() {
		return $this->getGenericTempDataValue( 'current_user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrentUser( $value ) {
		$value = trim( $value );

		return $this->setGenericTempDataValue( 'current_user_id', $value );
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

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * Set this to TRUE when the user has actually verified their own timesheets.
	 * @return bool|null
	 */
	function getUserVerified() {
		$value = $this->getGenericDataValue( 'user_verified' );
		if ( $value !== false && $value !== null ) {
			return $this->fromBool( $value );
		}

		return null;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserVerified( $value ) {
		$this->setGenericDataValue( 'user_verified', $this->toBool( $value ) );
		$this->setUserVerifiedDate();

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserVerifiedDate() {
		return $this->getGenericDataValue( 'user_verified_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setUserVerifiedDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'user_verified_date', $value );
	}

	/**
	 * @return bool|null
	 */
	function getAuthorized() {
		$value = $this->getGenericDataValue( 'authorized' );
		if ( $value !== false && $value !== null ) {
			return $this->fromBool( $value );
		}

		return null;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorized( $value ) {
		return $this->setGenericDataValue( 'authorized', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getAuthorizationLevel() {
		return $this->getGenericDataValue( 'authorization_level' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorizationLevel( $value ) {
		$value = (int)trim( $value );

		if ( $value < 0 ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'authorization_level', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableAuthorize() {
		if ( isset( $this->authorize ) ) {
			return $this->authorize;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableAuthorize( $bool ) {
		$this->authorize = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getVerificationType() {
		if ( is_object( $this->getPayPeriodObject() ) && $this->getPayPeriodObject()->getPayPeriodScheduleObject() != false ) {
			$time_sheet_verification_type_id = $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType();
			//Debug::Text( 'TimeSheet Verification Type: ' . $time_sheet_verification_type_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $time_sheet_verification_type_id;
		}

		return false;
	}

	/**
	 * Returns the start and end date of the verification window.
	 * @return array|bool
	 */
	function getVerificationWindowDates() {
		if ( is_object( $this->getPayPeriodObject() ) ) {
			return [ 'start' => $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate(), 'end' => $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() ];
		}

		return false;
	}

	/**
	 * Determines the color of the verification box.
	 * @return bool|string
	 */
	function getVerificationBoxColor() {
		$retval = false;
		if ( is_object( $this->getPayPeriodObject() )
				&& TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()
				&& TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() ) {

			if ( $this->getStatus() == 55 ) { //Declined
				$retval = '#FF0000';
			} else if ( $this->getStatus() != 50 ) {
				$retval = '#FFFF00';
			}
		}

		return $retval;
	}

	/**
	 * @param null $status_id ID
	 * @param null $pay_period_id
	 * @return string
	 */
	function getVerificationStatusShortDisplay( $status_id = null, $pay_period_id = null ) {
		if ( $status_id == '' ) {
			$status_id = $this->getStatus();
		}

		//If no verification object exists, we assume "No" for verification status.
		if ( $status_id == 50 ) {
			$retval = TTi18n::getText( 'Yes' );
		} else if ( $status_id == 30 || $status_id == 45 ) { //30=Pending Employee, 45=Pending superior.
			//States:
			//  Pay Period Schedule TimeSheet Verify Setting: Employee Only
			//		- Employee has not verified = Pending Employee
			//	    - Employee has verified = Yes
			//
			//  Pay Period Schedule TimeSheet Verify Setting: Superior Only
			//		- Superior has not verified = Pending Superior(s)
			//	    - Superior has verified = Yes

			//  Pay Period Schedule TimeSheet Verify Setting: Employee & Superior
			//  	- Employee & Superior has not verified = Pending Both
			//  	- Employee has verified, but superior has not. = Pending Superior(s)
			//  	- Superior has verified, but employee has not. = Pending Employee
			//  	- Employee & Superior has verified.  = Yes

			$pay_period_verify_type_id = $this->getVerificationType();
			if ( $pay_period_verify_type_id == 10 ) { //10=Disabled
				$retval = TTi18n::getText( 'Disabled' );
			} else if ( $pay_period_verify_type_id == 40 ) { //40=Employee & Superior
				if ( $status_id == 30 && $this->getUserVerified() == false ) { //30=Pending Employee & Superior(s)
					$retval = TTi18n::getText( 'Pending - Employee & Superior(s)' );
				} else if ( $status_id == 30 && $this->getUserVerified() == true ) { //30=Pending Superior(s)
					$retval = TTi18n::getText( 'Pending - Superior(s)' );
				} else if ( $status_id == 45 ) { //45=Pending Employee
					$retval = TTi18n::getText( 'Pending - Employee' );
				}
			} else { //20=Employee Only,  30=Superior Only
				if ( $status_id == 30 ) { //30=Pending Superior(s)
					$retval = TTi18n::getText( 'Pending - Superior(s)' );
				} else if ( $status_id == 45 ) { //45=Pending Employee
					$retval = TTi18n::getText( 'Pending - Employee' );
				}
			}
		} else if ( $status_id == 55 ) {
			$retval = TTi18n::getText( 'Declined' );
		} else if ( $status_id == '' ) { //Status could be NULL if no timesheet verify record exists.
			$this->setPayPeriod( $pay_period_id );
			$pay_period_verify_type_id = $this->getVerificationType();
			if ( $pay_period_verify_type_id == 10 ) { //10=Disabled
				$retval = TTi18n::getText( 'Disabled' );
			} else if ( $pay_period_verify_type_id == 20 ) { //20=Employee Only
				$retval = TTi18n::getText( 'Pending - Employee' );
			} else if ( $pay_period_verify_type_id == 30 ) { //30=Superior Only
				$retval = TTi18n::getText( 'Pending - Superior(s)' );
			} else if ( $pay_period_verify_type_id == 40 ) { //40=Employee & Superior
				$retval = TTi18n::getText( 'Pending - Employee & Superior(s)' );
			} else {
				$retval = TTi18n::getText( 'No' );
			}
		} else {
			$retval = TTi18n::getText( 'No' );
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getVerificationStatusDisplay() {
		$retval = TTi18n::getText( 'Not Verified' );
		if ( $this->getUserVerifiedDate() == true && $this->getAuthorized() == true ) {
			$retval = TTi18n::getText( 'Verified @' ) . ' ' . TTDate::getDate( 'DATE+TIME', $this->getUserVerifiedDate() ); //Date verification took place for employee.
		} else {
			if ( $this->isNew() == true
					&& ( is_object( $this->getUserObject() )
							&& is_object( $this->getPayPeriodObject() )
							&& ( TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) <= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getEndDate() ) )
							&& ( $this->getUserObject()->getTerminationDate() == '' || ( $this->getUserObject()->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getStartDate() ) ) )
					)
					&& TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()
					&& TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() ) {
				$pay_period_verify_type_id = $this->getVerificationType();
				if ( $pay_period_verify_type_id == 20 || $pay_period_verify_type_id == 40 ) {
					$retval = Option::getByKey( 45, $this->getOptions( 'status' ) ); //Pending employee verification.
				} else {
					$retval = Option::getByKey( 30, $this->getOptions( 'status' ) ); //Pending authorization.
				}
				//} elseif ( $this->isNew() == TRUE ) {
				//Use Default: Not Verified
			} else {
				if ( $this->getStatus() == 50 || $this->getStatus() == 55 ) {
					$retval = Option::getByKey( $this->getStatus(), $this->getOptions( 'status' ) ) . ' @ ' . TTDate::getDate( 'DATE+TIME', $this->getUpdatedDate() );
				} else if ( $this->getStatus() !== false ) {
					$retval = Option::getByKey( $this->getStatus(), $this->getOptions( 'status' ) );
				} // else { //Verify record has not been created yet, and the window hasnt opened yet, so display the default "Not Verified".
			}
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getVerificationConfirmationMessage() {
		$pp_obj = $this->getPayPeriodObject();
		if ( is_object( $pp_obj ) ) {
			$retval = '';
			if ( is_object( $pp_obj->getPayPeriodScheduleObject() ) ) {
				$retval = $pp_obj->getPayPeriodScheduleObject()->getTimeSheetVerifyAgreement();

				//Variables available in the message.
				$replace_arr = [
					'#first_name#' => $this->getUserObject()->getFirstName(),
					'#middle_name#' => $this->getUserObject()->getMiddleName(),
					'#last_name#' => $this->getUserObject()->getLastName(),
					'#full_name#' => $this->getUserObject()->getFullName( false, true ),
					'#current_date#' => TTDate::getDate( 'DATE', time() ),
					'#pay_period_start_date#' => TTDate::getDate( 'DATE', $pp_obj->getStartDate() ),
					'#pay_period_end_date#' => TTDate::getDate( 'DATE', $pp_obj->getEndDate() ),
					'#pay_period_transaction_date#' => TTDate::getDate( 'DATE', $pp_obj->getTransactionDate() ),
				];

				$retval = strtr( $retval, $replace_arr );

				if ( $this->isClientFriendly() == true ) {
					$retval = nl2br( $retval );
				}
			}

			if ( trim( $retval ) == '' ) {
				$retval = TTi18n::getText( 'I hereby certify that this timesheet for the pay period of' ) . ' ' . TTDate::getDate( 'DATE', $pp_obj->getStartDate() ) . ' ' . TTi18n::getText( 'to' ) . ' ' . TTDate::getDate( 'DATE', $pp_obj->getEndDate() ) . ' ' . TTi18n::getText( 'is accurate and correct.' );
			}

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getPreviousPayPeriodObject() {
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getPreviousPayPeriodById( $this->getPayPeriod() );
		if ( $pplf->getRecordCount() > 0 ) {
			return $pplf->getCurrent();
		}

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @return bool
	 */
	function isPreviousPayPeriodVerified( $user_id = null ) {
		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		//Check if previous pay period was verified or not
		$is_previous_time_sheet_verified = false;

		$previous_pay_period_obj = $this->getPreviousPayPeriodObject();
		if ( is_object( $previous_pay_period_obj ) ) {
			if ( ( is_object( $this->getUserObject() )
							&& TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) >= TTDate::getMiddleDayEpoch( $previous_pay_period_obj->getEndDate() ) )
					&& ( $this->getUserObject()->getTerminationDate() == '' || ( $this->getUserObject()->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $previous_pay_period_obj->getStartDate() ) ) )
			) {
				Debug::text( 'Hired after previous pay period ended...', __FILE__, __LINE__, __METHOD__, 10 );
				$is_previous_time_sheet_verified = true;
			} else if ( $previous_pay_period_obj->getStatus() == 20 ) {
				$is_previous_time_sheet_verified = true;
			} else {
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
				$pptsvlf->getByPayPeriodIdAndUserId( $previous_pay_period_obj->getId(), $user_id );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					$pptsv_obj = $pptsvlf->getCurrent();
					if ( $pptsv_obj->getAuthorized() == true ) {
						$is_previous_time_sheet_verified = true;
					}
				}
			}
		} else {
			$is_previous_time_sheet_verified = true; //There is no previous pay period
		}
		unset( $previous_pay_period_obj, $pptsvlf, $pptsv_obj );

		return $is_previous_time_sheet_verified;
	}

	/**
	 * @param string $current_user_id UUID
	 * @param string $user_id         UUID
	 * @return bool
	 */
	function displayPreviousPayPeriodVerificationNotice( $current_user_id = null, $user_id = null ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$previous_pay_period_obj = $this->getPreviousPayPeriodObject();
		$is_previous_time_sheet_verified = $this->isPreviousPayPeriodVerified( $user_id );
		Debug::text( 'Previous Pay Period Verified: ' . (int)$is_previous_time_sheet_verified, __FILE__, __LINE__, __METHOD__, 10 );


		$pay_period_verify_type_id = $this->getVerificationType();
		$is_timesheet_superior = $this->isHierarchySuperior( $current_user_id, $user_id );
		$superior_hierarchy_level = $this->getHierarchyLevelForSuperior( $current_user_id );
		if (
				(
						( $pay_period_verify_type_id == 20 && $current_user_id == $user_id )
						||
						( $pay_period_verify_type_id == 30 && $is_timesheet_superior == true )
						||
						( $pay_period_verify_type_id == 40 && ( ( $current_user_id == $user_id ) || ( $is_timesheet_superior == true && ( $this->getAuthorizationLevel() === false || $this->getAuthorizationLevel() >= $superior_hierarchy_level ) ) ) )
				)
				&&
				( $is_previous_time_sheet_verified == false && TTDate::getTime() <= $previous_pay_period_obj->getTimeSheetVerifyWindowEndDate() )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if we need to display the verification button or not.
	 * @param string $current_user_id UUID
	 * @param string $user_id         UUID
	 * @return bool
	 */
	function displayVerifyButton( $current_user_id = null, $user_id = null ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$pay_period_verify_type_id = $this->getVerificationType();
		$is_timesheet_superior = $this->isHierarchySuperior( $current_user_id, $user_id );
		$superior_hierarchy_level = $this->getHierarchyLevelForSuperior( $current_user_id );
		Debug::text( 'Current User ID: ' . $current_user_id . ' User ID: ' . $user_id . ' Verification Type ID: ' . $pay_period_verify_type_id . ' TimeSheet Superior: ' . (int)$is_timesheet_superior . ' Status: ' . (int)$this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::text('Hire Date: '. TTDate::getDATE('DATE+TIME', $this->getUserObject()->getHireDate() ) .' Termination Date: '. TTDate::getDATE('DATE+TIME', $this->getUserObject()->getTerminationDate() ), __FILE__, __LINE__, __METHOD__, 10);

		if (
				(
						( $pay_period_verify_type_id == 20 && $current_user_id == $user_id )
						||
						( $pay_period_verify_type_id == 30 && $this->getStatus() != 50 && ( $is_timesheet_superior == true && $current_user_id != $user_id && ( $this->getAuthorizationLevel() === false || $this->getAuthorizationLevel() >= $superior_hierarchy_level ) ) )
						||
						//If two superiors at the same level exist, and one verify's the timesheet, but the employee hasn't verified it yet, make sure the "Verify" button does not appear for both superiors. So we need to base this on the authorization level rather than if the superior themselves has authorized it or not.
						( $pay_period_verify_type_id == 40 && ( $this->getStatus() == 55 || ( $current_user_id == $user_id && $this->getUserVerified() == 0 ) || ( $is_timesheet_superior == true && ( $this->getAuthorizationLevel() === false || $this->getAuthorizationLevel() >= $superior_hierarchy_level ) ) ) )
				)
				&&
				(
					//If the employee is hired on the last day of a pay period, allow them to verify that timesheet, so <= is required here.
						(
								is_object( $this->getUserObject() )
								&&
								( TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) <= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getEndDate() ) )
								&&
								( $this->getUserObject()->getTerminationDate() == '' || ( $this->getUserObject()->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getStartDate() ) ) )
						)
						&&
						TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate() && TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() && $this->getStatus() != 50
				)
		) {

			return true;
		}

		return false;
	}

	/**
	 * @param string $current_user_id UUID
	 * @param string $user_id         UUID
	 * @return bool
	 */
	function isHierarchySuperior( $current_user_id = null, $user_id = null ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
			//Get timesheet verification hierarchy, so we know who the superiors are.
			//Immediate superiors only can verify timesheets directly so we set $immediate_parents_only = TRUE
			//  However this prevents superiors from dropping down levels and authorizing, as the superior wouldn't appear in the superior list then, so set $immediate_parents_only = FALSE
			$timesheet_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $user_obj->getCompany(), $user_obj->getId(), 90, false, false );
			//Debug::Arr( $timesheet_parent_level_user_ids, 'TimeSheet Parent Level Ids', __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Text( 'TimeSheet Parent Level Ids: '. ( is_array( $timesheet_parent_level_user_ids ) ? count( $timesheet_parent_level_user_ids ) : '0' ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( in_array( $current_user_id, (array)$timesheet_parent_level_user_ids ) ) {
				Debug::text( 'Is TimeSheet Hierarchy Superior: Yes', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
			unset( $hlf, $timesheet_parent_level_user_ids );
		}

		Debug::text( 'Is TimeSheet Hierarchy Superior: No', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $current_user_id
	 * @return bool
	 */
	function getHierarchyLevelForSuperior( $current_user_id ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return false;
		}

		$retval = false;

		$hllf = TTnew( 'HierarchyLevelListFactory' ); /** @var HierarchyLevelListFactory $hllf */
		$hllf->getByUserIdAndObjectTypeID( $current_user_id, 90 );
		if ( $hllf->getRecordCount() > 0 ) {
			$retval = $hllf->getCurrent()->getLevel();
		}

		return $retval;
	}


	/**
	 * @return bool
	 */
	function calcStatus() {
		if ( $this->getDeleted() == true ) {
			Debug::Text( ' Deleting record, not calculating status!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Get pay period schedule verification type.
		$time_sheet_verification_type_id = $this->getVerificationType();
		if ( $time_sheet_verification_type_id > 10 ) { //10 = Disabled
			$is_timesheet_superior = false;
			if ( $time_sheet_verification_type_id == 30 || $time_sheet_verification_type_id == 40 ) { //Superior or Employee & Superior
				$is_timesheet_superior = $this->isHierarchySuperior( $this->getCurrentUser() );
			}

			if ( $time_sheet_verification_type_id == 20 ) { //Employee Only
				if ( $this->getCurrentUser() == $this->getUser() ) {
					Debug::Text( 'aEmployee is verifiying their own timesheet...', __FILE__, __LINE__, __METHOD__, 10 );

					//Employee is verifiying their own timesheet.
					$this->setStatus( 50 ); //Authorized
					$this->setAuthorized( true );
					$this->setUserVerified( true );
				}
			} else if ( $time_sheet_verification_type_id == 30 ) { //Superior Only
				//Make sure superiors can drop down levels and verify timesheets in this mode.
				if ( $this->getCurrentUser() != $this->getUser() && $is_timesheet_superior == true ) {
					Debug::Text( 'Superior is verifiying their subordinates timesheet...', __FILE__, __LINE__, __METHOD__, 10 );
					$this->setStatus( 30 ); //Pending Authorization
				} else if ( $this->getCurrentUser() == $this->getUser() ) {
					//Allow supervisor to verify their own timesheet. That way they can see that 100% of all employees have verified timesheets, or report filters for exporting just verified timesheets work properly.
					$this->setStatus( 50 ); //Authorized
					$this->setAuthorized( true );
					Debug::Text( 'NOTICE: Superior is verifiying their own timesheet...', __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( 'ERROR: Superior is not in the hierarchy? Is Superior: ' . (int)$is_timesheet_superior . ' Current User: ' . $this->getCurrentUser() . ' User: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else if ( $time_sheet_verification_type_id == 40 ) { //Superior & Employee
				if ( $this->isNew() == true ) {
					$this->setStatus( 30 ); //Pending Authorization
				}

				if ( $this->getCurrentUser() == $this->getUser() ) {
					Debug::Text( 'bEmployee is verifiying their own timesheet...', __FILE__, __LINE__, __METHOD__, 10 );
					//Employee is verifying their own timesheet.
					$this->setUserVerified( true );

					if ( $this->getAuthorized() == true ) { //If this has already been verified by superiors, and the employee is the last step, make sure mark this as verified.
						$this->setStatus( 50 );             //Verified
					} else {
						$this->setStatus( 30 ); //Pending Authorization.
					}
				}

				//If the top-level superior authorizes the timesheet before the employee has, make sure we keep the status as 30.
				if ( $this->getStatus() == 50 && $this->getUserVerified() == false ) {
					$this->setStatus( 45 ); //Pending Employee Verification
				}
			}

			//If this is a new verification, find the current authorization level to assign to it.
			if ( ( $this->isNew() == true || $this->getStatus() == 55 ) && ( $time_sheet_verification_type_id == 30 || $time_sheet_verification_type_id == 40 ) ) {
				$hierarchy_highest_level = AuthorizationFactory::getInitialHierarchyLevel( ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getCompany() : TTUUID::getZeroID() ), ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getID() : TTUUID::getZeroID() ), 90 );
				$this->setAuthorizationLevel( $hierarchy_highest_level );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		$this->calcStatus();

		if ( $this->getAuthorized() == true ) {
			$this->setAuthorizationLevel( 0 );
		}

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
		// Pay Period
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$this->Validator->isResultSetWithRows( 'pay_period',
											   $pplf->getByID( $this->getPayPeriod() ),
											   TTi18n::gettext( 'Invalid Pay Period' )
		);
		// User
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
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
		if ( $this->getUserVerifiedDate() !== false ) {
			$this->Validator->isDate( 'user_verified_date',
									  $this->getUserVerifiedDate(),
									  TTi18n::gettext( 'Incorrect Date' )
			);
		}

		// Authorization level
		if ( $this->getAuthorizationLevel() !== false ) {
			$this->Validator->isNumeric( 'authorization_level',
										 $this->getAuthorizationLevel(),
										 TTi18n::gettext( 'Incorrect authorization level' )
			);
		}

		if ( $this->getDeleted() == false ) {
			//Check to make sure an authorized/declined timesheet is not set back to pending status.
			$data_diff = $this->getDataDifferences();
			if ( $this->isDataDifferent( 'status_id', $data_diff ) == true && in_array( $data_diff['status_id'], [ 50, 55 ] ) && $this->getStatus() < 50 ) {
				$this->Validator->isTRUE( 'status_id',
										  false,
										  TTi18n::gettext( 'TimeSheet has already been authorized/declined' ) );
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getStatus() == '' ) {
			$this->Validator->isTrue( 'status',
									  false,
									  TTi18n::gettext( 'Status is invalid' ) );
		}

		if ( $this->getDeleted() == false && $this->getStatus() != 55 ) { //Declined
			//Check to make sure no critical severity exceptions exist.
			//Make sure we ignore the 'V1 - TimeSheet Not Verified' exception, as that could be critical and prevent them from ever verifying their timesheet.
			$elf = TTNew( 'ExceptionListFactory' ); /** @var ExceptionListFactory $elf */
			$elf->getByCompanyIDAndUserIdAndPayPeriodIdAndSeverityAndNotTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getPayPeriod(), [ 30 ], [ 'V1' ] );
			Debug::Text( ' Critcal Severity Exceptions: ' . $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $elf->getRecordCount() > 0 ) {
				$this->Validator->isTrue( 'exception',
										  false,
										  TTi18n::gettext( 'Unable to verify this timesheet when critical severity exceptions exist in the pay period' ) );
			}

			//Check to make sure no pending requests still exist in the pay period, as that will likely result in the timesheet changing.
			if ( is_object( $this->getPayPeriodObject() ) ) {
				$rlf = TTnew( 'RequestListFactory' ); /** @var RequestListFactory $rlf */
				$rlf->getByCompanyIdAndUserIdAndStatusAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUser(), 30, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), 1 );
				if ( $rlf->getRecordCount() > 0 ) {
					$this->Validator->isTrue( 'request',
											  false,
											  TTi18n::gettext( 'Unable to verify this timesheet when pending requests exist in the pay period' ) );
				}
			}

		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//If status is pending auth (55=declined) delete all authorization history, because they could be re-verifying.
		if ( $this->getCurrentUser() != false && $this->getStatus() == 55 ) {
			$alf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $alf */
			$alf->getByObjectTypeAndObjectId( 90, $this->getId() );
			if ( $alf->getRecordCount() > 0 ) {
				foreach ( $alf as $a_obj ) {
					//Delete the record outright for now, as marking it as deleted causes transaction issues
					//and it never gets committed.
					$a_obj->Delete( true );
				}
			}
		}

		$authorize_timesheet = false;

		if ( $this->getCurrentUser() != false ) {
			$time_sheet_verification_type_id = $this->getVerificationType();
			if ( $time_sheet_verification_type_id > 10 ) { //10 = Disabled

				if ( $time_sheet_verification_type_id == 20 ) { //Employee Only
					$authorize_timesheet = true;
				} else if ( $time_sheet_verification_type_id == 30 ) { //Superior Only
					if ( $this->getStatus() == 30 && $this->getCurrentUser() != false ) { //Check on CurrentUser so we don't loop indefinitely through AuthorizationFactory.
						Debug::Text( ' aAuthorizing TimeSheet as superior...', __FILE__, __LINE__, __METHOD__, 10 );
						$authorize_timesheet = true;
					}
				} else if ( $time_sheet_verification_type_id == 40 ) { //Superior & Employee
					//if ( $this->getStatus() == 30 && $this->getCurrentUser() != false && $this->getCurrentUser() != $this->getUser() ) { //Check on CurrentUser so we don't loop indefinitely through AuthorizationFactory.
					if ( $this->getStatus() == 30 && $this->getCurrentUser() != false ) {
						//If a supervisor is verifying their own timesheet, just authorize it immediately as well.
						//  When verifying any timesheet, getCurrentUser() and getUser() will always match.
						//     When authorizing a timesheet, they will never match.
						if ( $this->getCurrentUser() != $this->getUser() || $this->isHierarchySuperior( $this->getCurrentUser(), $this->getUser() ) == true ) {
							Debug::Text( ' bAuthorizing TimeSheet as superior...', __FILE__, __LINE__, __METHOD__, 10 );
							$authorize_timesheet = true;
						}
					}
				}

				if ( $this->getEnableAuthorize() == true && $authorize_timesheet == true && TTUUID::isUUID( $this->getCurrentUser() ) ) {
					$af = TTnew( 'AuthorizationFactory' ); /** @var AuthorizationFactory $af */
					$af->setCurrentUser( $this->getCurrentUser() );
					$af->setObjectType( 90 ); //TimeSheet
					$af->setObject( $this->getId() );
					$af->setAuthorized( true );
					if ( $af->isValid() ) {
						$af->Save(); //AuthorizationFactory->postSave() re-saves the TimeSheetVerify record, and can cause recalculation to occur twice.
					} else {
						Debug::Text( 'WARNING: Unable to create timesheet authorization record, perhaps it already exists?', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'Not authorizing timesheet...', __FILE__, __LINE__, __METHOD__, 10 );

					//Send initial Pending Authorization notification to superiors. -- This should only happen on first save by the regular employee.
					AuthorizationFactory::sendNotificationAuthorizationOnInitialObjectSave( $this->getCurrentUser(), 90, $this->getId() );
				}
			} else {
				Debug::Text( 'TimeSheet Verification is disabled...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'CurrentUser() is not set, perhaps its being called a 2nd time from AuthorizationFactory postSave()?', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $authorize_timesheet == true || $this->getAuthorized() == true ) {
			//Recalculate exceptions on the last day of pay period to remove any TimeSheet Not Verified exceptions.
			if ( is_object( $this->getPayPeriodObject() ) ) {
				$flags = [
						'meal'              => false,
						'undertime_absence' => false,
						'break'             => false,
						'holiday'           => false,
						'schedule_absence'  => false,
						'absence'           => false,
						'regular'           => false,
						'overtime'          => false,
						'premium'           => false,
						'accrual'           => false,

						'exception'           => true,
						//Exception options
						'exception_premature' => false, //Calculates premature exceptions
						'exception_future'    => true, //Calculates exceptions in the future. This is needed if the timesheet is authorized several days before the end of the pay period so the V1 exception goes away.

						//Calculate policies for future dates.
						'future_dates'        => false, //Calculates dates in the future.
						'past_dates'          => false, //Calculates dates in the past. This is only needed when Pay Formulas that use averaging are enabled?*
				];

				$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
				$cp->setFlag( $flags );
				$cp->setUserObject( $this->getUserObject() );
				$cp->calculate( $this->getPayPeriodObject()->getEndDate() ); //This sets timezone itself.
				$cp->Save();
			} else {
				Debug::Text( 'No Pay Period found...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'Not recalculating last day of pay period...', __FILE__, __LINE__, __METHOD__, 10 );
		}

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
	 * @param string $permission_children_ids UUID
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
						case 'window_start_date':
						case 'window_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( $variable ) ) );
							break;
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		//Should the object_id be the pay period ID instead, that way its easier to find the audit logs?
		if ( is_object( $this->getPayPeriodObject() ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'TimeSheet Verify' ) . ' - ' . TTi18n::getText( 'Employee' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Pay Period' ) . ': ' . TTDate::getDate( 'DATE', $this->getPayPeriodObject()->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $this->getPayPeriodObject()->getEndDate() ), null, $this->getTable() );
		}

		return false;
	}
}

?>