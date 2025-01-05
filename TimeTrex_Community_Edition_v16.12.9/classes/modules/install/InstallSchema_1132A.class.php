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
 * @package Modules\Install
 */
class InstallSchema_1132A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		self::convertOtherFields( $this->getDatabaseConnection(), [ 2, 4, 5, 10, 12, 15, 18 ] );

		//Permissions need to be converted after other fields, as punch -> edit_custom_field<uuid> permissions needs to find related custom fields.
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */

				//Custom field IDs to map permissions for punch -> edit_other_id1 etc too
				$punch_custom_field_map = [];
				$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
				$cflf->getByCompanyIdAndParentTableAndEnabled( $c_obj->getId(), 'punch_control' );
				if ( $cflf->getRecordCount() > 0 ) {
					foreach ( $cflf as $cf_obj ) { /** @var CustomFieldFactory $cf_obj */
						//Need the first 5 to match previous edit permissions, not only 5 should be possible to exist in the first place.
						if ( $cf_obj->getLegacyOtherFieldId() < 6 ) {
							$punch_custom_field_map[$cf_obj->getLegacyOtherFieldId()] = $cf_obj->getPrefixedCustomFieldID();
						}
					}
				}

				Debug::Text( 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
				$pclf->getByCompanyId( $c_obj->getId() );
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) { /** @var PermissionControlFactory $pc_obj */
						Debug::Text( '  Permission Control: ' . $pc_obj->getName() . ' ID: ' . $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//Convert punch edit_other_id permissions to custom_fields
						$pflf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $pflf */
						$pflf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $c_obj->getId(), $pc_obj->getId(), 'punch', [ 'edit_other_id1', 'edit_other_id2', 'edit_other_id3', 'edit_other_id4', 'edit_other_id5' ] );
						if ( $pflf->getRecordCount() > 0 ) {
							foreach ( $pflf as $p_obj ) { /** @var PermissionFactory $p_obj */
								$edit_id = str_replace( 'edit_other_id', '', $p_obj->getName() );
								Debug::Text( '    Trying to convert Punch Permission: ' . $p_obj->getName() . ' ID: ' . $p_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								if ( isset( $punch_custom_field_map[$edit_id] ) ) {
									$p_obj->setName( 'edit_' . $punch_custom_field_map[$edit_id] );
									if ( $p_obj->isValid() ) {
										Debug::Text( '      Punch Converted Permission: ' . $p_obj->getName() . ' ID: ' . $p_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										$p_obj->Save();
									}
								} else {
									$p_obj->setDeleted( true ); //No custom field exists to match, delete this permission
									if ( $p_obj->isValid() ) {
										Debug::Text( '      Punch Deleted Permission: ' . $p_obj->getName() . ' ID: ' . $p_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										$p_obj->Save();
									}
								}
							}
						}

						//Convert all other_field permissions to custom_fields
						$pflf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $pflf */
						$pflf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $c_obj->getId(), $pc_obj->getId(), 'other_field', [ 'enabled', 'view_own', 'view', 'add', 'edit_own', 'edit', 'delete_own', 'delete' ] );
						if ( $pflf->getRecordCount() > 0 ) {
							foreach ( $pflf as $p_obj ) { /** @var PermissionFactory $p_obj */
								$p_obj->setSection( 'custom_field' );
								if ( $p_obj->isValid() ) {
									Debug::Text( '      OtherField Converted Permission: ' . $p_obj->getName() . ' ID: ' . $p_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
									$p_obj->Save();
								}
							}
						}
					}
				}
			}
		}

		$clf->CommitTransaction();

		return true;
	}

	/**
	 * @param array $other_id_type_to_convert
	 */
	static function convertOtherFields( $database_connection, $other_id_type_to_convert ) {
		$cf_obj = TTnew( 'CustomFieldFactory' ); /** @var CustomFieldFactory $cf_obj */
		$other_field_type_to_parent_table_map = $cf_obj->getOptions( 'legacy_type_to_parent_table' );

		$other_id_func_names = [
				'getOtherID1' => 'other_id1',
				'getOtherID2' => 'other_id2',
				'getOtherID3' => 'other_id3',
				'getOtherID4' => 'other_id4',
				'getOtherID5' => 'other_id5',
		];

		//Map old other_id values to new custom fields.
		$other_to_custom_field_map = [];

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */
				$oflf = TTnew( 'OtherFieldListFactory' ); /** @var OtherFieldListFactory $oflf */
				$oflf->getByCompanyId( $c_obj->getId() );
				if ( $oflf->getRecordCount() > 0 ) {
					Debug::Text( 'Company: ' . $c_obj->getName() .' Other Field Record Count: '. $oflf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

					foreach ( $oflf as $of_obj ) { /** @var OtherFieldListFactory $of_obj */
						Debug::Text( '  Attempting to convert Other Field record: '. $of_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
						//Only convert other field types that belong to the schema edition (a, b, c, d)
						if ( in_array( $of_obj->getType(), $other_id_type_to_convert ) === false ) {
							Debug::Text( '    Skipping Other Field due to type filter: Type: '. $of_obj->getType() .' Filter: '. implode( ',', $other_id_type_to_convert ), __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						foreach ( $other_id_func_names as $other_id_func_name => $other_id_field_name ) {
							if ( $of_obj->$other_id_func_name() !== '' && $of_obj->$other_id_func_name() !== false ) { //Cannot use lose check as 0 is a valid value.

								$legacy_id = (int)substr( $other_id_func_name, -1 ); //get ID from funcName

								//Getting custom fields by company id, parent table and legacy id, if exists use that custom field id instead of creating a new one.
								$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
								$cflf->getByCompanyIdAndParentTableAndLegacyId( $c_obj->getId(), $other_field_type_to_parent_table_map[$of_obj->getType()], $legacy_id );
								if ( $cflf->getRecordCount() == 1 ) {
									$cf_obj = $cflf->getCurrent();
								} else if ( $cflf->getRecordCount() == 0 ) {
									$cf_obj = TTnew( 'CustomFieldFactory' ); /** @var CustomFieldFactory $cf_obj */
									$cf_obj->setId( $cf_obj->getNextInsertId() );
									$cf_obj->setCompany( $c_obj->getId() );
									$cf_obj->setParentTable( $other_field_type_to_parent_table_map[$of_obj->getType()] );
									$cf_obj->setType( 100 );

									$cf_obj->setLegacyOtherFieldId( $legacy_id );
									$cf_obj->setDisplayOrder( $legacy_id ); //Default display order to legacy ID to keep in similar order to other fields.

									$cf_obj->setName( substr( $of_obj->$other_id_func_name(), 0, 90 ) );
									//Check if the name is already in use, and if so add random characters to the end.
									if ( $cf_obj->isUniqueName( $cf_obj->getName() ) == false ) {
										$cf_obj->setName( substr( $of_obj->$other_id_func_name(), 0, 90 ) . ' (' . rand( 1000, 9999 ) . ')' );
										Debug::Text( '  NOTICE: Found duplicate name, appending random digits... Name: ' . $cf_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
									}

									if ( $cf_obj->isValid() ) {
										Debug::Text( '  Migrating OtherField: ' . $other_id_field_name . ' to Custom Field ID: ' . $cf_obj->getId() . ' Name: ' . $of_obj->$other_id_func_name(), __FILE__, __LINE__, __METHOD__, 10 );

										$cf_obj->Save( false, true );
									} else {
										Debug::Text( '  ERROR: Unable to save custom field...', __FILE__, __LINE__, __METHOD__, 10 );
										unset( $cf_obj ); //Unset so below mapping doesn't get created.
									}
								} else {
									Debug::Text( '  ERROR: Found more than one custom field with legacy ID: ' . $legacy_id . ' parent table: ' .  $other_field_type_to_parent_table_map[$of_obj->getType()], __FILE__, __LINE__, __METHOD__, 10 );
									unset( $cf_obj ); //Unset so below mapping doesn't get created.
								}

								if ( isset( $cf_obj ) &&  is_object( $cf_obj ) ) {
									//Map new custom field ID to other_id*
									if ( isset( $other_to_custom_field_map[$c_obj->getId()] ) == false ) {
										$other_to_custom_field_map[$c_obj->getId()] = [];
									}
									if ( isset( $other_to_custom_field_map[$c_obj->getId()][$other_field_type_to_parent_table_map[$of_obj->getType()]] ) == false ) {
										$other_to_custom_field_map[$c_obj->getId()][$other_field_type_to_parent_table_map[$of_obj->getType()]] = [];
									}
									$other_to_custom_field_map[$c_obj->getId()][$other_field_type_to_parent_table_map[$of_obj->getType()]] += [ $other_id_field_name => $cf_obj->getId() ];
								}
							} else {
								Debug::Text( '  NOTICE: Skipping: Converting Other Field record: '. $of_obj->getID() .' Field Name: '. $other_id_field_name, __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
				}
			}
			Debug::Arr( $other_to_custom_field_map, 'Other to Custom Field Map: ', __FILE__, __LINE__, __METHOD__, 10 );

			if ( count( $other_to_custom_field_map ) > 0 ) {
				$tables_with_other_id_fields = []; //List of tables being used that we need to select from to get the other_id* fields.
				foreach ( $other_to_custom_field_map as $company_map ) {
					$tables_with_other_id_fields = array_merge( $tables_with_other_id_fields, array_keys( $company_map ) );
				}
				$tables_with_other_id_fields = array_unique( $tables_with_other_id_fields );
				sort( $tables_with_other_id_fields );
				Debug::Arr( $tables_with_other_id_fields, 'Tables with Other Fields: ', __FILE__, __LINE__, __METHOD__, 10 );

				//Only get actual custom fields being used and match to company_id.
				foreach ( $tables_with_other_id_fields as $other_field_table ) {
					if ( $other_field_table === 'punch_control' ) { //Needs to select ON user table also to get correct company_id.
						$from_where_query = ' b.company_id FROM punch_control as a, users as b WHERE a.user_id = b.id AND';
					} else if ( $other_field_table === 'client_contact' ) { //Needs to select client user table also to get correct company_id.
						$from_where_query = ' b.company_id FROM invoice as a, client as b WHERE a.client_id = b.id AND';
					} else if ( $other_field_table === 'invoice' ) { //Needs to select ON client table also to get correct company_id.
						$from_where_query = ' b.company_id FROM client_contact as a, client as b WHERE a.client_id = b.id AND ';
					} else if ( $other_field_table === 'company' ) {
						$from_where_query = ' a.id as company_id FROM company as a WHERE ';
					} else {
						$from_where_query = ' a.company_id FROM ' . $other_field_table . ' as a WHERE ';
					}

					$other_fields_query = 'SELECT a.id, a.other_id1, a.other_id2, a.other_id3, a.other_id4, a.other_id5, a.custom_field, ' . $from_where_query .
							" 
						(
							( a.other_id1 IS NOT NULL AND a.other_id1 != '' AND a.other_id1 != 'false' )
							OR
							( a.other_id2 IS NOT NULL AND a.other_id2 != '' AND a.other_id2 != 'false' )
							OR
							( a.other_id3 IS NOT NULL AND a.other_id3 != '' AND a.other_id3 != 'false' )
							OR
							( a.other_id4 IS NOT NULL AND a.other_id4 != '' AND a.other_id4 != 'false' )
							OR
							( a.other_id5 IS NOT NULL AND a.other_id5 != '' AND a.other_id5 != 'false' )
						);";

					$other_fields = $database_connection->Execute( $other_fields_query );
					Debug::Text( 'OtherField Query: ' . $other_fields_query, __FILE__, __LINE__, __METHOD__, 10 );

					$i = 0;
					while ( $result = $other_fields->fetchRow() ) {
						//Debug::Arr( $result, 'Raw Row to convert in table: ' . $other_field_table, __FILE__, __LINE__, __METHOD__, 10 );

						$custom_fields = [];

						if ( !isset( $other_to_custom_field_map[$result['company_id']] ) ) {
							Debug::Text( 'WARNING: Company ID is not in other_to_custom_field_map, likely other_field was deleted. Company ID: ' . $result['company_id'] . ' Row ID: ' . $result['id'], __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $other_to_custom_field_map, 'bOther to Custom Field Map: ', __FILE__, __LINE__, __METHOD__, 10 );
							continue; //Continue to next row, as the company_id could be specified in it.
						}

						if ( isset( $result['custom_field'] ) && trim( $result['custom_field'] ) != '' ) {
							Debug::Text( '  Custom Field has already been converted, skipping: Company ID: ' . $result['company_id'] . ' Row ID: ' . $result['id'] . ' CustomField: ' . $result['custom_field'], __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						if ( isset( $result['other_id1'] ) && isset( $other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id1'] ) && $result['other_id1'] !== null && $result['other_id1'] !== '' && $result['other_id1'] !== 'false' ) {
							$custom_fields[$other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id1']] = $result['other_id1'];
						}
						if ( isset( $result['other_id2'] ) && isset( $other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id2'] ) && $result['other_id2'] !== null && $result['other_id2'] !== '' && $result['other_id2'] !== 'false' ) {
							$custom_fields[$other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id2']] = $result['other_id2'];
						}
						if ( isset( $result['other_id3'] ) && isset( $other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id3'] ) && $result['other_id3'] !== null && $result['other_id3'] !== '' && $result['other_id3'] !== 'false' ) {
							$custom_fields[$other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id3']] = $result['other_id3'];
						}
						if ( isset( $result['other_id4'] ) && isset( $other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id4'] ) && $result['other_id4'] !== null && $result['other_id4'] !== '' && $result['other_id4'] !== 'false' ) {
							$custom_fields[$other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id4']] = $result['other_id4'];
						}
						if ( isset( $result['other_id5'] ) && isset( $other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id5'] ) && $result['other_id5'] !== null && $result['other_id5'] !== '' && $result['other_id5'] !== 'false' ) {
							$custom_fields[$other_to_custom_field_map[$result['company_id']][$other_field_table]['other_id5']] = $result['other_id5'];
						}

						if ( count( $custom_fields ) > 0 ) {
							Debug::Text( $i . '. Converting other fields from table: ' . $other_field_table . ' ID: ' . $result['id'] . ' New Custom Fields: ' . implode( ', ', array_keys( $custom_fields ) ) .' Values: '. implode( ', ', array_values( $custom_fields ) ), __FILE__, __LINE__, __METHOD__, 10 );
							$database_connection->Execute( 'UPDATE ' . $other_field_table . ' SET custom_field = ? WHERE id = ?', [ json_encode( $custom_fields ), $result['id'] ] );

							$i++;
						}
					}
					Debug::Text( 'Total records converted from: ' . $other_field_table . ': ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			//If there is no prefix #other_id1# vs #branch_other_id1# then this maps the type_id to the custom field parent table based on script field.
			$script_to_parent_table_map = [
					'TimesheetDetailReport'           => [
							[ 'users' => false, 'branch' => 'branch', 'department' => 'department', 'user_title' => 'user_title' ],
					],
					'TimesheetSummaryReport'          => [
							[ 'users' => false, 'branch' => 'branch', 'department' => 'department' ],
					],
					'AccrualBalanceSummaryReport'     => [
							[ 'users' => false ],
					],
					'ActiveShiftReport'               => [
							[ 'users' => false ],
					],
					'AffordableCareReport'            => [
							[ 'users' => false ],
					],
					'ExceptionReport'                 => [
							[ 'users' => false ],
					],
					'InvoiceTransactionSummaryReport' => [
							[ 'client' => 'client', 'client_contact' => 'client_contact', 'product' => 'product', 'invoice' => 'invoice' ],
					],
					'JobDetailReport'                 => [
							[ 'users' => false, 'branch' => 'branch', 'department' => 'department', 'user_title' => 'user_title', 'job' => 'job', 'job_item' => 'job_item' ],
					],
					'JobInformationReport'            => [
							[ 'job' => false ],
					],
					'JobItemInformationReport'        => [
							[ 'job' => false ],
					],
					'JobSummaryReport'                => [
							[ 'job' => false ],
					],
					'PayrollExportReport'             => [
							[ 'users' => false, 'branch' => 'branch', 'department' => 'department', 'user_title' => 'user_title', 'job' => 'job', 'job_item' => 'job_item' ],
					],
					'PayStubSummaryReport'            => [
							[ 'users' => false ],
					],
					'PayStubTransactionSummaryReport' => [
							[ 'users' => false ],
					],
					'PunchSummaryReport'              => [
							[ 'punch' => false, 'users' => 'user', 'job' => 'job', 'job_item' => 'job_item' ],
					],
					'ScheduleSummaryReport'           => [
							[ 'users' => false ],
					],
					'TaxSummaryReport'                => [
							[ 'users' => false, 'user_title' => 'user_title' ],
					],
					'UserSummaryReport'               => [
							[ 'users' => false, 'user_title' => 'user_title', 'branch' => 'branch', 'department' => 'department', 'job' => 'job', 'job_item' => 'job_item' ],
					],
			];

			//Required as for example user actual table name is 'users'
			$prefix_to_table_map = [
					'branch'         => 'branch',
					'department'     => 'department',
					'user'           => 'users',
					'users'          => 'users',
					'user_title'     => 'user_title',
					'client'         => 'client',
					'client_contact' => 'client_contact',
					'invoice'        => 'invoice',
					'product'        => 'product',
					'document'       => 'document',
					'job'            => 'job',
					'job_item'       => 'job_item',
					'punch'          => 'punch_control',
					'punch_control'  => 'punch_control',
					'schedule'       => 'schedule',
			];


			if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				//Now select all report custom columns to map them to the new custom field table.
				$custom_report_columns = $database_connection->Execute( 'SELECT id, company_id, script, formula FROM report_custom_column ORDER BY company_id, script, id' );
				while ( $result = $custom_report_columns->fetchRow() ) {
					Debug::Text( ' Converting custom fields in ReportCustomColumn for Company: ' . $result['company_id'] . ' ID: ' . $result['id'], __FILE__, __LINE__, __METHOD__, 10 );
					self::replaceOtherFieldStringReferences( $database_connection, 'formula', $result, 'report_custom_column', '#', $script_to_parent_table_map, $prefix_to_table_map, $other_to_custom_field_map );
				}
			}

			$user_report_data = $database_connection->Execute( 'SELECT id, company_id, script, data FROM user_report_data ORDER BY company_id, script, id' );
			while ( $result = $user_report_data->fetchRow() ) {
				Debug::Text( ' Converting custom fields in UserReportData for Company: ' . $result['company_id'] . ' ID: ' . $result['id'], __FILE__, __LINE__, __METHOD__, 10 );
				self::replaceOtherFieldStringReferences( $database_connection, 'data', $result, 'user_report_data', '', $script_to_parent_table_map, $prefix_to_table_map, $other_to_custom_field_map );
			}

			$user_import_script = $database_connection->Execute( 'SELECT id, company_id, script, data FROM user_generic_data WHERE script LIKE \'%import%\' ORDER BY company_id, script, id' );
			while ( $result = $user_import_script->fetchRow() ) {
				Debug::Text( ' Converting custom fields in UserGenericData for Company: ' . $result['company_id'] . ' ID: ' . $result['id'], __FILE__, __LINE__, __METHOD__, 10 );
				self::replaceOtherFieldStringReferencesInUserGenericImport( $database_connection, $result, $other_to_custom_field_map );
			}

			$paystub_entry_account_data = $database_connection->Execute( 'SELECT id, company_id, debit_account, credit_account FROM pay_stub_entry_account ORDER BY company_id, id' );
			while ( $result = $paystub_entry_account_data->fetchRow() ) {
				Debug::Text( ' Converting debit_account and credit_account in pay_stub_entry_account for Company: ' . $result['company_id'] . ' ID: ' . $result['id'], __FILE__, __LINE__, __METHOD__, 10 );
				self::replaceOtherFieldStringReferencesInPayStubEntryAccount( $database_connection, $result, 'debit_account', $other_to_custom_field_map );
				self::replaceOtherFieldStringReferencesInPayStubEntryAccount( $database_connection, $result, 'credit_account', $other_to_custom_field_map );
			}
		}

		$clf->CommitTransaction();
	}

	static function replaceOtherFieldStringReferences( $database_connection, $field_name, $data, $table_name, $wrap_character, $script_to_parent_table_map, $prefix_to_table_map, $other_to_custom_field_map ) {
		if ( strpos( $data[$field_name], 'other_id' ) !== false ) {
			if ( isset( $script_to_parent_table_map[$data['script']] ) ) {
				$search_array = [];
				$replace_array = [];
				foreach ( $script_to_parent_table_map[$data['script']] as $table_to_convert ) {
					foreach ( $table_to_convert as $field_to_convert => $prefix ) {
						$field_to_convert = $prefix_to_table_map[$field_to_convert];
						if ( $prefix === false ) {
							$legacy_prefix = '';
							$custom_field_prefix = '';
						} else {
							//Legacy prefix is the prefix used in the other_id field, whereas custom field prefix is the same as the table name.
							$legacy_prefix = $prefix . '_';
							$custom_field_prefix = $prefix_to_table_map[$prefix] . '_';
						}

						if ( isset( $other_to_custom_field_map[$data['company_id']][$field_to_convert] ) == false ) {
							continue;
						}

						$end_wrap_character = $wrap_character;
						$start_wrap_character = $wrap_character;
						if ( $field_name === 'data' ) {
							$start_wrap_character = '"';
							$end_wrap_character = '"';
						}

						array_push( $search_array,
									$start_wrap_character . $legacy_prefix . 'other_id1' . $end_wrap_character,
									$start_wrap_character . $legacy_prefix . 'other_id2' . $end_wrap_character,
									$start_wrap_character . $legacy_prefix . 'other_id3' . $end_wrap_character,
									$start_wrap_character . $legacy_prefix . 'other_id4' . $end_wrap_character,
									$start_wrap_character . $legacy_prefix . 'other_id5' . $end_wrap_character,

									//To handle default_branch_other_id we are adding "default_" to the beginning of the field name.
									$start_wrap_character . 'default_' . $legacy_prefix . 'other_id1' . $end_wrap_character,
									$start_wrap_character . 'default_' . $legacy_prefix . 'other_id2' . $end_wrap_character,
									$start_wrap_character . 'default_' . $legacy_prefix . 'other_id3' . $end_wrap_character,
									$start_wrap_character . 'default_' . $legacy_prefix . 'other_id4' . $end_wrap_character,
									$start_wrap_character . 'default_' . $legacy_prefix . 'other_id5' . $end_wrap_character );

						array_push( $replace_array,
									$start_wrap_character . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id1'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id2'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id3'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id4'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id5'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,

									//To handle default_branch_other_id we are adding "default_" to the beginning of the field name.
									$start_wrap_character . 'default_' . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id1'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . 'default_' . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id2'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . 'default_' . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id3'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . 'default_' . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id4'] ?? TTUUID::getNotExistID() ) . $end_wrap_character,
									$start_wrap_character . 'default_' . $custom_field_prefix . 'custom_field-' . ( $other_to_custom_field_map[$data['company_id']][$field_to_convert]['other_id5'] ?? TTUUID::getNotExistID() ) . $end_wrap_character );
					}
				}

				if ( empty( $search_array ) === false ) {
					Debug::Arr( [ $search_array, $replace_array ], 'Formula Search/Replace arrays: ', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Text( '  Pre Replace Formula: ' . $data[$field_name], __FILE__, __LINE__, __METHOD__, 10 );
					$new_string = str_replace( $search_array, $replace_array, $data[$field_name] );
					Debug::Text( '  Post Replace Formula: ' . $new_string, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $data[$field_name] != $new_string ) {
						$database_connection->Execute( 'UPDATE ' . $table_name . ' SET ' . $field_name . ' = ? WHERE id = ?', [ $new_string, $data['id'] ] );
					} else {
						Debug::Text( '    NOTICE: Formula was not changed!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '    NOTICE: Search array is empty!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( '  Script to parent table map not found, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( '  Formula does not contain any reference to legacy custom fields, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param $database_connection
	 * @param $data
	 * @param $other_to_custom_field_map
	 * @return void
	 */
	static function replaceOtherFieldStringReferencesInUserGenericImport( $database_connection, $data, $other_to_custom_field_map ) {
		$data['script'] = strtolower( $data['script'] ); //Make the script case insensitive.

		$prefix_to_import_wizard_script_map = [
				'import_wizardbranch'     => 'branch',
				'import_wizarddepartment' => 'department',
				'import_wizardclient'     => 'client',
				'import_wizardjob'        => 'job',
				'import_wizardjobitem'    => 'job_item',
				'import_wizarduser'       => 'users',
		];

		if ( isset( $prefix_to_import_wizard_script_map[$data['script']] ) ) {
			if ( strpos( $data['data'], 'other_id' ) !== false ) {
				if ( isset( $other_to_custom_field_map[$data['company_id']][$prefix_to_import_wizard_script_map[$data['script']]] ) ) {
					$convert_data = $other_to_custom_field_map[$data['company_id']][$prefix_to_import_wizard_script_map[$data['script']]];
					$search_array = [];
					$replace_array = [];

					array_push( $search_array,
								'"other_id1"',
								'"other_id2"',
								'"other_id3"',
								'"other_id4"',
								'"other_id5"' );

					array_push( $replace_array,
								'"custom_field-' . ( $convert_data['other_id1'] ?? TTUUID::getNotExistID() ) . '"',
								'"custom_field-' . ( $convert_data['other_id2'] ?? TTUUID::getNotExistID() ) . '"',
								'"custom_field-' . ( $convert_data['other_id3'] ?? TTUUID::getNotExistID() ) . '"',
								'"custom_field-' . ( $convert_data['other_id4'] ?? TTUUID::getNotExistID() ) . '"',
								'"custom_field-' . ( $convert_data['other_id5'] ?? TTUUID::getNotExistID() ) . '"' );

					if ( empty( $search_array ) === false ) {
						Debug::Arr( [ $search_array, $replace_array ], 'Formula Search/Replace arrays: ', __FILE__, __LINE__, __METHOD__, 10 );
						Debug::Text( '  Pre Replace Formula: ' . $data['data'], __FILE__, __LINE__, __METHOD__, 10 );
						$new_string = str_replace( $search_array, $replace_array, $data['data'] );
						Debug::Text( '  Post Replace Formula: ' . $new_string, __FILE__, __LINE__, __METHOD__, 10 );

						if ( $data['data'] != $new_string ) {
							$database_connection->Execute( 'UPDATE user_generic_data SET data = ? WHERE id = ?', [ $new_string, $data['id'] ] );
						} else {
							Debug::Text( '    NOTICE: Data was not changed!', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '    NOTICE: Search array is empty!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  Other to custom field map not found, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( '  Data does not contain any reference to legacy custom fields, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( '  Script to parent table map not found, skipping... Script: '. $data['script'], __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	/**
	 * @param $database_connection
	 * @param $data
	 * @param $field_name
	 * @param $other_to_custom_field_map
	 * @return void
	 */
	static function replaceOtherFieldStringReferencesInPayStubEntryAccount( $database_connection, $data, $field_name, $other_to_custom_field_map ) {
		$prefix_to_other_field_map = [
				'default_branch_'     => 'branch',
				'punch_branch_'       => 'branch',
				'default_department_' => 'department',
				'punch_department_'   => 'department',
				'default_job_'        => 'job',
				'punch_job_'          => 'job',
				'default_job_item_'   => 'job_item',
				'punch_job_item_'     => 'job_item',
				'title_'              => 'user_title',
				'employee_'           => 'users',
		];

		//For above check a prefix + other_id exist
		if ( isset( $data[$field_name] ) && strpos( $data[$field_name], 'other_id' ) !== false ) {
			$search_array = [];
			$replace_array = [];

			foreach ( $prefix_to_other_field_map as $prefix => $custom_field_key ) {
				if ( isset( $other_to_custom_field_map[$data['company_id']][$custom_field_key] ) ) {
					$convert_data = $other_to_custom_field_map[$data['company_id']][$custom_field_key];

					$legacy_prefix = $prefix;
					if ( $prefix == 'employee_' ) {
						$custom_field_prefix = 'users_';
					} else {
						$custom_field_prefix = $prefix;
					}

					array_push( $search_array,
								'#' . $legacy_prefix . 'other_id1#',
								'#' . $legacy_prefix . 'other_id2#',
								'#' . $legacy_prefix . 'other_id3#',
								'#' . $legacy_prefix . 'other_id4#',
								'#' . $legacy_prefix . 'other_id5#' );

					array_push( $replace_array,
								'#' . $custom_field_prefix . 'custom_field-' . ( $convert_data['other_id1'] ?? TTUUID::getNotExistID() ) . '#',
								'#' . $custom_field_prefix . 'custom_field-' . ( $convert_data['other_id2'] ?? TTUUID::getNotExistID() ) . '#',
								'#' . $custom_field_prefix . 'custom_field-' . ( $convert_data['other_id3'] ?? TTUUID::getNotExistID() ) . '#',
								'#' . $custom_field_prefix . 'custom_field-' . ( $convert_data['other_id4'] ?? TTUUID::getNotExistID() ) . '#',
								'#' . $custom_field_prefix . 'custom_field-' . ( $convert_data['other_id5'] ?? TTUUID::getNotExistID() ) . '#' );
				} else {
					Debug::Text( '  Other to custom field map not found, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			if ( empty( $search_array ) === false ) {
				Debug::Arr( [ $search_array, $replace_array ], 'Formula Search/Replace arrays: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Text( '  Pre Replace Formula: ' . $data[$field_name], __FILE__, __LINE__, __METHOD__, 10 );
				$new_string = str_replace( $search_array, $replace_array, $data[$field_name] );
				Debug::Text( '  Post Replace Formula: ' . $new_string, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $data[$field_name] != $new_string ) {
					$database_connection->Execute( 'UPDATE pay_stub_entry_account SET ' . $field_name . ' = ? WHERE id = ?', [ $new_string, $data['id'] ] );
				} else {
					Debug::Text( '    NOTICE: ' . $field_name . ' was not changed!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( '    NOTICE: Search array is empty!', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( '  ' . $field_name . ' does not contain any reference to legacy custom fields, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}
}

?>
