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
class ExceptionFactory extends Factory {
	protected $table = 'exception';
	protected $pk_sequence_name = 'exception_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $exception_policy_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'UserID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_period_id' )->setFunctionMap( 'PayPeriodID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'date_stamp' )->setFunctionMap( 'Date' )->setType( 'date' )->setIsNull( false ),
							TTSCol::new( 'exception_policy_id' )->setFunctionMap( 'ExceptionPolicyID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'punch_id' )->setFunctionMap( 'PunchID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'punch_control_id' )->setFunctionMap( 'PunchControlID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_demerit' )->setFunctionMap( 'EnableDemerit' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'authorized' )->setFunctionMap( 'Authorized' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'authorization_level' )->setFunctionMap( 'AuthorizationLevel' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'acknowledged_type_id' )->setFunctionMap( 'AcknowledgedType' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'acknowledged_reason_id' )->setFunctionMap( 'AcknowledgedReasonID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'varchar' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'c.id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'c.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'not_uuid_list' )->setColumn( 'c.id' )->setMulti( true ),
							TTSSearchField::new( 'user_status_id' )->setType( 'numeric_list' )->setColumn( 'c.status_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'c.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'severity_id' )->setType( 'numeric_list' )->setColumn( 'i.severity_id' )->setMulti( true ),
							TTSSearchField::new( 'exception_policy_type_id' )->setType( 'upper_text_list' )->setColumn( 'i.type_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid_list' )->setColumn( 'a.pay_period_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_period_status_id' )->setType( 'numeric_list' )->setColumn( 'h.status_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'c.group_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'c.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'c.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'c.title_id' )->setMulti( true ),
							TTSSearchField::new( 'branch_id' )->setType( 'uuid_list' )->setColumn( 'pcf.branch_id' )->setMulti( true ),
							TTSSearchField::new( 'department_id' )->setType( 'uuid_list' )->setColumn( 'pcf.department_id' )->setMulti( true ),
							TTSSearchField::new( 'start_date' )->setType( 'date' )->setColumn( 'a.date_stamp' ),
							TTSSearchField::new( 'end_date' )->setType( 'date' )->setColumn( 'a.date_stamp' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIException' )->setMethod( 'getException' )
									->setSummary( 'Get exception records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIException' )->setMethod( 'getExceptionDefaultData' )
									->setSummary( 'Get default exception data used for creating new exceptions. Use this before calling setException to get the correct default data.' )
					)
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				//Exception life-cycle
				//
				// - Exception occurs, such as missed out punch, in late.
				//	 - If the exception is pre-mature, we wait 16-24hrs for it to become a full-blown exception
				// - If the exception requires authorization, it sits in a pending state waiting for supervsior intervention.
				// - Supervisor authorizes the exception, or makes a correction, leaves a note or something.
				//	 - Exception no longer appears on timesheet/exception list.
				$retval = [
						5  => TTi18n::gettext( 'Pre-Mature' ),
						30 => TTi18n::gettext( 'PENDING AUTHORIZATION' ),
						40 => TTi18n::gettext( 'AUTHORIZATION OPEN' ),
						50 => TTi18n::gettext( 'ACTIVE' ),
						55 => TTi18n::gettext( 'AUTHORIZATION DECLINED' ),
						60 => TTi18n::gettext( 'DISABLED' ),
						70 => TTi18n::gettext( 'Corrected' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						//'-1005-user_status' => TTi18n::gettext('Employee Status'),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1039-group'              => TTi18n::gettext( 'Group' ),
						'-1050-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1060-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1070-branch'             => TTi18n::gettext( 'Branch' ),
						'-1080-department'         => TTi18n::gettext( 'Department' ),
						'-1090-country'            => TTi18n::gettext( 'Country' ),
						'-1100-province'           => TTi18n::gettext( 'Province' ),

						'-1120-date_stamp'               => TTi18n::gettext( 'Date' ),
						'-1130-severity'                 => TTi18n::gettext( 'Severity' ),
						'-1140-exception_policy_type'    => TTi18n::gettext( 'Exception' ),
						'-1150-exception_policy_type_id' => TTi18n::gettext( 'Code' ),
						'-1160-policy_group'             => TTi18n::gettext( 'Policy Group' ),
						'-1170-permission_group'         => TTi18n::gettext( 'Permission Group' ),
						'-1200-pay_period_schedule'      => TTi18n::gettext( 'Pay Period Schedule' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( [ 'date_stamp', 'severity', 'exception_policy_type', 'exception_policy_type_id' ], Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'date_stamp',
						'severity',
						'exception_policy_type',
						'exception_policy_type_id',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                          => 'ID',
				'date_stamp'                  => false,
				'pay_period_start_date'       => false,
				'pay_period_end_date'         => false,
				'pay_period_transaction_date' => false,
				'pay_period'                  => false,
				'exception_policy_id'         => 'ExceptionPolicyID',
				'punch_control_id'            => 'PunchControlID',
				'punch_id'                    => 'PunchID',
				'type_id'                     => 'Type',
				'type'                        => false,
				'severity_id'                 => false,
				'severity'                    => false,
				'exception_color'             => 'Color',
				'exception_background_color'  => 'BackgroundColor',
				'exception_policy_type_id'    => false,
				'exception_policy_type'       => false,
				'policy_group'                => false,
				'permission_group'            => false,
				'pay_period_schedule'         => false,
				//'enable_demerit' => 'EnableDemerits',

				'pay_period_id'          => false,
				'pay_period_schedule_id' => false,

				'user_id'               => false,
				'first_name'            => false,
				'last_name'             => false,
				'country'               => false,
				'province'              => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'group'                 => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,

				'branch_id'     => false,
				'branch'        => false,
				'department_id' => false,
				'department'    => false,

				'deleted' => 'Deleted',
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
	 * @return bool
	 */
	function getExceptionPolicyObject() {
		return $this->getGenericObject( 'ExceptionPolicyListFactory', $this->getExceptionPolicyID(), 'exception_policy_obj' );
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

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'user_id', $value );
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
		if ( $value == null ) {
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}

		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
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
	function setDateStamp( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value > 0 ) {
			return $this->setGenericDataValue( 'date_stamp', $value );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getExceptionPolicyID() {
		return $this->getGenericDataValue( 'exception_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setExceptionPolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'exception_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchControlID() {
		return $this->getGenericDataValue( 'punch_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPunchControlID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'punch_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchID() {
		return $this->getGenericDataValue( 'punch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPunchID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'punch_id', $value );
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
	 * @return bool|mixed
	 */
	function getEnableDemerits() {
		return $this->getGenericDataValue( 'enable_demerit' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableDemerits( $value ) {
		$this->setGenericDataValue( 'enable_demerit', $value );

		return true;
	}

	/**
	 * @return bool|string
	 */
	function getBackgroundColor() {
		//Use HTML color codes so they work in Flex too.
		$retval = false;
		if ( $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn( 'severity_id' ) != '' ) {
				switch ( $this->getColumn( 'severity_id' ) ) {
					case 10:
						$retval = false;
						break;
					case 20:
						$retval = '#FFFF00'; #'yellow';
						break;
					case 25:
						$retval = '#FF9900'; #'orange';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getColor() {
		$retval = false;

		//Use HTML color codes so they work in Flex too.
		if ( $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn( 'severity_id' ) != '' ) {
				switch ( $this->getColumn( 'severity_id' ) ) {
					case 10:
						$retval = '#000000'; #'black';
						break;
					case 20:
						$retval = '#0000FF'; #'blue';
						break;
					case 25:
						$retval = '#FF9900'; #'blue';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}


	/**
	 * @param $triggered_user_id
	 * @param $notified_user_id
	 * @param $severity_id
	 * @return false|string
	 */
	function getNotificationType( $triggered_user_id, $notified_user_id, $severity_id ) {
		if ( $this->getType() == 5 ) {
			//pre-mature exception can be ignored
			$retval = false;
		} else {
			if ( $triggered_user_id == $notified_user_id ) {
				$triggered_by_label = 'own';
			} else {
				$triggered_by_label = 'child';
			}

			switch ( $severity_id ) {
				case 10:
					$severity = 'low';
					break;
				case 20:
					$severity = 'medium';
					break;
				case 25:
					$severity = 'high';
					break;
				case 30:
					$severity = 'critical';
					break;
			}
			$retval = 'exception_' . $triggered_by_label . '_' . $severity;
		}

		return $retval;
	}

	/**
	 * @param object $u_obj
	 * @param object $ep_obj
	 * @return array|bool
	 */
	function getNotificationExceptionUserIds( $u_obj = null, $ep_obj = null ) {
		Debug::text( ' Get User IDs of who will be receiving the notification...', __FILE__, __LINE__, __METHOD__, 10 );

		//Make sure type is not pre-mature.
		if ( $this->getType() > 5 ) {
			if ( ( $ep_obj->getEmailNotification() == 10 || $ep_obj->getEmailNotification() == 100 ) ) { //Make sure exception policy has notifications enabled for employee or BOTH.
				$retarr[] = $u_obj->getId();
			}

			if ( ( $ep_obj->getEmailNotification() == 20 || $ep_obj->getEmailNotification() == 100 ) ) { //Make sure exception policy has notifications enabled for superior or BOTH.
				$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
				$parent_user_id = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $u_obj->getCompany(), $u_obj->getId(), 80 );
				if ( $parent_user_id != false ) {
					//Parent could be multiple supervisors, make sure we notify them all.
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByIdAndCompanyId( $parent_user_id, $u_obj->getCompany() );
					if ( $ulf->getRecordCount() > 0 ) {
						foreach ( $ulf as $parent_user_obj ) {
							$retarr[] = $parent_user_obj->getId();
						}
					}
				} else {
					Debug::Text( ' No Hierarchy Parent Found, skipping notification to supervisor.', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		} else {
			Debug::text( ' Pre-Mature exception, or not in production mode, skipping notification...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( isset( $retarr ) ) {
			$retarr = array_unique( $retarr ); //Remove any duplicates, as the current user could match a parent in a hierarchy too.
			return $retarr;
		}

		return false;
	}


	/*

		What do we pass the emailException function?
			To address, CC address (home email) and Bcc (supervisor) address?

	*/
	/**
	 * @param object $u_obj
	 * @param int $date_stamp EPOCH
	 * @param object $punch_obj
	 * @param object $schedule_obj
	 * @param object $ep_obj
	 * @return bool
	 */
	function sendNotificationException( $u_obj, $date_stamp, $punch_obj = null, $schedule_obj = null, $ep_obj = null ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		if ( $date_stamp == '' ) {
			return false;
		}

		if ( !is_object( $ep_obj ) ) {
			$ep_obj = $this->getExceptionPolicyObject();
		}

		//Only email on active exceptions.
		if ( $this->getType() != 50 ) {
			return false;
		}

		$user_ids = $this->getNotificationExceptionUserIds( $u_obj, $ep_obj );
		if ( $user_ids == false ) {
			return false;
		}

		foreach ( $user_ids as $user_id ) {
			//Grab each users preferences and etc can be custom to them and their language etc.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_to_obj = $ulf->getCurrent();

				if ( is_object( $user_to_obj ) ) {
					$user_to_pref_obj = $user_to_obj->getUserPreferenceObject(); /** @var UserPreferenceFactory $user_to_pref_obj */
					$user_to_pref_obj->setDateTimePreferences();
					TTi18n::setLanguage( $user_to_pref_obj->getLanguage() );
					TTi18n::setCountry( $user_to_obj->getCountry() );
					TTi18n::setLocale();
				} else {
					return false;
				}
			}

			$type = $this->getNotificationType( $u_obj->getId(), $user_id, $ep_obj->getSeverity() );

			//Define title_short/body variables here.
			$search_arr = [
					'#employee_first_name#',
					'#employee_last_name#',
					'#employee_default_branch#',
					'#employee_default_department#',
					'#employee_group#',
					'#employee_title#',
					'#exception_code#',
					'#exception_name#',
					'#exception_severity#',
					'#date#',
					'#company_name#',
					'#link#',
					'#schedule_start_time#',
					'#schedule_end_time#',
					'#schedule_branch#',
					'#schedule_department#',
					'#punch_time#',
					'#url#',
			];

			$replace_arr = Misc::escapeHTML( [
					$u_obj->getFirstName(),
					$u_obj->getLastName(),
					( is_object( $u_obj->getDefaultBranchObject() ) ) ? $u_obj->getDefaultBranchObject()->getName() : null,
					( is_object( $u_obj->getDefaultDepartmentObject() ) ) ? $u_obj->getDefaultDepartmentObject()->getName() : null,
					( is_object( $u_obj->getGroupObject() ) ) ? $u_obj->getGroupObject()->getName() : null,
					( is_object( $u_obj->getTitleObject() ) ) ? $u_obj->getTitleObject()->getName() : null,
					$ep_obj->getType(),
					Option::getByKey( $ep_obj->getType(), $ep_obj->getOptions( 'type' ) ),
					Option::getByKey( $ep_obj->getSeverity(), $ep_obj->getOptions( 'severity' ) ),
					TTDate::getDate( 'DATE', $date_stamp ),
					( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null,
					null,
					( is_object( $schedule_obj ) ) ? TTDate::getDate( 'TIME', $schedule_obj->getStartTime() ) : null,
					( is_object( $schedule_obj ) ) ? TTDate::getDate( 'TIME', $schedule_obj->getEndTime() ) : null,
					( is_object( $schedule_obj ) && is_object( $schedule_obj->getBranchObject() ) ) ? $schedule_obj->getBranchObject()->getName() : null,
					( is_object( $schedule_obj ) && is_object( $schedule_obj->getDepartmentObject() ) ) ? $schedule_obj->getDepartmentObject()->getName() : null,
					( is_object( $punch_obj ) ) ? TTDate::getDate( 'TIME', $punch_obj->getTimeStamp() ) : null,
					( Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() ),
			] );

			if ( strpos( $type, 'exception_own' ) !== false ) {
				//This is their own exception, so don't need a bunch of extra data like name and such.
				$title_short = TTi18n::gettext( 'Exception' ) .': #exception_name# (#exception_code#).';
				$title_long = TTi18n::gettext( 'Exception' ) .': #exception_name# (#exception_code#) '. TTi18n::gettext( 'on' ) . ' #date#';
				$body_short = TTi18n::gettext( 'Severity' ) . ': #exception_severity# ' . TTi18n::gettext( 'Date' ) .': #date#';
			} else {
				//This is an exception for a subordinate or someone else, so include additional information.
				$title_short = TTi18n::gettext( 'Subordinate Exception' ) .': #exception_name# (#exception_code#).';
				//$title_long = '#exception_name# (#exception_code#) ' . TTi18n::gettext( 'exception for' ) . '  ' . TTi18n::gettext( 'on' ) . ' #date#';
				$title_long = TTi18n::gettext( 'Subordinate Exception' ) .': #exception_name# (#exception_code#) '. TTi18n::gettext( 'for' ) .' #employee_first_name# #employee_last_name# ' . TTi18n::gettext( 'on' ) . ' #date#';
				$body_short = TTi18n::gettext( 'Employee' ) . ': #employee_first_name# #employee_last_name# '. TTi18n::gettext( 'Severity' ) . ': #exception_severity# ' . TTi18n::gettext( 'Date' ) .': #date#';
			}

			//$exception_email_body = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
			$exception_email_body = TTi18n::gettext( 'Employee' ) . ': #employee_first_name# #employee_last_name#' . "\n";
			$exception_email_body .= TTi18n::gettext( 'Date' ) . ': #date#' . "\n";
			$exception_email_body .= TTi18n::gettext( 'Exception' ) . ': #exception_name# (#exception_code#)' . "\n";
			$exception_email_body .= TTi18n::gettext( 'Severity' ) . ': #exception_severity#' . "\n";

			$exception_email_body .= ( $replace_arr[12] != '' || $replace_arr[13] != '' || $replace_arr[14] != '' || $replace_arr[15] != '' || $replace_arr[16] != '' ) ? "\n" : null;
			$exception_email_body .= ( $replace_arr[12] != '' && $replace_arr[13] != '' ) ? TTi18n::gettext( 'Schedule' ) . ': #schedule_start_time# - #schedule_end_time#' . "\n" : null;
			$exception_email_body .= ( $replace_arr[14] != '' ) ? TTi18n::gettext( 'Schedule Branch' ) . ': #schedule_branch#' . "\n" : null;
			$exception_email_body .= ( $replace_arr[15] != '' ) ? TTi18n::gettext( 'Schedule Department' ) . ': #schedule_department#' . "\n" : null;
			if ( $replace_arr[16] != '' ) {
				$exception_email_body .= TTi18n::gettext( 'Punch' ) . ': #punch_time#' . "\n";
				$body_short .= ' '. TTi18n::gettext( 'Punch' ) . ': #punch_time#';
			} else if ( $replace_arr[12] != '' && $replace_arr[13] != '' ) {
				$exception_email_body .= TTi18n::gettext( 'Punch' ) . ': ' . TTi18n::gettext( 'None' ) . "\n";
				$body_short .= ' '. TTi18n::gettext( 'Punch' ) . ': ' . TTi18n::gettext( 'None' );
			}

			$exception_email_body .= ( $replace_arr[2] != '' || $replace_arr[3] != '' || $replace_arr[4] != '' || $replace_arr[5] != '' ) ? "\n" : null;
			$exception_email_body .= ( $replace_arr[2] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #employee_default_branch#' . "\n" : null;
			$exception_email_body .= ( $replace_arr[3] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #employee_default_department#' . "\n" : null;
			$exception_email_body .= ( $replace_arr[4] != '' ) ? TTi18n::gettext( 'Group' ) . ': #employee_group#' . "\n" : null;
			$exception_email_body .= ( $replace_arr[5] != '' ) ? TTi18n::gettext( 'Title' ) . ': #employee_title#' . "\n" : null;

			$exception_email_body .= "\n";
			$exception_email_body .= TTi18n::gettext( 'Link' ) . ': <a href="#url#">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Sign In' ) . '</a>' ."\n";

			$exception_email_body .= NotificationFactory::addEmailFooter( ( ( is_object( $u_obj->getCompanyObject() ) ) ? $u_obj->getCompanyObject()->getName() : null ) );
			$body_long = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $exception_email_body ) . '</pre></body></html>';

			$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=Exception';
			//if ( $type == 'exception_own_critical' || $type == 'exception_own_high' || $type == 'exception_own_medium' || $type == 'exception_own_low' ) {
			//	$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=Exception';
			//} else {
			//	//Child exception - link to their timesheet. We can't link directly to the day though, so this is less helpful?
			//	$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=TimeSheet&user_id=' . $u_obj->getId() . '&show_wage=0&timezone=0&mode=punch';
			//}

			//If being received on the mobile app, send the user to the timesheet.
			$payload = [ 'link' => $link, 'timetrex' => [ 'event' => [ [ 'type' => 'open_view', 'data' => [ 'date' => $date_stamp ], 'view_name' => 'TimeSheet' ] ] ] ]; //Open TimeSheet view for reviewing the timesheet and submitting requests from there.

			$title_short = str_replace( $search_arr, $replace_arr, $title_short );
			$title_long = str_replace( $search_arr, $replace_arr, $title_long );
			$body_short = str_replace( $search_arr, $replace_arr, $body_short );

			$notification_data = [
					'object_id'      => $this->getId(),
					'user_id'        => $user_id,
					'type_id'        => $type,
					'object_type_id' => 10,
					'title_short'    => $title_short, //For App notifications.
					'title_long'     => $title_long,
					'body_short'     => $body_short,
					'body_long_html' => $body_long, //For Emails.
					'payload'        => $payload,
			];

			Notification::sendNotification( $notification_data );
		}

		//reset datetime and tti8n preferences to current user
		$user_pref_obj = $u_obj->getUserPreferenceObject(); /** @var UserPreferenceFactory $user_pref_obj */
		$user_pref_obj->setDateTimePreferences();
		TTi18n::setLanguage( $user_pref_obj->getLanguage() );
		TTi18n::setCountry( $u_obj->getCountry() );
		TTi18n::setLocale();

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

		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
		// Pay Period
		if ( $this->getPayPeriod() != false && $this->getPayPeriod() != TTUUID::getZeroID() ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$this->Validator->isResultSetWithRows( 'pay_period',
												   $pplf->getByID( $this->getPayPeriod() ),
												   TTi18n::gettext( 'Invalid Pay Period' )
			);
		}
		// Date
		if ( $this->getDateStamp() !== false ) {
			$this->Validator->isDate( 'date_stamp',
									  $this->getDateStamp(),
									  TTi18n::gettext( 'Incorrect date' ) );
			if ( $this->Validator->isError( 'date_stamp' ) == false ) {
				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
			}
		} else {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Incorrect date' )
			);
		}
		// Exception Policy ID
		if ( $this->getExceptionPolicyID() !== false && $this->getExceptionPolicyID() != TTUUID::getZeroID() ) {
			$eplf = TTnew( 'ExceptionPolicyListFactory' ); /** @var ExceptionPolicyListFactory $eplf */
			$this->Validator->isResultSetWithRows( 'exception_policy',
												   $eplf->getByID( $this->getExceptionPolicyID() ),
												   TTi18n::gettext( 'Invalid Exception Policy ID' )
			);
		}
		// Punch Control ID
		if ( $this->getPunchControlID() !== false && $this->getPunchControlID() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'punch_control',
												   $pclf->getByID( $this->getPunchControlID() ),
												   TTi18n::gettext( 'Invalid Punch Control ID' )
			);
		}
		// Punch ID
		if ( $this->getPunchID() !== false && $this->getPunchID() != TTUUID::getZeroID() ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$this->Validator->isResultSetWithRows( 'punch',
												   $plf->getByID( $this->getPunchID() ),
												   TTi18n::gettext( 'Invalid Punch ID' )
			);
		}
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == false && $this->getDateStamp() == false ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getPayPeriod() == false ) {
			$this->setPayPeriod();
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$variable_function_map = $this->getVariableToFunctionMap();

		$epf = TTnew( 'ExceptionPolicyFactory' ); /** @var ExceptionPolicyFactory $epf */
		$exception_policy_type_options = $epf->getOptions( 'type' );
		$exception_policy_severity_options = $epf->getOptions( 'severity' );

		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {
					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'pay_period_id':
						case 'pay_period_schedule_id':
							//case 'pay_period_start_date':
							//case 'pay_period_end_date':
							//case 'pay_period_transaction_date':
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'country':
						case 'province':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'branch_id':
						case 'branch':
						case 'department_id':
						case 'department':
						case 'severity_id':
						case 'exception_policy_type_id':
						case 'policy_group':
						case 'permission_group':
						case 'pay_period_schedule':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'severity':
							$data[$variable] = Option::getByKey( $this->getColumn( 'severity_id' ), $exception_policy_severity_options );
							break;
						case 'exception_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'exception_policy_type_id' ), $exception_policy_type_options );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'pay_period_start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_start_date' ) ) );
							break;
						case 'pay_period_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_end_date' ) ) );
							break;
						case 'pay_period':
						case 'pay_period_transaction_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'pay_period_transaction_date' ) ) );
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

}

?>
