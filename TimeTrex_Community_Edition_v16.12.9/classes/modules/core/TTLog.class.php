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
class TTLog {
	/**
	 * @param string $object_id UUID
	 * @param int $action_id
	 * @param $description
	 * @param string $user_id   UUID
	 * @param $table
	 * @param null $object
	 * @return bool
	 */
	static function addEntry( $object_id, $action_id, $description, $user_id, $table, $object = null ) {
		global $config_vars;

		if ( isset( $config_vars['other']['disable_audit_log'] ) && $config_vars['other']['disable_audit_log'] == true ) {
			return true;
		}

		if ( $object_id == '' ) {
			return false;
		}

		if ( $action_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			global $current_user;
			if ( is_object( $current_user ) && is_a( $current_user, 'UserFactory' ) ) { //Make sure we ignore Portal users.
				Debug::text( 'User Class: ' . get_class( $current_user ) . ' Full Name: ' . $current_user->getFullName(), __FILE__, __LINE__, __METHOD__, 10 );
				$user_id = $current_user->getId();
			} else {
				$user_id = TTUUID::getZeroID();
			}
		}

		if ( $table == '' ) {
			return false;
		}

		$lf = TTnew( 'LogFactory' ); /** @var LogFactory $lf */

		$lf->setObject( $object_id );
		$lf->setAction( $action_id );
		$lf->setTableName( $table );
		$lf->setUser( TTUUID::castUUID( $user_id ) );
		$lf->setDescription( $description );
		$lf->setDate( time() );

		//Debug::text('Object ID: '. $object_id .' Action ID: '. $action_id .' Table: '. $table .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
		if ( $lf->isValid() === true ) {
			$insert_id = $lf->Save();

			if ( (
							!isset( $config_vars['other']['disable_audit_log_detail'] )
							|| ( isset( $config_vars['other']['disable_audit_log_detail'] ) && $config_vars['other']['disable_audit_log_detail'] != true )
					)
					&& is_object( $object ) && $object->getEnableSystemLogDetail() == true ) {

				$ldf = TTnew( 'LogDetailFactory' ); /** @var LogDetailFactory $ldf */
				$ldf->addLogDetail( $action_id, $insert_id, $object );
			} else {
				Debug::text( 'LogDetail Disabled... Object ID: ' . $object_id . ' Action ID: ' . $action_id . ' Table: ' . $table . ' Description: ' . $description . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				//Debug::text('LogDetail Disabled... Config: '. (int)$config_vars['other']['disable_audit_log_detail'] .' Function: '. (int)$object->getEnableSystemLogDetail(), __FILE__, __LINE__, __METHOD__, 10);
			}

			return true;
		}

		return false;
	}
}

?>
