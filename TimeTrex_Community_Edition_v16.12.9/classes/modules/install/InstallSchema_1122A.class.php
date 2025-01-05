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
class InstallSchema_1122A extends InstallSchema_Base {

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

		//Add Notifications cronjob to database.
		$cjf = TTnew( 'CronJobFactory' ); /** @var CronJobFactory $cjf */
		$cjf->setName( 'Notifications' );
		$cjf->setMinute( '*' ); //Run as often as we can, especially with post-dated punch reminders such as when to return from lunch.
		$cjf->setHour( '*' );
		$cjf->setDayOfMonth( '*' );
		$cjf->setMonth( '*' );
		$cjf->setDayOfWeek( '*' );
		$cjf->setCommand( 'Notifications.php' );
		$cjf->Save();

		$upnf = TTnew( 'UserPreferenceNotificationFactory' ); /** @var UserPreferenceNotificationFactory $upnf */
		$notification_defaults = $upnf->getUserPreferenceNotificationTypeDefaultValues( [ 'system' ] );

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */
				// Not grabbing company by status == 10 because preference records have to be created even for inactive etc
				Debug::Text( 'Setting initial user (new hire) default notification preferences for Company: ' . $c_obj->getName() .' ('. $c_obj->getID() .')', __FILE__, __LINE__, __METHOD__, 10 );

				//Converting new hire defaults email notification defaults to new hire push notification defaults.
				$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
				$udlf->getByCompanyId( $c_obj->getId(), null, null, [ 'id' => 'asc' ] ); //Need to override order, as the default "display_order" column is not created until schema version 1123A.
				if ( $udlf->getRecordCount() > 0 ) {
					$udf_obj = $udlf->getCurrent(); /** @var UserDefaultFactory $udf_obj */

					foreach ( $notification_defaults as $notification_default_data ) {
						switch ( $notification_default_data['type_id'] ) {
							case 'exception_own_low':
							case 'exception_own_medium':
							case 'exception_own_high':
							case 'exception_own_critical':
							case 'exception_child_low':
							case 'exception_child_medium':
							case 'exception_child_high':
							case 'exception_child_critical':
								if ( $udf_obj->getEnableEmailNotificationException() !== true ) {
									$notification_default_data['status_id'] = 20; //20=Disabled
								}
								break;
							case 'pay_period':
							case 'pay_stub':
								if ( $udf_obj->getEnableEmailNotificationPayStub() !== true ) {
									$notification_default_data['status_id'] = 20; //20=Disabled
								}
								break;
							case 'message':
								if ( $udf_obj->getEnableEmailNotificationMessage() !== true ) {
									$notification_default_data['status_id'] = 20; //20=Disabled
								}
								break;
							default: //All new notification types default to enabled.
								break;
						}

						$notification_default_data['device_id'] = [ 4, 256, 32768 ]; //4=Web Push, 256=Work Email, 32768=App Push
						if ( $udf_obj->getEnableEmailNotificationHome() == true ) {
							$notification_default_data['device_id'][] = 512; //512=Home Email
						}

						//For punch reminders, just force to Web/App notifications, regardless if Home Email was enabled before.
						if ( strpos( $notification_default_data['type_id'], 'reminder_punch_' ) !== false ) {
							$notification_default_data['device_id'] = [ 4, 32768 ];
						}

						$udpnf = TTnew( 'UserDefaultPreferenceNotificationFactory' ); /** @var UserDefaultPreferenceNotificationFactory $udpnf */
						$udpnf->setUserDefault( $udf_obj->getId() );
						$udpnf->setStatus( $notification_default_data['status_id'] );
						$udpnf->setDevice( $notification_default_data['device_id'] );
						$udpnf->setType( $notification_default_data['type_id'] );
						$udpnf->setPriority( $notification_default_data['priority_id'] );
						if ( isset( $notification_default_data['reminder_delay']  ) ) {
							$udpnf->setReminderDelay( $notification_default_data['reminder_delay'] );
						}
						if ( $udpnf->isValid() ) {
							$udpnf->Save();
						} else {
							Debug::Text( '  Failed to create user default notification preference for company: ' . $c_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
					unset($notification_default_data);
				}
				unset( $udlf, $udf_obj, $udpnf );

				////Disable audit logging when creating UserPreferenceNotification records for performance reasons.
				//global $config_vars;
				//$tmp_disable_audit_log = $config_vars['other']['disable_audit_log'];
				//$config_vars['other']['disable_audit_log'] = true;

				$current_epoch = time();

				$upnf = TTnew( 'UserPreferenceNotificationFactory' ); /** @var UserPreferenceNotificationFactory $upnf */

				//Converting old user preferences to new Notification Preference table.
				$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $ulf */
				$uplf->getByCompanyId( $c_obj->getId() );
				if ( $uplf->getRecordCount() > 0 ) {
					Debug::Text( '  Setting initial default notification preferences for total users in this company: ' . $uplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

					$i = 1;
					foreach ( $uplf as $upf_obj ) { /** @var UserPreferenceFactory $upf_obj */
						if ( is_array( $notification_defaults ) ) {
							Debug::Text( '    '. $i .'. Setting initial default notification preferences for User ID: ' . $upf_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
							foreach ( $notification_defaults as $preference_notification_data ) {
								unset( $preference_notification_data['id'] ); //Allow object to be saved with a new ID.

								switch ( $preference_notification_data['type_id'] ) {
									case 'exception_own_low':
									case 'exception_own_medium':
									case 'exception_own_high':
									case 'exception_own_critical':
									case 'exception_child_low':
									case 'exception_child_medium':
									case 'exception_child_high':
									case 'exception_child_critical':
										if ( $upf_obj->getEnableEmailNotificationException() !== true ) {
											$preference_notification_data['status_id'] = 20; //20=Disabled
										}
										break;
									case 'pay_stub':
									case 'pay_period':
										if ( $upf_obj->getEnableEmailNotificationPayStub() !== true ) {
											$preference_notification_data['status_id'] = 20; //20=Disabled
										}
										break;
									case 'message':
										if ( $upf_obj->getEnableEmailNotificationMessage() !== true ) {
											$preference_notification_data['status_id'] = 20; //20=Disabled
										}
										break;
									default: //All new notification types default to enabled.
										break;
								}

								$preference_notification_data['device_id'] = [ 4, 256, 32768 ]; //4=Web Push,256=Work Email, 32768=App Push
								if ( $upf_obj->getEnableEmailNotificationHome() == true ) {
									$preference_notification_data['device_id'][] = 512; //512=Home Email
								}

								//For punch reminders, just force to Web/App notifications, regardless if Home Email was enabled before.
								if ( strpos( $preference_notification_data['type_id'], 'reminder_punch_' ) !== false ) {
									$preference_notification_data['device_id'] = [ 4, 32768 ];
								}

								//  Use optimized direct SQL query below instead to speed this up.
								//$upnf->setUser( $upf_obj->getUser() );
								//$upnf->setStatus( $preference_notification_data['status_id'] );
								//$upnf->setDevice( $preference_notification_data['device_id'] );
								//$upnf->setType( $preference_notification_data['type_id'] );
								//$upnf->setPriority( $preference_notification_data['priority_id'] );
								//if ( isset( $preference_notification_data['reminder_delay']  ) ) {
								//	$upnf->setReminderDelay( $preference_notification_data['reminder_delay'] );
								//}
								//if ( $upnf->isValid() ) {
								//	$upnf->Save();
								//} else {
								//	Debug::Text( 'Failed to create notification preference for user: ' . $upf_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
								//}

								//Duplicate entry for other each user_id.
								$ph = [
										$upnf->getNextInsertId(),
										$upf_obj->getUser(),
										$preference_notification_data['status_id'],
										$preference_notification_data['type_id'],
										$preference_notification_data['priority_id'],
										Option::getBitMaskByArray( $preference_notification_data['device_id'], $upnf->getOptions( 'devices' ) ),
										$current_epoch, //Created Date
										$current_epoch, //Updated Date
								];

								$ph[] = 0; //Deleted

								if ( isset( $preference_notification_data['reminder_delay'] ) ) {
									$ph[] = json_encode( [ 'reminder_delay' => $preference_notification_data['reminder_delay'] ] );
								} else {
									$ph[] = null;
								}

								$query = 'INSERT INTO user_preference_notification (id, user_id, status_id, type_id, priority_id, device_id, created_date, updated_date, deleted, other_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
								//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
								$this->db->Execute( $query, $ph );
							}
							unset( $preference_notification_data );
						}

						$i++;
					}
				}
				unset( $uplf, $upf_obj, $upnf );

				//$config_vars['other']['disable_audit_log'] = $tmp_disable_audit_log; //Restore audit log settings.


				//Set default Pay Formula Accrual Balance defaults so we don't break existing time banks.
				$pfplf = TTnew( 'PayFormulaPolicyListFactory' );
				$pfplf->getByCompanyId( $c_obj->getId() );
				if ( $pfplf->getRecordCount() > 0 ) {
					foreach ( $pfplf as $pfp_obj ) {
						if ( TTUUID::isUUID( $pfp_obj->getAccrualPolicyAccount() ) && $pfp_obj->getAccrualPolicyAccount() != TTUUID::getZeroID() ) { //Only if its linked to an accrual account, otherwise use the default of 0.
							if ( $pfp_obj->getAccrualRate() > 0 ) {
								$balance_threshold = ( 3600 * 999 ); //High positive balance. So its hopefully never hit for backwards compatibility.
							} else {
								$balance_threshold = ( 3600 * -999 ); //Low negative balance. So its hopefully never hit for backwards compatibility.
							}

							$pfp_obj->setAccrualBalanceThreshold( $balance_threshold );
							if ( $pfp_obj->isValid() ) {
								$pfp_obj->Save();
							}
						}
					}
				}
				unset( $pfplf, $pfp_obj );
			}
		}
		$clf->CommitTransaction();

		return true;
	}
}

?>
