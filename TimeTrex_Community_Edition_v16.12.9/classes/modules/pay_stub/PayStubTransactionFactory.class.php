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
 * @package Modules\Payroll Agency
 */
class PayStubTransactionFactory extends Factory {
	protected $table = 'pay_stub_transaction';
	protected $pk_sequence_name = 'pay_stub_transaction_id_seq'; //PK Sequence name

	protected $remittance_source_account_obj = null;
	protected $remittance_destination_account_obj = null;
	protected $pay_stub_obj = null;
	protected $currency_obj = null;
	protected $old_currency_id = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'Id' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'parent_id' )->setFunctionMap( 'Parent' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_stub_id' )->setFunctionMap( 'PayStub' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'remittance_source_account_id' )->setFunctionMap( 'RemittanceSourceAccount' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'remittance_destination_account_id' )->setFunctionMap( 'RemittanceDestinationAccount' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'currency_rate' )->setFunctionMap( 'CurrencyRate' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'amount' )->setFunctionMap( 'Amount' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'transaction_date' )->setFunctionMap( 'TransactionDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'confirmation_number' )->setFunctionMap( 'ConfirmationNumber' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'varchar' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_pay_stub_transaction' )->setLabel( TTi18n::getText( 'Pay Stub Transaction' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'getPayStubTransaction' ) ),
											TTSField::new( 'remittance_source_account_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Source Account' ) )->setDataSource( TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getRemittanceSourceAccount' ) ),
											TTSField::new( 'remittance_destination_account_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Destination Account' ) )->setDataSource( TTSAPI::new( 'APIRemittanceDestinationAccount' )->setMethod( 'getRemittanceDestinationAccount' ) ),
												TTSField::new( 'currency_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APICurrency' )->setMethod( 'getCurrency' ) ),
											TTSField::new( 'amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Amount' ) ),
											TTSField::new( 'transaction_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Transaction Date' ) ),
											TTSField::new( 'confirmation_number' )->setType( 'text' )->setLabel( TTi18n::getText( 'Confirmation #' ) ),
											TTSField::new( 'note' )->setType( 'textarea' )->setLabel( TTi18n::getText( 'Note' ) )
									)
							)
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'lef.id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'uf.status_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_id' )->setType( 'uuid_list' )->setColumn( 'psf.id' )->setMulti( true ),
							TTSSearchField::new( 'remittance_destination_account_type_id' )->setType( 'numeric_list' )->setColumn( 'rdaf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'remittance_destination_account_id' )->setType( 'uuid_list' )->setColumn( 'a.remittance_destination_account_id' )->setMulti( true ),
							TTSSearchField::new( 'remittance_source_account_id' )->setType( 'uuid_list' )->setColumn( 'a.remittance_source_account_id' )->setMulti( true ),
							TTSSearchField::new( 'remittance_source_account_type_id' )->setType( 'numeric_list' )->setColumn( 'rsaf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'transaction_date' )->setType( 'date_range_timestamp' )->setColumn( 'a.transaction_date' ),
							TTSSearchField::new( 'start_date' )->setType( 'timestamp' )->setColumn( 'a.transaction_date' ),
							TTSSearchField::new( 'end_date' )->setType( 'timestamp' )->setColumn( 'a.transaction_date' ),
							TTSSearchField::new( 'transaction_status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'transaction_type_id' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'currency_id' )->setType( 'uuid_list' )->setColumn( 'a.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'rdaf.user_id' )->setMulti( true ),
							TTSSearchField::new( 'include_user_id' )->setType( 'uuid_list' )->setColumn( 'uf.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'not_uuid_list' )->setColumn( 'uf.id' )->setMulti( true ),
							TTSSearchField::new( 'user_status_id' )->setType( 'numeric_list' )->setColumn( 'uf.status_id' )->setMulti( true ),
							TTSSearchField::new( 'user_group_id' )->setType( 'uuid_list' )->setColumn( 'uf.group_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'user_title_id' )->setType( 'uuid_list' )->setColumn( 'uf.title_id' )->setMulti( true ),
							TTSSearchField::new( 'sex_id' )->setType( 'numeric_list' )->setColumn( 'uf.sex_id' )->setMulti( true ),
							TTSSearchField::new( 'user_tag' )->setType( 'tag' )->setColumn( 'uf.id' ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid_list' )->setColumn( 'psf.pay_period_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_status_id' )->setType( 'numeric_list' )->setColumn( 'psf.status_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_type_id' )->setType( 'numeric_list' )->setColumn( 'psf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_run_id' )->setType( 'numeric_list' )->setColumn( 'psf.run_id' )->setMulti( true ),
							TTSSearchField::new( 'is_reprint' )->setType( 'boolean' )->setColumn( 'a.parent_id' ),
							TTSSearchField::new( 'include_subgroups' )->setType( 'boolean' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'getPayStubTransaction' )
									->setSummary( 'Get pay stub transaction records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'setPayStubTransaction' )
									->setSummary( 'Add or edit pay stub transaction records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'deletePayStubTransaction' )
									->setSummary( 'Delete pay stub transaction records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'getPayStubTransaction' ) ),
											   ) ),
							TTSAPI::new( 'APIPayStubTransaction' )->setMethod( 'getPayStubTransactionDefaultData' )
									->setSummary( 'Get default pay stub transaction data used for creating new transactions. Use this before calling setPayStubTransaction to get the correct default data.' ),
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
			case 'remittance_source_account_type_id':
				$pstf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $pstf */
				$retval = $pstf->getOptions( 'type' );
				break;
			case 'transaction_status_id':
			case 'status':
				$retval = [
						10  => TTi18n::gettext( 'Pending' ),
						20  => TTi18n::gettext( 'Paid' ),
						100 => TTi18n::gettext( 'Stop Payment' ), //Stop Payment and don't re-issue.
						200 => TTi18n::gettext( 'Stop Payment - ReIssue' ), //Use this for checks and EFT to simplify things.

						//FIXME: When the above 200 status is used, they should be converted to this status once the new transaction is created. That way we can better handle cases where their might be multiple "ReIssue" transactions and they add up to more than the net pay. Essentially Re-Issue transcations are handled like Pending then. This one is handled like Stop Payment.
						// Transaction can only get here from 200.
						//201 => TTi18n::gettext( 'Stop Payment - ReIssued' ),
				];
				break;
			case 'transaction_type_id':
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Valid' ), //was: Enabled
						20 => TTi18n::gettext( 'InValid' ), //was: Disabled
				];
				break;
			case 'columns':
				$retval = [
						'-1000-status'                         => TTi18n::gettext( 'Status' ),
						'-1010-destination_user_first_name'    => TTi18n::gettext( 'First Name' ),
						'-1020-destination_user_last_name'     => TTi18n::gettext( 'Last Name' ),
						'-1030-remittance_source_account'      => TTi18n::gettext( 'Source Account' ),
						'-1040-remittance_destination_account' => TTi18n::gettext( 'Destination Account' ),
						'-1050-currency'                       => TTi18n::gettext( 'Currency' ),
						'-1060-remittance_source_account_type' => TTi18n::gettext( 'Source Account Type' ),
						'-1070-amount'                         => TTi18n::gettext( 'Amount' ),
						'-1075-currency_rate'                  => TTi18n::gettext( 'Currency Rate' ),
						'-1080-transaction_date'               => TTi18n::gettext( 'Transaction Date' ),
						'-1090-confirmation_number'            => TTi18n::gettext( 'Confirmation Number' ),

						'-1200-pay_stub_start_date'       => TTi18n::gettext( 'Pay Stub Start Date' ),
						'-1205-pay_stub_end_date'         => TTi18n::gettext( 'Pay Stub End Date' ),
						'-1210-pay_stub_transaction_date' => TTi18n::gettext( 'Pay Stub Transaction Date' ),
						'-1220-pay_stub_run_id'           => TTi18n::gettext( 'Payroll Run' ),

						'-1300-pay_period_start_date'       => TTi18n::gettext( 'Pay Period Start Date' ),
						'-1305-pay_period_end_date'         => TTi18n::gettext( 'Pay Period End Date' ),
						'-1310-pay_period_transaction_date' => TTi18n::gettext( 'Pay Period Transaction Date' ),


						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				if ( isset( $params['payroll_wizard'] ) ) {
					$retval['-1400-total_amount'] = TTi18n::gettext( 'Total Amount' );
					$retval['-1410-total_transactions'] = TTi18n::gettext( 'Total Transactions' );
				}
				break;

			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'status',
						'destination_user_first_name',
						'destination_user_last_name',
						'remittance_source_account',
						'remittance_destination_account',
						'amount',
						'transaction_date',
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
				'id'                                                => 'Id',
				'parent_id'                                         => 'Parent',
				'pay_stub_id'                                       => 'PayStub',
				'type_id'                                           => 'Type',
				'type'                                              => false,
				'status_id'                                         => 'Status',
				'status'                                            => false,
				'transaction_date'                                  => 'TransactionDate',
				'remittance_source_account_id'                      => 'RemittanceSourceAccount',
				'remittance_source_account'                         => false,
				'remittance_source_account_type'                    => false,
				'remittance_destination_account_id'                 => 'RemittanceDestinationAccount',
				'remittance_destination_account'                    => false,
				'remittance_source_account_last_transaction_number' => false,
				'currency_id'                                       => false, //Always forced to pay stub currency in presave. Should never be set from UI.
				'currency'                                          => false,
				'currency_rate'                                     => 'CurrencyRate',
				'amount'                                            => 'Amount',
				'confirmation_number'                               => 'ConfirmationNumber',
				'note'                                              => 'Note',

				'user_id'                     => false,
				'destination_user_first_name' => false,
				'destination_user_last_name'  => false,
				'pay_period_id'               => false,
				'pay_period_start_date'       => false,
				'pay_period_end_date'         => false,
				'pay_period_transaction_date' => false,
				'pay_stub_run_id'             => false,
				'pay_stub_status_id'          => false,
				'pay_stub_start_date'         => false,
				'pay_stub_end_date'           => false,
				'pay_stub_transaction_date'   => false,
				'legal_entity_legal_name'     => false,
				'legal_entity_trade_name'     => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getPayStubObject() {
		return $this->getGenericObject( 'PayStubListFactory', $this->getPayStub(), 'pay_stub_obj' );
	}


	/**
	 * Allow setting the pay stub object so we can determine if its a new record or not rather than re-getting it from the database.
	 * @param $pay_stub_obj PayStubFactory
	 * @return bool
	 */
	function setPayStubObject( $pay_stub_obj ) {
		$this->pay_stub_obj = $pay_stub_obj;

		return true;
	}

	/**
	 * @return bool
	 */
	function getRemittanceSourceAccountObject() {
		return $this->getGenericObject( 'RemittanceSourceAccountListFactory', $this->getRemittanceSourceAccount(), 'remittance_source_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getRemittanceDestinationAccountObject() {
		return $this->getGenericObject( 'RemittanceDestinationAccountListFactory', $this->getRemittanceDestinationAccount(), 'remittance_destination_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		$value = TTUUID::castUUID( trim( $value ) );
		$this->setGenericDataValue( 'parent_id', $value );

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getTransactionDate( $raw = false ) {
		//Debug::Text('Transaction Date: '. $this->data['transaction_date'] .' - '. TTDate::getDate('DATE+TIME', $this->data['transaction_date']), __FILE__, __LINE__, __METHOD__, 10);
		$value = $this->getGenericDataValue( 'transaction_date' );
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setTransactionDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods transact at noon.
			$value = TTDate::getTimeLockedDate( strtotime( '12:00:00', $value ), $value );
		}

		return $this->setGenericDataValue( 'transaction_date', TTDate::getDBTimeStamp( $value, false ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStub() {
		return $this->getGenericDataValue( 'pay_stub_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayStub( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_stub_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceSourceAccountName() {
		return $this->getGenericDataValue( 'remittance_source_account' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRemittanceSourceAccountName( $value ) {
		return $this->setGenericDataValue( 'remittance_source_account', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceSourceAccount() {
		return $this->getGenericDataValue( 'remittance_source_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRemittanceSourceAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'remittance_source_account_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceDestinationAccount() {
		return $this->getGenericDataValue( 'remittance_destination_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRemittanceDestinationAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'remittance_destination_account_id', $value );
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
		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		//Currency rate is set in preValidate()
		$this->old_currency_id = $this->getCurrency();

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getAmount() {
		$value = $this->getGenericDataValue( 'amount' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( $value, 2 );
		}

		return false;
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

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrencyRate() {
		return $this->getGenericDataValue( 'currency_rate' );
	}

	/**
	 * Currency exchange rate to convert the amount back to the base currency. Rate=1 would usually only happen if the current currency is the base currency.
	 * @param $value
	 * @return bool
	 */
	function setCurrencyRate( $value ) {
		$value = trim( $value );
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );
		if ( $value == 0 ) {
			$value = 1;
		}

		return $this->setGenericDataValue( 'currency_rate', $value );
	}

	/**
	 * @return mixed
	 */
	function getConfirmationNumber() {
		return $this->getGenericDataValue( 'confirmation_number' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConfirmationNumber( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'confirmation_number', $value );
	}

	/**
	 * @return mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @return mixed
	 */
	function getPayPeriodID() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayPeriodID( $value ) {
		$value = trim( $value );

		if ( $value == '' ) {

			$this->setGenericDataValue( 'pay_period_id', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function getRemittanceSourceAccountType() {
		return $this->getGenericDataValue( 'remittance_source_account_type' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRemittanceSourceAccountType( $value ) {
		$value = trim( $value );

		if ( $value == '' ) {

			$this->setGenericDataValue( 'remittance_source_account_type', $value );

			return true;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getType() == '' ) {
			$this->setType( 10 ); //Valid
		}

		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //Pending
		}

		//Validation errors likely won't allow this to even execute, but leave it here just in case.
		if ( $this->getRemittanceSourceAccount() == false && is_object( $this->getRemittanceDestinationAccountObject() ) ) {
			$this->setRemittanceSourceAccount( $this->getRemittanceDestinationAccountObject()->getRemittanceSourceAccount() );
		}

		if ( $this->getCurrency() == false && is_object( $this->getPayStubObject() ) ) {
			if ( is_object( $this->getRemittanceSourceAccountObject() ) ) {
				$this->setCurrency( $this->getRemittanceSourceAccountObject()->getCurrency() );
			} else {
				$this->setCurrency( $this->getPayStubObject()->getCurrency() );
			}
		}

		if ( $this->getCurrencyRate() == false && is_object( $this->getCurrencyObject() ) && ( $this->isNew() || $this->old_currency_id != $this->getCurrency() ) ) {
			$this->setCurrencyRate( $this->getCurrencyObject()->getReverseConversionRate() ); //Must always get the conversion rate from the current currency to the base currency, so it can be converted to any other currency from that.
		}

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		Debug::Text( 'Validating PayStubTransaction...', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Pay Stub
		if ( $this->getPayStub() !== false ) {
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$this->Validator->isResultSetWithRows( 'pay_stub_id',
												   $pslf->getByID( $this->getPayStub() ),
												   TTi18n::gettext( 'Invalid Pay Stub' )
			);
		}
		// Remittance source account
		if ( $this->getRemittanceSourceAccount() !== false ) {
			$lf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $lf */
			$this->Validator->isResultSetWithRows( 'remittance_source_account_id',
												   $lf->getByID( $this->getRemittanceSourceAccount() ),
												   TTi18n::gettext( 'Remittance source account is invalid' )
			);
		}
		// Remittance destination account
		if ( $this->getRemittanceDestinationAccount() !== false ) {
			$lf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $lf */
			$this->Validator->isResultSetWithRows( 'remittance_destination_account_id',
												   $lf->getByID( $this->getRemittanceDestinationAccount() ),
												   TTi18n::gettext( 'Employee payment method is invalid' )
			);
		}

		// Currency
		if ( $this->getCurrency() !== false ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}

		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}

		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Transaction date
		if ( $this->getTransactionDate() !== false ) {
			$this->Validator->isDate( 'transaction_date',
									  $this->getTransactionDate(),
									  TTi18n::gettext( 'Incorrect transaction date' )
			);
		}

		// Amount
		if ( $this->getAmount() !== false ) {
			$this->Validator->isNumeric( 'amount',
										 $this->getAmount(),
										 TTi18n::gettext( 'Incorrect Amount' )
			);
			if ( $this->getAmount() == 0 || $this->getAmount() == '' ) {
				$this->Validator->isTrue( 'amount',
										  false,
										  TTi18n::gettext( 'Amount cannot be zero' )
				);
			}
			if ( $this->getAmount() < 0 ) {
				$this->Validator->isTrue( 'amount',
										  false,
										  TTi18n::gettext( 'Amount cannot be negative' )
				);
			}
		}

		// Currency Rate
		if ( $this->getCurrencyRate() !== false ) {
			$this->Validator->isFloat( 'currency_rate',
									   $this->getCurrencyRate(),
									   TTi18n::gettext( 'Incorrect Currency Rate' )
			);
			// Confirmation number
			if ( $this->getConfirmationNumber() != '' ) {
				$this->Validator->isLength( 'confirmation_number',
											$this->getConfirmationNumber(),
											TTi18n::gettext( 'Confirmation number is too short or too long' ),
											1,
											50
				);
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		// Status
		if ( $this->getStatus() !== false ) {
			$status_options = $this->getOptions( 'status' );
			$validate_msg = TTi18n::gettext( 'Invalid Status' );

			$old_status_id = $this->getGenericOldDataValue( 'status_id' );
			switch ( $old_status_id ) {
				case 100: //Stop Payment
				case 200: //Stop Payment - ReIssue
					$valid_statuses = [ 100, 200 ];
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext( 'Status can only be changed to another Stop Payment' );
					break;
				case 20: //Paid
					$valid_statuses = [ 20, 100, 200 ];
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext( 'Status can only be changed from Paid to Stop Payment' );
					break;
				case 10: //Pending
					$valid_statuses = [ 10, 20 ];
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext( 'Status can only be changed from Pending to Paid' );
					break;
				default:
					break;
			}

			Debug::Text( '  Old Status ID: ' . $old_status_id . ' Status ID: ' . $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  $validate_msg,
										  $status_options
			);
		}

		if ( $this->Validator->getValidateOnly() == false && $this->getStatus() != 100 ) { //Ignore this check when setting to stop payment.
			//Make sure Source Account and Destination Account types match.
			if ( $this->getRemittanceSourceAccount() !== false && $this->getRemittanceDestinationAccount() !== false ) {
				if ( is_object( $this->getRemittanceSourceAccountObject() ) && is_object( $this->getRemittanceDestinationAccountObject() ) ) {
					if ( $this->getRemittanceSourceAccountObject()->getType() != $this->getRemittanceDestinationAccountObject()->getType() ) {
						$this->Validator->isTrue( 'remittance_destination_account_id',
												  false,
												  TTi18n::gettext( 'Invalid Payment Method, Source/Destination Account types mismatch' ) );
					}
				}
			}

			if ( $this->getTransactionDate() == false ) {
				$this->Validator->isDate( 'transaction_date',
										  $this->getTransactionDate(),
										  TTi18n::gettext( 'Incorrect transaction date' ) );
			}

			// Presave is called after validate so we can't assume source account is set.
			if ( $this->getRemittanceSourceAccount() == false ) {
				$this->Validator->isTrue( 'remittance_source_account_id',
										  false,
										  TTi18n::gettext( 'Source account not specified' ) );
			}

			if ( $this->getCurrency() == false ) {
				$this->Validator->isTrue( 'currency_id',
										  false,
										  TTi18n::gettext( 'Currency not specified' ) );
			}

			//Make sure the pay stub is OPEN if its not a new pay stub. This allow API to create a paid pay stub with paid transactions in a single operation.
			if ( is_object( $this->getPayStubObject() ) && ( !isset( $this->getPayStubObject()->is_new ) || $this->getPayStubObject()->is_new == false ) && $this->getPayStubObject()->getStatus() > 25 ) {
				$this->Validator->isTrue( 'pay_stub',
										  false,
										  TTi18n::gettext( 'Pay Stub must be OPEN to modify transactions' ) );
			}
		}

		//Transaction is paid, fail when editing amount.
		if ( $this->getStatus() == 20 ) {
			$changed_fields = array_keys( $this->getDataDifferences() );
			$deny_fields = [ 'remittance_source_account_id', 'transaction_date', 'amount' ];

			if ( in_array( 'amount', $changed_fields ) ) {
				if ( TTMath::removeTrailingZeros( $this->data['amount'] ) == TTMath::removeTrailingZeros( $this->old_data['amount'] ) ) {
					unset( $changed_fields[array_search( 'amount', $changed_fields )] );
				}
			}
			foreach ( $changed_fields as $field ) {
				$this->Validator->isTrue( $field,
										  !in_array( $field, $deny_fields ),
										  TTi18n::gettext( 'Pay stub transaction is already paid unable to edit' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		$rs_obj = $this->getRemittanceSourceAccountObject();
		$le_obj = $rs_obj->getLegalEntityObject();
		if ( $this->getDeleted() == false && is_object( $rs_obj ) && $rs_obj->getType() == 3000 && $rs_obj->getDataFormat() == 5 && in_array( $this->getStatus(), [ 100, 200 ] ) ) { //3000=EFT/ACH, 5=TimeTrex EFT, 100=Stop Payment, 200=Stop Payment (ReIssue)
			Debug::Text( '  Issuing Stop Payment for TimeTrex PaymentServices... ', __FILE__, __LINE__, __METHOD__, 10 );

			//Send data to TimeTrex Remittances service.
			$tt_ps_api = $le_obj->getPaymentServicesAPIObject();

			if ( PRODUCTION == true && is_object( $le_obj ) && $le_obj->getPaymentServicesStatus() == 10 && $le_obj->getPaymentServicesUserName() != '' && $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
				try {
					$retval = $tt_ps_api->setPayStubTransaction( [ '_kind' => 'Transaction', 'remote_id' => $this->getId(), 'status_id' => 'S' ] ); //S=Stop Payment by Client
				} catch ( Exception $e ) {
					Debug::Text( 'ERROR! Unable to upload pay stub transaction data... (c) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'WARNING: Production is off, not calling payment services API...', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = true;
			}

			if ( $retval === false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * starts EFT file
	 *
	 * @param object $rs_obj
	 * @return EFT
	 */
	function startEFTFile( $rs_obj ) {
		$data_format_type_id = $rs_obj->getDataFormat();
		$data_format_types = $rs_obj->getOptions( 'data_format_eft_form' );

		$eft = new EFT();
		$eft->setFileFormat( $data_format_types[$data_format_type_id] );
		$eft->setBusinessNumber( $rs_obj->getValue4() ); //ACH
		$eft->setOriginatorID( $rs_obj->getValue5() );
		$eft->setFileCreationNumber( $rs_obj->getNextTransactionNumber() );
		$eft->setInitialEntryNumber( ( ( $rs_obj->getValue9() != '' ) ? $rs_obj->getValue9() : substr( $rs_obj->getValue5(), 0, 8 ) ) ); //ACH
		$eft->setBatchDiscretionaryData( ( ( $rs_obj->getValue10() != '' ) ? $rs_obj->getValue10() : null ) ); //ACH
		$eft->setDataCenter( $rs_obj->getValue7() );
		$eft->setDataCenterName( $rs_obj->getValue8() ); //ACH

		if ( $rs_obj->getLegalEntity() == TTUUID::getNotExistID() && is_object( $rs_obj->getCompanyObject() ) && trim( $rs_obj->getCompanyObject()->getShortName() ) != '' ) { //Source account assigned to "ANY" legal entity, fall back to company object for the long name.
			$eft->setOtherData( 'originator_long_name', $rs_obj->getCompanyObject()->getName() );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		} else if ( is_object( $rs_obj->getLegalEntityObject() ) && trim( $rs_obj->getLegalEntityObject()->getTradeName() ) != '' ) {
			$eft->setOtherData( 'originator_long_name', $rs_obj->getLegalEntityObject()->getTradeName() ); //Originator Long name based on legal entity name. It will be trimmed automatically in EFT class.
		}

		if ( trim( $rs_obj->getValue11() ) != '' ) {
			$eft->setCompanyShortName( $rs_obj->getValue11() );     //Company short name based on remittance source account setting.
		} else if ( trim( $rs_obj->getValue6() ) != '' ) {
			$eft->setCompanyShortName( $rs_obj->getValue6() ); //Fall back Immediate Origin (originator short) name as historically that is was used in older versions.
		} elseif ( $rs_obj->getLegalEntity() == TTUUID::getNotExistID() && is_object( $rs_obj->getCompanyObject() ) && trim( $rs_obj->getCompanyObject()->getShortName() ) != '' ) { //Source account assigned to "ANY" legal entity, fall back to company object for the company short name.
			$eft->setCompanyShortName( $rs_obj->getCompanyObject()->getShortName() );     //Company short name based on company name. It will be trimmed automatically in EFT class.
		} else if ( is_object( $rs_obj->getLegalEntityObject() ) && trim( $rs_obj->getLegalEntityObject()->getShortName() ) != '' ) {
			$eft->setCompanyShortName( $rs_obj->getLegalEntityObject()->getShortName() ); //Company short name based on legal entity name. It will be trimmed automatically in EFT class.
		} else {
			$eft->setCompanyShortName( $eft->getOtherData( 'originator_long_name' ) ); //Fall back immediate origin (originator short) name as historically that is was used in older versions.
		}

		if ( $rs_obj->getValue6() != '' ) {
			$eft->setOriginatorShortName( $rs_obj->getValue6() );
		} else {
			$eft->setOriginatorShortName( $eft->getOtherData( 'originator_long_name' ) ); //Base the short name off the long name if it isn't otherwise specified.
		}

		if ( is_object( $rs_obj->getCurrencyObject() ) ) {
			$eft->setCurrencyISOCode( $rs_obj->getCurrencyObject()->getISOCode() );
		}

		$eft->setOtherData( 'sub_file_format', $data_format_type_id );

		//So far only used for CIBC file format
		$eft->setOtherData( 'settlement_institution', $rs_obj->getValue26() );
		$eft->setOtherData( 'settlement_transit', $rs_obj->getValue27() );
		$eft->setOtherData( 'settlement_account', $rs_obj->getValue28() );

		//File header line, some RBC services require a "routing" line at the top of the file.
		if ( trim( $rs_obj->getValue29() ) != '' ) {
			$eft->setFilePrefixData( $rs_obj->getValue29() );
		}

		return $eft;
	}

	/**
	 * Completes the eft file.
	 *
	 * @param $eft
	 * @param object $rs_obj
	 * @param object $uf_obj
	 * @param object $ps_obj
	 * @param $current_company
	 * @param $total_credit_amount
	 * @param $next_transaction_number
	 * @param $output
	 * @return mixed
	 */
	function endEFTFile( $eft, $rs_obj, $uf_obj, $ps_obj, $current_company, $total_credit_amount, $next_transaction_number, $output ) {
		$is_balanced = $rs_obj->getValue24();
		if ( $total_credit_amount > 0 && (bool)$is_balanced == true ) {
			Debug::Text( '  Balancing ACH... ', __FILE__, __LINE__, __METHOD__, 10 );
			$record = new EFT_Record();
			$record->setType( 'D' );
			$record->setCPACode( 200 );
			$record->setAmount( $total_credit_amount );

			$record->setDueDate( TTDate::getBeginDayEpoch( $ps_obj->getTransactionDate() ) );

			if ( $rs_obj->getValue28() != '' ) { //If specific OFFSET bank account is specified, use it here. Otherwise default to the source account.
				if ( $rs_obj->getCountry() == 'CA' ) {
					$record->setInstitution( $rs_obj->getValue26() ); //Return Account Institution.
					$record->setTransit( $rs_obj->getValue27() );
					$record->setAccount( $rs_obj->getValue28() );
				} else {
					$record->setInstitution( $rs_obj->getValue1() ); //Checking/Savings Account.
					$record->setTransit( $rs_obj->getValue27() );
					$record->setAccount( $rs_obj->getValue28() );
				}
			} else {
				$record->setInstitution( $rs_obj->getValue1() );
				$record->setTransit( $rs_obj->getValue2() );
				$record->setAccount( $rs_obj->getValue3() );
			}

			$record->setName( substr( $eft->getOtherData( 'originator_long_name' ), 0, 30 ) );

			$record->setOriginatorShortName( $eft->getOriginatorShortName() );
			$record->setOriginatorLongName( $eft->getOtherData( 'originator_long_name' ) );

			$offset = $rs_obj->getValue25();
			if ( strlen( trim( $offset ) ) === 0 ) {
				$offset = 'OFFSET';
			}
			$record->setOriginatorReferenceNumber( $offset );

			//Don't need return accounts for ACH transactions.
			$eft->setRecord( $record );
		} else {
			Debug::Text( '  NOT Balancing ACH... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
		unset( $is_balanced );

		//File trailer line.
		if ( trim( $rs_obj->getValue30() ) != '' ) { //Make sure we don't put blank lines at the end of file, as that can break some systems like TelPay.
			$eft->setFilePostfixData( $rs_obj->getValue30() );
		}

		$eft->compile();
		$file_name = $this->formatFileName( $rs_obj, $next_transaction_number, 'EFT', null ); //Don't specify file extension, this forces IE to save the file rather than open it, hopefully preventing users from losing the file by clicking "OPEN", opening in notepad.exe, then closing notepad and having the file end up in IEs temporary "APP DATA" folder.


		Debug::Text( 'EFT File name : ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$output[$rs_obj->getId()] = [ 'file_name' => $file_name, 'mime_type' => 'Application/Text', 'data' => $eft->getCompiledData() ];

		//rs_obj cleared on save unless passed false
		$rs_obj->setLastTransactionNumber( $next_transaction_number );
		if ( $rs_obj->isValid() ) {
			$rs_obj->Save( false );
		}
		unset( $eft );

		return $output;
	}

	/**
	 * @param object $rs_obj
	 * @param $transaction_number
	 * @param $prefix
	 * @param $extension
	 * @return string
	 */
	function formatFileName( $rs_obj, $transaction_number, $prefix, $extension = null ) {
		//NOTE: At least one bank (Canadian Western Bank) do require a file extension (ie: .txt) before it allows you to upload a file.
		//      Other systems like Bambora require the file name to be less than 32 chars and a .txt extension.

		//Don't use users preferred date format, as it could contain spaces.
		$file_name = $prefix . '_' . substr( preg_replace( '/[^A-Za-z0-9_-]/', '', str_replace( ' ', '_', $rs_obj->getName() ) ), 0, 20 ) . '_' . (int)$transaction_number . '_' . TTDate::getHumanReadableDateStamp( time() );

		//Since we don't include the file extension in all cases (this helps prevent IE from opening the file), append the file format to the end of the file as a pseudo extension.
		if ( $rs_obj->getType() == 3000 ) {
			if ( $rs_obj->getDataFormat() == 10 ) { //ACH
				$file_name .= '_ACH';
			} else if ( $rs_obj->getDataFormat() == 1000 || $rs_obj->getDataFormat() == 1010 ) { //Carribbean CIBC/ECAB
				$file_name .= '_EFT';
				if ( $extension == '' ) {
					$extension = 'CSV';
				}
			} else { //EFT
				$file_name .= '_EFT';
			}
		}

		if ( $extension != '' ) {
			$file_name .= '.' . $extension;
		}

		return $file_name;
	}

	/**
	 * Complete the cheque pdf and assign to output array.
	 *
	 * @param object $rs_obj
	 * @param object $ps_obj
	 * @param $transaction_number
	 * @param $output
	 * @param $cheque_object
	 * @return array
	 */
	function endChequeFile( $rs_obj, $ps_obj, $transaction_number, $output, $cheque_object ) {
		$file_name = $this->formatFileName( $rs_obj, $rs_obj->getNextTransactionNumber(), 'CHK', 'pdf' ); //transaction number for filename should be first cheque # in this file
		Debug::Text( 'Cheque File name : ' . $file_name . ' Source Account Id: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
		$output[] = [ 'file_name' => $file_name, 'mime_type' => 'application/pdf', 'data' => $cheque_object->output( 'PDF' ) ];

		$rs_obj->setLastTransactionNumber( $transaction_number );
		//rs_obj cleared on save unless passed false
		if ( $rs_obj->isValid() ) {
			$rs_obj->Save( false );
		}

		return $output;
	}

	/**
	 * Compiles the cheque data
	 *
	 * @param object $ps_obj
	 * @param object $pst_obj
	 * @param $rs_obj
	 * @param object $uf_obj
	 * @param $transaction_number
	 * @param bool $alignment_grid
	 * @return array
	 */
	function getChequeData( $ps_obj, $pst_obj, $rs_obj, $uf_obj, $transaction_number, $alignment_grid = false ) {
		return [
				'date'             => $pst_obj->getTransactionDate(), //Use the transaction date rather than pay stub transaction (payment) date. So if a cheque isn't cashed, it can be re-issued at a later date.
				'amount'           => $pst_obj->getAmount(),
				'stub_left_column' => $uf_obj->getFullName() . "\n" .
						TTi18n::gettext( 'Identification #' ) . ': ' . $ps_obj->getDisplayID() . "\n" .
						TTi18n::gettext( 'Check #' ) . ': ' . $transaction_number . "\n" .
						TTi18n::gettext( 'Net Pay' ) . ': ' . $ps_obj->getCurrencyObject()->getSymbol() .
						$pst_obj->getAmount(), true, $ps_obj->getCurrencyObject()->getRoundDecimalPlaces(),

				'stub_right_column' => TTi18n::gettext( 'Pay Start Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getStartDate() ) . "\n" .
						TTi18n::gettext( 'Pay End Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getEndDate() ) . "\n" .
						TTi18n::gettext( 'Payment Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ),

				'start_date' => $ps_obj->getStartDate(),
				'end_date'   => $ps_obj->getEndDate(),

				'full_name'    => $uf_obj->getFullName(),
				'full_address' => Misc::formatAddress( $uf_obj->getFullName(), $uf_obj->getAddress1(), $uf_obj->getAddress2(), $uf_obj->getCity(), $uf_obj->getProvince(), $uf_obj->getPostalCode(), Option::getByKey( $uf_obj->getCountry(), $uf_obj->getCompanyObject()->getOptions( 'country' ) ), 'multiline_condensed' ), //Condensed format.
				'address1'     => $uf_obj->getAddress1(),
				'address2'     => $uf_obj->getAddress2(),
				'city'         => $uf_obj->getCity(),
				'province'     => $uf_obj->getProvince(),
				'postal_code'  => $uf_obj->getPostalCode(),
				'country'      => Option::getByKey( $uf_obj->getCountry(), $uf_obj->getCompanyObject()->getOptions( 'country' ) ),

				'company_name' => $uf_obj->getLegalEntityObject()->getLegalName(),
				'company_full_address' => Misc::formatAddress( '', $uf_obj->getLegalEntityObject()->getAddress1(), $uf_obj->getLegalEntityObject()->getAddress2(), $uf_obj->getLegalEntityObject()->getCity(), $uf_obj->getLegalEntityObject()->getProvince(), $uf_obj->getLegalEntityObject()->getPostalCode(), Option::getByKey( $uf_obj->getLegalEntityObject()->getCountry(), $uf_obj->getCompanyObject()->getOptions( 'country' ) ), 'oneline' ), //One line format.
				'company_address1' => $uf_obj->getLegalEntityObject()->getAddress1(),
				'company_address2' => $uf_obj->getLegalEntityObject()->getAddress2(),
				'company_city' => $uf_obj->getLegalEntityObject()->getCity(),
				'company_province' => $uf_obj->getLegalEntityObject()->getProvince(),
				'company_postal_code' => $uf_obj->getLegalEntityObject()->getPostalCode(),
				'company_country' => Option::getByKey( $uf_obj->getLegalEntityObject()->getCountry(), $uf_obj->getCompanyObject()->getOptions( 'country' ) ),


				'symbol' => $ps_obj->getCurrencyObject()->getSymbol(),

				'signature' => ( ( $rs_obj->isSignatureExists() == true ) ? $rs_obj->getSignatureFileName() : false ),

				'alignment_grid' => $alignment_grid,
		];
	}

	/**
	 * Compiles EFT record data.
	 * @param $eft
	 * @param object $pst_obj
	 * @param object $ps_obj
	 * @param object $rs_obj
	 * @param object $uf_obj
	 * @param $originator_reference_number
	 * @return EFT_Record
	 */
	function getEFTRecord( $eft, $pst_obj, $ps_obj, $rs_obj, $uf_obj, $originator_reference_number ) {
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( $pst_obj->getAmount() );

		$record->setDueDate( TTDate::getBeginDayEpoch( $pst_obj->getTransactionDate() ) ); //Use the transaction date rather than pay stub transaction (payment) date. So if a direct deposit was returned, it can be re-issued at a later date.

		//Destination Account
		if ( is_object( $pst_obj->getRemittanceDestinationAccountObject() ) ) {
			$record->setInstitution( $pst_obj->getRemittanceDestinationAccountObject()->getValue1() );
			$record->setTransit( $pst_obj->getRemittanceDestinationAccountObject()->getValue2() );
			$record->setAccount( $pst_obj->getRemittanceDestinationAccountObject()->getValue3() );
		}

		$record->setIndividualID( $uf_obj->getEmployeeNumber() );
		$record->setName( $uf_obj->getFullName( true ) ); //Last name first with middle initial, so it can be properly sorted.

		$record->setOriginatorShortName( $eft->getOriginatorShortName() );
		$record->setOriginatorLongName( $eft->getOtherData( 'originator_long_name' ) );
		$record->setOriginatorReferenceNumber( $originator_reference_number ); //19 or less chars.

		if ( $rs_obj->getValue28() != '' ) { //If specific return bank account is specified, use it here. Otherwise default to the source account.
			$record->setReturnInstitution( $rs_obj->getValue26() );
			$record->setReturnTransit( $rs_obj->getValue27() );
			$record->setReturnAccount( $rs_obj->getValue28() );
		} else {
			$record->setReturnInstitution( $rs_obj->getValue1() );
			$record->setReturnTransit( $rs_obj->getValue2() );
			$record->setReturnAccount( $rs_obj->getValue3() );
		}

		return $record;
	}

	/**
	 * The export portion of this function is mirrored in APIRemittanceSourceAccount::testExport()
	 * @param object $pstlf ListFactory
	 * @param object $company_obj
	 * @param null $last_transaction_numbers
	 * @return bool
	 */
	function exportPayStubTransaction( $pstlf = null, $company_obj = null, $last_transaction_numbers = null ) {
		require_once( Environment::getBasePath() . '/classes/ChequeForms/ChequeForms.class.php' );
		$output = [];

		if ( is_object( $company_obj ) ) {
			$current_company = $company_obj;
		} else {
			global $current_company;
		}

		if ( is_a( $pstlf, 'PayStubTransactionListFactory' ) == false ) {
			return false;
		}

		$pstlf->StartTransaction(); //Ensure that all transaction are processed in the entire batch, or NONE are processed to avoid cases where only one file, or one set of transactions is paid and the user misses the failures.
		if ( $pstlf->getRecordCount() > 0 ) {
			//start with getting paystub transactions sorted by legal entity id, source acocunt
			Debug::Text( 'Getting paystub transactions. Count: ' . $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$pstlf_sorted_array = [];
			foreach ( $pstlf as $tmp_pst_obj ) {
				$pstlf_sorted_array[TTUUID::castUUID( $tmp_pst_obj->getRemittanceSourceAccount() )][] = $tmp_pst_obj;
			}
			unset( $tmp_pst_obj );

			$i = 0;

			//EACH SOURCE
			foreach ( $pstlf_sorted_array as $pstlf_sub_sorted_array ) {
				$total_credit_amount = 0;
				$transaction_number = 1;
				$n = 0;
				$n_max = ( count( $pstlf_sub_sorted_array ) - 1 );
				//EACH BATCH
				foreach ( $pstlf_sub_sorted_array as $pst_obj ) {
					Debug::Text( '---------------------------------------------------------------------', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Text( 'PS Transaction ID: ' . $pst_obj->getId() . ' Amount: ' . $pst_obj->getAmount() . ' Type: ' . $pst_obj->getType() . ' Status: ' . $pst_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

					//If the status is a Stop Payment - ReIssue (200), and still type=10 (Valid)
					//clone the object and create a new one to provide history
					if ( $pst_obj->getStatus() == 200 ) {
						if ( $pst_obj->getType() == 10 && ( $this->getParent() == false || $this->getParent() == TTUUID::getZeroID() ) ) {
							Debug::Text( '  Found stop payment, re-issuing...', __FILE__, __LINE__, __METHOD__, 10 );
							//Stop payment. Mark this record disabled and add a new transaction to the parent chain.
							$old_obj = clone $pst_obj; //clone old object
							$old_obj->clearOldData();  //Clear out old data so its like starting from scratch. This prevents some validation failures on setStatus() changes.
							$old_obj->setType( 20 );   //set old object to InValid
							if ( $old_obj->isValid() ) {
								$old_obj->Save();
							}
							unset( $old_obj ); //get the old object out of memory

							//Since the object has been cloned, the old object id needs to be set as the parent id.
							$pst_obj->clearOldData();
							$pst_obj->setParent( $pst_obj->getId() );
							$pst_obj->setId( $pst_obj->getNextInsertId() ); //Now that parent id is set, force the ID to a new one. This must be done before data is uploaded to payment services ID, otherwise the mapping won't be made.
							$pst_obj->setStatus( 10 );                      //Pending

							//Make sure we update some key pieces of information when cloning the object.
							if ( is_object( $pst_obj->getPayStubObject() ) ) {
								$pay_stub_transaction_date = TTDate::getBeginDayEpoch( $pst_obj->getPayStubObject()->getTransactionDate() );
								if ( TTDate::getBeginDayEpoch( time() ) > $pay_stub_transaction_date ) {
									Debug::Text( '  NOTICE: Re-issuing transaction AFTER the pay stub transaction date, likely a cheque was lost/stolen/not cashed, therefore use todays date by default...', __FILE__, __LINE__, __METHOD__, 10 );
									$pst_obj->setTransactionDate( TTDate::getBeginDayEpoch( time() ) );
								} else {
									$pst_obj->setTransactionDate( $pst_obj->getPayStubObject()->getTransactionDate() ); //Allow the transaction date to change based on the *pay stub* transaction date. Since they have no other chance to change the date.
								}

								unset( $pay_stub_transaction_date );
							}

							$pst_obj->setCreatedDate();
							$pst_obj->setCreatedBy();
							$pst_obj->setUpdatedDate();
							$pst_obj->setUpdatedBy();
						}
					}

					if ( $pst_obj->getStatus() == 10 ) {
						$uf_obj = $pst_obj->getRemittanceDestinationAccountObject()->getUserObject();
						Debug::Text( 'USER: name: [' . $uf_obj->getFullName() . '] ID: ' . $uf_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						$rd_obj = $pst_obj->getRemittanceDestinationAccountObject();
						Debug::Text( 'RDA: name: [' . $rd_obj->getName() . '] ID: ' . $rd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						$ps_obj = $pst_obj->getPayStubObject();
						Debug::Text( 'Transaction Date: Transaction: [' . TTDate::getDate( 'DATE', $pst_obj->getTransactionDate() ) . '] Pay Stub: ['. TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ) .'] ID: ' . $ps_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//Get first rs_obj
						if ( $n == 0 ) {
							$rs_obj = $pst_obj->getRemittanceSourceAccountObject();

							$le_obj = $rs_obj->getLegalEntityObject();

							if ( isset( $last_transaction_numbers ) && isset( $last_transaction_numbers[$rs_obj->getId()] ) && count( $last_transaction_numbers ) > 0 ) {
								Debug::Text( 'Overriding last transaction number for ' . $rs_obj->getName() . ' to: ' . $last_transaction_numbers[$rs_obj->getId()], __FILE__, __LINE__, __METHOD__, 10 );
								$rs_obj->setLastTransactionNumber( $last_transaction_numbers[$rs_obj->getId()] );
							}
							Debug::Text( 'Starting New Batch! Name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

							$data_format_types = $rs_obj->getOptions( 'data_format_check_form' );
						}
						Debug::Text( 'RSA: name: [' . $rs_obj->getName() . '] Type: ' . $rs_obj->getType() . ' ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//TimeTrex PaymentServices API loop
						if ( $rs_obj->getType() == 3000 && $rs_obj->getDataFormat() == 5 ) { //3000=EFT/ACH 5=TimeTrex Payment Services
							//START BATCH
							if ( $n == 0 ) {
								//Send data to TimeTrex Remittances service.
								$tt_ps_api = $le_obj->getPaymentServicesAPIObject();

								$next_transaction_number = $rs_obj->getNextTransactionNumber();
								Debug::Text( 'PaymentServices API RemittanceSourceAccount: name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							}

							if ( $pst_obj->getAmount() > 0 ) {
								$confirmation_number = strtoupper( substr( sha1( TTUUID::generateUUID() ), -6 ) ); //Generate random string from UUIDs... Keep it around 6 chars so there is more room on EFT descriptions.

								//Batch payment services API requests so they can all be sent in a single transaction.
								$tt_ps_api_request_arr[] = $tt_ps_api->convertPayStubTransactionObjectToTransactionArray( $pst_obj, $ps_obj, $rs_obj, $uf_obj, $confirmation_number, $next_transaction_number );
							}

							//END BATCH
							if ( $n == $n_max ) {
								Debug::Text( 'Ending PaymentServices API  Batch! Source name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

								if ( isset( $tt_ps_api_request_arr ) && count( $tt_ps_api_request_arr ) > 0 ) {

									if ( PRODUCTION == true && is_object( $le_obj ) && $le_obj->getPaymentServicesStatus() == 10 && $le_obj->getPaymentServicesUserName() != '' && $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
										try {
											$tt_ps_api_retval = $tt_ps_api->setPayStubTransaction( $tt_ps_api_request_arr );
											if ( $tt_ps_api_retval->isValid() == true ) {
												$output[$rs_obj->getId()] = true;
											} else {
												Debug::Arr( $tt_ps_api_retval, 'ERROR! Unable to upload pay stub transaction data... (a)', __FILE__, __LINE__, __METHOD__, 10 );
												$pstlf->FailTransaction();
												unset( $confirmation_number ); //This prevents the transaction from being marked as PAID upon error, and also ensures the entire transaction fails.
											}
										} catch ( Exception $e ) {
											Debug::Text( 'ERROR! Unable to upload pay stub transaction data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
											$pstlf->FailTransaction();
											unset( $confirmation_number ); //This prevents the transaction from being marked as PAID upon error, and also ensures the entire transaction fails.
										}
									} else {
										Debug::Text( 'WARNING: Production is off, not calling payment services API...', __FILE__, __LINE__, __METHOD__, 10 );
										$output[$rs_obj->getId()] = true;
									}
								} else {
									Debug::Text( 'WARNING: No Payment Service API requests to send...', __FILE__, __LINE__, __METHOD__, 10 );
								}
								unset( $tt_ps_api_request_arr );

								//rs_obj cleared on save unless passed false
								$rs_obj->setLastTransactionNumber( $next_transaction_number );
								if ( $rs_obj->isValid() ) {
									$rs_obj->Save( false );
								}
							}
						} //end TimeTrex Remittances API loop

						//EFT loop
						if ( $rs_obj->getType() == 3000 && $rs_obj->getDataFormat() != 5 ) {
							//START BATCH
							if ( $n == 0 ) {
								$next_transaction_number = $rs_obj->getNextTransactionNumber();
								Debug::Text( 'EFT RemittanceSourceAccount: name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								$eft = $this->startEFTFile( $rs_obj );
							}

							if ( $pst_obj->getAmount() > 0 ) {
								$confirmation_number = strtoupper( substr( sha1( TTUUID::generateUUID() ), -8 ) ); //Generate random string from UUIDs... Keep it around 6 chars to so its easier to exchange over phone or something, as well as to display on pay stubs.
								$record = $this->getEFTRecord( $eft, $pst_obj, $ps_obj, $rs_obj, $uf_obj, $confirmation_number );
								$eft->setRecord( $record );
							}

							$total_credit_amount += $pst_obj->getAmount();

							//END BATCH
							if ( $n == $n_max ) {
								Debug::Text( 'Ending EFT Batch! Source name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								$output = $this->endEFTFile( $eft, $rs_obj, $uf_obj, $ps_obj, $current_company, $total_credit_amount, $next_transaction_number, $output );
							}
						} //end EFT loop

						//CHECK loop
						if ( $rs_obj->getType() == 2000 && $rs_obj->getDataFormat() != 5 ) {
							//START BATCH
							if ( $n == 0 ) {
								$data_format_type_id = $rs_obj->getDataFormat();
								$check_file_obj = TTnew( 'ChequeForms' ); /** @var ChequeForms $check_file_obj */
								$check_obj = $check_file_obj->getFormObject( strtoupper( $data_format_types[$data_format_type_id] ) );
								$check_obj->setPageOffsets( $rs_obj->getValue6(), $rs_obj->getValue5() ); //Value5=Vertical, Value6=Horizontal
								$transaction_number = $rs_obj->getNextTransactionNumber();
								Debug::Text( 'New Cheque of type: ' . $data_format_types[$data_format_type_id], __FILE__, __LINE__, __METHOD__, 10 );
							}

							$ps_data = $this->getChequeData( $ps_obj, $pst_obj, $rs_obj, $uf_obj, $transaction_number );
							$check_obj->addRecord( $ps_data );
							Debug::Text( 'Row added to cheque' . $ps_obj->getId() . ' Transaction Number: ' . $transaction_number, __FILE__, __LINE__, __METHOD__, 10 );

							$check_file_obj->addForm( $check_obj );
							$confirmation_number = $transaction_number;

							//end this file and start another file.
							if ( $n == $n_max ) {
								$output = $this->endChequeFile( $rs_obj, $ps_obj, $transaction_number, $output, $check_file_obj );
								$transaction_number = 1;
								//Debug::Arr($output,'NEW File Added To CHQ Output',__FILE__,__LINE__,__METHOD__,10);
							}

							$transaction_number++; //This needs to go after endChequeFile() otherwise it will always add 1 too many to the last cheque number.
						} //end CHECK loop

						if ( isset( $confirmation_number ) ) { //If no confirmation is set, it likely didn't get paid since it wasn't with check or direct deposit.
							$pst_obj->setConfirmationNumber( $confirmation_number );
							$pst_obj->setStatus( 20 ); //20=Paid

							if ( $pst_obj->isValid() ) {
								$pst_obj->Save( true, true ); //When Stop Payment - ReIssues are made, we manually set the ID before its sent to payment services ID, so there is a remote_id to be used.
							} else {
								$pstlf->FailTransaction();
								Debug::Text( '  Validation failed, not sending any output...', __FILE__, __LINE__, __METHOD__, 10 );
								$output = [];
							}
						} else {
							//Ensure that all transaction are processed in the entire batch, or NONE are processed to avoid cases where only one file, or one set of transactions is paid and the user misses the failures.
							$pstlf->FailTransaction();
							Debug::Text( '  Payment failed, not sending any output...', __FILE__, __LINE__, __METHOD__, 10 );
							$output = [];
						}
					} else {
						Debug::Text( '  Found transaction that is not pending payment, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					}

					$this->getProgressBarObject()->set( null, $i );
					$i++;
					$n++;
				}
			}
		}

		//$pstlf->FailTransaction(); //ZZZ REMOVE ME! Uncomment for easier testing.
		$pstlf->CommitTransaction();

		if ( isset( $output ) ) {
			return $output;
		}

		return false;
	}


	/**
	 * @param null $pstlf
	 * @param null $company_obj
	 * @return bool
	 */
	function exportPayStubRemittanceAgencyReports( $pstlf = null, $company_obj = null, $user_obj = null ) {
		// Need to handle cases where transactions are of different types (ie: check, EFT, payment services API)
		// If the remittance agency is setup to be paid through payment services, we need to obtain the proper amount regardless of what payment method was used for individual pay stubs.
		// The proper amount would be for just the pay period/run they are processing for, and only OPEN pay stubs to avoid uploading duplicate records to the payment services API.
		//   Since this should be called as soon as the transactions themselves are processed, the pay stubs should always be OPEN.
		//   If this is run multiple times due to the user only processing one type or source account worth of transactions at a time, it shouldn't be a problem as the information will just be updated on remote payment services API end.
		// If an out-of-cycle payroll run is processed, then we need to submit a completely separate agency report to the payment services API.
		// NOTE: This can't be triggered on pay period close, as that won't work for out-of-cycle pay stubs, because we don't know which pay stubs were included in previous reports or not.
		//       Unless we only worry about impounding the tax amount on the regular in-cycle payroll run, and never out-of-cycle ones? But sometimes they might need to remit to the agency after every transaction?

		if ( !is_object( $company_obj ) ) {
			global $current_company;
			$company_obj = $current_company;
		}

		if ( !is_object( $user_obj ) ) {
			global $current_user;
			$user_obj = $current_user;
		}

		if ( is_a( $pstlf, 'PayStubTransactionListFactory' ) == false ) {
			return false;
		}

		$pstlf->StartTransaction();
		Debug::Text( 'Total Transactions: ' . $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pstlf->getRecordCount() > 0 ) {
			$pay_period_run_ids = [];
			foreach ( $pstlf as $pst_obj ) {
				//Only export remittance agency reports for pay stubs when processing their first transaction (ie: no transactions have been marked paid yet)
				//  This will mostly eliminate the chance of processing transactions for the same pay stubs in separate batches due to different pay methods
				//  causing duplicate remittance reports to be uploaded. (see comments below)
				//  Processed transactions should include paid, and stop payment (re-issue) transactions to also elminate duplicate reports when there is only one transaction for a pay stub and they have to stop payment (re-issue) for it.
				$tmp_pay_stub_total_transactions_processed = (int)$pst_obj->getColumn('pay_stub_total_transactions_processed');
				if ( $tmp_pay_stub_total_transactions_processed == 0 ) {
					//Need to break the pay stubs out by pay period/run so we can batch the agency reports by those.
					//  Actually, we need to batch these by pay stub transaction date as well, see case #4 in comment block around line #1422, around where the remote_batch_id is generated.
					//$pay_period_run_ids[$pst_obj->getColumn( 'pay_period_id' )][(int)$pst_obj->getColumn( 'pay_stub_run_id' )][] = $pst_obj->getPayStub();
					$pay_period_run_ids[(string)$pst_obj->getColumn( 'pay_period_id' )][(int)$pst_obj->getColumn( 'pay_stub_run_id' )][(int)TTDate::getMiddleDayEpoch( TTDate::strtotime( $pst_obj->getColumn( 'pay_stub_transaction_date' ) ) )][] = $pst_obj->getPayStub();
				} else {
					Debug::Text( '  Pay Stub: '. $pst_obj->getPayStub() .' Total Transactions Processed: '. $tmp_pay_stub_total_transactions_processed, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset( $ps_obj, $tmp_pay_stub_total_transactions_processed );

			Debug::Arr( $pay_period_run_ids, '  Total Pay Stub Pay Periods: ' . count( $pay_period_run_ids ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( count( $pay_period_run_ids ) > 0 ) {
				//Find all full service agency events that need to be processed.
				$praelf = TTnew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
				$praelf->getByCompanyIdAndStatus( $company_obj->getId(), 15 ); //15=Full Service
				if ( $praelf->getRecordCount() > 0 ) {
					foreach ( $praelf as $prae_obj ) { /** @var PayrollRemittanceAgencyEventFactory $prae_obj */
						$event_data = $prae_obj->getEventData();
						if ( is_array( $event_data ) && isset( $event_data['flags'] ) && $event_data['flags']['auto_pay'] == true ) {

							if ( is_object( $prae_obj->getPayrollRemittanceAgencyObject() ) ) {
								$pra_obj = $prae_obj->getPayrollRemittanceAgencyObject();

								$rs_obj = $pra_obj->getRemittanceSourceAccountObject();

								$le_obj = $rs_obj->getLegalEntityObject();

								if ( $rs_obj->getType() == 3000 && $rs_obj->getDataFormat() == 5 ) { //3000=EFT/ACH, 5=TimeTrex EFT  -- This is the remittance source account assigned the remittance agency, not the individual transactions.

									if ( is_object( $pra_obj->getContactUserObject() ) ) {
										Debug::Text( '  Agency Event: Agency: ' . $prae_obj->getPayrollRemittanceAgencyObject()->getName() . ' Legal Entity: ' . $prae_obj->getPayrollRemittanceAgencyObject()->getLegalEntity() . ' Type: ' . $prae_obj->getType() . ' Due Date: ' . TTDate::getDate( 'DATE', $prae_obj->getDueDate() ) . ' ID: ' . $prae_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										Debug::Text( '  Remittance Source: Name: ' . $rs_obj->getName() . ' API Username: ' . $rs_obj->getValue5(), __FILE__, __LINE__, __METHOD__, 10 );

										$pra_user_obj = $pra_obj->getContactUserObject();

										foreach ( $pay_period_run_ids as $pay_period_id => $run_ids ) {
											Debug::Text( '    Pay Period ID: ' . $pay_period_id . ' Total Runs: ' . count( $run_ids ), __FILE__, __LINE__, __METHOD__, 10 );

											$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
											$pplf->getByIdAndCompanyId( $pay_period_id, $company_obj->getId() );
											if ( $pplf->getRecordCount() > 0 ) {
												$pp_obj = $pplf->getCurrent();

												foreach ( $run_ids as $run_id => $pay_stub_transaction_dates ) {
													Debug::Text( '      Run ID: ' . $run_id . ' Total PS Transaction Dates: ' . count( $pay_stub_transaction_dates ), __FILE__, __LINE__, __METHOD__, 10 );

													foreach( $pay_stub_transaction_dates as $pay_stub_transaction_date => $run_pay_stub_ids ) {
														$run_pay_stub_ids = array_unique( $run_pay_stub_ids );
														Debug::Text( '      Run ID: ' . $run_id . ' PS Transaction Date: '. TTDate::getDate('DATE', $pay_stub_transaction_date) .' Total Pay Stubs: ' . count( $run_pay_stub_ids ), __FILE__, __LINE__, __METHOD__, 10 );

														$report_obj = $prae_obj->getReport( 'raw', null, $user_obj, $pra_user_obj, new Permission() );
														//$report_obj = $prae_obj->getReport( '123456', NULL, $pra_user_obj, new Permission() ); //Test with generic TaxSummaryReport

														if ( is_object( $report_obj ) ) {
															$report_data['config'] = $report_obj->getConfig();

															unset( $report_data['config']['filter']['time_period'], $report_data['config']['filter']['start_date'], $report_data['config']['filter']['end_date'] ); //Remove custom date filters and only use pay_period_run_ids.

															//Limit to just specific pay stubs that are being processed.
															//  This helps in cases where they might be handling a terminated employee a few days into the pay period,
															//  but have for some reason generated open pay stubs for all employees when the pay period is still in
															//  progress that will be regenerated with different amounts at the end of the pay period.
															//  We don't want to process a agency deposit for the other OPEN pay stubs in this case.
															//
															//  *NOTE: Previously we would limit it to just the pay_period_id, run_id, and status_id=25, which would prevent over impounding
															//         when multiple pay methods exist on a single pay stub, but would result in over impounding when OPEN pay stubs exist that are never intended to be paid yet.
															//         And also cases where they want to process payroll for a single terminated employee first, then all other employees after.
															//         The above handling of 'pay_stub_total_transactions_processed' should take care of this though.
															//
															// See more comments around line 1391 that describe the different cases that need to be handled.
															$report_data['config']['filter']['pay_stub_id'] = $run_pay_stub_ids; //Legal entity is already set in $prae_obj->getReport()

															//Get report for the entire pay period/run and only include OPEN pay stubs.
															$report_data['config']['filter']['pay_period_id'] = $pay_period_id;                                                                                     //Legal entity is already set in $prae_obj->getReport()
															$report_data['config']['filter']['pay_stub_run_id'] = $run_id;
															$report_data['config']['filter']['pay_stub_status_id'] = 25; //25=OPEN

															$report_obj->setConfig( (array)$report_data['config'] );

															$output_data = $report_obj->getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj );
															Debug::Arr( $output_data, 'Report Payment Services Data: ', __FILE__, __LINE__, __METHOD__, 10 );
															if ( is_array( $output_data ) ) {
																if ( PRODUCTION == true && is_object( $le_obj ) && $le_obj->getPaymentServicesStatus() == 10 && $le_obj->getPaymentServicesUserName() != '' && $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
																	try {
																		if ( isset( $output_data['agency_report_data'] )
																				&& isset( $output_data['agency_report_data']['amount_due'] )
																				&& $output_data['agency_report_data']['amount_due'] > 0 ) { //Skip D=Deposit where amount_due=0
																			$tt_ps_api = $le_obj->getPaymentServicesAPIObject();

																			//Force agency report to D=Deposit and set other necessary data.
																			$output_data['agency_report_data']['type_id'] = 'D';             //D=Deposit
																			$output_data['agency_report_data']['remote_batch_id'] = $tt_ps_api->generateBatchID( $pp_obj->getEndDate(), $run_id );

																			//Generate a consistent remote_id based on the exact pay stubs that are selected, the remittance agency event, and batch ID.
																			//This helps to prevent duplicate records from being created, as well as work across separate or split up batches that may be processed.
																			//  Cases to consider:
																			//    1. Where the user generates a pay stub for an employee, pays them by direct deposit through us, then puts a stop payment on that to pay by them check instead.
																			//       Essentially the same agency report will be submitted twice with two completely different sets of data.
																			//    2. Where the user generates say 20 pay stubs (all open) for the current pay period that is only a few days into it, then terminates an employee and processes payment for just that one employee.
																			//       In this case the agency report would erroneously include all open (not yet completed) pay stubs, and the terminated one.
																			//    3. Where the user processes transactions for just one remittance source account/type, then does another remittance source account/type in a seprate batch. Both of which affect the same pay stubs.
																			//       The above 'pay_stub_total_transactions_processed' should handle this properly.
																			//    4. Where the user processes transactions for a out-of-cycle payroll run (ie: terminated employee), that has a *pay period* transaction date in the next month (ie: Oct), but a *pay stub* transaction date in the current month (ie: Sep)
																			//       When the transaction is processed, a agency deposit would be made and associated with Oct (PP transaction date), but when the user goes to submit a payment to the Gov. (ie: CRA),
																			//         PaymentServices will think there is a shortfall on the balance since the agency report (amount to pay) will include the out-of-cycle pay stub (pay stub transaction date in Sep), but the impound balance has that associated with Oct.
																			//
																			//I think the only sure fire way to properly handle the above cases is just prevent exact duplicates from being uploaded where the pay stub IDs are exactly the same. So add $run_pay_stub_ids into the 'remote_id'
																			//  If the user processes payment for 4 random pay stubs, then 6 other random ones, then again for all 10, it will technically could have multiple agency reports that cover the same pay stubs.
																			//  But the balance could just be returned to them once the payment report is sent at the end of the period (ie: month) and fully reconciled.
																			$output_data['agency_report_data']['remote_id'] = TTUUID::convertStringToUUID( md5( $prae_obj->getId() .':'. $output_data['agency_report_data']['remote_batch_id'] .':'. $pay_period_id .':'. $run_id .':'. implode( '', $run_pay_stub_ids ) ) );
																			$output_data['agency_report_data']['pay_period_start_date'] = TTDate::getISODateStamp( $pp_obj->getStartDate() );
																			$output_data['agency_report_data']['pay_period_end_date'] = TTDate::getISODateStamp( $pp_obj->getEndDate() );
																			//$output_data['agency_report_data']['pay_period_transaction_date'] = TTDate::getISODateStamp( $pp_obj->getTransactionDate() );
																			$output_data['agency_report_data']['pay_period_transaction_date'] = TTDate::getISODateStamp( $pay_stub_transaction_date ); //Must use pay stub transaction date, as the pay period transaction date could be Oct 1st, when the pay stub transaction date is Sept 30th, causing the remittance deposit and payment reports (if monthly) to mismatch and be in two different months.
																			$output_data['agency_report_data']['pay_period_run'] = $run_id;

																			//Check to see if transaction date is outside of the current agency event start/end period.
																			// If so then we want to use date from the next period, or from the period calculated from the transaction date?
																			//    The case here is if a customer doesn't process an agency event for a while (ie: business is seasonal and shuts down over winter),
																			//    then starts up again, the event could be months behind and PaymentServicesAPI would reject uploading the tax impound data if we just use the next period.
																			if ( TTDate::getMiddleDayEpoch( $pay_stub_transaction_date ) > TTDate::getMiddleDayEpoch( $prae_obj->getEndDate() ) ) {
																				$event_next_dates = $prae_obj->calculateNextDate( $prae_obj->getDueDate() ); //Next Period
																				//$event_next_dates = $prae_obj->calculateNextDate( $pay_stub_transaction_date ); //Period for current pay stub transaction date. Not sure if we can jump periods like this. So don't use right now.
																				if ( is_array( $event_next_dates ) ) {
																					$output_data['agency_report_data']['period_start_date'] = TTDate::getISODateStamp( $event_next_dates['start_date'] );
																					$output_data['agency_report_data']['period_end_date'] = TTDate::getISODateStamp( $event_next_dates['end_date'] );
																					$output_data['agency_report_data']['due_date'] = TTDate::getISOTimeStamp( $event_next_dates['due_date'] );
																				}
																				unset( $event_next_dates );
																			}

																			$agency_report_arr = $tt_ps_api->convertReportPaymentServicesDataToAgencyReportArray( $output_data, $prae_obj, $pra_obj, $rs_obj, $pra_user_obj );

																			$retval = $tt_ps_api->setAgencyReport( $agency_report_arr );

																			Debug::Arr( $retval, 'TimeTrexPaymentServices Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
																			if ( $retval->isValid() == true ) {
																				Debug::Text( 'Upload successful!', __FILE__, __LINE__, __METHOD__, 10 );
																			} else {
																				Debug::Arr( $retval, 'ERROR! Unable to upload agency report data... ', __FILE__, __LINE__, __METHOD__, 10 );

																				//No point in failing the transaction, as there isn't any easy way to re-trigger this right now. Its also after the transactions have all been uploaded too.
																				//$pstlf->FailTransaction();
																				//return FALSE;

																				if ( $retval->getDetailsDescription() != '' ) {
																					$result_message = $retval->getDetailsDescription();
																				} else {
																					$result_message = $retval->getDescription();
																				}

																				//Trigger a notification instead of failing the transaction.
																				$notification_data = [
																						'object_id'      => $prae_obj->getId(),
																						'user_id'        => $prae_obj->getReminderUser(), //Send to just the reminder user, or the agency contact too?
																						'priority_id'    => 1, //Critical
																						'type_id'        => 'payment_services',
																						'object_type_id' => 100,
																						'title_short'    => TTi18n::getText( 'URGENT: Payment Services failure!' ),
																						'body_short'     => TTi18n::getText( 'Please contact support immediately. Unable to process Payment Services Remittance Agency Event due to the following error(s): %1', [ $result_message ] ),
																				];

																				Notification::sendNotification( $notification_data );
																				unset( $result_message, $notification_date );
																			}
																			unset( $batch_id, $remote_id );
																		} else {
																			Debug::Text( 'NOTICE: Amount due is $0, not submitting a deposit record...', __FILE__, __LINE__, __METHOD__, 10 );
																		}
																	} catch ( Exception $e ) {
																		Debug::Text( 'ERROR! Unable to upload agency report data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
																	}
																} else {
																	Debug::Text( 'WARNING: Production is off, not calling payment services API...', __FILE__, __LINE__, __METHOD__, 10 );
																}
															} else {
																Debug::Arr( $output_data, 'Report returned unexpected number of rows, not transmitting...', __FILE__, __LINE__, __METHOD__, 10 );
															}
														} else {
															Debug::Text( '  ERROR! Report object was not returned, likely a validation failure, or permission are invalid!', __FILE__, __LINE__, __METHOD__, 10 );
															$pstlf->FailTransaction();
															return false;
														}
													}
												}
											} else {
												Debug::Text( '  ERROR! Pay Period does not exist!', __FILE__, __LINE__, __METHOD__, 10 );
											}
										}
									} else {
										Debug::Text( '  ERROR! Contact user assign to agency is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
									}
								} else {
									Debug::Text( '  ERROR! Remittance Source Account is not EFT or TimeTrex Payment Services!', __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::Text( '  ERROR! Remittance Agency Object is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							Debug::Text( '  Skipping non-auto pay events...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				} else {
					Debug::Text( '  No full service remittance agency events!', __FILE__, __LINE__, __METHOD__, 10 );
					//This must return TRUE, as its not a full service payroll customer.
				}
			} else {
				Debug::Text( '  No pay stubs to process! Are the transactions paid or stop payment (re-issue) perhaps?', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		$pstlf->CommitTransaction();

		Debug::Text( 'Done.', __FILE__, __LINE__, __METHOD__, 10 );

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
						case 'currency_id':
							//should never set currency id manually as it comes from the source account.
							//currency is automatically set from setRemittanceDestination()
							break;
						case 'transaction_date':
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
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];

		$variable_function_map = $this->getVariableToFunctionMap();
		$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'remittance_source_account_type':
							$data[$variable] = Option::getByKey( $this->getColumn( $variable ), $rsaf->getOptions( 'type' ) );
							break;
						case 'status':
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'currency_id':
						case 'currency':
						case 'currency_rate':
						case 'user_id':
						case 'pay_period_id':
						case 'destination_user_first_name':
						case 'destination_user_last_name':
						case 'remittance_source_account_last_transaction_number':
						case 'remittance_destination_account':
						case 'remittance_source_account':
						case 'pay_stub_run_id':

						case 'transaction_number':

						case 'pay_period_end_date':
						case 'pay_period_start_date':
						case 'pay_period_transaction_date':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'pay_stub_end_date':
						case 'pay_stub_start_date':
						case 'pay_stub_transaction_date':
							//strtotime is needed as the dates are stored as timestamps not epochs.
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( $variable ) ) );
							break;
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Stub Transaction' ), null, $this->getTable(), $this );
	}

}

?>
