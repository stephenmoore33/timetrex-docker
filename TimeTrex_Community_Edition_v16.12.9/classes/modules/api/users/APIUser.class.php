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
 * @package API\Users
 */
class APIUser extends APIFactory {
	protected $main_class = 'UserFactory';

	/**
	 * APIUser constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default user data for creating new users.
	 * @param string $tmp_company_id UUID
	 * @return array
	 */
	function getUserDefaultData( $tmp_company_id = null, $default_id = null ) {
		//Allow getting default data from other companies, so it makes it easier to create the first employee of a company.
		if ( $tmp_company_id != '' && TTUUID::isUUID( $tmp_company_id ) && $tmp_company_id != TTUUID::getZeroID() && $tmp_company_id != TTUUID::getNotExistID() && $this->getPermissionObject()->Check( 'company', 'enabled' ) && $this->getPermissionObject()->Check( 'company', 'view' ) ) {
			$company_id = $tmp_company_id;
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}
		Debug::Text( 'Getting user default data for Company ID: ' . $company_id . ' TMP Company ID: ' . $tmp_company_id, __FILE__, __LINE__, __METHOD__, 10 );

		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */

		//Get New Hire Defaults.
		$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
		if ( $default_id == '' ) {
			$udlf->getByCompanyId( $company_id );
		} else {
			if ( TTUUID::isUUID( $default_id ) ) {
				$udlf->getByIdAndCompanyId( $default_id, $company_id );
			} else {
				$udlf->getByCompanyIdAndName( $company_id, $default_id );
			}
		}
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::Text( 'Using User Defaults, as they exist...', __FILE__, __LINE__, __METHOD__, 10 );
			$udf_obj = $udlf->getCurrent(); /** @var UserDefaultFactory $udf_obj */

			$data = [
					'company_id'                          => $company_id,
					'legal_entity_id'                     => $udf_obj->getLegalEntity(),
					'enable_login'                        => true,
					'status_id'                           => 10, //Active.
					'title_id'                            => $udf_obj->getTitle(),
					'employee_number'                     => (string)$uf->getNextAvailableEmployeeNumber( $company_id ), //Force to string as JS supports 53 bit integers with JSON, and PHP allows 64bit integers.
					'city'                                => $udf_obj->getCity(),
					'country'                             => $udf_obj->getCountry(),
					'province'                            => $udf_obj->getProvince(),
					'work_phone'                          => $udf_obj->getWorkPhone(),
					'work_phone_ext'                      => $udf_obj->getWorkPhoneExt(),
					'work_email'                          => $udf_obj->getWorkEmail(),
					'hire_date'                           => TTDate::getAPIDate( 'DATE', time() ),
					'sex_id'                              => 5, //Unspecified.
					'default_branch_id'                   => $udf_obj->getDefaultBranch(),
					'default_department_id'               => $udf_obj->getDefaultDepartment(),
					'permission_control_id'               => $udf_obj->getPermissionControl(),
					'terminated_permission_control_id'    => $udf_obj->getTerminatedPermissionControl(),
					'pay_period_schedule_id'              => $udf_obj->getPayPeriodSchedule(),
					'policy_group_id'                     => $udf_obj->getPolicyGroup(),
					'currency_id'                         => $udf_obj->getCurrency(),
					'hierarchy_control'                   => $udf_obj->getHierarchyControl(),
					'recurring_schedule_id'               => $udf_obj->getRecurringSchedule(),
					'user_default_id'                     => $udf_obj->getId(),
			];
		} else {
			Debug::Text( ' User Default data does not exists for Company ID: ' . $company_id .' Trying to determine them automatically...', __FILE__, __LINE__, __METHOD__, 10 );

			$data = [
					'company_id'                          => $company_id,
					'enable_login'                        => true,
					'status_id'                           => 10, //Active.
					'employee_number'                     => (string)$uf->getNextAvailableEmployeeNumber( $company_id ), //Force to string as JS supports 53 bit integers with JSON, and PHP allows 64bit integers.
					'hire_date'                           => TTDate::getAPIDate( 'DATE', time() ),
					'sex_id'                              => 5, //Unspecified.
			];

			$lelf = TTnew( 'LegalEntityListFactory' );
			$lelf->getByCompanyId( $company_id );
			if ( $lelf->getRecordCount() > 0 ) {
				$data['legal_entity_id'] = $lelf->getCurrent()->getId();
			}
			unset( $lelf );

			$clf = TTnew( 'CurrencyListFactory' );
			$clf->getByCompanyIdAndDefault( $company_id, true );
			if ( $clf->getRecordCount() > 0 ) {
				$data['currency_id'] = $clf->getCurrent()->getId();
			}
			unset( $clf );

			$pclf = TTnew( 'PermissionControlListFactory' );
			$pclf->getByCompanyId( $company_id );
			if ( $pclf->getRecordCount() > 0 ) {
				$data['permission_control_id'] = $pclf->getCurrent()->getId();
			}
			unset( $pclf );
		}

		if ( !isset( $data['company_id'] ) ) {
			$data['company_id'] = $company_id;
		}

		if ( !isset( $data['status_id'] ) ) {
			$data['status_id'] = 10; //Active
		}

		if ( !isset( $data['currency_id'] ) ) {
			$data['currency_id'] = TTUUID::getZeroID();
		}

		if ( !isset( $data['country'] ) ) {
			$data['country'] = 'US';
		}

		if ( !isset( $data['user_default_id'] ) ) {
			$data['user_default_id'] = TTUUID::getZeroID();
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
		if ( $ulf->getRecordCount() > 0 ) {
			Debug::Text( 'Highest Employee Number: ' . $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == true ) {
				$data['next_available_employee_number'] = ( $ulf->getCurrent()->getEmployeeNumber() + 1 );
			} else {
				Debug::Text( 'Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__, 10 );
				$data['next_available_employee_number'] = null;
			}
		} else {
			$data['next_available_employee_number'] = 1;
		}

		if ( !isset( $data['hire_date'] ) || $data['hire_date'] == '' ) {
			$data['hire_date'] = TTDate::getAPIDate( 'DATE', time() );
		}

		//Try to default the hierarchy as best we can if its a supervisor (subordinates only) creating the employee record.
		if ( $this->getPermissionObject()->Check( 'user', 'view' ) == false && $this->getPermissionObject()->Check( 'user', 'view_child' ) == true ) {
			$api_hc = new APIHierarchyControl;
			$hierarchy_control_options = $this->stripReturnHandler( $api_hc->getHierarchyControlOptions( false ) ); //Don't include blank.
			if ( is_array( $hierarchy_control_options ) ) {
				foreach ( $hierarchy_control_options as $hierarchy_object_type => $hierarchy_control_ids ) {
					if ( count( $hierarchy_control_ids ) == 1 ) {
						$data['hierarchy_control'][$hierarchy_object_type] = Misc::trimSortPrefix( key( $hierarchy_control_ids ) );
					}
				}
			}
			unset( $api_hc );
		}

		$data = $uf->getCustomFieldsDefaultData( $company_id, $data );

		return $this->returnHandler( $data );
	}

	/**
	 * @param string $format
	 * @param array $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function exportUser( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getUser( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_employee', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get user data for one or more users.
	 * @param array $data             filter data, see reference for details.
	 * @param boolean $disable_paging disables paging and returns all records.
	 * @return array|bool
	 * @see UserListFactory::getAPISearchByCompanyIdAndArrayCriteria() To see a description of the ListFactory that is used.
	 */
	function getUser( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'view' ) || $this->getPermissionObject()->Check( 'user', 'view_own' ) || $this->getPermissionObject()->Check( 'user', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//We need to take into account different permissions, ie: punch->view, view_child, view_own when displaying the dropdown
		//box in the TimeSheet view and other views as well. Allow the caller of this function to pass a "permission_section"
		//that can be used to determine this.
		$permission_section = 'user';
		$valid_permission_sections = [ 'user', 'wage', 'user_contact', 'accrual', 'roe', 'punch', 'schedule', 'recurring_schedule', 'message', 'user_expense', 'pay_stub_amendment', 'policy_group', 'user_membership', 'user_skill', 'user_education', 'user_license', 'user_language', 'user_review', 'job_application' ]; //#2242 - Make sure we limit the sections to a specific list to avoid security bypasses.
		if ( isset( $data['permission_section'] ) && $data['permission_section'] != '' ) {
			if ( in_array( trim( strtolower( $data['permission_section'] ) ), $valid_permission_sections ) ) {
				$permission_section = trim( strtolower( $data['permission_section'] ) );
			} else {
				Debug::Text( 'ERROR: NOT ALLOWED: permission_section: ' . $data['permission_section'], __FILE__, __LINE__, __METHOD__, 10 );
			}
		}
		Debug::Text( 'Permission Section: ' . $permission_section, __FILE__, __LINE__, __METHOD__, 10 );

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		//$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( $permission_section, 'view' );
		$data['filter_data'] = array_merge( (array)$data['filter_data'], $this->getPermissionObject()->getPermissionFilterData( $permission_section, 'view' ) );
		//Debug::Arr($data['filter_data']['permission_children_ids'], 'Permission Section: '. $permission_section .' Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		//Allow getting users from other companies, so we can change admin contacts when using the master company.
		//Need to allow -1 to be accepted for Edit Company view to not show any employees in Contact dropdowns when creating a new company.
		//But show the proper employees (for that company) in Contact dropdowns when editing an existing company.
		if ( isset( $data['filter_data']['company_id'] )
				&& !empty( $data['filter_data']['company_id'] )
				&& ( $this->getPermissionObject()->Check( 'company', 'enabled' ) && $this->getPermissionObject()->Check( 'company', 'view' ) ) ) {
			$company_id = $data['filter_data']['company_id'];
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		$include_last_punch_time = ( isset( $data['filter_columns']['max_punch_time_stamp'] ) ) ? true : false;

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'], $include_last_punch_time );
		Debug::Text( 'Record Count: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $ulf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount() );

			$this->setPagerObject( $ulf );

			$retarr = [];
			foreach ( $ulf as $u_obj ) {
				$retarr[] = $u_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $ulf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//Debug::Arr($retarr, 'User Data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUser( $data, true ) ) );
	}

	/**
	 * Validate user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function validateUser( $data ) {
		return $this->setUser( $data, true );
	}

	/**
	 * Set user data for one or more users.
	 * @param array $data user data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUser( $data, $validate_only = false, $ignore_warning = true ) {
		global $authentication;

		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		//If they have permissions to edit user records other than their own, make sure their status is Active.
		if ( $this->getCurrentUserObject()->getStatus() != 10 && ( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) ) ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to modify employees' ) );
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_own' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) || $this->getPermissionObject()->Check( 'user', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = false;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Users', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$transaction_function = function () use ( $row, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key, $total_records, $permission_children_ids ) {
					$primary_validator = new Validator();

					$lf = TTnew( 'UserListFactory' ); /** @var UserListFactory $lf */
					if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
						$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					}
					$lf->StartTransaction();

					//Force Company ID to current company.
					if ( !isset( $row['company_id'] ) || ( isset( $row['company_id'] ) && $row['company_id'] == '' ) || !$this->getPermissionObject()->Check( 'company', 'view' ) ) {
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					}

					if ( isset( $row['id'] ) && $row['id'] != '' ) {
						//Modifying existing object.
						//Get user object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $row['id'], $row['company_id'] );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							//Debug::Text('User ID: '. $row['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
							if (
									$validate_only == true
									||
									(
											$this->getPermissionObject()->Check( 'user', 'edit' )
											|| ( $this->getPermissionObject()->Check( 'user', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
											|| ( $this->getPermissionObject()->Check( 'user', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === true )
									) ) {

								Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
								//$row = array_merge( $lf->getCurrent()->getObjectAsArray(), $row );
								$lf = $lf->getCurrent(); //Make the current $lf variable the current object, so we can ignore some fields if needed.
								$row = array_merge( $lf->getObjectAsArray(), $row );
							} else {
								$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Edit permission denied' ) );
							}
						} else {
							//Object doesn't exist.
							$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
						}
					} else {
						//Adding new object, check ADD permissions.
						$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'user', 'add' ), TTi18n::gettext( 'Add permission denied' ) );

						//Because password encryption requires the user_id, we need to get it first when creating a new employee.
						$row['id'] = $lf->getNextInsertId();
					}

					//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
					//Generate random user name if its validate only and not otherwise specified.
					if ( $validate_only == true && ( !isset( $row['user_name'] ) || $row['user_name'] == '' ) ) {
						$row['user_name'] = 'random' . rand( 10000000, 99999999 );
					}
					//Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

					$is_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						Debug::Text( 'Attempting to save data... API Message ID: ' . $this->getAPIMessageID(), __FILE__, __LINE__, __METHOD__, 10 );

						if ( DEMO_MODE == true && $lf->isNew() == false ) { //Allow changing these if DEMO is enabled, but they are adding new records.
							Debug::Text( 'DEMO Mode ENABLED, disable modifying some data...', __FILE__, __LINE__, __METHOD__, 10 );
							unset( $row['permission_control_id'], $row['status_id'], $row['phone_id'], $row['user_name'], $row['password'] );
						}

						//This has been moved into UserFactory->Validate() instead, so proper error messages can be displayed.
						//if ( $lf->isNew() == false && $this->getPermissionObject()->Check( 'user', 'edit_advanced' ) == false ) {
						//	Debug::Text( 'NOT allowing advanced edit...', __FILE__, __LINE__, __METHOD__, 10 );
						//	//Unset all advanced fields.
						//	unset(
						//			$row['user_name'],
						//			$row['currency_id'],
						//			$row['employee_number'], //This must always be set
						//			$row['default_branch_id'],
						//			$row['default_department_id'],
						//			$row['group_id'],
						//			$row['title_id'],
						//			$row['first_name'],
						//			$row['middle_name'],
						//			$row['last_name'],
						//			$row['city'],
						//			$row['country'],
						//			$row['province'],
						//			$row['hire_date'],
						//			$row['birth_date'],
						//			$row['termination_date'],
						//			$row['sin'],
						//			$row['note'],
						//			$row['tags']
						//	);
						//}

						unset( $row['mfa_type_id'] ); //Do not allow changing multifactor settings

						//If the user doesn't have permissions to change the hierarchy_control, unset that data.
						if ( isset( $row['hierarchy_control'] ) && ( $this->getPermissionObject()->Check( 'hierarchy', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_hierarchy' ) ) ) {
							Debug::Text( 'Allowing change of hierarchy...', __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::Text( 'NOT allowing change of hierarchy...', __FILE__, __LINE__, __METHOD__, 10 );
							unset( $row['hierarchy_control'] );
						}

						//Handle additional permission checks for setPermissionControl().
						if ( isset( $row['permission_control_id'] )
								&& ( $lf->getPermissionLevel() <= $this->getPermissionObject()->getLevel() && ( $this->getPermissionObject()->Check( 'permission', 'edit' ) || $this->getPermissionObject()->Check( 'permission', 'edit_own' ) || $this->getPermissionObject()->Check( 'user', 'edit_permission_group' ) ) ) ) {
							Debug::Text( 'Allowing change of permissions...', __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::Text( 'NOT allowing change of permissions...', __FILE__, __LINE__, __METHOD__, 10 );
							unset( $row['permission_control_id'] );
						}

						if ( isset( $row['pay_period_schedule_id'] ) && ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_pay_period_schedule' ) ) ) {
							Debug::Text( 'Allowing change of pay period schedule...', __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::Text( 'NOT allowing change of pay period schedule...', __FILE__, __LINE__, __METHOD__, 10 );
							unset( $row['pay_period_schedule_id'] );
						}

						if ( isset( $row['policy_group_id'] ) && ( $this->getPermissionObject()->Check( 'policy_group', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_policy_group' ) ) ) {
							Debug::Text( 'Allowing change of policy group...', __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::Text( 'NOT allowing change of policy group...', __FILE__, __LINE__, __METHOD__, 10 );
							unset( $row['policy_group_id'] );
						}

						$lf->setObjectFromArray( $row );

						$lf->Validator->setValidateOnly( $validate_only );

						$is_valid = $lf->isValid( $ignore_warning );

						if ( $validate_only == false && $lf->getIsRequiredCurrentPassword() == true ) {
							$lf->FailTransaction();
							$lf->CommitTransaction();
							$lf->setTransactionMode(); //Back to default isolation level.

							return [ $validator, $validator_stats, $key, false, true ];
						}

						if ( $is_valid == true ) {
							Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $validate_only == true ) {
								$save_result[$key] = true;
							} else {
								$save_result[$key] = $lf->Save( true, true );
							}
							$validator_stats['valid_records']++;
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ], ( ( $total_records > 1 && is_object( $lf ) ) ? $lf->getFullName() : null ) );
					} else if ( $validate_only == true ) {
						//Always fail transaction when valididate only is used, as	is saved to different tables immediately.
						$lf->FailTransaction();
					}

					$lf->CommitTransaction();
					$lf->setTransactionMode(); //Back to default isolation level.

					return [ $validator, $validator_stats, $key, $save_result, false ];
				};

				[ $validator, $validator_stats, $key, $save_result, $is_reauthentication_required ] = $this->getMainClassObject()->RetryTransaction( $transaction_function );

				if ( $is_reauthentication_required == true ) {
					return $this->getPermissionObject()->ReauthenticationRequired( $this->getCurrentUserObject() );
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
			$authentication->reauthenticationActionCompleted();

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result ); //Don't Enable System Job Queue status update, as it will trigger the spinner quite often when we rarely run background jobs currently.
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more users.
	 * @param array $data user data
	 * @return array|bool
	 */
	function deleteUser( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( DEMO_MODE == true ) {
			return $this->returnHandler( true );
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'delete' ) || $this->getPermissionObject()->Check( 'user', 'delete_own' ) || $this->getPermissionObject()->Check( 'user', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' Users', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserListFactory' ); /** @var UserListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					if ( $this->getPermissionObject()->Check( 'company', 'view' ) == true ) {
						$lf->getById( $id );//Allow deleting employees in other companies.
					} else {
						$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					}
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//Debug::Text('User ID: '. $user['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
						if ( $this->getPermissionObject()->Check( 'user', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'user', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
								|| ( $this->getPermissionObject()->Check( 'user', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === true ) ) {

							Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}
				} else {
					$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
					$lf->setDeleted( true );

					$is_valid = $lf->isValid();
					if ( $is_valid == true ) {
						Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ], ( ( $total_records > 1 && is_object( $lf ) ) ? $lf->getFullName() : null ) );
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Copy one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function copyUser( $data ) {
		//Can only Copy as New, not just a regular copy, as too much data needs to be changed,
		//such as username, password, employee_number, SIN, first/last name address...
		return $this->returnHandler( false );
	}

	/**
	 * Check if username is unique or not.
	 * @param string $user_name user name
	 * @return array|bool
	 */
	function isUniqueUserName( $user_name ) {
		Debug::Text( 'Checking for unique user name: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

		$uf = TTNew( 'UserFactory' ); /** @var UserFactory $uf */
		$retval = $uf->isUniqueUserName( $user_name );

		return $this->returnHandler( $retval );
	}

	/**
	 * Allows currently logged in user to change their password.
	 * @param string $new_password
	 * @param string $new_password2
	 * @param string $type
	 * @return array|bool
	 */
	function changePassword( $new_password, $new_password2, $type = 'user_name' ) {
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $this->getCurrentUserObject()->getId(), $this->getCurrentCompanyObject()->getId() );
		if ( $ulf->getRecordCount() == 1 ) {
			$uf = $ulf->getCurrent();

			global $authentication;

			if ( $authentication->isSessionReauthenticated() === false ) {
				return $this->getPermissionObject()->ReauthenticationRequired( $this->getCurrentUserObject() );
			}

			if ( $authentication->getRateLimitObject()->check() == false ) {
				Debug::Text( 'Excessive failed password attempts... Preventing password change from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
				sleep( 5 ); //Excessive password attempts, sleep longer.

				$uf->Validator->isTrue( 'current_password',
										false,
										TTi18n::gettext( 'Current password is incorrect' ) . ' (z)' );
			} else {
				$uf->setIsRequiredCurrentPassword( false );

				switch ( strtolower( $type ) ) {
					case 'quick_punch':
					case 'quick_punch_id':
					case 'phone_id':
					case 'phone':
						if ( $this->getPermissionObject()->Check( 'user', 'edit_own_phone_password' ) == false ) {
							return $this->getPermissionObject()->PermissionDenied();
						}

						$log_description = TTi18n::getText( 'Password - Quick Punch' );

						if ( $new_password != '' || $new_password2 != '' ) {
							if ( $new_password === $new_password2 ) {
								$uf->setPhonePassword( $new_password );
							} else {
								$uf->Validator->isTrue( 'password',
														false,
														TTi18n::gettext( 'Passwords don\'t match' ) );
							}
						}
						break;
					case 'user_name':
					case 'web':
						if ( $this->getPermissionObject()->Check( 'user', 'edit_own_password' ) == false ) {
							return $this->getPermissionObject()->PermissionDenied();
						}

						if ( $uf->getCompanyObject()->getLDAPAuthenticationType() == 0 && $uf->getMultiFactorType() < 1000 ) { //1000 = SAML
							$log_description = TTi18n::getText( 'Password - Web' );

							if ( $new_password != '' || $new_password2 != '' ) {
								if ( $new_password === $new_password2 ) {
									$uf->setPassword( $new_password );
								} else {
									$uf->Validator->isTrue( 'password',
															false,
															TTi18n::gettext( 'Passwords don\'t match' ) );
								}
							}
						} else {
							Debug::Text( 'LDAP or SAML Authentication is enabled, password changing is disabled! ', __FILE__, __LINE__, __METHOD__, 10 );
							$uf->Validator->isTrue( 'current_password',
													false,
													TTi18n::getText( 'Please contact your administrator for instructions on changing your password.' ) . ' (LDAP / SAML)' );
						}
						break;
				}
			}

			if ( $uf->isValid() ) {
				//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
				$authentication->reauthenticationActionCompleted();

				if ( DEMO_MODE == true ) {
					//Return TRUE even in demo mode, but nothing happens.
					return $this->returnHandler( true );
				} else {
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 20, $log_description, null, $uf->getTable() );

					$authentication->getRateLimitObject()->delete(); //Clear failed password rate limit upon successful login.

					$retval = $uf->Save( false ); //UserID is needed below.

					//Logout all other sessions for this user.
					$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
					$authentication->logoutUser( $uf->getID() );

					return $this->returnHandler( $retval ); //Single valid record
				}
			} else {
				//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
				$authentication->reauthenticationActionCompleted();
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $uf->Validator->getErrorsArray(), [ 'total_records' => 1, 'valid_records' => 0 ] );
			}
		}

		return $this->returnHandler( false );
	}


	/**
	 * Returns a list of unique provinces that employees are assigned to.
	 * @return array
	 */
	function getUniqueUserProvinces() {
		//Get a unique list of states each employee belongs to
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
		$retarr = [];
		if ( $ulf->getRecordCount() > 0 ) {
			foreach ( $ulf as $u_obj ) {
				$retarr[$u_obj->getProvince()] = $u_obj->getProvince();
			}
		} else {
			$retarr = false;
		}

		return $retarr;
	}

	/**
	 * @param $email
	 * @return bool
	 */
	function UnsubscribeEmail( $email ) {
		if ( $email != '' && $this->getPermissionObject()->Check( 'company', 'edit' ) ) {
			return UserFactory::UnsubscribeEmail( $email );
		}

		return false;
	}

	/**
	 * @param string $user_ids UUID
	 * @return bool
	 */
	function sendValidationEmail( $user_ids ) {
		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) || $this->getPermissionObject()->Check( 'user', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $user_ids, $this->getCurrentCompanyObject()->getId() );
		if ( $ulf->getRecordCount() == 1 ) {
			$emails_sent = 0;
			foreach ( $ulf as $u_obj ) {
				if ( $u_obj->getWorkEmailIsValid() == false ) {
					$u_obj->sendValidateEmail( 'work' );
					$emails_sent++;
				}

				if ( $u_obj->getHomeEmailIsValid() == false ) {
					$u_obj->sendValidateEmail( 'home' );
					$emails_sent++;
				}
			}

			Debug::Text( 'Users Found: ' . $ulf->getRecordCount() . ' Validation Emails Sent: ' . $emails_sent, __FILE__, __LINE__, __METHOD__, 10 );
			if ( $emails_sent > 0 ) {
				return true;
			}
		}

		Debug::Text( 'ERROR: No users to send validation emails to.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * Get user data for one or more users. This is an alias for getUser() that can be overridden by a plugin for getting data on remote servers.
	 * @param array $data             filter data, see reference for details.
	 * @param boolean $disable_paging disables paging and returns all records.
	 * @return array
	 * @see UserListFactory::getAPISearchByCompanyIdAndArrayCriteria() To see a description of the ListFactory that is used.
	 */
	function getCompanyUser( $data = null, $disable_paging = false ) {
		return $this->getUser( $data, $disable_paging );
	}

	/**
	 * @param int $rating Accepted values are -1, 0, 1.
	 * @param bool $message
	 * @return array|bool
	 */
	function setUserFeedbackRating( $rating, $message = false ) {
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $this->getCurrentUserObject()->getId(), $this->getCurrentCompanyObject()->getId() );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();

			if ( $rating != $u_obj->getFeedbackRating() ) {
				$u_obj->setFeedbackRating( $rating );
				if ( $u_obj->isValid() ) {
					$retval = $u_obj->Save( false );
					if ( $retval == true ) {
						//Save in user_setting table as well, so we have other information such as created date.
						UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getId(), 'feedback_rating', $rating, 20 ); //20=Private

						$ttsc = new TimeTrexSoapClient();
						$ttsc->sendUserFeedback( $rating, $message, $u_obj );

						//Since we are updating the user record, the audit log will contain the rating change.
						//TTLog::addEntry( $u_obj->getId(), 500, TTi18n::getText('Feedback Rating').': '. $rating .' '. TTi18n::getText('Message') .': '. $message, $u_obj->getId(), $u_obj->getTable() );
					}
				}
			} else if ( $message != '' ) {
				$ttsc = new TimeTrexSoapClient();
				$ttsc->sendUserFeedback( $rating, $message, $u_obj );
			}

			return $this->returnHandler( true );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param $submitted_review Accepted values are 0 or 1.
	 * @return array|bool
	 */
	function setUserFeedbackReview( $submitted_review ) {
		$submitted_review = (int)$submitted_review;

		return $this->returnHandler( UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getId(), 'feedback_rating_review', $submitted_review, 20 ) ); //20=Private
	}

	/**
	 * @param $employee_id
	 * @return array|bool
	 */
	function deleteImage( $employee_id ) {
		//permissions match setUser()
		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_own' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) || $this->getPermissionObject()->Check( 'user', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$result = $this->stripReturnHandler( $this->getUser( [ 'filter_data' => [ 'id' => $employee_id ] ] ) );
		if ( isset( $result[0] ) && count( $result[0] ) > 0 ) {
			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
			$file_name = $uf->getPhotoFileName( $this->current_company->getId(), $employee_id, false ); //Do not include default image.

			if ( file_exists( $file_name ) ) {
				unlink( $file_name );
				TTLog::addEntry( $employee_id, 30, TTi18n::getText( 'Photo' ), null, $uf->getTable() );
			}
		}

		return $this->returnHandler( true );
	}

	/**
	 * Get the number of pending authorizations, notifications and messages for the current user.
	 * @param array $object_types
	 * @return array
	 */
	function getUserPendingTotals( $object_types = [] ) {
		$totals = [];

		if ( empty( $object_types ) ) {
			$object_types = [ 'notification', 'request', 'message', 'request_authorization', 'timesheet_authorization', 'expense_authorization' ];
		}

		//No permissions required to view own notifications.
		//Unread notifications.
		if ( in_array( 'notification', $object_types ) ) {
			$ndtlf = TTnew( 'NotificationListFactory' );
			$totals['notification'] = (int)$ndtlf->getUnreadCountByUserIdAndCompanyId( $this->getCurrentUserObject()->getId(), $this->getCurrentUserObject()->getCompany() );
		}

		//Unread messages.
		if ( in_array( 'message', $object_types ) && $this->getPermissionObject()->Check( 'message', 'enabled' ) && $this->getPermissionObject()->Check( 'message', 'view_own' ) ) {
			$mclf = TTnew( 'MessageControlListFactory' );
			$totals['message'] = (int)$mclf->getNewMessagesByCompanyIdAndUserId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		}

		//Pending request authorizations.
		if ( in_array( 'request_authorization', $object_types ) && $this->getPermissionObject()->Check( 'request', 'enabled' ) && $this->getPermissionObject()->Check( 'request', 'authorize' ) && ( $this->getPermissionObject()->Check( 'request', 'view_child' ) || $this->getPermissionObject()->Check( 'request', 'view' ) ) ) {
			$rlf = TTnew( 'RequestListFactory' );
			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$type_ids = array_keys( $rlf->getOptions( 'type' ) );
			$hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $this->getCurrentUserObject()->getId(), $rlf->getHierarchyTypeId( $type_ids ) );
			$totals['request_authorization'] = (int)$rlf->getTotalPendingByCompanyIdAndHierarchyLevelMap( $this->getCurrentCompanyObject()->getId(), [ 'hierarchy_level_map' => $hierarchy_level_arr[1] ?? false, 'authorized' => [ 0 ], 'status_id' => [ 30 ], 'type_id' => $type_ids, 'permission_children_ids' => $this->getPermissionObject()->getPermissionChildren( 'request', 'view' ) ] );
		}

		//Pending timesheet authorizations.
		if ( in_array( 'timesheet_authorization', $object_types ) && $this->getPermissionObject()->Check( 'punch', 'enabled' ) && $this->getPermissionObject()->Check( 'punch', 'verify_time_sheet' ) && ( $this->getPermissionObject()->Check( 'punch', 'view_child' ) || $this->getPermissionObject()->Check( 'punch', 'view' ) ) ) {
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $this->getCurrentUserObject()->getId(), 90 );
			$totals['timesheet_authorization'] = (int)$pptsvlf->getTotalPendingByCompanyIdAndHierarchyLevelMap( $this->getCurrentCompanyObject()->getId(), [ 'hierarchy_level_map' => $hierarchy_level_arr[1] ?? false, 'authorized' => [ 0 ], 'permission_children_ids' => $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' ) ] );
		}

		//Pending expense authorizations.
		if ( in_array( 'expense_authorization', $object_types ) && $this->getCurrentCompanyObject()->getProductEdition() >= 25 && $this->getPermissionObject()->Check( 'user_expense', 'enabled' ) && $this->getPermissionObject()->Check( 'user_expense', 'authorize' ) && ( $this->getPermissionObject()->Check( 'user_expense', 'view_child' ) || $this->getPermissionObject()->Check( 'user_expense', 'view' ) ) ) {
			$uelf = TTnew( 'UserExpenseListFactory' );
			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $this->getCurrentUserObject()->getId(), 200 );
			$totals['expense_authorization'] = (int)$uelf->getTotalPendingByCompanyIdAndHierarchyLevelMap( $this->getCurrentCompanyObject()->getId(), [ 'hierarchy_level_map' => $hierarchy_level_arr[1] ?? false, 'authorized' => [ 0 ], 'status_id' => [ 20 ], 'permission_children_ids' => $this->getPermissionObject()->getPermissionChildren( 'user_expense', 'view' ) ] );
		}

		Debug::Arr( $totals, ' Pending Totals: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $this->returnHandler( $totals );
	}

	/**
	 * Check if MFA is enabled for the current user.
	 *   Mostly used for the mobile app to determine if a confirmation message should be displayed on logout. If they logout, its likely with MFA enabled they would lock themselves out.
	 * @return array|bool
	 */
	function isMFAEnabled() {
		if ( is_object( $this->getCurrentUserObject() ) === true ) {
			return $this->getCurrentUserObject()->isMFAEnabled();
		}

		return false;
	}

	/**
	 * @param $mfa_type_id
	 * @return array|bool
	 */
	function setMultiFactorSettings( $mfa_type_id ) {
		global $authentication;

		if ( is_object( $this->getCurrentUserObject() ) === false ) {
			return $this->returnHandler( false );
		}

		if ( $this->getCurrentUserObject()->getMultiFactorType() == 1000 ) { //1000 = SAML
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'SAML is enabled, you cannot change MFA settings.' ) );
		}

		if ( $this->getCurrentUserObject()->getMultiFactorType() != $mfa_type_id && $mfa_type_id != 0 && $this->getCurrentUserObject()->getMultiFactorType() != 0 ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'You must disable multifactor before switching to a different type.' ) );
		}

		//When disabling MFA ($mfa_type_id = 0), we need to make sure to use the users current $mfa_type_id and not the one passed in.
		//This means to disable MFA the user has to verify by using the current MFA type.
		if ( $mfa_type_id == 0 ) {
			//Passing null for $force_mfa_type_id will default to the users current MFA type.
			$force_mfa_type_id = null;
		} else {
			$force_mfa_type_id = $mfa_type_id;
		}

		if ( $authentication->isSessionReauthenticated() === false ) {
			return $this->getPermissionObject()->ReauthenticationRequired( $this->getCurrentUserObject(), $force_mfa_type_id );
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $this->getCurrentUserObject()->getId() );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$u_obj->setMultiFactorType( $mfa_type_id );
			if ( $u_obj->isValid() ) {
				$u_obj->Save();

				if ( $mfa_type_id > 0 ) {
					//Multifactor enabled, change user to 'user_name_multifactor' authentication.
					$new_session_type_id = 810;
				} else {
					//Multifactor disabled, change user to 'user_name' authentication.
					$new_session_type_id = 800;

					//Delete PasskeyUsername cookie.
					setcookie( 'PasskeyUsername', '', ( time() - 9999999 ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
				}

				$authentication->setType( $new_session_type_id );
				//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
				$authentication->reauthenticationActionCompleted();

				return $this->returnHandler( true );
			}
		}

		//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
		$authentication->reauthenticationActionCompleted();
		return $this->returnHandler( false );
	}
}

?>
