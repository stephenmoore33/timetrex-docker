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
 * @package Modules\Hierarchy
 */
class HierarchyLevelFactory extends Factory {
	protected $table = 'hierarchy_level';
	protected $pk_sequence_name = 'hierarchy_level_id_seq'; //PK Sequence name

	var $hierarchy_control_obj = null;
	var $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'hierarchy_control_id' )->setFunctionMap( 'HierarchyControl' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'level' )->setFunctionMap( 'Level' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted( true, true )
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'hierarchy_control_id' )->setType( 'uuid_list' )->setColumn( 'a.hierarchy_control_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIHierarchyLevel' )->setMethod( 'getHierarchyLevel' )
									->setSummary( 'Get hierarchy level records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIHierarchyLevel' )->setMethod( 'setHierarchyLevel' )
									->setSummary( 'Add or edit hierarchy level records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIHierarchyLevel' )->setMethod( 'deleteHierarchyLevel' )
									->setSummary( 'Delete hierarchy level records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIHierarchyLevel' )->setMethod( 'getHierarchyLevel' ) ),
											   ) ),
							TTSAPI::new( 'APIHierarchyLevel' )->setMethod( 'getHierarchyLevelDefaultData' )
									->setSummary( 'Get default hierarchy level data used for creating new hierarchy levels. Use this before calling setHierarchyLevel to get the correct default data.' ),
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
			case 'columns':
				$retval = [
						'-1010-level' => TTi18n::gettext( 'Level' ),
						'-1020-user'  => TTi18n::gettext( 'Superior' ),

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
						'level',
						'user',
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
				'id'                   => 'ID',
				'hierarchy_control_id' => 'HierarchyControl',
				'level'                => 'Level',
				'user_id'              => 'User',
				'deleted'              => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|null
	 */
	function getUserObject() {
		if ( is_object( $this->user_obj ) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;
			}

			return false;
		}
	}

	/**
	 * @return null
	 */
	function getHierarchyControlObject() {
		if ( is_object( $this->hierarchy_control_obj ) ) {
			return $this->hierarchy_control_obj;
		} else {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$this->hierarchy_control_obj = $hclf->getById( $this->getHierarchyControl() )->getCurrent();

			return $this->hierarchy_control_obj;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getHierarchyControl() {
		return $this->getGenericDataValue( 'hierarchy_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHierarchyControl( $value ) {
		$value = TTUUID::castUUID( $value );

		//This is a sub-class, need to support setting HierachyControlID before its created.
		return $this->setGenericDataValue( 'hierarchy_control_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getLevel() {
		return (int)$this->getGenericDataValue( 'level' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLevel( $value ) {
		$value = trim( $value );
		if ( $value <= 0 ) {
			$value = 1; //1 is the lowest level
		}
		if ( $value > 0 ) {
			return $this->setGenericDataValue( 'level', $value );
		}

		return false;
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
		$id = trim( $id );

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$hllf = TTnew( 'HierarchyLevelListFactory' ); /** @var HierarchyLevelListFactory $hllf */
		//$hulf = TTnew( 'HierarchyUserListFactory' );

		if ( $this->getHierarchyControl() == false ) {
			return false;
		}

		//Get user object so we can get the users full name to display as an error message.
		$ulf->getById( $id );

		//Shouldn't allow the same superior to be assigned at multiple levels. Can't check that properly here though, must be done at the Hierarchy Control level?


		//Don't allow a level to be set without a superior assigned to it.
		//$id == 0
		if (
		(
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $id ),
													   TTi18n::gettext( 'No superior defined for level' ) . ' (' . (int)$this->getLevel() . ')'
				)
				&&
				/*
				//Allow superiors to be assigned as subordinates in the same hierarchy to make it easier to administer hierarchies
				//that have superiors sharing responsibility.
				//For example Super1 and Super2 look after 10 subordinates as well as each other. This would require 3 hierarchies normally,
				//but if we allow Super1 and Super2 to be subordinates in the same hierarchy, it can be done with a single hierarchy.
				//The key with this though is to have Permission->getPermissionChildren() *not* return the current user, even if they are a subordinates,
				//as that could cause a conflict with view_own and view_child permissions (as a child would imply view_own)
				(
				$ulf->getRecordCount() > 0
				AND
				$this->Validator->isNotResultSetWithRows(	'user',
															$hulf->getByHierarchyControlAndUserId( $this->getHierarchyControl(), $id ),
															$ulf->getCurrent()->getFullName() .' '. TTi18n::gettext('is assigned as both a superior and subordinate')
															)
				)
				AND
				*/
				(
						$this->Validator->hasError( 'user' ) == false
						&&
						$this->Validator->isNotResultSetWithRows( 'user',
																  $hllf->getByHierarchyControlIdAndUserIdAndExcludeId( $this->getHierarchyControl(), $id, $this->getID() ),
																  $ulf->getCurrent()->getFullName() . ' ' . TTi18n::gettext( 'is already assigned as a superior' )
						)

				)
		)
		) {
			$this->setGenericDataValue( 'user_id', $id );

			return true;
		}

		return false;
	}

	/**
	 * Remaps raw hierarchy_levels so they always start from 1, and have no gaps in them.
	 * Also remove any duplicate superiors from the hierarchy.
	 * @param $hierarchy_level_data
	 * @return bool|array
	 */
	static function ReMapHierarchyLevels( $hierarchy_level_data ) {
		if ( !is_array( $hierarchy_level_data ) ) {
			return false;
		}

		$remapped_hierarchy_levels = false;
		$tmp_hierarchy_levels = [];
		foreach ( $hierarchy_level_data as $hierarchy_level ) {
			$tmp_hierarchy_levels[] = $hierarchy_level['level'];
		}
		sort( $tmp_hierarchy_levels );

		$level = 0;
		$prev_level = false;
		foreach ( $tmp_hierarchy_levels as $hierarchy_level ) {
			if ( $prev_level != $hierarchy_level ) {
				$level++;
			}

			$remapped_hierarchy_levels[$hierarchy_level] = $level;

			$prev_level = $hierarchy_level;
		}

		return $remapped_hierarchy_levels;
	}

	/**
	 * Takes a hierarchy level map array and converts it to a SQL where clause.
	 * @param $hierarchy_level_map
	 * @param string $object_table
	 * @param string $hierarchy_user_table
	 * @param null $type_id_column
	 * @return bool|string
	 */
	static function convertHierarchyLevelMapToSQL( $hierarchy_level_map, $object_table = 'a.', $hierarchy_user_table = 'z.', $type_id_column = null ) {
		/*
				( z.hierarchy_control_id = 469 AND a.authorization_level = 1 )
					OR ( z.hierarchy_control_id = 471 AND a.authorization_level = 2 )
					OR ( z.hierarchy_control_id = 470 AND a.authorization_level = 3 )

				OR

				( z.hierarchy_control_id = 469 AND a.authorization_level = 1 AND a.type_id in (10, 20, 30) )
					OR ( z.hierarchy_control_id = 471 AND a.authorization_level = 2 AND a.type_id in (10) )
					OR ( z.hierarchy_control_id = 470 AND a.authorization_level = 3 AND a.type_id in (100) )
		*/

		if ( is_array( $hierarchy_level_map ) ) {
			$rf = new RequestFactory();
			$clause_arr = [];
			foreach ( $hierarchy_level_map as $hierarchy_data ) {
				if ( !isset( $hierarchy_data['hierarchy_control_id'] ) || !isset( $hierarchy_data['level'] ) ) {
					continue;
				}

				if ( isset( $hierarchy_data['last_level'] ) && $hierarchy_data['last_level'] == true ) {
					$operator = ' >= ';
				} else {
					$operator = ' = ';
				}

				$object_type_clause = null;
				if ( $type_id_column != '' && isset( $hierarchy_data['object_type_id'] ) && count( $hierarchy_data['object_type_id'] ) > 0 ) {
					$hierarchy_data['object_type_id'] = $rf->getTypeIdFromHierarchyTypeId( $hierarchy_data['object_type_id'] );
					$object_type_clause = ' AND ' . $type_id_column . ' in (' . implode( ',', $hierarchy_data['object_type_id'] ) . ')';
				}
				$clause_arr[] = '( ' . $hierarchy_user_table . 'hierarchy_control_id = \'' . TTUUID::castUUID( $hierarchy_data['hierarchy_control_id'] ) . '\' AND ' . $object_table . 'authorization_level ' . $operator . ' ' . (int)$hierarchy_data['level'] . $object_type_clause . ' )';
			}

			if ( !empty( $clause_arr ) ) {
				$retval = '(' . implode( ' OR ', $clause_arr ) . ')'; //Wrap in brackets otherwise it can break queries due to changing logic on clauses appended after.
			} else {
				$retval = null;
			}

			//Debug::Text(' Hierarchy Filter SQL: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return false;
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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
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
		$u_obj = $this->getUserObject();
		if ( is_object( $u_obj ) ) {
			return TTLog::addEntry( $this->getHierarchyControl(), $log_action, TTi18n::getText( 'Superior' ) . ': ' . $u_obj->getFullName() . ' ' . TTi18n::getText( 'Level' ) . ': ' . $this->getLevel(), null, $this->getTable(), $this );
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
		// Hierarchy Control
		$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
		$this->Validator->isResultSetWithRows( 'hierarchy_control_id',
											   $hclf->getByID( $this->getHierarchyControl() ),
											   TTi18n::gettext( 'Invalid Hierarchy Control' )
		);
		// Level
		if ( $this->getLevel() !== false && $this->getLevel() > 0 ) {
			$this->Validator->isNumeric( 'level',
										 $this->getLevel(),
										 TTi18n::gettext( 'Level is invalid' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//


		if ( $this->getUser() === false || TTUUID::isUUID( $this->getUser() ) == false ) {
			$this->Validator->isTrue( 'user_id',
									  false,
									  TTi18n::gettext( 'A superior must be specified' )
			);
		}

		if ( $this->getDeleted() == true ) {
			return true;
		}

		return true;
	}

}

?>
