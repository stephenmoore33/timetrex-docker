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
 * @package Modules\PayStubAmendment
 */
class PayStubAmendmentFactory extends Factory {
	protected $table = 'pay_stub_amendment';
	protected $pk_sequence_name = 'pay_stub_amendment_id_seq'; //PK Sequence name

	var $user_obj = null;
	var $pay_stub_entry_account_link_obj = null;
	var $pay_stub_entry_name_obj = null;
	var $pay_stub_obj = null;
	var $percent_amount_entry_name_obj = null;

	private $pay_stub_status_change;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_stub_entry_name_id' )->setFunctionMap( 'PayStubEntryNameId' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'effective_date' )->setFunctionMap( 'EffectiveDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'rate' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'units' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'amount' )->setFunctionMap( 'Amount' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'authorized' )->setFunctionMap( 'Authorized' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'recurring_ps_amendment_id' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'ytd_adjustment' )->setFunctionMap( 'YTDAdjustment' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'percent_amount' )->setFunctionMap( 'PercentAmount' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'percent_amount_entry_name_id' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'private_description' )->setFunctionMap( 'PrivateDescription' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'branch_id' )->setFunctionMap( 'Branch' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'department_id' )->setFunctionMap( 'Department' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'job_id' )->setFunctionMap( 'Job' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'job_item_id' )->setFunctionMap( 'JobItem' )->setType( 'uuid' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_pay_stub_amendment' )->setLabel( TTi18n::getText( 'Pay Stub Amendment' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee(s)' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'pay_stub_entry_name_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Stub Account' ) ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Amount Type' ) )->setDataSource( TTSAPI::new( 'APIPayStub' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'units' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Units' ) ),
											TTSField::new( 'rate' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Rate' ) ),
											TTSField::new( 'amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Amount' ) ),
											TTSField::new( 'percent_amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Percent' ) ),
											TTSField::new( 'percent_amount_entry_name_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Percent of' ) ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Pay Stub Note (Public)' ) ),
											TTSField::new( 'private_description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description (Private)' ) ),
											TTSField::new( 'effective_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Effective Date' ) )
									)
							),
							TTSTab::new( 'tab_advanced' )->setLabel( TTi18n::getText( 'Advanced' ) )->setFields(
									new TTSFields(
											TTSField::new( 'branch_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											TTSField::new( 'department_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) ),
											TTSField::new( 'job_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Job' ) )->setDataSource( TTSAPI::new( 'APIJob' )->setMethod( 'getJob' ) ),
											TTSField::new( 'job_item_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Task' ) )->setDataSource( TTSAPI::new( 'APIJobItem' )->setMethod( 'getJobItem' ) ),
									)
							),
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid' )->setColumn( 'ppf.id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_entry_name_id' )->setType( 'uuid' )->setColumn( 'a.pay_stub_entry_name_id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'recurring_ps_amendment_id' )->setType( 'uuid' )->setColumn( 'a.recurring_ps_amendment_id' )->setMulti( true ),
							TTSSearchField::new( 'start_date' )->setType( 'start_date' )->setColumn( 'a.effective_date' ),
							TTSSearchField::new( 'end_date' )->setType( 'end_date' )->setColumn( 'a.effective_date' ),
							TTSSearchField::new( 'effective_date' )->setType( 'end_date' )->setColumn( 'a.effective_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayStubAmendment' )->setMethod( 'getPayStubAmendment' )
									->setSummary( 'Get pay stub amendment records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayStubAmendment' )->setMethod( 'setPayStubAmendment' )
									->setSummary( 'Add or edit pay stub amendment records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayStubAmendment' )->setMethod( 'deletePayStubAmendment' )
									->setSummary( 'Delete pay stub amendment records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayStubAmendment' )->setMethod( 'getPayStubAmendment' ) ),
											   ) ),
							TTSAPI::new( 'APIPayStubAmendment' )->setMethod( 'getPayStubAmendmentDefaultData' )
									->setSummary( 'Get default pay stub amendment data used for creating new pay stub amendments. Use this before calling setPayStubAmendment to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'filtered_status':
				//Select box options;
				$status_options_filter = [ 50 ];
				if ( $this->getStatus() == 55 ) {
					$status_options_filter = [ 55 ];
				} else if ( $this->getStatus() == 52 ) {
					$status_options_filter = [ 52 ];
				}

				$retval = Option::getByArray( $status_options_filter, $this->getOptions( 'status' ) );
				break;
			case 'status':
				$retval = [
					//10 => TTi18n::gettext('NEW'),
					//20 => TTi18n::gettext('OPEN'),
					//30 => TTi18n::gettext('PENDING AUTHORIZATION'),
					//40 => TTi18n::gettext('AUTHORIZATION OPEN'),
					50 => TTi18n::gettext( 'ACTIVE' ),
					52 => TTi18n::gettext( 'IN USE' ),
					55 => TTi18n::gettext( 'PAID' ),
					//60 => TTi18n::gettext('DISABLED')
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Fixed' ),
						20 => TTi18n::gettext( 'Percent' ),
				];
				break;
			case 'pay_stub_account_type':
				$retval = [ 10, 20, 30, 50, 60, 65, 80 ];
				break;
			case 'percent_pay_stub_account_type':
				$retval = [ 10, 20, 30, 40, 50, 60, 65, 80 ];
				break;
			case 'export_type':
			case 'export_eft':
			case 'export_cheque':
				$psf = TTNew( 'PayStubFactory' ); /** @var PayStubFactory $psf */
				$retval = $psf->getOptions( $name );
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'          => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'           => TTi18n::gettext( 'Last Name' ),
						'-1005-user_status'         => TTi18n::gettext( 'Employee Status' ),
						'-1010-title'               => TTi18n::gettext( 'Title' ),
						'-1020-user_group'          => TTi18n::gettext( 'Group' ),
						'-1030-default_branch'      => TTi18n::gettext( 'Default Branch' ),
						'-1040-default_department'  => TTi18n::gettext( 'Default Department' ),
						'-1080-branch'              => TTi18n::gettext( 'Branch' ),
						'-1085-department'          => TTi18n::gettext( 'Department' ),
						'-1090-job'                 => TTi18n::gettext( 'Job' ),
						'-1095-job_item'            => TTi18n::gettext( 'Task' ),
						'-1110-status'              => TTi18n::gettext( 'Status' ),
						'-1120-type'                => TTi18n::gettext( 'Type' ),
						'-1130-pay_stub_entry_name' => TTi18n::gettext( 'Account' ),
						'-1140-effective_date'      => TTi18n::gettext( 'Effective Date' ),
						'-1150-amount'              => TTi18n::gettext( 'Amount' ),
						'-1160-rate'                => TTi18n::gettext( 'Rate' ),
						'-1170-units'               => TTi18n::gettext( 'Units' ),
						'-1180-description'         => TTi18n::gettext( 'Pay Stub Note (Public)' ),
						'-1182-private_description' => TTi18n::gettext( 'Description (Private)' ),

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
						'status',
						'pay_stub_entry_name',
						'effective_date',
						'amount',
						'description',
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

				'branch_id'             => 'Branch',
				'branch'                => false,
				'department_id'         => 'Department',
				'department'            => false,
				'job_id'                => 'Job',
				'job'                   => false,
				'job_item_id'           => 'JobItem',
				'job_item'              => false,

				'pay_stub_entry_name_id'       => 'PayStubEntryNameId',
				'pay_stub_entry_name'          => false,
				//'recurring_ps_amendment_id' => 'RecurringPayStubAmendmentId',
				'effective_date'               => 'EffectiveDate',
				'status_id'                    => 'Status',
				'status'                       => false,
				'type_id'                      => 'Type',
				'type'                         => false,
				'rate'                         => 'Rate',
				'units'                        => 'Units',
				'amount'                       => 'Amount',
				'percent_amount'               => 'PercentAmount',
				'percent_amount_entry_name_id' => 'PercentAmountEntryNameId',
				'ytd_adjustment'               => 'YTDAdjustment',
				'description'                  => 'Description',
				'private_description'          => 'PrivateDescription',
				'authorized'                   => 'Authorized',
				'deleted'                      => 'Deleted',
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
	 * @return bool|null
	 */
	function getPayStubObject() {
		if ( is_object( $this->pay_stub_obj ) ) {
			return $this->pay_stub_obj;
		} else {
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$pslf->getByUserIdAndPayStubAmendmentId( $this->getUser(), $this->getID() );
			if ( $pslf->getRecordCount() > 0 ) {
				$this->pay_stub_obj = $pslf->getCurrent();

				return $this->pay_stub_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		if ( is_object( $this->pay_stub_entry_account_link_obj ) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();

				return $this->pay_stub_entry_account_link_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryNameObject() {
		if ( is_object( $this->pay_stub_entry_name_obj ) ) {
			return $this->pay_stub_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$psealf->getByID( $this->getPayStubEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_name_obj = $psealf->getCurrent();

				return $this->pay_stub_entry_name_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPercentAmountEntryNameObject() {
		if ( is_object( $this->percent_amount_entry_name_obj ) ) {
			return $this->percent_amount_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$psealf->getByID( $this->getPercentAmountEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->percent_amount_entry_name_obj = $psealf->getCurrent();

				return $this->percent_amount_entry_name_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
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
	 * @return bool|mixed
	 */
	function getBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setBranch( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultBranch();
			Debug::Text( 'Using Default Branch: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDepartment() {
		return $this->getGenericDataValue( 'department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDepartment( $value ) {
		$value = TTUUID::castUUID( $value );

		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultDepartment();
			Debug::Text( 'Using Default Department: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJob() {
		return $this->getGenericDataValue( 'job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJob( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJob();
			Debug::Text( 'Using Default Job: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJobItem() {
		return $this->getGenericDataValue( 'job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJobItem();
			Debug::Text( 'Using Default Job Item: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'job_item_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStubEntryNameId() {
		return $this->getGenericDataValue( 'pay_stub_entry_name_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayStubEntryNameId( $value ) {
		$value = TTUUID::castUUID( $value );
		//$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		//Debug::Arr($result, 'Result: ID: '. $id .' Rows: '. $result->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'pay_stub_entry_name_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function setName( $name ) {
		$name = trim( $name );

		$psenlf = TTnew( 'PayStubEntryNameListFactory' ); /** @var PayStubEntryNameListFactory $psenlf */
		$result = $psenlf->getByName( $name );

		if ( $this->Validator->isResultSetWithRows( 'name',
													$result,
													TTi18n::gettext( 'Invalid Entry Name' )
		) ) {

			$this->data['pay_stub_entry_name_id'] = $result->getCurrent()->getId();

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getRecurringPayStubAmendmentId() {
		return $this->getGenericDataValue( 'recurring_ps_amendment_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringPayStubAmendmentId( $value ) {
		$value = TTUUID::castUUID( $value );

		$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' ); /** @var RecurringPayStubAmendmentListFactory $rpsalf */
		$rpsalf->getById( $value );
		//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
		//$result = $rpsalf->getById( $id )->getCurrent();

		if ( ( $value == TTUUID::getZeroID() )
			//OR
			//$this->Validator->isResultSetWithRows(	'recurring_ps_amendment_id',
			//										$rpsalf,
			//										TTi18n::gettext('Invalid Recurring Pay Stub Amendment ID') )
		) {

			$this->setGenericDataValue( 'recurring_ps_amendment_id', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getEffectiveDate() {
		return $this->getGenericDataValue( 'effective_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEffectiveDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		//Adjust effective date, because we won't want it to be a
		//day boundary and have issues with pay period start/end dates.
		//Although with employees in timezones that differ from the pay period timezones, there can still be issues.
		$value = TTDate::getMiddleDayEpoch( $value );

		return $this->setGenericDataValue( 'effective_date', $value );
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
	 * @return null
	 */
	function getRate() {
		return $this->getGenericDataValue( 'rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRate( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );
		Debug::text( 'Setting Rate to: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		//if you don't ship null, you get a zero and that makes the UI logic disables the amount field which is not desired.
		if ( $value == 0 || $value == '' ) {
			$value = null;
		}

		return $this->setGenericDataValue( 'rate', $value );
	}

	/**
	 * @return null
	 */
	function getUnits() {
		return $this->getGenericDataValue( 'units' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUnits( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		//if you don't ship null, you get a zero and that makes the UI logic disables the amount field which is not desired.
		if ( $value == 0 || $value == '' ) {
			$value = null;
		}

		return $this->setGenericDataValue( 'units', $value );
	}

	/**
	 * @param object $pay_stub_obj
	 * @param string|string[] $ids UUID
	 * @return string
	 */
	function getPayStubEntryAmountSum( $pay_stub_obj, $ids ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		if ( !is_array( $ids ) ) {
			return false;
		}

		$type_ids = [];

		//Get Linked accounts so we know which IDs are totals.
		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids );
		if ( $total_gross_key !== false ) {
			$type_ids[] = 10;
			$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset( $ids[$total_gross_key] );
		}
		unset( $total_gross_key );

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids );
		if ( $total_employee_deduction_key !== false ) {
			$type_ids[] = 20;
			unset( $ids[$total_employee_deduction_key] );
		}
		unset( $total_employee_deduction_key );

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids );
		if ( $total_employer_deduction_key !== false ) {
			$type_ids[] = 30;
			unset( $ids[$total_employer_deduction_key] );
		}
		unset( $total_employer_deduction_key );

		$type_amount_arr = [];
		$type_amount_arr['amount'] = 0;
		if ( empty( $type_ids ) == false ) {
			//$type_amount_arr = $pself->getSumByPayStubIdAndType( $pay_stub_id, $type_ids );
			$type_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', $type_ids );
		}

		$amount_arr = [];
		$amount_arr['amount'] = 0;
		if ( count( $ids ) > 0 ) {
			//Still other IDs left to total.
			//$amount_arr = $pself->getAmountSumByPayStubIdAndEntryNameID( $pay_stub_id, $ids );
			$amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', null, $ids );
		}

		$retval = TTMath::add( $type_amount_arr['amount'], $amount_arr['amount'] );

		Debug::text( 'Type Amount: ' . $type_amount_arr['amount'] . ' Regular Amount: ' . $amount_arr['amount'] . ' Total: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param object $pay_stub_obj
	 * @return bool|null|string
	 */
	function getCalculatedAmount( $pay_stub_obj ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		if ( $this->getType() == 10 ) {
			//Fixed
			return $this->getAmount();
		} else {
			//Percent
			if ( $this->getPercentAmountEntryNameId() != '' ) {
				$ps_amendment_percent_amount = $this->getPayStubEntryAmountSum( $pay_stub_obj, [ $this->getPercentAmountEntryNameId() ] );

				$pay_stub_entry_account = $pay_stub_obj->getPayStubEntryAccountArray( $this->getPercentAmountEntryNameId() );
				if ( isset( $pay_stub_entry_account['type_id'] ) && $pay_stub_entry_account['type_id'] == 50 ) {
					//Get balance amount from previous pay stub so we can include that in our percent calculation.
					$previous_pay_stub_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', null, [ $this->getPercentAmountEntryNameId() ] );

					$ps_amendment_percent_amount = TTMath::add( $ps_amendment_percent_amount, $previous_pay_stub_amount_arr['ytd_amount'] );
					Debug::text( 'Pay Stub Amendment is a Percent of an Accrual, add previous pay stub accrual balance to amount: ' . $previous_pay_stub_amount_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $pay_stub_entry_account, $previous_pay_stub_amount_arr );

				Debug::text( 'Pay Stub Amendment Total Amount: ' . $ps_amendment_percent_amount . ' Percent Amount: ' . $this->getPercentAmount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $ps_amendment_percent_amount != 0 && $this->getPercentAmount() != 0 ) { //Allow negative values.
					$amount = TTMath::mul( $ps_amendment_percent_amount, TTMath::div( $this->getPercentAmount(), 100 ) );

					return $amount;
				}
			}
		}

		return false;
	}

	/**
	 * @return null|string
	 */
	function getAmount() {
		$value = $this->getGenericDataValue( 'amount' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( (float)$value, 2 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim( $value );

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		Debug::text( 'Amount: ' . $value . ' Name: ' . $this->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return null
	 */
	function getPercentAmount() {
		return $this->getGenericDataValue( 'percent_amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPercentAmount( $value ) {
		$value = trim( $value );

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		Debug::text( 'Amount: ' . $value . ' Name: ' . $this->getPayStubEntryNameId(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $value == null || $value == '' ) {
			return false;
		}

		return $this->setGenericDataValue( 'percent_amount', round( $value, 2 ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getPercentAmountEntryNameId() {
		return $this->getGenericDataValue( 'percent_amount_entry_name_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPercentAmountEntryNameId( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'percent_amount_entry_name_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPrivateDescription() {
		return $this->getGenericDataValue( 'private_description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrivateDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'private_description', $value );
	}

	/**
	 * @return bool
	 */
	function getAuthorized() {
		return $this->fromBool( $this->getGenericDataValue( 'authorized' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorized( $value ) {
		return $this->setGenericDataValue( 'authorized', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getYTDAdjustment() {
		return $this->fromBool( $this->getGenericDataValue( 'ytd_adjustment' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setYTDAdjustment( $value ) {
		return $this->setGenericDataValue( 'ytd_adjustment', $this->toBool( $value ) );
	}

	/**
	 * Used to determine if the pay stub is changing the status, so we can ignore some validation checks.
	 * @return bool
	 */
	function getEnablePayStubStatusChange() {
		if ( isset( $this->pay_stub_status_change ) ) {
			return $this->pay_stub_status_change;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnablePayStubStatusChange( $bool ) {
		$this->pay_stub_status_change = $bool;

		return true;
	}

	/**
	 * @param string $user_id     UUID
	 * @param int $effective_date EPOCH
	 * @return bool
	 */
	static function releaseAllAccruals( $user_id, $effective_date = null ) {
		Debug::Text( 'Release 100% of all accruals!', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_id == '' ) {
			return false;
		}

		if ( $effective_date == '' ) {
			$effective_date = TTDate::getTime();
		}
		Debug::Text( 'Effective Date: ' . TTDate::getDate( 'DATE+TIME', $effective_date ), __FILE__, __LINE__, __METHOD__, 10 );

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
		} else {
			return false;
		}

		//Get all PSE acccount accruals
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyIdAndStatusIdAndTypeId( $user_obj->getCompany(), 10, 50 );
		if ( $psealf->getRecordCount() > 0 ) {
			$ulf->StartTransaction();
			foreach ( $psealf as $psea_obj ) {
				//Get PSE account that affects this accrual.
				//What if there are two accounts? It takes the first one in the list.
				$psealf_tmp = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf_tmp */
				$psealf_tmp->getByCompanyIdAndAccrualId( $user_obj->getCompany(), $psea_obj->getId() );
				if ( $psealf_tmp->getRecordCount() > 0 ) {
					$release_account_id = $psealf_tmp->getCurrent()->getId();

					$psaf = TTnew( 'PayStubAmendmentFactory' ); /** @var PayStubAmendmentFactory $psaf */
					$psaf->setStatus( 50 ); //Active
					$psaf->setType( 20 );   //Percent
					$psaf->setUser( $user_obj->getId() );
					$psaf->setPayStubEntryNameId( $release_account_id );
					$psaf->setPercentAmount( 100 );
					$psaf->setPercentAmountEntryNameId( $psea_obj->getId() );
					$psaf->setEffectiveDate( $effective_date );
					$psaf->setDescription( 'Release Accrual Balance' );

					if ( $psaf->isValid() ) {
						Debug::Text( 'Release Accrual Is Valid!!: ', __FILE__, __LINE__, __METHOD__, 10 );
						$psaf->Save();
					}
				} else {
					Debug::Text( 'No Release Account for this Accrual!!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			//$ulf->FailTransaction();
			$ulf->CommitTransaction();
		} else {
			Debug::Text( 'No Accruals to release...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return string
	 */
	function calcAmount() {
		//If these aren't numeric then TTMath::mul() will fatal error below.
		if ( !is_numeric( $this->getRate() ) || !is_numeric( $this->getUnits() ) ) {
			return 0;
		}

		$retval = TTMath::mul( $this->getRate(), $this->getUnits(), 4 );

		$retval = TTMath::MoneyRound( $retval, 2, ( ( is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getCurrencyObject() ) ) ? $this->getUserObject()->getCurrencyObject() : null ) ); //Set MIN decimals to 2 and max to the currency rounding.
		//Debug::Text('Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @return bool
	 */
	function isUnique() {
		$ph = [
				'user_id'                => TTUUID::castUUID( $this->getUser() ),
				//'status_id' => $this->getStatus(), //This allows IN USE vs ACTIVE PSA to exists, which shouldn't.
				'pay_stub_entry_name_id' => TTUUID::castUUID( $this->getPayStubEntryNameId() ),
				'effective_date'         => (int)$this->getEffectiveDate(),
				'amount'                 => (float)$this->getAmount(),
				'id'                     => TTUUID::castUUID( $this->getId() ),
		];

		//This this is used for a warning now, there can be multiple duplicate records, so we have to always exclude the record we are working on in the SQL query, otherwise if it happens to be returned it won't trigger the warning.
		$query = 'select id from ' . $this->getTable() . ' where user_id = ? AND pay_stub_entry_name_id = ? AND effective_date = ? AND amount = ? AND id != ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique PSA: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

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
	 * @return bool
	 */
	function preValidate() {
		//Authorize all pay stub amendments until we decide they will actually go through an authorization process
		if ( $this->getAuthorized() == false ) {
			$this->setAuthorized( true );
		}

		//Make sure we always have a status and type set.
		if ( $this->getStatus() == false ) {
			$this->setStatus( 50 );
		}
		if ( $this->getType() == false ) {
			$this->setType( 10 );
		}

		//If amount isn't set, but Rate and units are, calc amount for them.
		if ( ( $this->getAmount() == null || $this->getAmount() == 0 || $this->getAmount() == '' )
				&& $this->getRate() !== null && $this->getUnits() !== null
				&& $this->getRate() != 0 && $this->getUnits() != 0
				&& $this->getRate() != '' && $this->getUnits() != ''
		) {
			Debug::Text( 'Calculating Amount...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setAmount( $this->calcAmount() );
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
		// Employee
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}
		// Pay Stub Account
		if ( $this->getPayStubEntryNameId() !== false ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$this->Validator->isResultSetWithRows( 'pay_stub_entry_name',
												   $psealf->getById( $this->getPayStubEntryNameId() ),
												   TTi18n::gettext( 'Invalid Pay Stub Account' )
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

		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}

		if ( $this->getType() == 10 ) { //10=Fixed
			// Rate
			if ( $this->getRate() != '' ) {
				$this->Validator->isFloat( 'rate',
										   $this->getRate(),
										   TTi18n::gettext( 'Invalid Rate' )
				);
				if ( $this->Validator->isError( 'rate' ) == false ) {
					$this->Validator->isLength( 'rate',
												$this->getRate(),
												TTi18n::gettext( 'Rate has too many digits' ),
												0,
												21
					); //Need to include decimal.
				}
				if ( $this->Validator->isError( 'rate' ) == false ) {
					$this->Validator->isLengthBeforeDecimal( 'rate',
															 $this->getRate(),
															 TTi18n::gettext( 'Rate has too many digits before the decimal' ),
															 0,
															 16
					);
				}
				if ( $this->Validator->isError( 'rate' ) == false ) {
					$this->Validator->isLengthAfterDecimal( 'rate',
															$this->getRate(),
															TTi18n::gettext( 'Rate has too many digits after the decimal' ),
															0,
															4
					);
				}
			}
			// Units
			if ( $this->getUnits() != '' ) {
				$this->Validator->isFloat( 'units',
										   $this->getUnits(),
										   TTi18n::gettext( 'Invalid Units' )
				);
				if ( $this->Validator->isError( 'units' ) == false ) {
					$this->Validator->isLength( 'units',
												$this->getUnits(),
												TTi18n::gettext( 'Units has too many digits' ),
												0,
												21
					); //Need to include decimal
				}
				if ( $this->Validator->isError( 'units' ) == false ) {
					$this->Validator->isLengthBeforeDecimal( 'units',
															 $this->getUnits(),
															 TTi18n::gettext( 'Units has too many digits before the decimal' ),
															 0,
															 16
					);
				}
				if ( $this->Validator->isError( 'units' ) == false ) {
					$this->Validator->isLengthAfterDecimal( 'units',
															$this->getUnits(),
															TTi18n::gettext( 'Units has too many digits after the decimal' ),
															0,
															4
					);
				}
			}
			// Amount
			if ( $this->getGenericDataValue( 'amount' ) !== false ) {
				$this->Validator->isFloat( 'amount',
										   $this->getGenericDataValue( 'amount' ),
										   TTi18n::gettext( 'Invalid Amount' )
				);
				if ( $this->Validator->isError( 'amount' ) == false ) {
					$this->Validator->isLength( 'amount',
												$this->getGenericDataValue( 'amount' ),
												TTi18n::gettext( 'Amount has too many digits' ),
												0,
												21
					); //Need to include decimal
				}
				if ( $this->Validator->isError( 'amount' ) == false ) {
					$this->Validator->isLengthBeforeDecimal( 'amount',
															 $this->getGenericDataValue( 'amount' ),
															 TTi18n::gettext( 'Amount has too many digits before the decimal' ),
															 0,
															 16
					);
				}
				if ( $this->Validator->isError( 'amount' ) == false ) {
					$this->Validator->isLengthAfterDecimal( 'amount',
															$this->getGenericDataValue( 'amount' ),
															TTi18n::gettext( 'Amount has too many digits after the decimal' ),
															0,
															4
					);
				}
			}
		} else if ( $this->getType() == 20 ) {
			// Percent
			if ( $this->getPercentAmount() !== false ) {
				$this->Validator->isFloat( 'percent_amount',
										   $this->getPercentAmount(),
										   TTi18n::gettext( 'Invalid Percent' )
				);
			}
			// Percent Of

			if ( $this->getPercentAmountEntryNameId() !== false && ( $this->getPercentAmountEntryNameId() == '' || $this->getPercentAmountEntryNameId() == TTUUID::getZeroID() ) ) {
				$this->Validator->isTrue( 'percent_amount_entry_name',
										  false,
										  TTi18n::gettext( 'Percent Of must be specified' ) );
			}

			if ( $this->Validator->isError( 'percent_amount_entry_name' ) == false && $this->getPercentAmountEntryNameId() !== false && $this->getPercentAmountEntryNameId() != TTUUID::getZeroID() ) {
				$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
				$psealf->getById( $this->getPercentAmountEntryNameId() );
				//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
				//$result = $psealf->getById( $id )->getCurrent();
				$this->Validator->isResultSetWithRows( 'percent_amount_entry_name',
													   $psealf,
													   TTi18n::gettext( 'Invalid Percent Of' )
				);
			}
		}
		// Description
		if ( $this->getDescription() !== false && $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										2,
										100
			);

			$this->Validator->isHTML( 'description',
									  $this->getDescription(),
										TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}
		// Private Description
		if ( $this->getPrivateDescription() !== false && $this->getPrivateDescription() != '' ) {
			$this->Validator->isLength( 'private_description',
										$this->getPrivateDescription(),
										TTi18n::gettext( 'Invalid Description Length' ),
										2,
										250
			);

			$this->Validator->isHTML( 'private_description',
									  $this->getPrivateDescription(),
									  TTi18n::gettext( 'Private Description contains invalid special characters' ),
			);
		}

		// Effective date
		if ( $this->Validator->getValidateOnly() == false || $this->getEffectiveDate() !== false ) {
			$this->Validator->isDate( 'effective_date',
									  $this->getEffectiveDate(),
									  TTi18n::gettext( 'Incorrect effective date' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == false ) {
			if ( $ignore_warning == false ) {
				//This is needed for releasing vacation accrual after they have been terminated. Just make this a warning instead.
				if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $this->getEffectiveDate() ) > TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
					$this->Validator->Warning( 'effective_date', TTi18n::gettext( 'Effective date is after the employees termination date.' ) );
				}

				//This needs to be ignored when the PSA status is being changed from the pay stub when the employee has been rehired and the new hire date is after the effective date.
				if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getHireDate() != '' && TTDate::getMiddleDayEpoch( $this->getEffectiveDate() ) < TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) ) {
					$this->Validator->Warning( 'effective_date', TTi18n::gettext( 'Effective date is before the employees hire date.' ) );
				}

				if ( $this->isUnique() == false ) { //Post-Adjustment Carry-Forward calculations may require duplicate entries to exist when correcting for example Dental Benefits of a fixed amount from the last 3 pay periods.
					$this->Validator->Warning( 'user_id', TTi18n::gettext( 'Another Pay Stub Amendment already exists for the same employee, account, effective date and amount' ) );
				}
			}

			if ( $this->Validator->getValidateOnly() == false && $this->getUser() == false && $this->Validator->hasError( 'user_id' ) == false ) {
				$this->Validator->isTrue( 'user_id',
										  false,
										  TTi18n::gettext( 'Invalid Employee' ) );
			}
		}

		//Only show this error if it wasn't already triggered earlier.
		if ( $this->Validator->getValidateOnly() == false && is_object( $this->Validator ) && $this->Validator->hasError( 'pay_stub_entry_name_id' ) == false && $this->getPayStubEntryNameId() == false ) {
			$this->Validator->isTrue( 'pay_stub_entry_name_id',
									  false,
									  TTi18n::gettext( 'Invalid Pay Stub Account' ) );
		}

		if ( $this->getDeleted() == false && $this->getType() == 10 ) {
			//If rate and units are set, and not amount, calculate the amount for us.
			// preSave() was changed to preValidate() so this shouldn't be needed anymore.
//			if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL AND $this->getAmount() == NULL ) {
//				$this->preSave();
//			}

			//Make sure rate * units = amount
			if ( $this->getAmount() === null ) {
				Debug::Text( 'Amount is NULL...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->Validator->isTrue( 'amount',
										  false,
										  TTi18n::gettext( 'Amount is blank or not specified' ) );
			}

			//Make sure amount is sane given the rate and units.
			if ( $this->getRate() !== null && $this->getUnits() !== null
					&& $this->getRate() != 0 && $this->getUnits() != 0
					&& $this->getRate() != '' && $this->getUnits() != ''
					&& is_numeric( $this->getRate() ) && is_numeric( $this->getUnits() ) //If these aren't numeric then TTMath::mul() will fatal error below.
					&& ( TTMath::MoneyRound( $this->calcAmount(), 2 ) != TTMath::MoneyRound( $this->getAmount(), 2 ) ) //Use MoneyRound so we always compare consistent decimal places.
			) {
				Debug::text( 'Validate: Rate: ' . $this->getRate() . ' Units: ' . $this->getUnits() . ' Amount: ' . $this->getAmount() . ' Calc: Amount: ' . $this->calcAmount() . ' Raw: ' . TTMath::mul( $this->getRate(), $this->getUnits(), 4 ), __FILE__, __LINE__, __METHOD__, 10 );
				$this->Validator->isTrue( 'amount',
										  false,
										  TTi18n::gettext( 'Invalid Amount, calculation is incorrect' ) . ' (' . $this->calcAmount() . ')' );
			}
		}

		if ( $this->getEnablePayStubStatusChange() == false ) {
			if ( $this->getDeleted() == true ) {
				//Check the status of any pay stub this is attached too. If its PAID then don't allow editing/deleting.
				if ( ( is_object( $this->getPayStubObject() ) && TTUUID::isUUID( $this->getPayStubObject()->getId() ) ) ) {
					$this->Validator->isTrue( 'user_id',
											  false,
											  TTi18n::gettext( 'Unable to delete a Pay Stub Amendment that is currently in use by a Pay Stub' ) );
				}
			} else {
				//Check the status of any pay stub this is attached too. If its PAID then don't allow editing/deleting.
				if ( ( $this->getStatus() == 55 //55=Paid
								|| ( is_object( $this->getPayStubObject() ) && $this->getPayStubObject()->getStatus() == 40 ) ) ) {
					$this->Validator->isTrue( 'user_id',
											  false,
											  TTi18n::gettext( 'Unable to modify Pay Stub Amendment that is currently in use by a Pay Stub marked PAID' ) );
				}

				if ( $ignore_warning == false
								&& ( $this->getStatus() == 52 //52=In Use
										|| ( is_object( $this->getPayStubObject() ) && $this->getPayStubObject()->getStatus() == 25 ) ) ) {
					$this->Validator->Warning( 'user_id', TTi18n::gettext( 'Pay Stub Amendment is assigned to a OPEN pay stub, for changes to take effect you must regenerate the pay stub after this modification' ) );
				}
			}
		}

		//Don't allow these to be deleted in closed pay periods either.
		//Make sure effective date isn't in a CLOSED pay period?
		$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getEffectiveDate() );
		if ( $pplf->getRecordCount() == 1 ) {
			$pp_obj = $pplf->getCurrent();

			//Only check for CLOSED (not locked) pay periods when the
			//status of the PSA is *not* 52=InUse and 55=PAID.
			//Allow deleting of 50=Active PSAs in CLOSED pay periods to make it easier to fix the warning that displays in this case when generating pay stubs.
			if ( $pp_obj->getStatus() == 20 && ( ( $this->getDeleted() == false && $this->getStatus() != 52 && $this->getStatus() != 55 ) || ( $this->getDeleted() == true && $this->getStatus() != 50 ) ) ) {
				$this->Validator->isTrue( 'effective_date',
										  false,
										  TTi18n::gettext( 'Pay Period that this effective date falls within is currently closed' ) );
			}
		}
		unset( $pplf, $pp_obj );

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
						case 'effective_date':
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
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */

		$data = [];
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
						case 'pay_stub_entry_name':
						case 'branch':
						case 'department':
						case 'job':
						case 'job_item':
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
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'amount':
							if ( $this->getType() == 20 ) { //Show percent sign at end, so the user can tell the difference.
								$data[$variable] = TTMath::removeTrailingZeros( (float)$this->getPercentAmount(), 0 ) . '%';
							} else {
								$data[$variable] = $this->getAmount();
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
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Stub Amendment - Employee' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Effective Date' ) . ': ' . TTDate::getDate( 'DATE', $this->getEffectiveDate() ) . ' ' . TTi18n::getText( 'Amount' ) . ': ' . $this->getAmount(), null, $this->getTable(), $this );
	}
}

?>
