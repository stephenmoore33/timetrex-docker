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
/** @noinspection PhpUndefinedFunctionInspection */

/**
 * @package Core
 */
abstract class Factory {
	//**IMPORTANT** These must all be reset in FactoryListIterator->__construct()
	public $data = [];
	public $old_data = []; //Used for detailed audit log.
	public $tmp_data = [];

	protected $enable_system_log_detail = true;

	protected $progress_bar_obj = null;
	protected $api_message_id = null;

	public $Validator = null;
	public $validate_only = false; //Used by the API to ignore certain validation checks if we are doing validation only.
	public $is_valid = false;     //Flag that determines if the data is valid since it was last changed or not.
	//**IMPORTANT** These must all be reset in FactoryListIterator->__construct()

	/**
	 * @var bool
	 */
	public $ignore_column_list;

	/**
	 * @var Cache_Lite_Function|Cache_Lite_Output
	 */
	public $cache = null;
	public $db = null;
	public $rs = null;

	public $is_new = null;

	/**
	 * Factory constructor.
	 */
	function __construct() {
		global $db, $cache;

		$this->db = $db;
		$this->cache = $cache;
		$this->Validator = new Validator();

		//Callback to the child constructor method.
		//  **IMPORTANT** -- This is required for overrides handled by external applications (ie: website)
		if ( method_exists( $this, 'childConstruct' ) ) {
			$this->childConstruct();
		}

		return true;
	}

	/**
	 * Used for updating progress bar for API calls.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		if ( $id != '' ) {
			$this->api_message_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setProgressBarObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->progress_bar_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * Check if the remote client is our main UI app controlled by us, vs. a user utilizing the API for example.
	 *   This is mostly used to determine if we can return columns that are resource intensive to compute when obtaining a list. (ie: Users, Jobs)
	 * @return bool
	 */
	function isClientFriendly() {
		global $authentication;
		if ( is_object( $authentication ) && $authentication->getClientID() == 'browser-timetrex' ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow method to pre-populate/overwrite the cache if needed.
	 * @param object $obj
	 * @param string $variable
	 * @return bool
	 */
	function setGenericObject( $obj, $variable ) {
		$this->$variable = $obj;

		return true;
	}

	/**
	 * Generic function to return and cache class objects
	 * ListFactory, ListFactoryMethod, Variable, ID, IDMethod
	 * @param string $list_factory
	 * @param string|int $id UUID
	 * @param string $variable
	 * @param string $list_factory_method
	 * @param string $id_method
	 * @return object|bool
	 */
	function getGenericObject( $list_factory, $id, $variable, $list_factory_method = 'getById', $id_method = 'getID' ) {
		if ( isset( $this->$variable ) && is_object( $this->$variable ) && $id == $this->$variable->$id_method() ) { //Make sure we always compare that the object IDs match.
			return $this->$variable;
		} else {
			$lf = TTnew( $list_factory );
			$lf->$list_factory_method( $id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->$variable = $lf->getCurrent();

				return $this->$variable;
			}

			return false;
		}
	}

	/**
	 * Generic function to return and cache CompanyGenericMap data, this greatly improves performance of CalculatePolicy when many policies exist.
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param string $id         UUID
	 * @param string $variable
	 * @return mixed
	 */
	function getCompanyGenericMapData( $company_id, $object_type_id, $id, $variable ) {
		if ( isset( $this->$variable ) ) {
			$tmp = &$this->$variable; //Works around a PHP issues where $this->$variable[$id] cause a fatal error on unknown string offset
		} else {
			$tmp = [];
		}

		if ( TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID()
				&& isset( $tmp[$id] ) ) {
			return $tmp[$id];
		} else {
			$tmp[$id] = CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $id );

			return $tmp[$id];
		}
	}

	/**
	 * Generic getter/setter functions that should be used when Validation code is moved from get/set functions to Validate() function.
	 * @param string $name
	 * @param null $cast
	 * @return bool|mixed
	 */
	function getGenericDataValue( $name, $cast = null ) {
		//FIXME: This won't pass through NULL values from the DB, because isset() checks for NULL.
		//Use array_key_exists(), instead, then return whatever it has, including NULL. Be sure to update getGenericTempDataValue() too.
		//  However we use "$value !== false" and "$this->getTaintedDate() !== false" all over the place for checking if mass edit is being performed. These would mostly need to be changed to !== null checks, but it would be quite invasive.
		if ( isset( $this->data[$name] ) ) {
//			if ( $cast != '' ) {
//				$this->castGenericDataValue( $this->data[$name], $cast );
//			}

			return $this->data[$name];
		}

		return false;
	}

	/**
	 * Sets a generic data value for a given name.
	 * This method updates the internal data array with the provided data value associated with the given name.
	 * It also marks the current state as invalid to force revalidation of the data.
	 * Casting of the data value is currently commented out, awaiting implementation of SQL schema-based casting.
	 *
	 * @param string $name The name of the data value to set.
	 * @param mixed $data The data value to set.
	 * @param null|string $cast The type to cast the data value to (not currently used).
	 * @return bool Always returns true.
	 */
	function setGenericDataValue( $name, $data, $cast = null ) {
		$this->is_valid = false; //Force revalidation when data is changed.

//		if ( $cast != '' ) {
//			$this->castGenericDataValue( $data, $cast );
//		}

		$this->data[$name] = $data;

		return true;
	}

	/**
	 * Generic casting function that all set/get*() functions should pass through.
	 * However for now lets wait until we have meta data from SQL schema so we can pass those datatypes directly into this.
	 * @param $value mixed
	 * @param $cast  string
	 * @return mixed
	 */
	function castGenericDataValue( &$value, $cast ) {
		if ( $cast != '' ) {
			$cast = strtolower( $cast );

			switch ( $cast ) {
				case 'uuid':
					$value = TTUUID::castUUID( $value );
					break;
				case 'uuid+zero':
					$value = TTUUID::castUUID( $value );
					if ( $value == '' ) {
						$value = TTUUID::getZeroID();
					}
					break;
				default:
					if ( settype( $value, $cast ) == false ) {
						Debug::Arr( $value, 'ERROR: Unable to cast variable to: ' . $cast, __FILE__, __LINE__, __METHOD__, 10 );
					}
					break;
			}
		}

		return $value;
	}

	/**
	 * Generic getter/setter functions that should be used when Validation code is moved from get/set functions to Validate() function.
	 * @param string $name
	 * @return bool|mixed
	 */
	function getGenericTempDataValue( $name ) {
		if ( isset( $this->tmp_data[$name] ) ) {
			return $this->tmp_data[$name];
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param mixed $data
	 * @return bool
	 */
	function setGenericTempDataValue( $name, $data ) {
		$this->is_valid = false; //Force revalidation when data is changed.
		$this->tmp_data[$name] = $data;

		return true;
	}

	/**
	 * Decodes JSON columns that are not the 'other_json' column.
	 * @param $column
	 * @return bool
	 */
	function decodeJSONColumn( $column ) {
		if ( isset( $this->data[$column] ) && Misc::isJSON( $this->data[$column] ) == true ) {
			$this->data[$column] = json_decode( $this->data[$column], true );
			return true;
		}

		return false;
	}

	/**
	 * Encode any JSON columns that are not 'other_json' column. This should only be done in Save() after all validation has occurred already and we are just about to commit it to the DB.
	 * @return bool
	 */
	function encodeJSONColumns() {
		if ( isset( $this->json_columns ) && is_array( $this->json_columns ) ) {
			foreach ( $this->json_columns as $column ) {
				//Check value is not already JSON to help prevent double encoding when just deleting a record without calling get*() which would trigger decode.
				if ( isset( $this->data[$column] ) && Misc::isJSON( $this->data[$column] ) == false ) {
					$this->data[$column] = json_encode( $this->data[$column] );
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Decode generic 'other_json' column data.
	 * @return bool
	 */
	function decodeGenericJSONData() {
		if ( isset( $this->data['other_json'] ) && !is_array( $this->data['other_json'] ) ) {
			$this->data['other_json'] = json_decode( $this->data['other_json'], true );
			return true;
		}

		return false;
	}

	/**
	 * Encode generic 'other_json' column data. This should only be done in Save() after all validation has occurred already and we are just about to commit it to the DB.
	 * @return bool
	 */
	function encodeGenericJSONData() {
		if ( isset( $this->data['other_json'] ) && is_array( $this->data['other_json'] ) ) {
			$this->data['other_json'] = json_encode( $this->data['other_json'] );
			return true;
		}

		return false;
	}

	/**
	 * Generic getter/setter functions for handling generic JSON data in 'other_json' SQL column.
	 * @param string $name
	 * @param null $cast
	 * @return bool|mixed
	 */
	function getGenericJSONDataValue( $name, $cast = null ) {
		//FIXME: This won't pass through NULL values from the DB, because isset() checks for NULL.
		//Use array_key_exists(), instead, then return whatever it has, including NULL. Be sure to update getGenericTempDataValue() too.

		$this->decodeGenericJSONData(); //Decode JSON data if it hasn't been already. Do this here as a 'lazy' decode rather in something like FactoryListIterator that would occur for every row even if such JSON data does not exist.
		if ( isset( $this->data['other_json'][$name] ) ) {
//			if ( $cast != '' ) {
//				$this->castGenericDataValue( $this->data[$name], $cast );
//			}

			return $this->data['other_json'][$name];
		}

		return false;
	}

	/**
	 * Generic getter/setter functions for handling generic JSON data in 'other_json' SQL column.
	 * @param string $name
	 * @param mixed $data
	 * @param null $cast
	 * @return bool
	 */
	function setGenericJSONDataValue( $name, $data, $cast = null ) {
		$this->is_valid = false; //Force revalidation when data is changed.

//		if ( $cast != '' ) {
//			$this->castGenericDataValue( $data, $cast );
//		}

		$this->decodeGenericJSONData(); //Decode JSON data if it hasn't been already. In cases where a get*() is not called, and just a set*() before the JSON data is decoded.
		$this->data['other_json'][$name] = $data;

		return true;
	}


	/**
	 * Parses custom fields from an array and applies permissions and formatting.
	 * This function processes an array of data, identifying custom fields by their key prefix,
	 * and applies necessary permission checks and formatting. Custom fields that are strings
	 * are trimmed, and only fields with the correct permissions are retained.
	 *
	 * @param array $data Associative array containing the data to be parsed.
	 * @return array Processed array with custom fields that have passed permission checks and formatting.
	 */
	function parseCustomFieldsFromArray( $data ) {
		$permission = false;

		//Certain custom fields depending on table have specific edit permissions that need to be checked
		if ( $this->getCustomFieldTableName() == 'punch_control' ) {
			global $current_user;
			if ( isset( $current_user ) && is_object( $current_user ) ) {
				$permission = new Permission();
			}
		}

		if ( is_array( $data ) ) {
			$custom_field = [];
			foreach ( $data as $key => $value ) {
				if ( strpos( $key, 'custom_field-' ) !== false ) {

					$custom_field_id = str_replace( 'custom_field-', '', $key );

					//Check if custom fields ends with _id. The _id version contains the actual value whereas the
					//none _id version contains the display value. Both these versions can be sent to the API, and we need
					//to ensure we only get the data of the _id version.
					if ( substr( $key, -3 ) == '_id' ) {
						$custom_field_id = str_replace( '_id', '', $custom_field_id );
					} else {
						if ( isset( $custom_field[$custom_field_id] ) ) {
							continue; //We already parsed the _id version of this field, so skip this one.
						}
					}

					if ( $permission == false || $permission->Check( 'punch', 'edit_custom_field-' . $custom_field_id, $current_user->getId(), $current_user->getCompany() ) ) {
						$custom_field[$custom_field_id] = is_string( $value ) ? trim( $value ) : $value; //If custom field is a string, trim it to avoid validating spaces etc.
					} else if ( $this->getCustomFieldTableName() == 'punch_control' ) {
						//Because punch custom fields can have additional permissions, a scenario could happen where a
						//custom field is required but the user does not have permission to edit it. Therefore, we need to
						//disable the required check similar to how APIClientStationUnAuthenticated does it.
						$this->setEnableRequiredFieldCheck( false );
						Debug::Text( 'WARNING: User does not have permission to edit custom field: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $data[$key] );
				}
			}

			if ( $this->getCustomFieldTableName() == 'punch_control' ) {
				//Custom fields could be lost if not all fields were sent to the API. This was due to fact that only the
				//provided custom fields would be saved and stored in JSON effectively overwriting the current custom fields as they all share the same database field.
				//An example is if there are 3 custom fields on a punch and then a user who has permission to edit only 1 custom field edits the punch, which would result in 2 custom fields getting lost. This could happen in any similar situation where not all fields are sent to the API.
				$custom_field_old = json_decode( $this->getGenericOldDataValue( 'custom_field' ), true );

				if ( is_array( $custom_field_old ) ) {
					$custom_field += $custom_field_old; //The "+" will only merge in old custom fields if the custom field was not provided to prevent custom from being lost.
				}
			}

			if ( count( $custom_field ) > 0 ) {
				$this->setCustomFields( $custom_field );
			}
		}

		return $data;
	}

	/**
	 * Decodes the JSON-encoded 'custom_field' data if it is set and not already an array.
	 * This function is used to ensure that the 'custom_field' data is in a usable array format
	 * for further processing by other functions.
	 *
	 * @return bool Returns true if 'custom_field' data exists and is decoded, false otherwise.
	 */
	function decodeCustomFields() {
		if ( isset( $this->data['custom_field'] ) ) {
			if ( !is_array( $this->data['custom_field'] ) ) {
				$this->data['custom_field'] = json_decode( $this->data['custom_field'], true );
			}

			return true; //Return true whenever custom_field data exists, so other functions know if they need to deal with data further.
		}

		return false; //Return false when there is no custom_field data, so other functions know they don't have to deal with it any further.
	}

	/**
	 * Encodes the 'custom_field' data to a JSON string if it is an array.
	 * This function is used to convert the 'custom_field' data from an array to a JSON string
	 * for storage or further processing. If encoding is successful or 'custom_field' data already exists,
	 * the function returns true. If 'custom_field' data is not set, it returns false.
	 *
	 * @return bool Returns true if 'custom_field' data exists and is encoded, or if it already exists as a non-array; false if 'custom_field' data is not set.
	 */
	function encodeCustomFields() {
		if ( isset( $this->data['custom_field'] ) ) {
			if ( is_array( $this->data['custom_field'] ) ) {
				$this->data['custom_field'] = json_encode( $this->data['custom_field'] );
			}

			return true; //Return true whenever custom_field data exists, so other functions know if they need to deal with data further.
		}

		return false; //Return false when there is no custom_field data, so other functions know they don't have to deal with it any further.
	}

	/**
	 * Sets the 'custom_field' data for the current object.
	 * This method assigns the provided data to the 'custom_field' key within the object's data array.
	 * It also marks the object's data as invalid to trigger revalidation before any further processing.
	 *
	 * @param array $data The custom field data to be set.
	 * @return bool Always returns true to indicate the data was set.
	 */
	function setCustomFields( $data ) {
		$this->is_valid = false; //Force revalidation when data is changed.

		$this->data['custom_field'] = $data;

		return true;
	}

	/**
	 * Retrieves the table name associated with custom fields for the current object.
	 * If the object is related to 'punch' data, it redirects the table name to 'punch_control'.
	 * This is used to determine where custom field data is stored or should be retrieved from.
	 *
	 * @return string The name of the table where custom field data is stored.
	 */
	function getCustomFieldTableName() {
		$table_name = $this->getTable( true );
		if ( $table_name == 'punch' ) { //Punch custom fields are stored in the punch_control table.
			$table_name = 'punch_control';
		}

		return $table_name;
	}

	/**
	 * Retrieves a list of custom field data associated with a specific object.
	 * This method is designed to cache the custom field data in memory to avoid repeated database queries.
	 * It uses a combination of the company ID and table name to generate a unique cache ID for storing the data.
	 *
	 * @param string $company_id The UUID of the company to which the object belongs.
	 * @param string|null $table_name The name of the table associated with the custom fields. If null, it will be determined by getCustomFieldTableName().
	 * @return array An associative array of custom field data, with custom field IDs as keys and their corresponding data as values.
	 * @throws ReflectionException If there is an error instantiating the CustomFieldListFactory.
	 */
	function getCustomFieldsDataForObject( $company_id, $table_name = null ) {
		if ( $table_name == null ) {
			$table_name = $this->getCustomFieldTableName();
		}

		if ( $company_id == '' ) {
			return [];
		}

		if ( $table_name == '' ) {
			return [];
		}

		$cache_id = $company_id . $table_name;

		global $__custom_field_data_cache;
		if ( isset( $__custom_field_data_cache[$cache_id] ) ) {
			return $__custom_field_data_cache[$cache_id];
		} else {
			$retarr = [];

			$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
			$cflf->getByCompanyIdAndParentTableAndEnabled( $company_id, $table_name );
			if ( $cflf->getRecordCount() > 0 ) {
				Debug::Text( 'Getting data for custom fields: ' . $cflf->getRecordCount() . ' custom field data...', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $cflf as $cf_obj ) { /** @var CustomFieldFactory $cf_obj */
					$retarr[$cf_obj->getId()] = [ 'custom_field_id' => $cf_obj->getPrefixedCustomFieldID(), 'type_id' => $cf_obj->getType(), 'name' => $cf_obj->getName(), 'default_value' => $cf_obj->getDefaultValue(), 'meta_data' => $cf_obj->getCustomFieldMetaData() ];
				}
			} else {
				Debug::Text( 'No custom fields for Company: '. $company_id .' Table: ' . $table_name, __FILE__, __LINE__, __METHOD__, 10 );
			}

			$__custom_field_data_cache[$cache_id] = $retarr;

			return $retarr;
		}
	}

	/**
	 * Retrieves a custom field's data by its UUID.
	 * This function looks up the custom field in the decoded data array and returns it if found.
	 *
	 * @param string $id The UUID of the custom field to retrieve.
	 * @return mixed The data of the custom field if found, or false otherwise.
	 */
	function getCustomField( $id ) {
		$this->decodeCustomFields(); //Must be decoded to an array first.

		if ( isset( $this->data['custom_field'][$id] ) ) {
			return $this->data['custom_field'][$id];
		}

		return false;
	}

	/**
	 * Sets the value of a custom field identified by its UUID.
	 * This function updates the custom field value in the data array and marks the object state as dirty for revalidation.
	 *
	 * @param mixed $value The value to be set for the custom field.
	 * @param string $id The UUID of the custom field to be updated.
	 * @return bool Returns true if the ID is not empty and the value is set, false otherwise.
	 */
	function setCustomField( $value, $id ) {
		if ( $id != '' ) {
			$this->decodeCustomFields(); //Must be decoded to an array first, so we can update. Encoding happens prior to save.

			$this->data['custom_field'][$id] = $value;

			return true;
		}

		return false;
	}

	/**
	 * Determines if custom fields are included in the $include_columns array.
	 * This check is used to decide whether to query the database for custom fields.
	 *
	 * @param array|null $include_columns The columns to include, where 'custom_field' key indicates custom fields.
	 * @return bool True if custom fields are included or if $include_columns is null or empty, false otherwise.
	 */
	function isCustomFieldsIncluded( $include_columns = null ) {
		if ( is_array( $include_columns ) && !empty( $include_columns ) && !isset( $include_columns['custom_field'] ) ) {
			$custom_fields_included = false;
			foreach( $include_columns as $column => $value ) {
				if ( strpos( $column, 'custom_field-') !== false ) {
					//Debug::Text( 'Custom fields included, retrieving...', __FILE__, __LINE__, __METHOD__, 10 );
					$custom_fields_included = true;
					break;
				}
			}
		} else {
			//Debug::Text( 'All fields included, retrieving custom fields...', __FILE__, __LINE__, __METHOD__, 10 );
			$custom_fields_included = true;
		}

		return $custom_fields_included;
	}

	/**
	 * Retrieves custom fields for a given company and merges them into the provided data array.
	 * Custom fields are only included if specified in the $include_columns parameter or if it is null.
	 *
	 * @param string $company_id The UUID of the company to retrieve custom fields for.
	 * @param array $data The data array to merge the custom fields into.
	 * @param array|null $include_columns Optional array specifying which custom fields to include. If null, all custom fields are included.
	 * @return array The data array merged with the custom fields.
	 */
	function getCustomFields( $company_id, $data, $include_columns = null ) {
		//Performance optimization. Check that at least one custom field is specified in the include_columns.
		$custom_fields_included = $this->isCustomFieldsIncluded( $include_columns );
		if ( $custom_fields_included == false ) {
			return $data;
		}

		$custom_field_data_arr = $this->getCustomFieldsDataForObject( $company_id );
		if ( count( $custom_field_data_arr ) > 0 ) {
			$this->decodeCustomFields();

			foreach ( $custom_field_data_arr as $custom_field_id => $custom_field_data ) {
				//
				//NOTE: This is called for every row, so it has to be highly optimized.
				//

				//Check if custom field is a requested column.
				if ( $include_columns == null || isset( $include_columns['custom_field'] ) || isset( $include_columns[$custom_field_data['custom_field_id']] ) || isset( $include_columns[$custom_field_data['custom_field_id'] . '_id'] ) ) { //check for _id also

					if ( !isset( $cf ) ) {
						$cf = TTnew( 'CustomFieldFactory' ); /** @var CustomFieldListFactory $cf */
						$conversion_field_types = $cf->getOptions( 'conversion_field_types' );
					}

					if ( isset( $this->data['custom_field'][$custom_field_id] ) ) {
						//Data exists for this custom field, cast and return it.

						$data[$custom_field_data['custom_field_id']] = $cf->castFromSQL( $custom_field_data['type_id'], $this->data['custom_field'][$custom_field_id], $custom_field_data['meta_data'], true );
						//Custom field is a conversion field and the backed _id version needs to be sent alongside the display value.
						if ( isset( $conversion_field_types[$custom_field_data['type_id']] ) ) {
							$data[$custom_field_data['custom_field_id'] . '_id'] = $cf->castFromSQL( $custom_field_data['type_id'], $this->data['custom_field'][$custom_field_id], $custom_field_data['meta_data'], false );
						}
					} else {
						//No data exists for this custom field on this record, but as it was requested return null or a default value for specific fields like checkboxes.
						if ( $custom_field_data['type_id'] == 500 ) { //Checkbox
							$data[$custom_field_data['custom_field_id']] = $cf->castFromSQL( $custom_field_data['type_id'], false, $custom_field_data['meta_data'], true );
							$data[$custom_field_data['custom_field_id'] . '_id'] = false;
						} else {
							$data[$custom_field_data['custom_field_id']] = null;
							if ( isset( $conversion_field_types[$custom_field_data['type_id']] ) ) {
								//Custom field is a conversion field and the backed _id version needs to be sent alongside the display value.
								$data[$custom_field_data['custom_field_id'] . '_id'] = null;
							}
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Retrieves custom fields data for a given company and data set.
	 * This function currently returns the data unchanged, but serves as a placeholder
	 * for future implementation of custom fields handling.
	 *
	 * @param string $company_id The unique identifier for the company.
	 * @param array $data The data to potentially include custom fields in.
	 * @param array|null $include_columns Optional array of columns to include in the custom fields data.
	 * @return array The data array, potentially augmented with custom fields.
	 */
	function _getCustomFields( $company_id, $data, $include_columns = null ) {
		return $data;
	}

	/**
	 * @param array $columns
	 * @param string $company_id
	 * @param bool $use_sort_prefix
	 * @param string|null $table_name
	 * @return array
	 */
	function getCustomFieldsColumns( $columns, $company_id, $use_sort_prefix = true, $table_name = null ) {
		if ( $company_id == null ) {
			global $current_company;
			if ( isset( $current_company ) && is_object( $current_company ) ) {
				$company_id = $current_company->getId();
			} else {
				return $columns;
			}
		}

		$custom_field_data_arr = $this->getCustomFieldsDataForObject( $company_id, ( $table_name ?? $this->getCustomFieldTableName() ) );
		if ( count( $custom_field_data_arr ) > 0 ) {
			$prefix_inc = 1900;
			foreach ( $custom_field_data_arr as $custom_field_id => $custom_field_data ) {
				if ( $use_sort_prefix == true ) {
					$columns['-' . $prefix_inc . '-' . $custom_field_data['custom_field_id']] = $custom_field_data['name'];
					$prefix_inc++;
				} else {
					$columns[$custom_field_data['custom_field_id']] = $custom_field_data['name'];
				}
			}
		}

		return $columns;
	}

	/**
	 * @param array $columns
	 * @param string $company_id
	 * @param string|null $table_name
	 * @return array
	 */
	function getCustomFieldsParseHints( $columns, $company_id, $table_name ) {
		if ( $company_id == null ) {
			global $current_company;
			if ( isset( $current_company ) && is_object( $current_company ) ) {
				$company_id = $current_company->getId();
			} else {
				return $columns;
			}
		}

		$custom_field_data_arr = $this->getCustomFieldsDataForObject( $company_id, $table_name );
		if ( count( $custom_field_data_arr ) > 0 ) {
			$cff = TTnew( 'CustomFieldFactory' );
			$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
			foreach ( $custom_field_data_arr as $custom_field_id => $custom_field_data ) {
				$custom_field_id = $cff->getPrefixedCustomFieldID() . $custom_field_id;
				if ( $custom_field_data['type_id'] == 1000 || $custom_field_data['type_id'] == 1200 ) {
					$columns[$custom_field_id] = $upf->getOptions( 'date_format' );
				} else if ( $custom_field_data['type_id'] == 1100 ) {
					$columns[$custom_field_id] = $upf->getOptions( 'time_format' );
				} else if ( $custom_field_data['type_id'] == 1300 ) {
					$columns[$custom_field_id] = $upf->getOptions( 'time_unit_format' );
				}
			}
		}

		return $columns;
	}

	/**
	 * @param $company_id
	 * @param $data
	 * @param bool $get_all_fields
	 * @return mixed
	 */
	function getCustomFieldsDefaultData( $company_id, $data, $get_all_fields = false ) {
		$custom_field_data_arr = $this->getCustomFieldsDataForObject( $company_id, $this->getCustomFieldTableName() );
		if ( count( $custom_field_data_arr ) > 0 ) {
			$cf_obj = TTnew( 'CustomFieldFactory' ); /** @var CustomFieldFactory $cf_obj */
			$conversion_field_types = $cf_obj->getOptions( 'conversion_field_types' );
			foreach ( $custom_field_data_arr as $custom_field_id => $custom_field_data ) {
				$custom_field_id = $cf_obj->getPrefixedCustomFieldID() . $custom_field_id;
				//Do not override custom field data if already exists, such as from punch control of a previous punch.
				if ( isset( $data[$custom_field_id] ) == false ) {
					$default_value = $custom_field_data['default_value'];
					if ( empty( $default_value ) == false ) {
						$data[$custom_field_id] = $cf_obj->castFromSQL( $custom_field_data['type_id'], $default_value, $custom_field_data['meta_data'], true );
						//Custom field is a conversion field and the backed _id version needs to be sent alongside the display value.
						if ( isset( $conversion_field_types[$custom_field_data['type_id']] ) ) {
							$data[$custom_field_id . '_id'] = $cf_obj->castFromSQL( $custom_field_data['type_id'], $default_value, $custom_field_data['meta_data'], false );
						}
					} else if ( $get_all_fields == true ) {
						$data[$custom_field_id] = null;
						//Custom field is a conversion field and the backed _id version needs to be sent alongside the display value.
						if ( isset( $conversion_field_types[$custom_field_data['type_id']] ) ) {
							$data[$custom_field_id . '_id'] = null;
						}
					}
				}
			}
		}

		return $data;
	}

	function validateCustomFields( $company_id, $enable_required_field_check = true ) {
		if ( $this->getDeleted() === true ) {
			return true;
		}

		if ( $company_id == null ) {
			global $current_company;
			if ( isset( $current_company ) && is_object( $current_company ) ) {
				$company_id = $current_company->getId();
			} else {
				Debug::Text( 'No Company ID found, cannot validate custom fields.', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}
		}


		//Don't bother validating custom fields if other fields have validation errors/warnings still. -- Minor performance optimization.
		if ( $this->Validator->isValid() == false ) {
			return true;
		}

		//When installer is enabled (ie: upgrading from old versions like v10 without custom fields to v16 with custom fields) we need to skip custom field checks.
		global $config_vars;
		if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) {
			return true;
		}

		if ( isset( $this->data['custom_field'] ) == true && is_string( $this->data['custom_field'] ) == true ) {
			$this->decodeCustomFields();
		}

		$permission = false;
		if ( $this->getCustomFieldTableName() == 'punch_control' ) {
			global $current_user;
			if ( isset( $current_user ) && is_object( $current_user ) ) {
				$permission = new Permission();
			}
		}

		$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
		$cflf->getByCompanyIdAndParentTableAndEnabled( $company_id, $this->getCustomFieldTableName() );
		if ( $cflf->getRecordCount() > 0 ) {
			Debug::Text( 'Validating custom field data for ' . $cflf->getRecordCount() . ' records...', __FILE__, __LINE__, __METHOD__, 10 );
			$valid_custom_fields = []; //Used as a reference to know if all custom fields sent to the API are valid or not.
			foreach ( $cflf as $cf_obj ) { /** @var CustomFieldFactory $cf_obj */
				if ( isset( $this->data['custom_field'][$cf_obj->getId()] ) || ( $cf_obj->getIsRequired() == true && $enable_required_field_check == true ) ) {
					//Make sure user has permission to edit this custom field.
					if ( $permission == false || $permission->Check( 'punch', 'edit_' . $cf_obj->getPrefixedCustomFieldID(), $current_user->getId(), $current_user->getCompany() ) == true ) {
						//We are validating first to ensure we don't cast invalid data to valid as the castToSQL is primarily designed for casting to SQL data types.
						//  Date fields are converted to ISO timestamp in castToSQL instead of epoch that the validate functions are expecting.
						//  We also do not want to cast a blank string to 0 prior to validating as the validation rules could require a minimum value > 0. A blank string is valid if the field is not required.
						$cf_obj->validateData( $this->data['custom_field'][$cf_obj->getId()] ?? '', $this->Validator );
						$this->data['custom_field'][$cf_obj->getId()] = $cf_obj->castToSQL( $cf_obj->getType(), $this->data['custom_field'][$cf_obj->getId()] ?? '' );
					} else {
						Debug::Text( ' Skipping custom field as user does not have permissions for custom field: ' . $cf_obj->getName() . ' (' . $cf_obj->getId() . ')...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  Skipping validation on custom field as was not provided and is not required ' . $cf_obj->getName() . ' (' . $cf_obj->getId() . ')...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$valid_custom_fields[$cf_obj->getId()] = true;
			}

			//Remove items from $this->data['custom_field'] array that are not in $valid_custom_fields.
			//They may have been disabled or deleted server side while the app in offline mode still has them.
			//$valid_custom_fields is an array of valid custom field ids.
			if ( isset( $this->data['custom_field'] ) && is_array( $this->data['custom_field'] ) ) {
				$this->data['custom_field'] = array_intersect_key( $this->data['custom_field'], $valid_custom_fields );
			}
		}

		return true;
	}

	/**
	 * @param string $name Gets data value from old_data array, or the original value in the database, prior to any changes currently in memory.
	 * @return bool|mixed
	 */
	function getGenericOldDataValue( $name ) {
		if ( isset( $this->old_data[$name] ) ) {
			return $this->old_data[$name];
		}

		return false;
	}

	/*
	 * Cache functions
	 */
	/**
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool|mixed
	 */
	function getCache( $cache_id, $group_id = null ) {
		if ( is_object( $this->cache ) ) {
			if ( $group_id == null ) {
				$group_id = $this->getTable( true );
			}

			//If the cache record is queued to be removed in this transaction, we can't trust the cache for that record anymore,
			//  as this transaction can be different from another transaction on another connection which could cause the cached record to be re-saved in the middle of the transaction.
			//Therefore we ignore getting or re-saving cache records that are pending removal.
			//  See removeCache() comments for more details.
			if ( $this->db->transCnt > 0 && isset( $this->cache->__transaction_remove_cache_ids[$cache_id.$group_id] ) || isset( $this->cache->__transaction_remove_cache_group_ids[$group_id] ) ) {
				Debug::text( 'NOTICE: Cache record was removed in this transaction, not getting it again!: ' . $cache_id . ' Group: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			$retval = $this->cache->get( $cache_id, $group_id );
			if ( is_object( $retval ) && get_class( $retval ) == 'PEAR_Error' ) {
				Debug::Arr( $retval, 'WARNING: Unable to read cache file, likely due to permissions or locking! Cache ID: ' . $cache_id . ' Table: ' . $this->getTable( true ) . ' File: ' . $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10 );
			} else if ( is_string( $retval ) && strpos( $retval, '====' ) === 0 ) { //Detect ADODB serialized record set so it can be properly unserialized.
				//Since the new ADOdb cache data format in serializeRS(), if we detect the old format try to remove the cache and return false.
				$this->removeCache( $cache_id, $group_id );
				return false;

				//return $this->unserializeRS( $retval );
			} else {
				global $__tt_cache_profiler;
				$__tt_cache_profiler['total_read']++;
				if ( $retval !== false ) {
					$__tt_cache_profiler['total_read_hits']++; //Used to calculate hit percent.
				}

				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param mixed $data
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool
	 */
	function saveCache( $data, $cache_id, $group_id = null ) {
		//Cache_ID can't have ':' in it, otherwise it fails on Windows.
		if ( is_object( $this->cache ) ) {
			if ( $group_id == null ) {
				$group_id = $this->getTable( true );
			}

			//If the cache record is queued to be removed in this transaction, we can't trust the cache for that record anymore,
			//  as this transaction can be different from another transaction on another connection which could cause the cached record to be re-saved in the middle of the transaction.
			//Therefore we ignore getting or re-saving cache records that are pending removal.
			//  See removeCache() comments for more details.
			if ( $this->db->transCnt > 0 && isset( $this->cache->__transaction_remove_cache_ids[$cache_id.$group_id] ) || isset( $this->cache->__transaction_remove_cache_group_ids[$group_id] ) ) {
				Debug::text( 'NOTICE: Cache record was removed in this transaction, not saving it again!: ' . $cache_id . ' Group: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
				return true;
			}

			//Check if its a ADODB record set, then serialize properly.
			if ( is_object( $data ) && strpos( get_class( $data ), 'ADORecordSet_' ) === 0 ) {
				if ( $data->RecordCount() >= 0  ) {
					$data = $this->serializeRS( $data );
				} else if ( $data->RecordCount() == -1 ) {
					//If record count is '-1', then for some reason ADODB either didn't count the rows, or it couldn't due to a transaction failure or something.
					// In either case the record set is invalid and should not be saved.
					// See TTLDAP->authenticate in the "catch" clause wrapping $ldap->getRow().
					Debug::text( 'ERROR: Record set was not counted and returned -1 rows. Cant save cache otherwise it will be invalid!: ' . $cache_id . ' Table: ' . $this->getTable( true ), __FILE__, __LINE__, __METHOD__, 10 );
					return false;
				}
			}
			$retval = $this->cache->save( $data, $cache_id, $group_id );
			if ( $this->cache->_caching == true && $retval === false ) { //If caching is disabled, save() will always return FALSE.
				//Due to locking, its common that cache files may fail writing once in a while.
				Debug::text( 'WARNING: Unable to write cache file, likely due to permissions or locking! Cache ID: ' . $cache_id . ' Table: ' . $this->getTable( true ) . ' File: ' . $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				global $__tt_cache_profiler;
				$__tt_cache_profiler['total_write']++;

				//If we are inside a transaction, collect all $cache_ids that have been saved and remove them upon transaction fail/rollback.
				//  There is still a potential race condition/problem here though, because if the transaction hasn't been committed, the data could still be visible
				//  in the cache for other processes to use. In theory this should be a very low chance of this causing issues though.
				//  **Consider**: Just disabling all saving of cache data while in transactions? Or defering cache saving to the end of the transaction?
				//                However now that we have $this->cache->__transaction_remove_cache_ids, if a cache record is removed (which should happen whenever the database is written too),
				// 				    it won't be saved again in the same transaction. This might avoid the above problem?
				if ( $this->db->transCnt > 0 ) {
					$this->cache->__transaction_cache_ids[$cache_id.$group_id] = [ $cache_id, $group_id ]; //$cache_ids can be the same across different groups (ie: user_id => users, user_id => user_preference) , so use $cache_id.$group_id as the key.
				}
			}

			return $retval;
		}

		return false;
	}

	/**
	 * @param string $cache_id
	 * @param string $group_id
	 * @return bool
	 */
	function removeCache( $cache_id = null, $group_id = null ) {
		//See ContributingPayCodePolicyFactory() ->getPayCode() for comments on a bug with caching...
		if ( is_object( $this->cache ) ) {
			$retval = false;

			if ( $group_id == '' ) {
				$group_id = $this->getTable( true );
			}

			//When using retryTransaction(), we set onlyMemoryCaching=TRUE.
			//  However Cache_Lite won't remove cache from persistent storage in that case. So whenever removing caching, set onlyMemoryCaching=FALSE so memory and persistent caches are cleared.
			//  **Since we have switched to recording all cache_ids saved while in a transaction, which are then cleared (see $this->clearCacheSavedInTransaction() ) on rollback this isn't needed anymore.
			//$current_cache_memory_state = $this->cache->_onlyMemoryCaching;
			//$this->cache->_onlyMemoryCaching = false;

			//If inside a transaction, queue cache keys to be removed once the transaction is committed as well.
			// **IMPORTANT** When cache records are pending removal, getCache() and saveCache() must ignore those records.
			//				 As saveCache() would write data only visible within our own transaction, and not other transactions on other connections.
			//				 and getCache() could retrieve cached data saved from other transactions on other connections that no longer match data within our own transaction.
			//   			 There is no point in removing the cache records immediately, and queing them for removal later though. As the cache records could still be useful for other transactions on other connections.
			//
			//               This can be tested by repeated running: start transaction, JobFactory->getUserBranch(), setUserBranch( [ 0, 1 ] ), getUserBranch(), setUserBranch( 3 ), getUserBranch(), commit transaction.
			if ( $this->db->transCnt > 0 ) {
				Debug::text( '  Queuing removal of cache: ' . $cache_id . ' Group Id: ' . $group_id .' TrnsCnt: '. $this->db->transCnt, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $cache_id != '' ) {
					$this->cache->__transaction_remove_cache_ids[$cache_id.$group_id] = [ $cache_id, $group_id ]; //$cache_ids can be the same across different groups (ie: user_id => users, user_id => user_preference) , so use $cache_id.$group_id as the key.
				} else {
					$this->cache->__transaction_remove_cache_group_ids[$group_id] = true;
				}
			} else {
				if ( $cache_id != '' ) {
					Debug::text( '  Removing cache: ' . $cache_id . ' Group Id: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $this->cache->remove( $cache_id, $group_id );
				} else if ( $group_id != '' ) {
					Debug::text( '  Removing cache group: ' . $group_id, __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $this->cache->clean( $group_id );
				}

				global $__tt_cache_profiler;
				$__tt_cache_profiler['total_delete']++;
			}

			//$this->cache->_onlyMemoryCaching = $current_cache_memory_state;

			return $retval;
		} else {
			Debug::text( 'WARNING: Unable to remove cache: ' . $cache_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param int $secs
	 * @return bool
	 */
	function setCacheLifeTime( $secs ) {
		if ( is_object( $this->cache ) ) {
			$this->cache->setLifeTime( $secs );

			return true;
		}

		return false;
	}

	/**
	 * Serialize ADODB recordset.
	 * @param object $rs
	 * @return string
	 * @noinspection PhpUndefinedConstantInspection
	 */
	function serializeRS( $rs ) {
		if ( $rs->RecordCount() > 0 ) {
			//
			//Below code is inspired by adodb-csvlib.inc.php _rs2serialize() and _rs2rs()
			//
			$rows = [];
			while ( !$rs->EOF ) {
				$rows[] = $rs->fields;
				$rs->MoveNext(); //This sets $rs->fields = false on last row, which makes it so we can't continue to use this RecordSet afterwards. We have modified adodb-postgres7.inc.php to prevent this from happening in the mean time.
			}

			$max = ( $rs ) ? $rs->FieldCount() : 0;
			for ( $i = 0; $i < $max; $i++ ) {
				$flds[] = $rs->FetchField( $i );
			}

			$class = $rs->connection->arrayClass;

			$rs2 = new $class( ADORecordSet::DUMMY_QUERY_ID );
			$rs2->compat = true; //Ensure fields are not cleared when MoveNext() is called, as that corrupts the in memory cache by clearing fields in it too.
			$rs2->timeCreated = $rs->timeCreated; # memcache fix
			$rs2->sql = $rs->sql;
			$rs2->InitArrayFields( $rows, $flds );
			$rs2->fetchMode = isset( $rs->adodbFetchMode ) ? $rs->adodbFetchMode : $rs->fetchMode;;

			return $rs2;
		} else {
			//PHP v8.1 can't serialize 'PgSql\Connection' or 'PgSql\Result', so make sure we pull those out of the $data object prior to caching.
			$rs->connection = null;
			$rs->_queryID = -1;

			return $rs;
		}

		return false;
	}

	/**
	 * @param bool $strip_quotes
	 * @return bool|string
	 */
	function getTable( $strip_quotes = false ) {
		if ( isset( $this->table ) ) {
			if ( $strip_quotes == true ) {
				return str_replace( '"', '', $this->table );
			} else {
				return $this->table;
			}
		}

		return false;
	}

	/**
	 * Generic function get any data from the data array.
	 * Used mainly for the reports that return grouped queries and such.
	 * @param string $column
	 * @return bool|mixed
	 */
	function getColumn( $column ) {
		if ( isset( $this->data[$column] ) ) {
			return $this->data[$column];
		}

		return false;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
	 * @param $data
	 * @return bool
	 */
	function TTSsetObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$data = $this->parseCustomFieldsFromArray( $data );

			$schema_columns = $this->getSchemaData( 'database' )->getColumns();
			if ( !empty( $schema_columns ) ) {
				foreach ( $schema_columns as $schema_column ) { /** @var TTSCol $schema_column */
					$column = $schema_column->getName();

					//Check if the field should be ignored as its not visible to the user anyways.
					if ( $schema_column->getIsUserVisible() == false ) {
						continue;
					}

					if ( $schema_column->getIsSynthetic() == true ) {
						continue;
					}

					if ( isset( $data[$column] ) ) {
						switch ( $column ) {
							case 'created_by':
							case 'created_by_id':
								if ( TTUUID::isUUID( $data[$column] ) && $data[$column] != TTUUID::getZeroID() && $data[$column] != TTUUID::getNotExistID() ) {
									$this->setCreatedBy( $data[$column] );
								}
								break;
							case 'created_date':
								if ( isset( $data[$column] ) && $data[$column] != false && $data[$column] != '' ) {
									$this->setCreatedDate( TTDate::parseDateTime( $data[$column] ) );
								}
								break;
							case 'updated_by':
							case 'updated_by_id':
								if ( TTUUID::isUUID( $data[$column] ) && $data[$column] != TTUUID::getZeroID() && $data[$column] != TTUUID::getNotExistID() ) {
									$this->setUpdatedBy( $data[$column] );
								}

								break;
							case 'updated_date':
								if ( isset( $data[$column] ) && $data[$column] != false && $data[$column] != '' ) {
									$this->setUpdatedDate( TTDate::parseDateTime( $data[$column] ) );
								}
								break;
							default:
								$function = $schema_column->getFunctionMap();
								if ( is_string( $function ) ) {
									$function = 'set' . $function;
									if ( method_exists( $this, $function ) ) {
										$this->$function( $this->setObjectFromArrayColumn( $column, $data[ $column ] ) );
									}
								}

								break;
						}
					}
				}
			}

			//$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	//Override in Factory class to for special column handling.
	function setObjectFromArrayColumn( string $column, $data ) {
		return $data;

		//Example when overridden:
		//switch ( $column ) {
		//	case 'effective_date':
		//		$retval = TTDate::getAPIDate( 'DATE', $data );
		//		break;
		//	default:
		//		$retval = $data;
		//		break;
		//}
		//
		//if ( isset( $retval ) ) {
		//	return $retval;
		//}
		//
		//return null;
	}

	//Override in Factory class to for special column handling.
	function getObjectAsArrayColumn( string $column, $data ) {
		return $data;

		//Example when overridden:
		//switch ( $column ) {
		//	case 'effective_date':
		//		$retval = TTDate::getAPIDate( 'DATE', $data );
		//		break;
		//	default:
		//		$retval = $data;
		//		break;
		//}
		//
		//if ( isset( $retval ) ) {
		//	return $retval;
		//}
		//
		//return null;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function TTSgetObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$schema_columns = $this->getSchemaData( 'database' )->getColumns();
		if ( !empty( $schema_columns ) ) {
			foreach ( $schema_columns as $schema_column ) { /** @var TTSCol $schema_column */
				$column = $schema_column->getName();
				if ( $include_columns == null || ( isset( $include_columns[$column] ) && $include_columns[$column] == true ) ) {
					//Check if the field is high resource and must be explicitly requested.
					if ( $schema_column->getIsExplicitRequest() == true && ( !isset( $include_columns[$column] ) || $include_columns[$column] != true ) ) {
						continue;
					}

					//Check if the field should be returned to the user or not.
					if ( $schema_column->getIsUserVisible() == false ) {
						continue;
					}

					$object_as_array_function = $schema_column->getObjectAsArrayFunction();
					if ( is_string( $object_as_array_function )  ) {
						switch ( $object_as_array_function ) {
							case 'Option::getByKey':
								$data[$column] = Option::getByKey( $this->getColumn( $column . '_id' ), $this->getOptions( $column ) );
								break;
							case 'getColumn':
								$data[$column] = $this->getObjectAsArrayColumn( $column, $this->getColumn( $column ) );
								break;
							default:
								$function = $schema_column->getFunctionMap();
								if ( is_string( $function ) ) {
									$function = 'get' . $function;
									if ( method_exists( $this, $function ) ) {
										$data[$column] = $this->getObjectAsArrayColumn( $column, $this->$function() );
									}
								}
								break;
						}
					} else {
						$function = $schema_column->getFunctionMap();
						if ( is_string( $function )  ) {
							$function = 'get'. $function;
							if ( method_exists( $this, $function ) ) {
								$data[$column] = $this->getObjectAsArrayColumn( $column, $this->$function() );
							}
						} else if ( is_array( $function ) ) {
							//This is used to call a custom function to return data, ie:
							//  TTSCol::new( 'status_display' )->setObjectAsArrayFunction( 'getStatusDisplay' )
							// Would call $this->>getStatusDisplay()
							$data[$column] = call_user_func( $function );
						} else {
							switch ( $column ) {
								case 'is_owner':
									if ( $this->getColumn( 'is_owner' ) !== false ) {
										$data['is_owner'] = (bool)$this->getColumn( 'is_owner' );
									} else {
										$permission = new Permission();
										$data['is_owner'] = $permission->isOwner( ( ( $schema_column->getEventFunction( 'getCreatedBy' ) != '' ) ? $this->{$schema_column->getEventFunction( 'getCreatedBy' )}() : null ), ( ( $schema_column->getEventFunction( 'getObjectUserID' ) != '' ) ? $this->{$schema_column->getEventFunction( 'getObjectUserID' )}() : null ) );
									}
									break;
								case 'is_child':
									//If is_child column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
									if ( $this->getColumn( 'is_child' ) !== false ) {
										$data['is_child'] = (bool)$this->getColumn( 'is_child' );
									} else {
										if ( is_array( $permission_children_ids ) ) {
											//ObjectID should always be a user_id.
											$permission = new Permission();
											$data['is_child'] = $permission->isChild( ( ( $schema_column->getEventFunction( 'getObjectUserID' ) != '' ) ? $this->{$schema_column->getEventFunction( 'getObjectUserID' )}() : null ), $permission_children_ids );
										} else {
											$data['is_child'] = false;
										}
									}
									break;

								case 'created_by':
									$data[$column] = Misc::getFullName( $this->getColumn( 'created_by_first_name' ), $this->getColumn( 'created_by_middle_name' ), $this->getColumn( 'created_by_last_name' ) );
									break;
								case 'created_by_id':
									$data[$column] = $this->getCreatedBy();
									break;
								case 'created_date':
									$data[$column] = TTDate::getAPIDate( 'DATE+TIME', $this->getCreatedDate() );
									break;
								case 'updated_by':
									$data[$column] = Misc::getFullName( $this->getColumn( 'updated_by_first_name' ), $this->getColumn( 'updated_by_middle_name' ), $this->getColumn( 'updated_by_last_name' ) );
									break;
								case 'updated_by_id':
									$data[$column] = $this->getUpdatedBy();
									break;
								case 'updated_date':
									$data[$column] = TTDate::getAPIDate( 'DATE+TIME', $this->getUpdatedDate() );
									break;
								case 'deleted':
									$data[$column] = $this->getDeleted();
									break;
							}
						}
					}


				}
			}

			if ( method_exists( $this, 'getCompany' ) == true ) {
				$data = $this->_getCustomFields( $this->getCompany(), $data, $include_columns );
			}
		}

		return $data;
	}

	/**
	 * Print primary columns from object.
	 * @return bool|string
	 */
	function __toString() {
		if ( method_exists( $this, 'getObjectAsArray' ) ) {
			$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
			$data = $this->getObjectAsArray( $columns );

			if ( is_array( $columns ) && is_array( $data ) ) {
				$retarr = [];
				foreach ( $columns as $column => $name ) {
					if ( isset( $data[$column] ) ) {
						$retarr[] = $name . ': ' . $data[$column];
					}
				}

				if ( count( $retarr ) > 0 ) {
					return implode( "\n", $retarr );
				}
			}
		}

		return false;
	}

	/**
	 * Converts a given value to a boolean integer representation.
	 * Accepts various input types and interprets common boolean representations.
	 * For example, the strings 't', 'true', '1', or a boolean `true` will all result in 1.
	 * Conversely, any other value will result in 0, representing `false`.
	 *
	 * @param string|int|bool $value The value to convert to a boolean integer.
	 * @return int Returns 1 if the value represents `true`, otherwise returns 0.
	 */
	function toBool( $value ) {
		$value = strtolower( trim( $value ) );

		if ( $value === true || $value == 1 || $value == 't' ) {
			//return 't';
			return 1;
		} else {
			//return 'f';
			return 0;
		}
	}

	/**
	 * Converts a value to a boolean.
	 * This function takes a value that can be a string, integer, or boolean
	 * and converts it to a boolean. It is designed to interpret common
	 * representations of truthy and falsy values, such as '1', 't', or true
	 * as `true`, and any other value as `false`.
	 *
	 * @param string|int|bool $value The value to be converted to a boolean.
	 * @return bool The boolean representation of the input value.
	 */
	function fromBool( $value ) {
		if ( $value == 1 ) {
			return true;
		} else if ( $value == 0 ) {
			return false;
		} else if ( strtolower( trim( $value ) ) == 't' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Converts a given value to a JSON compatible boolean string.
	 *
	 * This function accepts a variety of input types and interprets common boolean representations.
	 * For example, the strings 't', 'true', '1', 'yes', 'ye', or 'y', or a boolean `true` will all result in the string 'true'.
	 * Conversely, any other value will result in the string 'false'.
	 * This function is particularly useful when you need to ensure that boolean values are correctly represented in a JSON context,
	 * regardless of their original format.
	 *
	 * @param string|int|bool $value The value to be converted. It can be a string, integer, or boolean.
	 * @return string Returns 'true' if the value is a common representation of true. Otherwise, returns 'false'.
	 */
	function toJSONBool( $value ) {
		$value = strtolower( trim( $value ) );

		//Issue #3382 - On a dropdown list a user may search "y" or "yes" for checkbox fields as those search fields only allow text search.
		//We could add "ye" but likely the user will type fast enough to not see "no" results for "ye".
		if ( $value === true || $value == 1 || $value == 't' || $value == 'yes' || $value == 'ye' || $value == 'y' ) {
			return 'true';
		} else {
			return 'false';
		}
	}

	/**
	 * Determines if the provided value is considered a boolean true or false.
	 *
	 * This function accepts a variety of input types (string, integer, boolean) and interprets them as boolean values.
	 * It is designed to handle common representations of boolean values. For example, the function will return true
	 * if the input is a boolean true, an integer 1, or a string 'true'. Any other value will result in the function
	 * returning false.
	 *
	 * @param string|int|bool $value The value to be evaluated as a boolean.
	 * @return bool Returns true if the input value is considered a boolean true, false otherwise.
	 */
	function fromJSONBool( $value ) {
		if ( $value == 1 ) {
			return true;
		} else if ( $value == 0 ) {
			return false;
		} else if ( strtolower( trim( $value ) ) == 'true' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if the data is new data, or updated data. Basically determines if a database INSERT or UPDATE SQL statement is generated.
	 * @param bool $force_lookup
	 * @param string $id UUID
	 * @return bool
	 */
	function isNew( $force_lookup = false, $id = null ) {
		if ( $id === null ) {
			$id = $this->getId();
		}
		//Debug::Arr( $this->getId(), 'getId: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === false ) {
			//New Data
			return true;
		} else if ( $force_lookup == true ) {
			//See if we can find the ID to determine if the record needs to be inserted or update.
			$ph = [ 'id' => $id ]; // Do not cast to UUID as it needs to support both integer and UUID across v11 upgrade.
			$query = 'SELECT id FROM ' . $this->getTable() . ' WHERE id = ?';
			$retval = $this->db->GetOne( $query, $ph );
			if ( $retval === false ) {
				return true;
			}
		}

		//Not new data
		return false;
	}

	/**
	 * Retrieves the label ID for the current object.
	 * If the object has an ID, it returns that ID. If the object does not have an ID,
	 * it returns a default value of '-1', which is used in validator labels to indicate
	 * the absence of an ID. This function is useful for UI elements that require a consistent
	 * identifier even when the underlying object may not yet be persisted with a real ID.
	 *
	 * @return string The ID of the object or '-1' if the object has no ID.
	 */
	function getLabelId() {
		//Gets the ID used in validator labels. If no ID, uses "-1";
		if ( $this->getId() == false ) {
			return '-1';
		}

		return $this->getId();
	}

	/**
	 * @return bool|mixed
	 */
	function getId() {
		$id = $this->getGenericDataValue( 'id' );
		if ( $id != null ) {
			return $id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setId( $id ) {
		global $PRIMARY_KEY_IS_UUID;

		if ( $PRIMARY_KEY_IS_UUID == false ) {
			if ( is_numeric( $id ) || is_bool( $id ) ) {
				$this->setGenericDataValue( 'id', $id ); //Allow ID to be set as FALSE. Essentially making a new entry.

				return true;
			}
		} else {
			if ( is_bool( $id ) || TTUUID::isUUID( $id ) ) {
				$this->setGenericDataValue( 'id', $id ); //Allow ID to be set as FALSE. Essentially making a new entry.

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableSystemLogDetail() {
		if ( isset( $this->enable_system_log_detail ) ) {
			return $this->enable_system_log_detail;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSystemLogDetail( $bool ) {
		$this->enable_system_log_detail = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getDeleted() {
		return $this->fromBool( $this->getGenericDataValue( 'deleted' ) );
	}

	/**
	 * @param bool $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		$value = (bool)$bool;

		//Handle Postgres's boolean values.
		if ( $value === true ) {
			//Only set this one we're deleting
			$this->setDeletedDate();
			$this->setDeletedBy();
		}

		$this->setGenericDataValue( 'deleted', $this->toBool( $value ) );

		return true;
	}

	/**
	 * @return int
	 */
	function getCreatedDate() {
		$raw = false;
		$value = $this->getGenericDataValue( 'created_date' );
		if ( $value !== false && $value !== null ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value ); //**NOTE: This is required for PaymentServices which uses "timestamp with time zone" type, and eventually these will too.
			}
		}

		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch )  && $epoch != '' ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'created_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'created_date', $epoch );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getCreatedBy() {
		return $this->getGenericDataValue( 'created_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		if ( TTUUID::isUUID( $id ) == false ) { //Don't change if its not a valid UUID.
			return false;
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'created_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->setGenericDataValue( 'created_by', $id );

			return TRUE;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'created_by', $id );

		return true;
	}

	/**
	 * @return int
	 */
	function getUpdatedDate() {
		$raw = false;
		$value = $this->getGenericDataValue( 'updated_date' );
		if ( $value !== false && $value !== null ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value ); //**NOTE: This is required for PaymentServices which uses "timestamp with time zone" type, and eventually these will too.
			}
		}

		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool|int|null|string
	 */
	function setUpdatedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch ) && $epoch != '' ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'updated_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'updated_date', $epoch );

			//return TRUE;
			//Return the value so we can use it in getUpdateSQL
			return $epoch;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUpdatedBy() {
		return $this->getGenericDataValue( 'updated_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool|null
	 */
	function setUpdatedBy( $id = null ) {
		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		if ( TTUUID::isUUID( $id ) == false ) { //Don't change if its not a valid UUID.
			return false;
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {
			$this->setGenericDataValue( 'updated_by', $id );

			//return TRUE;
			return $id;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'updated_by', $id );

		return $id;
	}


	/**
	 * @return bool|mixed
	 */
	function getDeletedDate() {
		$raw = false;
		$value = $this->getGenericDataValue( 'deleted_date' );
		if ( $value !== false && $value !== null ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value ); //**NOTE: This is required for PaymentServices which uses "timestamp with time zone" type, and eventually these will too.
			}
		}

		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		$epoch = ( !is_int( $epoch ) && $epoch != '' ) ? trim( $epoch ) : $epoch; //Dont trim integer values, as it changes them to strings.

		if ( $epoch == null || $epoch == '' || $epoch == 0 ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->Validator->isDate( 'deleted_date',
									   $epoch,
									   TTi18n::gettext( 'Incorrect Date' ) ) ) {

			$this->setGenericDataValue( 'deleted_date', $epoch );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getDeletedBy() {
		return $this->getGenericDataValue( 'deleted_by' );
	}

	/**
	 * @param string $id UUID
	 * @return bool|null
	 */
	function setDeletedBy( $id = null ) {
		//$id = trim($id);

		if ( empty( $id ) ) {
			global $current_user;

			if ( is_object( $current_user ) ) {
				$id = $current_user->getID();
			} else {
				return false;
			}
		}

		//Its possible that these are incorrect, especially if the user was created by a system or master administrator.
		//Skip strict checks for now.
		/*
		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'updated_by',
													$ulf->getByID($id),
													TTi18n::gettext('Incorrect User')
													) ) {

			$this->setGenericDataValue( 'deleted_by', $id );

			return TRUE;
		}

		return FALSE;
		*/

		$this->setGenericDataValue( 'deleted_by', $id );

		return $id;
	}

	/**
	 * Sets the is_valid flag, mostly used to set it to FALSE to force a full re-validation.
	 * Required because $this->is_valid is a private variable and should stay that way.
	 * @param bool $is_valid
	 * @return bool
	 */
	function setIsValid( $is_valid = false ) {
		$this->is_valid = $is_valid;

		return true;
	}

	/**
	 * @param array $data
	 * @param array $variable_to_function_map
	 * @return bool
	 */
	function setCreatedAndUpdatedColumns( $data, $variable_to_function_map = [] ) {
		//Debug::text(' Set created/updated columns...', __FILE__, __LINE__, __METHOD__, 10);

		//CreatedBy/Time needs to be set to original values when doing things like importing records.
		//However from the API, Created By only needs to be set for a small subset of classes like RecurringScheduleTemplateControl.
		//For now, only allow these fields to be changed from user input if its set in the variable_to_function_map.

		//Update array in-place.
		if ( isset( $data['created_by'] )
				&& TTUUID::isUUID( $data['created_by'] ) && $data['created_by'] != TTUUID::getZeroID() && $data['created_by'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['created_by'] ) ) {
			$this->setCreatedBy( $data['created_by'] );
		}
		if ( isset( $data['created_by_id'] )
				&& TTUUID::isUUID( $data['created_by_id'] ) && $data['created_by_id'] != TTUUID::getZeroID() && $data['created_by_id'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['created_by'] ) ) {
			$this->setCreatedBy( $data['created_by_id'] );
		}
		if ( isset( $data['created_date'] ) && $data['created_date'] != false && $data['created_date'] != '' && isset( $variable_to_function_map['created_date'] ) ) {
			$this->setCreatedDate( TTDate::parseDateTime( $data['created_date'] ) );
		}

		if ( isset( $data['updated_by'] )
				&& TTUUID::isUUID( $data['updated_by'] ) && $data['updated_by'] != TTUUID::getZeroID() && $data['updated_by'] != TTUUID::getNotExistID()
				&& isset( $variable_to_function_map['updated_by'] ) ) {
			$this->setUpdatedBy( $data['updated_by'] );
		}
		if ( isset( $data['updated_by_id'] ) && TTUUID::isUUID( $data['updated_by_id'] ) && $data['updated_by_id'] > 0 && isset( $variable_to_function_map['updated_by'] ) ) {
			$this->setUpdatedBy( $data['updated_by_id'] );
		}
		if ( isset( $data['updated_date'] ) && $data['updated_date'] != false && $data['updated_date'] != '' && isset( $variable_to_function_map['updated_date'] ) ) {
			$this->setUpdatedDate( TTDate::parseDateTime( $data['updated_date'] ) );
		}

		return true;
	}

	/**
	 * @param array $data
	 * @param null $include_columns
	 * @return bool
	 */
	function getCreatedAndUpdatedColumns( &$data, $include_columns = null ) {
		//Update array in-place.
		if ( $include_columns == null || ( isset( $include_columns['created_by_id'] ) && $include_columns['created_by_id'] == true ) ) {
			$data['created_by_id'] = $this->getCreatedBy();
		}
		if ( $include_columns == null || ( isset( $include_columns['created_by'] ) && $include_columns['created_by'] == true ) ) {
			$data['created_by'] = Misc::getFullName( $this->getColumn( 'created_by_first_name' ), $this->getColumn( 'created_by_middle_name' ), $this->getColumn( 'created_by_last_name' ) );
		}
		if ( $include_columns == null || ( isset( $include_columns['created_date'] ) && $include_columns['created_date'] == true ) ) {
			$data['created_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getCreatedDate() );
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_by_id'] ) && $include_columns['updated_by_id'] == true ) ) {
			$data['updated_by_id'] = $this->getUpdatedBy();
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_by'] ) && $include_columns['updated_by'] == true ) ) {
			$data['updated_by'] = Misc::getFullName( $this->getColumn( 'updated_by_first_name' ), $this->getColumn( 'updated_by_middle_name' ), $this->getColumn( 'updated_by_last_name' ) );
		}
		if ( $include_columns == null || ( isset( $include_columns['updated_date'] ) && $include_columns['updated_date'] == true ) ) {
			$data['updated_date'] = TTDate::getAPIDate( 'DATE+TIME', $this->getUpdatedDate() );
		}

		return true;
	}

	/**
	 * @param array $data
	 * @param string $object_user_id          UUID
	 * @param string $created_by_id           UUID
	 * @param string $permission_children_ids UUID
	 * @param array $include_columns
	 * @return bool
	 */
	function getPermissionColumns( &$data, $object_user_id, $created_by_id, $permission_children_ids = null, $include_columns = null ) {
		$permission = new Permission();

		if ( $include_columns == null || ( isset( $include_columns['is_owner'] ) && $include_columns['is_owner'] == true ) ) {
			//If is_owner column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn( 'is_owner' ) !== false ) {
				$data['is_owner'] = (bool)$this->getColumn( 'is_owner' );
			} else {
				$data['is_owner'] = $permission->isOwner( $created_by_id, $object_user_id );
			}
		}

		if ( $include_columns == null || ( isset( $include_columns['is_child'] ) && $include_columns['is_child'] == true ) ) {
			//If is_child column is passed directly from SQL, use that instead of adding it here. Specifically the UserListFactory uses this.
			if ( $this->getColumn( 'is_child' ) !== false ) {
				$data['is_child'] = (bool)$this->getColumn( 'is_child' );
			} else {
				if ( is_array( $permission_children_ids ) ) {
					//ObjectID should always be a user_id.
					$data['is_child'] = $permission->isChild( $object_user_id, $permission_children_ids );
				} else {
					$data['is_child'] = false;
				}
			}
		}

		return true;
	}

	/**
	 * Retrieves options for a given name, potentially filtered by a parent identifier.
	 * This function is used to get configuration options or data arrays that are associated with a specific name.
	 * If a parent is specified, the options are further filtered to only include those relevant to the given parent.
	 * The parent can be a string or an integer, depending on the context of the options being retrieved.
	 *
	 * @param string $name            The name of the options to retrieve.
	 * @param string|int|null $params The parent identifier to filter the options, or null if no filtering is required.
	 * @return array|bool An array of options if successful, or false if no options are found or an error occurs.
	 */
	function getOptions( $name, $params = null ) {
		if ( $params == null || $params == '' ) {
			$retval = $this->_getFactoryOptions( $name );

			//Always return unique_columns and linked_columns as an empty array if they are NULL.
			//Otherwise if we return NULL it won't be JSON encoded and could cause a AJAX error in JS.
			//This also prevents having to duplicate this code in all the individual factory classes.
			if ( $retval === null && ( $name == 'unique_columns' || $name == 'linked_columns' ) ) {
				$retval = [];
			}

			return $retval;
		} else if ( is_array( $params ) ) {
			return $this->_getFactoryOptions( $name, $params );
		} else {
			$retval = $this->_getFactoryOptions( $name, $params );
			if ( isset( $retval[$params] ) ) {
				return $retval[$params];
			}
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param string|int $parent
	 * @return bool|array
	 */
	protected function _getFactoryOptions( $name, $params = null ) {
		return false;
	}

	function getSchemaData( ?array $filter = null, string $format = 'object' ): TTS|string|null {
		//TODO: Handle caching here eventually if more performance is needed?
		$schema_data = $this->_getSchemadata( $filter );

		if ( $format !== 'object' ) {
			return $schema_data->serializeSchemaData( $format );
		}

		return $schema_data;
	}

	function _getSchemadata( ?array $filter = null ): ?TTS {
		return null;
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	function getVariableToFunctionMap( $data = null ) {
		return $this->_getVariableToFunctionMap( $data );
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	protected function _getVariableToFunctionMap( $data ) {
		return false;
	}

	/**
	 * @return int|bool
	 */
	function getAffectedRows() {
		return $this->db->Affected_Rows();
	}

	/**
	 * @return int|bool
	 */
	function getRecordCount() {
		if ( isset( $this->rs->_numOfRows ) ) { //Check a deep variable to make sure it is in fact a valid ADODB record set, just in case some other object is passed in.
			return $this->rs->RecordCount();
		}

		return false;
	}

	/**
	 * @param int $offset
	 * @return int|bool
	 */
	function getCurrentRow( $offset = 1 ) {
		if ( isset( $this->rs ) && isset( $this->rs->_currentRow ) ) {
			return ( $this->rs->_currentRow + (int)$offset );
		}

		return false;
	}

	/**
	 * @param null $milliseconds
	 * @return bool
	 */
	function setQueryStatementTimeout( $milliseconds = null ) {
		if ( $milliseconds == '' ) {
			$milliseconds = 0;
			if ( isset( $this->config['other']['query_statement_timeout'] ) ) {
				$milliseconds = (int)$this->config['other']['query_statement_timeout'];
			}
		}

		Debug::Text( 'Setting DB query statement timeout to: ' . $milliseconds, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getDatabaseType() == 'postgres' ) {
			$this->db->Execute( 'SET statement_timeout = ' . (int)$milliseconds );
		}

		return true;
	}

	/**
	 * @param object $rs
	 * @return array|bool
	 */
	private function getRecordSetColumnList( $rs ) {
		if ( is_object( $rs ) ) {
			for ( $i = 0, $max = $rs->FieldCount(); $i < $max; $i++ ) {
				$field = $rs->FetchField( $i );
				$fields[] = $field->name;
			}

			return $fields;
		}

		return false;
	}

	/**
	 * Casts a given integer or string to a specific integer type if within bounds.
	 * This function handles casting to various integer sizes (smallint, integer, bigint),
	 * as well as special types like 'numeric_string' and 'uuid'.
	 * It ensures that the value is within the range of the specified type and returns false if not.
	 * For 'numeric_string', the value is returned as-is, assuming it's already been sanitized.
	 * For 'uuid', a UUID casting function is used.
	 * If the type is not recognized, the original value is returned.
	 *
	 * @param int|string $int The value to be cast, can be an integer or a string representing an integer.
	 * @param string $type The type to cast the value to. Supported types: 'smallint', 'numeric_string', 'numeric', 'int', 'bigint', 'uuid'.
	 * @return bool|int|string The cast value if within type bounds, false if out of bounds, or the original value for unrecognized types.
	 */
	protected function castInteger( $int, $type = 'int' ) {
		//smallint	2 bytes	small-range integer	-32768 to +32767
		//integer	4 bytes	typical choice for integer	-2147483648 to +2147483647
		//bigint	8 bytes	large-range integer	-9223372036854775808 to 9223372036854775807
		switch ( $type ) {
			case 'smallint':
				if ( $int > 32767 || $int < -32768 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'numeric_string':
				//This is just numeric values, but not actualling cast to integers, ie: SSNs that start with leading 0s.
				//Since stripNonNumeric is already run on the input, just return the value untouched.
				$retval = $int;
				break;
			case 'numeric':
			case 'int':
				if ( $int > 2147483647 || $int < -2147483648 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'bigint':
				if ( $int > 9223372036854775807 || $int < -9223372036854775808 ) {
					$retval = false;
				} else {
					$retval = (int)$int;
				}
				break;
			case 'uuid':
				$retval = TTUUID::castUUID( $int );
				break;
			default:
				return $int; //Make sure if the $type is not recognized we just return the raw value again.
				break;
		}

		if ( $retval === false ) {
			Debug::Text( ' Integer outside range: ' . $int . ' Type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * Constructs a SQL list string from an array or a single value.
	 * This function is used to create a comma-separated list for use in SQL queries.
	 * It can handle arrays of values, single integer values, or single string values.
	 * When a placeholder array is provided, it will populate it with the values from the input array.
	 * If casting is required, it will cast the values to the appropriate SQL type.
	 *
	 * @param array|string|int $array The input array or single value to be transformed into a SQL list string.
	 * @param array|null $ph A reference to an array that will be populated with placeholders for prepared statements.
	 * @param string|bool $cast The type to cast the values to, or false if no casting is needed.
	 * @return string A string containing a comma-separated list of values for SQL queries, or 'NULL' if the input is empty.
	 */
	protected function getListSQL( $array, &$ph = null, $cast = false ) {
		//Debug::Arr($array, 'List Values:', __FILE__, __LINE__, __METHOD__, 10);
		if ( $ph === null ) {
			if ( is_array( $array ) && count( $array ) > 0 ) {
				return '\'' . implode( '\', \'', $array ) . '\'';
			} else if ( is_array( $array ) ) {
				//Return NULL, because this is an empty array.
				return 'NULL';
			} else if ( $array == '' ) {
				return 'NULL';
			}

			//Just a single ID, return it.
			return $array;
		} else {
			//Debug::Arr($ph, 'Place Holder BEFORE:', __FILE__, __LINE__, __METHOD__, 10);

			//Append $array values to end of $ph, return
			//one "?, " for each element in $array.

			if ( is_array( $array ) && count( $array ) > 0 ) {
				//If its a large array, we could reach PostgreSQL's 65535 max placeholder limit.
				//  Therefore as long as we have a strict cast (ie: to UUID), its safe to use a string without place holders instead.
				//  The maximum query length is: 2147483648 which is about 63 million UUID's
				if ( count( $array ) > 65000 && $cast == 'uuid' ) {
					Debug::Text( '  NOTICE: Large array detected ('. count( $array ) .'), using string instead of place holders...', __FILE__, __LINE__, __METHOD__, 10 );
					$retval = '\'' . implode( '\',\'', array_map( [ 'TTUUID', 'castUUID' ], $array ) ) . '\'';
				} else {
					foreach ( $array as $val ) {
						$ph_arr[] = '?';

						//Make sure we filter out any FALSE or NULL values from going into a SQL list.
						//Replace them with "-1"'s so we keep the same number of place holders.
						//This should hopefully prevent SQL errors if a FALSE creeps into the SQL list array.
						//Check is_numeric/is_string before strtolower(), because if an array sneaks through it will cause a PHP warning.
						if ( is_null( $val ) === false && $val !== '' && ( is_numeric( $val ) || is_string( $val ) ) && strtolower( $val ) !== 'false' && strtolower( $val ) !== 'true' ) {
							$val = $this->castInteger( $val, $cast );
							if ( $val === false ) {
								$ph[] = -1;
							} else {
								$ph[] = $val;
							}
						} else {
							$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
						}
					}

					if ( isset( $ph_arr ) ) {
						$retval = implode( ',', $ph_arr );
					}
				}
			} else if ( is_array( $array ) ) {
				//Return NULL, because this is an empty array.
				//This may have to return -1 instead of NULL
				//$ph[] = 'NULL';
				$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				$retval = '?';
			} else if ( $array === false || $array === '' ) { //Make sure we don't catch int(0) here.
				//$ph[] = 'NULL';
				//$ph[] = -1;
				$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				$retval = '?';
			} else {
				$array = $this->castInteger( $array, $cast );
				if ( $array === false ) {
					$ph[] = ( ( $cast == 'uuid' ) ? TTUUID::getNotExistID() : -1 );
				} else {
					$ph[] = $array;
				}
				$retval = '?';
			}

			//Debug::Arr($ph, 'Place Holder AFTER: Cast: '. $cast, __FILE__, __LINE__, __METHOD__, 10);

			//Just a single ID, return it.
			return $retval;
		}
	}

	/**
	 * Constructs a SQL condition for filtering records within a specified date range.
	 * The function interprets user input that specifies date boundaries using operators
	 * such as '>=', '<=', '<', '>', and combinations thereof. It supports a syntax where
	 * dates are combined with these operators to define the range. The resulting SQL
	 * condition can be used in a WHERE clause to filter database records based on the
	 * specified date criteria. The function also handles the inclusion of records with
	 * blank or null date values if specified by the caller.
	 *
	 * Supported Syntax Examples:
	 *       >=01-Jan-09
	 *       <=01-Jan-09
	 *       <01-Jan-09
	 *       >01-Jan-09
	 *       >01-Jan-09 & <10-Jan-09
	 *
	 * @param string $str The date range criteria in a supported syntax.
	 * @param string $column The database column to apply the date range filter on.
	 * @param string $format The format in which the date should be returned ('epoch', 'timestamp', 'datestamp').
	 * @param bool $include_blank_dates Whether to include records where the date column is blank or null.
	 * @return string|false A SQL condition string for the date range filter, or false if input is invalid.
	 */
	function getDateRangeSQL( $str, $column, $format = 'epoch', $include_blank_dates = false ) {
		if ( $str == '' ) {
			return false;
		}

		if ( $column == '' ) {
			return false;
		}

		//Debug::text(' Format: '. $format .' String: '. $str .' Column: '. $column, __FILE__, __LINE__, __METHOD__, 10);

		$operators = [
				'>',
				'<',
				'>=',
				'<=',
				'=',
		];
		$operations = [];
		//Parse input, separate any subqueries first.
		$split_str = explode( '&', $str, 2 ); //Limit sub-queries
		if ( is_array( $split_str ) ) {
			foreach ( $split_str as $tmp_str ) {
				$tmp_str = trim( $tmp_str );
				$date = (int)TTDate::parseDateTime( str_replace( $operators, '', $tmp_str ) );
				//Debug::text(' Parsed Date: '. $tmp_str .' To: '. TTDate::getDate('DATE+TIME', $date) .' ('. $date .')', __FILE__, __LINE__, __METHOD__, 10);

				if ( $date != 0 && TTDate::isValidDate( $date ) == true ) {
					preg_match( '/^>=|>|<=|</i', $tmp_str, $operator );
					//Debug::Arr($operator, ' Operator: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $operator[0] ) && in_array( $operator[0], $operators ) ) {
						if ( TTDate::getHour( $date ) == 0 && TTDate::getMinute( $date ) == 0 ) { //If the date isn't midnight, its likely a timestamp has been specifically passed in, so don't modify it by using getEndOfDayEpoch()
							if ( $operator[0] == '<=' ) {
								$date = TTDate::getEndDayEpoch( $date );
							} else if ( $operator[0] == '>' ) {
								$date = TTDate::getEndDayEpoch( $date );
							}
						}

						if ( $format == 'timestamp' ) {
							$date = '\'' . $this->db->bindTimeStamp( $date ) . '\'';
						} else if ( $format == 'datestamp' ) {
							$date = '\'' . $this->db->bindDate( $date ) . '\'';
						}

						if ( $include_blank_dates == true ) {
							$operations[] = '(' . $column . ' ' . $operator[0] . ' ' . $date . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
						} else {
							$operations[] = $column . ' ' . $operator[0] . ' ' . $date;
						}
					} else {
						//FIXME: Need to handle date filters without any operators better.
						//for example JobListFactory and JobSummaryReport and the time period is specified.
						$date1 = TTDate::getBeginDayEpoch( $date );
						$date2 = TTDate::getEndDayEpoch( $date );
						if ( $format == 'timestamp' ) {
							$date1 = '\'' . $this->db->bindTimeStamp( $date1 ) . '\'';
							$date2 = '\'' . $this->db->bindTimeStamp( $date2 ) . '\'';
						} else if ( $format == 'datestamp' ) {
							$date1 = '\'' . $this->db->bindDate( $date1 ) . '\'';
							$date2 = '\'' . $this->db->bindDate( $date2 ) . '\'';
						}

						//Debug::text(' No operator specified... Using a 24hr period', __FILE__, __LINE__, __METHOD__, 10);
						if ( $include_blank_dates == true ) {
							if ( $format == 'epoch' ) {
								$operations[] = '(' . $column . ' >= ' . $date1 . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
								$operations[] = '(' . $column . ' <= ' . $date2 . ' OR ( ' . $column . ' is NULL OR ' . $column . ' = 0 ) )';
							} else {
								//When $column is a date/timestamp datatype, can't use = 0 on it without causing SQL error.
								$operations[] = '(' . $column . ' >= ' . $date1 . ' OR ( ' . $column . ' is NULL ) )';
								$operations[] = '(' . $column . ' <= ' . $date2 . ' OR ( ' . $column . ' is NULL ) )';
							}
						} else {
							$operations[] = $column . ' >= ' . $date1;
							$operations[] = $column . ' <= ' . $date2;
						}
					}
				}
			}
		}

		//Debug::Arr($operations, ' Operations: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $operations ) && count( $operations ) > 0 ) {
			$retval = ' ( ' . implode( ' AND ', $operations ) . ' )';

			//Debug::text(' Query parts: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}

		return false;
	}

	/**
	 * Prepares a string for use in an SQL WHERE clause.
	 *
	 * This function sanitizes the input string to be used in a database query by handling
	 * wildcards and exact match syntax. It replaces '*' with '%' to match any number of characters
	 * and ensures that a '%' is added at the end of the string unless the string ends with '|',
	 * which indicates no wildcard should be appended. If the string is enclosed in double quotes,
	 * it is treated as an exact match.
	 *
	 * @param string $arg The input string to be sanitized for the SQL query.
	 * @return string The sanitized string, ready to be used in an SQL WHERE clause.
	 */
	protected function handleSQLSyntax( $arg ) {
		$arg = str_replace( '*', '%', trim( $arg ) );

		//Make sure we don't add '%' if $arg is blank.
		if ( $arg != '' && strpos( $arg, '%' ) === false && ( strpos( $arg, '|' ) === false && strpos( $arg, '"' ) === false ) ) {
			$arg .= '%';
		}

		return addslashes( $this->stripSQLSyntax( $arg ) ); //Addaslashes to prevent SQL syntax error if %\ is at the end of the where clause.
	}

	/**
	 * @param string $arg
	 * @return mixed
	 */
	protected function stripSQLSyntax( $arg ) {
		return str_replace( [ '"' ], '', $arg ); //Strip syntax characters out.
	}

	/**
	 * @return string
	 */
	protected function getSQLToTimeStampFunction() {
		$to_timestamp_sql = 'to_timestamp';

		return $to_timestamp_sql;
	}

	/**
	 * @return string
	 */
	protected function getDatabaseType() {
		$database_driver = 'postgres';

		return $database_driver;
	}

	/**
	 * Converts a SQL timestamp to the number of seconds since the Unix Epoch.
	 *
	 * This function takes a SQL expression representing a timestamp and converts it
	 * into an expression that will yield the number of seconds since the Unix Epoch (1970-01-01 00:00:00 UTC).
	 * It is particularly useful for database operations that need to compare or calculate
	 * durations based on timestamps stored in the database.
	 *
	 * @param string $sql The SQL expression representing a timestamp.
	 * @return string A SQL expression to convert the timestamp to seconds since the Unix Epoch.
	 */
	protected function getSQLToEpochFunction( $sql ) {
		//In cases where the column is a timestamp without timezone column (ie: Pay Periods when used from PayPeriodTimeSheetVerify)
		//We need to case it to a timezone otherwise when adding/subtracting epoch seconds, it may be unexpectedly offset by the timezone amount.
		$to_timestamp_sql = 'EXTRACT( EPOCH FROM ' . $sql . '::timestamp with time zone )';

		return $to_timestamp_sql;
	}

	/**
	 * Converts a SQL timestamp expression to the number of seconds since the Unix Epoch.
	 * This function is useful for converting SQL timestamp columns to a Unix timestamp
	 * which can be used for date calculations and comparisons in PHP.
	 *
	 * @param string $sql The SQL expression representing a timestamp.
	 * @return string A SQL expression to convert the given timestamp to a Unix timestamp.
	 */
	protected function getSQLToTimeFunction( $sql ) {
		$to_time_sql = $sql . '::time';

		return $to_time_sql;
	}

	/**
	 * Constructs a SQL expression to concatenate an array of values into a string.
	 *
	 * This method takes a SQL expression that represents an array of values and a string to use as glue.
	 * It returns a SQL expression that will concatenate the array elements into a single string, separated by the glue.
	 * This is particularly useful for aggregating values from a column in a query result into a single field.
	 *
	 * @param string $sql The SQL expression representing an array of values.
	 * @param string $glue The string to use as a separator between the array values.
	 * @return string A SQL expression for the string aggregation.
	 */
	protected function getSQLStringAggregate( $sql, $glue ) {
		//See Group.class.php aggegate() function with 'concat' argument, that is used in most reports instead.
		$agg_sql = 'array_to_string( array_agg( ' . $sql . ' ), \'' . $glue . '\')'; //Works with PGSQL 8.4+
		//$agg_sql = 'string_agg('. $sql .', \''. $glue .'\')'; //Works with PGSQL 9.1+

		return $agg_sql;
	}

	/**
	 * Constructs a SQL WHERE clause for filtering based on custom fields.
	 *
	 * This method processes an array of filter criteria related to custom fields and
	 * generates the corresponding SQL WHERE clause. It is designed to handle complex
	 * filtering logic that may involve various custom field types and their respective
	 * storage formats in the database. The method also populates a placeholder array
	 * used for parameterized queries, ensuring safe and efficient database interactions.
	 *
	 * @param array $filter_data An associative array of filter criteria for custom fields.
	 * @param array &$ph A reference to an array that will be populated with placeholders for prepared statements.
	 * @return string A SQL WHERE clause as a string.
	 */
	protected function getCustomFieldWhereSQL( $company_id, $column, $filter_data, &$ph ) {
		$query_string = '';

		if ( is_array( $filter_data ) ) {

			//Get any custom fields that are being used as filters
			$custom_fields = [];
			foreach ( $filter_data as $filter_name => $request_data ) {
				if ( strpos( $filter_name, 'custom_field-' ) !== false ) {
					$custom_fields[str_replace( 'custom_field-', '', $filter_name )] = $request_data;
				}
			}

			if ( empty( $custom_fields ) == false ) {
				$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
				$cflf->getByIdAndCompanyId( array_keys( $custom_fields ), $company_id );
				if ( $cflf->getRecordCount() > 0 ) {
					foreach ( $cflf as $cf_obj ) {
						$custom_field_data_requested = $custom_fields[$cf_obj->getID()];
						switch ( $cf_obj->getType() ) { /** @var CustomFieldFactory $cf_obj */
							case 100: //Text
							case 110: //Textare
								$query_string .= $this->getWhereClauseSQL( $column . '->>\'' . $cf_obj->getId() . '\'', $custom_field_data_requested, 'text', $ph );
								break;
							case 400: //Integer
								$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $custom_field_data_requested ], 'custom_field_jsonb_int', $ph );
								break;
							case 500: //Checkbox
								if ( $custom_field_data_requested !== TTUUID::getZeroID() ) { //Zero uuid is any checkbox value.
									$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $custom_field_data_requested ], 'custom_field_jsonb_bool', $ph );
								}
								break;
							case 410: //Decimal
							case 420: //Currency
								$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $custom_field_data_requested ], 'custom_field_jsonb_float', $ph );
								break;
							case 1000: //Date
							case 1100: //Time
								$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $cf_obj->castToSql( $cf_obj->getType(), $custom_field_data_requested ) ], 'custom_field_jsonb_string', $ph );
								break;
							case 1200: //Datetime
								$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $cf_obj->castToSql( $cf_obj->getType(), $custom_field_data_requested ) ], 'custom_field_jsonb_int', $ph );
								break;
							//case 1010: //Date Range Search Disabled
							//	$retarr_query = $this->getWhereClauseSQL( '(' . $column . '->>\'' . $cf_obj->getId() . '\')', $cf_obj->castToSql( $cf_obj->getType(), $custom_field_data_requested ), 'jsonb_text_list', $ph );
							//	break;
							case 2100: //Single-select
								$query_string .= $this->getWhereClauseSQL( '(' . $column . '->>\'' . $cf_obj->getId() . '\')', $custom_field_data_requested, 'jsonb_text_any', $ph );
								break;
							case 2110: //Multi-select
								$query_string .= $this->getWhereClauseSQL( '(' . $column . '->>\'' . $cf_obj->getId() . '\')', $custom_field_data_requested, 'jsonb_text_list', $ph );
								break;
							default:
								$query_string .= $this->getWhereClauseSQL( $column, [ 'uuid' => $cf_obj->getId(), 'value' => $custom_field_data_requested ], 'custom_field_jsonb_string', $ph );
								break;
						}
					}
				}
			}
		}

		return $query_string;
	}

	/**
	 * Constructs a SQL WHERE clause based on the provided parameters.
	 * This function is used to dynamically build a part of a SQL query that specifies the conditions
	 * that must be met for the rows to be selected. It supports various types of conditions, such as
	 * geographical overlaps, full-text search, and standard SQL comparisons. The function can handle
	 * both simple and complex conditions, including those requiring JSONB operations.
	 *
	 * Supported types:
	 * - 'string': Standard SQL string comparison.
	 * - 'int': Standard SQL integer comparison.
	 * - 'bool': Standard SQL boolean comparison.
	 * - 'date': Date comparison.
	 * - 'full_text': Full-text search.
	 * - 'geo_overlaps': Geographical overlap comparison.
	 * - 'geo_contains': Geographical containment comparison.
	 * - 'jsonb_text_any': JSONB operation for text in any position.
	 * - 'jsonb_text_list': JSONB operation for text list.
	 * - 'custom_field_jsonb_int': JSONB operation for custom field integer.
	 * - 'custom_field_jsonb_float': JSONB operation for custom field float.
	 * - 'custom_field_jsonb_bool': JSONB operation for custom field boolean.
	 * - 'custom_field_jsonb_string': JSONB operation for custom field string.
	 *
	 * @param array|string $columns The column(s) to be used in the WHERE clause.
	 * @param array|string $args The arguments that define the values to be matched in the WHERE clause.
	 * @param string $type The type of condition to be applied (e.g., 'geo_overlaps', 'full_text').
	 * @param array $ph A reference to an array that will be populated with placeholders for prepared statements.
	 * @param string|null $query_stub A partial query string to be used as a base for the WHERE clause.
	 * @param bool $and A boolean indicating whether to concatenate conditions with AND (true) or OR (false).
	 * @return string|null The constructed WHERE clause or null if the arguments are not valid.
	 */
	protected function getWhereClauseSQL( $columns, $args, $type, &$ph, $query_stub = null, $and = true ) {
		//Debug::Text('Type: '. $type .' Query Stub: '. $query_stub .' AND: '. (int)$and, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($columns, 'Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($args, 'Args: ', __FILE__, __LINE__, __METHOD__, 10);
		switch ( strtolower( $type ) ) {
			case 'geo_overlaps':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' && polygon(' . $this->db->qstr( $args ) . ')'; //If we ever pass anything into here *not* from convertGEOPolygonToString(), this needs to changed to use placeholders instead to avoid SQL attacks.
					}
					$retval = $query_stub;
				}
				break;
			case 'geo_contains':
				//Args must always be two elements to make a point.
				if ( isset( $args ) && is_array( $args ) && count( $args ) == 2 && isset( $args[0] ) && isset( $args[1] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$ph[] = $args[0];
						$ph[] = $args[1];
					}

					if ( $query_stub == '' && !is_array( $columns ) ) {
						//$query_stub = 'circle('. $columns .') @> point( ? )'; //Sometimes polygons are passed into this, so we can't convert them to circles.
						$query_stub = $columns . ' @> point( ?, ? )';
					}
					$retval = $query_stub;
				}
				break;
			case 'full_text':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					$split_args = explode( ',', str_replace( [ ' ', ';' ], ',', $args ) ); //Support " " (space) and ";" and ", " as separators.
					if ( is_array( $split_args ) && count( $split_args ) > 0 && $query_stub == '' ) {
						foreach ( $split_args as $key => $arg ) {
							if ( trim( $arg ) != '' ) {
								$ph_arr[] = $this->stripSQLSyntax( TTi18n::strtolower( $arg ) );
							}
						}

						if ( $query_stub == '' && !is_array( $columns ) ) {
							$ph[] = implode( ' & ', $ph_arr );
						}
					}

					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' @@ plainto_tsquery( ? )';
					}
					$retval = $query_stub;
				}
				break;
			case 'string':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' = ?';
					}
					$ph[] = $this->handleSQLSyntax( $args );
					$retval = $query_stub;
				}
				break;
			case 'text':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$columns = [ $columns ];
					}

					if ( $query_stub == '' && is_array( $columns ) && count( $columns ) > 0 ) {
						foreach ( $columns as $column ) {
							$query_stub[] = 'lower(' . $column . ') LIKE ?';
							$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( $args ) );
						}

						$query_stub = '('. implode( ' OR ', $query_stub ) .')'; //Wrap in brackets otherwise it can break queries due to changing logic on clauses appended after.
					}

					$retval = $query_stub;
				}
				break;
			case 'text_metaphone':
				//See also: Option::getByFuzzyValue -- As it tries to replicate this.
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = '( lower(' . $columns . ') LIKE ? OR ' . $columns . '_metaphone LIKE ? )';
					}

					$ph[] = $this->handleSQLSyntax( TTi18n::strtolower( $args ) );
					if ( strpos( $args, '"' ) !== false ) { //ignores metaphone search.
						$ph[] = '';
					} else {
						$ph[] = $this->handleSQLSyntax( metaphone( Misc::stripThe( $args ) ) ); //Strip "The " from metaphones so its easier to find company names.
					}
					$retval = $query_stub;
				}
				break;
			case 'uuid':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' && TTUUID::isUUID( $args ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' = ?';
					}
					$ph[] = TTUUID::castUUID( $args );
					$retval = $query_stub;
				}
				break;
			case 'uuid_list':
			case 'not_uuid_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( TTUUID::getNotExistID(), $args, true ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) { //Check for -1 as well for backwards compatibily with INT ID lists.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						if ( strtolower( $type ) == 'not_uuid_list' ) {
							$query_stub = $columns . ' NOT IN (?)';
						} else {
							$query_stub = $columns . ' IN (?)';
						}
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, 'uuid' ), $query_stub );
				}
				break;
			case 'uuid_list_with_all': //Doesn't check for -1 and ignore the filter completely. Used in KPIListFactory.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}
				if ( isset( $args ) && isset( $args[0] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', 'uuid_list' ) ), $query_stub );
				}
				break;
			case 'text_list':
			case 'lower_text_list':
			case 'upper_text_list':
			case 'not_text_list':
			case 'not_lower_text_list':
			case 'not_upper_text_list':
				if ( !is_array( $args ) ) {
					$args = [ (string)$args ];
				}

				$sql_text_include_or_exclude = 'IN';
				if ( stripos( $type, 'not_' ) !== false ) {
					$sql_text_include_or_exclude = 'NOT IN';
				}

				$sql_text_case_function = null;
				if ( $type == 'upper_text_list' || $type == 'lower_text_list' || $type == 'not_upper_text_list' || $type == 'not_lower_text_list' ) {
					if ( $type == 'upper_text_list' || $type == 'not_upper_text_list' ) {
						$sql_text_case_function = 'UPPER';
						$text_case = CASE_UPPER;
					} else {
						$sql_text_case_function = 'LOWER';
						$text_case = CASE_LOWER;
					}
					$args = array_flip( array_change_key_case( array_flip( $args ), $text_case ) );
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) && !in_array( strtoupper( TTUUID::getNotExistID() ), $args, true ) && !in_array( TTUUID::getNotExistID(), $args, true ) && !in_array( '00', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $sql_text_case_function . '(' . $columns . ') '. $sql_text_include_or_exclude .' (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, 'string' ), $query_stub );
				}

				break;
			case 'province':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) && !in_array( '00', $args ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph ), $query_stub );
				}
				break;
			case 'phone':
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$columns = [ $columns ];
					}

					if ( $query_stub == '' && is_array( $columns ) && count( $columns ) > 0 ) {
						foreach ( $columns as $column ) {
							$query_stub[] = "( replace( replace( replace( replace( replace( replace( " . $column . ", ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') LIKE ? OR " . $column . " LIKE ? )";
							$ph[] = $ph[] = $this->handleSQLSyntax( preg_replace( '/[^0-9\%\*\"]/', '', strtolower( $args ) ) ); //Need the same value twice for the query stub.
						}

						$query_stub = '('. implode( ' OR ', $query_stub ) .')'; //Wrap in brackets otherwise it can break queries due to changing logic on clauses appended after.
					}

					$retval = $query_stub;
				}
				break;
			case 'smallint':
			case 'int':
			case 'bigint':
			case 'numeric':
			case 'numeric_string':
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						if ( $args === null ) {
							$query_stub = $columns . ' is NULL';
						} else {
							$args = $this->castInteger( $this->Validator->stripNonNumeric( $args ), $type );
							if ( is_numeric( $args ) ) {
								$ph[] = $args;
								$query_stub = $columns . ' = ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'smallint_list':
			case 'int_list':
			case 'bigint_list':
			case 'numeric_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', $type ) ), $query_stub );
				}
				break;
			case 'numeric_list_with_all': //Doesn't check for -1 and ignore the filter completely. Used in KPIListFactory.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}
				if ( isset( $args ) && isset( $args[0] ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( '_list', '', 'numeric_list' ) ), $query_stub );
				}
				break;
			case 'not_smallint_list':
			case 'not_int_list':
			case 'not_bigint_list':
			case 'not_numeric_list':
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$query_stub = $columns . ' NOT IN (?)';
					}
					$retval = str_replace( '?', $this->getListSQL( $args, $ph, str_replace( [ 'not_', '_list' ], '', $type ) ), $query_stub );
				}
				break;
			case 'tag':
				//We need company_id and object_type_id passed in.
				if ( isset( $args['company_id'] ) && isset( $args['object_type_id'] ) && isset( $args['tag'] ) ) {
					//Parse the tags search syntax to determine ANY, AND, OR searches.
					$parsed_tags = CompanyGenericTagFactory::parseTags( $args['tag'] );
					//Debug::Arr($parsed_tags, 'Parsed Tags: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( is_array( $parsed_tags ) ) {
						$retval = '';
						if ( isset( $parsed_tags['add'] ) && count( $parsed_tags['add'] ) > 0 ) {
							$query_stub = ' EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = \'' . TTUUID::castUUID( $args['company_id'] ) . '\'
															AND cgtm.object_type_id = ' . (int)$args['object_type_id'] . '
															AND ' . $columns . ' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = ' . count( $parsed_tags['add'] ) . '
													)';
							$retval .= str_replace( '?', $this->getListSQL( Misc::arrayChangeValueCase( $parsed_tags['add'] ), $ph ), $query_stub );
							if ( isset( $parsed_tags['delete'] ) && count( $parsed_tags['delete'] ) > 0 ) {
								$retval .= ' AND ';
							}
						}

						if ( isset( $parsed_tags['delete'] ) && count( $parsed_tags['delete'] ) > 0 ) {
							$query_stub = ' NOT EXISTS	(
														select 1
														from company_generic_tag_map as cgtm
														INNER JOIN company_generic_tag as cgt ON (cgtm.tag_id = cgt.id)
														WHERE cgt.company_id = \'' . TTUUID::castUUID( $args['company_id'] ) . '\'
															AND cgtm.object_type_id = ' . (int)$args['object_type_id'] . '
															AND ' . $columns . ' = cgtm.object_id
															AND ( lower(cgt.name) in (?) )
														group by cgtm.object_id
														HAVING COUNT(*) = ' . count( $parsed_tags['delete'] ) . '
													)';
							$retval .= str_replace( '?', $this->getListSQL( Misc::arrayChangeValueCase( $parsed_tags['delete'] ), $ph ), $query_stub );
						}
					}
				}
				if ( !isset( $retval ) ) {
					$retval = '';
				}
				break;
			case 'date_stamp': //Input epoch values, but convert bind to datestamp for datastamp datatypes.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( -1, $args, true ) && !in_array( '-1', $args, true ) ) {
					foreach ( $args as $tmp_arg ) {
						if ( TTDate::isValidDate( $tmp_arg ) ) {
							$converted_args[] = $this->db->bindDate( (int)$tmp_arg );
						}
					}

					if ( isset( $converted_args ) && count( $converted_args ) > 0 ) {
						if ( $query_stub == '' && !is_array( $columns ) ) {
							$query_stub = $columns . ' IN (?)';
						}
						$retval = str_replace( '?', $this->getListSQL( $converted_args, $ph ), $query_stub );
					}
				}
				break;
			case 'start_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_datestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $this->db->bindDate( $args );
							if ( strtolower( $type ) == 'start_datestamp' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
			case 'end_timestamp': //Uses EPOCH values only, used for date/timestamp datatype columns
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $this->db->bindTimeStamp( $args );
							if ( strtolower( $type ) == 'start_timestamp' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'start_date': //Uses EPOCH values only, used for integer datatype columns
			case 'end_date':
				if ( !is_array( $args ) ) { //Can't check isset() on a NULL value.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$args = $this->castInteger( $this->Validator->stripNonFloat( $args ), 'int' ); //Make sure we allow decimals
						if ( is_numeric( $args ) && TTDate::isValidDate( $args ) ) {
							$ph[] = $args;
							if ( strtolower( $type ) == 'start_date' ) {
								$query_stub = $columns . ' >= ?';
							} else {
								$query_stub = $columns . ' <= ?';
							}
						}
					}

					$retval = $query_stub;
				}
				break;
			case 'date_range': //Uses EPOCH values only, used for integer datatype columns
			case 'date_range_include_blank': //Include NULL/Blank dates.
			case 'date_range_datestamp':
			case 'date_range_datestamp_include_blank': //Include NULL/Blank dates.
			case 'date_range_timestamp': //Uses text timestamp values, used for timestamp datatype columns
			case 'date_range_timestamp_include_blank': //Include NULL/Blank dates.
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						$include_blank_dates = ( strpos( $type, '_include_blank' ) !== false ) ? true : false;
						switch ( $type ) {
							case 'date_range_timestamp':
							case 'date_range_timestamp_include_blank':
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'timestamp', $include_blank_dates );
								break;
							case 'date_range_datestamp':
							case 'date_range_datestamp_include_blank':
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'datestamp', $include_blank_dates );
								break;
							default:
								$query_stub = $this->getDateRangeSQL( $args, $columns, 'epoch', $include_blank_dates );
								break;
						}
					}
					//Debug::Text('Query Stub: '. $query_stub, __FILE__, __LINE__, __METHOD__, 10);
					$retval = $query_stub;
				}
				break;
			case 'user_id_or_name':
				if ( isset( $args ) && is_array( $args ) ) {
					$retval = $this->getWhereClauseSQL( $columns[0], $args, 'uuid_list', $ph, '', false );
				}
				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) {
					$ph[] = $ph[] = $this->handleSQLSyntax( TTi18n::strtolower( trim( $args ) ) );
					$retval = '(lower(' . $columns[1] . ') LIKE ? OR lower(' . $columns[2] . ') LIKE ? ) ';
				}
				break;
			case 'boolean':
				if ( is_bool( $args ) ) { //Handle strict boolean types here, convert to strings to be matched later on.
					if ( $args === true ) {
						$args = 'true';
					} else {
						$args = 'false';
					}
				} else if ( is_int( $args ) ) { //Handle strict integer types here, convert to strings to be matched later on.
					if ( $args === 1 ) {
						$args = 'true';
					} else {
						$args = 'false';
					}
				}

				if ( isset( $args ) && !is_array( $args ) && trim( $args ) != '' ) { // trim($args) != '' won't match (bool)FALSE. So it must be changed to a string above.
					if ( $query_stub == '' && !is_array( $columns ) ) {
						switch ( strtolower( trim( (string)$args ) ) ) { //Cast to string here is critical for the below CASE's to work properly.
							//Can't check for (int)1 or (bool)TRUE here as it matches even with (bool)FALSE. DocumentList passes (bool)FALSE for handling private documents.
							case '1':
							case 'yes':
							case 'y':
							case 'true':
							case 't':
							case 'on':
								$ph[] = 1;
								$query_stub = $columns . ' = ?';
								break;
							case '0':
							case 'no':
							case 'n':
							case 'false':
							case 'f':
							case 'off':
								$ph[] = 0;
								$query_stub = $columns . ' = ?';
								break;
							default:
								Debug::Text( 'Invalid boolean value: ' . $args, __FILE__, __LINE__, __METHOD__, 10 );
								break;
						}
					}
					$retval = $query_stub;
				}
				break;
			case 'jsonb_text_any':
				//jsonb_text_any can be used when searching on a json string value against an array of strings. This is an OR check.
				//It cannot be used on json arrays and jsonb_text_list should be used instead.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( TTUUID::getNotExistID(), $args, true ) ) {
					foreach ( $args as $arg ) {
						$ph_arr[] = '?';
						$ph[] = (string)$arg;
					}

					$query_stub = ' ' . $columns . ' = ANY(array[' . implode( ',', $ph_arr ) . '])';

					$retval = $query_stub;
				}
				break;
			case 'jsonb_uuid_array':
			case 'jsonb_text_list':
				//jsonb_uuid_array and jsonb_text_list can be used on a json array against an array of strings. This is an AND check.
				//The json array must contain All the strings in the search array, but can contain more.
				//To search against a json string instead of a json array, use jsonb_text_any instead.
				if ( !is_array( $args ) ) {
					$args = (array)$args;
				}

				//Always use strict mode when calling in_array(), otherwise if we pass in array( (int)0 ) as the args, it will match in_array( TTUUID::getNotExistID(), $args ).
				if ( isset( $args ) && isset( $args[0] ) && !in_array( TTUUID::getNotExistID(), $args, true ) ) {
					foreach ( $args as $arg ) {
						$ph_arr[] = '?';
						if ( strtolower( $type ) == 'jsonb_uuid_array' ) {
							$ph[] = TTUUID::castUUID( $arg );
						} else {
							$ph[] = (string)$arg;
						}
					}

					//Use jsonb_exists_any for an OR condition, and jsonb_exists_all for an AND condition.
					//Using jsonb_exists_all as it is an alias of the ?| operator. Helps migigate issues when using prepared statements and placeholders.
					$query_stub = ' jsonb_exists_all(' . $columns . '::jsonb, array[' . implode( ',', $ph_arr ) . '])';

					$retval = $query_stub;
				}
				break;
			case 'custom_field_jsonb_int':
			case 'custom_field_jsonb_float':
			case 'custom_field_jsonb_string':
			case 'custom_field_jsonb_bool':
				$needs_quotes = true;

				if ( strtolower( $type ) == 'custom_field_jsonb_int' ) {
					$args['value'] = (int)$args['value'];
					$needs_quotes = false;
				} else if ( strtolower( $type ) == 'custom_field_jsonb_float' ) {
					$args['value'] = (float)$args['value'];
					$needs_quotes = false;
				} else if ( strtolower( $type ) == 'custom_field_jsonb_bool' ) {
					$args['value'] = $this->toJSONBool( $args['value'] );
					$needs_quotes = false;
				} else if ( strtolower( $type ) == 'custom_field_jsonb_string' ) {
					$args['value'] = (string)$args['value'];
				}

				if ( isset( $args ) && is_array( $args ) ) {
					if ( $query_stub == '' && !is_array( $columns ) ) {
						if ( $needs_quotes == true ) {
							$quoted_value = '"' . $this->db->escape( $args['value'] ) . '"';
						} else {
							$quoted_value = $this->db->escape( $args['value'] );
						}

						$query_stub = ' ' . $columns . ' @> \'{"' . $args['uuid'] . '": ' . $quoted_value . '}\'';
					}

					//$ph[] = $this->handleSQLSyntax( $args['value'] );
					$retval = $query_stub;
				}
				break;
			default:
				Debug::Text( 'Invalid type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
				break;
		}

		if ( isset( $retval ) ) {
			$and_sql = null;
			if ( $and == true && $retval != '' ) { //Don't prepend ' AND' if there is nothing to come after it.
				$and_sql = 'AND ';
			}

			//Debug::Arr($ph, 'Query Stub: '. $and_sql.$retval, __FILE__, __LINE__, __METHOD__, 10);
			return ' ' . $and_sql . $retval . ' '; //Wrap each query stub in spaces.
		}

		return null;
	}

	/**
	 * Parses out the exact column name, without any aliases, or = signs in it.
	 * This is used to sanitize and standardize column names for use in SQL queries,
	 * ensuring that only valid column names are processed to prevent SQL injection.
	 * The function strips away any SQL injection attack vectors while allowing
	 * certain SQL expressions like ordering or casting.
	 *
	 * @param string $column The column name to be sanitized.
	 * @return string|false The sanitized column name if valid, or false if the input is potentially malicious.
	 */
	private function parseColumnName( $column ) {
		$column = trim( $column );

		//Make sure there isn't a SQL injection attack here, but still allow things like: "order by a.column = 1 asc" or "order by a.column::int asc"
		//  Example attack vectors:
		// 		'(SELECT 1)-- .id.' => 1
		//  This may cause problems if we want to use a function in sorting though.
		if ( preg_match( '/^([a-z0-9_:\-\=\.\ ]+)$/i', $column ) !== 1 ) {
			if ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) {
				trigger_error( 'ERROR: Invalid column name: ' . $column ); //Trigger error so we can get feedback of any problems or potential attacks.
			} else {
				Debug::Text( 'ERROR: Invalid column name: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return false;
		}

		if ( strpos( $column, '=' ) !== false ) {
			$retval = trim( explode( '=', $column )[0] );
		} else {
			$retval = $column;
		}

		if ( strpos( $retval, '.' ) !== false ) {
			$retval = explode( '.', $retval )[1];
		}

		if ( strpos( $retval, '::' ) !== false ) { //Casting, ie: employee_number::int
			$retval = explode( '::', $retval )[0];
		}

		//Debug::Text('Column: '. $column .' RetVal: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * Constructs a SQL WHERE clause from a given associative array of conditions.
	 * The array keys represent column names and the values are the conditions to apply.
	 * The function ensures that only valid columns, as determined by the record set's column list,
	 * are included in the resulting SQL clause. If $append_where is true, the clause is prefixed
	 * with 'WHERE', otherwise with 'AND'. This allows for the dynamic construction of query filters
	 * based on varying input criteria.
	 *
	 * @param array $array Associative array of column names to conditions.
	 * @param bool $append_where Determines whether to prefix the clause with 'WHERE' (true) or 'AND' (false).
	 * @return bool|string The constructed SQL WHERE clause, or false if the input array is not valid.
	 */
	protected function getWhereSQL( $array, $append_where = false ) {
		//Make this a multi-dimensional array, the first entry
		//is the WHERE clauses with '?' for placeholders, the second is
		//the array to replace the placeholders with.
		if ( is_array( $array ) ) {
			$rs = $this->getEmptyRecordSet();
			$fields = $this->getRecordSetColumnList( $rs );

			if ( is_array( $fields ) ) {
				foreach ( $array as $orig_column => $expression ) {
					if ( is_array( $expression ) ) { //Handle nested arrays, so we the same column can be specified multiple times.
						foreach ( $expression as $tmp_orig_column => $tmp_expression ) {
							$tmp_orig_column = trim( $tmp_orig_column );
							$column = $this->parseColumnName( $tmp_orig_column );
							$tmp_expression = trim( $tmp_expression );

							if ( in_array( $column, $fields ) ) {
								$sql_chunks[] = $tmp_orig_column . ' ' . $tmp_expression;
							}
						}
					} else {
						$orig_column = trim( $orig_column );
						$column = $this->parseColumnName( $orig_column );
						$expression = trim( $expression );

						if ( in_array( $column, $fields ) ) {
							$sql_chunks[] = $orig_column . ' ' . $expression;
						}
					}
				}
			}

			if ( isset( $sql_chunks ) ) {
				//Don't escape this, as prevents quotes from being used in cases where they are required link bindTimeStamp
				//$sql = $this->db->escape( implode(' AND ', $sql_chunks) );
				$sql = implode( ' AND ', $sql_chunks );

				if ( $append_where == true ) {
					return ' WHERE ' . $sql;
				} else {
					return ' AND ' . $sql;
				}
			}
		}

		return false;
	}

	/**
	 * Transforms an array of column names based on a set of alias mappings.
	 *
	 * This function takes an array of column names and an associative array of aliases,
	 * and replaces the column names with their corresponding aliases. If an alias is not
	 * provided for a specific column, the original column name is retained. This is useful
	 * for mapping frontend column names to their actual database column names before
	 * constructing SQL queries.
	 *
	 * @param array $columns The original array of column names to be transformed.
	 * @param array $aliases An associative array mapping original column names to their aliases.
	 * @return array The transformed array of column names with aliases applied.
	 */
	protected function getColumnsFromAliases( $columns, $aliases ) {
		// Columns is the original column array.
		//
		// Aliases is an array of search => replace key/value pairs.
		//
		// This is used so the frontend can sort by the column name (ie: type) and it can be converted to type_id for the SQL query.
		if ( is_array( $columns ) && is_array( $aliases ) ) {
			$columns = $this->convertFlexArray( $columns );

			//Debug::Arr($columns, 'Columns before: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach ( $columns as $column => $sort_order ) {
				if ( isset( $aliases[$column] ) && !isset( $columns[$aliases[$column]] ) ) {
					if ( $aliases[$column] != '' ) { //If the alias column is set to null/false, just ignore that sort column completely.
						$retarr[$aliases[$column]] = $sort_order;
					}
				} else {
					$retarr[$column] = $sort_order;
				}
			}
			//Debug::Arr($retarr, 'Columns after: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $retarr ) ) {
				return $retarr;
			}
		}

		return $columns;
	}

	/**
	 * Converts an array structure from a legacy system to a standardized associative array.
	 * The legacy system used a non-standard format for sorting data, which this function
	 * normalizes into a consistent associative array format where the key is the column name
	 * and the value is the sort order. This is necessary for compatibility with newer systems
	 * that expect a standard array format.
	 *
	 * @param array $array The array to be converted, originating from the legacy system.
	 * @return array The converted array in a standardized associative format.
	 */
	function convertFlexArray( $array ) {
		//NOTE: This needs to stick around to handle saved search & layouts created in Flex and still in use.
		//Flex doesn't appear to be consistent on the order the fields are placed into an assoc array, so
		//handle this type of array too:
		// array(
		//		0 => array('first_name' => 'asc')
		//		1 => array('last_name' => 'desc')
		//		)

		if ( isset( $array[0] ) && is_array( $array[0] ) ) {
			Debug::text( 'Found Flex Sort Array, converting to proper format...', __FILE__, __LINE__, __METHOD__, 10 );

			//Debug::Arr($array, 'Before conversion...', __FILE__, __LINE__, __METHOD__, 10);

			$new_arr = [];
			foreach ( $array as $tmp_order => $tmp_arr ) {
				if ( is_array( $tmp_arr ) ) {
					foreach ( $tmp_arr as $tmp_column => $tmp_order ) {
						$new_arr[$tmp_column] = $tmp_order;
					}
				}
			}
			$array = $new_arr;
			unset( $tmp_key, $tmp_arr, $tmp_order, $tmp_column, $new_arr );
			//Debug::Arr($array, 'Converted format...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $array;
	}

	/**
	 * Filters and validates SQL column names against a list of known columns.
	 * This function ensures that only valid column names are returned, which is important
	 * for preventing SQL injection attacks and ensuring database integrity. If $strict is true,
	 * only columns that exist in the database are returned. If $strict is false, all columns
	 * are returned. Additional fields can be merged into the final list of columns.
	 *
	 * @param array $array An associative array where keys are column names and values are expressions.
	 * @param bool $strict If true, only columns that exist in the database are returned.
	 * @param array|null $additional_fields An array of additional fields to merge with the column list.
	 * @return array An associative array of valid SQL columns.
	 * @throws Exception If an invalid column is found and the system is in UNIT_TEST_MODE.
	 */
	public function getValidSQLColumns( $array, $strict = true, $additional_fields = null ) {
		$retarr = [];

		$fields = $this->getRecordSetColumnList( $this->getEmptyRecordSet() );

		if ( in_array( 'custom_field', $fields ) ) {
			global $current_company;
			if ( isset( $current_company ) && is_object( $current_company ) ) {
				$custom_field_columns = $this->getCustomFieldsColumns( [], $current_company->getId(), false );

				$fields = array_merge( $fields, array_keys( $custom_field_columns ) );
			}
		}

		//Merge additional fields
		if ( is_array( $fields ) && is_array( $additional_fields ) ) {
			$fields = array_merge( $fields, $additional_fields );
		}
		//Debug::Arr($fields, 'Column List:', __FILE__, __LINE__, __METHOD__, 10);

		foreach ( $array as $orig_column => $expression ) {
			$orig_column = trim( $orig_column );

			if ( $strict == false ) {
				$retarr[$orig_column] = $expression;
			} else {
				if ( in_array( $orig_column, $fields ) ) {
					$retarr[$orig_column] = $expression;
				} else {
					$column = $this->parseColumnName( $orig_column );
					if ( in_array( $column, $fields ) ) {
						$retarr[$orig_column] = $expression;
					} else {
						Debug::text( 'Invalid Column: ' . $orig_column, __FILE__, __LINE__, __METHOD__, 10 );
						if ( defined( 'UNIT_TEST_MODE' ) && UNIT_TEST_MODE === true ) {
							throw new Exception( 'Invalid column: ' . $orig_column );
						}
					}
				}
			}
		}

		//Debug::Arr($retarr, 'Valid Columns: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retarr;
	}

	/**
	 * Retrieves a list of valid SQL columns based on the provided array.
	 * This function checks the given array against the set of valid columns for the record set.
	 * If $strict is true, it filters out any columns not found in the record set's column list.
	 * If $strict is false, it includes all columns from the input array.
	 * Additional fields can be merged into the final list if provided.
	 * This is useful for validating and preparing an array of columns for SQL operations,
	 * ensuring that only valid columns are included in the query.
	 *
	 * @param null|array $array An associative array where keys are column names and values are expressions.
	 * @param bool $strict Whether to strictly filter columns against the record set's column list.
	 * @param array|null $additional_fields An array of additional fields to merge with the column list.
	 * @return bool|string SQL clause for sorting.
	 */
	protected function getSortSQL( $array, $strict = true, $additional_fields = null ) {
		if ( is_array( $array ) ) {
			$sql_reserved_words = [ 'group' ];

			//Disabled in v10 to start migrating away from FlexArray formats.
			//  This is still needed, as clicking on a column header to sort by that seems to use the wrong format.
			$array = $this->convertFlexArray( $array );

			$alt_order_options = [ 1 => 'ASC', -1 => 'DESC' ];
			$order_options = [ 'ASC', 'DESC' ];

			$valid_columns = $this->getValidSQLColumns( $array, $strict, $additional_fields );
			if ( is_array( $valid_columns ) ) {
				foreach ( $valid_columns as $orig_column => $order ) {
					$order = trim( strtoupper( $order ) );
					//Handle both order types.
					if ( is_numeric( $order ) ) {
						if ( isset( $alt_order_options[$order] ) ) {
							$order = $alt_order_options[$order];
						}
					}

					if ( $strict == false || in_array( $order, $order_options ) ) {

						if ( $strict == false ) {
							//Out of abundance of caution removing certain characters to reduce SQL injection risk.
							$orig_column = str_replace( [ ';', '-', '$', '"', "'" ], '', $orig_column );
						}

						if ( in_array( $orig_column, $sql_reserved_words ) ) { //Quote reserved words such as 'group'.
							$orig_column = '"' . $orig_column . '"';
						}

						if ( strpos( $orig_column, 'custom_field-' ) !== false ) {
							$custom_field_id = str_replace( 'custom_field-', '', $orig_column );
							//Using $$ to quite custom_field_id because $this->db->escape( $sql ) would escape the single quotes below.
							$sql_chunks[] = 'a.custom_field->$$' . $custom_field_id . '$$ ' . $order;
						} else {
							$sql_chunks[] = $orig_column . ' ' . $order;
						}
					} else {
						Debug::text( 'Invalid Sort Order: ' . $orig_column . ' Order: ' . $order, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}

			if ( isset( $sql_chunks ) ) {
				$sql = implode( ',', $sql_chunks );
				//We can't escape the quotes needed to order by specific UUID's such as UUID_ZERO...
				//For example: ScheduleListFactory::getSearchByCompanyIdAndArrayCriteria()
				if ( $strict === false ) {
					return ' ORDER BY ' . $sql;
				} else {
					return ' ORDER BY ' . $this->db->escape( $sql );
				}
			}
		}

		return false;
	}

	/**
	 * Retrieves the list of valid columns for the current data set.
	 * This method filters out any columns that are not directly part of the table
	 * or are not mapped to a function in the object. It also ensures that deleted
	 * or updated date and user columns are included when appropriate.
	 *
	 * @return bool|array The list of valid columns, or false if no valid data is present.
	 */
	public function getColumnList() {
		if ( is_array( $this->data ) && count( $this->data ) > 0 ) {
			//Possible errors can happen if $this->data[<invalid_column>] is passed to save/update the database,
			//like what happens with APIPunch when attempting to delete a punch.

			//Remove all columns that are not directly part of the table itself, or those mapped not mapped to a function in the object.
			$variable_to_function_map = $this->getVariableToFunctionMap();
			if ( is_array( $variable_to_function_map ) ) {
				foreach ( $variable_to_function_map as $variable => $function ) {
					if ( $function !== false ) {
						$valid_column_list[] = $variable;
					}
				}
				$column_list = array_intersect( $valid_column_list, array_keys( $this->data ) );
			} else {
				$column_list = array_keys( $this->data );
			}
			unset( $variable_to_function_map, $variable, $function );

			if ( $this->getDeleted() == true ) {
				//Make sure if the record is deleted we update the deleted columns.
				if ( $this->getDeletedDate() !== false ) {
					$column_list[] = 'deleted_date';
				}
				if ( $this->getDeletedBy() !== false ) {
					$column_list[] = 'deleted_by';
				}
			} else {
				//Don't set updated_date when deleting records, we use deleted_date/deleted_by for that.
				if ( $this->getUpdatedDate() !== false ) {
					$column_list[] = 'updated_date';
				}
				if ( $this->getUpdatedBy() !== false ) {
					$column_list[] = 'updated_by';
				}
			}

			//Make sure we always add 'other_json' to the SQL column list if its specified.
			if ( isset( $this->data['other_json'] ) ) {
				$column_list[] = 'other_json';
			}

			if ( isset( $this->data['custom_field'] ) ) {
				$column_list[] = 'custom_field';
			}

			$column_list = array_unique( $column_list );

			//Debug::Arr($this->data, 'aColumn List', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($column_list, 'bColumn List', __FILE__, __LINE__, __METHOD__, 10);

			return $column_list;
		}

		return false;
	}

	/**
	 * Retrieves an empty record set for a given UUID or a default one if no UUID is provided.
	 * This function is typically used to initialize data structures for new records or to ensure
	 * a consistent starting point for operations that require a record set. If no UUID is provided,
	 * a default 'where' clause is used to prevent selection of any existing records.
	 *
	 * @param string|null $id The UUID of the record to retrieve an empty record set for, or null to use the default 'where' clause.
	 * @return mixed Returns an empty record set corresponding to the provided UUID, or false on failure.
	 * @throws DBError If a database error occurs while retrieving the record set.
	 */
	public function getEmptyRecordSet( $id = null ) {
		global $profiler, $config_vars;
		$profiler->startTimer( 'getEmptyRecordSet()' );

		if ( $id == null ) {
			$where_clause = 'FALSE'; //Was: TTUUID::getNotExistID(); //Was $id = -1 -- This helps avoid failures in serializable mode as no data is actually selected.
		} else {
			$where_clause = 'id = \'' . TTUUID::castUUID( $id ) . '\'';
		}

		//Possible errors can happen if $this->data[<invalid_column>] is passed, like what happens with APIPunch when attempting to delete a punch.
		//Why are we not using '*' for all empty record set queries? Will using * cause more fields to be updated then necessary?
		//Yes, it will, as well the updated_by/updated_date fields aren't controllable by getColumnList() then either.
		//Therefore any ListFactory queries used to potentially delete data should only include columns from its own table,
		//Or collect the IDs and use bulkDelete instead.
		//**getColumnList() now only returns valid table columns based on the variable to function map.
		$column_list = $this->getColumnList();

		//ignore_column_list can be set in InstallSchema files to prevent column names from being used which may cause SQL errors during upgrade process.
		if ( is_array( $column_list ) && !isset( $this->ignore_column_list ) ) {
			//Implode columns.
			$column_str = implode( ',', $column_list );
		} else {
			$column_str = '*'; //Get empty RS with all columns.
		}

		$query = 'SELECT ' . $column_str . ' FROM ' . $this->table . ' WHERE ' . $where_clause;
		if ( $id == null && isset( $config_vars['cache']['enable'] ) && $config_vars['cache']['enable'] == true ) {
			//When caching empty record sets, always write to persistent cache as it doesn't matter if we are inside a retry transaction for this or not, this data will always be the same.
			//$current_cache_memory_state = $this->cache->_onlyMemoryCaching;
			//if ( $current_cache_memory_state == false && ( !isset( $config_vars['cache']['only_memory_cache_enable'] ) || $config_vars['cache']['only_memory_cache_enable'] == false ) ) {
			//	$this->cache->_onlyMemoryCaching = false;
			//}

			//Try to use Cache Lite instead of ADODB, to avoid cache write errors from causing a transaction rollback, especially important for serializable transactions. It should be faster too.
			$cache_id = 'empty_rs_' . $this->table; //No need to add $id to the end as its always NULL here, but we may need to handle different columns that may be passed in with a md5() perhaps?
			$rs = $this->getCache( $cache_id );
			if ( $rs === false ) {
				$rs = $this->ExecuteSQL( $query );
				$rs = $this->db->_rs2rs( $rs );                           //Needed to include the _fieldObjects property for ADODB.
				$this->saveCache( $this->serializeRS( $rs ), $cache_id ); //Only run serializeRS() when passing to saveCache() otherwise it corrupts the $rs being returned in this function.
			}

			//$this->cache->_onlyMemoryCaching = $current_cache_memory_state;


//			try {
//				$save_error_handlers = $this->db->IgnoreErrors(); //Prevent a cache write error from causing a transaction rollback.
//				$rs = $this->db->CacheExecute(604800, $query);
//				$this->db->IgnoreErrors( $save_error_handlers ); //Prevent a cache write error from causing a transaction rollback.
//			} catch ( Exception $e ) {
//				if ( $e->getCode() == -32000 OR $e->getCode() == -32001 ) { //Cache write error/cache file lock error.
//					//Likely a cache write error occurred, fall back to non-cached query and log this error.
//					Debug::Text('ERROR: Unable to write cache file, likely due to permissions or locking! Code: '. $e->getCode() .' Msg: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
//				}
//
//				//Execute non-cached query
//				$rs = $this->ExecuteSQL( $query );
//			}
		} else {
			$rs = $this->ExecuteSQL( $query );
		}

		$profiler->stopTimer( 'getEmptyRecordSet()' );

		return $rs;
	}

	/**
	 * Retrieves an empty record set from the database for the current table.
	 * This function is typically used to initialize a record set with the correct structure
	 * but without any actual data from the database. It can be useful for creating new entries
	 * that will later be filled with data and saved back to the database.
	 * If caching is enabled and the record set is already cached, it will be retrieved from the cache.
	 * Otherwise, a new record set will be generated by executing a SQL query.
	 *
	 * @return mixed Returns a record set object on success, or false on failure.
	 * @throws DBError Throws an exception if there is an error executing the database query.
	 */
	private function getUpdateQuery() {
		//Debug::text('Update table: '. $this->getTable(), __FILE__, __LINE__, __METHOD__, 9);

		//
		// If the table has timestamp columns without timezone set
		// this function will think the data has changed, and update it.
		// PayStubFactory() had this issue.
		//

		//Debug::arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);

		//Add new columns to record set.
		//Check to make sure the columns exist in the table first though
		//Classes like station don't have updated_date, so we need to take that in to account.
		$rs = $this->getEmptyRecordSet( $this->getId() );
		//Set old_data in FactoryListIterator->getCurrent() instead, that way getDataDfifferences() can be used in Validate/preSave functions as well.
		//$this->old_data = $rs->fields; //Store old data in memory for detailed audit log.

		if ( !$rs ) {
			Debug::text( 'No Record Found! (ID: ' . $this->getID() . ') Insert instead?', __FILE__, __LINE__, __METHOD__, 9 );
			//Throw exception?
		}

		//Debug::Arr($rs->fields, 'RecordSet: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr($this->data, 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);
		//Debug::Arr( array_diff_assoc($rs->fields, $this->data), 'Data Array: ', __FILE__, __LINE__, __METHOD__, 9);

		//If no columns changed, this will be FALSE.
		$query = $this->db->GetUpdateSQL( $rs, $this->data );

		//No updates are fine. We still want to run postsave() etc...
		if ( $query === false ) {
			$query = true;
		} else {
			Debug::text( 'Data changed, set updated date: ', __FILE__, __LINE__, __METHOD__, 9 );
		}

		//Debug::text('Update Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	/**
	 * Constructs and returns an update SQL query for the current table.
	 *
	 * This method generates an SQL query that can be used to update the current table
	 * with the data stored in the $this->data property. It compares the current data
	 * against an empty record set to determine which columns have changed and need to be updated.
	 * If no changes are detected, it returns true to indicate that no update query is necessary,
	 * but post-save operations should still be executed.
	 *
	 * @return string|bool The update SQL query string or true if no update is needed.
	 * @throws DBError If the record set cannot be retrieved or another database error occurs.
	 */
	private function getInsertQuery() {
		//Debug::text( 'Insert table: '. $this->getTable(), __FILE__, __LINE__, __METHOD__, 9 );

		//Debug::Arr($this->data, 'Data Arr', __FILE__, __LINE__, __METHOD__, 10);\

		//This prevents SQL errors (ie: NULL columns when they shouldn't be) caused by only certain columns being cached in the empty record set, and therefore being ignored in the INSERT query.
		$this->ignore_column_list = true;
		$rs = $this->getEmptyRecordSet();
		$this->ignore_column_list = false;

		if ( !$rs ) {
			Debug::text( 'ERROR: Unable to get empty record set for insert!', __FILE__, __LINE__, __METHOD__, 9 );
			//Throw exception?
		}

		$query = $this->db->GetInsertSQL( $rs, $this->data );

		$query = $this->modifyInsertQuery( $query );
		//Debug::text('Insert Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		return $query;
	}

	/**
	 * Placeholder for extending the SQL INSERT query with additional clauses.
	 * This method can be overridden in subclasses to modify the generated SQL INSERT query,
	 * for example, to add clauses like "ON CONFLICT ..." for upsert operations in PostgreSQL.
	 * Currently, it simply returns the input query unmodified.
	 *
	 * @param string $query The original SQL INSERT query to be potentially modified.
	 * @return string The modified or unmodified SQL INSERT query.
	 */
	function modifyInsertQuery( $query ) {
		return $query;
	}

	/**
	 * Initiates a database transaction.
	 * This function starts a new transaction in the database, allowing multiple operations
	 * to be executed as a single atomic operation. This ensures that either all operations
	 * within the transaction are committed to the database, or none are if an error occurs,
	 * maintaining data integrity.
	 *
	 * @return bool True on success, false on failure.
	 */
	function StartTransaction() {
		Debug::text( 'StartTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );

		return $this->db->StartTrans();
	}

	/**
	 * Clears the cache of records saved within a transaction.
	 * This function is typically called after a transaction fails to ensure that
	 * any records cached during the transaction are removed to prevent stale data.
	 * It iterates over the cached record IDs stored in `__transaction_cache_ids`
	 * and removes each from the cache. It also ensures that the cache does not
	 * retain these IDs for future transactions by unsetting them after removal.
	 *
	 * @return bool Always returns true, indicating the cache clearing process was called.
	 */
	function clearCacheSavedInTransaction() {
		if ( isset( $this->cache->__transaction_cache_ids ) && is_array( $this->cache->__transaction_cache_ids ) && count( $this->cache->__transaction_cache_ids ) > 0 ) {
			Debug::text( 'Removing cached records created during failed transaction: Count: ' . count( $this->cache->__transaction_cache_ids ), __FILE__, __LINE__, __METHOD__, 9 );
			foreach( $this->cache->__transaction_cache_ids as $cache_id_group_id => $args ) {
				$this->removeCache( $args[0], $args[1] );

				//Remove record from __transaction_cache_ids so in long runnings processes (ie: Job Queue) they don't start to stack up and try to get removed multiple times.
				if ( isset( $this->cache->__transaction_cache_ids[$cache_id_group_id] ) ) {
					unset( $this->cache->__transaction_cache_ids[$cache_id_group_id] );
				}
			}
		}

		return true;
	}

	/**
	 * Queues cache removals to occur after a transaction is committed.
	 *
	 * This method is designed to prevent cache corruption that could occur if cache records
	 * are removed during a transaction. By queuing the removals until after the transaction
	 * is committed, it ensures that any cache records that are re-created are based on the
	 * most recent and correct database records. Although there is a brief period where old
	 * data may be retrieved from the cache, this window is very short and should not pose
	 * significant issues.
	 *
	 * @return bool Always returns true, indicating the cache removals have been queued.
	 */
	function clearCacheRemovedInTransaction() {
		if ( isset( $this->cache->__transaction_remove_cache_ids ) && is_array( $this->cache->__transaction_remove_cache_ids ) && count( $this->cache->__transaction_remove_cache_ids ) > 0 ) {
			Debug::text( 'Removing cached records pending removal during successful transaction: Count: ' . count( $this->cache->__transaction_remove_cache_ids ), __FILE__, __LINE__, __METHOD__, 9 );
			foreach ( $this->cache->__transaction_remove_cache_ids as $cache_id_group_id => $args ) {
				$this->removeCache( $args[0], $args[1] );

				//Remove record from __transaction_remove_cache_ids so in long runnings processes (ie: Job Queue) they don't start to stack up and try to get removed multiple times.
				unset( $this->cache->__transaction_remove_cache_ids[$cache_id_group_id] );
			}
		}

		if ( isset( $this->cache->__transaction_remove_cache_group_ids ) && is_array( $this->cache->__transaction_remove_cache_group_ids ) && count( $this->cache->__transaction_remove_cache_group_ids ) > 0 ) {
			Debug::text( 'Removing cached group records pending removal during successful transaction: Count: ' . count( $this->cache->__transaction_remove_cache_group_ids ), __FILE__, __LINE__, __METHOD__, 9 );
			foreach ( $this->cache->__transaction_remove_cache_group_ids as $group_id => $dummy ) {
				$this->removeCache( null, $group_id );

				//Remove record from __transaction_remove_cache_group_ids so in long runnings processes (ie: Job Queue) they don't start to stack up and try to get removed multiple times.
				unset( $this->cache->__transaction_remove_cache_group_ids[$group_id] );
			}
		}

		return true;
	}

	/**
	 * Initiates a transaction failure process.
	 *
	 * This function is called to indicate that a database transaction should be
	 * considered failed. It triggers the rollback of the transaction and clears
	 * any cached data that was saved during the transaction to ensure consistency.
	 * It is typically used in exception handling where an operation within a
	 * transaction cannot be completed successfully, necessitating a rollback to
	 * the state before the transaction began.
	 *
	 * @return bool Returns true on successful rollback of the transaction.
	 */
	function FailTransaction() {
		Debug::text( 'FailTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );

		$retval = $this->db->FailTrans();
		$this->clearCacheSavedInTransaction(); //Fail transaction first so its not kept open as long.
		return $retval;
	}

	/**
	 * Commits a database transaction, with the option to unnest nested transactions.
	 * This function attempts to commit the current database transaction. If the $unnest_transactions
	 * parameter is true and the transaction has failed, it will attempt to unnest (complete) any
	 * nested transactions until the transaction count reaches zero. If the final outer transaction
	 * is committed without any failures, it will clear any pending cache removals. If an exception
	 * occurs during the commit, it will be caught and handled according to its type, potentially
	 * triggering a retry if the exception is deemed retryable.
	 *
	 * @param bool $unnest_transactions If true, attempts to unnest transactions on failure.
	 * @return bool Returns true on successful commit, false on failure.
	 * @throws DBError Throws an exception if a non-retryable database error occurs.
	 */
	function CommitTransaction( $unnest_transactions = false ) {
		if ( $this->db->transOff == 1 ) {
			Debug::text( 'CommitTransaction(): Final Commit... Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );
		} else {
			if ( $this->db->transCnt == 0 ) {
				Debug::text( 'CommitTransaction(): ERROR: Double Commit... Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff . ' OK: ' . (int)$this->db->_transOK, __FILE__, __LINE__, __METHOD__, 9 );
			} else {
				Debug::text( 'CommitTransaction(): Transaction: Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
			}
		}

		try {
			if ( $unnest_transactions == true && $this->db->_transOK == 0 ) { //Only unnest if the transaction has failed.
				Debug::text( 'CommitTransaction(): Unnesting transactions... Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
				do {
					$retval = $this->db->CompleteTrans();
				} while ( $this->db->transCnt > 0 );
				Debug::text( 'CommitTransaction(): Done unnesting transactions... Count: ' . $this->db->transCnt . ' Off: ' . $this->db->transOff, __FILE__, __LINE__, __METHOD__, 9 );
			} else {
				//throw new Exception( 'could not serialize access due to concurrent' ); //Use only for testing transaction retries on commit failures.
				$retval = $this->db->CompleteTrans();
			}

			if ( $this->db->transOff == 0 && $this->db->_transOK == 1 ) { //Only after the final outer transaction has been committed without any failures, do we actually clear pending cache removals.
				$this->clearCacheRemovedInTransaction(); //Fail transaction first so its not kept open as long.
			}
		} catch ( Exception $e ) {
			//SQL serialization failures can occur on commit, so make sure we catch those and can trigger a retry.
			// This is done in Factory->ExecuteSQL() and Factory->CommitTransaction() too.
			if ( $this->isSQLExceptionRetryable( $e ) == true ) {
				Debug::Text( 'WARNING: Rethrowing Serialization Exception from commit so it can be caught in an outside TRY block...', __FILE__, __LINE__, __METHOD__, 10 );
				//Fail transaction, so it can automatically be restarted in the outter retry loop.
				$this->FailTransaction(); //Don't call Commit after, as that complicates transaction nesting later on.
				throw $e;
			} else {
				throw new DBError( $e );
			}
		}

		if ( $retval == false ) { //Check to see if the transaction has failed.
			//In PostgreSQL, when SESSION/LOCAL variables are set within a transaction that later rollsback, the session variables also rollback. This ensures the timezone still matches what we think it should.
			TTDate::setTimeZone( TTDate::getTimeZone(), true );
		}

		return $retval;
	}

	/**
	 * Initiates a database savepoint with the given name.
	 * A savepoint allows a transaction to partially roll back to a certain point without affecting the rest of the transaction.
	 * This is useful for complex transactions where some steps may fail and need to be retried without aborting the entire transaction.
	 *
	 * @param string $name The name of the savepoint to create.
	 * @return bool Returns true if the savepoint was successfully created, false otherwise.
	 */
	function StartSavePoint( $name ) {
		Debug::Text( 'Starting SavePoint: '. $name, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $name != '' ) {
			$retval = $this->db->Execute( 'SAVEPOINT ' . $name );

			return $retval;
		}

		return false;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function RollbackSavePoint( $name ) {
		Debug::Text( 'Rolling Back SavePoint: '. $name, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $name != '' ) {
			$retval = $this->db->Execute( 'ROLLBACK TO SAVEPOINT ' . $name );

			return $retval;
		}

		return false;
	}

	/**
	 * Rolls back to the previously set savepoint within a database transaction.
	 * This allows for partial rollback of a transaction, providing a way to undo
	 * changes without affecting the entire transaction. It is useful in scenarios
	 * where a specific set of operations within a larger transaction may fail and
	 * need to be retried or ignored.
	 *
	 * @param string $name The name of the savepoint to roll back to.
	 * @return bool Returns true if the rollback to savepoint was successful, false otherwise.
	 */
	function CommitSavePoint( $name ) {
		Debug::Text( 'Committing SavePoint: '. $name, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $name != '' ) {
			$retval = $this->db->Execute( 'RELEASE SAVEPOINT ' . $name );

			return $retval;
		}

		return false;
	}

	/**
	 * Sets the transaction mode for the current database connection.
	 * This function is used to configure the transaction behavior, such as isolation level,
	 * read/write access, and deferrable constraints. The specific modes available depend on
	 * the database system in use. It is important to set the transaction mode before initiating
	 * a transaction to ensure the desired consistency and isolation properties.
	 *
	 * @param string $mode The transaction mode to set. An empty string indicates no change.
	 * @return bool Returns true if the transaction mode was successfully set, false otherwise.
	 */
	function setTransactionMode( $mode = '' ) {
		Debug::text( 'setTransactionMode(): Mode: ' . $mode . ' Transaction Count: ' . $this->db->transCnt, __FILE__, __LINE__, __METHOD__, 9 );

		if ( $mode != '' && $this->db->transCnt > 0 ) {
			Debug::text( 'setTransactionMode(): WARNING: Nested transaction, unlikely to be able to set transaction mode.', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return $this->db->setTransactionMode( $mode );
	}

	/**
	 * Retrieves the current transaction mode from the database.
	 * This function can either return the cached transaction mode or query the database
	 * to get the current setting, depending on the $force parameter.
	 *
	 * @param bool $force If true, forces a database query to get the current transaction mode.
	 * @return string The transaction mode as a string, in uppercase.
	 */
	function getTransactionMode( $force = false ) {
		if ( $force == true ) {
			$mode = $this->db->GetOne( 'select current_setting(\'transaction_isolation\')' );
		} else {
			if ( isset( $this->db->_transmode ) ) {
				$mode = $this->db->_transmode;
			} else {
				$mode = 'DEFAULT';
			}
		}

		Debug::text( 'getTransactionMode(): Mode: ' . $mode . ' Force: ' . (bool)$force, __FILE__, __LINE__, __METHOD__, 9 );

		return strtoupper( $mode );
	}


	/**
	 * Converts a string to a 64-bit integer representation.
	 * This is typically used to create a numeric hash of a string for storing or comparison purposes.
	 * The conversion uses the MD5 algorithm to generate a hash, then takes the first 16 characters
	 * of that hash and converts them from hexadecimal to a decimal integer.
	 *
	 * @param string $text The input string to be converted.
	 * @return int The resulting 64-bit integer.
	 */
	function convertStringTo64BitInteger( $text ) {
		// Extract first 16 characters of MD5 hash and convert it to a 64-bit integer
		$int = hexdec( substr( hash( 'md5', $text ), 0, 16 ) );
		return (int)$int;
	}

	/**
	 * Acquires an advisory lock on the database to ensure exclusive access to a resource.
	 * Advisory locks are used to control access to shared resources without blocking other database operations.
	 * This function attempts to acquire either a transaction-level or session-level lock based on the parameters provided.
	 * Transaction-level locks are not suitable with isolation levels of REPEATABLE READ or SERIALIZABLE.
	 * Session-level locks are acquired before the transaction starts and released after it's committed.
	 *
	 * @param string $name A unique name identifying the lock.
	 * @param bool $is_transaction_level Whether to use a transaction-level lock (true) or session-level lock (false).
	 * @param int $retry_max_attempts The number of times to retry acquiring the lock (-1 for indefinite retries).
	 * @param int $retry_sleep The number of seconds to wait between retry attempts.
	 * @return array An array containing the result of the lock attempt and the time taken to acquire the lock, if successful.
	 * @throws Exception If the lock cannot be acquired within the specified attempts.
	 */
	function acquireAdvisoryLock( $name, $is_transaction_level = false, $retry_max_attempts = -1, $retry_sleep = 60 ) {
		$lock_key = $this->convertStringTo64BitInteger( $name );

		if ( $retry_max_attempts == -1 ) { //If there are -1 retries, then just wait the $retry_sleep period for the lock to be released.
			if ( $is_transaction_level == true ) {
				$lock_function = 'pg_advisory_xact_lock';
			} else {
				$lock_function = 'pg_advisory_lock';
			}
		} else {
			if ( $is_transaction_level == true ) {
				$lock_function = 'pg_try_advisory_xact_lock';
			} else {
				$lock_function = 'pg_try_advisory_lock';
			}
		}

		try {
			$retry_function = function () use ( $name, $lock_function, $lock_key, $is_transaction_level, $retry_max_attempts, $retry_sleep ) {
				if ( $retry_max_attempts == -1 ) {
					$query_prefix = 'SET LOCAL lock_timeout = \''. $retry_sleep .'s\';';
					$query_suffix = 'RESET lock_timeout';
				} else {
					$query_prefix = '';
					$query_suffix = '';
				}

				$lock_acquire_start = microtime( true );
				$query = $query_prefix . 'SELECT ' . $lock_function . '( ' . $lock_key . ' )';
				$lock_result = $this->db->GetOne( $query );
				$lock_acquire_time = ( microtime( true ) - $lock_acquire_start );

				if ( $query_suffix != '' ) {
					$this->db->GetOne( $query_suffix ); //Should be executed separately so we can get the return result from the lock function itself.
				}

				if ( $lock_result == 't' || $retry_max_attempts == -1 ) { //Only the *_try_* functions return boolean. All others return VOID.
					$retval = true;
				} else {
					throw new Exception( 'Unable to acquire lock... Name: ' . $name . '(' . $lock_key . ') Function: ' . $lock_function ); //Causes the retry when in Misc::retry()
				}

				return [ $retval, $lock_acquire_time ];
			};

			if ( $retry_max_attempts == -1 ) {
				$retval = $retry_function();
			} else {
				$retval = Misc::Retry( $retry_function, $retry_max_attempts, $retry_sleep );
			}
		} catch ( Exception $e ) {
			Debug::text( ' NOTICE: Failed to acquire Lock Name: ' . $name . '(' . $lock_key . ') Function: ' . $lock_function . ' Time: ' . microtime( true ), __FILE__, __LINE__, __METHOD__, 10 );

			//Rethrow the exception so it can be caught in the calling function and retried in a transaction retry loop. This will restart the entire transaction from scratch though, which is quite heavy.
			//  We have to do this though, as the lock_timeout causes the transaction to fail anyways, and it has to be rolled back.
			throw $e;

			//if ( $retry_max_attempts == -1 ) {
			//	//Rethrow the exception so it can be caught in the calling function and retried in a transaction retry loop. This will restart the entire transaction from scratch though, which is quite heavy.
			//	//  We have to do this though, as the lock_timeout causes the transaction to fail anyways, and it has to be rolled back.
			//	throw $e;
			//} else {
			//	$retval = [ false, null ]; //Doesn't make sense to return false here, as we never check for false and always expect an exception.
			//}
		}

		Debug::text( ' Lock Name: ' . $name . '(' . $lock_key . ') Result: ' . (int)$retval[0] . ' Function: ' . $lock_function . ' Time: ' . microtime( true ) .' Acquired In: '. $retval[1], __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}


	/**
	 * Attempts to acquire an advisory lock for the specified name.
	 * This function is used to prevent concurrent access to shared resources.
	 * It uses a retry mechanism to attempt to acquire the lock multiple times if necessary.
	 * The lock is identified by a unique name, which is converted to a 64-bit integer key.
	 * If the lock cannot be acquired after the specified number of retries, an exception is thrown.
	 *
	 * @param string $name The unique name of the lock to acquire.
	 * @param callable $lock_function The function used to attempt to acquire the lock.
	 * @param int|string $lock_key The key associated with the lock, derived from the lock name.
	 * @param bool $is_transaction_level Indicates if the lock is at the transaction level.
	 * @param int $retry_max_attempts The maximum number of times to retry acquiring the lock (-1 for no limit).
	 * @param int $retry_sleep The number of seconds to wait between retry attempts.
	 * @return bool
	 */

	function releaseAdvisoryLock( $name ) {
		$lock_key = $this->convertStringTo64BitInteger( $name );

		//NOTE: You can't unlock a transaction level lock. You have to finish the transaction.
		$lock_result = $this->db->GetOne( 'SELECT pg_advisory_unlock( ' . $lock_key . ' )' );
		if ( $lock_result == 't' ) {
			$retval = true;
		} else {
			$retval = false;
		}
		Debug::text( ' Lock Name: ' . $name . '(' . $lock_key . ') Result: ' . (int)$retval . ' Time: ' . microtime( true ), __FILE__, __LINE__, __METHOD__, 9 );

		return $retval;
	}

	/**
	 * Validates the current object state by invoking class-specific validation methods.
	 * This method is intended to be called before persisting an object to ensure data integrity.
	 * It first checks if the object is already marked as valid, and if not, it calls the pre-validation
	 * and validation methods if they are defined in the class. If the object passes validation,
	 * it is marked as valid to avoid redundant checks in the future.
	 *
	 * @param bool $ignore_warning Flag to indicate whether validation warnings should be ignored.
	 * @return bool Returns true if the object is valid, otherwise false.
	 */
	function isValid( $ignore_warning = true ) {
		if ( $this->is_valid == false ) {
			//Most preSave()'s should actually be preValidates, so they are always run prior to validation.
			//  This will only get called if the data is not valid.
			if ( method_exists( $this, 'preValidate' ) ) {
				Debug::text( 'Calling preValidate() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->preValidate() === false ) {
					throw new GeneralError( 'preValidate() failed.' );
				}
			}

			if ( method_exists( $this, 'Validate' ) ) {
				Debug::text( 'Calling Validate() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->Validate( $ignore_warning ) == true ) {
					$this->is_valid = true; //Set flag so we don't revalidate all data unless it has changed.
				}
			}
		} else {
			Debug::text( 'Data has already been validated...', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return $this->Validator->isValid();
	}

	/**
	 * Triggers the class-specific validation function.
	 * This method is intended to be overridden by child classes to implement
	 * object-specific validation logic that should be executed just before
	 * the object is saved to the database. It should return true if the
	 * validation passes, or false if it fails.
	 *
	 * @return bool True if validation is successful, false otherwise.
	 */
	function isWarning() {
		if ( method_exists( $this, 'validateWarning' ) ) {
			Debug::text( 'Calling validateWarning() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
			$this->validateWarning();
		}

		return $this->Validator->isWarning();
	}

	/**
	 * Retrieves the primary key sequence name for the object.
	 * If the object has a defined primary key sequence name, it returns that name.
	 * Otherwise, it returns false, indicating that no sequence name is set.
	 *
	 * @return string|bool The primary key sequence name if set, otherwise false.
	 */
	function getSequenceName() {
		if ( isset( $this->pk_sequence_name ) ) {
			return $this->pk_sequence_name;
		}

		return false;
	}

	/**
	 * Generates the next insert ID for the database entry.
	 * If the primary key is not a UUID, it retrieves the next ID from the sequence.
	 * Otherwise, it generates a UUID to be used as the primary key.
	 *
	 * @return bool|string The next insert ID as a string, or false if the sequence name is not set or UUIDs are not used.
	 */
	function getNextInsertId() {
		global $PRIMARY_KEY_IS_UUID;

		if ( $PRIMARY_KEY_IS_UUID == false ) {
			if ( isset( $this->pk_sequence_name ) ) {
				return $this->db->GenID( $this->pk_sequence_name );
			}

			return false;
		} else {
			return TTUUID::generateUUID();
		}
	}

	/**
	 * Executes an SQL query with optional placeholders and pagination.
	 * This method is designed to execute a given SQL query using the database connection.
	 * It supports pagination by accepting limit and page parameters, which are used to retrieve
	 * a subset of records. Placeholders can be provided for prepared statements.
	 *
	 * @param string $query The SQL query to execute.
	 * @param array|null $ph An array of placeholders for the SQL query. Defaults to null if not provided.
	 * @param int|null $limit The maximum number of records to return. If not set, all records are returned.
	 * @param int|null $page The page number to retrieve when using pagination. Relevant only if $limit is set.
	 * @return bool True on success, false on failure.
	 * @throws DBError If a database error occurs.
	 * @throws Exception If a general error occurs during query execution.
	 */
	function ExecuteSQL( $query, $ph = null, $limit = null, $page = null ) {
		try {
			if ( $ph === null ) { //Work around ADODB change that requires $ph === FALSE, otherwise it changes it to a array( 0 => NULL ) and causes SQL errors.
				$ph = false;
			}

			if ( Debug::getVerbosity() >= 11 ) {
				$start_time = microtime( true );
			}

			if ( $limit == null ) {
				$rs = $this->db->Execute( $query, $ph );
			} else {
				$rs = $this->db->PageExecute( $query, (int)$limit, (int)$page, $ph );
			}

			if ( Debug::getVerbosity() >= 11 ) {
				$total_time = ( ( microtime( true ) - $start_time ) * 1000 );

				global $__tt_sql_profiler;
				if ( !isset( $__tt_sql_profiler ) ) {
					$__tt_sql_profiler = [ 'total_queries' => 0, 'total_read_queries' => 0, 'total_write_queries' => 0, 'slowest_query_time' => 0, 'total_time' => 0, 'slowest_query' => [ 'time' => 0, 'query' => '', 'ph' => [], 'backtrace' => '' ] ];
				}

				$__tt_sql_profiler['total_queries']++;
				if ( stripos( $query, 'SELECT' ) !== false ) {
					$__tt_sql_profiler['total_read_queries']++;
				} else {
					$__tt_sql_profiler['total_write_queries']++;
				}
				$__tt_sql_profiler['total_time'] += $total_time;
				if ( $total_time > $__tt_sql_profiler['slowest_query']['time'] ) {
					$__tt_sql_profiler['slowest_query']['time'] = $total_time;
					$__tt_sql_profiler['slowest_query']['query'] = $query;
					$__tt_sql_profiler['slowest_query']['ph'] = $ph;
					$__tt_sql_profiler['slowest_query']['backtrace'] = Debug::backTrace();
				}

				Debug::Query( 'Executed In: '. $total_time ." ms\n". $query, $ph, __FILE__, __LINE__, __METHOD__, 11 );
			}

			//throw new Exception( 'could not serialize access due to concurrent' ); //Use only for testing transaction retries on SQL failures.
		} catch ( Exception $e ) {
			if ( $this->isSQLExceptionRetryable( $e ) == true ) { // This is done in Factory->ExecuteSQL() and Factory->CommitTransaction() too.
				Debug::Text( 'WARNING: Rethrowing Serialization Exception so it can be caught in an outside TRY block...', __FILE__, __LINE__, __METHOD__, 10 );
				//Fail transaction, so it can automatically be restarted in the outter retry loop.
				$this->FailTransaction();                         //Don't call Commit after, as that complicates transaction nesting later on.
				throw $e;
			} else {
				throw new DBError( $e );
			}
		}

		return $rs;
	}

	/**
	 * Determines if a SQL exception is one that can be retried or not.
	 * This function is typically used in a database transaction context where certain exceptions
	 * may indicate temporary conditions like lock timeouts or deadlocks. In such cases, it may be
	 * safe to retry the transaction. The function inspects the exception message for known patterns
	 * that signify retryable errors.
	 *
	 * @param Exception $e The exception to evaluate.
	 * @return bool True if the exception is considered retryable, false otherwise.
	 */
	function isSQLExceptionRetryable( $e ) {
		if ( $e instanceof Exception && $e->getMessage() != ''
				&& ( stristr( $e->getMessage(), 'could not serialize' ) !== false
						|| stristr( $e->getMessage(), 'deadlock' ) !== false
						|| stristr( $e->getMessage(), 'lock timeout' ) !== false
						|| stristr( $e->getMessage(), 'current transaction is aborted' ) !== false ) //There seems to be cases where the "could not serialize" error is not picked up by PHP and therefore not triggered, so on the next query we get this error instead.
						|| stristr( $e->getMessage(), 'duplicate key value violates unique constraint' ) !== false //Needed for INSERT queries that fail due to unique constraint violations, such as timesheet verification.
		) {
			Debug::text( 'Retryable SQL Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		Debug::text( 'Non-Retryable SQL Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * Executes a transaction function and retries it on failure.
	 *
	 * This method accepts a Closure that contains transactional code, which is executed
	 * and retried a specified number of times if it fails due to database errors that
	 * are considered retryable (like deadlocks). The retries occur after a specified
	 * sleep interval. This can help to resolve transient database contention issues.
	 *
	 * @param Closure $transaction_function The transactional code to be executed.
	 * @param int $retry_max_attempts The maximum number of retry attempts.
	 * @param int $retry_sleep The interval, in seconds, to wait between retries.
	 * @return mixed The result of the transaction function, if successful.
	 * @throws DBError If the transaction function consistently fails and exceeds the maximum retry attempts.
	 */
	function RetryTransaction( $transaction_function, $retry_max_attempts = 4, $retry_sleep = 1 ) { //When changing function definition, also see APIFactory->RetryTransaction()
		// Help mitigate function injection attacks due to the variable function call below $transaction_function();
		if ( !$transaction_function instanceof Closure ) {
			Debug::text( 'ERROR: Retry function is not a closure, unable to execute!', __FILE__, __LINE__, __METHOD__, 10 );
			return null;
		}

		$is_nested_retry_transaction = false;

		if ( $this->db->transCnt > 0 ) {
			//This can happen during import validation, because we need to wrap everything in a transaction that will always be rolled back.
			Debug::text( 'WARNING: RetryTransaction called from within a transaction, as the entire transaction cant be rolled back max retry attempts will be 1. Trans Cnt: ' . $this->db->transCnt, __FILE__, __LINE__, __METHOD__, 10 );
			//throw new Exception('ERROR: RetryTransaction cannot be called from within a transaction, as the entire transaction cant be rolled back then...');
			$retry_max_attempts = 1;
			$is_nested_retry_transaction = true;
		}

		if ( $retry_max_attempts < 1 ) { //Make sure max attempts is set to at least 1.
			$retry_max_attempts = 1;
		}

		//$current_cache_memory_state = $this->cache->_onlyMemoryCaching;

		$tmp_sleep = ( $retry_sleep * 1000000 );
		$retry_attempts = 0;
		while ( $retry_attempts < $retry_max_attempts ) {
			try {
				//In PostgreSQL, may need to increase "max_pred_locks_per_transaction" setting to avoid transactions waiting on more lock slots to become available.
				// Can monitor this with: select count(*) from pg_locks where mode = 'SIReadLock';

				unset( $e );                             //Clear any exceptions on retry.

				//$this->cache->_onlyMemoryCaching = true; //Disable persistent caching and switch to memory caching only when retrying blocks of transaction, this allows us to clear all memory cache on rollback below.
				$this->cache->__in_retry_transaction_function = true;

				Debug::text( '==================START: TRANSACTION BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $transaction_function(); //This function should call StartTransaction() at the beginning, and CommitTransaction() at the end.
				Debug::text( '==================END: TRANSACTION BLOCK=====================================', __FILE__, __LINE__, __METHOD__, 10 );

				//$this->cache->_onlyMemoryCaching = $current_cache_memory_state;
				$this->cache->__in_retry_transaction_function = false;
			} catch ( Exception $e ) {
				if ( $is_nested_retry_transaction == true ) {
					//If we are inside a nested retry transaction block that fails, we can't fail/retry just part of the transaction,
					// so instead immediately re-throw a new NestedRetryTransaction exception so we can pass that up to the outer retry transaction block, for retrying at the outer most retry block instead.
					// Don't need to bother with any sleep intervals, or transaction fail/commit calls, as the outer block will handle that itself.
					// See APIAuthorization->setAuthorization() and search for "NestedRetryTransaction" for example usage.
					Debug::text( 'WARNING: Inner nested RetryTransaction failed, passing exception to outer block for retry there...', __FILE__, __LINE__, __METHOD__, 10 );
					throw new NestedRetryTransaction( $e ); //'SQL exception in Nested RetryTransaction...'
				} else {
					if ( $this->isSQLExceptionRetryable( $e ) == true ) {
						//Quick way to clear all memory cache on retry.
						$this->cache->_memoryCachingArray = [];
						$this->cache->_memoryCachingCounter = 0;

						//When we get here, fail transaction should already be called.
						// But if it hasn't, call it again just in case.
						if ( $this->db->_transOK == true ) {
							$this->FailTransaction();
						}

						$this->CommitTransaction( true ); //Make sure we fully unnest all transactions so the retry is in a good state that can be fully restarted.

						$random_sleep_interval = ( ceil( ( rand() / getrandmax() ) * ( ( $tmp_sleep * 0.33 ) * 2 ) - ( $tmp_sleep * 0.33 ) ) ); //+/- 33% of the sleep time.

						Debug::text( 'WARNING: SQL query failed, likely due to transaction isolation: Retry Attempt: ' . $retry_attempts . ' Sleep: ' . ( $tmp_sleep + $random_sleep_interval ) . '(' . $tmp_sleep . ') Code: ' . $e->getCode() . ' Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						Debug::text( '==================END: TRANSACTION BLOCK===================================', __FILE__, __LINE__, __METHOD__, 10 );

						if ( $retry_attempts < ( $retry_max_attempts - 1 ) ) { //Don't sleep on the last iteration as its serving no purpose.
							usleep( $tmp_sleep + $random_sleep_interval );
						}

						$tmp_sleep = ( $tmp_sleep * 2 ); //Exponential back-off with 25% of retry sleep time as a random value.
						$retry_attempts++;

						continue;
					} else {
						Debug::text( 'ERROR: Non-Retryable SQL failure (syntax error?), aborting... Code: ' . $e->getCode() . ' Message: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						break;
					}
				}
			}
			break;
		}

		if ( isset( $e ) ) {
			Debug::text( 'ERROR: SQL query failed after max attempts: ' . $retry_attempts . ' Max: ' . $retry_max_attempts, __FILE__, __LINE__, __METHOD__, 10 );
			throw new DBError( $e );
		}

		if ( isset( $retval ) ) {
			Debug::Arr( $retval, 'Returning Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return null;
	}

	/**
	 * Compares the current in-memory object data with the persisted data in the database.
	 * It identifies which fields have been modified since the object was last saved.
	 * This is particularly useful in scenarios such as validation or post-save operations
	 * where it's necessary to know which fields have changed.
	 * The comparison is done by checking the 'old_data' against the current 'data' state of the object.
	 * Note that 'old_data' represents the state of the data as it was in the database before any changes.
	 *
	 * @return array An associative array containing the differences, with keys as the field names
	 *               and values as the original data from the database.
	 */
	function getDataDifferences() {
		if ( isset( $this->json_columns ) && is_array( $this->json_columns ) ) {
			//NOTE: When using JSON columns like 'punch_tag_id', 'old_data' is still JSON encoded, while 'data' is likely not JSON encoded yet, as that is done at the last possible moment in the Factory::Save() function.
			//      array_diff_assoc() doesn't like nested arrays, so if it comes across them, it can trigger: PHP Notice:  Array to string conversion
			//      Therefore we need to *copy* the new data, then json_encode() it first, then there shouldn't be any nested arrays.

			$tmp_data = $this->data; //*Copy* the data array so we can JSON encode it without affecting the real data.

			foreach ( $this->json_columns as $column ) {
				if ( isset( $this->data[$column] ) && is_array( $this->data[$column] ) ) {
					$tmp_data[$column] = json_encode( $this->data[$column] );
				}
			}
		}

		//Other json are generic JSON columns and also need to be encoded before diffing.
		if ( isset( $this->data['other_json'] ) && is_array( $this->data['other_json'] ) ) {
			if ( isset( $tmp_data ) == false ) {
				$tmp_data = $this->data; //*Copy* the data array so we can JSON encode it without affecting the real data.
			}

			$tmp_data['other_json'] = json_encode( $this->data['other_json'] );
		}

		//Custom fields are JSON columns and also need to be encoded before diffing.
		if ( isset( $this->data['custom_field'] ) && is_array( $this->data['custom_field'] ) ) {
			if ( isset( $tmp_data ) == false ) {
				$tmp_data = $this->data; //*Copy* the data array so we can JSON encode it without affecting the real data.
			}

			$tmp_data['custom_field'] = json_encode( $this->data['custom_field'] );
		}

		$retarr = array_diff_assoc( (array)$this->old_data, (array)( $tmp_data ?? $this->data ) );

		if ( isset( $tmp_data ) == true ) {
			unset( $tmp_data );
		}

		//Debug::Arr( $retarr, 'Calling getDataDifferences() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * Determine if the record data has changed at all.
	 * This function checks if there have been any changes to the data of a record since it was last persisted.
	 * It is useful for avoiding unnecessary database writes when the data has not changed.
	 * The function relies on the `getDataDifferences` method to compare the current in-memory data against
	 * the data that was last saved to the database.
	 *
	 * @return bool True if the data has changed, false otherwise.
	 */
	function hasDataChanged() {
		if ( $this->isNew( true ) == true ) { //If the record is new, assume that data has always changed.
			return true;
		} else {
			$data_diff = $this->getDataDifferences();
			if ( !empty( $data_diff ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if there is a difference between the old and new data for a specific key.
	 * This function is crucial for determining if fields that require special handling, such as dates or timestamps,
	 * have changed. It compares the value associated with the provided key in the old data array to the new data value.
	 *
	 * @param string $key The key to check in the data arrays.
	 * @param array $data_diff An associative array containing the differences between the old and new data.
	 * @param string|null $type_id An optional string indicating the type of the data (e.g., 'date', 'time_stamp').
	 *                             This is used to apply specific comparison logic for different data types.
	 * @param mixed|null $new_data The new data value to compare against the old data. If null, the current data for the key is used.
	 * @return bool True if there is a difference between the old and new data for the specified key, false otherwise.
	 */
	function isDataDifferent( $key, $data_diff, $type_id = null, $new_data = null ) {
		// Must use array_key_exists as there could be a NULL value which is old value and is different of course.
		if ( is_array( $data_diff ) && array_key_exists( $key, $data_diff ) == true ) {
			$retval = false;

			$old_data = $data_diff[$key];

			if ( $new_data === null && array_key_exists( $key, $this->data ) ) {
				$new_data = $this->data[$key];
			}

			$type_id = ( is_string( $type_id ) ) ? strtolower( $type_id ) : $type_id; //When $type_id is not null, strtolower it.

			switch ( $type_id ) {
				case 'date':
					//When comparing dates, the old_data is likely from the DB and a string date, while the new data is likely epoch.
					if ( TTDate::getMiddleDayEpoch( strtotime( (string)$old_data ) ) != TTDate::getMiddleDayEpoch( $new_data ) ) {
						$retval = true;
					}
					break;
				case 'time':
				case 'time_stamp':
					//When comparing dates, the old_data is likely from the DB and a string date, while the new data is likely epoch.
					if ( strtotime( $old_data ) != $new_data ) {
						$retval = true;
					}
					break;
				default:
					$retval = true;
					break;
			}

			return $retval;
		}

		return false;
	}


	/**
	 * Handles the "RETURNING ..." clause of a SQL INSERT/UPDATE query.
	 * This function is typically used to retrieve values of auto-generated fields upon insertion or update.
	 * It is useful for databases like PostgreSQL that support the RETURNING clause, which allows
	 * the query to return values from the inserted/updated rows.
	 *
	 * @param object $rs The result set object returned by the database query execution.
	 * @return bool Always returns true, indicating the function executed successfully.
	 */
	function handleSaveSQLReturning( $rs ) {
		return true;
	}

	/**
	 * Executes the save operation for the current object state.
	 * This method determines whether to perform an insert or an update operation
	 * based on the object's current state. It also handles the assignment of
	 * creation and modification metadata such as created/updated timestamps and
	 * the corresponding user IDs responsible for those actions.
	 *
	 * @param bool $reset_data Flag to reset object data after save operation.
	 * @param bool $force_lookup Flag to force a lookup to determine if the object is new.
	 * @return bool|int|string Returns true on success, or an error code or message on failure.
	 * @throws DBError Throws an exception if a database error occurs.
	 * @throws GeneralError Throws an exception for general errors not related to the database.
	 */
	function Save( $reset_data = true, $force_lookup = false ) {
		$this->StartTransaction();

		//Run Pre-Save function
		//This is called before validate so it can do extra calculations, etc before validation.
		//Should this AND validate() NOT be called when delete flag is set?
		if ( method_exists( $this, 'preSave' ) ) {
			Debug::text( 'Calling preSave() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
			//  **NOTE: If preSave() calls any set*() functions, it will cause isValid() below to be called again, duplicating many of the SQL queries isValid() calls.
			//          Therefore consider using preValidate() instead.
			if ( $this->preSave() === false ) {
				throw new GeneralError( 'preSave() failed.' );
			}
		}

		//Don't validate when deleting, so we can delete records that may have some invalid options.
		//However we can still manually call this function to check if we need too.
		if ( $this->getDeleted() == false && $this->isValid() === false ) {
			throw new GeneralError( 'Invalid Data, not saving.' );
		}

		//Must come after preValidate, Validate, preSave, otherwise those can just decode it again.
		$this->encodeGenericJSONData();
		$this->encodeCustomFields();
		$this->encodeJSONColumns();

		//Should we insert, or update?
		if ( $this->isNew( $force_lookup ) ) {
			//Insert
			$time = TTDate::getTime();

			//CreatedBy/Time needs to be set to original values when doing things like importing records.
			//However from the API, Created By only needs to be set for a small subset of classes like RecurringScheduleTemplateControl.
			//We handle this in setCreatedAndUpdatedColumns().
			if ( empty( $this->getCreatedDate() ) ) {
				$this->setCreatedDate( $time );
			}
			if ( empty( $this->getCreatedBy() ) ) {
				$this->setCreatedBy();
			}

			//Set updated date at the same time, so we can easily select last
			//updated, or last created records.
			if ( empty( $this->getUpdatedDate() ) ) {
				$this->setUpdatedDate( $time );
			}
			if ( empty( $this->getUpdatedBy() ) ) {
				$this->setUpdatedBy();
			}

			unset( $time );

			$insert_id = $this->getID();
			if ( $insert_id == false ) {
				//Append insert ID to data array.
				$insert_id = $this->getNextInsertId();
				Debug::text( 'Insert ID: ' . $insert_id . ' Table: ' . $this->getTable(), __FILE__, __LINE__, __METHOD__, 9 );
				$this->setId( $insert_id );
			}

			try {
				$query = $this->getInsertQuery();
			} catch ( Exception $e ) {
				throw new DBError( $e );
			}
			$retval = TTUUID::castUUID( $insert_id );
			$log_action = 10; //'Add';
		} else {
			Debug::text( ' Updating ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->getDeleted() == true ) {
				$this->setDeletedDate();
				$this->setDeletedBy();
			} else {
				//Don't set updated_date when deleting records, we use deleted_date for that instead.
				$this->setUpdatedDate();
				$this->setUpdatedBy();
			}

			//Update
			$query = $this->getUpdateQuery(); //Don't pass data, too slow

			//Debug::Arr($this->data, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
			$retval = true;

			if ( $this->getDeleted() === true ) {
				$log_action = 30; //'Delete';
			} else {
				$log_action = 20; //'Edit';
			}
		}

		//Debug::text('Save(): Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($query, 'Save(): Query: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $query != '' || $query === true ) {

			if ( is_string( $query ) && $query != '' ) {
				$rs = $this->ExecuteSQL( $query );
				$this->handleSaveSQLReturning( $rs );
			}

			if ( method_exists( $this, 'addLog' ) ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				global $config_vars;
				if ( !isset( $config_vars['other']['disable_audit_log'] ) || $config_vars['other']['disable_audit_log'] != true ) {
					$this->addLog( $log_action );
				}
			}

			//Run postSave function.
			if ( method_exists( $this, 'postSave' ) ) {
				Debug::text( 'Calling postSave() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->postSave() === false ) {
					throw new GeneralError( 'postSave() failed.' );
				}
			}

			//Clear the data.
			if ( $reset_data == true ) {
				$this->clearData();
			}
			//IF YOUR NOT RESETTING THE DATA, BE SURE TO CLEAR THE OBJECT MANUALLY
			//IF ITS IN A LOOP!! VERY IMPORTANT!

			$this->CommitTransaction();

			//Debug::Arr($retval, 'Save Retval: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}

		Debug::text( 'Save(): returning FALSE! Very BAD!', __FILE__, __LINE__, __METHOD__, 10 );

		throw new GeneralError( 'Save(): failed.' );

		//return false; //This should return false here?
	}

	/**
	 * Deletes the record directly from the database.
	 * This function performs a hard delete operation on the database record associated with the current object instance.
	 * It optionally allows the bypassing of audit logging for the delete operation based on the parameter provided.
	 *
	 * @param bool $disable_audit_log If true, disables audit logging for this delete operation.
	 * @return bool Returns true if the delete operation is successful, false otherwise.
	 * @throws DBError Throws an exception if there is an error executing the delete operation on the database.
	 */
	function Delete( $disable_audit_log = false ) {
		Debug::text( 'Delete: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 9 );

		if ( $this->getId() !== false ) {
			if ( $disable_audit_log == false && method_exists( $this, 'addLog' ) ) {
				//In some cases, like deleting users, this function will fail because the user is deleted before they are removed from other
				//tables like PayPeriodSchedule, so addLog() can't get the user information.
				global $config_vars;
				if ( !isset( $config_vars['other']['disable_audit_log'] ) || $config_vars['other']['disable_audit_log'] != true ) {
					$this->addLog( 30 ); //30=Delete
				}
			}

			$ph = [
					'id' => $this->getId(),
			];

			$query = 'DELETE FROM ' . $this->getTable() . ' WHERE id = ?';
			$this->ExecuteSQL( $query, $ph );

			//Run postDelete function, often used to remove cache records.
			if ( method_exists( $this, 'postDelete' ) ) {
				Debug::text( 'Calling postDelete() of: '. get_class( $this ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $this->postDelete() === false ) {
					throw new GeneralError( 'postDelete() failed.' );
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Retrieves an array of IDs from a list factory object.
	 * This function iterates over the elements of the list factory object,
	 * extracting the ID of each element and collecting them into an array.
	 * If the list factory object is not valid or is empty, the function returns false.
	 *
	 * @param object $lf The list factory object containing elements with IDs.
	 * @return array|bool An array of IDs if the list factory object is valid, false otherwise.
	 */
	function getIDSByListFactory( $lf ) {
		if ( !is_object( $lf ) ) {
			return false;
		}

		foreach ( $lf as $lf_obj ) {
			$retarr[] = $lf_obj->getID();
		}

		if ( isset( $retarr ) ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * Performs a bulk deletion of records based on their UUIDs.
	 * This function accepts either a single UUID as a string or an array of UUIDs.
	 * It constructs a DELETE SQL query to remove all records with the provided UUIDs from the database.
	 * If the deletion is successful, it returns true, otherwise false.
	 * An exception of type DBError is thrown if there is an issue executing the SQL query.
	 *
	 * @param string|array $ids A single UUID or an array of UUIDs identifying the records to delete.
	 * @return bool True if the deletion was successful, false otherwise.
	 * @throws DBError If the SQL query fails to execute.
	 */
	function bulkDelete( $ids ) {
		//Debug::text('Delete: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 9);

		//Make SURE you get the right table when calling this.
		if ( is_array( $ids ) && count( $ids ) > 0 ) {
			$ph = [];

			$query = 'DELETE FROM ' . $this->getTable() . ' WHERE id in (' . $this->getListSQL( $ids, $ph, 'uuid' ) . ')';
			$this->ExecuteSQL( $query, $ph );
			Debug::text( 'Bulk Delete Query: ' . $query . ' Affected Rows: ' . $this->getAffectedRows() . ' IDs: ' . count( $ph ), __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		return false;
	}

	/**
	 * Clears geocode data if address-related fields have changed.
	 * This function checks if any of the address-related fields ('address1', 'address2', 'city', 'province', 'country', 'postal_code')
	 * have changed by comparing them with the provided $data_diff array. If a change is detected in any of these fields,
	 * it updates the corresponding record in the database to set the 'longitude' and 'latitude' fields to NULL.
	 * This is likely done to ensure that outdated geocode data is not retained when address information is updated.
	 *
	 * @param array|null $data_diff An associative array containing the new values of the fields to be compared with the existing values.
	 *                              If null or not provided, no action is taken.
	 * @return bool True if geocode data was cleared, false otherwise.
	 */
	function clearGeoCode( $data_diff = null ) {
		if ( is_array( $data_diff )
				&& ( $this->isDataDifferent( 'address1', $data_diff ) || $this->isDataDifferent( 'address2', $data_diff ) || $this->isDataDifferent( 'city', $data_diff ) || $this->isDataDifferent( 'province', $data_diff ) || $this->isDataDifferent( 'country', $data_diff ) || $this->isDataDifferent( 'postal_code', $data_diff ) ) ) {
			//Run a separate custom query to clear the geocordinates. Do we really want to do this for so many objects though...
			Debug::text( 'Address has changed, clear geocordinates!', __FILE__, __LINE__, __METHOD__, 10 );
			$query = 'UPDATE ' . $this->getTable() . ' SET longitude = NULL, latitude = NULL where id = ?';
			$this->ExecuteSQL( $query, [ 'id' => $this->getID() ] );

			return true;
		}

		return false;
	}


	/**
	 * Filters out any elements in the provided array that do not have a corresponding
	 * mapping in the function map. This ensures that only recognized data fields are
	 * retained, which can be important for operations that rely on the structure
	 * defined by the function map, such as database updates or data validation.
	 *
	 * @param array|null $data The array of data to be filtered based on the function map.
	 * @return array|null The filtered array with only the elements that are mapped, or null if the input was null.
	 */
	function clearNonMappedData( $data = null ) {
		if ( is_array( $data ) && method_exists( $this, '_getVariableToFunctionMap' ) ) {
			$function_map = $this->getVariableToFunctionMap();
			if ( is_array( $function_map ) ) {
				foreach ( $data as $column => $value ) {
					if ( !isset( $function_map[$column] ) || ( $function_map[$column] == '' ) ) {
						unset( $data[$column] );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Clears all data stored in the object's properties.
	 * This method resets the 'data' and 'tmp_data' properties to empty arrays and calls the 'clearOldData' method
	 * to reset the 'old_data' property as well. It is likely used to reinitialize the object to a clean state
	 * before it is reused, ensuring that no stale data persists that could affect subsequent operations.
	 *
	 * @return bool Always returns true, indicating the data was cleared successfully.
	 */
	function clearData() {
		$this->data = $this->tmp_data = [];

		$this->clearOldData();

		return true;
	}

	/**
	 * Clears the old data array.
	 *
	 * This function is responsible for resetting the state of the object by clearing
	 * the old data that might have been stored from previous operations. It ensures
	 * that the object does not retain stale data which could affect future operations
	 * or lead to incorrect behavior.
	 *
	 * @return bool Always returns true to indicate the operation was successful.
	 */
	function clearOldData() {
		$this->old_data = [];

		return true;
	}


	/**
	 * Retrieves an iterator for the Factory object.
	 *
	 * This method allows the Factory object to be iterated over using foreach loops.
	 * It wraps the Factory object in a FactoryListIterator, which implements the Iterator interface,
	 * enabling the iteration of elements within the Factory.
	 *
	 * @return FactoryListIterator An iterator for the Factory object.
	 */
	#[\ReturnTypeWillChange]
	final function getIterator() {
		return new FactoryListIterator( $this );
	}

	/**
	 * Resets the iterator for the record set to the first element.
	 * This function is analogous to the MoveFirst() function found in ADODB,
	 * which is used to reset the pointer in a database record set to the first record.
	 * It is useful when you need to iterate over the record set again from the start.
	 *
	 * @return bool Returns true on success, or false on failure.
	 */
	final function rewind() {
		return $this->getIterator()->rewind();
	}

	/**
	 * Retrieves the current element from the Factory iterator.
	 *
	 * This method is used to obtain the current element in the iteration
	 * sequence when iterating over the Factory object. It is typically
	 * called after the iterator has been advanced using the `next` method
	 * or automatically during a foreach loop.
	 *
	 * @return mixed The current element from the Factory iterator, or false if no current element or not valid.
	 */
	final function getCurrent() {
		return $this->getIterator()->current();
	}
}

?>
