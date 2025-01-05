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
class RemittanceSourceAccountFactory extends Factory {
	protected $table = 'remittance_source_account';
	protected $pk_sequence_name = 'remittance_source_account_id_seq'; //PK Sequence name

	protected $legal_entity_obj = null;
	protected $currency_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'legal_entity_id' )->setFunctionMap( 'LegalEntity' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'country' )->setFunctionMap( 'Country' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'data_format_id' )->setFunctionMap( 'DataFormat' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'last_transaction_number' )->setFunctionMap( 'LastTransactionNumber' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value1' )->setFunctionMap( 'Value1' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value2' )->setFunctionMap( 'Value2' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value3' )->setFunctionMap( 'Value3' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value4' )->setFunctionMap( 'Value4' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value5' )->setFunctionMap( 'Value5' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value6' )->setFunctionMap( 'Value6' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value7' )->setFunctionMap( 'Value7' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value8' )->setFunctionMap( 'Value8' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value9' )->setFunctionMap( 'Value9' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value10' )->setFunctionMap( 'Value10' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value11' )->setFunctionMap( 'Value11' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value12' )->setFunctionMap( 'Value12' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value13' )->setFunctionMap( 'Value13' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value14' )->setFunctionMap( 'Value14' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value15' )->setFunctionMap( 'Value15' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value16' )->setFunctionMap( 'Value16' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value17' )->setFunctionMap( 'Value17' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value18' )->setFunctionMap( 'Value18' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value19' )->setFunctionMap( 'Value19' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value20' )->setFunctionMap( 'Value20' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value21' )->setFunctionMap( 'Value21' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value22' )->setFunctionMap( 'Value22' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value23' )->setFunctionMap( 'Value23' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value24' )->setFunctionMap( 'Value24' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value25' )->setFunctionMap( 'Value25' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value26' )->setFunctionMap( 'Value26' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value27' )->setFunctionMap( 'Value27' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value28' )->setFunctionMap( 'Value28' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value29' )->setFunctionMap( 'Value29' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'value30' )->setFunctionMap( 'Value30' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'created_date' )->setFunctionMap( 'CreatedDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'created_by' )->setFunctionMap( 'CreatedBy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'updated_date' )->setFunctionMap( 'UpdatedDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'updated_by' )->setFunctionMap( 'UpdatedBy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'deleted_date' )->setFunctionMap( 'DeletedDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'deleted_by' )->setFunctionMap( 'DeletedBy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'deleted' )->setFunctionMap( 'Deleted' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_remittance_source_account' )->setLabel( TTi18n::getText( 'Remittance Source Account' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'legal_entity_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Legal Entity' ) )->setDataSource( TTSAPI::new( 'APILegalEntity' )->setMethod( 'getLegalEntity' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'country' )->setType( 'text' )->setLabel( TTi18n::getText( 'Country' ) )->setWidth( '100%' ),
											TTSField::new( 'currency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APICurrency' )->setMethod( 'getCurrency' ) ),
											TTSField::new( 'data_format_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Format' ) )->setDataSource( TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getOptions' )->setArg( 'data_format' ) ),
											TTSField::new( 'last_transaction_number' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Last Transaction Number' ) ),
											TTSField::new( 'value1_1' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value1' ) ),
											TTSField::new( 'value1_2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value1' ) ),
											TTSField::new( 'value2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value2' ) ),
											TTSField::new( 'value3' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value3' ) ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) ),
									)
							),
							TTSTab::new( 'tab_advanced' )->setLabel( TTi18n::getText( 'Advanced' ) )->setFields(
									new TTSFields(
											TTSField::new( 'value4' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value4' ) ),
											TTSField::new( 'value5' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value5' ) ),
											TTSField::new( 'value6' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value6' ) ),
											TTSField::new( 'value7' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value7' ) ),
											TTSField::new( 'value8' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value8' ) ),
											TTSField::new( 'value9' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value9' ) ),
											TTSField::new( 'value10' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value10' ) ),
											TTSField::new( 'value11' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value11' ) ),
											TTSField::new( 'value12' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value12' ) ),
											TTSField::new( 'value13' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value13' ) ),
											TTSField::new( 'value14' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value14' ) ),
											TTSField::new( 'value15' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value15' ) ),
											TTSField::new( 'value16' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value16' ) ),
											TTSField::new( 'value17' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value17' ) ),
											TTSField::new( 'value18' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value18' ) ),
											TTSField::new( 'value19' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value19' ) ),
											TTSField::new( 'value20' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value20' ) ),
											TTSField::new( 'value21' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value21' ) ),
											TTSField::new( 'value22' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value22' ) ),
											TTSField::new( 'value23' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value23' ) ),
											TTSField::new( 'value24' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value24' ) ),
											TTSField::new( 'value25' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value25' ) ),
											TTSField::new( 'value26' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value26' ) ),
											TTSField::new( 'value27' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value27' ) ),
											TTSField::new( 'value28' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value28' ) ),
											TTSField::new( 'value29' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value29' ) ),
											TTSField::new( 'value30' )->setType( 'text' )->setLabel( TTi18n::getText( 'Value30' ) ),
											TTSField::new( 'signature' )->setType( 'text' )->setLabel( TTi18n::getText( 'Signature' ) ),
									)
							),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'a.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'currency_id' )->setType( 'uuid' )->setColumn( 'a.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'integer' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getRemittanceSourceAccount' )
									->setSummary( 'Get remittance source account records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'setRemittanceSourceAccount' )
									->setSummary( 'Add or edit remittance source account records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'deleteRemittanceSourceAccount' )
									->setSummary( 'Delete remittance source account records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getRemittanceSourceAccount' ) ),
											   ) ),
							TTSAPI::new( 'APIRemittanceSourceAccount' )->setMethod( 'getRemittanceSourceAccountDefaultData' )
									->setSummary( 'Get default remittance source account data used for creating new accounts. Use this before calling setRemittanceSourceAccount to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param bool $name
	 * @param null|mixed $params
	 * @return array|bool|null
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
			case 'ach_transaction_type': //ACH transactions require a transaction code that matches the bank account.
				//**NOTE: These are different than whats in RemittanceSourceAccount because its for debit transactions instead of credit.
				$retval = [
						27 => TTi18n::getText( 'Checking' ),
						37 => TTi18n::getText( 'Savings' ),
				];
				break;
			case 'country':
				$cf = TTNew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
				$retval = $cf->getOptions( 'country' );
				break;
			case 'type':
				$retval = [
					//1000 => TTi18n::gettext('TimeTrex EFT'), //See Formats instead
					//1010 => TTi18n::gettext('TimeTrex Check'), //See Formats instead
					2000 => TTi18n::gettext( 'Check' ),
					3000 => TTi18n::gettext( 'EFT/ACH' ),
					//9000 => TTi18n::gettext('Bitcoin'),
				];
				break;
			case 'data_format_eft_form': //data_format ID to EFT class name mapping.
				$retval = [
						10   => 'ACH',
						20   => '1464',
						30   => '1464', //CIBC
						50   => '105',
						70   => 'BEANSTREAM',
						1000 => 'CIBC_EPAY',
						1010 => 'ECAB',
				];
				break;
			case 'data_format_check_form': //data_format ID to CHECK class name mapping.
				$retval = [
						10 => '9085', //cheque_9085
						20 => '9209P', //cheque_9209p
						30 => 'DLT103', //cheque_dlt103
						40 => 'DLT104', //cheque_dlt104
						1000 => 'MBL2398', //cheque_dlt104
				];
				break;
			case 'data_format':
				$retval = [
						0 => TTi18n::gettext( '-- None --' ),
				];

				if ( isset( $params['type_id'] )
						&& isset( $params['country'] )
						&& $params['country'] != false ) {
					$tmp_retval = [];
					$valid_keys = [];
					switch ( $params['type_id'] ) {
						case 2000: //Check
							$tmp_retval = [
								//5  => TTi18n::gettext('TimeTrex Checks'),
								10 => TTi18n::gettext( 'Top Check (Sage) [9085]' ), //cheque_9085 // SS9085 (still current for Sage 50 & Accpac)  https://www.nebs.ca/canEcat/products/product_detail.jsp?pc=SS9085
								20 => TTi18n::gettext( 'Top Check (QuickBooks) [9209P]' ), //cheque_9209p // SS9209 (still current for Quickbooks)  https://www.nebs.ca/canEcat/products/product_detail.jsp?pc=SS9209
								30 => TTi18n::gettext( 'Top Check Lined (QuickBooks) [DLT103]' ), //cheque_dlt103 // DLT103 (fill-in lines on cheques)  https://www.deluxe.com/shopdeluxe/pd/laser-top-checks-lined/_/A-DLT103
								40 => TTi18n::gettext( 'Top Check (QuickBooks) [DLT104]' ), //cheque_dlt104 // DLT104 ("$" & "Dollar" on cheques) https://www.deluxe.com/shopdeluxe/pd/laser-top-checks-lined/_/A-DLT104

								1000 => TTi18n::gettext( 'Middle Check (Sage) [2398]' ), //cheque_mbl2398 // Middle check supplied by MBL Enterprises with double signature lines.

								//2000 => //Bottom Checks
							];
							$valid_keys = array_keys( $tmp_retval );
							break;
						case 3000: //EFT
							$tmp_retval = [
									5  => TTi18n::gettext( 'TimeTrex Payment Services' ),
									10 => TTi18n::gettext( 'United States - ACH (94-Byte)' ),
									20 => TTi18n::gettext( 'Canada - EFT (1464-Byte)' ),
									30 => TTi18n::gettext( 'Canada - EFT CIBC (1464-Byte)' ),
									//40 => TTi18n::gettext('Canada - EFT RBC (1464-Byte)'),
									50 => TTi18n::gettext( 'Canada - EFT (105-Byte)' ),
									70 => TTi18n::gettext( 'Bambora *DEPRECATED* (CSV)' ), //02-May-24: FINTRAC requires employee addresses be added to the file formats now, very few customers use them anymore, therefore we are deprecating them. Ticket #648041

									1000 => TTi18n::gettext( 'Caribbean - CIBC E-Pay (CSV)' ),
									1010 => TTi18n::gettext( 'Caribbean - ECAB (CSV)' ),
							];

							if ( $params['country'] == 'US' ) {
								$valid_keys = [ 5, 10 ];
							} else if ( $params['country'] == 'CA' ) {
								$valid_keys = [ 5, 20, 30, 50, 70 ];
							} else if ( in_array( $params['country'], [ 'AI', 'AG', 'AW', 'BS', 'BB', 'BZ', 'CU', 'DM', 'DO', 'GD', 'GP', 'GY', 'HT', 'JM', 'KN', 'LC', 'MQ', 'MS', 'VC', 'SR', 'TC', 'TT', 'VC', 'VI', 'VG' ] ) ) { //Caribbean countries.
								$valid_keys = [ 10, 1000, 1010 ];
							} else {
								$valid_keys = [ 10 ]; //Default to US ACH format for all other countries.
							}

							break;
					}

					if ( count( $valid_keys ) > 0 ) {
						unset( $retval[0] ); //remove "-- None --"
						foreach ( $valid_keys as $key ) {
							$retval[$key] = $tmp_retval[$key];
						}
					}
				}
				break;
			case 'columns':
				$retval = [
						'-1010-status'                  => TTi18n::gettext( 'Status' ),
						'-1020-type'                    => TTi18n::gettext( 'Type' ),
						'-1030-legal_name'              => TTi18n::gettext( 'Legal Entity Name' ),
						'-1040-name'                    => TTi18n::gettext( 'Name' ),
						'-1050-description'             => TTi18n::gettext( 'Description' ),
						'-1060-country'                 => TTi18n::gettext( 'Country' ),
						'-1150-data_format'             => TTi18n::gettext( 'Data Format' ),
						'-1160-last_transaction_number' => TTi18n::gettext( 'Last Transaction Number' ),

						'-1500-value1' => TTi18n::gettext( 'Institution' ),
						'-1510-value2' => TTi18n::gettext( 'Transit/Routing' ),
						'-1520-value3' => TTi18n::gettext( 'Account' ),

						'-1900-in_use'       => TTi18n::gettext( 'In Use' ),
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
						'status',
						'type',
						'legal_name',
						'name',
						'description',
						'country', //This is needed by JS to determine which fields to show, so users without access to view remittance source accounts doesn't break the UI.
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
						'value1',
						'value1_1',
						'value1_2',
						'value2',
						'value3',
						'value4',
						'value5',
						'value6',
						'value7',
						'value8',
						'value9',
						'value10',
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
				'id'                      => 'ID',
				'company_id'              => 'Company',
				'legal_entity_id'         => 'LegalEntity',
				'status_id'               => 'Status',
				'status'                  => false,
				'type_id'                 => 'Type',
				'type'                    => false,
				'legal_name'              => false,
				'name'                    => 'Name',
				'description'             => 'Description',
				'country'                 => 'Country',
				'currency_id'             => 'Currency',
				'currency'                => false,
				'data_format_id'          => 'DataFormat',
				'data_format'             => false,
				'last_transaction_number' => 'LastTransactionNumber',
				'value1'                  => 'Value1',
				'value2'                  => 'Value2',
				'value3'                  => 'Value3',
				'value4'                  => 'Value4',
				'value5'                  => 'Value5',
				'value6'                  => 'Value6',
				'value7'                  => 'Value7',
				'value8'                  => 'Value8',
				'value9'                  => 'Value9',
				'value10'                 => 'Value10',
				'value11'                 => 'Value11',
				'value12'                 => 'Value12',
				'value13'                 => 'Value13',
				'value14'                 => 'Value14',
				'value15'                 => 'Value15',
				'value16'                 => 'Value16',
				'value17'                 => 'Value17',
				'value18'                 => 'Value18',
				'value19'                 => 'Value19',
				'value20'                 => 'Value20',
				'value21'                 => 'Value21',
				'value22'                 => 'Value22',
				'value23'                 => 'Value23',
				'value24'                 => 'Value24',
				'value25'                 => 'Value25',
				'value26'                 => 'Value26',
				'value27'                 => 'Value27',
				'value28'                 => 'Value28',
				'value29'                 => 'Value29',
				'value30'                 => 'Value30',
				'in_use'                  => false,
				'deleted'                 => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool
	 */
	function getLegalEntityObject() {
		return $this->getGenericObject( 'LegalEntityListFactory', $this->getLegalEntity(), 'legal_entity_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = trim( $value );
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegalEntity( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCurrency( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'currency_id', $value );
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
	 * @return int
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
	 * @return bool|mixed
	 */
	function getCountry() {
		return $this->getGenericDataValue( 'country' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCountry( $value ) {
		return $this->setGenericDataValue( 'country', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return int
	 */
	function getDataFormat() {
		return $this->getGenericDataValue( 'data_format_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDataFormat( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'data_format_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );

		$company_id = $this->getCompany();

		if ( $name == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'type_id'    => (int)$this->getType(),
				'name'       => $name,
		];

		$query = 'SELECT a.id
					FROM ' . $this->getTable() . ' as a
					WHERE a.company_id = ?
					    AND a.type_id = ?
					    AND LOWER(a.name) = LOWER(?)
						AND a.deleted = 0';

		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
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
	function getLastTransactionNumber() {
		return $this->getGenericDataValue( 'last_transaction_number' );
	}

	/**
	 * @return int
	 */
	function getNextTransactionNumber() {
		return ( (int)$this->getLastTransactionNumber() + 1 );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastTransactionNumber( $value ) {
		$value = trim( $value ); //This can be alphanumeric.

		//Pull out only digits
//		$value = $this->Validator->stripNonNumeric($value);
//
//		if (	$this->Validator->isFloat(	'last_transaction_number',
//											$value,
//											TTi18n::gettext('Incorrect transaction number')) ) {
//
//			$this->setGenericDataValue( 'last_transaction_number', $value );
//
//			return TRUE;
//		}
//
//		return FALSE;

		$this->setGenericDataValue( 'last_transaction_number', $value );

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getValue1() {
		return $this->getGenericDataValue( 'value1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value1', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue2() {
		return $this->getGenericDataValue( 'value2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value2', $value );
	}

	/**
	 * VALUE 3 is the account number. It needs to be encrypted.
	 * @param null $account
	 * @return bool|string
	 */
	function getSecureValue3( $account = null ) {
		if ( $account == null ) {
			$account = $this->getValue3();
		}

		return Misc::censorString( $account, '*', 1, 2, 1, 4 );
	}

	/**
	 * VALUE 3 is the account number. It needs to be encrypted.
	 * @param null $value
	 * @return bool|string
	 */
	function getValue3( $value = null ) {
		if ( $value == null ) {
			$value = $this->getGenericDataValue( 'value3' );
		}

		$value = Misc::decrypt( $value, null, TTPassword::getPasswordSalt( $this->getCompany() ) );

		//We must check is_numeric to ensure that the value properly decrypted.
		if ( isset( $value ) && $this->Validator->isAlphaNumeric( null, $value ) == false ) {
			Debug::Text( 'DECRYPTION FAILED: Your salt may have changed.', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			return $value;
		}

		return false;
	}

	/**
	 * VALUE 3 is the account number. It needs to be encrypted.
	 * @param $value
	 * @return bool
	 */
	function setValue3( $value ) {
		//If X's are in the account number, skip setting it
		// Also if a colon is in the account number, its likely an encrypted string, also skip.
		//This allows them to change other data without seeing the account number.

		//$value = $this->Validator->stripNonAlphaNumeric( trim( $value ) ); //Don't strip invalid characters to be a little more strict on ensuring what they input is correct.
		$value = trim( $value );

		if ( stripos( $value, '*' ) !== false || stripos( $value, ':' ) !== false ) {
			return false;
		}

		if ( $value != '' ) { //Make sure we can clear out the value if needed. Misc::encypt() will return FALSE on a blank value.
			$encrypted_value = Misc::encrypt( $value, null, TTPassword::getPasswordSalt( $this->getCompany() ) );
			if ( $encrypted_value === false ) {
				return false;
			}
		} else {
			$encrypted_value = $value;
		}

		return $this->setGenericDataValue( 'value3', $encrypted_value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue4() {
		return $this->getGenericDataValue( 'value4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value4', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue5() {
		return $this->getGenericDataValue( 'value5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value5', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getValue6() {
		return $this->getGenericDataValue( 'value6' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue6( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value6', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue7() {
		return $this->getGenericDataValue( 'value7' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue7( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value7', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue8() {
		return $this->getGenericDataValue( 'value8' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue8( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value8', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue9() {
		return $this->getGenericDataValue( 'value9' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue9( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value9', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue10() {
		return $this->getGenericDataValue( 'value10' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue10( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value10', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue11() {
		return $this->getGenericDataValue( 'value11' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue11( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value11', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue12() {
		return $this->getGenericDataValue( 'value12' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue12( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value12', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue13() {
		return $this->getGenericDataValue( 'value13' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue13( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value13', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue14() {
		return $this->getGenericDataValue( 'value14' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue14( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value14', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue15() {
		return $this->getGenericDataValue( 'value15' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue15( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value15', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getValue16() {
		return $this->getGenericDataValue( 'value16' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue16( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value16', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue17() {
		return $this->getGenericDataValue( 'value17' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue17( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value17', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue18() {
		return $this->getGenericDataValue( 'value18' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue18( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value18', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue19() {
		return $this->getGenericDataValue( 'value19' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue19( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value19', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue20() {
		return $this->getGenericDataValue( 'value20' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue20( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value20', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue21() {
		return $this->getGenericDataValue( 'value21' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue21( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value21', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue22() {
		return $this->getGenericDataValue( 'value22' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue22( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value22', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue23() {
		return $this->getGenericDataValue( 'value23' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue23( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value23', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue24() {
		return $this->getGenericDataValue( 'value24' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue24( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value24', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue25() {
		return $this->getGenericDataValue( 'value25' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue25( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value25', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getValue26() {
		return $this->getGenericDataValue( 'value26' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue26( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value26', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue27() {
		return $this->getGenericDataValue( 'value27' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue27( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value27', $value );
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @param null $account
	 * @return bool|string
	 */
	function getSecureValue28( $account = null ) {
		if ( $account == null ) {
			$account = $this->getValue28();
		}

		return Misc::censorString( $account, '*', 1, 2, 1, 4 );
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @return bool|string
	 */
	function getValue28() {
		$value = $this->getGenericDataValue( 'value28' );
		if ( $value !== false ) {
			$retval = Misc::decrypt( $value, null, TTPassword::getPasswordSalt( $this->getCompany() ) );
			if ( is_numeric( $retval ) ) {
				return $retval;
			}
		}

		return false;
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @param $value
	 * @return bool
	 */
	function setValue28( $value ) {
		//If X's are in the account number, skip setting it
		// Also if a colon is in the account number, its likely an encrypted string, also skip.
		//This allows them to change other data without seeing the account number.
		if ( stripos( $value, '*' ) !== false || stripos( $value, ':' ) !== false ) {
			return false;
		}

		$value = trim( $value );
		if ( $value != '' ) { //Make sure we can clear out the value if needed. Misc::encypt() will return FALSE on a blank value.
			$encrypted_value = Misc::encrypt( $value, null, TTPassword::getPasswordSalt( $this->getCompany() ) );
			if ( $encrypted_value === false ) {
				return false;
			}
		} else {
			$encrypted_value = $value;
		}

		return $this->setGenericDataValue( 'value28', $encrypted_value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue29() {
		return $this->getGenericDataValue( 'value29' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue29( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value29', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue30() {
		return $this->getGenericDataValue( 'value30' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue30( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value30', $value );
	}


	/**
	 * @return bool
	 */
	function isSignatureExists() {
		return file_exists( $this->getSignatureFileName() );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @return bool|string
	 */
	function getSignatureFileName( $company_id = null, $id = null ) {
		if ( $id == null ) {
			$id = $this->getId();
		}

		//Test for both jpg and png
		$base_name = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR . $id;
		if ( file_exists( $base_name . '.img' ) ) {
			$signature_file_name = $base_name . '.img';
		} else {
			$signature_file_name = false;
		}

		//Debug::Text('Logo File Name: '. $signature_file_name .' Base Name: '. $base_name .' User ID: '. $user_id .' Include Default: '. (int)$include_default_signature, __FILE__, __LINE__, __METHOD__, 10);
		return $signature_file_name;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @return bool
	 */
	function cleanStoragePath( $company_id = null, $id = null ) {
		if ( $company_id == '' ) {
			if ( is_object( $this->getLegalEntityObject() ) ) {
				$company_id = $this->getLegalEntityObject()->getCompany();
			}
		}

		if ( $company_id == '' ) {
			return false;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;
		if ( $dir != '' ) {
			if ( $id != '' ) {
				@unlink( $this->getSignatureFileName( $company_id, $id ) ); //Delete just signature.
			} else {
				//Delete tmp files.
				foreach ( glob( $dir . '*' ) as $filename ) {
					unlink( $filename );
					Misc::deleteEmptyParentDirectory( dirname( $filename ), 0 ); //Recurse to $user_id parent level and remove empty directories.
				}
			}
		}

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @return bool|string
	 */
	function getStoragePath( $company_id = null, $id = null ) {
		if ( $company_id == '' ) {
			if ( is_object( $this->getLegalEntityObject() ) ) {
				$company_id = $this->getLegalEntityObject()->getCompany();
			}
		}

		if ( $company_id == '' || TTUUID::isUUID( $company_id ) == false ) {
			return false;
		}

		return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR . 'remittance_source_account' . DIRECTORY_SEPARATOR . $company_id;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Legal entity
		if ( $this->getLegalEntity() !== false && $this->getLegalEntity() != TTUUID::getNotExistID() ) {
			$llf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $llf */
			$this->Validator->isResultSetWithRows( 'legal_entity_id',
												   $llf->getByID( $this->getLegalEntity() ),
												   TTi18n::gettext( 'Legal entity is invalid' )
			);
		}

		//When using TimeTrex EFT service, all source accounts must be directly assigned to a legal entity.
		if ( $this->getType() == 3000 && $this->getDataFormat() == 5 && $this->getLegalEntity() == TTUUID::getNotExistID() ) {
			$this->Validator->isTrue( 'legal_entity_id',
									  false,
									  TTi18n::gettext( 'Legal Entity must be specified' )
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
		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status_id',
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
		}
		// Country
		if ( $this->getCountry() !== false ) {
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Incorrect Country' ),
										  $this->getOptions( 'country' )
			);
		}
		// Data format
		if ( $this->getDataFormat() !== false ) {
			$this->Validator->inArrayKey( 'data_format_id',
										  $this->getDataFormat(),
										  TTi18n::gettext( 'Incorrect data format' ),
										  $this->getOptions( 'data_format', [ 'type_id' => $this->getType(), 'country' => $this->getCountry() ] )
			);
		}
		// Name
		if ( $this->getName() !== false && $this->getName() != '' ) {
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
										  TTi18n::gettext( 'Name already exists' )
				);
			}
		}
		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is too short or too long' ),
									0, 255
		);

		$this->Validator->isHTML( 'description',
								  $this->getDescription(),
								  TTi18n::gettext( 'Description contains invalid special characters' ),
		);

		// Value 1
		if ( $this->getValue1() != '' ) {
			$this->Validator->isLength( 'value1',
										$this->getValue1(),
										TTi18n::gettext( 'Value 1 is invalid' ),
										1, 255
			);
		}
		// Value 2
		if ( $this->getValue2() != '' ) {
			$this->Validator->isLength( 'value2',
										$this->getValue2(),
										TTi18n::gettext( 'Value 2 is invalid' ),
										1, 255
			);
		}
		// Value 4
		if ( $this->getValue4() != '' ) {
			$this->Validator->isLength( 'value4',
										$this->getValue4(),
										TTi18n::gettext( 'Value 4 is invalid' ),
										1, 255
			);
		}
		// Value 5
		if ( $this->getValue5() != '' ) {
			$this->Validator->isLength( 'value5',
										$this->getValue5(),
										TTi18n::gettext( 'Value 5 is invalid' ),
										1, 255
			);
		}
		// Value 6
		if ( $this->getValue6() != '' ) {
			$this->Validator->isLength( 'value6',
										$this->getValue6(),
										TTi18n::gettext( 'Value 6 is invalid' ),
										1, 255
			);
		}
		// Value 7
		if ( $this->getValue7() != '' ) {
			$this->Validator->isLength( 'value7',
										$this->getValue7(),
										TTi18n::gettext( 'Value 7 is invalid' ),
										1, 255
			);
		}
		// Value 8
		if ( $this->getValue8() != '' ) {
			$this->Validator->isLength( 'value8',
										$this->getValue8(),
										TTi18n::gettext( 'Value 8 is invalid' ),
										1, 255
			);
		}
		// Value 9
		if ( $this->getValue9() != '' ) {
			$this->Validator->isLength( 'value9',
										$this->getValue9(),
										TTi18n::gettext( 'Value 9 is invalid' ),
										1, 255
			);
		}
		// Value 10
		if ( $this->getValue10() != '' ) {
			$this->Validator->isLength( 'value10',
										$this->getValue10(),
										TTi18n::gettext( 'Value 10 is invalid' ),
										1, 255
			);
		}
		// Value 11
		if ( $this->getValue11() != '' ) {
			$this->Validator->isLength( 'value11',
										$this->getValue11(),
										TTi18n::gettext( 'Value 11 is invalid' ),
										1, 255
			);
		}
		// Value 12
		if ( $this->getValue12() != '' ) {
			$this->Validator->isLength( 'value12',
										$this->getValue12(),
										TTi18n::gettext( 'Value 12 is invalid' ),
										1, 255
			);
		}
		// Value 13
		if ( $this->getValue13() != '' ) {
			$this->Validator->isLength( 'value13',
										$this->getValue13(),
										TTi18n::gettext( 'Value 13 is invalid' ),
										1, 255
			);
		}
		// Value 14
		if ( $this->getValue14() != '' ) {
			$this->Validator->isLength( 'value14',
										$this->getValue14(),
										TTi18n::gettext( 'Value 14 is invalid' ),
										1, 255
			);
		}
		// Value 15
		if ( $this->getValue15() != '' ) {
			$this->Validator->isLength( 'value15',
										$this->getValue15(),
										TTi18n::gettext( 'Value 15 is invalid' ),
										1, 255
			);
		}
		// Value 16
		if ( $this->getValue16() != '' ) {
			$this->Validator->isLength( 'value16',
										$this->getValue16(),
										TTi18n::gettext( 'Value 16 is invalid' ),
										1, 255
			);
		}
		// Value 17
		if ( $this->getValue17() != '' ) {
			$this->Validator->isLength( 'value17',
										$this->getValue17(),
										TTi18n::gettext( 'Value 17 is invalid' ),
										1, 255
			);
		}
		// Value 18
		if ( $this->getValue18() != '' ) {
			$this->Validator->isLength( 'value18',
										$this->getValue18(),
										TTi18n::gettext( 'Value 18 is invalid' ),
										1, 255
			);
		}
		// Value 19
		if ( $this->getValue19() != '' ) {
			$this->Validator->isLength( 'value19',
										$this->getValue19(),
										TTi18n::gettext( 'Value 19 is invalid' ),
										1, 255
			);
		}
		// Value 20
		if ( $this->getValue20() != '' ) {
			$this->Validator->isLength( 'value20',
										$this->getValue20(),
										TTi18n::gettext( 'Value 20 is invalid' ),
										1, 255
			);
		}
		// Value 21
		if ( $this->getValue21() != '' ) {
			$this->Validator->isLength( 'value21',
										$this->getValue21(),
										TTi18n::gettext( 'Value 21 is invalid' ),
										1, 255
			);
		}
		// Value 22
		if ( $this->getValue22() != '' ) {
			$this->Validator->isLength( 'value22',
										$this->getValue22(),
										TTi18n::gettext( 'Value 22 is invalid' ),
										1, 255
			);
		}
		// Value 23
		if ( $this->getValue23() != '' ) {
			$this->Validator->isLength( 'value23',
										$this->getValue23(),
										TTi18n::gettext( 'Value 23 is invalid' ),
										1, 255
			);
		}
		// Value 24
		if ( $this->getValue24() != '' ) {
			$this->Validator->isLength( 'value24',
										$this->getValue24(),
										TTi18n::gettext( 'Value 24 is invalid' ),
										1, 255
			);
		}
		// Value 25
		if ( $this->getValue25() != '' ) {
			$this->Validator->isLength( 'value25',
										$this->getValue25(),
										TTi18n::gettext( 'Value 25 is invalid' ),
										1, 255
			);
		}
		// Value 26
		if ( $this->getValue26() != '' ) {
			$this->Validator->isLength( 'value26',
										$this->getValue26(),
										TTi18n::gettext( 'Value 26 is invalid' ),
										1, 255
			);
		}
		// Value 27
		if ( $this->getValue27() != '' ) {
			$this->Validator->isLength( 'value27',
										$this->getValue27(),
										TTi18n::gettext( 'Value 27 is invalid' ),
										1, 255
			);
		}
		// Value 28
		if ( $this->getValue28() != '' ) {
			$this->Validator->isLength( 'value28',
										$this->getValue28(),
										TTi18n::gettext( 'Value 28 is invalid' ),
										1, 255
			);
		}
		// Value 29
		if ( $this->getValue29() != '' ) {
			$this->Validator->isLength( 'value29',
										$this->getValue29(),
										TTi18n::gettext( 'Value 29 is invalid' ),
										1, 255
			);
		}
		// Value 30
		if ( $this->getValue30() != '' ) {
			$this->Validator->isLength( 'value30',
										$this->getValue30(),
										TTi18n::gettext( 'Value 30 is invalid' ),
										1, 255
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Linked remittance destination records need to be checked in multiple places.
		$linked_remittance_destination_records = 0;
		$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
		$rdalf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
		if ( $rdalf->getRecordCount() > 0 ) {
			$linked_remittance_destination_records = $rdalf->getRecordCount();
		}
		unset( $rdalf );

		$data_diff = $this->getDataDifferences();

		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.
		if ( $this->getDeleted() == true ) {
			if ( $linked_remittance_destination_records > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This remittance source account is currently in use' ) . ' ' . TTi18n::gettext( 'by employee pay methods' ) );
			}

			$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
			$pralf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $pralf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This remittance source account is currently in use' ) . ' ' . TTi18n::gettext( 'by remittance agencies' ) );
			}

			$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
			$pstlf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $pstlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This remittance source account is currently in use' ) . ' ' . TTi18n::gettext( 'by pay stub transactions' ) );
			}
		}

		if ( $this->Validator->getValidateOnly() == false ) { //Make sure we can mass edit type/source account, so validating these has to be delayed.
			if ( $this->getStatus() == 10 ) { //10=Enabled - Only validate when status is enabled, so records that are invalid but used in the past can always be disabled.
				if ( $this->getType() == 2000 ) {
					// when type is CHECK
					if ( $this->getLastTransactionNumber() !== false ) {
						$value = $this->Validator->stripNonNumeric( $this->getLastTransactionNumber() );
						$this->Validator->isFloat(
								'last_transaction_number',
								$value,
								TTi18n::gettext( 'Incorrect last check number' ) );
					}
				} else if ( $this->getType() == 3000 && $this->getCountry() == 'US' ) {
					// when type is ACH
					if ( $this->getLastTransactionNumber() !== false ) {
						$value = $this->Validator->stripNonNumeric( $this->getLastTransactionNumber() );
						$this->Validator->isFloat(
								'last_transaction_number',
								$value,
								TTi18n::gettext( 'Incorrect last batch number' ) );
					}
					// Routing number
					if ( $this->getValue2() !== false ) {
						if ( strlen( $this->getValue2() ) != 9 ) {
							$this->Validator->isTrue( 'value2',
													  false,
													  TTi18n::gettext( 'Invalid routing number length, must be 9 digits' ) );
						} else {
							$this->Validator->isDigits( 'value2',
														$this->getValue2(),
														TTi18n::gettext( 'Invalid routing number, must be digits only' ) );
						}
					}
					// Account number
					if ( $this->getValue3() !== false && ( strlen( $this->getValue3() ) < 3 || strlen( $this->getValue3() ) > 17 ) ) {
						$this->Validator->isTrue( 'value3',
												  false,
												  TTi18n::gettext( 'Invalid account number length, must be between 3 and 17 digits' ) );
					} else {
						$this->Validator->isAlphaNumeric( 'value3',
														  $this->getValue3(),
														  TTi18n::gettext( 'Invalid account number, must be alpha numeric only' ) );
					}
				} else if ( $this->getType() == 3000 && $this->getCountry() == 'CA' ) {
					// when type is EFT
					if ( $this->getLastTransactionNumber() !== false ) {
						$this->Validator->isFloat(
								'last_transaction_number',
								$this->Validator->stripNonNumeric( $this->getLastTransactionNumber() ),
								TTi18n::gettext( 'Incorrect last batch number' ) );
					}
					// Institution number
					if ( $this->getValue1() !== false ) {
						if ( strlen( $this->getValue1() ) != 3 ) {
							$this->Validator->isTrue( 'value1',
													  false,
													  TTi18n::gettext( 'Invalid institution number length, must be 3 digits' ) );
						} else {
							$this->Validator->isDigits( 'value1',
														$this->getValue1(),
														TTi18n::gettext( 'Invalid institution number, must be digits only' ) );
						}
					}
					// Transit number
					if ( $this->getValue2() !== false ) {
						if ( strlen( $this->getValue2() ) != 5 ) {
							$this->Validator->isTrue( 'value2',
													  false,
													  TTi18n::gettext( 'Invalid transit number length, must be 5 digits' ) );
						} else {
							$this->Validator->isDigits( 'value2',
														$this->getValue2(),
														TTi18n::gettext( 'Invalid transit number, must be digits only' ) );
						}
					}
					// Account number
					if ( $this->getValue3() !== false && ( strlen( $this->getValue3() ) < 3 || strlen( $this->getValue3() ) > 12 ) ) {
						$this->Validator->isTrue( 'value3',
												  false,
												  TTi18n::gettext( 'Invalid account number length, must be between 3 and 12 digits' ) );
					} else {
						$this->Validator->isDigits( 'value3',
													$this->getValue3(),
													TTi18n::gettext( 'Invalid account number, must be digits only' ) );
					}
				}
			} else {
				//Source account is disabled, make sure no active destination accounts are linked to it.
				$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
				$rdalf->getByRemittanceSourceAccountIdAndStatusId( $this->getId(), 10, 1 ); //Limit 1.
				if ( $rdalf->getRecordCount() > 0 ) {
					$this->Validator->isTrue( 'status_id',
											  false,
											  TTi18n::gettext( 'Disabled remittance source account is currently in use by enabled employee pay methods' ) );
				}
				unset( $rdalf );

			}
		}

		//Make sure the name does not contain the account number for security reasons.
		// **NOTE: If the stripos() needle is a false, null, or an empty string and haystack is also empty it will return 0 rather than FALSE. So just use a random UUID to work around this.
		$this->Validator->isTrue( 'name',
				( ( stripos( $this->Validator->stripNonNumeric( $this->getName() ), ( ( $this->getValue3() == '' ) ? TTUUID::generateUUID() : $this->getValue3() ) ) !== false ) ? false : true ),
								  TTi18n::gettext( 'Account number must not be a part of the Name' ) );

		//Make sure the description does not contain the account number for security reasons.
		$this->Validator->isTrue( 'description',
				( ( stripos( $this->Validator->stripNonNumeric( $this->getDescription() ), ( ( $this->getValue3() == '' ) ? TTUUID::generateUUID() : $this->getValue3() ) ) !== false ) ? false : true ),
								  TTi18n::gettext( 'Account number must not be a part of the Description' ) );

		//Don't allow type to be changed if its already in use. It also prevents further errors when trying to edit/delete destination records where a type mismatch occurs.
		if ( is_array( $data_diff ) && $this->isDataDifferent( 'type_id', $data_diff ) && $linked_remittance_destination_records > 0 ) { //Type has changed
			$this->Validator->isTRUE( 'type_id',
									  false,
									  TTi18n::gettext( 'This remittance source account is currently in use by employee pay methods of a different type' ) );
		}

		if ( is_array( $data_diff ) && $this->isDataDifferent( 'legal_entity_id', $data_diff ) ) { //Legal entity has changed
			//Cases to handle:
			//  Always allow going from a specific legal entity to ANY without any additional validation checks.
			//  Switching from a specific legal entity to another specific legal entity should check that destination accounts aren't assigned.
			//  Switching from ANY legal enity to any specific legal entity, should ensure that all destination accounts are assigned to the same legal entity.
			$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */

			if ( $this->getLegalEntity() != TTUUID::getNotExistID() && $data_diff['legal_entity_id'] != TTUUID::getNotExistID() ) { //Switching from any specific legal entity to any other specific legal entity.
				$rdalf->getByRemittanceSourceAccountId( $this->getId(), 1 );                                                         //Limit 1.
				if ( $rdalf->getRecordCount() > 0 ) {
					$this->Validator->isTrue( 'legal_entity_id',
											  false,
											  TTi18n::gettext( 'This remittance source account is currently in use by employee pay methods' ) );
				}
			} else if ( $this->getLegalEntity() != TTUUID::getNotExistID() && $data_diff['legal_entity_id'] == TTUUID::getNotExistID() ) { //Switching from ANY legal entity to a specific legal entity.
				//Make sure all destination accounts users are assigned to the same legal entity and they are trying to switch to.
				$rdalf->getByRemittanceSourceAccountIdAndNotUserLegalEntityId( $this->getId(), $this->getLegalEntity(), 1 );                //Limit 1
				if ( $rdalf->getRecordCount() > 0 ) {
					foreach ( $rdalf as $rda_obj ) {
						$this->Validator->isTrue( 'legal_entity_id',
												  false,
												  TTi18n::gettext( 'This remittance source account is currently in use by employee pay methods assigned to a different legal entity. (%1)', $rda_obj->getUserObject()->getFullName() ) );
						break;
					}
				}
			}

			unset( $rdalf );
		}

		if ( is_array( $data_diff ) && $this->isDataDifferent( 'country', $data_diff ) ) { //Country has changed
			//Cases to handle:
			//  Don't allow changing the country if destination accounts are linked to it already, as that will change the bank account validations and such.
			$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
			$rdalf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //Limit 1.
			if ( $rdalf->getRecordCount() > 0 ) {
				$this->Validator->isTrue( 'country',
										  false,
										  TTi18n::gettext( 'This remittance source account is currently in use by employee pay methods in a different country' ) );
			}
			unset( $rdalf );
		}

		//Make sure these fields are always specified, but don't break mass edit.
		if ( $this->Validator->getValidateOnly() == false && $this->getLegalEntity() != TTUUID::getNotExistID() ) {
			if ( $this->getLegalEntity() == false && $this->Validator->hasError( 'legal_entity_id' ) == false ) {
				$this->Validator->isTrue( 'legal_entity_id',
										  false,
										  TTi18n::gettext( 'Please specify a legal entity' ) );
			}

			if ( $this->getCurrency() == false && $this->Validator->hasError( 'currency_id' ) == false ) {
				$this->Validator->isTrue( 'currency_id',
										  false,
										  TTi18n::gettext( 'Please specify a currency' ) );
			}

			if ( $this->getStatus() == false && $this->Validator->hasError( 'status_id' ) == false ) {
				$this->Validator->isTrue( 'status_id',
										  false,
										  TTi18n::gettext( 'Please specify status' ) );
			}

			if ( $this->getType() == false && $this->Validator->hasError( 'type_id' ) == false ) {
				$this->Validator->isTrue( 'type_id',
										  false,
										  TTi18n::gettext( 'Please specify type' ) );
			}
		}

		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.
			if ( $this->getName() == false && $this->Validator->hasError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}

			if ( $this->getDataFormat() == false ) {
				$this->Validator->isTrue( 'data_format_id',
										  false,
										  TTi18n::gettext( 'Please specify data format' ) );
			}
		}

		if ( $ignore_warning == false && $this->getDeleted() == false && $this->getStatus() == 10 ) {
			$le_obj = $this->getLegalEntityObject();

			if ( $this->getType() == 3000 && $this->getDataFormat() == 5 && is_object( $this->getLegalEntityObject() ) ) { //3000=EFT/ACH, 5=TimeTrex EFT
				if ( $le_obj->getPaymentServicesStatus() == 10 ) {
					$this->Validator->isTrue( 'data_format_id',
											  $le_obj->checkPaymentServicesCredentials(),
											  TTi18n::gettext( 'Payment Services User Name or API Key is incorrect, or service not activated' ) );
				} else {
					$this->Validator->isTrue( 'data_format_id',
											  false,
											  TTi18n::gettext( 'Payment Services are not enabled for this Legal Entity' ) );
				}
			}

			//Even if Payment Services is not enabled, we can still validate bank account information while being unauthenticated.
			if ( PRODUCTION == true && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL
					&& $this->getType() == 3000 //3000=EFT/ACH
					&& is_object( $le_obj ) && is_object( $le_obj->getCompanyObject() ) && $le_obj->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				try {
					$tt_ps_api = $le_obj->getPaymentServicesAPIObject();
					$retval = $tt_ps_api->validateBankAccount( $tt_ps_api->convertRemittanceSourceAccountObjectToBankAccountArray( $this ) );
					if ( is_object( $retval ) && $retval->isValid() === false ) {
						Debug::Text( 'ERROR! Unable to validate remittance destination account data through Payment Services API... (a)', __FILE__, __LINE__, __METHOD__, 10 );
						$api_f = new APIRemittanceDestinationAccount();
						$validation_arr = $api_f->convertAPIReturnHandlerToValidatorObject( $retval->getResultData() );

						$this->Validator->merge( $validation_arr );
					}
				} catch ( Exception $e ) {
					Debug::Text( 'ERROR! Unable to validate remittance destination account  data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
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

		if ( $this->getType() == 3000 && $this->getDataFormat() == 5 ) { //3000=EFT/ACH, 5=TimeTrex EFT
			//Send data to TimeTrex Payment Services.
			$le_obj = $this->getLegalEntityObject();
			if ( PRODUCTION == true && is_object( $le_obj ) && $le_obj->getPaymentServicesStatus() == 10 && $le_obj->getPaymentServicesUserName() != '' && $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
				try {
					$tt_ps_api = $le_obj->getPaymentServicesAPIObject();
					$retval = $tt_ps_api->setRemittanceSourceAccount( $tt_ps_api->convertRemittanceSourceAccountObjectToBankAccountArray( $this ) );
					if ( $retval === false ) {
						Debug::Text( 'ERROR! Unable to upload remittance source account data... (a)', __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					}
				} catch ( Exception $e ) {
					Debug::Text( 'ERROR! Unable to upload remittance source account data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'ERROR! Payment Services not enable in legal entity!', __FILE__, __LINE__, __METHOD__, 10 );
			}
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
						case 'legal_name':
						case 'currency':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'data_format':
							$data[$variable] = Option::getByKey( $this->getDataFormat(), $this->getOptions( $variable, [ 'type_id' => $this->getType() ] ) );
							break;
						case 'value3': //Account Number
							$data[$variable] = $this->getSecureValue3();
							break;
						case 'value28': //Return Account Number
							$data[$variable] = $this->getSecureValue28();
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
	 * @param $lf
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf, $include_blank = true ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		$list = [];
		if ( $include_blank == true ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ( $lf as $obj ) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Remittance source account' ) . ': ' . $this->getName(), null, $this->getTable(), $this );
	}

}

?>
