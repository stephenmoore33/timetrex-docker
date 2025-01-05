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
class AuthorizationFactory extends Factory {
	protected $table = 'authorizations';
	protected $pk_sequence_name = 'authorizations_id_seq'; //PK Sequence name

	protected $obj_handler = null;
	protected $obj_handler_obj = null;
	protected $hierarchy_arr = null;

	protected $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'object_type_id' )->setFunctionMap( 'ObjectType' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'object_id' )->setFunctionMap( 'Object' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'authorized' )->setFunctionMap( 'Authorized' )->setType( 'smallint' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'object_type_id' )->setType( 'numeric_list' )->setColumn( 'a.object_type_id' ),
							TTSSearchField::new( 'object_id' )->setType( 'uuid_list_with_all' )->setColumn( 'a.object_id' ),
							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid_list' )->setColumn( 'pptsvf.pay_period_id' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIAuthorization' )->setMethod( 'getAuthorization' )
									->setSummary( 'Get authorization records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIAuthorization' )->setMethod( 'setAuthorization' )
									->setSummary( 'Add or edit authorization records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIAuthorization' )->setMethod( 'deleteAuthorization' )
									->setSummary( 'Delete authorization records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIAuthorization' )->setMethod( 'getAuthorization' ) ),
											   ) ),
							TTSAPI::new( 'APIAuthorization' )->setMethod( 'getAuthorizationDefaultData' )
									->setSummary( 'Get default authorization data used for creating new authorizations. Use this before calling setAuthorization to get the correct default data.' ),
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
			case 'object_type':
				$retval = [
					//10 => 'default_schedule',
					//20 => 'schedule_amendment',
					//30 => 'shift_amendment',
					//40 => 'pay_stub_amendment',

					//52 => 'request_vacation',
					//54 => 'request_missed_punch',
					//56 => 'request_edit_punch',
					//58 => 'request_absence',
					//59 => 'request_schedule',
					90 => 'timesheet',

					200  => 'expense',

					//50 => 'request', //request_other
					1010 => 'request_punch',
					1020 => 'request_punch_adjust',
					1030 => 'request_absence',
					1040 => 'request_schedule',
					1100 => 'request_other',
				];
				break;
			case 'columns':
				$retval = [

						'-1010-created_by'   => TTi18n::gettext( 'Name' ),
						'-1020-created_date' => TTi18n::gettext( 'Date' ),
						'-1030-authorized'   => TTi18n::gettext( 'Authorized' ),
						//'-1100-object_type' => TTi18n::gettext('Object Type'),

						//'-2020-updated_by' => TTi18n::gettext('Updated By'),
						//'-2030-updated_date' => TTi18n::gettext('Updated Date'),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'created_by',
						'created_date',
						'authorized',
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
				'id'             => 'ID',
				'object_type_id' => 'ObjectType',
				'object_type'    => false,
				'object_id'      => 'Object',
				'authorized'     => 'Authorized',
				'deleted'        => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCurrentUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getCurrentUser(), 'user_obj' );
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
	 * @return array|bool|null
	 */
	function getHierarchyArray() {
		if ( is_array( $this->hierarchy_arr ) ) {
			return $this->hierarchy_arr;
		} else {
			$user_id = $this->getCurrentUser();

			if ( is_object( $this->getObjectHandler() ) ) {
				$this->getObjectHandler()->getByID( $this->getObject() );
				$current_obj = $this->getObjectHandler()->getCurrent();
				$object_user_id = $current_obj->getUser();

				if ( TTUUID::isUUID( $object_user_id ) && $object_user_id != TTUUID::getZeroID() && $object_user_id != TTUUID::getNotExistID() ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$company_id = $ulf->getById( $object_user_id )->getCurrent()->getCompany();
					Debug::Text( ' Authorizing User ID: ' . $user_id .' Object User ID: ' . $object_user_id . ' Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

					$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
					$this->hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $object_user_id, $this->getObjectType(), false );
					Debug::Arr( $this->hierarchy_arr, ' Hierarchy Arr: ', __FILE__, __LINE__, __METHOD__, 10 );

					return $this->hierarchy_arr;
				} else {
					Debug::Text( ' Could not find Object User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( ' ERROR: No ObjectHandler defined...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}


	/**
	 * @return array|bool
	 */
	function getHierarchyChildLevelArray() {
		$retval = [];

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					continue;
				}

				if ( $next_level == true ) {
					//Debug::Arr( $level_parent_arr, ' Child: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = array_merge( $retval, $level_parent_arr ); //Append from all levels.
				}
			}
		}

		if ( count( $retval ) > 0 ) {
			return $retval;
		}

		return [];
	}

	/**
	 * @param bool $force
	 * @return bool|mixed
	 */
	function getHierarchyCurrentLevelArray( $force = false ) {
		$retval = false;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					if ( $force == false ) {
						continue;
					}
				}

				if ( $next_level == true ) { //Current level is alway one level lower, as this often gets called after the level has been changed.
					$retval = $level_parent_arr;
					//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}

			if ( $next_level == true && $retval == false ) {
				//Current level was the top and only level.
				$retval = $level_parent_arr;
				//Debug::Arr( $level_parent_arr, ' Current: Level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $retval;
	}

	/**
	 * @return array|bool|mixed
	 */
	function getHierarchyParentLevelArray() {
		$retval = false;

		$user_id = TTUUID::castUUID( $this->getCurrentUser() );
		$parent_arr = array_reverse( (array)$this->getHierarchyArray() );
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			$next_level = false;
			foreach ( $parent_arr as $level_parent_arr ) {
				if ( is_array( $level_parent_arr ) && in_array( $user_id, $level_parent_arr ) ) {
					$next_level = true;
					continue;
				}

				//Since this loops in reverse, always assume the first element is the parent for cases where a subordinate may be submitting the object (ie: request) and it needs to go to the direct superiors.
				if ( $next_level == true ) {
					//Debug::Arr( $level_parent_arr, ' Parents: Level: '. $level, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $level_parent_arr;
					break;
				}
			}

			//If we get here without finding a parent, use the lowest lower parents by default.
			if ( $next_level == false ) {
				reset( $parent_arr );
				$retval = $parent_arr[key( $parent_arr )];
			}
		}

		return $retval;
	}

	/**
	 * This will return false if it can't find a hierarchy, or if its at the top level (1) and can't find a higher level.
	 * @return bool|int|string
	 */
	function getNextHierarchyLevel() {
		$retval = false;

		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			foreach ( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					break;
				}
				$retval = $level;
			}
		}

		if ( $retval < 1 ) {
			Debug::Text( ' ERROR, hierarchy level goes past 1... This shouldnt happen...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}

		return $retval;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $hierarchy_type_id
	 * @return int|mixed
	 */
	static function getInitialHierarchyLevel( $company_id, $user_id, $hierarchy_type_id ) {
		$hierarchy_highest_level = 99;
		if ( $company_id != '' && $user_id != '' && $hierarchy_type_id > 0 ) {
			$hlf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hlf */
			$hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, $hierarchy_type_id, false );
			if ( isset( $hierarchy_arr ) && is_array( $hierarchy_arr ) ) {
				//Debug::Arr( $hierarchy_arr, ' aUser ID ' . $user_id . ' Type ID: ' . $hierarchy_type_id . ' Array: ', __FILE__, __LINE__, __METHOD__, 10 );

				//See if current user is in superior list, if so, start at one level up in the hierarchy, unless its level 1.
				foreach ( $hierarchy_arr as $level => $superior_user_ids ) {
					if ( in_array( $user_id, $superior_user_ids, true ) == true ) {
						Debug::Text( '   Found user in superior list at level: ' . $level, __FILE__, __LINE__, __METHOD__, 10 );

						$i = $level;
						while ( isset( $hierarchy_arr[$i] ) ) {
							if ( $i != 1 ) {
								Debug::Text( '    Removing lower level: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
								unset( $hierarchy_arr[$i] );
							}
							$i++;
						}
					}
				}

				//Debug::Arr( $hierarchy_arr, ' bUser ID ' . $user_id . ' Type ID: ' . $hierarchy_type_id . ' Array: ', __FILE__, __LINE__, __METHOD__, 10 );
				$hierarchy_arr = array_keys( $hierarchy_arr );
				$hierarchy_highest_level = end( $hierarchy_arr );
			}
		}

		Debug::Text( ' Returning initial hierarchy level to: ' . $hierarchy_highest_level, __FILE__, __LINE__, __METHOD__, 10 );

		return $hierarchy_highest_level;
	}

	/**
	 * @return bool
	 */
	function isValidParent() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			krsort( $parent_arr );
			foreach ( $parent_arr as $level_parent_arr ) {
				if ( in_array( $user_id, $level_parent_arr ) ) {
					return true;
				}
			}
		}

		Debug::Text( ' Authorizing User is not a parent of the object owner: ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function isFinalAuthorization() {
		$user_id = $this->getCurrentUser();
		$parent_arr = $this->getHierarchyArray();
		if ( is_array( $parent_arr ) && count( $parent_arr ) > 0 ) {
			//Check that level 1 parent exists
			if ( isset( $parent_arr[1] ) && in_array( $user_id, $parent_arr[1] ) ) {
				Debug::Text( ' Final Authorization!', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		Debug::Text( ' NOT Final Authorization!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Checks to see if the currently logged in user is the only superior in the hierarchy at the current level.
	 *   This would normally be paired with a isFinalAuthorization() check as well.
	 * @return bool
	 */
	function isCurrentUserOnlySuperior() {
		$hierarchy_current_level_user_ids = $this->getHierarchyCurrentLevelArray();
		if ( count( $hierarchy_current_level_user_ids ) == 1 && in_array( $this->getCurrentUser(), $hierarchy_current_level_user_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return null|object
	 */
	function getObjectHandler() {
		if ( is_object( $this->obj_handler ) ) {
			return $this->obj_handler;
		} else {
			switch ( $this->getObjectType() ) {
				case 90: //TimeSheet
					$this->obj_handler = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
					break;
				case 200:
					$this->obj_handler = TTnew( 'UserExpenseListFactory' );
					break;
				case 50: //Requests
				case 1010:
				case 1020:
				case 1030:
				case 1040:
				case 1100:
					$this->obj_handler = TTnew( 'RequestListFactory' );
					break;
			}

			return $this->obj_handler;
		}
	}

	/**
	 * @return bool|int
	 */
	function getObjectType() {
		return $this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'object_type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getObject() {
		return $this->getGenericDataValue( 'object_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setObject( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'object_id', $value );
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
	function clearHistory() {
		Debug::text( 'Clearing Authorization History For Type: ' . $this->getObjectType() . ' ID: ' . $this->getObject(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getObjectType() === false || $this->getObject() === false ) {
			Debug::text( 'Clearing Authorization History FAILED!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$alf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $alf */
		$alf->getByObjectTypeAndObjectId( $this->getObjectType(), $this->getObject() );
		foreach ( $alf as $authorization_obj ) {
			$authorization_obj->setDeleted( true );
			$authorization_obj->Save();
		}

		return true;
	}

	/**
	 * @return object
	 */
	function getObjectHandlerObject() {
		if ( is_object( $this->obj_handler_obj ) ) {
			return $this->obj_handler_obj;
		} else {
			//Get user_id of object.
			$this->getObjectHandler()->getByID( $this->getObject() );
			$this->obj_handler_obj = $this->getObjectHandler()->getCurrent();
//			if ( method_exists( $this->obj_handler_obj, 'setCurrentUser' ) AND $this->obj_handler_obj->getCurrentUser() != $this->getCurrentUser() ) { //Required for authorizing TimeSheets from MyAccount -> TimeSheet Authorization.
//				$this->obj_handler_obj->setCurrentUser( $this->getCurrentUser() );
//			}

			return $this->obj_handler_obj;
		}
	}

	/**
	 * @return boolean
	 */
	function setObjectHandlerStatus() {
		$is_final_authorization = $this->isFinalAuthorization();

		$this->obj_handler_obj = $this->getObjectHandlerObject();
		if ( $this->getAuthorized() === true ) {
			if ( $is_final_authorization === true ) {
				//If no other superiors exist in the hierarchy and we are at the top level, assume its authorized.
				if ( $this->getCurrentUser() != $this->obj_handler_obj->getUser() || $this->isCurrentUserOnlySuperior() == true ) {
					Debug::Text( '  Approving Authorization... Final Authorizing Object: ' . $this->getObject() . ' - Type: ' . $this->getObjectType(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->obj_handler_obj->setAuthorizationLevel( 1 );
					$this->obj_handler_obj->setStatus( 50 ); //Active/Authorized
					$this->obj_handler_obj->setAuthorized( true );
				} else {
					Debug::Text( '  Currently logged in user is authorizing (or submitting as new) their own request, when other superiors exist in the hierarchy, not authorizing...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::text( '  Approving Authorization, moving to next level up...', __FILE__, __LINE__, __METHOD__, 10 );
				$current_level = $this->obj_handler_obj->getAuthorizationLevel();
				if ( $current_level > 1 ) { //Highest level is 1, so no point in making it less than that.

					//Get the next level above the current user doing the authorization, in case they have dropped down a level or two.
					$next_level = $this->getNextHierarchyLevel();
					if ( $next_level !== false && $next_level < $current_level ) {
						Debug::text( '  Current Level: ' . $current_level . ' Moving Up To Level: ' . $next_level, __FILE__, __LINE__, __METHOD__, 10 );
						$this->obj_handler_obj->setAuthorizationLevel( $next_level );
					}
				}
				unset( $current_level, $next_level );
			}
		} else {
			Debug::text( '  Declining Authorization...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->obj_handler_obj->setStatus( 55 ); //'AUTHORIZATION DECLINED'
			$this->obj_handler_obj->setAuthorized( false );
		}

		return true;
	}


	/**
	 * @return array|bool
	 */
	function getUserAuthorizationIds() {
		$object_handler_user_id = $this->getObjectHandlerObject()->getUser(); //Object handler (request) user_id.

		$is_final_authorization = $this->isFinalAuthorization();
		$authorization_level = $this->getObjectHandlerObject()->getAuthorizationLevel(); //This is the *new* level, not the old level.

		$hierarchy_current_level_arr = $this->getHierarchyCurrentLevelArray();
		Debug::Arr( $hierarchy_current_level_arr, '  Authorization Level: ' . $authorization_level . ' Authorized: ' . (int)$this->getAuthorized() . ' Is Final Auth: ' . (int)$is_final_authorization . ' Object Handler User ID: ' . $object_handler_user_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getAuthorized() == true && $authorization_level == 0 ) {
			//Final authorization has taken place
			//Notify original submittor and all lower level superiors?
			$user_ids = $this->getHierarchyChildLevelArray();

			if ( is_a( $this->getObjectHandlerObject(), 'PayPeriodTimeSheetVerify' ) ) { //is_a() will match on plugin class names too because it also checks the parent class name.
				//Check to see what type of timesheet verification is required, if its superior only, don't notify the employee to avoid confusion.
				if ( $this->getObjectHandlerObject()->getVerificationType() != 30 ) {
					$user_ids[] = $object_handler_user_id;
				} else {
					Debug::text( '  TimeSheetVerification for superior only, dont motify employee...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				$user_ids[] = $object_handler_user_id;
			}
			//Debug::Arr($user_ids , '  aAuthorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ' , __FILE__, __LINE__, __METHOD__, 10);
		} else {
			//Debug::Text('  bAuthorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized(), __FILE__, __LINE__, __METHOD__, 10);
			//Final authorization has *not* yet taken place
			if ( $this->getObjectHandlerObject()->getStatus() == 55 ) { //Declined
				//Authorization declined. Notify original submittor and all lower level superiors?
				$user_ids = $this->getHierarchyChildLevelArray();
				$user_ids[] = $object_handler_user_id;
				//Debug::Arr($user_ids , '  b1Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ', __FILE__, __LINE__, __METHOD__, 10);
			} else if ( $is_final_authorization == true && $this->getCurrentUser() == $object_handler_user_id && $this->getAuthorized() == true && $authorization_level == 1 ) {
				//Subordinate who is also a superior at the top and only level of the hierarchy is submitting a request.
				$user_ids = $this->getHierarchyCurrentLevelArray( true ); //Force to real current level.
				//Debug::Arr($user_ids , '  b2Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Child: ', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				//Authorized at a middle level, notify current level superiors only so they know its waiting on them.
				$user_ids = $this->getHierarchyParentLevelArray();
				//Debug::Arr($user_ids , '  b3Authorization Level: '. $authorization_level .' Authorized: '. (int)$this->getAuthorized() .' Parent: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		if( isset( $user_ids ) && !empty( $user_ids ) ) {
			//Remove the current authorizing user from the array, as they don't need to be notified as they are performing the action.
			$user_ids = array_diff( (array)$user_ids, [ $this->getCurrentUser() ] );         //CurrentUser is currently logged in user.

			//remove duplicate user_ids
			$user_ids = array_unique( $user_ids );
			return $user_ids;
		}

		return [];
	}

	/**
	 * @return bool
	 */
	function sendNotificationAuthorization( ) {
		Debug::Text( 'getNotificationData: ', __FILE__, __LINE__, __METHOD__, 10 );

		$user_ids = $this->getUserAuthorizationIds();
		if ( empty ( $user_ids ) ) {
			return false;
		}

		//Get initiator user from User Object so we can include more information in the message.
		if ( is_object( $this->getCurrentUserObject() ) ) {
			$u_obj = $this->getCurrentUserObject();
		} else {
			Debug::Text( 'From object does not exist: ' . $this->getCurrentUser(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		foreach ( $user_ids as $user_id ) {
			//Grab each users preferences as they can be custom to them and their language etc.
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
					Debug::Text( 'ERROR: User object does not exist, skipping User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue; //Move on to next user.
				}
			} else {
				Debug::Text( 'ERROR: User does not exist, skipping User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				continue; //Move on to next user.
			}

			$object_handler_user_obj = $this->getObjectHandlerObject()->getUserObject();                                                                                       //Object handler (request) user_id.
			$status_label = TTi18n::ucfirst( TTi18n::strtolower( Option::getByKey( $this->getObjectHandlerObject()->getStatus(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions( 'status' ) ) ) ) ); //PENDING, AUTHORIZED, DECLINED

			if ( $object_handler_user_obj->getId() === $user_to_obj->getId() ) {
				//When sending requests to the original user who submitted them, no need to put that persons name in the email/notification subject.
				$title_short = '#object_type# #status#.';
				$title_long = '#object_type# #status# ' . TTi18n::gettext( 'for' ) . ' #date#';
			} else {
				$title_short = '#object_type# ' . TTi18n::gettext( 'by' ) . ' #object_employee_first_name# #object_employee_last_name# #status#.';
				$title_long = '#object_type# ' . TTi18n::gettext( 'by' ) . ' #object_employee_first_name# #object_employee_last_name# #status# ' . TTi18n::gettext( 'for' ) . ' #date#';
			}

			switch ( $this->getObjectType() ) {
				case 90: //TimeSheet
					$object_type = TTi18n::getText( 'TimeSheet' );
					$notification_object_type = 90;

					if ( $object_handler_user_obj->getId() === $user_to_obj->getId() ) {
						$notification_type = 'timesheet_verify';
					} else {
						$notification_type = 'timesheet_authorize';
					}

					if ( $notification_type == 'timesheet_authorize' && $this->getObjectHandlerObject()->getAuthorizationLevel() != 0 ) {
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=TimeSheetAuthorization&a=view&id=' . $this->getObject() . '&tab=TimeSheetVerification';
					} else {
						// TimeSheet Verification is at its final level, or notification being sent to original user, link back to original timesheet and not the authorization view.
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=TimeSheet';
					}

					$display_date = TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getEndDate() );
					$body_short = TTi18n::getText( 'Pay Period' ) . ': ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getPayPeriodObject()->getEndDate() );
					break;
				case 200: //Expense
					$object_type = TTi18n::getText( 'Expense' );
					$notification_object_type = 110;

					if ( $object_handler_user_obj->getId() === $user_to_obj->getId() ) {
						$notification_type = 'expense_verify';
					} else {
						$notification_type = 'expense_authorize';
					}

					if ( $notification_type == 'expense_authorize' && $this->getObjectHandlerObject()->getAuthorizationLevel() != 0 ) {
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=ExpenseAuthorization&a=edit&id=' . $this->getObject() . '&tab=Expense';
					} else {
						// Expense is at its final level, or notification being sent to original user, link back to original expense and not the authorization view.
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=LoginUserExpense&a=view&id=' . $this->getObject() . '&tab=Expense';
					}

					$display_date = TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getIncurredDate() );

					//Check if its a custom unit or just dollars so the message can be formatted properly for each.
					if ( is_object( $this->getObjectHandlerObject()->getExpensePolicyObject() ) && $this->getObjectHandlerObject()->getExpensePolicyObject()->getType() == 30 ) { //30=Per Unit
						$body_short = $this->getObjectHandlerObject()->getGrossAmount() . ' ' . $this->getObjectHandlerObject()->getExpensePolicyObject()->getUnitName() . ' ' . TTi18n::getText( 'incurred on' ) . ': ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getIncurredDate() );
					} else {
						$body_short = '$' . $this->getObjectHandlerObject()->getGrossAmount() . ' ' . TTi18n::getText( 'incurred on' ) . ': ' . TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getIncurredDate() );
					}

					//Add the reimbursable amount so its clear to the end-user.
					$body_short .= "\n". TTi18n::getText( 'Reimbursable Amount' ) .': $'. $this->getObjectHandlerObject()->getReimburseAmount();

					break;
				case 50: //Requests
				case 1010:
				case 1020:
				case 1030:
				case 1040:
				case 1100:
					$object_type = TTi18n::getText( 'Request' );
					$notification_object_type = 50;

					// If request belongs to user being notified set type as request else request_authorize for supervisors.
					if ( $object_handler_user_obj->getId() === $user_to_obj->getId() ) {
						$notification_type = 'request';
					} else {
						$notification_type = 'request_authorize';
					}

					if ( $notification_type == 'request_authorize' && $this->getObjectHandlerObject()->getAuthorizationLevel() != 0 ) {
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=RequestAuthorization&a=view&id=' . $this->getObject() . '&tab=Request';
					} else {
						// Request is at its final level, or notification being sent to original user, link back to original request and not the authorization view.
						$link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=Request&a=view&id=' . $this->getObject() . '&tab=Request';
					}


					$request_schedule_obj = $this->getObjectHandlerObject()->getRequestSchedule( true ); //Return object.
					if ( is_object( $request_schedule_obj ) && TTDate::getMiddleDayEpoch( $request_schedule_obj->getStartDate() ) != TTDate::getMiddleDayEpoch( $request_schedule_obj->getEndDate() ) ) {
						$display_date = TTDate::getDate( 'DATE', $request_schedule_obj->getStartDate() ) .' '. TTi18n::getText( 'to' ) .' '. TTDate::getDate( 'DATE', $request_schedule_obj->getEndDate() );
					} else {
						$display_date = TTDate::getDate( 'DATE', $this->getObjectHandlerObject()->getDateStamp() );
					}

					$display_type = Option::getByKey( $this->getObjectHandlerObject()->getType(), Misc::trimSortPrefix( $this->getObjectHandlerObject()->getOptions( 'type' ) ) );

					//Add the absence policy to the display if its an absence request, so the supervisor knows what policy it is before having to login.
					if ( is_object( $request_schedule_obj ) && $request_schedule_obj->getStatus() == 20 && is_object( $request_schedule_obj->getAbsencePolicyObject() ) ) { //20=Absence
						$display_type .= ' ['. $request_schedule_obj->getAbsencePolicyObject()->getName() .']';
					}

					$body_short = $display_type . ' ' . TTi18n::getText( 'for' ) . ' ' . $display_date;
					break;
			}

			//Define title_short/body variables here.
			$search_arr = [
					'#object_type#',
					'#object_type_long_description#',
					'#status#',
					'#date#',

					'#current_employee_first_name#',
					'#current_employee_last_name#',

					'#object_employee_first_name#',
					'#object_employee_last_name#',
					'#object_employee_default_branch#',
					'#object_employee_default_department#',
					'#object_employee_group#',
					'#object_employee_title#',

					'#company_name#',
					'#url#',
			];

			$replace_arr = Misc::escapeHTML( [
					$object_type,
					$body_short,
					$status_label,
					$display_date,

					$u_obj->getFirstName(),
					$u_obj->getLastName(),

					$object_handler_user_obj->getFirstName(),
					$object_handler_user_obj->getLastName(),
					( is_object( $object_handler_user_obj->getDefaultBranchObject() ) ) ? $object_handler_user_obj->getDefaultBranchObject()->getName() : null,
					( is_object( $object_handler_user_obj->getDefaultDepartmentObject() ) ) ? $object_handler_user_obj->getDefaultDepartmentObject()->getName() : null,
					( is_object( $object_handler_user_obj->getGroupObject() ) ) ? $object_handler_user_obj->getGroupObject()->getName() : null,
					( is_object( $object_handler_user_obj->getTitleObject() ) ) ? $object_handler_user_obj->getTitleObject()->getName() : null,

					( is_object( $object_handler_user_obj->getCompanyObject() ) ) ? $object_handler_user_obj->getCompanyObject()->getName() : null,
					( Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() ),
			] );

			$title_short = str_replace( $search_arr, $replace_arr, $title_short );
			$title_long = str_replace( $search_arr, $replace_arr, $title_long );
			$body_short = str_replace( $search_arr, $replace_arr, $body_short );

			//$body_long = TTi18n::gettext( '*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*' ) . "\n\n";
			$body_long = '#object_type# '. TTi18n::gettext( 'by' ) .' #object_employee_first_name# #object_employee_last_name# #status#'. "\n";
			$body_long .= ( $replace_arr[1] != '' ) ? '#object_type_long_description#' . "\n" : null;
			$body_long .= "\n";
			$body_long .= ( $replace_arr[8] != '' ) ? TTi18n::gettext( 'Default Branch' ) . ': #object_employee_default_branch#' . "\n" : null;
			$body_long .= ( $replace_arr[9] != '' ) ? TTi18n::gettext( 'Default Department' ) . ': #object_employee_default_department#' . "\n" : null;
			$body_long .= ( $replace_arr[10] != '' ) ? TTi18n::gettext( 'Group' ) . ': #object_employee_group#' . "\n" : null;
			$body_long .= ( $replace_arr[11] != '' ) ? TTi18n::gettext( 'Title' ) . ': #object_employee_title#' . "\n" : null;
			$body_long .= TTi18n::gettext( 'Link' ) . ': <a href="#url#">' . APPLICATION_NAME . ' ' . TTi18n::gettext( 'Sign In' ) . '</a>' . "\n";

			$body_long .= NotificationFactory::addEmailFooter( ( ( is_object( $object_handler_user_obj->getCompanyObject() ) ) ? $object_handler_user_obj->getCompanyObject()->getName() : null ) );
			$body_long = '<html><body><pre>' . str_replace( $search_arr, $replace_arr, $body_long ) . '</pre></body></html>';

			$notification_data = [
					'object_id'      => $this->getObject(),
					'user_id'        => $user_id,
					'type_id'        => $notification_type,
					'object_type_id' => $notification_object_type,
					'title_short'    => $title_short,
					'title_long'     => $title_long,
					'body_short'     => $body_short,
					'body_long_html' => $body_long, //For emails
					'payload'        => [ 'link' => $link ],
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

	function markRelatedNotificationsAsRead() {
		$request_object_type_to_notification_object_type_map = [
			90 => 90, //'timesheet',
			200  => 110, //'expense',
			//50 => 'request', //request_other
			1010 => 50, //'request_punch',
			1020 => 50, //'request_punch_adjust',
			1030 => 50, //'request_absence',
			1040 => 50, //'request_schedule',
			1100 => 50, //'request_other',
		];

		if ( isset( $request_object_type_to_notification_object_type_map[$this->getObjectType()] ) ) {
			$notification_object_type_id = $request_object_type_to_notification_object_type_map[$this->getObjectType()];
			if ( $this->isFinalAuthorization() == true ) {
				//If its a final authorization, mark notification as read for *all* notifications/users at any level.
				NotificationFactory::updateStatusByObjectIdAndObjectTypeId( $notification_object_type_id, $this->getObject() ); //Mark any notifications linked to these exceptions as read.
			} else {
				//If its a superior at a low level, only mark notifications as read for any other superior at the same level.
				$hierarchy_current_level_user_ids = $this->getHierarchyCurrentLevelArray();
				NotificationFactory::updateStatusByObjectIdAndObjectTypeId( $notification_object_type_id, $this->getObject(), $hierarchy_current_level_user_ids ); //Mark any notifications linked to these exceptions as read.
			}
		}

		return true;
	}

	/**
	 * Used by Request/TimeSheetVerification/Expense when initially saving a record to notify the immediate superiors, rather than using the message notification.
	 * @param string $current_user_id UUID
	 * @param int $object_type_id
	 * @param string $object_id       UUID
	 * @return bool
	 */
	static function sendNotificationAuthorizationOnInitialObjectSave( $current_user_id, $object_type_id, $object_id ) {
		$authorization_obj = TTNew( 'AuthorizationFactory' ); /** @var AuthorizationFactory $authorization_obj */
		$authorization_obj->setObjectType( $object_type_id );
		$authorization_obj->setObject( $object_id );
		$authorization_obj->setCurrentUser( $current_user_id );
		$authorization_obj->setAuthorized( true );
		$authorization_obj->sendNotificationAuthorization();

		return true;
	}

	/**
	 * @return bool
	 */
	function isUnique() {
		$ph = [
				'object_type' => (int)$this->getObjectType(),
				'object_id'   => TTUUID::castUUID( $this->getObject() ),
				'authorized'  => (int)$this->getAuthorized(),
				'created_by'  => TTUUID::castUUID( $this->getCreatedBy() ),
		];

		$query = 'select id from ' . $this->getTable() . ' where object_type_id = ? AND object_id = ? AND authorized = ? AND created_by = ?';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Authorization: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

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
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Object Type
		$this->Validator->inArrayKey( 'object_type',
									  $this->getObjectType(),
									  TTi18n::gettext( 'Object Type is invalid' ),
									  $this->getOptions( 'object_type' )
		);
		// Object ID
		$this->Validator->isResultSetWithRows( 'object',
											   ( is_object( $this->getObjectHandler() ) ) ? $this->getObjectHandler()->getByID( $this->getObject() ) : false,
											   TTi18n::gettext( 'Object ID is invalid' )
		);

		//Prevent duplicate authorizations by the same person.
		// This may cause problems if the hierarchy is changed and the same superior needs to authorize the request again though?
		//   By definition this should never happen at the final authorization level, so someone higher up in the hierarchy could always drop down and authorize it during the transition.
		if ( $this->getDeleted() == false ) {
			if ( $this->Validator->getValidateOnly() == false && $this->isUnique() == false ) {
				$this->Validator->isTrue( 'object',
										  false,
										  TTi18n::gettext( 'Record has already been authorized/declined by you' ) );
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() === false
				&& $this->isFinalAuthorization() === false
				&& $this->isValidParent() === false ) {
			//FYI: This error may occur on timesheet authorization if the timesheet cannot be verified because pending requests or critical severity exceptions exist. Though it should display a proper validation message to that affect instead.
			$this->Validator->isTrue( 'parent',
									  false,
									  TTi18n::gettext( 'Employee authorizing this object is not a superior in the hierarchy that controls it' ) );

			return false;
		}

		$this->setObjectHandlerStatus();

		if ( $this->getDeleted() == false && is_object( $this->getObjectHandlerObject() ) && $this->getObjectHandlerObject()->isValid() == false ) {
			Debug::text( '  ObjectHandler Validation Failed, pass validation errors up the chain...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Debug::Text(' Calling preSave!: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->StartTransaction();

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getDeleted() == false ) {
			if ( is_object( $this->getObjectHandlerObject() ) && $this->getObjectHandlerObject()->isValid() == true ) {
				Debug::text( '  Object associated with authorization record is valid, saving...', __FILE__, __LINE__, __METHOD__, 10 );
				//Return true if object saved correctly.
				$retval = $this->getObjectHandlerObject()->Save( false );
				if ( $this->getObjectHandlerObject()->isValid() == false ) {
					Debug::text( '  Object postSave validation FAILED!', __FILE__, __LINE__, __METHOD__, 10 );
					$this->Validator->merge( $this->getObjectHandlerObject()->Validator );
				} else {
					Debug::text( '  Object postSave validation SUCCESS!', __FILE__, __LINE__, __METHOD__, 10 );
					$this->markRelatedNotificationsAsRead(); //Mark existing notifications as read before new ones are sent.
					$this->sendNotificationAuthorization();
				}

				if ( $retval === true ) {
					$this->CommitTransaction();

					return true;
				} else {
					$this->FailTransaction();
				}
			} else {
				//Always fail the transaction if we get this far.
				//This stops authorization entries from being inserted.
				$this->FailTransaction();
			}

			$this->CommitTransaction(); //preSave() starts the transaction

			return false;
		}

		$this->CommitTransaction(); //preSave() starts the transaction

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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'object_type':
							Debug::text( '  Object Type...', __FILE__, __LINE__, __METHOD__, 10 );
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
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
		if ( $this->getAuthorized() === true ) {
			$authorized = TTi18n::getText( 'True' );
		} else {
			$authorized = TTi18n::getText( 'False' );
		}

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Authorization Object Type' ) . ': ' . ucwords( str_replace( '_', ' ', Option::getByKey( $this->getObjectType(), $this->getOptions( 'object_type' ) ) ) ) . ' ' . TTi18n::getText( 'Authorized' ) . ': ' . $authorized, null, $this->getTable() );
	}
}

?>
