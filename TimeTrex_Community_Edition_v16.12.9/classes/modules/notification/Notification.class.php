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
 * @package Modules\Notification
 */
class Notification {

	/**
	 * @param $data_array
	 * @param int $created_before_timestamp
	 * @return bool
	 */
	public static function sendNotification( $data_array, $created_before_timestamp = 0 ) {
		if ( !isset( $data_array['user_id'] ) || $data_array['user_id'] == '' || $data_array['user_id'] == TTUUID::getZeroID() ) {
			return false;
		}

		if ( !isset( $data_array['type_id'] ) || $data_array['type_id'] == '' ) {
			return false;
		}

		if ( !isset( $data_array['object_id'] ) || $data_array['object_id'] == '' ) {
			$data_array['object_id'] = TTUUID::getZeroID();
		}

		$nf = TTnew( 'NotificationFactory' ); /** @var NotificationFactory $nf */
		$nf->setUser( $data_array['user_id'] );
		$nf->setType( $data_array['type_id'] );

		//If no title is specified, can't save the notification to the DB, so try and send it through as a background notification without saving.
		if ( ( ( isset( $data_array['title_short'] ) && $data_array['title_short'] != '' ) || ( isset( $data_array['title_long'] ) && $data_array['title_long'] != '' ) ) ) {
			$nf->setIsBackgroundNotification( false );
		} else {
			$nf->setIsBackgroundNotification( true );
		}


		//Check preferences before the duplication check in getRecentSystemNotificationByUserIdAndObjectAndCreatedBefore(),
		// because if the user doesn't have the notification enabled to begin with, no point in checking if they have received one recently.
		//
		// Also if no title is specified (background notification) always allow that to be sent regardless of preferences.
		// However now that we may need to send background notifications that get changed into local foreground notifications on iOS/Android devices (must get attention notificaitons)
		// we made need to reconsider what constitutes a background notification.
		if ( $nf->getIsBackgroundNotification() == true || $nf->isNotificationEnabledByUserPreference() == true ) {
			if ( $created_before_timestamp !== 0 ) {
				$ntlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $ntlf */
				$ntlf->getRecentSystemNotificationByUserIdAndObjectAndCreatedBefore( $data_array['user_id'], $data_array['object_id'], $created_before_timestamp );
				if ( $ntlf->getRecordCount() > 0 ) {
					Debug::Text( 'Notification has already been sent since: ' . TTDate::getDate('DATE+TIME', $created_before_timestamp ) .' not sending again...', __FILE__, __LINE__, __METHOD__, 10 );
					return false;
				}
			}

			if ( isset( $data_array['body_short'] ) && $data_array['body_short'] != '' ) {
				$nf->setBodyShortText( $data_array['body_short'] );
			}
			if ( isset( $data_array['body_long'] ) && $data_array['body_long'] != '' ) {
				$nf->setBodyLongText( $data_array['body_long'] );
			}
			if ( isset( $data_array['body_long_html'] ) && $data_array['body_long_html'] != '' ) {
				$nf->setBodyLongHtml( $data_array['body_long_html'] );
			}
			if ( isset( $data_array['title_short'] ) && $data_array['title_short'] != '' ) {
				$nf->setTitleShort( $data_array['title_short'] );
			}
			if ( isset( $data_array['title_long'] ) && $data_array['title_long'] != '' ) {
				$nf->setTitleLong( $data_array['title_long'] );
			}
			if ( isset( $data_array['object_id'] ) && $data_array['object_id'] != '' ) {
				$nf->setObject( $data_array['object_id'] );
			}
			if ( isset( $data_array['object_type_id'] ) && $data_array['object_type_id'] != '' ) {
				$nf->setObjectType( $data_array['object_type_id'] );
			}
			if ( isset( $data_array['save_notification'] ) && $data_array['save_notification'] !== '' ) {
				$nf->setEnableSaveNotification( $data_array['save_notification'] );
			}

			//If reminder delays are specified in user preferences, apply them here.
			if  ( is_object( $nf->getUserPreferenceNotificationObject() ) ) {
				$reminder_delay = (int)$nf->getUserPreferenceNotificationObject()->getReminderDelay();
				Debug::Text( '  Reminder Delay: '. $reminder_delay, __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				$reminder_delay = 0;
			}

			if ( isset( $data_array['effective_date'] ) && $data_array['effective_date'] != '' ) {
				$nf->setEffectiveDate( ( $data_array['effective_date'] + $reminder_delay ) );
			} else {
				$nf->setEffectiveDate( ( time() + $reminder_delay ) ); //Always set effective date, as that is the date of the notification rather than the created date.
			}

			if ( isset( $data_array['time_to_live'] ) && $data_array['time_to_live'] != '' ) {
				$nf->setTimeToLive( $data_array['time_to_live'] );
			}

			$priority = 5;
			if ( $nf->getType() != 'system' ) {
				$priority = $nf->getUserPreferenceNotificationObject()->getPriority(); //If no user preferences exist, this could return false.
			} else if ( isset( $data_array['priority_id'] ) && $data_array['priority_id'] != '' ) {
				$priority = $data_array['priority_id'];
			}
			$nf->setPriority( ( ( !empty( $priority ) ) ? $priority : 5 ) );

			if ( !isset( $data_array['payload'] ) ) {
				$data_array['payload'] = [];
			}

			// Ensure TimeTrex payload is always set along with user_id.
			$data_array['payload']['timetrex']['user_id'] = $data_array['user_id'];

			// Set priority in the TimeTrex key so when receiving the push notification on the frontend we can react differently based on the priority.
			$data_array['payload']['timetrex']['priority'] = $nf->getPriority();

			// The notification is sent to the proxy during the preSave() of the NotificationFactory, because of that we manually set the notification ID ahead of time.
			// The ID is required in the notification payload so that the notification can be marked as read once the push notification is clicked on and to create a link to the notification itself.
			$nf->setId( $nf->getNextInsertId() );
			$data_array['payload']['timetrex']['id'] = $nf->getId();

			// Push notifications without a link, link to themselves.
			// This means the payload requires the id of the notitication before the notification is saved and sent.
			if ( !isset( $data_array['payload']['link'] ) || $data_array['payload']['link'] == '' ) {
				$data_array['payload']['link'] = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=Notification&a=view&id=' . $nf->getId() . '&tab=Notification';
			}

			$nf->setPayloadData( $data_array['payload'] );

			if ( isset( $data_array['device_id'] ) ) {
				//Only sending notification to specific devices and not the devices set by user preferences.
				//This is useful in the case of multifactor authentication, where we want to send a notification to the mobile app.
				$nf->setDeviceIds( $data_array['device_id'] );
			}

			//Notifications without titles are background notifications, and should not be saved.
			//Specific notifications can also be set to not be saved such as MFA notifications that we do not want to show up in the notification list.
			if ( $nf->getIsBackgroundNotification() == false && $nf->getEnableSaveNotification() == true ) {
				if ( $nf->isValid() == true ) {
					Debug::Text( 'Notification ID: ' . $nf->getId() . ' Effective Date: ' . TTDate::getDate( 'DATE+TIME', $nf->getEffectiveDate() ), __FILE__, __LINE__, __METHOD__, 10 );
					$nf->save( true, true );

					return true;
				} else {
					Debug::Text( 'WARNING: Notification failed validation!', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'Not saving notification, sending it directly instead... Notification ID: ' . $nf->getId() . ' Effective Date: ' . TTDate::getDate( 'DATE+TIME', $nf->getEffectiveDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				$nf->queueSendNotification();

				return true;
			}
		} else {
			Debug::Text( 'Notification preferences not enabled, skipping notification... IsBackground: '. (int)$nf->getIsBackgroundNotification() .' Type: '. $nf->getType(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Finds the next weekday at 12PM noon to make the notification effective date.
	 * This is useful for maintenance jobs that run in the middle of the night or on weekends that send notifications, so users aren't getting woken up for things like "New Version" notices.
	 * @param $epoch
	 * @return false|int
	 */
	static function getNextDecentEffectiveDate( $epoch = null ) {
		if ( empty( $epoch ) ) {
			$epoch = time();
		}

		$next_week_day_epoch = TTDate::getNearestWeekDay( $epoch, 2 );

		$retval = TTDate::getTimeLockedDate( strtotime( '12:00 PM', $next_week_day_epoch ), $next_week_day_epoch ); //12PM on the day.
		if ( $retval < $epoch ) {
			//Increment to the next weekday
			$next_week_day_epoch = TTDate::incrementDate( $epoch, 1, 'day' );
			$retval = TTDate::getTimeLockedDate( strtotime( '12:00 PM', $next_week_day_epoch ), $next_week_day_epoch ); //12PM on the day.
		}

		Debug::Text( '  Decent Epoch: '. TTDate::getDate('DATE+TIME', $retval ) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' Next Weekday: '. TTDate::getDate('DATE+TIME', $next_week_day_epoch ), __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	/**
	 * @param $user_ids
	 * @param $type_id
	 * @param null $epoch
	 * @param null $object_id
	 * @return bool
	 */
	static function deletePendingNotifications( $type_id, $user_ids = null, $object_id = null, $epoch = null ) {
		$nlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $nlf */
		if ( $user_ids !== null ) {
			//If user ids are supplied, delete pending notifications by user id and optional parameters.
			if ( !is_array( $user_ids ) ) {
				$user_ids = [ $user_ids ];
			}
			$nlf->getPendingByUserIdsAndTypeIdAndObjectId( $user_ids, $type_id, $epoch, $object_id );
		} else if ( $object_id !== null ) {
			//If no user ids are supplied and object id is not null, delete all notications for that type and object id.
			$nlf->getPendingByObjectIdAndTypeId( $object_id, $type_id, $epoch );
		} else {
			Debug::Text( '  No user id or object id provided. Cannot delete pending notifications.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $nlf->getRecordCount() > 0 ) {
			foreach( $nlf as $n_obj ) {
				$n_obj->setDeleted( true );
				if ( $n_obj->isValid() ) {
					Debug::Text( '  Deleting pending notification: '. $n_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$n_obj->Save();
				}
			}

			return true;
		}

		Debug::Text( '  No pending notifications to delete...', __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * @param $minimum_permission_level
	 * @param $per_user_check_function
	 * @param $per_company_check_function
	 * @param $notification_data
	 * @param $minimum_time_between_duplicates
	 * @param null $company_id
	 */
	static function sendNotificationToAllUsers( $minimum_permission_level, $per_user_check_function, $per_company_check_function, $notification_data, $minimum_time_between_duplicates, $company_id = null, $append_email_footer = true ) {
		$clf = TTnew('CompanyListFactory' ); /** @var CompanyListFactory $clf */
		if ( !empty( $company_id ) ) {
			$clf->getById( $company_id );
		} else {
			$clf->getByStatusID( [ 10, 20 ], null, [ 'a.id' => 'asc' ] ); //10=Active, 20=Hold -- Companies on hold should still get notifications, as they can be prime candidates for some notification.
		}

		//Save the original data so we can always revert back to it at the beginning of each loop.
		$original_notification_data = $notification_data;

		if ( $clf->getRecordCount() > 0 ) {
			Debug::Text( 'Attempting to send notification to total companies: ' . $clf->getRecordCount() .' Type: '. $notification_data['type_id'] ?? 'N/A' .' Object ID: '. $notification_data['object_id'] ?? 'N/A', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */
				if ( $per_company_check_function instanceof Closure ) {
					$per_company_result = $per_company_check_function( $c_obj );
				}

				if ( ( is_bool( $per_company_check_function ) && $per_company_check_function == true ) || ( isset( $per_company_result ) && $per_company_result['status'] == true ) ) {
					$company_notification_data = $original_notification_data; //Make sure we start with the fresh original notification data for each iteration to be absolutely sure we avoid cross contamination between companies.

					// Merge in result of company closure if not empty return data.
					if ( isset( $per_company_result ) && isset( $per_company_result['notification_data'] ) && !empty( $per_company_result['notification_data'] ) ) {
						$company_notification_data = array_merge( $company_notification_data, $per_company_result['notification_data'] );
					}

					//If this causes too many problems with HTML emails, we could use a place holder like "#email_footer#" instead if needed?
					//We need to replace $notification_data['body_long_html'] for each company, so copy the original value and replace it in the company loop below.
					// *NOTE: We since we appending this, we have ot make sure we start with the original notification data for each iteration!
					if ( $append_email_footer == true && isset( $company_notification_data['body_long_html'] ) && $company_notification_data['body_long_html'] != '' ) {
						$company_notification_data['body_long_html'] .= '<pre>'. NotificationFactory::addEmailFooter( ( ( is_object( $c_obj ) ) ? $c_obj->getName() : null ) ) .'</pre>';
					}

					$created_before_timestamp = ( TTDate::getTime() - $minimum_time_between_duplicates ); // Check if users already received notification in last 14 days.
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByCompanyIDAndStatusIdAndMinimumPermissionLevelAndRecentNotification( $c_obj->getId(), 10, $minimum_permission_level, $company_notification_data['object_id'], $created_before_timestamp );
					if ( $ulf->getRecordCount() > 0 ) {
						Debug::Text( '  Attempting to send notification to total users: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $ulf as $u_obj ) { /** @var UserFactory $u_obj */
							$notification_data = $company_notification_data; //Make sure we start with the fresh company specific notification data for each iteration to be absolutely sure we avoid cross contamination between users.

							if ( $per_user_check_function instanceof Closure ) {
								$per_user_result = $per_user_check_function( $u_obj, $c_obj );
							}

							if ( ( is_bool( $per_user_check_function ) && $per_user_check_function == true ) || ( isset( $per_user_result ) && $per_user_result['status'] == true ) ) {
								// Merge in result of user closure if not empty return data.
								if ( isset( $per_user_result ) && isset( $per_user_result['notification_data'] ) && !empty( $per_user_result['notification_data'] ) ) {
									$notification_data = array_merge( $notification_data, $per_user_result['notification_data'] );
								}

								$notification_data['user_id'] = $u_obj->getId();

								Notification::sendNotification( $notification_data );
							} else {
								Debug::Text( '    Per User Check failed for User: ' . $u_obj->getFullName() .'('. $u_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
				} else {
					Debug::Text( '  Per Company Check failed for Company: ' . $c_obj->getName() .'('. $c_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}
	}

	/**
	 * @param $last_check_epoch
	 * @return bool
	 */
	static function syncNotifications( $last_check_epoch ) {
		Debug::Text( 'Syncing notifications since: ' . TTDate::getDate('DATE+TIME', $last_check_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		$ttsc = new TimeTrexSoapClient();
		$retval = $ttsc->syncNotifications( $last_check_epoch );
		if ( is_array( $retval ) ) {
			Debug::Text( '  Notifications Received: ' . count( $retval ), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $retval as $sync_notification ) {
				if ( is_string( $sync_notification['per_user_check_function'] ) ) {
					try {
						eval( '$per_user_check_function = ' . $sync_notification['per_user_check_function'] );
					} catch ( ParseError $e ) {
						//Error parsing closure. Set result to false so we do not send notifications to unintended users.
						$per_user_check_function = false;
						Debug::Text( 'Evalulation failed for $per_user_check_function. ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					$per_user_check_function = $sync_notification['per_user_check_function'];
				}

				if ( is_string( $sync_notification['per_company_check_function'] ) ) {
					try {
						eval( '$per_company_check_function = ' . $sync_notification['per_company_check_function'] );
					} catch ( ParseError $e ) {
						//Error parsing closure. Set result to false so we do not send notifications to unintended users.
						$per_company_check_function = false;
						Debug::Text( 'Evalulation failed for $per_company_check_function. ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					$per_company_check_function = $sync_notification['per_company_check_function'];
				}

				Notification::sendNotificationToAllUsers( $sync_notification['minimum_permission_level'], $per_user_check_function, $per_company_check_function, $sync_notification['notification_data'], $sync_notification['minimum_time_between_duplicates'] ); //Send to all companies as this as per company checks.
			}
		} else {
			Debug::Text( '  No Notifications Received...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		SystemSettingFactory::setSystemSetting( 'last_notification_sync_time', ( (int)$last_check_epoch > 0 ) ? $last_check_epoch : TTDate::getTime() );

		return true;
	}
}

?>
