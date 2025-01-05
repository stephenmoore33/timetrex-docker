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
class PayStubEntryAccountFactory extends Factory {
	protected $table = 'pay_stub_entry_account';
	protected $pk_sequence_name = 'pay_stub_entry_account_id_seq'; //PK Sequence name

	var $pay_stub_entry_account_link_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'group_id' )->setFunctionMap( 'Group' )->setType( 'uuid' )->setIsNull( true ), //In DB, but not in use yet.

							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'ps_order' )->setFunctionMap( 'Order' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'debit_account' )->setFunctionMap( 'DebitAccount' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'credit_account' )->setFunctionMap( 'CreditAccount' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'accrual_pay_stub_entry_account_id' )->setFunctionMap( 'Accrual' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'accrual_type_id' )->setFunctionMap( 'AccrualType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'in_use' )->setFunctionMap( 'In Use' )->setIsSynthetic( true ),

							TTSCol::new( 'custom_field' )->setFunctionMap( 'CustomField' )->setType( 'jsonb' ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_pay_stub_account' )->setLabel( TTi18n::getText( 'Pay Stub Account' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'ps_order' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Order' ) ),
											TTSField::new( 'accrual_pay_stub_entry_account_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Accrual' ) ),
											TTSField::new( 'accrual_type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Accrual Type' ) ),
											TTSField::new( 'debit_account' )->setType( 'text' )->setLabel( TTi18n::getText( 'Debit Account' ) ),
											TTSField::new( 'credit_account' )->setType( 'text' )->setLabel( TTi18n::getText( 'Credit Account' ) )
									)
							),
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'a.status_id' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'debit_account' )->setType( 'text' )->setColumn( 'a.debit_account' ),
							TTSSearchField::new( 'credit_account' )->setType( 'text' )->setColumn( 'a.credit_account' ),

							TTSSearchField::new( 'type' )->setType( 'text' )->setColumn( 'a.type_id' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'type_id' )->setType( 'integer' )->setColumn( 'a.type_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'getPayStubEntryAccount' )
									->setSummary( 'Get pay stub entry account records.' )
									->setDescription( 'The "type" field should always be included in the pay stub account name to avoid confusion, as there is often multiple pay stub accounts with the same name, but different types (ie: "Medicare [Employee Deduction]" and "Medicare [Employer Deduction])".' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'setPayStubEntryAccount' )
									->setSummary( 'Add or edit pay stub entry account records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'deletePayStubEntryAccount' )
									->setSummary( 'Delete pay stub entry account records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'getPayStubEntryAccount' ) ),
											   ) ),
							TTSAPI::new( 'APIPayStubEntryAccount' )->setMethod( 'getPayStubEntryAccountDefaultData' )
									->setSummary( 'Get default pay stub entry account data used for creating new pay stub entry accounts. Use this before calling setPayStubEntryAccount to get the correct default data.' ),
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
						10 => TTi18n::gettext( 'Enabled' ),
						20 => TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Earning' ),
						20 => TTi18n::gettext( 'Employee Deduction' ),
						30 => TTi18n::gettext( 'Employer Deduction' ),
						40 => TTi18n::gettext( 'Total' ),
						50 => TTi18n::gettext( 'Accrual' ),
						//60 => TTi18n::gettext('Advance Earning'),
						//65 => TTi18n::gettext('Advance Deduction'),
						80 => TTi18n::gettext( 'Miscellaneous' ), //Neither earnings or deductions, just for record keeping, ie: Employer parts of RRSP's, or other items that employees need to see.

				];
				break;
			case 'accrual_type':
				$retval = [
					//May need to add 6 more options here for every permutation of Add/Subtract across just ER Ded and Misc. Since Earning & ER Deduction should always be opposite.
					10 => TTi18n::gettext( 'Earning/Misc Subtracts, EE/ER Deduction Adds' ),
					//12 => TTi18n::gettext('Earning/ER Deduction Subtracts, EE Deduction/Misc Adds'),
					//14 => TTi18n::gettext('Earning/ER Deduction/Misc Subtracts, EE Deduction Adds'),
					//16 => TTi18n::gettext('Earning Subtracts, EE & ER Deduction/Misc Adds'),
					20 => TTi18n::gettext( 'Earning/Misc Adds, EE/ER Deduction Subtracts' ),
					//22 => TTi18n::gettext('Earning/ER Deduction Adds, EE Deduction/Misc Subtracts'),
					//24 => TTi18n::gettext('Earning/ER Deduction/Misc Adds, EE Subtracts'),
					//26 => TTi18n::gettext('Earning Adds, EE & ER Deduction/Misc Subtracts'),
				];
				break;
			case 'type_calculation_order':
				//If any of these exceed 3 digits, need to update CalculatePayStub->getDeductionObjectSortValue() to handle more digits.
				$retval = [
						10 => 40,
						20 => 50,
						30 => 60,
						40 => 70,
						50 => 30,
						60 => 10,
						65 => 20,
						80 => 65,
				];
				break;
			case 'columns':
				$retval = [
						'-1010-status' => TTi18n::gettext( 'Status' ),
						'-1020-type'   => TTi18n::gettext( 'Type' ),
						'-1030-name'   => TTi18n::gettext( 'Name' ),

						'-1140-ps_order'       => TTi18n::gettext( 'Order' ),
						'-1150-debit_account'  => TTi18n::gettext( 'Debit Account' ),
						'-1150-credit_account' => TTi18n::gettext( 'Credit Account' ),

						'-1900-in_use' => TTi18n::gettext( 'In Use' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				$retval = $this->getCustomFieldsColumns( $retval, null );
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'status',
						'type',
						'name',
						'ps_order',
						'debit_account',
						'credit_account',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [
						'type',
						'accrual',
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
				'id'                                => 'ID',
				'company_id'                        => 'Company',
				'status_id'                         => 'Status',
				'status'                            => false,
				'type_id'                           => 'Type',
				'type'                              => false,
				'name'                              => 'Name',
				'ps_order'                          => 'Order',
				'debit_account'                     => 'DebitAccount',
				'credit_account'                    => 'CreditAccount',
				'accrual_pay_stub_entry_account_id' => 'Accrual',
				'accrual_type_id'                   => 'AccrualType',
				'in_use'                            => false,
				'deleted'                           => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		if ( is_object( $this->pay_stub_entry_account_link_obj ) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
			$pseallf->getByCompanyId( $this->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();

				return $this->pay_stub_entry_account_link_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return int
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
	 * Returns the order in which accounts should be calculated
	 * given a circular dependency scenario
	 * @return bool
	 */
	function getTypeCalculationOrder() {
		if ( $this->getType() !== false ) {
			$order_arr = $this->getOptions( 'type_calculation_order' );

			if ( isset( $order_arr[$this->getType()] ) ) {
				return $order_arr[$this->getType()];
			}
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isInUse( $id ) {
		$pslf = new PayStubListFactory();
		$pself = new PayStubEntryListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = [
				'pay_stub_account_id'  => (string)$id,
				'pay_stub_account_idb' => (string)$id,
		];

		$query = '
					select	a.id
					from	' . $pself->getTable() . ' as a
						LEFT JOIN ' . $pslf->getTable() . ' as b ON ( a.pay_stub_id = b.id )
					where	a.pay_stub_entry_name_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					UNION ALL
					select	a.id
					from	' . $psalf->getTable() . ' as a
					where	a.pay_stub_entry_name_id = ? AND a.deleted = 0
					LIMIT 1';

		$retval = $this->db->GetOne( $query, $ph );
		Debug::Arr( $retval, 'In Use... ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retval === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Check to see if this PS account is linked in the PayStubEntryAccountLink so we can prevent it from being deleted if it is.
	 * @param string $id UUID
	 * @return bool
	 */
	function isInPSEAccountLink( $id ) {
		$id = (string)$id; //UUID

		$pseal_obj = $this->getPayStubEntryAccountLinkObject();
		if ( is_object( $pseal_obj ) ) {
			if ( in_array( $id, [ (string)$pseal_obj->getRegularTime(), (string)$pseal_obj->getTotalGross(), (string)$pseal_obj->getTotalEmployeeDeduction(), (string)$pseal_obj->getTotalEmployerDeduction(), (string)$pseal_obj->getTotalNetPay() ], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check to see if this PS account is linked by another PS account as an accrual.
	 * @param string $id UUID
	 * @return bool
	 */
	function isInPSEAccountAccrual( $id ) {
		$psealf = new PayStubEntryAccountListFactory();

		$ph = [
				'pay_stub_account_id' => (string)$id,
		];

		$query = '
					select	a.id
					from	' . $psealf->getTable() . ' as a
					where	a.accrual_pay_stub_entry_account_id = ?
						AND ( a.deleted = 0 )
					LIMIT 1';

		$retval = $this->db->GetOne( $query, $ph );
		Debug::Arr( $retval, 'In Use... ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retval === false ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function getCurrentType( $id ) {
		$psealf = TTNew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByIdAndCompanyId( $id, $this->getCompany() );
		if ( $psealf->getRecordCount() == 1 ) {
			$retval = $psealf->getCurrent()->getType();
			Debug::Text( 'Current Type: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'type_id'    => (int)$this->getType(),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND type_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Pay Stub Account: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

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
	function getName() {
		if ( $this->getGenericDataValue( 'name' ) !== false ) {
			/*I18n:	apply gettext in the result of this function
					to be use in the getByIdArray() function in
					the PayStubEntryAccountListFactory.class.php
					file.
			*/
			return TTi18n::gettext( $this->getGenericDataValue( 'name' ) );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOrder() {
		return $this->getGenericDataValue( 'ps_order' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOrder( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'ps_order', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDebitAccount() {
		return $this->getGenericDataValue( 'debit_account' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDebitAccount( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'debit_account', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCreditAccount() {
		return $this->getGenericDataValue( 'credit_account' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCreditAccount( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'credit_account', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrual() {
		return $this->getGenericDataValue( 'accrual_pay_stub_entry_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAccrual( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'accrual_pay_stub_entry_account_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAccrualType() {
		return $this->getGenericDataValue( 'accrual_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'accrual_type_id', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
			if ( $this->Validator->isError( 'type_id' ) == false ) {
				Debug::Text( 'Type: ' . $this->getType() . ' isNew: ' . (int)$this->isNew(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->isNew() == false && $this->getCurrentType( $this->getId() ) != $this->getType() && $this->isInUse( $this->getId() ) == true ) {
					$this->Validator->isTrue( 'type_id',
											  false,
											  TTi18n::gettext( 'Type cannot be modified when Pay Stub Account is in use' )
					);
				}
			}
		}
		// Name
		if ( $this->getName() !== false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2,
										100
			);

			$this->Validator->isHTML( 'name',
									  $this->getName(),
									  TTi18n::gettext( 'Name contains invalid special characters' ),
			);

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name is already in use' )
				);
			}
		}
		// Order
		if ( $this->Validator->getValidateOnly() == false && $this->getOrder() == '' ) {
			$this->Validator->isTRUE( 'ps_order',
									  false,
									  TTi18n::gettext( 'Order must be specified' )
			);
		}
		if ( $this->getOrder() != '' && $this->Validator->isError( 'ps_order' ) == false ) {
			$this->Validator->isNumeric( 'ps_order',
										 $this->getOrder(),
										 TTi18n::gettext( 'Invalid Order' )
			);
		}
		// Debit Account
		if ( $this->getDebitAccount() != '' ) {
			$this->Validator->isLength( 'debit_account',
										$this->getDebitAccount(),
										TTi18n::gettext( 'Invalid Debit Account' ),
										2,
										1000
			);
		}
		// Credit Account
		if ( $this->getCreditAccount() != '' ) {
			$this->Validator->isLength( 'credit_account',
										$this->getCreditAccount(),
										TTi18n::gettext( 'Invalid Credit Account' ),
										2,
										1000
			);
		}
		// Accrual Account
		if ( $this->getAccrual() !== false && $this->getAccrual() != TTUUID::getZeroID() ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$psealf->getByID( $this->getAccrual() );
			if ( $psealf->getRecordCount() > 0 ) {
				if ( $psealf->getCurrent()->getType() != 50 ) {
					//Reset Result set so an error occurs.
					$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
				}
			}
			$this->Validator->isResultSetWithRows( 'accrual_pay_stub_entry_account_id',
												   $psealf,
												   TTi18n::gettext( 'Accrual Account is invalid' )
			);
		}
		// Accrual Type
		if ( $this->getAccrualType() !== false ) {
			$this->Validator->inArrayKey( 'accrual_type_id',
										  $this->getAccrualType(),
										  TTi18n::gettext( 'Incorrect Accrual Type' ),
										  $this->getOptions( 'accrual_type' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getType() == 50 ) {
			//If the PSE account is an accrual, it can't link to one as well.
			$this->setAccrual( null );
		}

		//Make sure this account doesn't point to itself as an accrual.
		if ( $this->isNew() == false && $this->getAccrual() == $this->getId() ) {
			$this->Validator->isTrue( 'accrual',
									  false,
									  TTi18n::gettext( 'Accrual account is invalid' )
			);
		}

		if ( $this->getDeleted() == true ) {
			if ( $this->getType() == 10 || $this->getType() == 40 ) { //10=Earning (for Regular Time PSA for salaried employees) 40=Total
				$this->Validator->isTRUE( 'in_use',
						( $this->isInPSEAccountLink( $this->getId() ) == true ? false : true ),
										  TTi18n::gettext( 'This pay stub account is currently desiginated as a critical system account, unable to delete' ) );
			}

			if ( $this->getType() == 50 ) { //50=Accrual
				$this->Validator->isTRUE( 'in_use',
						( $this->isInPSEAccountAccrual( $this->getId() ) == true ? false : true ),
										  TTi18n::gettext( 'This pay stub account is currently linked to others as an accrual account, unable to delete' ) );
			}

			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			// The isInUse() check in preSave() already looks for pay stubs, pay stub amendments, and if those exist it should never get here.
			$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
			$pclf->getByCompanyIdAndPayStubEntryAccountID( $this->getCompany(), $this->getId(), 1 );
			if ( $pclf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This account is currently in use' ) . ' ' . TTi18n::gettext( 'by pay codes' ) );
			}

			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndPayStubEntryAccountId( $this->getCompany(), $this->getId(), 1 );
			if ( $cdlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This account is currently in use' ) . ' ' . TTi18n::gettext( 'by Tax/Deductions' ) );
			}
		}

		//Make sure PS order is correct, in that types can't be separated by total or accrual accounts.
		if ( $this->getDeleted() == false && $this->getOrder() != '' && $this->Validator->isError( 'ps_order' ) == false ) {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
			$pseallf->getByCompanyId( $this->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$pseal_obj = $pseallf->getCurrent();

				$psea_map = [];
				$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
				$psealf->getByCompanyIdAndTypeId( $this->getCompany(), 40 );
				if ( $psealf->getRecordCount() > 0 ) {
					foreach ( $psealf as $psea_obj ) {
						$psea_map[$psea_obj->getId()] = $psea_obj->getOrder();
					}
					unset( $psea_obj );
				}

				switch ( $this->getType() ) {
					case 10: //Earning
						//Greater the 0, less then Total Gross Account
						if ( isset( $psea_map[$pseal_obj->getTotalGross()] ) ) {
							$min_ps_order = 0;
							$max_ps_order = $psea_map[$pseal_obj->getTotalGross()];
						}
						break;
					case 20: //EE Deduction
						//Greater then Total Gross Account, less then Total Employee Deduction
						if ( isset( $psea_map[$pseal_obj->getTotalGross()] ) && isset( $psea_map[$pseal_obj->getTotalEmployeeDeduction()] ) ) {
							$min_ps_order = $psea_map[$pseal_obj->getTotalGross()];
							$max_ps_order = $psea_map[$pseal_obj->getTotalEmployeeDeduction()];
						}
						break;
					case 30: //ER Deduction
						//Greater then Net Pay Account, less then Total Employer Deduction
						if ( isset( $psea_map[$pseal_obj->getTotalNetPay()] ) && isset( $psea_map[$pseal_obj->getTotalEmployerDeduction()] ) ) {
							$min_ps_order = $psea_map[$pseal_obj->getTotalNetPay()];
							$max_ps_order = $psea_map[$pseal_obj->getTotalEmployerDeduction()];
						}
						break;
					case 50: //Accrual
					case 80: //Misc
						//Greater then Total Employer Deduction
						if ( isset( $psea_map[$pseal_obj->getTotalEmployerDeduction()] ) ) {
							$min_ps_order = $psea_map[$pseal_obj->getTotalEmployerDeduction()];
							$max_ps_order = 10001;
						}
						break;
				}

				if ( isset( $min_ps_order ) && isset( $max_ps_order ) && ( $this->getOrder() <= $min_ps_order || $this->getOrder() >= $max_ps_order ) ) {
					Debug::text( 'PS Order... Min: ' . $min_ps_order . ' Max: ' . $max_ps_order, __FILE__, __LINE__, __METHOD__, 10 );
					$this->Validator->isTrue( 'ps_order',
											  false,
											  TTi18n::gettext( 'Order is invalid for this type of account, it must be between' ) . ' ' . ( $min_ps_order + 1 ) . ' ' . TTi18n::gettext( 'and' ) . ' ' . ( $max_ps_order - 1 ) );
				}
			}
		}

		$this->validateCustomFields( $this->getCompany() );

		return true;
	}

	/**
	 * @param string $company_id  UUID
	 * @param string[] $src_ids   UUID
	 * @param string $dst_id      UUID
	 * @param int $effective_date EPOCH
	 * @return bool
	 */
	function migrate( $company_id, $src_ids, $dst_id, $effective_date ) {
		$dst_id = TTUUID::castUUID( $dst_id );
		$src_ids = array_unique( (array)$src_ids );

		if ( empty( $dst_id ) || $dst_id == TTUUID::getZeroID() ) {
			return false;
		}

		Debug::Arr( $src_ids, 'Attempting to migrate to: ' . $dst_id, __FILE__, __LINE__, __METHOD__, 10 );

		$current_epoch = time();

		$pself = TTNew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */

		//Loop over just ACTIVE employees.
		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		//Get names of all Pay Stub Accounts
		$psealf = TTNew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyId( $company_id );
		$pay_stub_account_arr = $psealf->getArrayByListFactory( $psealf, false );

		$ulf->StartTransaction();

		$ulf->getByCompanyIdAndStatus( $company_id, 10 );
		if ( is_array( $pay_stub_account_arr ) && count( $pay_stub_account_arr ) > 0 && $ulf->getRecordCount() > 0 ) {
			foreach ( $ulf as $u_obj ) {
				//Get current YTD values assigned to the src_ids.
				foreach ( $src_ids as $src_id ) {
					$pse_row = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $u_obj->getId(), $src_id, $current_epoch );
					if ( isset( $pse_row['amount'] ) && $pse_row['amount'] != 0 ) {
						Debug::Text( 'Found existing YTD amount for User ID: ' . $u_obj->getID() . ' PayStubEntryNameID: ' . $src_id . ' Amount: ' . $pse_row['amount'], __FILE__, __LINE__, __METHOD__, 10 );

						if ( isset( $pay_stub_account_arr[$dst_id] ) ) {
							$from_description = TTi18n::getText( 'Migrated YTD Amount to' ) . ': ' . $pay_stub_account_arr[$dst_id];
						} else {
							$from_description = TTi18n::getText( 'Migrated YTD Amount to other account' );
						}

						if ( isset( $pay_stub_account_arr[$src_id] ) ) {
							$to_description = TTi18n::getText( 'Migrated YTD Amount from' ) . ': ' . $pay_stub_account_arr[$src_id];
						} else {
							$to_description = TTi18n::getText( 'Migrated YTD Amount from other account' );
						}
						Debug::Text( 'Description: From: ' . $from_description . ' To: ' . $to_description, __FILE__, __LINE__, __METHOD__, 10 );

						//Create Pay Stub Amendments to reduce current values to 0.
						$psaf = TTNew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
						$psaf->setStatus( 50 );
						$psaf->setType( 10 );
						$psaf->setUser( $u_obj->getID() );
						$psaf->setPayStubEntryNameId( $src_id );
						$psaf->setAmount( ( $pse_row['amount'] * -1 ) );
						$psaf->setEffectiveDate( $effective_date );
						$psaf->setDescription( $from_description );
						if ( $psaf->isValid() ) {
							$psaf->Save();
						}

						//Create Pay Stub Amendments to copy amounts to new dst_id
						$psaf = TTNew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
						$psaf->setStatus( 50 );
						$psaf->setType( 10 );
						$psaf->setUser( $u_obj->getID() );
						$psaf->setPayStubEntryNameId( $dst_id );
						$psaf->setAmount( $pse_row['amount'] );
						$psaf->setEffectiveDate( $effective_date );
						$psaf->setDescription( $to_description );
						if ( $psaf->isValid() ) {
							$psaf->Save();
						}
					}
				}
			}
		}

		$ulf->CommitTransaction();

		return true;
	}


	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDeleted() == true ) {
			//Validate() checks for pay codes, Tax/Deductions etc...
			Debug::text( 'Attempting to delete PSE Account', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->isInUse( $this->getId() ) ) {
				Debug::text( 'PSE Account is in use by Pay Stubs... Disabling instead.', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setDeleted( false ); //Can't delete, account is in use.
				$this->setStatus( 20 );     //Disable instead
			} else {
				Debug::text( 'aPSE Account is NOT in use... Deleting...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			if ( $this->getAccrualType() == '' ) {
				$this->setAccrualType( 10 );
			}
		}

		return true;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( null, $this->getTable( true ) . $this->getCompany() ); //PayStubEntryAccountListFactory has several functions that cache data in this group, so clear them all.
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$data = $this->parseCustomFieldsFromArray( $data );
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
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
						case 'type':
						case 'accrual_type':
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
			$data = $this->getCustomFields( $this->getCompany(), $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Stub Account' ), null, $this->getTable(), $this );
	}
}

?>
