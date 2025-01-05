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
class LogDetailFactory extends Factory {
	protected $table = 'system_log_detail';
	protected $pk_sequence_name = 'system_log_detail_id_seq'; //PK Sequence name

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'system_log_id' )->setFunctionMap( 'SystemLog' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'field' )->setFunctionMap( 'Field' )->setType( 'varchar' ),
							TTSCol::new( 'new_value' )->setFunctionMap( 'NewValue' )->setType( 'text' ),
							TTSCol::new( 'old_value' )->setFunctionMap( 'OldValue' )->setType( 'text' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			//No API Methods.
		}

		return $schema_data;
	}

	/**
	 * @return mixed
	 */
	function getSystemLog() {
		return $this->getGenericDataValue( 'system_log_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setSystemLog( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'system_log_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getField() {
		return $this->getGenericDataValue( 'field' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setField( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'field', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOldValue() {
		return $this->getGenericDataValue( 'old_value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOldValue( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'old_value', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNewValue() {
		return $this->getGenericDataValue( 'new_value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNewValue( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'new_value', $value );
	}

	/**
	 * @param int $action_id
	 * @param string $system_log_id UUID
	 * @param $object
	 * @return bool
	 */
	function addLogDetail( $action_id, $system_log_id, $object ) {
		$start_time = microtime( true );

		//Only log detail records on add, edit, delete, undelete
		//Logging data on Add/Delete/UnDelete, or anything but Edit will greatly bloat the database, on the order of tens of thousands of entries
		//per day. The issue though is its nice to know exactly what data was originally added, then what was edited, and what was finally deleted.
		//We may need to remove logging for added data, but leave it for edit/delete, so we know exactly what data was deleted.
		if ( !in_array( $action_id, [ 10, 20, 30, 31, 40 ] ) ) {
			Debug::text( 'Not logging detail audit records for Action ID: ' . $action_id, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( TTUUID::isUUID( $system_log_id ) && $system_log_id != TTUUID::getZeroID() && $system_log_id != TTUUID::getNotExistID() && is_object( $object ) ) {
			//Remove "Plugin" from the end of the class name incase plugins are enabled.
			$class = str_replace( 'Plugin', '', get_class( $object ) );
			Debug::text( 'System Log ID: ' . $system_log_id . ' Class: ' . $class, __FILE__, __LINE__, __METHOD__, 10 );
			//Debug::Arr($object->data, 'Object Data: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($object->old_data, 'Object Old Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//Only store raw data changes, don't convert *_ID fields to full text names, it bloats the storage and slows down the logging process too much.
			//We can do the conversion when someone actually looks at the audit logs, which will obviously be quite rare in comparison. Even though this will
			//require quite a bit more code to handle.
			//There are also translation issues if we convert IDs to text at this point. However there could be continuity problems if ID values change in the future.
			$new_data = $object->data;
			//Debug::Arr($new_data, 'New Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $action_id == 20 ) { //Edit
				$custom_field_log_data = $this->getCustomFieldLogData( $object, $new_data, $object->old_data );
				$other_json_field_log_data = $this->getOtherJsonFieldLogData( $object, $new_data, $object->old_data );

				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					if ( isset( $object->old_data ) && isset( $object->old_data['password'] ) ) { //Password from old_data is encrypted, and if put back into the class always causes validation error.
						$object->old_data['password'] = null;
					}

					$tmp_class = new $class;

					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					//  However this can cause problems, specifically with PP Schedule TimeSheet Verification settings, as they are calculated going into the DB and coming out.
					//  Shouldn't the diff just be strictly on the data changed in the DB itself, and not passed through setObjectFromArray()?
					//  See the Delete case below as well.
					//  setObjectFromArray() is needed for parsing date/time values back to epoch, otherwise these fields will always show as changed.
					//$old_data = $object->old_data;
					$tmp_class->setObjectFromArray( $object->old_data );
					$old_data = $tmp_class->data;
					unset( $tmp_class );
				} else {
					$old_data = $object->old_data;
				}

				//Strip any data that does not have getter/setter functions, since some data can be coming in from SQL joins that we don't want to include in the audit trailer as it belongs to other objects.
				$old_data = $object->clearNonMappedData( $old_data );
				$new_data = $object->clearNonMappedData( $new_data );

				if ( empty( $custom_field_log_data['old'] ) == false ) {
					$old_data = array_merge( $old_data, $custom_field_log_data['old'] );
				}
				if ( empty( $custom_field_log_data['new'] ) == false ) {
					$new_data = array_merge( $new_data, $custom_field_log_data['new'] );
				}

				if ( empty( $other_json_field_log_data['old'] ) == false ) {
					$old_data = array_merge( $old_data, $other_json_field_log_data['old'] );
				}

				if ( empty( $other_json_field_log_data['new'] ) == false ) {
					$new_data = array_merge( $new_data, $other_json_field_log_data['new'] );
				}

				//We don't want to include any sub-arrays, as those classes should take care of their own logging, even though it may be slower in some cases.
				$diff_arr = array_diff_assoc( (array)$new_data, (array)$old_data );
			} else if ( $action_id == 30 ) { //Delete
				$old_data = [];
				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					$tmp_class = new $class;
					$tmp_class->setObjectFromArray( $object->data );
					$diff_arr = $tmp_class->data;
					unset( $tmp_class );
				} else {
					$diff_arr = $object->data;
				}
			} else { //Add
				//Debug::text('Not editing, skipping the diff process...', __FILE__, __LINE__, __METHOD__, 10);
				//No need to store data that is added, as its already in the database, and if it gets changed or deleted we store it then.
				$custom_field_log_data = $this->getCustomFieldLogData( $object, $new_data, null );
				$other_json_field_log_data = $this->getOtherJsonFieldLogData( $object, $new_data, null );

				$old_data = [];
				$diff_arr = $object->data;

				if ( empty( $custom_field_log_data['new'] ) == false ) {
					//Add custom fields to the diff array so they get logged.
					$diff_arr = array_merge( $diff_arr, $custom_field_log_data['new'] );
					//Only log specific custom fields and not entire json string.
					if ( isset( $diff_arr['custom_field'] ) ) {
						unset( $diff_arr['custom_field'] );
					}
					//Need to make sure new data gets the custom fields added to it, otherwise they won't be logged.
					$new_data = $diff_arr;
				}

				if ( empty( $other_json_field_log_data['new'] ) == false ) {
					//Add other json fields to the diff array so they get logged.
					$diff_arr = array_merge( $diff_arr, $other_json_field_log_data['new'] );
					//Only log specific other json fields and not entire json string.
					if ( isset( $diff_arr['other_json'] ) ) {
						unset( $diff_arr['other_json'] );
					}
					//Need to make sure new data gets the other json fields added to it, otherwise they won't be logged.
					$new_data = $diff_arr;
				}
			}
			//Debug::Arr($old_data, 'Old Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);

			//Handle class specific fields.
			switch ( $class ) {
				case 'CompanyFactory':
				case 'CompanyListFactory':
					unset(
							$diff_arr['ldap_bind_password'],
							$diff_arr['saml_sp_json'],
					);
					break;
				case 'UserFactory':
				case 'UserListFactory':
					unset(
							$diff_arr['labor_standard_industry'],
							$diff_arr['password'],
							$diff_arr['phone_password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_reset_date'],
							$diff_arr['password_updated_date'],
							$diff_arr['mfa_json'],
							$diff_arr['last_login_date'],
							$diff_arr['full_name'],
							$diff_arr['first_name_metaphone'],
							$diff_arr['last_name_metaphone'],
							$diff_arr['ibutton_id'],
							$diff_arr['finger_print_1'],
							$diff_arr['finger_print_2'],
							$diff_arr['finger_print_3'],
							$diff_arr['finger_print_4'],
							$diff_arr['finger_print_1_updated_date'],
							$diff_arr['finger_print_2_updated_date'],
							$diff_arr['finger_print_3_updated_date'],
							$diff_arr['finger_print_4_updated_date'],
							$diff_arr['work_email_is_valid'],
							$diff_arr['work_email_is_valid_key'],
							$diff_arr['work_email_is_valid_date'],
							$diff_arr['home_email_is_valid'],
							$diff_arr['home_email_is_valid_key'],
							$diff_arr['home_email_is_valid_date'],
					);
					break;
				case 'UserPreferenceFactory':
				case 'UserPreferenceListFactory':
					unset(
							$diff_arr['browser_permission_ask_date'],
							$diff_arr['schedule_icalendar_event_name'],
							$diff_arr['user_full_name_format']
					);
					break;
				case 'PayPeriodScheduleFactory':
				case 'PayPeriodScheduleListFactory':
					unset(
							$diff_arr['primary_date_ldom'],
							$diff_arr['primary_transaction_date_ldom'],
							$diff_arr['primary_transaction_date_bd'],
							$diff_arr['secondary_date_ldom'],
							$diff_arr['secondary_transaction_date_ldom'],
							$diff_arr['secondary_transaction_date_bd']
					);
					break;
				case 'PayPeriodFactory':
				case 'PayPeriodListFactory':
					unset(
							$diff_arr['is_primary']
					);
					break;
				case 'PayStubEntryFactory':
				case 'PayStubEntryListFactory':
				case 'PayStubTransactionFactory':
				case 'PayStubTransactionListFactory':
					unset(
							$diff_arr['pay_stub_id']
					);
					break;
				case 'StationFactory':
				case 'StationListFactory':
					unset(
							$diff_arr['last_poll_date'],
							$diff_arr['last_push_date'],
							$diff_arr['last_punch_time_stamp'],
							$diff_arr['last_partial_push_date'],
							$diff_arr['mode_flag'], //This is changed often for some reason, would be nice to audit it though.
							$diff_arr['work_code_definition'],
							$diff_arr['allowed_date']
					);
					break;
				case 'ScheduleFactory':
				case 'ScheduleListFactory':
					unset(
							$diff_arr['recurring_schedule_template_control_id'],
							$diff_arr['replaced_id']
					);
					break;
				case 'PunchFactory':
				case 'PunchListFactory':
					unset(
							$diff_arr['user_id'], //Set by PunchControlFactory instead.
							$diff_arr['actual_time_stamp'],
							$diff_arr['original_time_stamp'],
							$diff_arr['punch_control_id'],
							$diff_arr['station_id']
					);
					break;
				case 'PunchControlFactory':
				case 'PunchControlListFactory':
					unset(
							$diff_arr['date_stamp'], //Logged in Punch Factory instead.
							$diff_arr['overlap'],
							$diff_arr['actual_total_time']
					);
					break;
				case 'ExceptionPolicyFactory':
				case 'ExceptionPolicyListFactory':
					unset(
							$diff_arr['enable_authorization']
					);
					break;
				case 'GEOFenceFactory':
				case 'GEOFenceListFactory':
					break;
				case 'AccrualFactory':
				case 'AccrualListFactory':
					unset(
							$diff_arr['user_date_total_id']
					);
					break;
				case 'JobItemFactory':
				case 'JobItemListFactory':
					unset(
							$diff_arr['type_id'],
							$diff_arr['department_id']
					);
					break;
				case 'ClientFactory':
				case 'ClientListFactory':
					unset(
							$diff_arr['company_name_metaphone'],
							$diff_arr['company_dba_name_metaphone']
					);
					break;
				case 'ClientContactFactory':
				case 'ClientContactListFactory':
					unset(
							$diff_arr['password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_reset_date']
					);
					break;
				case 'UserReviewFactory':
				case 'UserReviewListFactory':
					unset(
							$diff_arr['user_review_control_id']
					);
					break;
				case 'ClientPaymentFactory':
				case 'ClientPaymentListFactory':
					if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
						//Only log secure values.
						if ( isset( $diff_arr['cc_number'] ) && isset( $new_data['client_id'] ) ) {
							$clf = TTnew('ClientListFactory');
							$clf->getById( $new_data['client_id'] );
							if ( $clf->getRecordCount() > 0 ) {
								$company_id = $clf->getCurrent()->getCompany();

								$old_data['cc_number'] = ( isset( $old_data['cc_number'] ) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $old_data['cc_number'], null, TTPassword::getPasswordSalt( $company_id ) ) ) : '';
								$new_data['cc_number'] = ( isset( $new_data['cc_number'] ) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $new_data['cc_number'], null, TTPassword::getPasswordSalt( $company_id ) ) ) : '';
							}
							unset( $clf, $company_id );
						}

						if ( isset( $diff_arr['bank_account'] ) ) {
							$old_data['bank_account'] = ( isset( $old_data['bank_account'] ) ) ? $object->getSecureAccount( $old_data['bank_account'] ) : '';
							$new_data['bank_account'] = ( isset( $new_data['bank_account'] ) ) ? $object->getSecureAccount( $new_data['bank_account'] ) : '';
						}

						if ( isset( $diff_arr['cc_check'] ) ) {
							$old_data['cc_check'] = ( isset( $old_data['cc_check'] ) ) ? $object->getSecureCreditCardCheck( $old_data['cc_check'] ) : '';
							$new_data['cc_check'] = ( isset( $new_data['cc_check'] ) ) ? $object->getSecureCreditCardCheck( $new_data['cc_check'] ) : '';
						}
					}
					break;
				case 'CompanyDeductionFactory':
				case 'CompanyDeductionListFactory':
				case 'UserDeductionFactory':
				case 'UserDeductionListFactory':
					unset(
							$diff_arr['minimum_length_of_service_days'], //User doesn't need to see this.
							$diff_arr['maximum_length_of_service_days'], //User doesn't need to see this.
					);
					break;
				case 'RemittanceSourceAccountFactory':
				case 'RemittanceSourceAccountListFactory':
				case 'RemittanceDestinationAccountFactory':
				case 'RemittanceDestinationAccountListFactory':
					//Only log secure values.
					if ( isset( $diff_arr['value3'] ) ) {
						$old_data['value3'] = ( isset( $old_data['value3'] ) ) ? $object->getSecureValue3( $object->getValue3( $old_data['value3'] ) ) : '';
						$new_data['value3'] = ( isset( $new_data['value3'] ) ) ? $object->getSecureValue3( $object->getValue3( $new_data['value3'] ) ) : '';
					}
					break;
				case 'JobApplicantFactory':
				case 'JobApplicantListFactory':
					unset(
							$diff_arr['password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_reset_date'],
							$diff_arr['first_name_metaphone'],
							$diff_arr['last_name_metaphone']
							//$diff_arr['longitude'],
							//$diff_arr['latitude']
					);
					break;
				case 'ReportScheduleFactory':
				case 'ReportScheduleListFactory':
					unset(
							$diff_arr['user_report_data_id'],
							$diff_arr['state_id']
					);
					break;
				case 'LegalEntityFactory':
				case 'LegalEntityListFactory':
					//Only log secure values.
					if ( isset( $diff_arr['payment_services_api_key'] ) ) {
						$old_data['payment_services_api_key'] = ( isset( $old_data['payment_services_api_key'] ) ) ? $object->getSecurePaymentServicesAPIKey( $object->getPaymentServicesAPIKey( $old_data['payment_services_api_key'] ) ) : '';
						$new_data['payment_services_api_key'] = ( isset( $new_data['payment_services_api_key'] ) ) ? $object->getSecurePaymentServicesAPIKey( $object->getPaymentServicesAPIKey( $new_data['payment_services_api_key'] ) ) : '';
					}
					break;
			}

			//Ignore specific columns here, like updated_date, updated_by, etc...
			unset(
					//These fields should never change, and therefore don't need to be recorded.
					$diff_arr['id'],
					$diff_arr['company_id'],

					//UserDateID controls which user things like schedules are assigned too, which is critical in the audit log.
					$diff_arr['user_date_id'], //UserDateTotal, Schedule, PunchControl, etc...

					$diff_arr['name_metaphone'],
					$diff_arr['first_name_metaphone'],
					$diff_arr['last_name_metaphone'],

					//General fields to skip
					$diff_arr['created_date'],
					//$diff_arr['created_by'], //Need to audit created_by, because it can change on some records like RecurringScheduleTemplateControl
					//$diff_arr['created_by_id'],
					$diff_arr['updated_date'],
					$diff_arr['updated_by'],
					$diff_arr['updated_by_id'],
					$diff_arr['deleted_date'],
					$diff_arr['deleted_by'],
					$diff_arr['deleted_by_id'],
					$diff_arr['deleted']
			);

			//Debug::Arr($diff_arr, 'Array Diff: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array( $diff_arr ) && count( $diff_arr ) > 0 ) {
				$ph = [];
				$data = [];
				foreach ( $diff_arr as $field => $value ) {

					$old_value = null;
					if ( isset( $old_data[$field] ) ) {
						$old_value = $old_data[$field];
						if ( is_bool( $old_value ) && $old_value === false ) {
							$old_value = null;
						} else if ( is_float( $old_value ) ) {
							$old_value = TTMath::removeTrailingZeros( $old_value, 0 ); //Normalize without trailing zeros, so 73.000 and 73.00 and 73.0 and 73 are all treated the same.
						} else if ( is_array( $old_value ) ) {
							//$old_value = serialize($old_value);
							//If the old value is an array, replace it with NULL because it will always match the NEW value too.
							$old_value = null;
						}
					}

					$new_value = $new_data[$field];
					if ( is_bool( $new_value ) && $new_value === false ) {
						$new_value = null;
					} else if ( is_float( $new_value ) ) {
						$new_value = TTMath::removeTrailingZeros( $new_value, 0 ); //Normalize without trailing zeros, so 73.000 and 73.00 and 73.0 and 73 are all treated the same.
					} else if ( is_array( $new_value ) ) {
						$new_value = serialize( $new_value );
					} else if ( isset( $old_data[$field] ) == false && $new_value == TTUUID::getZeroID() ) { //Don't log cases where old value doesn't exist but new value is a zero UUID.
						$new_value = null;
					}

					//Debug::Text('Old Value: '. $old_value .' New Value: '. $new_value, __FILE__, __LINE__, __METHOD__, 10);
					if ( !( $old_value == '' && $new_value == '' ) && ( $old_value != $new_value ) ) {
						$ph[] = $this->getNextInsertId(); //This needs work before UUID and after.
						$ph[] = TTUUID::castUUID( $system_log_id );
						$ph[] = $field;
						$ph[] = $new_value;
						$ph[] = $old_value;
						$data[] = '(?, ?, ?, ?, ?)';
					}
				}
				unset( $value ); //code standards

				if ( empty( $data ) == false ) {
					//Save data in a single SQL query.
					$query = 'INSERT INTO ' . $this->getTable() . '(ID, SYSTEM_LOG_ID, FIELD, NEW_VALUE, OLD_VALUE) VALUES' . implode( ',', $data );
					//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
					$this->ExecuteSQL( $query, $ph );

					Debug::Text( 'Logged detail records in: ' . ( microtime( true ) - $start_time ), __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		Debug::Text( 'Not logging detail records, likely no data changed in: ' . ( microtime( true ) - $start_time ) . 's', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		// System log
		if ( $this->getSystemLog() !== false && $this->getSystemLog() != TTUUID::getZeroID() ) {
			$llf = TTnew( 'LogListFactory' ); /** @var LogListFactory $llf */
			$this->Validator->isResultSetWithRows( 'user',
												   $llf->getByID( $this->getSystemLog() ),
												   TTi18n::gettext( 'System log is invalid' )
			);
		}
		// Field
		$this->Validator->isString( 'field',
									$this->getField(),
									TTi18n::gettext( 'Field is invalid' )
		);
		// Old value
		$this->Validator->isLength( 'old_value',
									$this->getOldValue(),
									TTi18n::gettext( 'Old value is invalid' ),
									0,
									1024
		);
		// New value
		$this->Validator->isLength( 'new_value',
									$this->getNewValue(),
									TTi18n::gettext( 'New value is invalid' ),
									0,
									1024
		);
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		return true;
	}

	/**
	 * @param $object
	 * @param $new_data
	 * @param $old_data
	 * @return array
	 */
	function getCustomFieldLogData( $object, $new_data, $old_data ) {
		$old_custom_field_data = [];
		$new_custom_field_data = [];

		//Check if $object is a factory utilizing custom fields.
		$cf_obj = TTnew( 'CustomFieldFactory' ); /** @var CustomFieldFactory $cf_obj */
		if ( isset( $cf_obj->getOptions( 'parent_table' )[$object->getCustomFieldTableName()] ) ) {

			if ( $old_data !== null ) {
				$old_custom_field_data = Misc::addKeyPrefix( 'custom_field-', isset( $old_data['custom_field'] ) ? json_decode( $old_data['custom_field'], true ) : [] );
			}
			if ( $new_data !== null ) {
				$new_custom_field_data = Misc::addKeyPrefix( 'custom_field-', isset( $new_data['custom_field'] ) ? json_decode( $new_data['custom_field'], true ) : [] );
			}

			//Sub-array differences will not be found and no changes will be logged for multi-select custom fields,
			//so we need to check for those and implode them into a single string. This also makes getting display values easier.
			foreach ( $old_custom_field_data as $custom_key => $old_custom ) {
				if ( is_array( $old_custom ) ) {
					$old_custom_field_data[$custom_key] = implode( ',', $old_custom );
				}
			}

			foreach ( $new_custom_field_data as $custom_key => $new_custom ) {
				if ( is_array( $new_custom ) ) {
					$new_custom_field_data[$custom_key] = implode( ',', $new_custom );
				}
			}
		}

		return [ 'old' => $old_custom_field_data, 'new' => $new_custom_field_data ];
	}

	/**
	 * @param $object
	 * @param $new_data
	 * @param $old_data
	 * @return array[]
	 */
	function getOtherJsonFieldLogData( $object, $new_data, $old_data ) {
		$old_other_json_field_data = [];
		$new_other_json_field_data = [];

		//Check if $object is a factory utilizing other json fields.
		if ( isset( $object->data['other_json'] ) ) {
			$old_other_json_field_data = isset( $old_data['other_json'] ) ? json_decode( $old_data['other_json'], true ) : [];
			$new_other_json_field_data = isset( $new_data['other_json'] ) ? json_decode( $new_data['other_json'], true ) : [];


			//When we get the diff_array there is a comment that says: We don't want to include any sub-arrays, as those classes should take care of their own logging, even though it may be slower in some cases.
			//Therefore, if a other_json field is an array we need to convert it to a string so that it gets logged. And then the LogDetailDisplay would need to convert it for display purposes, specific to that data and class.

			foreach ( $old_other_json_field_data as $key => $old_data ) {
				if ( is_array( $old_data ) ) {
					$old_other_json_field_data[$key] = json_encode( $old_data );
				} else {
					$old_other_json_field_data[$key] = $old_data;
				}
			}

			foreach ( $new_other_json_field_data as $key => $new_data ) {
				if ( is_array( $new_data ) ) {
					$new_other_json_field_data[$key] = json_encode( $new_data );
				} else {
					$new_other_json_field_data[$key] = $new_data;
				}
			}
		}

		return [ 'old' => $old_other_json_field_data, 'new' => $new_other_json_field_data ];
	}

	/**
	 * This table doesn't have any of these columns, so overload the functions.
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDate() === false ) {
			$this->setDate();
		}

		return true;
	}
}

?>
