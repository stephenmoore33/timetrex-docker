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
class InstallSchema_1004A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		$tables = $this->getDatabaseConnection()->MetaTables();
		if ( in_array( 'log', $tables ) ) {
			//Make sure this only runs for PGSQL databases.

			if ( strncmp( $this->getDatabaseConnection()->databaseType, 'postgres', 8 ) == 0 ) {
				//Upgrading, rename log file.
				$query = 'alter table "log" rename to "system_log"';
				$this->getDatabaseConnection()->Execute( $query );

				$query = 'alter index "log_id" rename to "system_log_id"';
				$this->getDatabaseConnection()->Execute( $query );

				$query = 'alter index "log_user_id_table_name_action_id" rename to "system_log_user_id_table_name_action_id";';
				$this->getDatabaseConnection()->Execute( $query );

				$query = 'alter table "log_id_seq" rename to "system_log_id_seq";';
				$this->getDatabaseConnection()->Execute( $query );
			} else {
				Debug::text( 'preInstall: Not a PGSQL database, skipping special commands', __FILE__, __LINE__, __METHOD__, 9 );
			}
		}


		return true;
	}


	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}
}

?>
