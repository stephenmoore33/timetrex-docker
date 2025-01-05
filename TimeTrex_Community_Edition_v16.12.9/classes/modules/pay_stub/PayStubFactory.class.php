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
 * @package Modules\PayStub
 */
class PayStubFactory extends Factory {
	protected $table = 'pay_stub';
	protected $pk_sequence_name = 'pay_stub_id_seq'; //PK Sequence name

	public $tmp_data = [ 'previous_pay_stub' => null, 'current_pay_stub' => null ];
	protected $is_unique_pay_stub = null;
	protected $is_unique_pay_stub_type = null;

	protected $pay_period_obj = null;
	protected $currency_obj = null;
	protected $user_obj = null;
	protected $pay_stub_entry_account_link_obj = null;

	protected $pay_stub_entry_accounts_obj = null;
	protected $old_currency_id = null;

	public $validate_only = false; //Used by the API to ignore certain validation checks if we are doing validation only.

	/**
	 * @var bool
	 */
	private $linked_accruals;
	/**
	 * @var bool
	 */
	private $enable_notification;
	/**
	 * @var bool
	 */
	private $is_recalc_ytd;
	/**
	 * @var bool
	 */
	private $calc_current_ytd;
	/**
	 * @var bool
	 */
	private $calc_ytd;
	/**
	 * @var bool
	 */
	private $sync_pending_pay_stub_transaction_dates;
	/**
	 * @var bool
	 */
	private $process_transactions;
	/**
	 * @var bool
	 */
	private $process_entries;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_period_id' )->setFunctionMap( 'PayPeriod' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status_date' )->setFunctionMap( 'StatusDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'status_by' )->setFunctionMap( 'StatusBy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'end_date' )->setFunctionMap( 'EndDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'transaction_date' )->setFunctionMap( 'TransactionDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'tainted' )->setFunctionMap( 'Tainted' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'temp' )->setFunctionMap( 'Temp' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'currency_rate' )->setFunctionMap( 'CurrencyRate' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'run_id' )->setFunctionMap( 'Run' )->setType( 'smallint' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_pay_stub' )->setLabel( TTi18n::getText( 'Pay Stub' ) )->setFields(
									new TTSFields(
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIPayStub' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPayStub' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'currency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APICurrency' )->setMethod( 'getCurrency' ) ),
											TTSField::new( 'pay_period_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Pay Period' ) )->setDataSource( TTSAPI::new( 'APIPayPeriod' )->setMethod( 'getPayPeriod' ) ),
											TTSField::new( 'run_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Payroll Run' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Pay Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Pay End Date' ) ),
											TTSField::new( 'transaction_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Payment Date' ) )
									)
							)->setHTMLTemplate( '<div id="tab_pay_stub" class="edit-view-tab-outside">
                                <div class="edit-view-tab" id="tab_pay_stub_content_div">
                                    <div class="first-column"></div>
                                    <div class="second-column"></div>
                                    <div class="inside-pay-stub-entry-editor-div full-width-column" style="float: left; position: relative">
                                    </div>
                                </div>
                            </div>' )
									->setInitCallback( 'initSubLogView' )
									->setDisplayOnMassEdit( false )

					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'include_user_id' )->setType( 'uuid' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'uuid' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'user_status_id' )->setType( 'numeric_list' )->setColumn( 'b.status_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'user_group_id' )->setType( 'uuid' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'first_name' )->setType( 'text_metaphone' )->setColumn( 'b.first_name' ),
							TTSSearchField::new( 'last_name' )->setType( 'text_metaphone' )->setColumn( 'b.last_name' ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'user_title_id' )->setType( 'uuid' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'sex_id' )->setType( 'numeric_list' )->setColumn( 'b.sex_id' )->setMulti( true ),
							TTSSearchField::new( 'currency_id' )->setType( 'uuid' )->setColumn( 'b.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_currency_id' )->setType( 'uuid' )->setColumn( 'a.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid' )->setColumn( 'a.pay_period_id' )->setMulti( true ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' ),
							TTSSearchField::new( 'city' )->setType( 'text' )->setColumn( 'b.city' ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'run_id' )->setType( 'smallint_list' )->setColumn( 'a.run_id' )->setMulti( true ),
							TTSSearchField::new( 'start_date' )->setType( 'timestamp' )->setColumn( 'a.transaction_date' ),
							TTSSearchField::new( 'end_date' )->setType( 'timestamp' )->setColumn( 'a.transaction_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayStub' )->setMethod( 'getPayStub' )
									->setSummary( 'Get pay stub records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayStub' )->setMethod( 'setPayStub' )
									->setSummary( 'Add or edit pay stub records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayStub' )->setMethod( 'deletePayStub' )
									->setSummary( 'Delete pay stub records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayStub' )->setMethod( 'getPayStub' ) ),
											   ) ),
							TTSAPI::new( 'APIPayStub' )->setMethod( 'getPayStubDefaultData' )
									->setSummary( 'Get default pay stub data used for creating new pay stubs. Use this before calling setPayStub to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'filtered_status':
				$retval = Option::getByArray( [ 25, 40, 100 ], $this->getOptions( 'status' ) );
				break;
			case 'status':
				$retval = [
						10  => TTi18n::gettext( 'NEW' ),
						20  => TTi18n::gettext( 'LOCKED' ),
						25  => TTi18n::gettext( 'Open' ),
						//25 => TTi18n::gettext('Pending Payment'), //At least one transaction still pending
						40  => TTi18n::gettext( 'Paid' ), //Change this to mean Paid (all transactions paid), but not yet visible to employees?
						//50 => TTi18n::gettext('Complete'), //Paid and visible to employees. Also sends out emails. Closing the pay period changes to this state.
						//100 => TTi18n::gettext( 'Opening Balance (YTD)' ), //Switched to TYPE instead.
				];
				break;
			case 'type':
				$retval = [
						10  => TTi18n::gettext( 'Normal (In-Cycle)' ),
						20  => TTi18n::gettext( 'Bonus/Correction (Out-of-Cycle)' ),
						90  => TTi18n::gettext( 'Year-to-Date (YTD) Adjustment' ), //Not visible to employees, pay stubs must have a $0 net pay(?). Only includes pay stub amendments like Opening Balance, no Tax/Deductions.
						100 => TTi18n::gettext( 'Opening Balance (YTD)' ),
				];
				break;
			case 'payroll_run_type':
				$retval = [
						10 => TTi18n::gettext( 'Normal (In-Cycle)' ), //In-Cycle
						20 => TTi18n::gettext( 'Bonus/Correction (Out-of-Cycle)' ), //Out-of-Cycle
						90 => TTi18n::gettext( 'Year-to-Date (YTD) Adjustment' ), //Not visible to employees, pay stubs must have a $0 net pay(?). Only includes pay stub amendments like Opening Balance, no Tax/Deductions.
				];

				//$param should be the pay_period status_id.
				if ( is_array( $params ) && count( array_unique( $params ) ) == 1 && end( $params ) == 30 ) {
					$retval[5] = TTi18n::gettext( 'Post-Adjustment Carry-Forward' ); //Just generate PSA's in the next pay period.
					ksort( $retval );
				}


				$show_opening_balance = false;

				//How to determine if Opening Balance pay stubs still need to be generated?
				//  Check to see if any normal pay stub is marked paid at anytime, if it is, then Opening Balances can't be generated.
				//  **How do we handle if a customer merges with another company, and needs to import YTD for another group of employees? Put the first pay period into post-adjustment status.
				global $current_company;
				if ( is_object( $current_company ) ) {
					$pslf = TTnew('PayStubListFactory');
					$pslf->getByCompanyIdAndStatusIdAndTypeId( $current_company->getId(), 40, [ 10, 20 ], 1 ); //40=Paid
					Debug::Text( '  Checking for paid, non-opening balance pay stubs to enable Opening Balance type... Found: '. $pslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $pslf->getRecordCount() == 0 ) {
						$show_opening_balance = true;
					}
					unset( $pslf );

					if ( $show_opening_balance == false ) {
						//Handle the case where opening balance pay stubs might need to be re-generated due to an error.
						//  For that, check if the first pay period is open or in post-adjustment status, and allow opening balance pay stubs to be generated.
						$pplf = TTnew( 'PayPeriodListFactory' );
						$pplf->getByCompanyId( $current_company->getId(), 1, null, null, [ 'transaction_date' => 'asc' ] );
						if ( $pplf->getRecordCount() == 1 ) {
							$first_pp_obj = $pplf->getCurrent();
							Debug::Text( '  First pay period status: '. $first_pp_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );
							if ( $first_pp_obj->getStatus() == 10 || $first_pp_obj->getStatus() == 30 ) {
								$show_opening_balance = true;
							}
						}
						unset( $pplf, $first_pp_obj );
					}
				}

				if ( $show_opening_balance == true ) {
					$retval[100] = TTi18n::gettext( 'Opening Balance (YTD)' ); //Disables all Tax/Deductions and only uses pay stub amendments.
				}
				break;
			case 'export_general_ledger':
				$retval = [
						'-2010-export_csv'      => TTi18n::gettext( 'Excel (CSV)' ),
						'-2011-export_csv_flat' => TTi18n::gettext( 'Excel (CSV) [Flat]' ),
						'-2020-quickbooks'      => TTi18n::gettext( 'Quickbooks GL' ),
						'-2030-simply'          => TTi18n::gettext( 'Sage 50 GL' ), //Was Simply Accounting
						'-2040-sage300'         => TTi18n::gettext( 'Sage 300 GL' ),
						'-2050-xero'            => TTi18n::gettext( 'Xero Manual Journal' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						'-1005-user_status'        => TTi18n::gettext( 'Employee Status' ),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1020-user_group'         => TTi18n::gettext( 'Group' ),
						'-1030-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1040-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1050-city'               => TTi18n::gettext( 'City' ),
						'-1060-province'           => TTi18n::gettext( 'Province/State' ),
						'-1070-country'            => TTi18n::gettext( 'Country' ),
						'-1080-currency'           => TTi18n::gettext( 'Currency' ),
						//'-1080-pay_period' => TTi18n::gettext('Pay Period'),

						'-1140-status'           => TTi18n::gettext( 'Status' ),
						'-1150-type'             => TTi18n::gettext( 'Type' ),
						'-1170-start_date'       => TTi18n::gettext( 'Start Date' ),
						'-1180-end_date'         => TTi18n::gettext( 'End Date' ),
						'-1190-transaction_date' => TTi18n::gettext( 'Transaction Date' ),
						'-1200-run_id'           => TTi18n::gettext( 'Payroll Run' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$columns = [ 'status', 'start_date', 'end_date', 'transaction_date', 'run_id', 'type' ];
				$retval = Misc::arrayIntersectByKey( $columns, Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'status',
						'start_date',
						'end_date',
						'transaction_date',
						'run_id',
						'type',
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
				'id'      => 'ID',
				'user_id' => 'User',

				'first_name'            => false,
				'last_name'             => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'user_group'            => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,
				'city'                  => false,
				'province'              => false,
				'country'               => false,

				'pay_period_id'    => 'PayPeriod',
				'type_id'          => 'Type',
				'type'             => false,
				'run_id'           => 'Run',
				//'pay_period' => FALSE,
				'currency_id'      => 'Currency',
				'currency'         => false,
				'currency_rate'    => 'CurrencyRate',
				'start_date'       => 'StartDate',
				'end_date'         => 'EndDate',
				'transaction_date' => 'TransactionDate',
				'status_id'        => 'Status',
				'status'           => false,
				'status_date'      => 'StatusDate',
				'status_by'        => 'StatusBy',
				'tainted'          => 'Tainted',
				'temp'             => 'Temp',
				'deleted'          => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|PayPeriodObject
	 */
	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	/**
	 * @return bool|CurrencyFactory
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool|UserFactory
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
	 * @param string $id UUID
	 * @return bool
	 */
	function setUser( $id ) {
		$id = TTUUID::castUUID( $id );

		return $this->setGenericDataValue( 'user_id', $id );
	}

	/**
	 * @return bool|mixed
	 */
	function getDisplayID() {
		if ( TTUUID::isUUID( $this->getId() ) ) {
			return strtoupper( TTUUID::truncateUUID( $this->getId(), 12, false ) );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setPayPeriod( $id ) {
		$id = TTUUID::castUUID( $id );

		return $this->setGenericDataValue( 'pay_period_id', $id );
	}

	/**
	 * @return int
	 */
	function getRun() {
		if ( $this->getGenericDataValue( 'run_id' ) !== false ) {
			return (int)$this->getGenericDataValue( 'run_id' );
		}

		return 1; //Always default to 1 if its not set otherwise.
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setRun( $id ) {
		$id = trim( $id );

		return $this->setGenericDataValue( 'run_id', $id );
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
		$value = trim( $value );

		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		$this->old_currency_id = $this->getCurrency();

		return $this->setGenericDataValue( 'currency_id', $value );
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

		return $this->setGenericDataValue( 'currency_rate', $value );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidStartDate( $epoch ) {
		if ( is_object( $this->getPayPeriodObject() ) &&
				( $epoch >= $this->getPayPeriodObject()->getStartDate() && $epoch < $this->getPayPeriodObject()->getEndDate() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods start at the first second of the day.
			$value = TTDate::getTimeLockedDate( strtotime( '00:00:00', $value ), $value );
		}

		return $this->setGenericDataValue( 'start_date', TTDate::getDBTimeStamp( $value, false ) );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidEndDate( $epoch ) {
		//Allow a 59 second grace period around the pay period end date, due to seconds being stripped in some cases.
		if ( is_object( $this->getPayPeriodObject() ) &&
				( $epoch <= ( $this->getPayPeriodObject()->getEndDate() + 59 ) && $epoch >= $this->getPayPeriodObject()->getStartDate() ) ) {
			return true;
		} else if ( is_object( $this->getPayPeriodObject() ) == false ) {
			//In cases where mass editing pay stubs and changing just the end date or transaction date, if the pay period dropdown box is not checked to be mass edited as well,
			//  then there won't be a pay period object, and it will always cause a validation error. This confuses users, so just assume if no pay period object exists the date is correct.
			return true;
		}

		return false;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//In cases where you set the date, then immediately read it again, it will return -1 unless do this.
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods end at the last second of the day.
			$value = TTDate::getTimeLockedDate( strtotime( '23:59:59', $value ), $value );
		}

		return $this->setGenericDataValue( 'end_date', TTDate::getDBTimeStamp( $value, false ) );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidTransactionDate( $epoch ) {
		Debug::Text( 'Epoch: ' . $epoch . ' ( ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' ) Pay Stub End Date: ' . TTDate::getDate( 'DATE+TIME', $this->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $epoch >= $this->getEndDate() ) {
			return true;
		}

		return false;
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
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setTransactionDate( $epoch ) {
		$epoch = ( !is_int( $epoch ) ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch != '' ) {
			//Make sure all pay periods transact at noon.
			$epoch = TTDate::getTimeLockedDate( strtotime( '12:00:00', $epoch ), $epoch );

			//Unless they are on the same date as the end date, then it should match that.
			if ( $this->getEndDate() != '' && $this->getEndDate() > $epoch ) {
				$epoch = $this->getEndDate();
			}
		}

		return $this->setGenericDataValue( 'transaction_date', TTDate::getDBTimeStamp( $epoch, false ) );
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
		$this->setStatusDate();
		$this->setStatusBy();

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStatusDate() {
		return $this->getGenericDataValue( 'status_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStatusDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'status_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStatusBy() {
		return $this->getGenericDataValue( 'status_by' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setStatusBy( $value = null ) {
		$value = trim( (string)$value );
		if ( empty( $value ) ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$value = $current_user->getID();
			} else {
				return false;
			}
		}

		return $this->setGenericDataValue( 'status_by', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $id
	 * @return bool
	 */
	function setType( $id ) {
		$id = (int)trim( $id );

		return $this->setGenericDataValue( 'type_id', $id );
	}

	/**
	 * @return bool
	 */
	function getTainted() {
		return $this->fromBool( $this->getGenericDataValue( 'tainted' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTainted( $value ) {
		return $this->setGenericDataValue( 'tainted', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getTemp() {
		return $this->fromBool( $this->getGenericDataValue( 'temp' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTemp( $value ) {
		return $this->setGenericDataValue( 'temp', $this->toBool( $value ) );
	}

	/**
	 * @return bool|null
	 */
	function isUniquePayStub() {
		if ( $this->getTemp() == true ) {
			return true;
		}

		if ( $this->is_unique_pay_stub === null ) {
			$ph = [
					'user_id'       => TTUUID::castUUID( $this->getUser() ),

					'pay_period_id' => TTUUID::castUUID( $this->getPayPeriod() ),
					'run_id'        => (int)$this->castInteger( (int)$this->getRun(), 'smallint' ),

					'transaction_date' => $this->db->BindTimeStamp( (int)$this->getTransactionDate() ),
					'run_id_b'        => (int)$this->castInteger( (int)$this->getRun(), 'smallint' ),
			];

			//Make sure a pay stub does not exist in the same pay period and run_id, as well as the same transaction_date and run_id, even if its in different pay periods.
			//  As getLastPayStubByUserIdAndStartDateAndRun() can't tell the proper order the pay stubs should occur within for YTD purposes if either of cases were to occur.
			$query = 'select id from ' . $this->getTable() . ' where user_id = ? AND ( ( pay_period_id = ? AND run_id = ? ) OR ( transaction_date = ? AND run_id = ? ) ) AND deleted = 0';
			$pay_stub_id = $this->db->GetOne( $query, $ph );

			if ( $pay_stub_id === false ) {
				$this->is_unique_pay_stub = true;
			} else {
				if ( $pay_stub_id == $this->getId() ) {
					$this->is_unique_pay_stub = true;
				} else {
					$this->is_unique_pay_stub = false;
				}
			}
		}

		return $this->is_unique_pay_stub;
	}

	/**
	 * @return bool|null
	 */
	function isUniquePayStubType() {
		//Only 10=Regular (In-Cycle) types are unique.
		if ( $this->getType() == 20 || $this->getTemp() == true ) {
			return true;
		}

		if ( $this->is_unique_pay_stub_type === null ) {
			$ph = [
					'pay_period_id' => TTUUID::castUUID( $this->getPayPeriod() ),
					'user_id'       => TTUUID::castUUID( $this->getUser() ),
					'type_id'       => (int)$this->getType(),
			];

			$query = 'select id from ' . $this->getTable() . ' where pay_period_id = ? AND user_id = ? AND type_id = ? AND deleted = 0';
			$pay_stub_id = $this->db->GetOne( $query, $ph );

			if ( $pay_stub_id === false ) {
				$this->is_unique_pay_stub_type = true;
			} else {
				if ( $pay_stub_id == $this->getId() ) {
					$this->is_unique_pay_stub_type = true;
				} else {
					$this->is_unique_pay_stub_type = false;
				}
			}
		}

		return $this->is_unique_pay_stub_type;
	}

	/**
	 * @return bool
	 */
	function setDefaultDates() {
		$start_date = $this->getPayPeriodObject()->getStartDate();
		$end_date = $this->getPayPeriodObject()->getEndDate();
		$transaction_date = $this->getPayPeriodObject()->getTransactionDate();

		Debug::Text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->setStartDate( $start_date );
		$this->setEndDate( $end_date );
		$this->setTransactionDate( $transaction_date );

		Debug::Text( 'Transaction Date: Before: ' . TTDate::getDate( 'DATE+TIME', $transaction_date ) . ' After: ' . TTDate::getDate( 'DATE+TIME', $this->getTransactionDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableProcessEntries() {
		if ( isset( $this->process_entries ) ) {
			return $this->process_entries;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableProcessEntries( $bool ) {
		$this->process_entries = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableProcessTransactions() {
		if ( isset( $this->process_transactions ) ) {
			return $this->process_transactions;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableProcessTransactions( $bool ) {
		$this->process_transactions = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableSyncPendingPayStubTransactionDates() {
		if ( isset( $this->sync_pending_pay_stub_transaction_dates ) ) {
			return $this->sync_pending_pay_stub_transaction_dates;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSyncPendingPayStubTransactionDates( $bool ) {
		$this->sync_pending_pay_stub_transaction_dates = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcYTD() {
		if ( isset( $this->calc_ytd ) ) {
			return $this->calc_ytd;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcYTD( $bool ) {
		$this->calc_ytd = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcCurrentYTD() {
		if ( isset( $this->calc_current_ytd ) ) {
			return $this->calc_current_ytd;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcCurrentYTD( $bool ) {
		$this->calc_current_ytd = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getIsReCalculatingYTD() {
		if ( isset( $this->is_recalc_ytd ) ) {
			return $this->is_recalc_ytd;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setIsReCalculatingYTD( $bool ) {
		$this->is_recalc_ytd = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableNotification() {
		if ( isset( $this->enable_notification ) ) {
			return $this->enable_notification;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableNotification( $bool ) {
		$this->enable_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableLinkedAccruals() {
		if ( isset( $this->linked_accruals ) ) {
			return $this->linked_accruals;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableLinkedAccruals( $bool ) {
		$this->linked_accruals = (bool)$bool;

		return true;
	}

	/**
	 * @param string $pay_stub_id1     UUID
	 * @param string $pay_stub_id2     UUID
	 * @param int $pay_stub_2_end_date EPOCH
	 * @param int $ps_amendment_date   EPOCH
	 * @return bool
	 */
	static function CalcDifferences( $pay_stub_id1, $pay_stub_id2, $pay_stub_2_end_date, $ps_amendment_date = null ) {
		$pay_stub_id1 = TTUUID::castUUID( $pay_stub_id1 );
		$pay_stub_id2 = TTUUID::castUUID( $pay_stub_id2 );

		//Allow passing blank/null old pay stub, so we can handle cases where an employee wasn't paid at all, but we need to carry-forward the transaction still.

		//PayStub 1 is new.
		//PayStub 2 is old.
		if ( $pay_stub_id1 == TTUUID::getZeroID() ) {
			return false;
		}

		if ( $pay_stub_id1 == $pay_stub_id2 ) {
			return false;
		}

		Debug::Text( 'Calculating the differences between Pay Stub: ' . $pay_stub_id1 . ' and: ' . $pay_stub_id2, __FILE__, __LINE__, __METHOD__, 10 );

		$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */

		$pslf->StartTransaction();

		$pslf_a = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf_a */
		$pslf_a->getById( $pay_stub_id1 );
		if ( $pslf_a->getRecordCount() > 0 ) {
			$pay_stub1_obj = $pslf_a->getCurrent();
		} else {
			Debug::Text( 'Pay Stub1 does not exist: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		unset( $pslf_a );

		$pslf_b = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf_b */
		$pslf_b->getById( $pay_stub_id2 );
		if ( $pslf_b->getRecordCount() > 0 ) {
			$pay_stub2_obj = $pslf_b->getCurrent();
		} else {
			Debug::Text( 'Pay Stub2 does not exist: ', __FILE__, __LINE__, __METHOD__, 10 );
		}
		unset( $pslf_b );

		if ( isset( $pay_stub2_obj ) && $pay_stub1_obj->getUser() != $pay_stub2_obj->getUser() ) {
			Debug::Text( 'Pay Stubs are from different users!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $ps_amendment_date == null || $ps_amendment_date == '' ) {
			Debug::Text( 'PS Amendment Date not set, trying to figure it out!', __FILE__, __LINE__, __METHOD__, 10 );
			//Take a guess at the end of the newest open pay period.
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByUserId( $pay_stub1_obj->getUser() );
			if ( $ppslf->getRecordCount() > 0 ) {
				Debug::Text( 'Found Pay Period Schedule, ID: ' . $ppslf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$pplf->getByPayPeriodScheduleIdAndTransactionDate( $ppslf->getCurrent()->getId(), time(), null, [ 'a.transaction_date' => 'DESC' ] );
				if ( $pplf->getRecordCount() > 0 ) {
					Debug::Text( 'Using Pay Period End Date.', __FILE__, __LINE__, __METHOD__, 10 );
					$ps_amendment_date = TTDate::getBeginDayEpoch( $pplf->getCurrent()->getEndDate() );
				}
			} else {
				Debug::Text( 'Using Today.', __FILE__, __LINE__, __METHOD__, 10 );
				$ps_amendment_date = time();
			}
		}
		Debug::Text( 'Using Date: ' . TTDate::getDate( 'DATE+TIME', $ps_amendment_date ), __FILE__, __LINE__, __METHOD__, 10 );

		//Only do Earnings for now.
		//Get all earnings, EE/ER deduction PS entries.
		$pay_stub1_entry_ids = [];
		$pay_stub1_entries = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pay_stub1_entries */
		$pay_stub1_entries->getByPayStubIdAndType( $pay_stub1_obj->getId(), [ 10, 20, 30 ] );
		if ( $pay_stub1_entries->getRecordCount() > 0 ) {
			Debug::Text( 'Pay Stub1 Entries DO exist: ', __FILE__, __LINE__, __METHOD__, 10 );

			foreach ( $pay_stub1_entries as $pay_stub1_entry_obj ) {
				$pay_stub1_entry_ids[] = $pay_stub1_entry_obj->getPayStubEntryNameId();
			}
		} else {
			Debug::Text( 'Pay Stub1 Entries does not exist: ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Arr( $pay_stub1_entry_ids, 'Pay Stub1 Entry IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pay_stub2_entry_ids = [];
		if ( isset( $pay_stub2_obj ) ) {
			$pay_stub2_entries = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pay_stub2_entries */
			$pay_stub2_entries->getByPayStubIdAndType( $pay_stub2_obj->getId(), [ 10, 20, 30 ] );
			if ( $pay_stub2_entries->getRecordCount() > 0 ) {
				Debug::Text( 'Pay Stub2 Entries DO exist: ', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $pay_stub2_entries as $pay_stub2_entry_obj ) {
					$pay_stub2_entry_ids[] = $pay_stub2_entry_obj->getPayStubEntryNameId();
				}
			} else {
				Debug::Text( 'Pay Stub2 Entries does not exist: ', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}
		Debug::Arr( $pay_stub2_entry_ids, 'Pay Stub2 Entry IDs: ', __FILE__, __LINE__, __METHOD__, 10 );


		$pay_stub_entry_ids = array_unique( array_merge( $pay_stub1_entry_ids, $pay_stub2_entry_ids ) );
		Debug::Arr( $pay_stub_entry_ids, 'Pay Stub Entry Differences: ', __FILE__, __LINE__, __METHOD__, 10 );
		//var_dump($pay_stub_entry_ids);

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		if ( count( $pay_stub_entry_ids ) > 0 ) {
			foreach ( $pay_stub_entry_ids as $pay_stub_entry_id ) {
				$pay_stub1_entry_arr = $pself->getSumByPayStubIdAndEntryNameIdAndNotPSAmendment( $pay_stub1_obj->getId(), $pay_stub_entry_id );

				if ( isset( $pay_stub2_obj ) ) {
					$pay_stub2_entry_arr = $pself->getSumByPayStubIdAndEntryNameIdAndNotPSAmendment( $pay_stub2_obj->getId(), $pay_stub_entry_id );
				} else {
					$pay_stub2_entry_arr = [ 'amount' => 0, 'units' => 0 ];
				}
				Debug::Text( '  Entry ID: ' . $pay_stub_entry_id .' Pay Stub1: Amount: ' . $pay_stub1_entry_arr['amount'] . ' Units: '. $pay_stub1_entry_arr['units'] .' Pay Stub2: Amount: ' . $pay_stub2_entry_arr['amount'] .' Units: '. $pay_stub2_entry_arr['units'], __FILE__, __LINE__, __METHOD__, 10 );

				if ( $pay_stub1_entry_arr['amount'] != $pay_stub2_entry_arr['amount'] ) {
					//Generate PS Amendment.
					$psaf = TTnew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
					$psaf->setUser( $pay_stub1_obj->getUser() );
					$psaf->setStatus( 50 ); //Active
					$psaf->setType( 10 );
					$psaf->setPayStubEntryNameId( $pay_stub_entry_id );

					$units_diff = abs( TTMath::sub( $pay_stub1_entry_arr['units'], $pay_stub2_entry_arr['units'], 4 ) );                                                                                                                                                                                    //Allow units to be up to 4 decimal places, especially important for customers who don't round punches, as this could result in a slightly different amount than expected, especially if the rate is auto calculated below.
					$amount_diff = TTMath::MoneyRound( TTMath::sub( $pay_stub1_entry_arr['amount'], $pay_stub2_entry_arr['amount'], 4 ), 2, ( ( is_object( $psaf->getUserObject() ) && is_object( $psaf->getUserObject()->getCurrencyObject() ) ) ? $psaf->getUserObject()->getCurrencyObject() : null ) ); //Set MIN decimals to 2 and max to the currency rounding.
					Debug::Text( '    aFOUND DIFFERENCE of: Amount: ' . $amount_diff . ' Units: ' . $units_diff, __FILE__, __LINE__, __METHOD__, 10 );

					//Try to avoid showing units/rate when the units difference is less than 0.0166 = 1min.
					//  This can happen when a wage change occurs in the pay period so the previous pay stub had Units: 51.2667 and the new pay stub has two line items of Units: 34.0833 + 17.1833 = 51.2666
					//  There could even be 3 or more line items, making the difference 0.0003+ too.
					//  Unfortunately there no way around this, so instead just set some threshold value that when under we ignore units on pay stub amendments.
					if ( $units_diff > 0.0166 ) {
						//Re-calculate amount when units are involved, due to rounding issues.
						//FIXME: However in the case of salaried employees, where there were no units previously, or no units after,
						//don't use unit calculation to get the amount, just use the amount directly, as it could be different than what they expect.
						// For example a salaried employee doesn't get paid in a previous PP, the before pay stub doesn't exist, but the new pay stub
						// could have 42.5 units at an amont of 254.80 (but no rate specified).
						// However 254.80 / 42.50 = 5.995, which rounds to 6.00 * 42.5 = 255.00. So its $0.20 different when using a rate calculation.
						// If we just check to see if before/after units != 0, it will break having units in any other case where the line item didn't exist before, like adding overtime.
						//   Not sure if there is an easy way to fix this...
						$unit_rate = TTMath::div( $amount_diff, $units_diff, 4 );
						$amount_diff = TTMath::MoneyRound( TTMath::mul( $unit_rate, $units_diff, 4 ), 2, ( ( is_object( $psaf->getUserObject() ) && is_object( $psaf->getUserObject()->getCurrencyObject() ) ) ? $psaf->getUserObject()->getCurrencyObject() : null ) ); //Set MIN decimals to 2 and max to the currency rounding.
						Debug::Text( '    bFOUND DIFFERENCE of: Amount: ' . $amount_diff . ' Units: ' . $units_diff . ' Unit Rate: ' . $unit_rate, __FILE__, __LINE__, __METHOD__, 10 );

						$psaf->setRate( $unit_rate );
						$psaf->setUnits( $units_diff );
						$psaf->setAmount( $amount_diff );
					} else {
						$psaf->setAmount( $amount_diff );
					}

					$psaf->setDescription( TTi18n::getText( 'Adjustment from Pay Period Ending') .': ' . TTDate::getDate( 'DATE', $pay_stub_2_end_date ) . ( ( isset( $pay_stub2_obj ) ) ? ' '. TTi18n::getText( 'Run' ). ': '. $pay_stub2_obj->getRun() : '' ) );
					$psaf->setPrivateDescription( TTi18n::getText( 'Original Amount') .': '. TTMath::MoneyRound( $pay_stub2_entry_arr['amount'] ) .' '. TTi18n::getText( 'Corrected Amount') .': '. TTMath::MoneyRound( $pay_stub1_entry_arr['amount'] ) .' '. TTi18n::getText( 'Difference') .': '. TTMath::MoneyRound( $amount_diff ) );

					$psaf->setEffectiveDate( TTDate::getBeginDayEpoch( $ps_amendment_date ) );

					if ( $psaf->isValid() ) {
						$psaf->Save();
					} else {
						Debug::Text( 'ERROR: Unable to save PS Amendment!', __FILE__, __LINE__, __METHOD__, 10 );
					}

					unset( $amount_diff, $units_diff, $unit_rate );
				} else {
					Debug::Text( 'No DIFFERENCE!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		$pslf->CommitTransaction();

		return true;
	}

	/**
	 * @param string $pay_stub_id UUID
	 * @param bool $enable_notification
	 * @return bool
	 */
	function reCalculatePayStubYTD( $pay_stub_id, $enable_notification = false ) {
		//Make sure the entire pay stub object is loaded before calling this.
		if ( $pay_stub_id != '' ) {
			Debug::text( 'Attempting to recalculate pay stub YTD for pay stub id: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$pslf->StartTransaction();

			$pslf->getById( $pay_stub_id );
			if ( $pslf->getRecordCount() == 1 ) {
				$pay_stub = $pslf->getCurrent();

				$pay_stub->loadPreviousPayStub();
				if ( $pay_stub->loadCurrentPayStubEntries() == true ) {
					$pay_stub->setEnableProcessEntries( true );
					$pay_stub->setIsReCalculatingYTD( true );
					$pay_stub->processEntries();

					$pay_stub->setEnableNotification( $enable_notification );
					if ( $pay_stub->isValid() == true ) {
						Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
						$pay_stub->Save();

						$pslf->CommitTransaction();

						return true;
					} else {
						$pslf->FailTransaction();
						Debug::text( 'ERROR: Failed validation calculating YTD amounts!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( 'Failed loading current pay stub entries.', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			$pslf->CommitTransaction();
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function reCalculateCurrentYTD() {
		Debug::Text( 'ReCalculating Current Pay Stub YTD...', __FILE__, __LINE__, __METHOD__, 10 );

		//Recalculate the current pay stub as well, in case they changed the transaction date into the next year without modifying entries.
		$this->reCalculatePayStubYTD( $this->getId(), false );

		return true;
	}

	/**
	 * @return bool
	 */
	function reCalculateYTD() {
		//Get all pay stubs NEWER then the oldest transaction date/run of this pay stub.
		$recalc_transaction_date = $this->getTransactionDate();
		$recalc_run_id = $this->getRun();

		$data_diff = $this->getDataDifferences();
		if ( is_array( $data_diff ) ) {
			if ( $this->isDataDifferent( 'transaction_date', $data_diff, 'date' ) && TTDate::getMiddleDayEpoch( strtotime( $data_diff['transaction_date'] ) ) < $recalc_transaction_date ) {
				$recalc_transaction_date = strtotime( $data_diff['transaction_date'] );
			}

			if ( $this->isDataDifferent( 'run_id', $data_diff, 'int' ) && (int)$data_diff['run_id'] < $recalc_run_id ) {
				$recalc_run_id = $data_diff['run_id'];
			}
		}
		Debug::Text( 'ReCalculating YTD on all newer pay stubs from Transaction Date: '. TTDate::getDate('DATE+TIME', $recalc_transaction_date ) .' Run: '. $recalc_run_id, __FILE__, __LINE__, __METHOD__, 10 );

		//Because this recalculates YTD amounts and accrual balances which span years, we need to recalculate ALL (even 10yrs into the future) newer pay stubs.
		//Increase transaction date by one day, otherwise it can include the current pay stub and recalculate it, causing it to be incorrect with YTD adjustment PS amendments.
		// Ensure that the sort order is always oldest pay stub to newest, so YTD amounts are properly progated from one to the next.
		// **NOTE: If the transaction date/run gets changed from older to newer, we have to recalculate from the oldest transaction date forward.
		//         For example: Transaction date changes from 01-Dec to 10-Dec and that passes a pay stub that already exists with 05-Dec, we need to recalculate from 01-Dec forward.
		$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
		$pslf->getNextPayStubByUserIdAndTransactionDateAndRun( $this->getUser(), $recalc_transaction_date, $recalc_run_id );
		$total_pay_stubs = $pslf->getRecordCount();
		if ( $total_pay_stubs > 0 ) {
			Debug::Text( '  Newer Pay Stubs found: '. $total_pay_stubs, __FILE__, __LINE__, __METHOD__, 10 );
			$pslf->StartTransaction();

			foreach ( $pslf as $ps_obj ) {
				Debug::Text( '    Recalculating YTD for Pay Stub: ID: '. $ps_obj->getId() .' Transaction Date: '. TTDate::getDate('DATE+TIME', $ps_obj->getTransactionDate() ) .' Run: '. $ps_obj->getRun(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->reCalculatePayStubYTD( $ps_obj->getId(), false ); //Make sure pay stubs are not emailed out when just recalculating YTD amounts.
			}

			$pslf->CommitTransaction();
		} else {
			Debug::Text( 'No Newer Pay Stubs found!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function checkIfPayStubOrderChanged() {
		// When creating a new pay stub, its possible that there are pay stubs after the one that is being generated, so we would need to recalculate the YTD on those.
		if ( $this->isNew() == true ) {
			Debug::text( '  Creating new pay stub, check to see if there are any future ones that need to recalculate YTD for...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setEnableCalcYTD( true );
		} else {
			//If the user is changing the Transaction Date between years, make sure we always recalc the current pay stub YTD amount.
			// ie: Changing it from Dec 31st to January 1st, or vice versa makes the YTD amount reset.
			// This occur before processEntries() so it can be disabled by it if needed.
			//  Also check if they changed the payroll run_id, as that would change the order of the pay stubs too.
			//     **NOTE: We do *not* need to check if the pay period changed, as it all hinges on the transaction date anyways, regardless of the pay period start/end dates themselves.
			$data_diff = $this->getDataDifferences();
			if ( is_array( $data_diff )
					&& ( $this->isDataDifferent( 'transaction_date', $data_diff, 'date' ) || $this->isDataDifferent( 'run_id', $data_diff, 'int' ) ) ) {
				Debug::text( 'Transaction Date or Payroll Run has changed, recalculate YTD amounts on this and subsequent pay stubs.', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setEnableCalcYTD( true );

				if ( $this->getEnableProcessEntries() == false ) {
					$this->setEnableCalcCurrentYTD( true ); //Only calculate current pay stub YTD if we aren't already processing entries for it.
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		//Remember if this is a new pay stub for postSave()
		if ( $this->isNew( true ) == true ) {
			$this->is_new = true;
		}

		if ( $this->getStatus() == '' ) {
			$this->setStatus( 25 ); //Open
		}

		if ( $this->getType() == '' ) {
			$this->setType( 10 ); //Normal In-Cycle
		}

		if ( $this->getStatusBy() == '' ) {
			$this->setStatusBy();
		}

		//Automatically default to the users currency so this doesn't need to be specified in most cases.
		if ( $this->getCurrency() == '' && is_object( $this->getUserObject() ) ) {
			$this->setCurrency( $this->getUserObject()->getCurrency() );
		}

		$this->checkIfPayStubOrderChanged();

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		Debug::Text( 'Validating PayStub...', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// BELOW: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == false ) {
			// User
			if ( $this->getUser() !== false ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
			// Pay Period
			if ( $this->getPayPeriod() !== false && $this->getIsReCalculatingYTD() == false ) { //Don't check this when recalculating YTD amounts, as it could cause them to be incorrect as pay stubs with invalid pay periods (ie: deleted ones) would be skipped.
				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$this->Validator->isResultSetWithRows( 'start_date', //pay_period label isn't used when editing pay stubs.
													   $pplf->getByID( $this->getPayPeriod() ),
													   TTi18n::gettext( 'Invalid Pay Period' )
				);
			}
			// Payroll Run
			$this->Validator->isGreaterThan( 'run_id',
											 $this->getRun(),
											 TTi18n::gettext( 'Payroll Run must be 1 or higher' ),
											 1
			);
			if ( $this->Validator->isError( 'run_id' ) == false ) {
				$this->Validator->isLessThan( 'run_id',
											  $this->getRun(),
											  TTi18n::gettext( 'Payroll Run must be less than 128' ),
											  128
				);
			}
			// Currency
			if ( $this->getCurrency() !== false ) {
				$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
				$this->Validator->isResultSetWithRows( 'currency',
													   $culf->getByID( $this->getCurrency() ),
													   TTi18n::gettext( 'Invalid Currency' )
				);
				if ( $this->Validator->isError( 'currency' ) == false ) {
					if ( $culf->getRecordCount() == 1 && ( $this->is_new || $this->old_currency_id != $this->getCurrency() ) ) {
						$this->setCurrencyRate( $culf->getCurrent()->getReverseConversionRate() );
					}
				}
			}
			// Type
			if ( $this->getType() !== false ) {
				$this->Validator->inArrayKey( 'type_id',
											  $this->getType(),
											  TTi18n::gettext( 'Incorrect Type' ),
											  $this->getOptions( 'type' )
				);
			}

			// Currency Rate
			if ( $this->getCurrencyRate() !== false ) {
				$this->Validator->isFloat( 'currency_rate',
										   $this->getCurrencyRate(),
										   TTi18n::gettext( 'Incorrect Currency Rate' )
				);
			}

			//Relax much of the validation checks when recalculating YTD amounts.
			//  Otherwise as we change validation requirements it could prevent YTDs from recalculating.
			if ( $this->getIsReCalculatingYTD() == false ) {
				// Start date
				if ( $this->getStartDate() !== false ) {
					$this->Validator->isDate( 'start_date',
											  $this->getStartDate(),
											  TTi18n::gettext( 'Incorrect start date' )
					);
					if ( $this->getPayPeriod() !== TTUUID::getZeroID() && $this->Validator->isError( 'start_date' ) == false ) {
						$this->Validator->isTrue( 'start_date',
												  $this->isValidStartDate( $this->getStartDate() ),
												  TTi18n::gettext( 'Conflicting start date, does not match pay period' )
						);
					}
				}
				// End date
				if ( $this->getEndDate() !== false ) {
					$this->Validator->isDate( 'end_date',
											  $this->getEndDate(),
											  TTi18n::gettext( 'Incorrect end date' )
					);
					if ( $this->getPayPeriod() !== TTUUID::getZeroID() && $this->Validator->isError( 'end_date' ) == false ) {
						$this->Validator->isTrue( 'end_date',
												  $this->isValidEndDate( $this->getEndDate() ),
												  TTi18n::gettext( 'Conflicting end date, does not match pay period' )
						);
					}
				}
			}

			// Transaction date
			if ( $this->getTransactionDate() !== false ) {
				$this->Validator->isDate( 'transaction_date',
										  $this->getTransactionDate(),
										  TTi18n::gettext( 'Incorrect transaction date' )
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
			// Date
			if ( $this->getStatusDate() !== false ) {
				$this->Validator->isDate( 'status_date',
										  $this->getStatusDate(),
										  TTi18n::gettext( 'Incorrect Date' )
				);
			}
			// Status By Employee
			if ( $this->getStatusBy() !== false ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'status_by',
													   $ulf->getByID( $this->getStatusBy() ),
													   TTi18n::gettext( 'Incorrect Status By Employee' )
				);
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Don't allow deleting pay stubs with paid transactions.
		if ( $this->getDeleted() == true ) {
			$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
			$pstlf->getByPayStubIdAndTypeIdAndStatusId( $this->getId(), 10, [ 20 ] ); //Type: 10=Valid, Statuses: 20=Paid.

			Debug::Text( $pstlf->getRecordCount() . ' Paid transactions found...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $pstlf->getRecordCount() > 0 ) {
				$this->Validator->isTrue( 'status_id',
										  false,
										  TTi18n::gettext( 'This pay stub cannot be deleted as it has paid transactions' ) );
			}
		}

		if ( $this->getType() == 5 && $this->getTemp() == false ) {
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Invalid type, must be a temporary pay stub instead' ) );
		}

		//We could re-check these after processEntries are validated,
		//but that might duplicate the error messages?
		if ( $this->getDeleted() == false && $this->isUniquePayStub() == false ) {
			Debug::Text( 'Unique Pay Stub...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->isTrue( 'user_id',
									  false,
									  TTi18n::gettext( 'Employee already has a pay stub for this Pay Period/Transaction Date and Payroll Run' ) );
		}

		if ( $this->getDeleted() == false && $this->getPayPeriod() != TTUUID::getZeroID() && $this->isUniquePayStubType() == false ) {
			Debug::Text( 'Unique Pay Stub Type...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Employee already has %1 pay stub for this Pay Period', [ Option::getByKey( (int)$this->getType(), $this->getOptions( 'type' ) ) ] ) );
		}

		//When mass editing, don't require all dates to be set.
		if ( $this->Validator->getValidateOnly() == false ) {
			if ( TTUUID::isUUID( $this->getUser() ) == false || $this->getUser() == TTUUID::getZeroID() || $this->getUser() == TTUUID::getNotExistID() || is_object( $this->getUserObject() ) == false ) {
				$this->Validator->isTrue( 'user_id',
										  false,
										  TTi18n::gettext( 'Employee is not specified' ) );
			}

			if ( $this->getCurrency() == false ) {
				$this->Validator->isTrue( 'currency_id',
										  false,
										  TTi18n::gettext( 'Currency not specified' ) );
			}
			if ( $this->getStartDate() == false ) {
				$this->Validator->isDate( 'start_date',
										  $this->getStartDate(),
										  TTi18n::gettext( 'Incorrect start date' ) );
			}
			if ( $this->getEndDate() == false ) {
				$this->Validator->isDate( 'end_date',
										  $this->getEndDate(),
										  TTi18n::gettext( 'Incorrect end date' ) );
			}
			if ( $this->getTransactionDate() == false ) {
				$this->Validator->isDate( 'transaction_date',
										  $this->getTransactionDate(),
										  TTi18n::gettext( 'Incorrect transaction date' ) );
			}

			if ( $this->isValidTransactionDate( $this->getTransactionDate() ) == false ) {
				$this->Validator->isTrue( 'transaction_date',
										  false,
										  TTi18n::gettext( 'Transaction date is before pay period end date' ) );
			}
		}

		//Make sure they aren't setting a pay stub to OPEN if the pay period is closed.
		if ( is_object( $this->getPayPeriodObject() ) ) {
			if ( $this->getDeleted() == true ) {
				if ( $this->getStatus() == 40 ) {
					$this->Validator->isTrue( 'status_id',
											  false,
											  TTi18n::gettext( 'Unable to delete pay stubs that are marked as PAID' ) );
				}

				if ( $this->getPayPeriodObject()->getStatus() == 20 ) {
					$this->Validator->isTrue( 'status_id',
											  false,
											  TTi18n::gettext( 'Unable to delete pay stubs in closed pay periods' ) );
				}
			} else {
				//Make sure we aren't creating a new pay stub in a already closed pay period
				//  User must be able to change a pay stub from PAID to OPEN though.
				if ( $this->getStatus() != 40 && $this->getPayPeriodObject()->getStatus() == 20 ) {
					if ( $this->is_new == true ) {
						$this->Validator->isTrue( 'status_id',
												  false,
												  TTi18n::gettext( 'Unable to create pay stubs in a closed pay period' ) );
					} else {
						$this->Validator->isTrue( 'status_id',
												  false,
												  TTi18n::gettext( 'Unable to modify pay stubs assigned to a closed pay period' ) );
					}
				}
			}
		}

		$data_diff = $this->getDataDifferences();

		//Make sure transaction date is not earlier than a pay stub in the same pay period but having a higher payroll run.
		// However ignore these checks if its a temporary pay stub and we're just doing a post-adjustment carry-forward, otherwise it will fail everytime if the transaction date of the original pay stub was moved ahead by 1+ days.
		$pslf = TTNew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
		if ( $this->getIsReCalculatingYTD() == false
				&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID()
				&& is_object( $this->getUserObject() ) && $this->getTemp() == false ) {

			if ( $this->getStatus() == 40 && $this->getStatus() == $this->getGenericOldDataValue( 'status_id' ) ) { //40=Paid -- Must allow user to change pay stub status from PAID to OPEN.
				if ( $this->isDataDifferent( 'type_id', $data_diff ) == true ) {
					$this->Validator->isTrue( 'type_id',
											  false,
											  TTi18n::gettext( 'Type cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->isDataDifferent( 'pay_period_id', $data_diff ) == true ) {
					$this->Validator->isTrue( 'pay_period_id',
											  false,
											  TTi18n::gettext( 'Pay Period cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->Validator->isError( 'pay_period_id' ) == false && $this->isDataDifferent( 'start_date', $data_diff ) == true ) {
					$this->Validator->isTrue( 'start_date',
											  false,
											  TTi18n::gettext( 'Start Date cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->Validator->isError( 'pay_period_id' ) == false && $this->isDataDifferent( 'end_date', $data_diff ) == true ) {
					$this->Validator->isTrue( 'end_date',
											  false,
											  TTi18n::gettext( 'End Date cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->Validator->isError( 'pay_period_id' ) == false && $this->isDataDifferent( 'transaction_date', $data_diff ) == true ) {
					$this->Validator->isTrue( 'transaction_date',
											  false,
											  TTi18n::gettext( 'Payment Date cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->isDataDifferent( 'run_id', $data_diff ) == true ) {
					$this->Validator->isTrue( 'run_id',
											  false,
											  TTi18n::gettext( 'Payroll Run cannot be modified when the pay stub is marked as PAID' ) );
				}
				if ( $this->isDataDifferent( 'currency_id', $data_diff ) == true ) {
					$this->Validator->isTrue( 'currency_id',
											  false,
											  TTi18n::gettext( 'Currency cannot be modified when the pay stub is marked as PAID' ) );
				}
			}

			if ( $this->getPayPeriod() != TTUUID::getZeroID() ) {
				$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $this->getUser(), $this->getUserObject()->getCompany(), [ $this->getPayPeriod() ] );
				if ( $pslf->getRecordCount() > 0 ) {
					foreach ( $pslf as $ps_obj ) {
						Debug::Text( '  Checking conflicting transaction dates: Pay Stub ID: ' . $ps_obj->getId() . ' Pay Period ID: ' . $this->getPayPeriod() . ' Transaction Date: ' . TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ) . '(' . $this->getTransactionDate() . ') Run: ' . $ps_obj->getRun(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $ps_obj->getRun() < $this->getRun() && TTDate::getMiddleDayEpoch( $this->getTransactionDate() ) < TTDate::getMiddleDayEpoch( $ps_obj->getTransactionDate() ) ) {
							$this->Validator->isTrue( 'transaction_date',
													  false,
													  TTi18n::gettext( 'Transaction Date in this pay period cannot come before a previous payroll run transaction date' ) );
							break;
						}

						if ( $ps_obj->getRun() > $this->getRun() && TTDate::getMiddleDayEpoch( $this->getTransactionDate() ) > TTDate::getMiddleDayEpoch( $ps_obj->getTransactionDate() ) ) {
							$this->Validator->isTrue( 'transaction_date',
													  false,
													  TTi18n::gettext( 'Transaction Date in this pay period cannot come after a subsequent payroll run transaction date' ) );
							break;
						}

						//Make sure they aren't modifying the run_id to change the order of pay stubs without generating the pay stub again.
						//  Of course this could cause incorrect calculations due to YTD amounts being different when the PS was generated.
						if ( $this->is_new == false && $ps_obj->getId() != $this->getId() && $this->isDataDifferent( 'run_id', $data_diff ) == true ) {
							$old_run_id = (int)$data_diff['run_id'];
							$new_run_id = $this->getRun();

							if ( ( ( $old_run_id < $ps_obj->getRun() && $new_run_id > $ps_obj->getRun() ) || ( $old_run_id > $ps_obj->getRun() && $new_run_id < $ps_obj->getRun() ) ) ) {
								$this->Validator->isTrue( 'run_id',
														  false,
														  TTi18n::gettext( 'Payroll Run cannot be modified to come before or after another pay stub in this pay period' ) );
								break;
							}
						}
					}
					unset( $ps_obj );
				}
			}
		} //PSLF is used lower down.

		//Check to see if they are changing the transaction date between years, as that can cause taxes to need to be recalculated
		//  for example if they reached a tax limit in 2019, but it restarts in 2020, but they change the transaction date from 2020 back to 2019 without recalculating the pay stub.
		//  **Note: If they are terminating an employee on say Dec 22nd, and the transaction date is normally Jan 1st of the next year, they would need to back-date the transaction date to pay earlier due to government requirements.
		//          Because of this, we likely need to allow generating all in-cycle pay stubs with custom transaction dates.
		if ( $this->isDataDifferent( 'transaction_date', $data_diff ) == true && $this->getGenericOldDataValue( 'transaction_date' ) != false && TTDate::getYear( $this->getTransactionDate() ) != TTDate::getYear( TTDate::strtotime( $this->getGenericOldDataValue( 'transaction_date' ) ) ) ) {
			$this->Validator->isTrue( 'transaction_date',
									  false,
									  TTi18n::gettext( 'Transaction Date cannot be modified to a different year as it would result in tax/deductions being incorrect' ) );
		}

		if ( $this->getDeleted() == false && $this->getType() == 100 && $this->getStartDate() != '' ) { //Opening Balance
			//Check for any earlier pay stubs so Opening Balance Pay Stubs must be first.
			//$pslf->getLastPayStubByUserIdAndStartDateAndRun( $this->getUser(), $this->getStartDate(), $this->getRun() );
			$pslf->getLastPayStubByUserIdAndTransactionDateAndRun( $this->getUser(), $this->getTransactionDate(), $this->getRun() );
			if ( $pslf->getRecordCount() > 0 ) {
				$this->Validator->isTrue( 'type_id',
										  false,
										  TTi18n::gettext( 'Opening Balance Pay Stubs must not come after any other pay stub for this employee' ) );
			}
		}

		//Make sure a new pay stub isn't empty (no line items), otherwise the next pay stub YTD will get reset to $0, since this one isn't carrying YTD amounts through from the pay stub before it.
		//  Since when calculating the pay stub originally, we save it before any records are added, then add records, we can only do the below validation check when marking the pay stub as paid.
		if ( $this->Validator->getValidateOnly() == false && $this->getDeleted() == false && $this->getStatus() == 40 && $this->getIsReCalculatingYTD() == false && $this->isNew( true ) == true ) { //40=Paid
			$this->Validator->isGreaterThan( 'earnings',
									  $this->getTotalEntries(),
									  TTi18n::gettext( 'Pay stub must have at least one line item' ),
									  1 );
		}

		if ( $this->getEnableProcessEntries() == true ) {
			$this->ValidateEntries();
		}

		//40=Paid  -- Only check if we aren't mass editing, as we don't load transactions in that case anyways.
		// Also don't validate transactions when recalculating YTD amounts on newer pay stubs, as pre v11 they won't have transactions, so validation will always fail.
		if ( $this->Validator->getValidateOnly() == false && $this->getIsReCalculatingYTD() == false && ( $this->getStatus() == 40 || $this->getEnableProcessTransactions() == true ) ) {
			$this->ValidateTransactions();
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function ValidateEntries() {
		Debug::Text( 'Validating PayStub Entries...', __FILE__, __LINE__, __METHOD__, 10 );

		//Do Pay Stub Entry checks here
		if ( $this->isNew() == false ) {
			//Make sure the pay stub math adds up.
			Debug::Text( 'Validate: checkEarnings...', __FILE__, __LINE__, __METHOD__, 10 );

			//Allow YTD Adjustment pay stubs to not have any earnings, as they aren't shown to employees anyways.
			if ( $this->getType() == 90 ) { //90=YTD Adjustment.
				$this->Validator->isTrue( 'net_pay',
										  $this->checkZeroNetPay(),
										  TTi18n::gettext( 'Net Pay for Year-to-Date Adjustment pay stubs must be $0.00. Consider generating a Bonus/Correction pay stub instead.' ) );

				$this->Validator->isTrue( 'earnings',
										  ( ( $this->getTotalEntries() > 0 ) ? true : false ),
										  TTi18n::gettext( 'No pay stub amendments to process, skipping...' ) );
			} else {
				$this->Validator->isTrue( 'earnings',
										  $this->checkNoEarnings(),
										  TTi18n::gettext( 'No Earnings, employee may not have any hours for this pay period, or their wage may not be set' ) );
			}

			$this->Validator->isTrue( 'earnings',
									  $this->checkEarnings(),
									  TTi18n::gettext( 'Earnings don\'t match gross pay' ) );


			Debug::Text( 'Validate: checkDeductions...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->isTrue( 'deductions',
									  $this->checkDeductions(),
									  TTi18n::gettext( 'Deductions don\'t match total deductions' ) );

			Debug::Text( 'Validate: checkNegativeNetPay...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->Validator->isError( 'earnings' ) == false && $this->Validator->isError( 'deductions' ) == false && $this->Validator->isError( 'net_pay' ) == false ) {
				$this->Validator->isTrue( 'net_pay',
										  $this->checkNegativeNetPay(),
										  TTi18n::gettext( 'Net Pay (%1) is a negative amount, deductions (%2) exceed earnings (%3)', [ TTMath::MoneyRound( $this->getNetPay() ), TTMath::MoneyRound( $this->getDeductions() ), TTMath::MoneyRound( $this->getGrossPay() ) ] ) );
			}

			Debug::Text( 'Validate: checkNetPay...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->Validator->isError( 'earnings' ) == false && $this->Validator->isError( 'deductions' ) == false && $this->Validator->isError( 'net_pay' ) == false ) {
				$this->Validator->isTrue( 'net_pay',
										  $this->checkNetPay(),
										  TTi18n::gettext( 'Net Pay doesn\'t match earnings or deductions' ) );
			}
		}

		return $this->Validator->isValid();
	}

	/**
	 * @return bool
	 */
	function ValidateTransactions() {
		Debug::Text( 'Validating PayStub Transactions...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->isNew() == false ) {
			$this->loadCurrentPayStubEntries(); //Entries is needed to determine net pay to compare with transactions.
			$this->loadCurrentPayStubTransactions();
		}

		//Make sure the pay stub math adds up.
		Debug::Text( 'Validate: checkTransactions...', __FILE__, __LINE__, __METHOD__, 10 );

		//Allow Opening Balance pay stubs to have no transactions.
		// Only show transaction errors if their are actually earnings
		if ( $this->Validator->isError( 'earnings' ) == false ) {
			if ( $this->getType() == 100 ) { //Opening balance pay stub, no transactions should exist.
				$this->Validator->isTrue( 'transactions',
						( ( $this->getTotalTransactions() == 0 ) ? true : false ),
										  TTi18n::gettext( 'Transactions must not exist for opening balance pay stub' ) );
			} else {
				//Make sure if net pay is greater than zero, at least one transaction must exist.
				//  Need to allow $0 net pay, pay stubs with no transactions though.
				$net_pay_arr = $this->getNetPaySum();
				if ( isset( $net_pay_arr['amount'] ) && $net_pay_arr['amount'] > 0 ) {
					$this->Validator->isTrue( 'transactions',
							( ( $this->getTotalTransactions() > 0 ) ? true : false ),
											  TTi18n::gettext( 'No transactions exists, or employee does not have any pay methods' ) );
				}

				if ( $this->Validator->isError( 'transactions' ) == false && $this->Validator->isError( 'status_id' ) == false && $this->Validator->isError( 'earnings' ) == false && $this->Validator->isError( 'deductions' ) == false && $this->Validator->isError( 'net_pay' ) == false ) {
					$this->Validator->isTrue( 'status_id',
											  $this->checkTransactions(),
											  TTi18n::gettext( 'Net pay doesn\'t match total of all pending or paid transactions' ) );
				}
			}

			//Check if any transactions are PENDING state and that total of paid transactions matches net pay
			if ( $this->getStatus() == 40 && $this->Validator->isError( 'transactions' ) == false ) { //40=Paid
				$this->Validator->isTrue( 'status_id',
						( ( $this->getTotalPendingTransactions() > 0 ) ? false : true ),
										  TTi18n::gettext( 'This pay stub can\'t be marked paid as it has pending transactions' ) );
			}

		}

		return $this->Validator->isValid();
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$data_diff = $this->getDataDifferences();
		$this->removeCache( $this->getId() );

		if ( $this->getEnableProcessEntries() == true ) {
			if ( $this->savePayStubEntries() == false ) {
				$this->FailTransaction(); //Fail transaction as one of the PS entries was not saved.
			}
		}

		if ( $this->getEnableSyncPendingPayStubTransactionDates() == true ) {
			if ( $this->syncPendingPayStubTransactionDates( $data_diff ) == true ) {
				Debug::Text( 'Pay Stub Transaction Dates were syncd, enable processing transactions.', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setEnableProcessTransactions( true );
			}
		}

		if ( $this->getEnableProcessTransactions() == true ) {
			if ( $this->savePayStubTransactions() == false ) {
				Debug::Text( 'ERROR: Unable to save pay stub transactions, rolling back transaction...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->FailTransaction(); //Fail transaction as one of the PS entries was not saved.
			}
		}

		if ( $this->getTemp() == false ) { //Disable YTD calculations with temporary pay stubs.
			//This needs to be run even if entries aren't being processed,
			//for things like marking the pay stub paid or not.
			$this->handlePayStubAmendmentStatuses();
			$this->handleUserExpenseStatuses();

			if ( $this->getDeleted() == true ) {
				Debug::Text( 'Deleting Pay Stub, re-calculating YTD ', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setEnableCalcYTD( true );
			}

			if ( $this->getEnableCalcCurrentYTD() == true ) {
				$this->reCalculateCurrentYTD(); //Recalculate the current pay stub as well, in case they changed the transaction date into the next year without modifying entries.
			}

			if ( $this->getEnableCalcYTD() == true ) { //Don't recalculate YTD amounts on a temporary pay stub that is likely just used for creating carry-forward pay stub amendment records.
				$this->reCalculateYTD();
			}
		}


		//Make sure we only notify pay stubs that are being switched to the "PAID" status.
		//Do we want to avoid notifying pay stubs if they are making adjustments after the transaction date? Or maybe just in closed pay periods?
		if ( $this->getEnableNotification() && $this->getStatus() == 40 && $this->isDataDifferent( 'status_id', $data_diff ) == true ) { //Paid
			$this->sendNotificationPayStub();
		} else {
			Debug::Text( 'Pay Stub is not marked paid or notification sending is disabled, not notifying...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function handlePayStubAmendmentStatuses() {
		if ( $this->getTemp() == true ) { //Don't change pay stub amendment statuses for temporary pay stubs (just calculating correcitons)
			return true;
		}

		//Mark all PS amendments as 'PAID' if this status is paid.
		//Mark as NEW if the PS is deleted?
		if ( $this->getStatus() == 40 ) {
			$ps_amendment_status_id = 55; //PAID
		} else if ( $this->getDeleted() == false ) {
			$ps_amendment_status_id = 52; //INUSE
		} else {                          //Deleted pay stub, re-activate PSA so it can be used again.
			$ps_amendment_status_id = 50; //ACTIVE
		}

		//Loop through each entry in current pay stub, if they have
		//a PS amendment ID assigned to them, change the status.
		if ( isset( $this->tmp_data['current_pay_stub'] ) && isset( $this->tmp_data['current_pay_stub']['entries'] ) && is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['entries'] as $entry_arr ) {
				if ( isset( $entry_arr['pay_stub_amendment_id'] ) && $entry_arr['pay_stub_amendment_id'] != '' ) {
					Debug::Text( 'aFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__, 10 );

					$ps_amendment_ids[] = $entry_arr['pay_stub_amendment_id'];
				}
			}

			unset( $entry_arr );
		} else if ( $this->getStatus() != 10 ) {
			//Instead of loading the current pay stub entries, just run a query instead.
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$pself->getByPayStubId( $this->getId() );
			foreach ( $pself as $pay_stub_entry_obj ) {
				if ( $pay_stub_entry_obj->getPayStubAmendment() != false ) {
					Debug::Text( 'bFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__, 10 );
					$ps_amendment_ids[] = $pay_stub_entry_obj->getPayStubAmendment();
				}
			}
		}

		if ( isset( $ps_amendment_ids ) && is_array( $ps_amendment_ids ) ) {
			Debug::Text( 'cFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__, 10 );

			foreach ( $ps_amendment_ids as $ps_amendment_id ) {
				//Set PS amendment status to match Pay stub.
				$psalf = TTnew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psalf */
				$psalf->getById( $ps_amendment_id );
				if ( $psalf->getRecordCount() == 1 ) {
					$ps_amendment_obj = $psalf->getCurrent();
					if ( $ps_amendment_obj->getStatus() != $ps_amendment_status_id ) {
						Debug::Text( 'Changing Status of PS Amendment: ' . $ps_amendment_id, __FILE__, __LINE__, __METHOD__, 10 );
						$ps_amendment_obj->setEnablePayStubStatusChange( true ); //Tell PSA that its the pay stub changing the status, so we can ignore some validation checks.
						$ps_amendment_obj->setStatus( $ps_amendment_status_id );
						if ( $ps_amendment_obj->isValid() ) {
							$ps_amendment_obj->Save();
						} else {
							Debug::Text( 'Changing Status of PS Amendment FAILED!: ' . $ps_amendment_id, __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( 'Not Changing Status of PS Amendment, as its already the same: ' . $ps_amendment_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $ps_amendment_obj );
				}
				unset( $psalf );
			}
			unset( $ps_amendment_ids );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function handleUserExpenseStatuses() {
		if ( getTTProductEdition() <= TT_PRODUCT_CORPORATE ) {
			return true;
		}

		if ( $this->getTemp() == true ) { //Don't change pay stub amendment statuses for temporary pay stubs (just calculating corrections)
			return true;
		}

		Debug::Text( 'Change Expense Statuses: Pay Stub Status: ' . $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		//Mark all expenses as 'PAID' if this status is paid.
		//Mark as ACTIVE if the expense is deleted?
		if ( $this->getStatus() == 40 ) {
			$user_expense_status_id = 40; //ReImbursed
		} else if ( $this->getDeleted() == false ) {
			$user_expense_status_id = 35; //INUSE
		} else {                          //Deleted pay stub, re-activate expense so it can be used again.
			$user_expense_status_id = 30; //ACTIVE
		}

		//Loop through each entry in current pay stub, if they have
		//a User Expense ID assigned to them, change the status.
		if ( isset( $this->tmp_data['current_pay_stub'] ) && isset( $this->tmp_data['current_pay_stub']['entries'] ) && is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['entries'] as $entry_arr ) {
				if ( isset( $entry_arr['user_expense_id'] ) && TTUUID::isUUID( $entry_arr['user_expense_id'] ) && $entry_arr['user_expense_id'] != TTUUID::getZeroID() ) {
					Debug::Text( 'aFound User Expenses to change status on... ID: ' . $entry_arr['user_expense_id'], __FILE__, __LINE__, __METHOD__, 10 );
					$user_expense_ids[] = $entry_arr['user_expense_id'];
				}
			}

			unset( $entry_arr );
		} else if ( $this->getStatus() != 10 ) {
			//Instead of loading the current pay stub entries, just run a query instead.
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$pself->getByPayStubId( $this->getId() );
			foreach ( $pself as $pay_stub_entry_obj ) {
				if ( TTUUID::isUUID( $pay_stub_entry_obj->getUserExpense() ) && $pay_stub_entry_obj->getUserExpense() != TTUUID::getZeroID() ) {
					Debug::Text( 'bFound User Expense to change status on... ID: ' . $pay_stub_entry_obj->getUserExpense(), __FILE__, __LINE__, __METHOD__, 10 );
					$user_expense_ids[] = $pay_stub_entry_obj->getUserExpense();
				}
			}
		}

		if ( isset( $user_expense_ids ) && is_array( $user_expense_ids ) ) {
			Debug::Text( 'Found User Expenses to change status on...', __FILE__, __LINE__, __METHOD__, 10 );

			foreach ( $user_expense_ids as $user_expense_id ) {
				Debug::Text( '  Changing Status to: '. $user_expense_status_id, __FILE__, __LINE__, __METHOD__, 10 );
				//Set User Expense status to match Pay stub.
				$uelf = TTnew( 'UserExpenseListFactory' ); /** @var UserExpenseListFactory $uelf */
				$uelf->getById( $user_expense_id );
				if ( $uelf->getRecordCount() == 1 ) {
					$user_expense_obj = $uelf->getCurrent();
					if ( $user_expense_obj->getStatus() != $user_expense_status_id ) {
						Debug::Text( '    Changing Status of User Expense: ' . $user_expense_id, __FILE__, __LINE__, __METHOD__, 10 );
						$user_expense_obj->setEnablePayStubStatusChange( true ); //Tell Expense that its the pay stub changing the status, so we can ignore some validation checks.
						$user_expense_obj->setStatus( $user_expense_status_id );
						if ( $user_expense_obj->isValid() ) {
							$user_expense_obj->Save();
						} else {
							Debug::Text( '    ERROR: Changing Status of User Expense FAILED!: ' . $user_expense_id, __FILE__, __LINE__, __METHOD__, 10 );
							$this->FailTransaction(); //Prevent the transaction from completed as expenses will be out of sync now.
						}
					} else {
						Debug::Text( '    Not Changing Status of User Expense as its already the same: ' . $user_expense_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $user_expense_obj );
				}
				unset( $uelf );
			}
			unset( $user_expense_ids );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function isAccrualBalanceOutstanding() {
		$psea_arr = $this->getPayStubEntryAccountsArray();
		if ( is_array( $psea_arr ) ) {
			foreach ( $psea_arr as $psea_id => $psea_data ) {
				if ( $psea_data['type_id'] == 50 ) { //Accruals
					$psea_ids[] = $psea_id;
				}
			}

			if ( isset( $psea_ids ) ) {
				$retval = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $psea_ids );
				Debug::Arr( $retval, 'Sum Entries of Accruals: ', __FILE__, __LINE__, __METHOD__, 10 );
				if ( isset( $retval['ytd_amount'] ) && $retval['ytd_amount'] != 0 ) {
					Debug::Text( 'Accrual balances do exist...', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		return false;
	}

	/*


		Functions used in adding PayStub entries.


	*/
	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		if ( is_object( $this->pay_stub_entry_account_link_obj ) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			if ( is_object( $this->getUserObject() ) ) {
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
				$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();

					return $this->pay_stub_entry_account_link_obj;
				}
			}

			return false;
		}
	}

	/**
	 * @return array|bool|null
	 */
	function getPayStubEntryAccountsArray() {
		if ( is_array( $this->pay_stub_entry_accounts_obj ) ) {
			//Debug::text('Returning Cached data...', __FILE__, __LINE__, __METHOD__, 10);
			return $this->pay_stub_entry_accounts_obj;
		} else {
			if ( is_object( $this->getUserObject() ) ) {
				$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
				$psealf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $psealf->getRecordCount() > 0 ) {
					foreach ( $psealf as $psea_obj ) {
						$this->pay_stub_entry_accounts_obj[$psea_obj->getId()] = [
								'type_id'                           => $psea_obj->getType(),
								'accrual_pay_stub_entry_account_id' => $psea_obj->getAccrual(),
								'accrual_type_id'                   => $psea_obj->getAccrualType(),
						];
					}

					//Debug::Arr($this->pay_stub_entry_accounts_obj, ' Pay Stub Entry Accounts ('.count($this->pay_stub_entry_accounts_obj).'): ', __FILE__, __LINE__, __METHOD__, 10);
					return $this->pay_stub_entry_accounts_obj;
				}
			}

			Debug::text( 'Returning FALSE...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	/**
	 * @param string $id UUID
	 * @return bool|mixed
	 */
	function getPayStubEntryAccountArray( $id ) {
		if ( $id == '' ) {
			return false;
		}

		//Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$psea = $this->getPayStubEntryAccountsArray();

		if ( isset( $psea[$id] ) ) {
			return $psea[$id];
		}

		Debug::text( 'Returning FALSE...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $ps_entries
	 * @param int $type_ids          ID
	 * @param string $ps_account_ids UUID
	 * @return array|bool
	 */
	function getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, $type_ids = null, $ps_account_ids = null ) {
		$ps_entries = strtolower( $ps_entries );
		//Debug::text('PS Entries: '. $ps_entries .' Type ID: '. count($type_ids) .' PS Account ID: '. count($ps_account_ids), __FILE__, __LINE__, __METHOD__, 10);

		if ( !isset( $this->tmp_data['current_pay_stub'] ) ) {
			$this->tmp_data['current_pay_stub'] = null;
		}

		if ( !isset( $this->tmp_data['previous_pay_stub'] ) ) {
			$this->tmp_data['previous_pay_stub'] = null;
		}

		$entries = [];

		if ( $ps_entries == 'current' ) {
			if ( isset( $this->tmp_data['current_pay_stub']['entries'] ) ) {
				$entries = $this->tmp_data['current_pay_stub']['entries'];
			}

			//Only calculate temporary totals when specific ps_account_ids are specified, to avoid infinite recursion when calling get*Sum() functions that call this function with just type_ids.
			if ( is_array( $ps_account_ids ) ) {
				$entries = $this->calculateTemporaryTotals( $entries );
			}
		} else if ( $ps_entries == 'previous' ) {
			if ( isset( $this->tmp_data['previous_pay_stub']['entries'] ) ) {
				$entries = $this->tmp_data['previous_pay_stub']['entries'];
			}
		} else if ( $ps_entries == 'previous+ytd_adjustment' ) {
			if ( isset( $this->tmp_data['previous_pay_stub']['entries'] ) ) {
				$entries = $this->tmp_data['previous_pay_stub']['entries'];
			}

			//Include any YTD adjustment PS amendments in the current entries as if they occurred in the previous pay stub.
			//This so we can account for the first pay stub having a YTD adjustment that exceeds a wage base amount, so no amount is calculated.
			if ( isset( $this->tmp_data['current_pay_stub']['entries'] ) && is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
				foreach ( $this->tmp_data['current_pay_stub']['entries'] as $current_entry_arr ) {
					if ( isset( $current_entry_arr['ytd_adjustment'] ) && $current_entry_arr['ytd_adjustment'] === true ) {
						Debug::Text( 'Found YTD Adjustment in current pay stub when calculating previous pay stub amounts... Amount: ' . $current_entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
						//Debug::Arr($current_entry_arr, 'Found YTD Adjustment in current pay stub when calculating previous pay stub amounts...', __FILE__, __LINE__, __METHOD__, 10);
						$entries[] = $current_entry_arr;
					}
				}
				unset( $current_entry_arr );
			}
		}
		//Debug::Arr( $entries, 'Sum Entries Array: ', __FILE__, __LINE__, __METHOD__, 10);

		$retarr = [
				'rate'       => 0,
				'units'      => 0,
				'amount'     => 0,
				'ytd_units'  => 0,
				'ytd_amount' => 0,
		];

		if ( !is_array( $entries ) || count( $entries ) == 0 ) {
			Debug::text( '  No entries, return all zeros...', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		if ( $type_ids != '' && !is_array( $type_ids ) ) {
			$type_ids = [ $type_ids ];
		}

		if ( $ps_account_ids != '' && !is_array( $ps_account_ids ) ) {
			$ps_account_ids = [ $ps_account_ids ];
		}

		foreach ( $entries as $entry_arr ) {
			if ( $type_ids != '' && is_array( $type_ids ) ) {
				foreach ( $type_ids as $type_id ) {
					if ( isset( $entry_arr['pay_stub_entry_type_id'] ) && $type_id == $entry_arr['pay_stub_entry_type_id'] && $entry_arr['pay_stub_entry_type_id'] != 50 ) {
						if ( isset( $entry_arr['ytd_adjustment'] ) && $entry_arr['ytd_adjustment'] === true ) {
							//If a PS amendment makes a YTD adjustment, we need to treat it as a regular PS amendment
							//affecting the 'amount' instead of the 'ytd_amount', otherwise it will double up YTD amounts.
							//There are two issues at hand, doubling up YTD amounts, and not counting YTD adjustments
							//towards getting YTD amounts on the current pay stub for things like calculating
							//Wage Base/Maximum contributions.
							//Also, we need to make sure that these amounts aren't included in Tax/Deduction calculations
							//for this pay stub. But ARE calculated in this pay stub if they affect accruals.
							//FIXME: I think we need to change this so YTD adjustment PS amendments are just "magically" included in the YTD Amount on pay stubs
							//		 at anytime, then add a flag to have reports such as Tax reports include YTD adjustments or not. (enabled by default)
							//		 This should cut down on clutter/confusion with any pay stubs that currently have YTD amounts, as well as offer flexibility
							//		 to add these amounts in at anytime without having to regenerate pay stubs, so corrections can be made at the end of the year.
							$retarr['ytd_amount'] = TTMath::add( $retarr['ytd_amount'], $entry_arr['amount'] ?? 0 );
							$retarr['ytd_units'] = TTMath::add( $retarr['ytd_units'], $entry_arr['units'] ?? 0 );
						} else {
							$retarr['amount'] = TTMath::add( $retarr['amount'], $entry_arr['amount'] ?? 0 );
							$retarr['units'] = TTMath::add( $retarr['units'], $entry_arr['units'] ?? 0 );
							$retarr['ytd_amount'] = TTMath::add( $retarr['ytd_amount'], $entry_arr['ytd_amount'] ?? 0 );
							$retarr['ytd_units'] = TTMath::add( $retarr['ytd_units'], $entry_arr['ytd_units'] ?? 0 );

							//$retarr['rate'] = $entry_arr['rate']; //Can't add rate together, so what do we do, just use the rate from the last line item?
							//If amount and units are specified, try to calculate the hourly rate based on those. This will essentially result in a weighted average hourly rate if multiple line items of different rates exist.
							//  This should at least be more accurate than just using the last hourly rate.
							//  However, if amount and units are not specified, ignore those line items when calculating the weighted average hourly rate, since they could just be a PS amendment or a retro pay that likely shouldn't affect it anyways?
							if ( $retarr['amount'] != 0 && $retarr['units'] != 0 ) {
								$retarr['rate'] = TTMath::div( $retarr['amount'], $retarr['units'], 4 );
							} else {
								$retarr['rate'] = $entry_arr['rate'];
							}
						}
					} //else { //Debug::text('Type ID: '. $type_id .' does not match: '. $entry_arr['pay_stub_entry_type_id'], __FILE__, __LINE__, __METHOD__, 10);
				}
			} else if ( $ps_account_ids != '' && is_array( $ps_account_ids ) ) {
				foreach ( $ps_account_ids as $ps_account_id ) {
					if ( isset( $entry_arr['pay_stub_entry_account_id'] ) && $ps_account_id == $entry_arr['pay_stub_entry_account_id'] ) {
						if ( isset( $entry_arr['ytd_adjustment'] ) && $entry_arr['ytd_adjustment'] === true && $entry_arr['pay_stub_entry_type_id'] != 50 ) {
							$retarr['ytd_amount'] = TTMath::add( $retarr['ytd_amount'], $entry_arr['amount'] ?? 0 );
							$retarr['ytd_units'] = TTMath::add( $retarr['ytd_units'], $entry_arr['units'] ?? 0 );
						} else {
							$retarr['amount'] = TTMath::add( $retarr['amount'], $entry_arr['amount'] ?? 0 );
							$retarr['units'] = TTMath::add( $retarr['units'], $entry_arr['units'] ?? 0 );
							$retarr['ytd_amount'] = TTMath::add( $retarr['ytd_amount'], $entry_arr['ytd_amount'] ?? 0 );
							$retarr['ytd_units'] = TTMath::add( $retarr['ytd_units'], $entry_arr['ytd_units'] ?? 0 );

							//$retarr['rate'] = $entry_arr['rate']; //Can't add rate together, so just use the rate from the last line item.
							//If amount and units are specified, try to calculate the hourly rate based on those. This will essentially result in a weighted average hourly rate if multiple line items of different rates exist.
							//  This should at least be more accurate than just using the last hourly rate.
							//  However, if amount and units are not specified, ignore those line items when calculating the weighted average hourly rate, since they could just be a PS amendment or a retro pay that likely shouldn't affect it anyways?
							if ( $retarr['amount'] != 0 && $retarr['units'] != 0 ) {
								$retarr['rate'] = TTMath::div( $retarr['amount'], $retarr['units'], 4 );
							} else {
								$retarr['rate'] = $entry_arr['rate'];
							}
						}
					}
				}
			}
		}

		//Debug::Arr($retarr, 'SumByEntries RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retarr;
	}

	/**
	 * Calculate the real-time total accounts while the pay stub is being generated. This should allow Tax/Deductions records to calculate based on including/excluding Net Pay.
	 * This only needs to work on 'current' PS entries, as previous entries should already have the net pay calculated.
	 * @param $entries array
	 * @return array
	 */
	function calculateTemporaryTotals( $entries ) {
		$totals = [ 'total_gross', 'employee_deduction', 'employer_deduction', 'net_pay' ];
		foreach ( $totals as $total_name ) {
			switch ( $total_name ) {
				case 'total_gross':
					$sum_arr = $this->getEarningSum();
					$entries[] = $this->prepareEntry( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
					break;
				case 'employee_deduction':
					$sum_arr = $this->getDeductionSum();
					$entries[] = $this->prepareEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
					break;
				case 'employer_deduction':
					$sum_arr = $this->getEmployerDeductionSum();
					$entries[] = $this->prepareEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
					break;
				case 'net_pay':
					$sum_arr = $this->getNetPaySum();
					if ( is_array( $sum_arr ) ) {
						$entries[] = $this->prepareEntry( $this->getPayStubEntryAccountLinkObject()->getTotalNetPay(), $sum_arr['amount'], null, null, null, null, $sum_arr['ytd_amount'] );
					}
					break;
			}
		}

		return $entries;
	}

	/**
	 * @return bool
	 */
	function loadCurrentPayStubTransactions() {
		Debug::Text( 'aLoading current pay stub transactions, Pay Stub ID: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getId() != '' && ( !isset( $this->tmp_data['current_pay_stub']['transactions'] ) || count( $this->tmp_data['current_pay_stub']['transactions'] ) == 0 ) ) { //Don't load transactions if they are already set.
			$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
			$pstlf->getByPayStubId( $this->getID() );
			Debug::Text( 'bLoading current pay stub transactions, Pay Stub ID: ' . $this->getId() . ' Record Count: ' . $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $pstlf->getRecordCount() > 0 ) {
				$this->tmp_data['current_pay_stub']['transactions'] = null;

				foreach ( $pstlf as $pst_obj ) {
					$pst_arr[] = $pst_obj;
				}

				//Debug::Arr($pse_arr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( isset( $pst_arr ) ) {
					$this->tmp_data['current_pay_stub']['transactions'] = $pst_arr;

					Debug::Text( 'Loading current pay stub transactions success!', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		Debug::Text( 'No current pay stub transactions to load...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function loadCurrentPayStubEntries() {
		Debug::Text( 'aLoading current pay stub entries, Pay Stub ID: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getId() != '' && ( !isset( $this->tmp_data['current_pay_stub']['entries'] ) || count( $this->tmp_data['current_pay_stub']['entries'] ) == 0 ) ) { //Don't load entries if they are already set.
			//Get pay stub entries
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$pself->getByPayStubId( $this->getID() );
			Debug::Text( 'bLoading current pay stub entries, Pay Stub ID: ' . $this->getId() . ' Record Count: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $pself->getRecordCount() > 0 ) {
				$this->tmp_data['current_pay_stub']['entries'] = null;

				foreach ( $pself as $pse_obj ) {
					//Get PSE account type, group by that.
					$psea_arr = $this->getPayStubEntryAccountArray( $pse_obj->getPayStubEntryNameId() );
					if ( is_array( $psea_arr ) ) {
						$type_id = $psea_arr['type_id'];
					} else {
						$type_id = null;
					}

					//Skip total entries
					if ( $type_id != 40 ) {
						$pse_arr[] = [
								'id'                        => $pse_obj->getId(),
								'pay_stub_entry_type_id'    => $type_id,
								'pay_stub_entry_account_id' => $pse_obj->getPayStubEntryNameId(),
								'pay_stub_amendment_id'     => $pse_obj->getPayStubAmendment(),
								'user_expense_id'           => $pse_obj->getUserExpense(),
								'rate'                      => $pse_obj->getRate(),
								'units'                     => $pse_obj->getUnits(),
								'amount'                    => $pse_obj->getAmount(),
								//'ytd_units' => $pse_obj->getYTDUnits(),
								//'ytd_amount' => $pse_obj->getYTDAmount(),
								//Don't load YTD values, they need to be recalculated.
								'ytd_units'                 => null,
								'ytd_amount'                => null,
								'description'               => $pse_obj->getDescription(),

								//Make sure we carry over YTD adjustments when only recalculating Pay Stub YTD amounts going forward.
								//This fixes a bug where someone is using YTD adjustments in the middle of the year, and they modify to pay stubs prior to that, causing all newer pay stubs to be recalculated.
								'ytd_adjustment'            => (bool)$pse_obj->getColumn( 'ytd_adjustment' ),
						];
					}
					unset( $type_id, $psea_obj );
				}

				//Debug::Arr($pse_arr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( isset( $pse_arr ) ) {
					$this->tmp_data['current_pay_stub']['entries'] = $pse_arr;

					Debug::Text( 'Loading current pay stub entries success!', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		Debug::Text( 'Loading current pay stub entries failed!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function loadPreviousPayStub() {
		if ( $this->getUser() == false || $this->getStartDate() == false || $this->getRun() == false ) {
			return false;
		}

		Debug::text( 'Loading Pay Stub data prior to Transaction Date: ' . TTDate::getDate( 'DATE', $this->getTransactionDate() ) . ' Run: ' . $this->getRun(), __FILE__, __LINE__, __METHOD__, 10 );

		//Grab last pay stub so we can use it for YTD calculations on this pay stub.
		//  If we base this off the pay stub start_date, then its possible for the start_date to be 01-Nov but the transaction date being in 15-Dec with multiple pay stubs inbetween.
		//    This would cause problems with YTD amounts getting out-of-sync. Since YTD amounts should always be based on transaction date, make sure we use that here instead.
		$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
		//$pslf->getLastPayStubByUserIdAndStartDateAndRun( $this->getUser(), $this->getStartDate(), $this->getRun(), 1 );
		$pslf->getLastPayStubByUserIdAndTransactionDateAndRun( $this->getUser(), $this->getTransactionDate(), $this->getRun(), 1 );
		if ( $pslf->getRecordCount() > 0 ) {
			$ps_obj = $pslf->getCurrent();
			Debug::text( 'Loading Data from Pay Stub ID: ' . $ps_obj->getId() . ' Transaction Date: ' . TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ) . ' Run: ' . $ps_obj->getRun(), __FILE__, __LINE__, __METHOD__, 10 );

			$retarr = [
					'id'               => $ps_obj->getId(),
					'start_date'       => $ps_obj->getStartDate(),
					'end_date'         => $ps_obj->getEndDate(),
					'transaction_date' => $ps_obj->getTransactionDate(),
					'entries'          => null,
			];

			//
			//If previous pay stub is in a different year, only carry forward the accrual accounts.
			//
			$new_year = false;
			if ( TTDate::getYear( $this->getTransactionDate() ) != TTDate::getYear( $ps_obj->getTransactionDate() ) ) {
				Debug::text( 'Pay Stub Years dont match!...', __FILE__, __LINE__, __METHOD__, 10 );
				$new_year = true;
			}

			//Get pay stub entries
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$pself->getByPayStubId( $ps_obj->getID() );
			if ( $pself->getRecordCount() > 0 ) {
				foreach ( $pself as $pse_obj ) {
					Debug::text( '  Pay Stub Account ID: ' . $pse_obj->getPayStubEntryNameId() . ' Amount: ' . $pse_obj->getAmount() .' YTD Amount: '. $pse_obj->getYTDAmount(), __FILE__, __LINE__, __METHOD__, 10 );

					//Get PSE account type, group by that.
					$psea_arr = $this->getPayStubEntryAccountArray( $pse_obj->getPayStubEntryNameId() );
					if ( is_array( $psea_arr ) ) {
						$type_id = $psea_arr['type_id'];
					} else {
						$type_id = null;
					}

					//If we're just starting a new year, only carry over accrual balances (that are non-zero balance), reset all YTD entries.
					if ( $new_year == false || $type_id == 50 ) {
						//Need a way of getting accrual balances off the pay stubs if they are no longer used. So starting a new year will do that as long as they have zero balance.
						//  This way they mimic regular pay stub entries that reset on the new year too.
						if ( $new_year == true && $type_id == 50 && $pse_obj->getYTDAmount() == 0 ) {
							Debug::text( '  Skipping unused Accrual Balance in new year for Pay Stub Account ID: ' . $pse_obj->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						$pse_arr[] = [
								'id'                        => $pse_obj->getId(),
								'pay_stub_entry_type_id'    => $type_id,
								'pay_stub_entry_account_id' => $pse_obj->getPayStubEntryNameId(),
								'pay_stub_amendment_id'     => $pse_obj->getPayStubAmendment(),
								'user_expense_id'           => $pse_obj->getUserExpense(),
								'rate'                      => $pse_obj->getRate(),
								'units'                     => $pse_obj->getUnits(),
								'amount'                    => $pse_obj->getAmount(),
								'ytd_units'                 => $pse_obj->getYTDUnits(),
								'ytd_amount'                => $pse_obj->getYTDAmount(),
						];
					}
					unset( $type_id, $psea_obj );
				}

				if ( isset( $pse_arr ) ) {
					$retarr['entries'] = $pse_arr;

					$this->tmp_data['previous_pay_stub'] = $retarr;

					return true;
				}
			} else {
				Debug::text( 'WARNING: Found pay stub, but it doesnt have any entries...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		Debug::text( 'Returning FALSE...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Prepares the Pay Stub entry for adding.
	 * @param string $pay_stub_entry_account_id UUID
	 * @param $amount
	 * @param null $units
	 * @param null $rate
	 * @param null $description
	 * @param string $ps_amendment_id           UUID
	 * @param null $ytd_amount
	 * @param null $ytd_units
	 * @param bool $ytd_adjustment
	 * @param string $user_expense_id           UUID
	 * @return array|bool
	 */
	function prepareEntry( $pay_stub_entry_account_id, $amount, $units = null, $rate = null, $description = null, $ps_amendment_id = null, $ytd_amount = null, $ytd_units = null, $ytd_adjustment = false, $user_expense_id = null ) {
		Debug::text( 'Prepare Entry: PSE Account ID: ' . $pay_stub_entry_account_id . ' Amount: ' . $amount .' Rate: '. $rate .' Units: '. $units . ' YTD Amount: ' . $ytd_amount . ' Pay Stub Amendment Id: ' . $ps_amendment_id . ' User Expense: ' . $user_expense_id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pay_stub_entry_account_id == '' || TTUUID::isUUID( $pay_stub_entry_account_id ) == false || $pay_stub_entry_account_id == TTUUID::getZeroID() || $pay_stub_entry_account_id == TTUUID::getNotExistID() ) {
			return false;
		}

		//Round amount to 2 decimal places (or whatever the currency object is set too)
		//So any totaling is proper after this point, because it gets rounded to two decimal places in PayStubEntryFactory too.
		//PHP has a bug that round() converts large values with 0's on the end into scientific notation. Use number_format() instead.
		$rate = ( $rate != '' ) ? TTMath::MoneyRound( $rate, 4 ) : $rate;     //DB schema limits to 4 decimal places.
		$units = ( $units != '' ) ? TTMath::MoneyRound( $units, 4 ) : $units; //DB schema limits to 4 decimal places.
		$amount = TTMath::MoneyRound( $amount, 2, $this->getCurrencyObject() );
		$ytd_amount = TTMath::MoneyRound( $ytd_amount, 2, $this->getCurrencyObject() );
		if ( is_numeric( $amount ) ) {
			$psea_arr = $this->getPayStubEntryAccountArray( $pay_stub_entry_account_id );
			if ( is_array( $psea_arr ) ) {
				$type_id = $psea_arr['type_id'];
			} else {
				$type_id = null;
			}

			$retarr = [
					'pay_stub_entry_type_id'    => $type_id,
					'pay_stub_entry_account_id' => $pay_stub_entry_account_id,
					'pay_stub_amendment_id'     => $ps_amendment_id,
					'user_expense_id'           => $user_expense_id,
					'rate'                      => $rate,
					'units'                     => $units,
					'amount'                    => $amount, //PHP v5.3.5 has a bug that it converts large values with 0's on the end into scientific notation.
					'ytd_units'                 => $ytd_units,
					'ytd_amount'                => $ytd_amount,
					'description'               => $description,
					'ytd_adjustment'            => $ytd_adjustment,
			];


			return $retarr;
		}

		Debug::text( 'Returning FALSE', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $pay_stub_entry_account_id UUID
	 * @param $amount
	 * @param null $units
	 * @param null $rate
	 * @param null $description
	 * @param string $ps_amendment_id           UUID
	 * @param null $ytd_amount
	 * @param null $ytd_units
	 * @param bool $ytd_adjustment
	 * @param string $user_expense_id           UUID
	 * @return bool
	 */
	function addEntry( $pay_stub_entry_account_id, $amount, $units = null, $rate = null, $description = null, $ps_amendment_id = null, $ytd_amount = null, $ytd_units = null, $ytd_adjustment = false, $user_expense_id = null ) {
		Debug::text( 'Add Entry: PSE Account ID: ' . $pay_stub_entry_account_id . ' Amount: ' . $amount . ' Rate: '. $rate .' Units: '. $units .' YTD Amount: ' . $ytd_amount . ' Pay Stub Amendment Id: ' . $ps_amendment_id . ' User Expense: ' . $user_expense_id, __FILE__, __LINE__, __METHOD__, 10 );

		$retarr = $this->prepareEntry( $pay_stub_entry_account_id, $amount, $units, $rate, $description, $ps_amendment_id, $ytd_amount, $ytd_units, $ytd_adjustment, $user_expense_id );
		if ( is_array( $retarr ) ) {
			$this->tmp_data['current_pay_stub']['entries'][] = $retarr;

			//Check if this pay stub account is linked to an accrual account.
			//Make sure the PSE account does not match the PSE Accrual account,
			//because we don't want to get in to an infinite loop.
			//Also don't touch the accrual account if the amount is 0.
			//This happens mostly when AddUnUsedEntries is called.
			$psea_arr = $this->getPayStubEntryAccountArray( $pay_stub_entry_account_id );
			if ( is_array( $psea_arr ) ) {
				$type_id = $psea_arr['type_id'];
			} else {
				$type_id = null;
			}

			if ( $this->getEnableLinkedAccruals() == true
					&& $amount != 0
					&& $psea_arr['accrual_pay_stub_entry_account_id'] != ''
					&& $psea_arr['accrual_pay_stub_entry_account_id'] != TTUUID::getZeroID()
					&& $psea_arr['accrual_pay_stub_entry_account_id'] != $pay_stub_entry_account_id
					&& $psea_arr['accrual_type_id'] != ''
					&& $ytd_adjustment == false ) {

				Debug::text( '  Add Entry: PSE Account Links to Accrual Account!: ' . $pay_stub_entry_account_id . ' Accrual Account ID: ' . $psea_arr['accrual_pay_stub_entry_account_id'] . ' Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

				$tmp_amount = $amount;

				//Handle add/subtract amounts for the accrual
				$reverse_amount = false;
				switch ( (int)$psea_arr['accrual_type_id'] ) {
					case 10: //Earning/Misc Subtracts, EE/ER Deduction Adds
						if ( $type_id == 10 || $type_id == 80 ) { //10=Earning, 80=Misc
							$reverse_amount = true;
						}
						break;
					case 20: //Earning/Misc Adds, EE/ER Deduction Subtracts
						if ( $type_id == 20 || $type_id == 30 ) { //20=EE Ded, 30=ER Ded
							$reverse_amount = true;
						}
						break;
				}

				if ( $reverse_amount == true ) {
					$tmp_amount = TTMath::mul( $amount, -1 );
				}
				Debug::text( '  Amount: ' . $tmp_amount, __FILE__, __LINE__, __METHOD__, 10 );

				return $this->addEntry( $psea_arr['accrual_pay_stub_entry_account_id'], $tmp_amount, null, null, null, null, null, null );
			}

			return true;
		}

		Debug::text( 'Returning FALSE', __FILE__, __LINE__, __METHOD__, 10 );

		$this->Validator->isTrue( 'entry',
								  false,
								  TTi18n::gettext( 'Invalid Pay Stub Entry. A Pay Code may not have a Pay Stub Account specified, or amount is invalid' ) );

		return false;
	}

	/**
	 * @return bool
	 */
	function processEntries() {
		Debug::Text( 'Processing PayStub (' . ( isset( $this->tmp_data['current_pay_stub']['entries'] ) ? count( $this->tmp_data['current_pay_stub']['entries'] ) : 0 ) . ') Entries...', __FILE__, __LINE__, __METHOD__, 10 );
		///Debug::Arr($this->tmp_data['current_pay_stub'], 'Current Entries...', __FILE__, __LINE__, __METHOD__, 10);

		$this->deleteZeroAmountEntries(); //Must occur before any other processing function is called, so we can remove $0 entries.
		$this->deleteEntries( false );    //Delete only total entries
		$this->addUnUsedYTDEntries();
		$this->addEarningSum();
		$this->addDeductionSum();
		$this->addEmployerDeductionSum();
		$this->addNetPay();

		$this->setEnableCalcCurrentYTD( false ); //No need to recalculate current YTD if we are processing entries.

		return true;
	}

	/**
	 * @param $pay_stub_arr
	 * @param bool $clear_out_ytd
	 * @return bool
	 */
	function markPayStubEntriesForYTDCalculation( &$pay_stub_arr, $clear_out_ytd = true ) {
		if ( !is_array( $pay_stub_arr ) ) {
			return false;
		}

		//Debug::Text('Marking which entries are to have YTD calculated on!', __FILE__, __LINE__, __METHOD__, 10);

		$trace_pay_stub_entry_account_id = [];

		//Loop over the array in reverse
		$pay_stub_arr = array_reverse( $pay_stub_arr, true );
		foreach ( $pay_stub_arr as $current_key => $val ) {
			if ( !isset( $trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']] ) ) {
				$trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']] = 0;
			} else {
				$trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']]++;
			}

			$pay_stub_arr[$current_key]['calc_ytd'] = $trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']];
			//Order here matters in cases for pay stubs with multiple accrual entries.
			//Because if the YTD amount is:
			// -800.00
			//	  0.00
			//	  0.00
			//We may end up clearing out the only YTD value that is of use.

			//CLEAR_OUT_YTD is used for backwards compat, so old pay stubs that calculated YTD
			//Only duplicate PS entries get zero'd out.
			if ( $clear_out_ytd == true && $pay_stub_arr[$current_key]['calc_ytd'] > 0 ) {
				//Clear out YTD entries so the sum() function can calculate them properly.
				//This is for backwards compat.
				$pay_stub_arr[$current_key]['ytd_amount'] = 0;
				$pay_stub_arr[$current_key]['ytd_units'] = 0;
			}
		}
		$pay_stub_arr = array_reverse( $pay_stub_arr, true );

		//Debug::Arr($pay_stub_arr, 'Copy Marked Entries ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @return bool
	 */
	function calcPayStubEntriesYTD() {
		if ( !is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			return false;
		}

		Debug::Text( 'Calculating Pay Stub Entry YTD values!', __FILE__, __LINE__, __METHOD__, 10 );

		$this->markPayStubEntriesForYTDCalculation( $this->tmp_data['previous_pay_stub']['entries'] );
		$this->markPayStubEntriesForYTDCalculation( $this->tmp_data['current_pay_stub']['entries'], false ); //Dont clear out YTD values.

		//Debug::Arr($this->tmp_data['current_pay_stub']['entries'], 'Before YTD calculation', __FILE__, __LINE__, __METHOD__, 10);

		//addUnUsedYTDEntries() should be called before this

		//Go through each pay stub entry, and if there is no entry of the same
		//PSE account id, calc YTD. If there is a duplicate PSE account id,
		//only calculate the YTD on the LAST one.
		foreach ( $this->tmp_data['current_pay_stub']['entries'] as $key => $entry_arr ) {
			//If YTD is already set, don't recalculate it, because it could be a PS amendment YTD adjustment.
			//Keep in mind this makes it so if a YTD adjustment is set it will show up in the YTD column, and if there
			//is a second PSE account of the same, its YTD will show up too.
			//So this is the ONLY time YTD values should show up for the duplicate PSE accounts on the same PS.
			if ( $entry_arr['calc_ytd'] == 0 ) {
				//Debug::Text('Calculating YTD on PSE account: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__, 10);
				$current_pay_stub_sum = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $entry_arr['pay_stub_entry_account_id'] );
				$previous_pay_stub_sum = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, $entry_arr['pay_stub_entry_account_id'] );

				Debug::Text( 'Key: ' . $key . ' Previous YTD Amount: ' . $previous_pay_stub_sum['ytd_amount'] . ' Current Amount: ' . $current_pay_stub_sum['amount'] . ' Current YTD Amount: ' . $current_pay_stub_sum['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );
				$this->tmp_data['current_pay_stub']['entries'][$key]['ytd_amount'] = TTMath::add( $previous_pay_stub_sum['ytd_amount'], TTMath::add( $current_pay_stub_sum['amount'], $current_pay_stub_sum['ytd_amount'] ), ( is_object( $this->getCurrencyObject() ) ) ? $this->getCurrencyObject()->getRoundDecimalPlaces() : 2 );
				$this->tmp_data['current_pay_stub']['entries'][$key]['ytd_units'] = TTMath::add( $previous_pay_stub_sum['ytd_units'], TTMath::add( $current_pay_stub_sum['units'], $current_pay_stub_sum['ytd_units'] ), 4 );
			} else if ( $this->tmp_data['current_pay_stub']['entries'][$key]['ytd_amount'] == '' ) {
				//Debug::Text('Setting YTD on PSE account: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__, 10);
				$this->tmp_data['current_pay_stub']['entries'][$key]['ytd_amount'] = 0;
				$this->tmp_data['current_pay_stub']['entries'][$key]['ytd_units'] = 0;
			}
		}

		//Debug::Arr($this->tmp_data['current_pay_stub']['entries'], 'After YTD calculation', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @return bool
	 */
	function savePayStubEntries() {
		if ( !is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			return false;
		}

		//Cant add entries to a new paystub, since the pay_stub_id isn't set yet.
		if ( $this->isNew() == true ) {
			return false;
		}

		$this->calcPayStubEntriesYTD();

		//Debug::Arr($this->tmp_data['current_pay_stub']['entries'], 'Current Pay Stub Entries: ', __FILE__, __LINE__, __METHOD__, 10);

		foreach ( $this->tmp_data['current_pay_stub']['entries'] as $pse_arr ) {
			if ( isset( $pse_arr['pay_stub_entry_account_id'] ) && isset( $pse_arr['amount'] ) ) {
				Debug::Text( 'Current Pay Stub ID: ' . $this->getId() . ' Adding Pay Stub Entry for: ' . $pse_arr['pay_stub_entry_account_id'] . ' Amount: ' . $pse_arr['amount'] . ' Rate: '. $pse_arr['rate'] .' Units: '. $pse_arr['units'] .' YTD Amount: ' . $pse_arr['ytd_amount'] . ' YTD Units: ' . $pse_arr['ytd_units'], __FILE__, __LINE__, __METHOD__, 10 );
				$psef = TTnew( 'PayStubEntryFactory' ); /** @var PayStubEntryFactory $psef */

				$psef->setEnableCalculateYTD( $this->getIsReCalculatingYTD() ); //Must come before $psef->setPayStubAmendment() below.

				$psef->setPayStub( $this->getId() );
				$psef->setPayStubEntryNameId( $pse_arr['pay_stub_entry_account_id'] );
				$psef->setRate( $pse_arr['rate'] );
				$psef->setUnits( $pse_arr['units'] );
				$psef->setAmount( $pse_arr['amount'] );
				$psef->setYTDAmount( $pse_arr['ytd_amount'] );
				$psef->setYTDUnits( $pse_arr['ytd_units'] );

				$psef->setDescription( $pse_arr['description'] );
				if ( TTUUID::isUUID( $pse_arr['pay_stub_amendment_id'] ) && $pse_arr['pay_stub_amendment_id'] != TTUUID::getZeroID() && $pse_arr['pay_stub_amendment_id'] != TTUUID::getNotExistID() ) {
					$psef->setPayStubAmendment( $pse_arr['pay_stub_amendment_id'] );
				}
				if ( isset( $pse_arr['user_expense_id'] )
						&& TTUUID::isUUID( $pse_arr['user_expense_id'] ) && $pse_arr['user_expense_id'] != TTUUID::getZeroID() && $pse_arr['user_expense_id'] != TTUUID::getNotExistID() ) {
					$psef->setUserExpense( $pse_arr['user_expense_id'] );
				}

				if ( $psef->isValid() == false || $psef->Save() == false ) {
					Debug::Text( 'Adding Pay Stub Entry failed!', __FILE__, __LINE__, __METHOD__, 10 );

					$this->Validator->isTrue( 'entry',
											  false,
																					   //TTi18n::gettext('Invalid Pay Stub entry')
											  $psef->Validator->getTextErrors( false ) //Get specific error messages from PSEF, rather than use a generic message, as user does see these.
					);

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * There may be cases where Tax/Deduction records need to do calculations on hours (ie: unpaid absences) when there is no pay, but they need to do other calculations to add pay.
	 * So we need to make sure all paid and unpaid time ($0 amounts) from the timesheets still appears on the pay stubs initially, then gets deleted off after all Tax/Deductions are calculated.
	 * @return bool
	 */
	function deleteZeroAmountEntries() {
		if ( isset( $this->tmp_data['current_pay_stub']['entries'] ) && is_array( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['entries'] as $key => $entry_arr ) {
				if ( $entry_arr['amount'] == 0 && $entry_arr['ytd_amount'] == 0 ) { //Check ytd_amount as well to handle YTD adjustment PS amendments.
					Debug::Text( 'Deleting Pay Stub Entry: Key: ' . $key . ' Amount: ' . $entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
					unset( $this->tmp_data['current_pay_stub']['entries'][$key] );
				}
			}
		}

		return true;
	}

	/**
	 * @param bool $all_entries
	 * @return bool
	 */
	function deleteEntries( $all_entries = false ) {
		//Delete any entries from the pay stub, so they can be re-created.
		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */

		if ( $all_entries == true ) {
			$pself->getByPayStubIdAndType( $this->getId(), 40 );
		} else {
			$pself->getByPayStubId( $this->getId() );
		}

		foreach ( $pself as $pay_stub_entry_obj ) {
			Debug::Text( 'Deleting Pay Stub Entry: ' . $pay_stub_entry_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$del_ps_entry_ids[] = $pay_stub_entry_obj->getId();
		}
		if ( isset( $del_ps_entry_ids ) ) {
			$pself->bulkDelete( $del_ps_entry_ids );
		}
		unset( $pay_stub_entry_obj, $del_ps_entry_ids );

		return true;
	}

	/**
	 * @return bool
	 */
	function addUnUsedYTDEntries() {
		Debug::Text( 'Adding Unused Entries ', __FILE__, __LINE__, __METHOD__, 10 );
		//This has to happen ABOVE the total entries... So Gross pay and stuff
		//takes them in to account when doing YTD totals
		//
		//Find out which prior entries have been made and carry any YTD entries forward with 0 amounts
		if ( isset( $this->tmp_data['previous_pay_stub'] ) && is_array( $this->tmp_data['previous_pay_stub']['entries'] ) ) {
			//Debug::Arr($this->tmp_data['current_pay_stub'], 'Current Pay Stub Entries:', __FILE__, __LINE__, __METHOD__, 10);

			foreach ( $this->tmp_data['previous_pay_stub']['entries'] as $key => $entry_arr ) {
				//See if current pay stub entries have previous pay stub entries.
				//Skip total entries, as they will be created after anyways.
				if ( $entry_arr['pay_stub_entry_type_id'] != 40
						&& isset( $this->tmp_data['current_pay_stub']['entries'] )
						&& Misc::inArrayByKeyAndValue( $this->tmp_data['current_pay_stub']['entries'], 'pay_stub_entry_account_id', $entry_arr['pay_stub_entry_account_id'] ) == false ) {
					Debug::Text( 'Adding UnUsed Entry: ' . $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__, 10 );
					$this->addEntry( $entry_arr['pay_stub_entry_account_id'], 0, 0 );
				} else {
					Debug::Text( 'NOT Adding already existing Entry: ' . $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function addEarningSum() {
		$sum_arr = $this->getEarningSum();
		Debug::Text( 'Sum: ' . $sum_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
		if ( isset( $sum_arr['amount'] ) && is_object( $this->getPayStubEntryAccountLinkObject() ) ) { //Allow negative amounts for adjustment purposes
			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
		}
		unset( $sum_arr );

		return true;
	}

	/**
	 * @return bool
	 */
	function addDeductionSum() {
		$sum_arr = $this->getDeductionSum();
		if ( isset( $sum_arr['amount'] ) && is_object( $this->getPayStubEntryAccountLinkObject() ) ) { //Allow negative amounts for adjustment purposes
			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
		}
		unset( $sum_arr );

		return true;
	}

	/**
	 * @return bool
	 */
	function addEmployerDeductionSum() {
		$sum_arr = $this->getEmployerDeductionSum();
		if ( isset( $sum_arr['amount'] ) && is_object( $this->getPayStubEntryAccountLinkObject() ) ) { //Allow negative amounts for adjustment purposes
			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $sum_arr['amount'], $sum_arr['units'], null, null, null, $sum_arr['ytd_amount'] );
		}
		unset( $sum_arr );

		return true;
	}

	/**
	 * @return array|bool
	 * @noinspection PhpArrayIndexImmediatelyRewrittenInspection
	 * @noinspection PhpArrayIndexImmediatelyRewrittenInspection
	 */
	function getNetPaySum() {
		$earning_sum_arr = $this->getEarningSum();
		$deduction_sum_arr = $this->getDeductionSum();

		if ( $earning_sum_arr['amount'] >= 0 ) {
			$retarr = [
					'units'      => 0,
					'amount'     => 0,
					'ytd_units'  => 0,
					'ytd_amount' => 0,
			];

			$retarr['amount'] = TTMath::sub( $earning_sum_arr['amount'], $deduction_sum_arr['amount'] );
			$retarr['ytd_amount'] = TTMath::sub( $earning_sum_arr['ytd_amount'], $deduction_sum_arr['ytd_amount'] );

			Debug::Text( 'Net Pay: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}
		unset( $net_pay_amount, $net_pay_ytd_amount, $earning_sum_arr, $deduction_sum_arr );

		Debug::Text( 'Earning Sum is 0 or less. ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function addNetPay() {
		$net_pay_arr = $this->getNetPaySum();
		if ( isset( $net_pay_arr['amount'] ) && is_object( $this->getPayStubEntryAccountLinkObject() ) ) {
			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalNetPay(), $net_pay_arr['amount'], null, null, null, null, $net_pay_arr['ytd_amount'] );
		}

		return true;
	}

	/**
	 * @return array
	 */
	function getEarningSum() {
		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 10 );
		Debug::Text( 'Earnings Sum (' . $this->getId() . '): ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * @return int
	 */
	function getTotalTransactions() {
		//getTransactionSum() has the same code.
		$total = 0;
		if ( isset( $this->tmp_data['current_pay_stub']['transactions'] ) && is_array( $this->tmp_data['current_pay_stub']['transactions'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['transactions'] as $pst_obj ) {
				if ( !isset( $pst_obj->data['deleted'] ) || $pst_obj->data['deleted'] == 0 ) {
					$total++;
				}
			}
		}

		return $total;
	}

	/**
	 * @return int
	 */
	function getTotalPendingTransactions() {
		//getTransactionSum() has the same code.
		$total = 0;
		if ( isset( $this->tmp_data['current_pay_stub']['transactions'] ) && is_array( $this->tmp_data['current_pay_stub']['transactions'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['transactions'] as $pst_obj ) {
				if ( ( !isset( $pst_obj->data['deleted'] ) || $pst_obj->data['deleted'] == 0 ) && ( !isset( $pst_obj->data['status_id'] ) || $pst_obj->data['status_id'] == 10 ) ) {
					$total++;
				}
			}
		}

		return $total;
	}

	/**
	 * @return int|string
	 */
	function getTransactionsSum() {
		$total = 0;
		if ( isset( $this->tmp_data['current_pay_stub']['transactions'] ) && is_array( $this->tmp_data['current_pay_stub']['transactions'] ) ) {
			foreach ( $this->tmp_data['current_pay_stub']['transactions'] as $pst_obj ) {
				//Include amounts from both pending and paid transactions, as combined they should never exceed net pay.
				if ( in_array( $pst_obj->getStatus(), [ 10, 20 ] ) && ( !isset( $pst_obj->data['deleted'] ) || $pst_obj->data['deleted'] == 0 ) ) {
					//Convert the transactions back to the pay stub currency so they can be added together.
					if ( $pst_obj->getCurrency() == $this->getCurrency() ) {
						$amount = $pst_obj->getAmount();
					} else {
						//Since PayStubTransactions currency_rate is only used to get the transaction amount back into the base currency, we have to convert to the base currency first,
						//  then use the PayStub currency_rate to convert into the pay stub currency.
						$amount = $this->getCurrencyObject()->convert( $this->getCurrencyRate(), 1, $this->getCurrencyObject()->convert( 1, $pst_obj->getCurrencyRate(), $pst_obj->getAmount(), 10 ), $this->getCurrencyObject()->getRoundDecimalPlaces() ); //Convert back to Pay Stub's currency.
					}

					Debug::Text( '   PayStubTransction ID: ' . $pst_obj->getId() .' Raw Amount: '. $pst_obj->getAmount()  .' Currency Converted Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10 );
					$total = TTMath::add( $amount, $total );
				}
			}
		}

		return $total;
	}

	/**
	 * @return array
	 */
	function getDeductionSum() {
		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 20 );
		Debug::Text( 'Deduction Sum: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * @return array
	 */
	function getEmployerDeductionSum() {
		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 30 );
		Debug::Text( 'Employer Deduction Sum: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * @return bool|int|mixed
	 */
	function getGrossPay() {
		if ( !is_object( $this->getPayStubEntryAccountLinkObject() ) || $this->getPayStubEntryAccountLinkObject()->getTotalGross() == TTUUID::getZeroID() ) {
			return false;
		}

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $this->getPayStubEntryAccountLinkObject()->getTotalGross() );
		Debug::Text( 'Gross Pay: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retarr['amount'] == '' ) {
			$retarr['amount'] = 0;
		}

		return $retarr['amount'];
	}

	/**
	 * @return bool|int|mixed
	 */
	function getDeductions() {
		if ( !is_object( $this->getPayStubEntryAccountLinkObject() ) || $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction() == TTUUID::getZeroID() ) {
			return false;
		}

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction() );
		Debug::Text( 'Deductions: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retarr['amount'] == '' ) {
			$retarr['amount'] = 0;
		}

		return $retarr['amount'];
	}

	/**
	 * @return bool|int|mixed
	 */
	function getEmployerDeductions() {
		if ( !is_object( $this->getPayStubEntryAccountLinkObject() ) || $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction() == TTUUID::getZeroID() ) {
			return false;
		}

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction() );
		Debug::Text( 'Employer Deductions: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retarr['amount'] == '' ) {
			$retarr['amount'] = 0;
		}

		return $retarr['amount'];
	}

	/**
	 * @return bool|int|mixed
	 */
	function getNetPay() {
		if ( !is_object( $this->getPayStubEntryAccountLinkObject() ) || $this->getPayStubEntryAccountLinkObject()->getTotalNetPay() == TTUUID::getZeroID() ) {
			return false;
		}

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $this->getPayStubEntryAccountLinkObject()->getTotalNetPay() );
		Debug::Text( 'Net Pay: ' . $retarr['amount'], __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retarr['amount'] == '' ) {
			$retarr['amount'] = 0;
		}

		return $retarr['amount'];
	}

	/**
	 * @return bool
	 */
	function checkNoEarnings() {
		$earnings = $this->getEarningSum();
		if ( $earnings == false || $earnings['amount'] <= 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns TRUE unless Amount explicitly does not match Gross Pay
	 * use checkNoEarnings to see if any earnings exist or not.
	 * @return bool
	 */
	function checkEarnings() {
		$earnings = $this->getEarningSum();
		if ( isset( $earnings['amount'] ) && $earnings['amount'] != $this->getGrossPay() ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function checkTransactions() {
		$pst_total = $this->getTransactionsSum();
		$net_pay_arr = $this->getNetPaySum();
		if ( isset( $net_pay_arr['amount'] ) ) {
			$net_pay = $net_pay_arr['amount'];
		}

		if ( isset( $pst_total ) && isset( $net_pay ) && $pst_total != $net_pay ) {
			Debug::Text( 'Mismatched Net Pay / Transaction total: ' . $pst_total . ' Net Pay: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function checkDeductions() {
		$deductions = $this->getDeductionSum();
		if ( $deductions['amount'] != $this->getDeductions() ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function checkNetPay() {
		$net_pay = $this->getNetPay();
		$tmp_net_pay = TTMath::sub( $this->getGrossPay(), $this->getDeductions() );
		Debug::Text( 'aCheck Net Pay: Net Pay: ' . $net_pay . ' Tmp Net Pay: ' . $tmp_net_pay, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $net_pay == $tmp_net_pay ) {
			return true;
		}

		Debug::Text( 'Check Net Pay: Returning false', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function checkNegativeNetPay() {
		$net_pay = $this->getNetPay();
		Debug::Text( 'Check Negative Net Pay: Net Pay: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $net_pay >= 0 ) {
			return true;
		}

		Debug::Text( 'Check Negative Net Pay: Returning false', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function checkZeroNetPay() {
		$net_pay = $this->getNetPay();
		Debug::Text( 'Check Zero Net Pay: Net Pay: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $net_pay == 0 ) {
			return true;
		}

		Debug::Text( 'Check Zero Net Pay: Returning false', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Returns total number of entries on the current pay stub.
	 * @return int
	 */
	function getTotalEntries() {
		$total = 0;

		if ( isset( $this->tmp_data['current_pay_stub']['entries'] ) ) {
			foreach( $this->tmp_data['current_pay_stub']['entries'] as $entry ) {
				if ( $entry['pay_stub_entry_type_id'] != 40 ) { //Skip all total entries as many of those will be there no matter what.
					$total++;
				}
			}
		}

		return $total;
	}

	/**
	 * For the api to edit transactions ensure that you validate at the API before calling this method.
	 * @param object $pst_obj
	 * @return bool
	 */
	function addTransaction( $pst_obj ) {
		if ( is_object( $pst_obj ) ) {
			$this->tmp_data['current_pay_stub']['transactions'][] = $pst_obj;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function syncPendingPayStubTransactionDates( $data_diff ) {
		$retval = false;
		if ( $this->isDataDifferent( 'transaction_date', $data_diff ) == true ) {
			$this->loadCurrentPayStubTransactions();
			if ( $this->getTotalPendingTransactions() > 0 ) {
				Debug::Text( 'Sync Pay Stub Transaction to Pending Transactions: ' . TTDate::getDate( 'DATE+TIME', $this->getTransactionDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $this->tmp_data['current_pay_stub']['transactions'] as $pst_obj ) {
					//Syncing only if old pay stub transaction date matches current pay stub transaction transaction date. Allowing user to change indivual transaction dates without auto sync.
					if ( $pst_obj->getStatus() == 10 && $this->getGenericOldDataValue( 'transaction_date' ) != false && TTDate::getMiddleDayEpoch( $pst_obj->getTransactionDate() ) == TTDate::getMiddleDayEpoch( TTDate::strtotime( $this->getGenericOldDataValue( 'transaction_date' ) ) ) ) { // 10 = pending
						$pst_obj->setTransactionDate( $this->getTransactionDate() );
						$retval = true;
					}
				}
			}
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function savePayStubTransactions() {
		Debug::Text( 'Saving Pay Stub transactions', __FILE__, __LINE__, __METHOD__, 10 );
		if ( isset( $this->tmp_data['current_pay_stub']['transactions'] ) && count( $this->tmp_data['current_pay_stub']['transactions'] ) > 0 ) {
			foreach ( $this->tmp_data['current_pay_stub']['transactions'] as $pst_obj ) {
				//Pass along the current pay stub object rather than having PayStubTransaction have to get it from the database again. This also helps PayStubTransaction determine if this is a new pay stub or not.
				$pst_obj->setPayStubObject( $this );

				$pst_obj->setPayStub( $this->getId() );
				if ( $pst_obj->isValid() ) {
					$pst_obj->Save( false ); //To prevent clearing the object before validation is called.
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function calculateDefaultTransactions() {
		$remaining_amount = $net_pay = $this->getNetPay();

		if ( $net_pay == 0 ) {
			return false; //Nothing to calculate
		}

		$primary_currency_obj = $this->getCurrencyObject();

		$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
		$rdalf->getByUserIdAndStatusId( $this->getUser(), 10 );
		if ( $rdalf->getRecordCount() > 0 ) {
			$rdalf->StartTransaction();

			//Delete any existing transactions, so they can be re-created.
			$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
			$pstlf->getByPayStubId( $this->getId() );
			$pstlf->bulkDelete( $pstlf->getIDSByListFactory( $pstlf ) );

			$max = $rdalf->getRecordCount();
			$i = 1;
			foreach ( $rdalf as $rda_obj ) {
				Debug::Text( 'Destination Account ID: ' . $rda_obj->getId() . ' Amount Type: ' . $rda_obj->getAmountType() . ' Amount: ' . $rda_obj->getAmount() . ' Net Pay: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $remaining_amount != 0 ) {
					if ( is_object( $rda_obj->getRemittanceSourceAccountObject() ) && $rda_obj->getRemittanceSourceAccountObject()->getStatus() == 10 ) { //10=Enabled
						$pstf = TTnew( 'PayStubTransactionFactory' ); /** @var PayStubTransactionFactory $pstf */
						$pstf->setPayStub( $this->getId() );
						$pstf->setStatus( 10 ); //10=Pending
						$pstf->setType( 10 );   //10=Enabled
						$pstf->setRemittanceSourceAccount( $rda_obj->getRemittanceSourceAccount() );
						$pstf->setRemittanceDestinationAccount( $rda_obj->getId() );
						$pstf->setCurrency( $rda_obj->getRemittanceSourceAccountObject()->getCurrency() );
						$pstf->setTransactionDate( $this->getTransactionDate() );

						if ( $i == $max ) {
							$amount = $remaining_amount;
							Debug::Text( ' Final account, using remaining amount...', __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							if ( $rda_obj->getAmountType() == 10 ) { //Percent of Net Pay
								$amount = TTMath::mul( $net_pay, TTMath::div( $rda_obj->getPercentAmount(), 100 ) );
								Debug::Text( ' Percent Amount: ' . $rda_obj->getPercentAmount() . ' of: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );
							} else if ( $rda_obj->getAmountType() == 15 ) { //Percent of Remaining
								$amount = TTMath::mul( $remaining_amount, TTMath::div( $rda_obj->getPercentAmount(), 100 ) );
								Debug::Text( ' Percent Amount: ' . $rda_obj->getPercentAmount() . ' of: ' . $remaining_amount, __FILE__, __LINE__, __METHOD__, 10 );
							} else { //Fixed Amount
								$amount = $rda_obj->getAmount();
								Debug::Text( ' Fixed Amount: ' . $rda_obj->getAmount() . ' of: ' . $net_pay, __FILE__, __LINE__, __METHOD__, 10 );
							}
						}

						if ( $amount > $remaining_amount ) {
							$amount = $remaining_amount;
							Debug::Text( ' Exceeds remaining amount: ' . $amount . ' Remaining: ' . $remaining_amount, __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							$amount = $primary_currency_obj->round( $amount );
						}

						if ( $this->getCurrency() == $pstf->getCurrency() ) {
							$pstf->setAmount( $amount );
						} else {
							$currency_converted_amount = $this->getCurrencyObject()->convert( $this->getCurrencyObject()->getConversionRate(), $rda_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->getConversionRate(), $amount, 10 );
							$currency_converted_rounded_amount = $rda_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->round( $currency_converted_amount );
							$rounding_adjusted_currency_rate = TTMath::add( $pstf->getCurrencyObject()->getReverseConversionRate(), TTMath::sub( TTMath::div( $currency_converted_amount, $currency_converted_rounded_amount, 10 ), 1 ) );

							$pstf->setAmount( $currency_converted_rounded_amount );

							//Recalculate currency rate based on rounded $currency_converted_amount, so we can easily get it back to the exact right rounded value.
							//Currency rate must be the rate to get the amount back into the *non-rounded* base currency.
							//  From there we can use the currency_rate of the pay stub to get the amount back into the pay stub currency.
							//For example if a currency rate is 1USD=9755Leones, and pay stub currency is Leones with a net pay of 429166.67 Leones, that gets converted to 43.99 USD (had to round to nearest penny).
							//  The raw converted currency amount is 43.9945190917 (rounds to: 43.99), so the conversion rate needs to be 1.0001027299 to get 43.99 back to 43.9945190917.
							//  Then based on the pay stub currency rate of 9755, we can get 43.9945190917 back to 429166.67.
							//Even though this is technically two steps when it could be done in one, it keeps the conversion_rate functionality consistent, in that it always converts back to the base rate.
							$pstf->setCurrencyRate( $rounding_adjusted_currency_rate );

							Debug::Text( '   Currency differences... Amount: ' . $amount . ' ( '. $currency_converted_amount .' @ '. $rda_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->getISOCode() .'= R1: '. $this->getCurrencyObject()->getConversionRate() .' R2: '. $rda_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->getConversionRate() .' Adjusted Rate: '. $rounding_adjusted_currency_rate .') Remaining Amount: ' . $remaining_amount, __FILE__, __LINE__, __METHOD__, 10 );
							unset( $currency_converted_amount, $currency_converted_rounded_amount, $rounding_adjusted_currency_rate );
						}

						if ( $pstf->isValid() ) {
							$this->addTransaction( $pstf );
							$remaining_amount = TTMath::sub( $remaining_amount, $amount );
							Debug::Text( ' Amount: ' . $amount . ' Remaining Amount: ' . $remaining_amount, __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( 'ERROR: No remittance source account, or its disabled! Skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'Remaining Amount is 0... Skipping..', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$i++;
			}

			$rdalf->CommitTransaction();
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function sendNotificationPayStub() {
		Debug::Text( 'sendNotificationPayStub: ', __FILE__, __LINE__, __METHOD__, 10 );

		$u_obj = $this->getUserObject();

		//Define subject/body variables here.
		$search_arr = [
				'#employee_first_name#',
				'#employee_last_name#',
				'#employee_default_branch#',
				'#employee_default_department#',
				'#employee_group#',
				'#employee_title#',
				'#company_name#',
				'#link#',
				'#pay_stub_start_date#', //8
				'#pay_stub_end_date#',
				'#pay_stub_transaction_date#',
				'#display_id#',
				'#url#',
		];

		$replace_arr = Misc::escapeHTML( [
				$u_obj->getFirstName(),
				$u_obj->getLastName(),
				( is_object( $u_obj->getDefaultBranchObject() ) ) ? $u_obj->getDefaultBranchObject()->getName() : null,
				( is_object( $u_obj->getDefaultDepartmentObject() ) ) ? $u_obj->getDefaultDepartmentObject()->getName() : null,
				( is_object( $u_obj->getGroupObject() ) ) ? $u_obj->getGroupObject()->getName() : null,
				( is_object( $u_obj->getTitleObject() ) ) ? $u_obj->getTitleObject()->getName() : null,
				( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null,
				null,
				TTDate::getDate( 'DATE', $this->getStartDate() ), //8
				TTDate::getDate( 'DATE', $this->getEndDate() ),
				TTDate::getDate( 'DATE', $this->getTransactionDate() ),
				$this->getDisplayID(),
				( Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() ),
		] );

		$paystub_subject = TTi18n::gettext( 'Pay Stub now available in' ) . ' ' . APPLICATION_NAME;

		$subject = str_replace( $search_arr, $replace_arr, $paystub_subject );

		//$email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
		$email_body = TTi18n::gettext( 'You have a new pay stub available for you in' ) . ' ' . APPLICATION_NAME . "\n";
		$email_body .= "\n";
		$email_body .= ( $replace_arr[8] != '' ) ? TTi18n::gettext( 'Pay Stub Start Date' ) . ': #pay_stub_start_date# ' : null;
		$email_body .= ( $replace_arr[9] != '' ) ? TTi18n::gettext( 'End Date' ) . ': #pay_stub_end_date# ' : null;
		$email_body .= ( $replace_arr[10] != '' ) ? TTi18n::gettext( 'Transaction Date' ) . ': #pay_stub_transaction_date#' . "\n" : null;
		$email_body .= ( $replace_arr[11] != '' ) ? TTi18n::gettext( 'Identification #' ) . ': #display_id#' : null;
		$email_body .= "\n\n";
		$email_body .= ( $replace_arr[2] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #employee_default_branch#' . "\n" : null;
		$email_body .= ( $replace_arr[3] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #employee_default_department#' . "\n" : null;
		$email_body .= ( $replace_arr[4] != '' ) ? TTi18n::gettext( 'Group' ) . ': #employee_group#' . "\n" : null;
		$email_body .= ( $replace_arr[5] != '' ) ? TTi18n::gettext( 'Title' ) . ': #employee_title#' . "\n" : null;
		$email_body .= "\n";
		$email_body .= TTi18n::gettext( 'Link' ) . ': <a href="#url#">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Sign In' ) . '</a>' . "\n";

		$email_body .= NotificationFactory::addEmailFooter( ( ( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null ) );
		$email_body = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $email_body ) . '</pre></body></html>';

		$notification_data = [
				'object_id'      => $this->getId(),
				'user_id'        => $this->getUser(),
				'type_id'        => 'pay_stub',
				'object_type_id' => 20,
				'title_short'    => $subject,
				'body_short'     => TTi18n::gettext( 'You have a new pay stub waiting for you in' ) . ' ' . APPLICATION_NAME,
				'body_long_html' => $email_body, //email
				'payload'        => [ 'timetrex' => [ 'event' => [ [ 'type' => 'open_view', 'data' => [ 'id' => $this->getId() ], 'view_name' => 'PayStub', 'action_name' => 'view' ] ] ], 'link' => Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=PayStub' ],
		];

		Notification::sendNotification( $notification_data );

		return true; //Always return true
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
						case 'tainted': //Don't allow this to be set from the API.
						case 'status_by':
						case 'status_date':
							break;
						case 'start_date':
						case 'end_date':
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
	 * @param bool $permission_children_ids
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'user_group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'city':
						case 'province':
						case 'country':
						case 'currency':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'status':
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/*

		Below here are functions for generating PDF pay stubs

	*/

	/**
	 * @param null $pslf
	 * @param bool $hide_employer_rows
	 * @return bool|string
	 * @noinspection PhpUndefinedConstantInspection
	 */
	function getPayStub( $pslf = null, $hide_employer_rows = true ) {
		if ( !is_object( $pslf ) && $this->getId() != '' ) {
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$pslf->getById( $this->getId() );
		}

		if ( is_a( $pslf, 'PayStubListFactory' ) == false ) {
			return false;
		}

		$border = 0;

		if ( $pslf->getRecordCount() > 0 ) {

			$legal_entity_obj_cache = [];
			$i = 0;
			foreach ( $pslf as $pay_stub_obj ) { /** @var PayStubFactory $pay_stub_obj */
				if ( $i == 0 ) {
					//Mark notifications as read if the currently logged in user is viewing their own employee pay stub one at a time.
					if ( $pslf->getRecordCount() == 1 ) {
						global $current_user;
						if ( is_object( $current_user ) && $current_user->getId() == $pay_stub_obj->getUser() ) {
							NotificationFactory::updateStatusByObjectIdAndObjectTypeId( 20, $pay_stub_obj->getId(), $current_user->getId() ); //20=Pay Stub, Mark any notifications linked to these exceptions as read.
						}
					}

					$pdf = new TTPDF( 'P', 'mm', 'LETTER', $pay_stub_obj->getUserObject()->getCompanyObject()->getEncoding() );
					$pdf->setMargins( 0, 0 ); //Margins are ignored because we use setXY() to force the coordinates before each drawing and therefore ignores margins.
					//$pdf->SetAutoPageBreak(TRUE, 30);
					$pdf->SetAutoPageBreak( false );

					$pdf->SetFont( TTi18n::getPDFDefaultFont( $pay_stub_obj->getUserObject()->getUserPreferenceObject()->getLanguage(), $pay_stub_obj->getUserObject()->getCompanyObject()->getEncoding() ), '', 10 );

					$net_pay_stub_account_entry_id = $pay_stub_obj->getPayStubEntryAccountLinkObject()->getTotalNetPay(); //Optimization.
				}

				$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */

				//Use Pay Stub dates, not Pay Period dates.
				$pp_start_date = $pay_stub_obj->getStartDate();
				$pp_end_date = $pay_stub_obj->getEndDate();
				$pp_transaction_date = $pay_stub_obj->getTransactionDate();

				//Get User information
				$user_obj = $pay_stub_obj->getUserObject();

				//Cache legal entity object to reduce one SQL query per pay stub at least.
				if ( !isset( $legal_entity_obj_cache[$user_obj->getLegalEntity()] ) ) {
					$legal_entity_obj = $legal_entity_obj_cache[$user_obj->getLegalEntity()] = $user_obj->getLegalEntityObject();
				} else {
					$legal_entity_obj = $legal_entity_obj_cache[$user_obj->getLegalEntity()];
				}

				//If printing pay stubs for employees, change locale to users own locale.
				//Otherwise when printing pay stubs for employer, show in employers own locale.
				if ( $hide_employer_rows == true ) {
					TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );
					TTi18n::setCountry( $user_obj->getCountry() );
					TTi18n::setLocale();
				}

				//
				// Pay Stub Header
				//
				$pdf->AddPage();

				$status_text = null;
				if ( $pay_stub_obj->getType() == 100 ) {
					$status_text = TTi18n::gettext( 'OPENING BALANCE' );
				} else {
					//To help avoid fraud, print "SAMPLE" on sample pay stubs when:
					//   The company is less than 2 weeks old AND the pay stub is not marked as PAID AND the employee does not have a SSN/SIN specified.
					if ( DEPLOYMENT_ON_DEMAND == true && ( $pay_stub_obj->getStatus() != 40 || $user_obj->getSIN() == '' ) && $legal_entity_obj->getCreatedDate() > TTDate::incrementDate( time(), -2, 'week' ) ) { //40=Paid
						$status_text = TTi18n::gettext( 'SAMPLE' ) .' '. TTi18n::gettext( 'SAMPLE' );
					}
				}

				if ( $status_text != '' ) {
					//Print important status as watermark on pay stub.
					$pdf->setXY( Misc::AdjustXY( 0, 20 ), Misc::AdjustXY( 0, 240 ) );

					$pdf->StartTransform();
					$pdf->Rotate( 57 );
					$pdf->SetFont( '', 'B', 80 );
					$pdf->setTextColor( 255, 200, 200 );
					$pdf->Cell( 250, 50, $status_text, $border, 0, 'C' );
					$pdf->StopTransform();
					$pdf->setPageMark(); //Must be set to multicells know about the background text.
					$pdf->SetFont( '', '', 10 );
					$pdf->setTextColor( 0, 0, 0 );
					unset( $status_text );
					//Reset pointer to the beginning of the page after watermark is drawn
				}

				$adjust_x = 20;
				$adjust_y = 10;

				//Logo
				$pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );
				@$pdf->Image( $legal_entity_obj->getLogoFileName( null, true, false, 'large' ), Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( 1, $adjust_y ), $pdf->pixelsToUnits( 167 ), $pdf->pixelsToUnits( 42 ), '', '', '', false, 300, '', false, false, 0, true );

				//Company name/address
				$pdf->SetFont( '', 'B', 14 );
				$pdf->setXY( Misc::AdjustXY( 50, $adjust_x ), Misc::AdjustXY( 0, $adjust_y ) );
				$pdf->Cell( 75, 5, $legal_entity_obj->getTradeName(), $border, 0, 'C', false, '', 1 );

				$pdf->SetFont( '', '', 10 );
				$pdf->setXY( Misc::AdjustXY( 50, $adjust_x ), Misc::AdjustXY( 5, $adjust_y ) );
				$pdf->Cell( 75, 3, $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2(), $border, 0, 'C', false, '', 1 );

				$pdf->setXY( Misc::AdjustXY( 50, $adjust_x ), Misc::AdjustXY( 8.5, $adjust_y ) );
				$pdf->Cell( 75, 3, Misc::getCityAndProvinceAndPostalCode( $legal_entity_obj->getCity(), $legal_entity_obj->getProvince(), $legal_entity_obj->getPostalCode() ), $border, 0, 'C', false, '', 1 ); //Oregon State requires employer phone number on the pay stubs.

				if ( $legal_entity_obj->getWorkPhone() != '' ) {
					$pdf->setXY( Misc::AdjustXY( 50, $adjust_x ), Misc::AdjustXY( 12, $adjust_y ) );
					$pdf->Cell( 75, 3, TTi18n::gettext( 'Tel' ) . ': ' . $legal_entity_obj->getWorkPhone(), $border, 0, 'C', false, '', 1 ); //Oregon State requires employer phone number on the pay stubs.
				}


				//Pay Period info
				$pdf->SetFont( '', '', 10 );
				$pdf->setXY( Misc::AdjustXY( 125, $adjust_x ), Misc::AdjustXY( 0, $adjust_y ) );
				$pdf->Cell( 30, 5, TTi18n::gettext( 'Pay Start Date' ) . ': ', $border, 0, 'R', false, '', 1 );
				$pdf->setXY( Misc::AdjustXY( 125, $adjust_x ), Misc::AdjustXY( 5, $adjust_y ) );
				$pdf->Cell( 30, 5, TTi18n::gettext( 'Pay End Date' ) . ': ', $border, 0, 'R', false, '', 1 );
				$pdf->setXY( Misc::AdjustXY( 125, $adjust_x ), Misc::AdjustXY( 10, $adjust_y ) );
				$pdf->Cell( 30, 5, TTi18n::gettext( 'Payment Date' ) . ': ', $border, 0, 'R', false, '', 1 );

				$pdf->SetFont( '', 'B', 10 );
				$pdf->setXY( Misc::AdjustXY( 155, $adjust_x ), Misc::AdjustXY( 0, $adjust_y ) );
				$pdf->Cell( 20, 5, TTDate::getDate( 'DATE', $pp_start_date ), $border, 0, 'R', false, '', 1 );
				$pdf->setXY( Misc::AdjustXY( 155, $adjust_x ), Misc::AdjustXY( 5, $adjust_y ) );
				$pdf->Cell( 20, 5, TTDate::getDate( 'DATE', $pp_end_date ), $border, 0, 'R', false, '', 1 );
				$pdf->setXY( Misc::AdjustXY( 155, $adjust_x ), Misc::AdjustXY( 10, $adjust_y ) );
				$pdf->Cell( 20, 5, TTDate::getDate( 'DATE', $pp_transaction_date ), $border, 0, 'R', false, '', 1 );

				//Line
				$pdf->setLineWidth( 1 );
				$pdf->Line( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( 17, $adjust_y ), Misc::AdjustXY( 185, $adjust_y ), Misc::AdjustXY( 17, $adjust_y ) );
				$pdf->setLineWidth( 0 );

				$pdf->SetFont( '', 'B', 14 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( 19, $adjust_y ) );
				$pdf->Cell( 175, 5, TTi18n::gettext( 'STATEMENT OF EARNINGS AND DEDUCTIONS' ), $border, 0, 'C', false, '', 1 );

				//Line
				$pdf->setLineWidth( 1 );
				$pdf->Line( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( 27, $adjust_y ), Misc::AdjustXY( 185, $adjust_y ), Misc::AdjustXY( 27, $adjust_y ) );

				$pdf->setLineWidth( 0.25 );

				if ( $pay_stub_obj->getType() == 100 ) {
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( 30, $adjust_y ) );
					$pdf->SetFont( '', 'B', 35 );
					$pdf->setTextColor( 255, 0, 0 );
					$pdf->Cell( 175, 12, TTi18n::getText( 'VOID' ), $border, 0, 'C' );
					$pdf->SetFont( '', '', 10 );
					$pdf->setTextColor( 0, 0, 0 );
				}

				//Get pay stub entries.
				$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
				$pself->getByPayStubId( $pay_stub_obj->getId() );
				Debug::text( 'Pay Stub Entries: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

				$max_widths = [ 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 ];
				$prev_type = null;
				$description_subscript_counter = 1;
				foreach ( $pself as $pay_stub_entry ) {
					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10);
					$description_subscript = null;

					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();

					//Use this to put the total for each type at the end of the array.
					//Check for prev_type=NULL/!isset($type) in case there are only Total Gross entries for $0.
					if ( $prev_type == 40 || $pay_stub_entry_name_obj->getType() != 40 || ( $prev_type == null && !isset( $type ) ) ) {
						$type = $pay_stub_entry_name_obj->getType();
					}
					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);

					if ( $pay_stub_entry->getDescription() !== null
							&& $pay_stub_entry->getDescription() !== false
							&& strlen( $pay_stub_entry->getDescription() ) > 0
							&& ( $type != 30 || ( $type == 30 && $hide_employer_rows == false ) ) ) {     //Make sure PSA descriptions are not shown on employee pay stubs.
						$pay_stub_entry_descriptions[] = [
								'subscript'   => $description_subscript_counter,
								'description' => $pay_stub_entry->getDescription(),
						];

						$description_subscript = $description_subscript_counter;

						$description_subscript_counter++;
					}

					//If type is 40 (a total) and the amount is 0, skip it.
					//In cases where the employee has no deductions at all, it won't be displayed on the pay stub.
					// The != 0 check is only for TOTAL line items. If we use >= 0 instead we need to rework how netpay is displayed when no deductions exist.
					if ( $type != 40 || ( $type == 40
									&& ( $pay_stub_entry->getAmount() != 0 || ( isset( $net_pay_stub_account_entry_id ) && $pay_stub_entry->getPayStubEntryNameId() == $net_pay_stub_account_entry_id ) ) ) ) {
						$pay_stub_entries[$type][] = [
								'id'                     => $pay_stub_entry->getId(),
								'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),
								'type'                   => $pay_stub_entry_name_obj->getType(),
								'name'                   => $pay_stub_entry_name_obj->getName(),
								'display_name'           => $pay_stub_entry_name_obj->getName(),
								'rate'                   => $pay_stub_entry->getRate(),
								'units'                  => $pay_stub_entry->getUnits(),
								'ytd_units'              => $pay_stub_entry->getYTDUnits(),
								'amount'                 => $pay_stub_entry->getAmount(),
								'ytd_amount'             => $pay_stub_entry->getYTDAmount(),

								'description'           => $pay_stub_entry->getDescription(),
								'description_subscript' => $description_subscript,

								'created_date' => $pay_stub_entry->getCreatedDate(),
								'created_by'   => $pay_stub_entry->getCreatedBy(),
								'updated_date' => $pay_stub_entry->getUpdatedDate(),
								'updated_by'   => $pay_stub_entry->getUpdatedBy(),
								'deleted_date' => $pay_stub_entry->getDeletedDate(),
								'deleted_by'   => $pay_stub_entry->getDeletedBy(),
						];

						//Calculate maximum widths of numeric values.
						$width_units = strlen( $pay_stub_entry->getUnits() );
						if ( $width_units > $max_widths['units'] ) {
							$max_widths['units'] = $width_units;
						}

						$width_rate = strlen( $pay_stub_entry->getRate() );
						if ( $width_rate > $max_widths['rate'] ) {
							$max_widths['rate'] = $width_rate;
						}

						$width_amount = strlen( $pay_stub_entry->getAmount() );
						if ( $width_amount > $max_widths['amount'] ) {
							$max_widths['amount'] = $width_amount;
						}

						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );
						if ( $width_amount > $max_widths['ytd_amount'] ) {
							$max_widths['ytd_amount'] = $width_ytd_amount;
						}

						unset( $width_rate, $width_units, $width_amount, $width_ytd_amount );
					}

					$prev_type = $pay_stub_entry_name_obj->getType();
				}

				//There should always be pay stub entries for a pay stub.
				if ( !isset( $pay_stub_entries ) ) {
					continue;
				}
				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get Accrual Balance records here so we can use it for sizing the font.
				$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
				$ablf->getByUserIdAndCompanyIdAndStartDateAndEndDateAndEnablePayStubBalanceDisplay( $user_obj->getId(), $user_obj->getCompany(), $pp_start_date, $pp_end_date, true );

				//Get Transaction records here so we can use it for sizing the font.
				$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
				if ( $hide_employer_rows != true ) {
					$pstlf->getByPayStubIdAndStatusId( $pay_stub_obj->getId(), [ 10, 20 ] ); //10=Pending, 20=Paid
				} else {
					$pstlf->getByPayStubIdAndStatusId( $pay_stub_obj->getId(), 20 ); //20=Paid
				}

				//Calculate font size based on number of records to display
				$total_pay_stub_rows = 0;
				$total_pay_stub_rows += ( $ablf->getRecordCount() > 0 ) ? ( $ablf->getRecordCount() + 1 ) : 0;
				$total_pay_stub_rows += ( $pstlf->getRecordCount() > 0 ) ? ( $pstlf->getRecordCount() + 2 ) : 0;
				$total_pay_stub_rows += ( isset( $pay_stub_entries[10] ) ) ? ( count( $pay_stub_entries[10] ) + 2 ) : 0;
				$total_pay_stub_rows += ( isset( $pay_stub_entries[20] ) ) ? ( ceil( count( $pay_stub_entries[20] ) / 2 ) + 2 ) : 0;
				$total_pay_stub_rows += ( isset( $pay_stub_entries[50] ) ) ? ( count( $pay_stub_entries[50] ) + 1 ) : 0;
				$total_pay_stub_rows += ( isset( $pay_stub_entries[80] ) ) ? ( ceil( count( $pay_stub_entries[80] ) / 2 ) + 1 ) : 0;
				$total_pay_stub_rows += ( isset( $pay_stub_entry_descriptions ) ) ? ( ceil( count( $pay_stub_entry_descriptions ) / 2 ) + 1 ) : 0;
				if ( $hide_employer_rows != true ) {
					$total_pay_stub_rows += ( isset( $pay_stub_entries[30] ) ) ? ( ceil( count( $pay_stub_entries[30] ) / 2 ) + 2 ) : 0;
				}

				if ( $total_pay_stub_rows == 0 ) {
					$total_pay_stub_rows = 1; //Prevent division by 0 on empty pay stubs.
				}

				$default_line_item_font_size = ( 335 / $total_pay_stub_rows );
				if ( $default_line_item_font_size > 12 ) {
					$default_line_item_font_size = 12;
				} else if ( $default_line_item_font_size < 4 ) {
					$default_line_item_font_size = 4;
				}
				Debug::Text( 'Pay Stub Total Rows: ' . $total_pay_stub_rows . ' Default Font Size: ' . $default_line_item_font_size, __FILE__, __LINE__, __METHOD__, 10 );

				$block_adjust_y = 30;

				//Set Default cell height/width outside of the earnings, especially important if a pay stub has no earnings.
				$cell_height = 10;
				$column_widths['ytd_amount'] = ( ( $max_widths['ytd_amount'] * 2 ) < 25 ) ? 25 : ( $max_widths['ytd_amount'] * 2 );
				$column_widths['amount'] = ( ( $max_widths['amount'] * 2 ) < 20 ) ? 20 : ( $max_widths['amount'] * 2 );
				$column_widths['rate'] = ( ( $max_widths['rate'] * 2 ) < 5 ) ? 5 : ( $max_widths['rate'] * 2 );
				$column_widths['units'] = ( ( $max_widths['units'] * 2 ) < 17 ) ? 17 : ( $max_widths['units'] * 2 );
				$column_widths['name'] = ( 175 - ( $column_widths['ytd_amount'] + $column_widths['amount'] + $column_widths['rate'] + $column_widths['units'] ) );
				//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__, 10);

				//
				//Earnings
				//
				if ( isset( $pay_stub_entries[10] ) ) {
					//Earnings Header
					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Earnings' ), $border, 0, 'L', false, '', 1 );
					$pdf->Cell( $column_widths['rate'], $cell_height, TTi18n::gettext( 'Rate' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['units'], $cell_height, TTi18n::gettext( 'Hrs/Units' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					foreach ( $pay_stub_entries[10] as $pay_stub_entry ) {

						if ( $pay_stub_entry['type'] == 10 ) {
							if ( $pay_stub_entry['description_subscript'] != '' ) {
								$subscript = '[' . $pay_stub_entry['description_subscript'] . ']';
							} else {
								$subscript = null;
							}

							$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
							$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 ); //68
							$pdf->Cell( $column_widths['rate'], $cell_height, ( $pay_stub_entry['rate'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['rate'], true ) : '-', $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['units'], $cell_height, ( $pay_stub_entry['units'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['units'], true ) : '-', $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, ( $pay_stub_entry['ytd_amount'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) : '-', $border, 0, 'R', false, '', 1 );
						} else {
							//Total
							$pdf->SetFont( '', 'B', $default_line_item_font_size );
							$cell_height = $pdf->getStringHeight( 10, 'Z' );

							$pdf->line( Misc::AdjustXY( ( ( 175 - ( $column_widths['ytd_amount'] ) - $column_widths['amount'] ) - $column_widths['units'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( ( 175 - ( 1 + $column_widths['ytd_amount'] ) - $column_widths['amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) ); //90
							$pdf->line( Misc::AdjustXY( ( 175 - ( $column_widths['ytd_amount'] ) - $column_widths['amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( ( 175 - ( 1 + $column_widths['ytd_amount'] ) ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );                                                          //111
							$pdf->line( Misc::AdjustXY( ( 175 - $column_widths['ytd_amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( 175, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );                                                                                                                                    //141
							$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
							$pdf->Cell( $column_widths['name'], $cell_height, $pay_stub_entry['name'], $border, 0, 'L', false, '', 1 );
							$pdf->Cell( $column_widths['rate'], $cell_height, '', $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['units'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['units'], true ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
						}

						$block_adjust_y = ( $block_adjust_y + $cell_height );
					}
				}

				//
				// Deductions
				//
				if ( isset( $pay_stub_entries[20] ) ) {
					$max_deductions = count( $pay_stub_entries[20] );

					//Deductions Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					if ( $max_deductions > 2 ) {
						$column_widths['name'] = ( 85 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Deductions' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

						$pdf->setXY( Misc::AdjustXY( 90, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Deductions' ), $border, 0, 'L', false, '', 1 );
					} else {
						$column_widths['name'] = ( 175 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Deductions' ), $border, 0, 'L', false, '', 1 );
					}

					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$x = 0;
					$max_block_adjust_y = 0;
					foreach ( $pay_stub_entries[20] as $pay_stub_entry ) {
						//Start with the left side, so Pay Stub Account order is maintained left to right.
						if ( $max_deductions > 2 && $x < floor( ( ($max_deductions - 1) / 2 ) ) ) { //Minus 1 as the Total Deductions entry is included in the count.
							$tmp_adjust_x = 0;
						} else {
							if ( $tmp_block_adjust_y != 0 ) {
								$block_adjust_y = $tmp_block_adjust_y;
								$tmp_block_adjust_y = 0;
							}
							$tmp_adjust_x = 90;
						}

						if ( $pay_stub_entry['type'] == 20 ) {
							if ( $pay_stub_entry['description_subscript'] != '' ) {
								$subscript = '[' . $pay_stub_entry['description_subscript'] . ']';
							} else {
								$subscript = null;
							}

							if ( $max_deductions > 2 ) {
								$pdf->setXY( Misc::AdjustXY( 2, ( $tmp_adjust_x + $adjust_x ) ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 );
							} else {
								$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 );
							}
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, ( $pay_stub_entry['ytd_amount'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) : '-', $border, 0, 'R', false, '', 1 );
							//Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							$block_adjust_y = $max_block_adjust_y;

							//Total
							$pdf->SetFont( '', 'B', $default_line_item_font_size );
							$cell_height = $pdf->getStringHeight( 10, 'Z' );

							$pdf->line( Misc::AdjustXY( ( 175 - ( $column_widths['ytd_amount'] ) - $column_widths['amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( ( 175 - ( 1 + $column_widths['ytd_amount'] ) ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) ); //111
							$pdf->line( Misc::AdjustXY( ( 175 - $column_widths['ytd_amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( 175, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );                                                                           //141

							$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
							$pdf->Cell( ( 175 - ( $column_widths['amount'] + $column_widths['ytd_amount'] ) ), $cell_height, $pay_stub_entry['name'], $border, 0, 'L', false, '', 1 ); //110
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
						}

						$block_adjust_y = ( $block_adjust_y + $cell_height );
						if ( $block_adjust_y > $max_block_adjust_y ) {
							$max_block_adjust_y = $block_adjust_y;
						}

						$x++;
					}

					//Draw line to separate the two columns
					if ( $max_deductions > 2 ) {
						$pdf->Line( Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $top_block_adjust_y - $cell_height ), $adjust_y ), Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $max_block_adjust_y - $cell_height ), $adjust_y ) );
					}

					unset( $x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y );
				}

				if ( isset( $pay_stub_entries[40][0] ) ) {
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					//Net Pay entry
					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );

					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( ( 175 - ( $column_widths['amount'] + $column_widths['ytd_amount'] ) ), $cell_height, $pay_stub_entries[40][0]['name'], $border, 0, 'L', false, '', 1 );
					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = ( $block_adjust_y + $cell_height );
				}

				//
				//Miscellaneous
				//
				if ( isset( $pay_stub_entries[80] ) ) {
					$max_deductions = count( $pay_stub_entries[80] );
					//Deductions Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					if ( $max_deductions > 2 ) {
						$column_widths['name'] = ( 85 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Miscellaneous' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

						$pdf->setXY( Misc::AdjustXY( 90, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Miscellaneous' ), $border, 0, 'L', false, '', 1 );
					} else {
						$column_widths['name'] = ( 175 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Miscellaneous' ), $border, 0, 'L', false, '', 1 );
					}

					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$x = 1;
					$max_block_adjust_y = 0;

					foreach ( $pay_stub_entries[80] as $pay_stub_entry ) {
						//Start with the left side, so Pay Stub Account order is maintained left to right.
						if ( $max_deductions > 2 && $x <= floor( $max_deductions / 2 ) ) {
							$tmp_adjust_x = 0;
						} else {
							if ( $tmp_block_adjust_y != 0 ) {
								$block_adjust_y = $tmp_block_adjust_y;
								$tmp_block_adjust_y = 0;
							}
							$tmp_adjust_x = 90;
						}

						if ( $pay_stub_entry['type'] == 80 ) {
							if ( $pay_stub_entry['description_subscript'] != '' ) {
								$subscript = '[' . $pay_stub_entry['description_subscript'] . ']';
							} else {
								$subscript = null;
							}

							if ( $max_deductions > 2 ) {
								$pdf->setXY( Misc::AdjustXY( 2, ( $tmp_adjust_x + $adjust_x ) ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 ); //38
							} else {
								$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 ); //128
							}
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, ( $pay_stub_entry['ytd_amount'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) : '-', $border, 0, 'R', false, '', 1 );
						}

						$block_adjust_y = ( $block_adjust_y + $cell_height );
						if ( $block_adjust_y > $max_block_adjust_y ) {
							$max_block_adjust_y = $block_adjust_y;
						}

						$x++;
					}

					//Draw line to separate the two columns
					if ( $max_deductions > 2 ) {
						$pdf->Line( Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $top_block_adjust_y - $cell_height ), $adjust_y ), Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $max_block_adjust_y ), $adjust_y ) );
					}

					unset( $x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y );
				}

				//
				//Employer Contributions
				//
				if ( isset( $pay_stub_entries[30] ) && $hide_employer_rows != true ) {
					$max_deductions = count( $pay_stub_entries[30] );
					//Deductions Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					if ( $max_deductions > 2 ) {
						$column_widths['name'] = ( 85 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Employer Contributions' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

						$pdf->setXY( Misc::AdjustXY( 90, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Employer Contributions' ), $border, 0, 'L', false, '', 1 );
					} else {
						$column_widths['name'] = ( 175 - ( $column_widths['ytd_amount'] + $column_widths['amount'] ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( $column_widths['name'], $cell_height, TTi18n::gettext( 'Employer Contributions' ), $border, 0, 'L', false, '', 1 );
					}

					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'YTD Amount' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$x = 1;
					$max_block_adjust_y = 0;

					foreach ( $pay_stub_entries[30] as $pay_stub_entry ) {
						//Start with the left side, so Pay Stub Account order is maintained left to right.
						if ( $max_deductions > 2 && $x <= floor( ( ( $max_deductions - 1 ) / 2 ) ) ) { //Minus 1 as the Total Deductions entry is included in the count.
							$tmp_adjust_x = 0;
						} else {
							if ( $tmp_block_adjust_y != 0 ) {
								$block_adjust_y = $tmp_block_adjust_y;
								$tmp_block_adjust_y = 0;
							}
							$tmp_adjust_x = 90;
						}

						if ( $pay_stub_entry['type'] == 30 ) {
							if ( $pay_stub_entry['description_subscript'] != '' ) {
								$subscript = '[' . $pay_stub_entry['description_subscript'] . ']';
							} else {
								$subscript = null;
							}

							if ( $max_deductions > 2 ) {
								$pdf->setXY( Misc::AdjustXY( 2, ( $tmp_adjust_x + $adjust_x ) ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 ); //38
							} else {
								$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
								$pdf->Cell( ( $column_widths['name'] - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 ); //128
							}
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, ( $pay_stub_entry['ytd_amount'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) : '-', $border, 0, 'R', false, '', 1 );
						} else {
							$block_adjust_y = $max_block_adjust_y;

							//Total
							$pdf->SetFont( '', 'B', $default_line_item_font_size );

							$pdf->line( Misc::AdjustXY( ( 175 - ( $column_widths['ytd_amount'] ) - $column_widths['amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( ( 175 - ( 1 + $column_widths['ytd_amount'] ) ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) ); //111
							$pdf->line( Misc::AdjustXY( ( 175 - $column_widths['ytd_amount'] ), $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( 175, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );                                                                           //141

							$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
							$pdf->Cell( ( 175 - ( $column_widths['amount'] + $column_widths['ytd_amount'] ) ), $cell_height, $pay_stub_entry['name'], $border, 0, 'L', false, '', 1 );
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
						}

						$block_adjust_y = ( $block_adjust_y + $cell_height );
						if ( $block_adjust_y > $max_block_adjust_y ) {
							$max_block_adjust_y = $block_adjust_y;
						}

						$x++;
					}

					//Draw line to separate the two columns
					if ( $max_deductions > 2 ) {
						$pdf->Line( Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $top_block_adjust_y - $cell_height ), $adjust_y ), Misc::AdjustXY( 88, $adjust_x ), Misc::AdjustXY( ( $max_block_adjust_y - $cell_height ), $adjust_y ) );
					}

					unset( $x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y );
				}

				//
				//Accruals PS accounts
				//
				if ( isset( $pay_stub_entries[50] ) ) {
					//Accrual Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( ( 175 - ( $column_widths['amount'] + $column_widths['ytd_amount'] ) ), $cell_height, TTi18n::gettext( 'Accruals' ), $border, 0, 'L', false, '', 1 );
					$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( $column_widths['ytd_amount'], $cell_height, TTi18n::gettext( 'Balance' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					foreach ( $pay_stub_entries[50] as $pay_stub_entry ) {

						if ( $pay_stub_entry['type'] == 50 ) {
							if ( $pay_stub_entry['description_subscript'] != '' ) {
								$subscript = '[' . $pay_stub_entry['description_subscript'] . ']';
							} else {
								$subscript = null;
							}

							$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
							$pdf->Cell( ( 175 - ( $column_widths['amount'] + $column_widths['ytd_amount'] ) - 2 ), $cell_height, $pay_stub_entry['name'] . $subscript, $border, 0, 'L', false, '', 1 );
							$pdf->Cell( $column_widths['amount'], $cell_height, TTi18n::formatNumber( $pay_stub_entry['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ), $border, 0, 'R', false, '', 1 );
							$pdf->Cell( $column_widths['ytd_amount'], $cell_height, ( $pay_stub_entry['ytd_amount'] != 0 ) ? TTi18n::formatNumber( $pay_stub_entry['ytd_amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) : '-', $border, 0, 'R', false, '', 1 );
						}

						$block_adjust_y = ( $block_adjust_y + $cell_height );
					}
				}


				/*
				Name        Amount (USD)     Amount
				Check       $100.00 		 $70.00 USD
				Check       $100.00 @ 0.7232 $70.00 CAD

				Name      				       Amount
				Check        		 		  $100.00 USD
				Check ($100.00CAD @ 0.7232)    $70.00 USD


				1 USD = 0.88586 EUR (Inverse: 1.12884)
				1 USD = 1.30736 CAD (Inverse: 0.76490)

				1 EUR = 1.47553 CAD
				1 EUR = 1.47553 CAD	1 CAD = 0.677725 EUR

				1 CAD =	0.677733 EUR
				1 CAD = 0.677733 EUR 1 EUR = 1.47551 CAD

				Do Pay stub transactions need to currency_rates? currency_rate to go back to the base currency, and pay_stub_currency_rate to go back to the pay stub currency amount?
				*/

				//
				//Transactions
				//
				if ( $pstlf->getRecordCount() > 0 ) {
					//Transaction Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );

					$pdf->setXY( Misc::AdjustXY( 1, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );

					$transaction_header_start_x = $pdf->getX();
					$transaction_header_start_y = $pdf->getY();

					$multiple_transaction_currency = false;
					foreach ( $pstlf as $pst_obj ) {
						if ( $pay_stub_obj->getCurrency() != $pst_obj->getCurrency() ) {
							$multiple_transaction_currency = true;
							break;
						}
					}

					if ( $multiple_transaction_currency == true ) {
						$pdf->Cell( 45, $cell_height, TTi18n::gettext( 'Payments' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( 20, $cell_height, TTi18n::gettext( 'Type' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 23, $cell_height, TTi18n::gettext( 'Confirm #' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 50, $cell_height, TTi18n::gettext( 'Currency Conversion' ), $border, 0, 'C', false, '', 1 );
						$pdf->Cell( 35, $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					} else {
						$pdf->Cell( 55, $cell_height, TTi18n::gettext( 'Payments' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( 30, $cell_height, TTi18n::gettext( 'Type' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 44, $cell_height, TTi18n::gettext( 'Confirm #' ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 44, $cell_height, TTi18n::gettext( 'Amount' ), $border, 0, 'R', false, '', 1 );
					}

					$block_adjust_y = ( $block_adjust_y + $cell_height );
					$box_height = $cell_height;

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					foreach ( $pstlf as $pst_obj ) { /** @var PayStubTransactionFactory $pst_obj */
						$rda_obj = $pst_obj->getRemittanceDestinationAccountObject();
						$rsa_obj = $pst_obj->getRemittanceSourceAccountObject();
						if ( is_object( $rda_obj ) ) {

							$cross_conversion_rate = $pay_stub_obj->getCurrencyObject()->getCrossConversionRate( $pay_stub_obj->getCurrencyObject()->getConversionRate(), $pst_obj->getCurrencyObject()->getConversionRate() );
							Debug::Text( 'Transaction Currency: ' . $pst_obj->getCurrencyObject()->getISOCode() . '(' . $pst_obj->getCurrencyObject()->getConversionRate() . ') Pay Stub Currency: ' . $pay_stub_obj->getCurrencyObject()->getISOCode() . '(' . $pay_stub_obj->getCurrencyObject()->getConversionRate() . ') Cross Rate: ' . $cross_conversion_rate, __FILE__, __LINE__, __METHOD__, 10 );
							$pdf->setXY( Misc::AdjustXY( 1, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );

							if ( $multiple_transaction_currency == true ) {
								$pdf->Cell( 45, $cell_height, $rda_obj->getName(), $border, 0, 'L', false, '', 1 );
								$pdf->Cell( 20, $cell_height, Option::getByKey( $rsa_obj->getType(), $rsa_obj->getOptions( 'type' ) ), $border, 0, 'R', false, '', 1 );
								$pdf->Cell( 23, $cell_height, $pst_obj->getConfirmationNumber(), $border, 0, 'R', false, '', 1 );
								$pdf->Cell( 25, $cell_height, $pay_stub_obj->getCurrencyObject()->getSymbol() . TTi18n::formatNumber( $pay_stub_obj->getCurrencyObject()->convert( $pst_obj->getCurrencyObject()->getConversionRate(), $pay_stub_obj->getCurrencyObject()->getConversionRate(), $pst_obj->getAmount() ), true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() ) . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 0, 'R', false, '', 1 );
								$pdf->Cell( 25, $cell_height, '@ ' . TTi18n::formatNumber( $cross_conversion_rate, true, 2, 6 ), $border, 0, 'L', false, '', 1 );
								$pdf->Cell( 35, $cell_height, $pst_obj->getCurrencyObject()->getSymbol() . TTi18n::formatNumber( $pst_obj->getAmount(), true, $pst_obj->getCurrencyObject()->getRoundDecimalPlaces() ) . ' ' . $pst_obj->getCurrencyObject()->getISOCode(), $border, 0, 'R', false, '', 1 );
							} else {
								$pdf->Cell( 55, $cell_height, $rda_obj->getName(), $border, 0, 'L', false, '', 1 );
								$pdf->Cell( 30, $cell_height, Option::getByKey( $rsa_obj->getType(), $rsa_obj->getOptions( 'type' ) ), $border, 0, 'R', false, '', 1 );
								$pdf->Cell( 44, $cell_height, ( ( $pst_obj->getStatus() == 20 ) ? $pst_obj->getConfirmationNumber() : Option::getByKey( $pst_obj->getStatus(), $pst_obj->getOptions( 'status' ) ) ), $border, 0, 'R', false, '', 1 );

								$stop_payment = ( ( in_array( $pst_obj->getStatus(), [ 100, 200 ] ) ) ? TTi18n::getText( 'SP' ) . ' ' : '' );
								$pdf->Cell( 44, $cell_height, $stop_payment . $pst_obj->getCurrencyObject()->getSymbol() . TTi18n::formatNumber( $pay_stub_obj->getCurrencyObject()->convert( $pst_obj->getCurrencyObject()->getConversionRate(), $pay_stub_obj->getCurrencyObject()->getConversionRate(), $pst_obj->getAmount() ), true, $pst_obj->getCurrencyObject()->getRoundDecimalPlaces() ) . ' ' . $pst_obj->getCurrencyObject()->getISOCode(), $border, 0, 'R', false, '', 1 );
							}

							$block_adjust_y = ( $block_adjust_y + $cell_height );
							$box_height = ( $box_height + $cell_height );
						}
					}
//					$pdf->Rect( $transaction_header_start_x, $transaction_header_start_y, 173, $box_height, NULL, array('all' => array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => '20,5', 'phase' => 10 ) ) );
					$pdf->Rect( $transaction_header_start_x, $transaction_header_start_y, 173, $box_height, null, [ 'all' => [ 'width' => 0.25 ] ] );
					$pdf->Rect( ( $transaction_header_start_x - 1 ), ( $transaction_header_start_y - 1 ), 175, ( $box_height + 2 ), null, [ 'all' => [ 'width' => 0.25 ] ] );
					$pdf->setLineStyle( [ 'width' => 0.5 ] ); //Reset LineStyle back to default.

					unset( $transaction_header_start_x, $transaction_header_start_y, $box_height, $rda_obj, $rsa_obj, $pst_obj, $cross_conversion_rate );
				}


				//
				//Accrual Account Balances
				//
				if ( $ablf->getRecordCount() > 0 ) {
					//Accrual Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );

					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );

					$accrual_time_header_start_x = $pdf->getX();
					$accrual_time_header_start_y = $pdf->getY();

					$pdf->Cell( 70, $cell_height, TTi18n::gettext( 'Accrual Balances (Hours)' ), $border, 0, 'L', false, '', 1 );
					$pdf->Cell( 20, $cell_height, TTi18n::gettext( 'Accrued' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( 17, $cell_height, TTi18n::gettext( 'YTD' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( 6, $cell_height, '', $border, 0, 'R', false, '', 1 );
					$pdf->Cell( 20, $cell_height, TTi18n::gettext( 'Reduced' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( 17, $cell_height, TTi18n::gettext( 'YTD' ), $border, 0, 'R', false, '', 1 );
					$pdf->Cell( 25, $cell_height, TTi18n::gettext( 'Balance' ), $border, 0, 'R', false, '', 1 );

					$block_adjust_y = ( $block_adjust_y + $cell_height );
					$top_block_adjust_y = $block_adjust_y + 2;
					$box_height = $cell_height;

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					foreach ( $ablf as $ab_obj ) {
						$balance = TTMath::sub( $ab_obj->getBalance(), $ab_obj->getColumn( 'future_balance_amount' ) );

						$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						$pdf->Cell( 70, $cell_height, $ab_obj->getColumn( 'name' ), $border, 0, 'L', false, '', 1 );
						$pdf->Cell( 20, $cell_height, TTi18n::formatNumber( TTDate::getHours( $ab_obj->getColumn( 'accrued_amount' ) ), true, 2, 2 ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 17, $cell_height, TTi18n::formatNumber( TTDate::getHours( $ab_obj->getColumn( 'accrued_amount_ytd' ) ), true, 2, 2 ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 6, $cell_height, '', $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 20, $cell_height, TTi18n::formatNumber( TTDate::getHours( $ab_obj->getColumn( 'used_amount' ) ), true, 2, 2 ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 17, $cell_height, TTi18n::formatNumber( TTDate::getHours( $ab_obj->getColumn( 'used_amount_ytd' ) ), true, 2, 2 ), $border, 0, 'R', false, '', 1 );
						$pdf->Cell( 25, $cell_height, TTi18n::formatNumber( TTDate::getHours( $balance ), true, 2, 2 ), $border, 0, 'R', false, '', 1 );

						$block_adjust_y = ( $block_adjust_y + $cell_height );
						$box_height = ( $box_height + $cell_height );
						unset( $balance, $accrued_amount, $used_amount );
					}
					$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 175, $box_height );

					$pdf->setLineWidth( 0.10 );
					$pdf->Line( Misc::AdjustXY( 110, $adjust_x ), floor( Misc::AdjustXY( ( $top_block_adjust_y - $cell_height ), $adjust_y ) ), Misc::AdjustXY( 110, $adjust_x ), floor( Misc::AdjustXY( $top_block_adjust_y + ( $cell_height * $ablf->getRecordCount() ) - 3, $adjust_y ) ) );
					$pdf->Line( Misc::AdjustXY( 153, $adjust_x ), floor( Misc::AdjustXY( ( $top_block_adjust_y - $cell_height ), $adjust_y ) ), Misc::AdjustXY( 153, $adjust_x ), floor( Misc::AdjustXY( $top_block_adjust_y + ( $cell_height * $ablf->getRecordCount() ) - 3, $adjust_y ) ) );

					unset( $accrual_time_header_start_x, $accrual_time_header_start_y, $box_height );
				}


				//
				//Descriptions
				//
				if ( isset( $pay_stub_entry_descriptions ) && count( $pay_stub_entry_descriptions ) > 0 ) {

					//Description Header
					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', 'B', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 175, $cell_height, TTi18n::gettext( 'Notes' ), $border, 0, 'L', false, '', 1 );

					$block_adjust_y = ( $block_adjust_y + $cell_height );

					$pdf->SetFont( '', '', $default_line_item_font_size );
					$cell_height = $pdf->getStringHeight( 10, 'Z' );
					$x = 0;
					foreach ( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {
						if ( ( $x % 2 ) == 0 ) {
							$pdf->setXY( Misc::AdjustXY( 2, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						} else {
							$pdf->setXY( Misc::AdjustXY( 90, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
						}

						$pdf->Cell( 85, $cell_height, '[' . $pay_stub_entry_description['subscript'] . '] ' . html_entity_decode( $pay_stub_entry_description['description'] ), $border, 0, 'L', false, '', 1 );

						if ( ( $x % 2 ) != 0 ) {
							$block_adjust_y = ( $block_adjust_y + $cell_height );
						}
						$x++;
					}
				}
				unset( $x, $pay_stub_entry_descriptions, $pay_stub_entry_description );


				//
				// Tax information.
				//
				$block_adjust_y = 213;
				$pdf->SetFont( '', '', 6 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 3 ), $adjust_y ) );

				$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
				$udlf->getByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getID() );
				$udlf->getAPISearchByCompanyIdAndArrayCriteria( $user_obj->getCompany(), [ 'status_id' => 10, 'user_id' => $user_obj->getID(), 'calculation_id' => [ 100, 200 ] ] );
				if ( $udlf->getRecordCount() > 0 ) {
					$pdf->setLineWidth( 0.10 );

					//$max_tax_info_rows = ($udlf->getRecordCount() / 2);

					$left_total_rows = 0;
					$right_total_rows = 0;
					foreach ( $udlf as $ud_obj ) {
						if ( $ud_obj->getCompanyDeductionObject()->getCalculation() == 100 ) { //Federal
							$left_total_rows++;
						} else if ( $ud_obj->getCompanyDeductionObject()->getCalculation() == 200 ) { //Province/State
							$right_total_rows++;
						}
					}

					$left_block_adjust_y = $right_block_adjust_y = $block_adjust_y;

					Debug::Text( 'Tax Info Rows: Left: ' . $left_total_rows . ' Right: ' . $right_total_rows . ' Transaction Date: ' . TTDate::getDate( 'DATE', $pp_transaction_date ), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $left_total_rows < $right_total_rows ) {
						for ( $i = 0; $i < ( $right_total_rows - $left_total_rows ); $i++ ) {
							$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $left_block_adjust_y, $adjust_y ) );
							$pdf->Cell( 87.5, 3, '', 1, 0, 'C', false, '', 1 );
							$left_block_adjust_y = ( $left_block_adjust_y - 3 );
						}
					} else if ( $right_total_rows < $left_total_rows ) {
						for ( $i = 0; $i < ( $left_total_rows - $right_total_rows ); $i++ ) {
							$pdf->setXY( Misc::AdjustXY( 87.5, $adjust_x ), Misc::AdjustXY( $right_block_adjust_y, $adjust_y ) );
							$pdf->Cell( 87.5, 3, '', 1, 0, 'C', false, '', 1 );
							$right_block_adjust_y = ( $right_block_adjust_y - 3 );
						}
					}

					foreach ( $udlf as $ud_obj ) {
						if ( $ud_obj->getCompanyDeductionObject()->getCalculation() == 100 ) { //Federal
							$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $left_block_adjust_y, $adjust_y ) );
							$pdf->Cell( 87.5, 3, $ud_obj->getDescription( $pp_transaction_date ), 1, 0, 'C', false, '', 1 );
							$left_block_adjust_y = ( $left_block_adjust_y - 3 );
						}
					}

					foreach ( $udlf as $ud_obj ) {
						if ( $ud_obj->getCompanyDeductionObject()->getCalculation() == 200 ) { //Province/State
							$pdf->setXY( Misc::AdjustXY( 87.5, $adjust_x ), Misc::AdjustXY( $right_block_adjust_y, $adjust_y ) );
							$pdf->Cell( 87.5, 3, $ud_obj->getDescription( $pp_transaction_date ), 1, 0, 'C', false, '', 1 );
							$right_block_adjust_y = ( $right_block_adjust_y - 3 );
						}
					}

					$block_adjust_y = $left_block_adjust_y;

					$pdf->SetFont( '', 'B', 6 );
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 87.5, 3, TTi18n::gettext( 'Federal' ), 'LB', 0, 'C', false, '', 1 );

					$pdf->SetFont( '', 'B', 6 );
					$pdf->setXY( Misc::AdjustXY( 87.5, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 87.5, 3, TTi18n::gettext( 'Province/State' ), 'BR', 0, 'C', false, '', 1 );

					$pdf->SetFont( '', 'B', 6 );
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y - 1.5 ), $adjust_y ) );
					$pdf->Cell( 175, 3, TTi18n::gettext( 'Tax Information as of' ) . ' ' . TTDate::getDate( 'DATE', time() ), 'LTR', 0, 'C', false, '', 1 );
				}
				unset( $udlf, $ud_obj, $left_block_adjust_y, $right_block_adjust_y, $left_total_rows, $right_total_rows );

				//
				// Pay Stub Footer
				//

				$block_adjust_y = 217;
				//Line
				$pdf->setLineWidth( 1 );
				$pdf->Line( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ), Misc::AdjustXY( 185, $adjust_y ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
				$pdf->setLineWidth( 0 );

				//Non Negotiable
				$pdf->SetFont( '', 'B', 14 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 3 ), $adjust_y ) );
				$pdf->Cell( 175, 5, TTi18n::gettext( 'NON NEGOTIABLE' ), $border, 0, 'C', false, '', 1 );

				if ( $pay_stub_obj->getType() == 100 ) {
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 15 ), $adjust_y ) );
					$pdf->SetFont( '', 'B', 35 );
					$pdf->setTextColor( 255, 0, 0 );
					$pdf->Cell( 175, 12, TTi18n::getText( 'VOID' ), $border, 0, 'C' );
					$pdf->SetFont( '', '', 10 );
					$pdf->setTextColor( 0, 0, 0 );
				}

				//Employee Address
				$pdf->SetFont( '', 'B', 12 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 9 ), $adjust_y ) );
				$pdf->Cell( 60, 5, TTi18n::gettext( 'CONFIDENTIAL' ), $border, 0, 'C', false, '', 1 );
				$pdf->SetFont( '', '', 10 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 14 ), $adjust_y ) );
				$pdf->Cell( 60, 5, $user_obj->getFullName() . ' (#' . $user_obj->getEmployeeNumber() . ')', $border, 0, 'C', false, '', 1 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 19 ), $adjust_y ) );
				$pdf->Cell( 60, 5, $user_obj->getAddress1(), $border, 0, 'C', false, '', 1 );
				$address2_adjust_y = 0;
				if ( $user_obj->getAddress2() != '' ) {
					$address2_adjust_y = 5;
					$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 24 ), $adjust_y ) );
					$pdf->Cell( 60, 5, $user_obj->getAddress2(), $border, 0, 'C', false, '', 1 );
				}
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 24 + $address2_adjust_y ), $adjust_y ) );
				$pdf->Cell( 60, 5, Misc::getCityAndProvinceAndPostalCode( $user_obj->getCity(), $user_obj->getProvince(), $user_obj->getPostalCode() ), $border, 1, 'C', false, '', 1 );

				//Pay Period - Balance - ID
				$net_pay_amount = '0.00';
				if ( isset( $pay_stub_entries[40][0] ) ) {
					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], true, $pay_stub_obj->getCurrencyObject()->getRoundDecimalPlaces() );
				}

				$pdf->SetFont( '', 'B', 12 );
				$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 9 ), $adjust_y ) );
				$pdf->Cell( 100, 5, TTi18n::gettext( 'Net Pay' ) . ': ' . $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', false, '', 1 );

				//Display additional employee information on the pay stub such as job title, SIN, hire date.
				$block_adjust_y = ( $block_adjust_y + 12 );

				$pdf->SetFont( '', '', 8 );
				if ( TTUUID::isUUID( $user_obj->getTitle() ) && $user_obj->getTitle() != TTUUID::getZeroID() && $user_obj->getTitle() != TTUUID::getNotExistID()
						&& is_object( $user_obj->getTitleObject() ) ) {
					$block_adjust_y = ( $block_adjust_y + 3 );
					$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 100, 4, TTi18n::gettext( 'Title' ) . ': ' . $user_obj->getTitleObject()->getName(), $border, 1, 'R', false, '', 1 );
				}
				if ( $user_obj->getHireDate() != '' ) {
					$block_adjust_y = ( $block_adjust_y + 3 );
					$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 100, 4, TTi18n::gettext( 'Hire Date' ) . ': ' . TTDate::getDate( 'DATE', $user_obj->getHireDate() ), $border, 1, 'R', false, '', 1 );
				}
				if ( $user_obj->getTerminationDate() != '' && $user_obj->getTerminationDate() <= $pay_stub_obj->getEndDate() ) {
					$block_adjust_y = ( $block_adjust_y + 3 );
					$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 100, 4, TTi18n::gettext( 'Termination Date' ) . ': ' . TTDate::getDate( 'DATE', $user_obj->getTerminationDate() ), $border, 1, 'R', false, '', 1 );
				}
				if ( $user_obj->getSIN() != '' ) {
					$block_adjust_y = ( $block_adjust_y + 3 );
					$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( $block_adjust_y, $adjust_y ) );
					$pdf->Cell( 100, 4, TTi18n::gettext( 'SIN / SSN' ) . ': ' . $user_obj->getSecureSIN( null, true ), $border, 1, 'R', false, '', 1 ); //Force secure SIN always.
				}


				if ( $pay_stub_obj->getTainted() == true ) {
					$tainted_flag = '[T]';
				} else {
					$tainted_flag = '';
				}

				$block_adjust_y = 217;
				$pdf->setXY( Misc::AdjustXY( 75, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 27.5 ), $adjust_y ) );
				$pdf->Cell( 100, 4, TTi18n::gettext( 'Payroll Run #' ) . ': ' . str_pad( $pay_stub_obj->getRun(), 2, 0, STR_PAD_LEFT ), $border, 1, 'R', false, '', 1 );

				$pdf->SetFont( '', '', 8 );
				$pdf->setXY( Misc::AdjustXY( 125, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 30 ), $adjust_y ) );
				$pdf->Cell( 50, 5, TTi18n::gettext( 'Identification #' ) . ': ' . $pay_stub_obj->getDisplayID() . $tainted_flag, $border, 1, 'R', false, '', 1 );
				unset( $net_pay_amount, $tainted_flag );

				//Line
				$pdf->setLineWidth( 1 );
				$pdf->Line( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 35 ), $adjust_y ), Misc::AdjustXY( 185, $adjust_y ), Misc::AdjustXY( ( $block_adjust_y + 35 ), $adjust_y ) );
				$pdf->setLineWidth( 0 );

				$pdf->SetFont( '', '', 6 );
				$pdf->setXY( Misc::AdjustXY( 0, $adjust_x ), Misc::AdjustXY( ( $block_adjust_y + 36 ), $adjust_y ) );
				$pdf->Cell( 175, 1, TTi18n::getText( 'Pay Stub Generated by' ) . ' ' . APPLICATION_NAME . ' @ ' . TTDate::getDate( 'DATE+TIME', $pay_stub_obj->getCreatedDate() ), $border, 0, 'C', false, '', 1 );

				unset( $pay_stub_entries );

				$this->getProgressBarObject()->set( null, $pslf->getCurrentRow() );

				$i++;
			}

			Debug::Text( 'Generating PDF...', __FILE__, __LINE__, __METHOD__, 10 );
			$output = $pdf->Output( '', 'S' );
		}

		TTi18n::setMasterLocale();

		if ( isset( $output ) ) {
			return $output;
		}

		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Stub' ) . ' - ' . TTi18n::getText( 'Employee' ) . ': ' . $this->getUserObject()->getFullName() . ' ' . TTi18n::getText( 'Status' ) . ': ' . Option::getByKey( $this->getStatus(), $this->getOptions( 'status' ) ) . ' ' . TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ) . ' ' . TTi18n::getText( 'Start' ) . ': ' . TTDate::getDate( 'DATE', $this->getStartDate() ) . ' ' . TTi18n::getText( 'End' ) . ': ' . TTDate::getDate( 'DATE', $this->getEndDate() ) . ' ' . TTi18n::getText( 'Transaction' ) . ': ' . TTDate::getDate( 'DATE', $this->getTransactionDate() ) . ' ' . TTi18n::getText( 'Run' ) . ': ' . $this->getRun(), null, $this->getTable(), $this );
	}
}

?>
