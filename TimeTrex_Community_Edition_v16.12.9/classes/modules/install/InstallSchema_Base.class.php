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
class InstallSchema_Base {

	protected $schema_sql_file_name = null;
	protected $version = null;
	protected $db = null;
	protected $is_upgrade = false;
	protected $install_obj = false;

	/**
	 * InstallSchema_Base constructor.
	 * @param bool $install_obj
	 */
	function __construct( $install_obj = false ) {
		if ( is_object( $install_obj ) ) {
			$this->install_obj = $install_obj;
		}

		return true;
	}

	/**
	 * @param $db
	 */
	function setDatabaseConnection( $db ) {
		$this->db = $db;
	}

	/**
	 * @return null
	 */
	function getDatabaseConnection() {
		return $this->db;
	}

	/**
	 * @param $val
	 */
	function setIsUpgrade( $val ) {
		$this->is_upgrade = (bool)$val;
	}

	/**
	 * @return bool
	 */
	function getIsUpgrade() {
		return $this->is_upgrade;
	}

	/**
	 * @param $value
	 */
	function setVersion( $value ) {
		$this->version = $value;
	}

	/**
	 * @return null
	 */
	function getVersion() {
		return $this->version;
	}

	/**
	 * @param $file_name
	 */
	function setSchemaSQLFilename( $file_name ) {
		$this->schema_sql_file_name = $file_name;
	}

	/**
	 * @return null
	 */
	function getSchemaSQLFilename() {
		return $this->schema_sql_file_name;
	}

	/**
	 * @return string
	 */
	function getSchemaGroup() {
		$schema_group = substr( $this->getVersion(), -1, 1 );
		Debug::text( 'Schema: ' . $this->getVersion() . ' Group: ' . $schema_group, __FILE__, __LINE__, __METHOD__, 9 );

		return strtoupper( $schema_group );
	}

	/**
	 * Copied from Install class.
	 * @param $table_name
	 * @return bool
	 */
	function checkTableExists( $table_name ) {
		Debug::text( 'Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == false ) {
			return false;
		}

		$table_arr = $db_conn->MetaTables();

		if ( in_array( $table_name, $table_arr ) ) {
			Debug::text( 'Exists - Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		Debug::text( 'Does not Exist - Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * load Schema file data
	 * @return bool|string
	 */
	function getSchemaSQLFileData() {
		//Read SQL data into memory
		if ( is_readable( $this->getSchemaSQLFilename() ) ) {
			Debug::text( 'Schema SQL File is readable: ' . $this->getSchemaSQLFilename(), __FILE__, __LINE__, __METHOD__, 9 );
			$contents = file_get_contents( $this->getSchemaSQLFilename() );

			Debug::Arr( $contents, 'SQL File Data: ', __FILE__, __LINE__, __METHOD__, 9 );

			return $contents;
		}

		Debug::text( 'Schema SQL File is NOT readable, or is empty!', __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @param $sql
	 * @return string
	 */
	function removeSchemaSQLFileComments( $sql ) {
		$retval = '';

		$split_sql = explode( "\n", $sql );
		if ( is_array( $split_sql ) ) {
			foreach ( $split_sql as $sql_line ) {
				if ( substr( trim( $sql_line ), 0, 2 ) != '--' ) {
					$retval .= $sql_line . "\n"; //Make sure the newlines are put back in the proper place, otherwise it can other SQL parse errors.
				} else {
					Debug::text( 'Skipping SQL Comment: ' . $sql_line, __FILE__, __LINE__, __METHOD__, 9 );
				}
			}
		}

		return $retval;
	}

	/**
	 * Add support for custom variables in SQL files so we can access PHP variables in SQL and keep SQL schema files across database systems similar.
	 * @param $sql
	 * @return mixed
	 */
	function replaceSQLVariables( $sql ) {
		if ( $this->getVersion() != '1000A' ) {           //Don't replace any variables on first schema version, as TTUUID requires a registration key, which itself requires the system_settings table.
			$uuid_prefix = TTUUID::getConversionPrefix(); //Conversion prefix can't be used until at least after 1000A is done and system_setting table exists. Preferrably not until after a registration key has also been created.
			$search_arr = [ '#UUID_PREFIX#' ];
			$replace_arr = [ $uuid_prefix ];

			$retval = str_ireplace( $search_arr, $replace_arr, $sql );

			return $retval;
		} else {
			Debug::text( 'Skipping SQL variable replace, as this is the first schema version: 1000A...', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return $sql;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function _InstallSchema() {
		//Run the actual SQL queries here

		$sql = $this->removeSchemaSQLFileComments( $this->getSchemaSQLFileData() );
		if ( $sql == false ) {
			return false;
		}

		if ( $sql !== false && strlen( $sql ) > 0 ) {
			//Handle variable replacements on the entire schema version file at once to avoid having initialize the search/replace variables for every line.
			$sql = $this->replaceSQLVariables( $sql );

			Debug::text( 'Schema SQL has data, executing commands! Version: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

			$i = 0;

			//Split into individual SQL queries in case more than one is on the same line, so we can better differentiate between actual queries.
			$split_sql = explode( ';', $sql );
			if ( is_array( $split_sql ) ) {
				$total_sql_queries = count( $split_sql );
				foreach ( $split_sql as $sql_line ) {
					//Debug::text('SQL Line: '. trim($sql_line), __FILE__, __LINE__, __METHOD__, 9);
					if ( trim( $sql_line ) != '' && substr( trim( $sql_line ), 0, 2 ) != '--' ) {
						try {
							Debug::text( '  Executing SQL command: ' . $i . ' of: ' . $total_sql_queries, __FILE__, __LINE__, __METHOD__, 10 );
							$this->getDatabaseConnection()->Execute( $sql_line );
						} catch ( Exception $e ) {
							Debug::text( 'SQL Command failed on line: ' . $i . ' of: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );
							throw new DBError( $e );

							//return false;
						}
					}

					$i++;
				}
			}

			Debug::text( 'Schema upgrade succeeded, last line: ' . $i . ' of: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );
		} else {
			Debug::text( 'Schema SQL does not have data, not executing commands, continuing...', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function _postPostInstall() {
		Debug::text( 'Modify Schema version in system settings table!', __FILE__, __LINE__, __METHOD__, 9 );
		//Modify schema version in system_settings table.

		Debug::text( 'Setting Schema Version to: ' . $this->getVersion() . ' Group: ' . $this->getSchemaGroup(), __FILE__, __LINE__, __METHOD__, 9 );

		//Clear the ADODB GETINSERTSQL static cache, which is required when the schema changes specifically for the SystemSetting/SystemLog tables after the cache has been populated. ie: pre-UUID to post-UUID.
		global $ADODB_GETINSERTSQL_CLEAR_CACHE;
		$ADODB_GETINSERTSQL_CLEAR_CACHE = true;

		$retval = SystemSettingFactory::setSystemSetting( 'schema_version_group_' . $this->getSchemaGroup(), $this->getVersion() );

		$ADODB_GETINSERTSQL_CLEAR_CACHE = false;

		return $retval;
	}

	/**
	 * @return bool
	 */
	function InstallSchema() {
		$this->getDatabaseConnection()->StartTrans();

		Debug::text( 'Installing Schema Version: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );
		if ( $this->preInstall() == true ) {
			if ( $this->_InstallSchema() == true ) {
				if ( $this->postInstall() == true ) {
					$retval = $this->_postPostInstall();
					if ( $retval == true ) {
						$this->getDatabaseConnection()->CompleteTrans();

						return $retval;
					}
				}
			}
		}

		$this->getDatabaseConnection()->FailTrans();

		return false;
	}


}

?>
