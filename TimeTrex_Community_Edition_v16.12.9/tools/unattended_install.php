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

ignore_user_abort( true );
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '1024M' ); //Just in case.

if ( $argc < 1 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: unattended_install.php\n";
	$help_output .= " [-f] = Force upgrade even if INSTALL mode is disabled.\n";
	$help_output .= " [-u] = Default username to create.\n";
	echo $help_output;
} else {
	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '-u', $argv ) ) {
		$user_name = trim( $argv[( array_search( '-u', $argv ) + 1 )] );
	} else {
		$user_name = 'demoadmin';
	}

	if ( in_array( '-f', $argv ) ) {
		$force = true;
	} else {
		$force = false;
	}

	if ( in_array( '-f', $argv ) ) {
		$force = true;
	} else {
		$force = false;
	}

	if ( $force == true ) {
		echo "Force Mode enabled...\n";
		//Force installer_enabled to TRUE so we don't have to manually modify the config file with scripts.
		$config_vars['other']['installer_enabled'] = true;
	}

	//Re-initialize install object with new config file.
	$install_obj = new Install();
	$install_obj->cleanCacheDirectory();
	if ( $install_obj->isInstallMode() == false ) {
		echo "ERROR: Install mode is not enabled in the timetrex.ini.php file!\n";
		exit( 1 );
	} else {
		$check_all_requirements = $install_obj->checkAllRequirements( true );
		if ( $check_all_requirements == 0
			//AND $install_obj->checkTimeTrexVersion() == 0 //This causes unit tests to fail when a new version is released that is newer than the unit test version.
		) {

			$install_obj->setDatabaseConnection( $db ); //Default connection

			//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
			if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
				if ( $install_obj->checkTableExists( 'company' ) == true ) {
					//Table could be created, but check to make sure a company actually exists too.
					$clf = TTnew( 'CompanyListFactory' );
					$clf->getAll();
					if ( $clf->getRecordCount() >= 1 ) {
						$install_obj->setIsUpgrade( true );
					} else {
						//No company exists, send them to the create company page.
						$install_obj->setIsUpgrade( false );
					}
				} else {
					$install_obj->setIsUpgrade( false );
				}
			}

			if ( $install_obj->getIsUpgrade() == false ) {
				if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
					//Check to see if the DB schema is before the UUID upgrade (schema 1070 or older) and set the $PRIMARY_KEY_IS_UUID accordingly.
					//  THIS IS in tools/unattended_install.php, tools/unattended_upgrade.php, includes/database.inc.php  as well.
					$PRIMARY_KEY_IS_UUID = false;

					//Create SQL, always try to install every schema version, as
					//installSchema() will check if its already been installed or not.
					//  InstallSchema_1000A->postInstall() now sets the registration key and UUID seed.
					$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
					$install_obj->createSchemaRange( null, null ); //All schema versions
					$install_obj->setVersions();

					//Clear all cache.
					$install_obj->cleanCacheDirectory();
					$cache->clean();

					$data['other']['installer_enabled'] = 'FALSE';
					$install_obj->writeConfigFile( $data );


					$cf = TTnew( 'CompanyFactory' );
					$cf->StartTransaction();
					$cf->setStatus( 10 ); //Active
					$cf->setProductEdition( getTTProductEdition() );
					$cf->setName( 'ABC Company', true ); //Must force this change due to demo mode being enabled.
					$cf->setShortName( 'ABC' );
					//$cf->setBusinessNumber( '123456789' );
					$cf->setAddress1( '123 Main St' );
					$cf->setAddress2( 'Unit #123' );
					$cf->setCity( 'New York' );
					$cf->setCountry( 'US' );
					$cf->setProvince( 'NY' );
					$cf->setPostalCode( '12345' );
					$cf->setWorkPhone( '555-555-5555' );
					$cf->setEnableAddLegalEntity( true );
					$cf->setEnableAddCurrency( true );
					$cf->setEnableAddPermissionGroupPreset( true );
					$cf->setEnableAddUserDefaultPreset( true );
					$cf->setEnableAddStation( true );
					$cf->setEnableAddPayStubEntryAccountPreset( true );
					$cf->setEnableAddCompanyDeductionPreset( true );
					$cf->setEnableAddRecurringHolidayPreset( true );
					if ( $cf->isValid() ) {
						$company_id = $cf->Save( false );
						$install_obj->writeConfigFile( [ 'other' => [ 'primary_company_id' => $company_id ] ] );

						//Setup admin user.
						$uf = TTnew( 'UserFactory' );
						$uf->setCompany( $company_id );
						$uf->setLegalEntity( $cf->legal_entity_id ); //Legal entity was created as part of $cf->Save() (postSave)
						$uf->setStatus( 10 );
						$uf->setUserName( $user_name );
						$uf->setPassword( 'demo', null, true ); //Force password
						$uf->setEmployeeNumber( 1 );

						$uf->setFirstName( 'Mr.' );
						$uf->setLastName( 'Administrator' );
						$uf->setSex( 10 );
						$uf->setAddress1( rand( 100, 9999 ) . ' Main St' );
						$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
						$uf->setCity( 'New York' );

						$uf->setCountry( 'US' );
						$uf->setProvince( 'NY' );

						$uf->setPostalCode( str_pad( rand( 400, 599 ), 5, 0, STR_PAD_LEFT ) );
						$uf->setWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
						$uf->setWorkPhoneExt( rand( 100, 1000 ) );
						$uf->setHomePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
						$uf->setWorkEmail( 'demoadmin1@abc-company.com' );
						$uf->setSIN( rand( 100, 999 ) . '-' . rand( 100, 999 ) . '-' . rand( 100, 999 ) );
						$uf->setBirthDate( strtotime( rand( 1970, 1990 ) . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) ) );

						if ( is_object( $uf->getCompanyObject() ) ) {
							$uf->setCountry( $uf->getCompanyObject()->getCountry() );
							$uf->setProvince( $uf->getCompanyObject()->getProvince() );
							$uf->setAddress1( $uf->getCompanyObject()->getAddress1() );
							$uf->setAddress2( $uf->getCompanyObject()->getAddress2() );
							$uf->setCity( $uf->getCompanyObject()->getCity() );
							$uf->setPostalCode( $uf->getCompanyObject()->getPostalCode() );
							$uf->setWorkPhone( $uf->getCompanyObject()->getWorkPhone() );
							$uf->setHomePhone( $uf->getCompanyObject()->getWorkPhone() );
						}

						//Get currency
						$clf = TTNew('CurrencyListFactory');
						$clf->getByCompanyIdAndDefault( $company_id, true );
						if ( $clf->getRecordCount() == 1 ) {
							$currency_id = $clf->getCurrent()->getId();
							$uf->setCurrency( $currency_id );
						}
						unset( $clf );

						//Get Permission Control with highest level, assume its for Administrators and use it.
						$pclf = TTnew( 'PermissionControlListFactory' );
						$pclf->getByCompanyId( $company_id, null, null, null, [ 'level' => 'desc' ] );
						if ( $pclf->getRecordCount() > 0 ) {
							$pc_obj = $pclf->getCurrent();
							if ( is_object( $pc_obj ) ) {
								Debug::Text( 'Adding User to Permission Control: ' . $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								$uf->setPermissionControl( $pc_obj->getId() );
							}
						}

						if ( $uf->isValid() ) {
							$user_id = $uf->Save( false );
							$cf->CommitTransaction();

							echo "Install Successfull!\n";
							echo "User Name: " . $uf->getUserName() . " Password: demo\n";
							//Debug::Display();
							exit( 0 );
						} else {
							Debug::Arr( $uf->Validator->getTextErrors(), 'ERROR: Unable to create User!', __FILE__, __LINE__, __METHOD__, 10 );
							Debug::Display();
							echo "ERROR: Unable to create User!\n";
						}
					} else {
						Debug::Text( 'ERROR: Unable to create Company!', __FILE__, __LINE__, __METHOD__, 10 );
						echo "ERROR: Unable to create Company!\n";
					}

					$cf->FailTransaction();
					$cf->CommitTransaction();
				} else {
					Debug::Text( 'ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__, 10 );
					echo "ERROR: Database does not exists!\n";
				}
			} else {
				echo "ERROR: Company already exists, install has likely already occurred!\n";
			}
		} else {
			echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
			echo 'Failed Requirements: ' . implode( ',', $install_obj->getFailedRequirements( true ) ) . "\n";
		}
	}
}
//Debug::Display();
exit( 1 );
?>
