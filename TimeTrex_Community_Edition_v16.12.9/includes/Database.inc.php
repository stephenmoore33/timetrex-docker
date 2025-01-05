<?php /** @noinspection PhpUndefinedVariableInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection ALL */

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

//Disable database connections when using APIProgressBar, as it doesn't need them anyways, and it adds about 10x overhead to the response time. (30ms instead of 5ms)
if ( isset( $_GET['Class'] ) && $_GET['Class'] === 'APIProgressBar' ) {
	$disable_database_connection = true;
}

define( 'ADODB_DEFAULT_METATYPE', 'C' ); //Default to "C" or char type, which should allow most advanced data types such as geometry/ENUMS/JSON to work without further modifications.
require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb.inc.php' );
require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-exceptions.inc.php' );

$PRIMARY_KEY_IS_UUID = true;
$COMPANY_GENERIC_MAP_DELETED_COLUMN = true; //Schema 1126A added "deleted" column to company_generic_map table, so we have to not use it in SELECT queries prior to 1126A upgrade.

//Use overloading to abstract $db and have calls directly to ADODB
if ( !isset( $disable_database_connection ) ) {
	if ( isset( $config_vars['database']['type'] ) && isset( $config_vars['database']['host'] ) && isset( $config_vars['database']['user'] ) && isset( $config_vars['database']['password'] ) && isset( $config_vars['database']['database_name'] ) ) {
		try {
			if ( isset( $config_vars['cache']['dir'] ) && $config_vars['cache']['dir'] != '' ) {
				$ADODB_CACHE_DIR = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR;
			}

			if ( Debug::getVerbosity() >= 12 ) {
				$ADODB_OUTP = 'ADODBDebug';
				function ADODBDebug( $msg, $newline = true ) {
					Debug::Text( html_entity_decode( strip_tags( $msg ) ), __FILE__, __LINE__, __METHOD__, 11 );

					return true;
				}
			}

			if ( !isset( $config_vars['database']['persistent_connections'] ) ) {
				$config_vars['database']['persistent_connections'] = false;
			}

			//Always force the database type that starts with "postgres" (ie: postgres7, postgres8, ...) to be just 'postgres'.
			if ( strpos( $config_vars['database']['type'], 'postgres' ) !== false ) {
				$config_vars['database']['type'] = 'postgres';
			}

			$ADODB_GETONE_EOF = false; //Make sure GetOne returns FALSE rather then NULL.
			if ( strpos( $config_vars['database']['host'], ',' ) !== false ) {
				require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

				$db = new ADOdbLoadBalancer();

				//Use comma separated database hosts, assuming the first is always the master, the rest are slaves.
				//Anything after the # is the weight. Username/password/database is assumed to be the same across all connections.
				//ie: 127.0.0.1:5433#10,127.0.0.2:5433#100,127.0.0.3:5433#120
				$db_hosts = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
				foreach ( $db_hosts as $db_host_arr ) {
					Debug::Text( 'Adding DB Connection: Host: ' . $db_host_arr[0] . ' Type: ' . $db_host_arr[1] . ' Weight: ' . $db_host_arr[2], __FILE__, __LINE__, __METHOD__, 1 );
					$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
					$db_connection_obj->setConnectionTestCallback( function( $connection_obj, $adodb_obj ) use ( $config_vars ) {
						if ( $connection_obj->type == 'readonly' ) {
							//When connecting to a slave database, make sure the replication delay never exceeds our threshold, otherwise discard the connection and try a different host.
							$maximum_replication_delay = 60; //Seconds
							if ( isset($config_vars['database']['maximum_replication_delay']) && is_numeric( $config_vars['database']['maximum_replication_delay'] ) ) {
								$maximum_replication_delay = (float)$config_vars['database']['maximum_replication_delay'];
							}

							$result = (float)$adodb_obj->GetOne( 'SELECT EXTRACT(epoch FROM ( now() - CASE WHEN pg_last_xact_replay_timestamp() IS NULL THEN now() ELSE pg_last_xact_replay_timestamp() END ) ) AS replication_delay' );
							//Debug::Text( '  Database Replication Delay: '. $result .' Host: '. $connection_obj->host, __FILE__, __LINE__, __METHOD__, 10 );
							if ( $result <= $maximum_replication_delay ) {
								return true;
							} else {
								Debug::Text( 'ERROR: Database Connection is invalid, ignoring! Host: '. $connection_obj->host .' Replication Delay: '. $result, __FILE__, __LINE__, __METHOD__, 1 );
								return false;
							}
						}

						return true; //Always return true for master connections.
					} );
					$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
					$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s O'";


					if ( Debug::getVerbosity() >= 12 ) {
						//Use 1 instead of TRUE, so it only outputs some debugging and not things like backtraces for every cache read/write.
						//Set to 99 to get all debug output.
						$db_connection_obj->getADODbObject()->debug = 1;
					}

					if ( !isset( $config_vars['database']['disable_row_count'] ) || $config_vars['database']['disable_row_count'] == true ) {
						//Dont count rows for pagination, much faster. However two queries must be run to tell if we are at the last page or not.
						$db_connection_obj->getADODbObject()->pageExecuteCountRows = false;
					}
					$db->addConnection( $db_connection_obj );
				}
				unset( $db_hosts, $db_host_arr, $db_connection_obj );

				$db->setSessionInitSQL( 'SET datestyle = \'ISO\'' ); //Needed for ADODB to properly parse dates, as we removed it from ADODB as an optimization so it can be delayed until the first query is executed.
				//$db->setSessionInitSQL( 'SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL REPEATABLE READ' ); //This is required to properly handle simultaneous recalculations of timesheets/pay stubs. We moved this to trigger via setTransactionMode() only for certain operations instead though.
			} else {
				//To enable PDO support. Type: pdo_pgsql
				//$dsn = $config_vars['database']['type'].'://'.$config_vars['database']['user'].':'.$config_vars['database']['password'].'@'.$config_vars['database']['host'].'/'.$config_vars['database']['database_name'].'?persist';
				//$db = ADONewConnection( $dsn );
				$db = ADONewConnection( $config_vars['database']['type'] );
				/** @noinspection PhpUndefinedConstantInspection */
				$db->SetFetchMode( ADODB_FETCH_ASSOC );
				if ( isset( $config_vars['database']['persistent_connections'] ) && $config_vars['database']['persistent_connections'] == true ) {
					$db->PConnect( $config_vars['database']['host'], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
				} else {
					$db->Connect( $config_vars['database']['host'], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
				}

				//Use long timezone format because PostgreSQL 8.1 doesn't support some short names, like SGT,IST
				//Using "e" for the timezone fixes the Asia/Calcutta & IST bug where the two were getting confused.
				//We set the session timezone in PostgreSQL, so 'e' shouldn't be required anymore.
				//$db->fmtTimeStamp = "'Y-m-d H:i:s e'";
				//$db->fmtTimeStamp = "'Y-m-d H:i:s'";
				$db->fmtTimeStamp = "'Y-m-d H:i:s O'"; //Using timezone offset rather than the timezone name allows saving punches like '04-Nov-23 1:15AM PDT' and '04-Nov-23 1:15AM PST' separately. PHP converts the timezone name to the offset for the database, and should avoid any name conflicts too.


				if ( Debug::getVerbosity() >= 12 ) {
					//Use 1 instead of TRUE, so it only outputs some debugging and not things like backtraces for every cache read/write.
					//Set to 99 to get all debug output.
					$db->debug = 1;
				}

				$db->Execute( 'SET datestyle = \'ISO\'' ); //Needed for ADODB to properly parse dates, as we removed it from ADODB as an optimization so it can be delayed until the first query is executed.
				//$db->Execute( 'SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL REPEATABLE READ' ); //This is required to properly handle simultaneous recalculations of timesheets/pay stubs. We moved this to trigger via setTransactionMode() only for certain operations instead though.

				if ( !isset( $config_vars['database']['disable_row_count'] ) || $config_vars['database']['disable_row_count'] == true ) {
					//Dont count rows for pagination, much faster. However two queries must be run to tell if we are at the last page or not.
					$db->pageExecuteCountRows = false;
				}
			}
		} catch ( Exception $e ) {
			Debug::Text( 'Error connecting to the database!', __FILE__, __LINE__, __METHOD__, 1 );
			throw new DBError( $e );
		}


		if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) || ( isset( $_SERVER['SCRIPT_FILENAME'] ) && stripos( $_SERVER['SCRIPT_FILENAME'], 'unattended_upgrade' ) !== false ) ) { //Make sure we always check the schema versions when run from unattended_upgrade, just in case the upgrade failed and we need to determine if we are in UUID mode or not.
			//Make sure that during initial installation we confirm that the database/table actually exists, otherwise this can throw a fatal SQL error.
			$install_obj = new Install();
			$install_obj->setDatabaseConnection( $db ); //Default connection
			if ( $install_obj->checkSystemSettingTableExists() == true ) {
				//Check to see if the DB schema is before the UUID upgrade (schema 1070 or older) and set the $PRIMARY_KEY_IS_UUID accordingly.
				//  THIS IS in tools/unattended_install.php, tools/unattended_upgrade.php, includes/database.inc.php  as well.
				$schema_version_group_a = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' ); //If the database is down, or there are multiple records with the same name, this will return false.
				if ( $schema_version_group_a !== false && (int)$schema_version_group_a < 1100 ) {
					Debug::Text( 'Setting PRIMARY_KEY_IS_UUID to FALSE due to pre-UUID schema version: ' . SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' ), __FILE__, __LINE__, __METHOD__, 1 );
					$PRIMARY_KEY_IS_UUID = false;
				}
				unset( $schema_version_group_a );
			}
		}
	} else {
		Debug::Text( 'Database config options are not set... Unable to connect to database.', __FILE__, __LINE__, __METHOD__, 1 );
		throw new DBError( new Exception );
	}
}

//Set timezone to system local timezone by default. This is so we sync up all timezones in the database and PHP.
//This fixes timezone bugs mainly in maintenance scripts. We used to default this to just GMT, but that can cause additional problems in threaded environments.
//This must be run AFTER the database connection has been made to work properly.
if ( !isset( $config_vars['other']['system_timezone'] ) || ( isset( $config_vars['other']['system_timezone'] ) && $config_vars['other']['system_timezone'] == '' ) ) {
	$config_vars['other']['system_timezone'] = @date( 'e' );
}
TTDate::setTimeZone( $config_vars['other']['system_timezone'], false, false ); //Don't force SQL to be executed here, as an optimization to avoid DB connections when calling things like getProgressBar()
?>
