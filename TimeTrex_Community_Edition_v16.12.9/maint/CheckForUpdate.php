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

/*
 * Checks for any version updates...
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//Pass "-f" on the command line to force update check.
if ( in_array( '-f', $argv ) ) {
	$force = true;
} else {
	$force = false;
}

$ttsc = new TimeTrexSoapClient();
$clf = new CompanyListFactory();
$clf->getAll();
if ( $clf->getRecordCount() > 0 ) {
	sleep( rand( 0, 60 ) ); //Further randomize when calls are made.

	$i = 0;
	foreach ( $clf as $c_obj ) {
		if ( $i == 0 && $ttsc->getLocalRegistrationKey() == false || $ttsc->getLocalRegistrationKey() == '' ) {
			$ttsc->saveRegistrationKey();
		}

		//We must ensure that the data is up to date
		//Otherwise version check will fail.
		$ttsc->sendCompanyData( $c_obj->getId() );
		$ttsc->sendCompanyUserLocationData( $c_obj->getId() );
		$ttsc->sendCompanyUserCountData( $c_obj->getId() );
		$ttsc->sendCompanyVersionData( $c_obj->getId() );

		//Check for new license once it starts expiring.
		//Help -> About, checking for new versions also gets the updated license file.
		if ( $i == 0 && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( !isset( $system_settings['license'] ) ) {
				$system_settings['license'] = null;
			}

			$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $license = @new $obj_class;
			$license->checkLicenseFile( $system_settings['license'] );
		}

		//Only if forced or update notifications are enabled do we need to create the notification.
		//  The above code still needs to be run though to perform the necessary check.
		if ( $force == true || $ttsc->isUpdateNotifyEnabled() == true ) {
			//Only need to call this on the last company
			if ( $i == ( $clf->getRecordCount() - 1 ) ) {
				$latest_version = $ttsc->isLatestVersion( $c_obj->getId() );
				if ( $latest_version == false ) {
					SystemSettingFactory::setSystemSetting( 'new_version', 1 );

					if ( DEMO_MODE == false && PRODUCTION == true && isset( $config_vars['other']['primary_company_id'] ) ) {
						$link = ( $c_obj->getProductEdition() == TT_PRODUCT_COMMUNITY ) ? 'https://coreapi.timetrex.com/r.php?id=19' : 'https://coreapi.timetrex.com/r.php?id=9';
						$notification_data = [
								'object_id'      => TTUUID::getNotExistID( 1010 ),
								'object_type_id' => 0,
								'type_id'        => 'system',
								'title_short'    => TTi18n::getText( 'NOTICE: New version of %1 is available.', [ APPLICATION_NAME ] ),
								'body_long_html' => TTi18n::getText( 'NOTICE: New version of %1 is available, it is highly recommended that you upgrade as soon as possible. <a href="%2">Click here</a> to download the latest version.', [ APPLICATION_NAME, $link ] ),
								'body_short'     => TTi18n::getText( 'NOTICE: New version of %1 is available, it is highly recommended that you upgrade as soon as possible. Click here to download the latest version.', [ APPLICATION_NAME ] ),
								'payload'        => [ 'link_target' => '_blank', 'link' => $link ],
								'effective_date' => Notification::getNextDecentEffectiveDate(), //Since this maintenance job runs at night or early morning, date the notification to a decent hour like 8AM.
						];

						Notification::sendNotificationToAllUsers( 90, true, true, $notification_data, ( 14 * 86400 ), $config_vars['other']['primary_company_id'] ); //Only send to primary company
					}

				} else {
					SystemSettingFactory::setSystemSetting( 'new_version', 0 );
				}
			}
		} else if ( $i == 0 ) { //Just display this once.
			Debug::Text( 'Auto Update Notifications are disabled!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$i++;
	}
}
?>