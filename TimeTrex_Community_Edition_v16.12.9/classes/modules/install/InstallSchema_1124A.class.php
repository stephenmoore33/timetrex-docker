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
class InstallSchema_1124A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//Used for starting a transaction.
		$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
		$rsclf->StartTransaction();

		$control_query_rs = $this->db->Execute( 'SELECT * FROM recurring_schedule_control' );
		while ( $result_control = $control_query_rs->fetchRow() ) {
			$ph = [
					TTUUID::castUUID( $result_control['id'] ),
					TTUUID::castUUID( $result_control['company_id'] ),
					TTUUID::castUUID( $result_control['recurring_schedule_template_control_id'] ),
					(int)$result_control['start_week'],
					( $this->db->BindDate( $result_control['start_date'] ) === 'null' ) ? null : $this->db->BindDate( $result_control['start_date'] ), //String null is not valid, must be actual null.
					( $this->db->BindDate( $result_control['end_date'] ) === 'null' ) ? null : $this->db->BindDate( $result_control['end_date'] ),     //String null is not valid, must be actual null.
					(int)$result_control['auto_fill'],
					( (int)$result_control['created_date'] === 0 ) ? null : (int)$result_control['created_date'],                                             //Null not 0 if no date.
					( TTUUID::castUUID( $result_control['created_by'] ) === TTUUID::getZeroID() ) ? null : TTUUID::castUUID( $result_control['created_by'] ), //Null not zero id if no user has done the action.
					( (int)$result_control['updated_date'] === 0 ) ? null : (int)$result_control['updated_date'],                                             //Null not 0 if no date.
					( TTUUID::castUUID( $result_control['updated_by'] ) === TTUUID::getZeroID() ) ? null : TTUUID::castUUID( $result_control['updated_by'] ), //Null not zero id if no user has done the action.
					( (int)$result_control['deleted_date'] === 0 ) ? null : (int)$result_control['deleted_date'],                                             //Null not 0 if no date.
					( TTUUID::castUUID( $result_control['deleted_by'] ) === TTUUID::getZeroID() ) ? null : TTUUID::castUUID( $result_control['deleted_by'] ), //Null not zero id if no user has done the action.
					(int)$result_control['deleted'],
					(int)$result_control['display_weeks'],
					TTUUID::castUUID( $result_control['user_id'] ),
			];

			$user_query_rs = $this->db->Execute( 'SELECT user_id FROM recurring_schedule_user WHERE recurring_schedule_control_id = ?', [ TTUUID::castUUID( $result_control['id'] ) ] );
			if ( $user_query_rs->RecordCount() > 0 ) {
				$i = 0;
				while ( $result_user = $user_query_rs->fetchRow() ) {
					if ( $i === 0 ) {
						//Existing entry needs to be updated for first user.
						$this->db->Execute( 'UPDATE recurring_schedule_control SET user_id = ? WHERE id = ?', [ TTUUID::castUUID( $result_user['user_id'] ), TTUUID::castUUID( $result_control['id'] ) ] );
						Debug::Text( 'Updating recurring_schedule_control record: ' . $result_control['id'] . ' User ID: ' . $result_user['user_id'], __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						//Duplicate entry for other each user_id.
						$ph[0] = $rsclf->getNextInsertId();
						$ph[15] = TTUUID::castUUID( $result_user['user_id'] );
						$this->db->Execute( 'INSERT INTO recurring_schedule_control (id, company_id, recurring_schedule_template_control_id, start_week, start_date, end_date, auto_fill, created_date, created_by, updated_date, updated_by, deleted_date, deleted_by, deleted, display_weeks, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $ph );
						Debug::Text( '  Creating recurring_schedule_control record: User: ' . $result_user['user_id'] . ' ID: ' . $ph[0], __FILE__, __LINE__, __METHOD__, 10 );
					}

					$i++;
				}
			} else {
				Debug::Text( '  WARNING: No users assigned to recurring_schedule_control ID: '. $result_control['id'] .' Deleting to avoid NOT NULL constraint failure...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->db->Execute( 'DELETE FROM recurring_schedule_control WHERE id = ?', [ TTUUID::castUUID( $result_control['id'] ) ]  );
			}
			unset( $user_query_rs );
		}

		//After all companies have been converted, check to make sure all records are assigned to a user so schema version 1125A that tries to add a unique constraint doesn't fail causing the upgrade to break and to require manual intervention.
		//  We want to fail here and rollback the transaction instead so a new (fixed) version of the code can be run to pick up where it left off instead.
		$not_null_contraint_query_rs = $this->db->Execute( 'SELECT id FROM recurring_schedule_control WHERE user_id IS NULL' );
		if ( $not_null_contraint_query_rs->RecordCount() > 0 ) {
			Debug::Text( '  ERROR: Found recurring_schedule_control records with no user_id assigned to them: '. $not_null_contraint_query_rs->RecordCount() .' failing now so this schema version can be re-run if necessary...', __FILE__, __LINE__, __METHOD__, 10 );
			$rsclf->FailTransaction();
			return false;
		}
		unset( $not_null_contraint_query_rs );

		$rsclf->CommitTransaction();

		return true;
	}
}

?>
