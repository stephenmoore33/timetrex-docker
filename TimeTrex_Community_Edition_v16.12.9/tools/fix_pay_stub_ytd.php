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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 1 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: fix_pay_stub_ytd.php [options] [company_id]\n";
	$help_output .= "    -date			Date\n";
	$help_output .= "    -n				Dry-run\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '-date', $argv ) ) {
		$epoch = strtotime( trim( $argv[array_search( '-date', $argv ) + 1] ) );
	} else {
		$epoch = time();
	}

	if ( in_array( '-n', $argv ) ) {
		$dry_run = true;
		echo "Using DryRun!\n";
	} else {
		$dry_run = false;
	}

	if ( isset( $argv[$last_arg] ) && $argv[$last_arg] != '' && TTUUID::isUUID( $argv[$last_arg] ) ) {
		$company_id = $argv[$last_arg];
	}


	$filter_start_date = TTDate::getBeginYearEpoch( $epoch );
	$filter_end_date = TTDate::getEndYearEpoch( $epoch );


	//Force flush after each output line.
	ob_implicit_flush( true );
	ob_end_flush();

	//TTDate::setTimeZone( 'UTC' ); //Always force the timezone to be set.

	Debug::text( 'Start Date: ' . TTDate::getDate('DATE', $filter_start_date ) .' End Date: '. TTDate::getDate('DATE', $filter_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

	$total_users = 0;
	$total_recalculated_users = 0;
	$total_recalculated_users_errors = 0;

	$clf = new CompanyListFactory();
	$clf->getAll();
	if ( $clf->getRecordCount() > 0 ) {
		foreach ( $clf as $c_obj ) {
			if ( isset( $company_id ) && $company_id != '' && $company_id != $c_obj->getId() ) {
				continue;
			}

			//if ( !in_array( $c_obj->getID(), array(1310,1347) ) ) {
			//	continue;
			//}

			if ( $c_obj->getStatus() != 30 ) {
				$clf->StartTransaction();

				Debug::text( 'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				echo "\n\n".'Company: ' . $c_obj->getName() . ' ID: ' . $c_obj->getID() . "\n";

				//Get Pay Stub Entry Account ID for Net Pay.
				$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
				$pseallf->getByCompanyId( $c_obj->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pay_stub_entry_account_link_obj = $pseallf->getCurrent();

					$net_pay_ps_account_id = $pay_stub_entry_account_link_obj->getTotalNetPay();
					Debug::text( ' Net Pay PS Account ID: '. $net_pay_ps_account_id, __FILE__, __LINE__, __METHOD__, 10 );

					$ulf = TTnew('UserListFactory'); /** @var UserListFactory $ulf */
					$ulf->getByCompanyId( $c_obj->getId() );
					if ( $ulf->getRecordCount() > 0 ) {
						foreach( $ulf as $u_obj ) {
							//if ( $u_obj->getId() != '05a2b435-5a46-cb8f-1c14-6b2027c3ea62' ) {
							//	continue;
							//}

							Debug::text( '  User: ' . $u_obj->getFullName() . ' ID: ' . $u_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
							//echo '  User: ' . $u_obj->getFullName() . ' ID: ' . $u_obj->getID() . "\n";
							echo '.';

							$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
							$pay_stub_entry_arr = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $u_obj->getID(), $net_pay_ps_account_id, $filter_end_date ); //Must use end-date, as it will automatically go to the start date of the year.
							if ( $pay_stub_entry_arr['amount'] == $pay_stub_entry_arr['ytd_amount'] ) {
								Debug::text( '    Net Pay YTD in-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
								//echo "\n".'    Net Pay YTD in-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'] . "\n";
							} else {
								Debug::text( '    Net Pay YTD out-of-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
								echo "\n";
								echo '  User: ' . $u_obj->getFullName() . ' ID: ' . $u_obj->getID() . "\n";
								echo '    **Recalculating Net Pay YTD out-of-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'] . "\n";

								//Get first pay stub of the year, then trigger a recalculate on it.
								$psf = TTnew('PayStubFactory');
								$psf->setUser( $u_obj->getID() );
								$psf->setTransactionDate( $filter_start_date );
								$psf->setRun( 1 );
								$psf->recalculateYTD();

								$pay_stub_entry_arr = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $u_obj->getID(), $net_pay_ps_account_id, $filter_end_date ); //Must use end-date, as it will automatically go to the start date of the year.
								if ( $pay_stub_entry_arr['amount'] == $pay_stub_entry_arr['ytd_amount'] ) {
									Debug::text( '        Net Pay YTD in-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
									echo '        Net Pay YTD in-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'] . "\n";
								} else {
									Debug::text( '      ERROR: Net Pay YTD still out-of-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'], __FILE__, __LINE__, __METHOD__, 10 );
									echo '        ERROR: Net Pay YTD still out-of-sync: YTD: ' . $pay_stub_entry_arr['ytd_amount'] . ' Actual (sum): ' . $pay_stub_entry_arr['amount'] . "\n";
									$total_recalculated_users_errors++;
								}

								$total_recalculated_users++;
							}

							$total_users++;
						}
					}
				}

				if ( $dry_run == true ) {
					$clf->FailTransaction();
				}
				$clf->CommitTransaction();
			}
		}
	}

	echo "\n";
	echo "Users: Checked: ". $total_users ." Recalculated: ". $total_recalculated_users ." Errors: ". $total_recalculated_users_errors ."\n";
}
echo "Done...\n";
Debug::WriteToLog();
Debug::Display();
?>
