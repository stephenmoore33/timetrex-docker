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
 * Adds recurring holidays X days in advance,
 * This file should run once a day.
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

$offset = 86400 * 371; //371 days - Every Holiday policy below can overwrite this.

$hplf = new HolidayPolicyListFactory();

//Get all holiday policies
$hplf->getAll( null, null, null );

$epoch = time();

foreach ( $hplf as $hp_obj ) { /** @var $hp_obj HolidayPolicyFactory */
	//Get all recurring holidays
	$recurring_holiday_ids = $hp_obj->getRecurringHoliday();

	//Must order recurring holidays to come in order based on static days, so Xmas Eve, Xmas, Boxing day are always in order so they can all be moved to nearest weekdays as a group.
	$rhlf = new RecurringHolidayListFactory();
	$rhlf->getByIdAndCompanyId( $recurring_holiday_ids, $hp_obj->getCompany(), null, [ 'type_id' => 'asc', 'month_int' => 'asc', 'day_of_month' => 'asc' ] );
	if ( $rhlf->getRecordCount() > 0 ) {
		Debug::Text( 'Found Recurring Holidays: '. $rhlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		foreach( $rhlf as $rh_obj ) {
			Debug::Text( 'Found Recurring Holiday: ' . $rh_obj->getName() .' ID: '. $rh_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

			// How many days in the future holidays are populated. Set per holiday policy by users.
			$offset = ( $hp_obj->getHolidayDisplayDays() * 86400 );

			//Get all existing holidays that are already populated, so if two holidays occur one right after another (ie: Xmas Eve, Xmas, Boxing Day), we can adjust them accordingly.
			$exclude_dates = [];
			$hlf = TTnew( 'HolidayListFactory' ); /** @var HolidayListFactory $hlf */
			$hlf->getByHolidayPolicyIdAndStartDateAndEndDate( $hp_obj->getId(), ( $epoch - 86400 ), ( $epoch + $offset ) );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach( $hlf as $h_obj ) {
					if ( strtolower( trim( $rh_obj->getName() ) ) != strtolower( trim( $h_obj->getName() ) ) ) { //Skip the holiday we are trying to populate so it doesn't get duplicated.
						$exclude_dates[] = TTDate::getBeginDayEpoch( $h_obj->getDateStamp() );
					}
				}
			}
			unset( $hlf, $h_obj );

			$next_holiday_date = $rh_obj->getNextDate( $epoch, $exclude_dates );
			Debug::Text( 'Next Holiday Date: ' . TTDate::getDate( 'DATE+TIME', $next_holiday_date ) .' Excluded Dates: '. count( $exclude_dates ), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $next_holiday_date <= ( $epoch + $offset ) ) {
				Debug::Text( 'Next Holiday Date is within Time Period (offset) adding...', __FILE__, __LINE__, __METHOD__, 10 );

				$hf = new HolidayFactory();
				$hf->setHolidayPolicyId( $hp_obj->getId() );
				$hf->setDateStamp( $next_holiday_date );
				$hf->setName( $rh_obj->getName() );
				if ( $hf->isValid() ) {
					$hf->Save();
				}
			} else {
				Debug::Text( 'Next Holiday Date is NOT within Time Period (offset)!', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}
	}
}
?>