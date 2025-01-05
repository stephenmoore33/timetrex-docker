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
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes/payroll_deduction/PayrollDeduction.class.php' );

if ( in_array( $argv[1] ?? null, [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: generate_payroll_deduction_state_ui_csv.php\n";
	echo $help_output;
} else {
	$country = 'US';

	$cf = new CompanyFactory();
	$province_arr = $cf->getOptions( 'province' );

	if ( !isset( $province_arr[$country] ) ) {
		echo "Country does not have any province/states.\n";
	}
	ksort( $province_arr[$country] );

	//Future year, current year, last year
	for( $x = (TTDate::getYear() - 1); $x <= (TTDate::getYear() + 1); $x++ ) {
		$effective_date_arr[] = strtotime( $x.'-01-01' );
	}
	rsort( $effective_date_arr );

	if ( $country != '' && isset( $province_arr[$country] ) ) {
		foreach ( $province_arr[$country] as $province_code => $province ) {
			$retarr[] = [
					'country'                  => $country,
					'province'                 => $province_code,
					//'date'                     => date( 'm/d/y', $effective_date ),
					//'wage_base'                => 10000,
			];
			$key = array_key_last( $retarr );

			foreach( $effective_date_arr as $effective_date ) {
				//echo "Year: ". date('Y', $effective_date ) ." : $province_code\n";

				$pd_obj = new PayrollDeduction( $country, ( ( $province_code == '00' ) ? 'AK' : $province_code ) ); //Valid state is needed to calculate something, even for just federal numbers.
				$pd_obj->setDate( $effective_date );
				$pd_obj->setAnnualPayPeriods( 26 );

				//Some states have wage base ranges depending on the UI rate, so use a low and high rate to get those.
				$pd_obj->setStateUIRate( 0.01 );
				$low_wage_base = $pd_obj->getStateUIWageBase();

				$pd_obj->setStateUIRate( 99.9 );
				$high_wage_base = $pd_obj->getStateUIWageBase();

				if ( $low_wage_base == $high_wage_base ) {
					$retarr[$key]['wage_base-' . date( 'Y', $effective_date )] = $low_wage_base;
				} else {
					$retarr[$key]['wage_base-' . date( 'Y', $effective_date )] = $low_wage_base.';'. $high_wage_base;
				}

			}
		}

		//generate column array.
		$column_keys = array_keys( $retarr[0] );
		foreach ( $column_keys as $column_key ) {
			$columns[$column_key] = $column_key;
		}

		//var_dump($test_data);
		//var_dump($retarr);
		echo Misc::Array2CSV( $retarr, $columns, false, $include_header = true );
	}
}
//Debug::Display();
?>
