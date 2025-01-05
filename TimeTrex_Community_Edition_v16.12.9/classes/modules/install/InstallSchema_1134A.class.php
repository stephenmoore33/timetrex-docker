<?php
/*
 * $License$
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1134A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */

		$clf->StartTransaction();

		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				if ( $c_obj->getCountry() == 'US' ) {
					Debug::text( 'Company: ' . $c_obj->getName() .' ('. $c_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 9 );

					//Make sure report -> view_us_state_unemployment permissions are allowed.
					$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
					$pclf->getByCompanyId( $c_obj->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
					if ( $pclf->getRecordCount() > 0 ) {
						foreach ( $pclf as $pc_obj ) {
							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $c_obj->getId(), $pc_obj->getId(), 'report', 'view_generic_tax_summary', 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
								Debug::text( '    Found permission group with report -> view_generic_tax_summary allowed, add view_us_state_unemployment: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission( [ 'report' => [ 'view_us_state_unemployment' => true ] ] );
							}
						}
					}
					unset( $pclf, $plf );

					//Convert generic US Federal Unemployment Tax/Deductions to ones with specific calculations.
					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					//Put wildcard at beginning/end incase they have prefixed/appended the name for some reason.
					// Also try to catch: US - (FUTA)Federal Unemployment Insurance
					$cdlf->getByCompanyIdAndName( $c_obj->getID(), '%US%-%Federal Unemployment Insurance%' );
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach( $cdlf as $cd_obj ) {
							if ( $cd_obj->getCalculation() == 15 ) {
								Debug::text( '  Found legacy US Federal Unemployment Insurance Tax / Deduction, ID: ' . $cd_obj->getID() . ' Percent: ' . $cd_obj->getUserValue1() .' Wage Base: '. $cd_obj->getUserValue2(), __FILE__, __LINE__, __METHOD__, 9 );
								Debug::text( '    US Federal Unemployment Insurance Employer Tax / Deduction Matches... Switching to specific calculation ID...', __FILE__, __LINE__, __METHOD__, 9 );
								$cd_obj->setCalculation( 89 );      //89=US - Federal Unemployment Insurance (Employer)
								$cd_obj->ignore_column_list = true; //Prevents SQL errors due to new columns being added later on.
								if ( $cd_obj->isValid() ) {
									$cd_obj->Save();
								} else {
									Debug::text( '  ERROR: Failed saving US Federal Unemployment Insurance Employer Tax / Deduction...', __FILE__, __LINE__, __METHOD__, 9 );
								}
							}
						}
					} else {
						Debug::text( '  No legacy US Federal Unemployment Insurance Employer Tax / Deductions for Company: ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
					}

					//Convert generic US State Unemployment Tax/Deductions to ones with specific calculations.
					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */

					//Put wildcard at beginning/end incase they have prefixed/appended the name for some reason.
					// Also try to catch: AR - (SUTA)Unemployment Insurance - Employer
					$cdlf->getByCompanyIdAndName( $c_obj->getID(), '%-%Unemployment Insurance - Employer%' );
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach( $cdlf as $cd_obj ) {
							if ( $cd_obj->getCalculation() == 15 ) {
								Debug::text( '  Found legacy US State Unemployment Insurance Tax / Deduction: ' . $cd_obj->getName() .' ('. $cd_obj->getID() .') Percent: ' . $cd_obj->getUserValue1() .' Wage Base: '. $cd_obj->getUserValue2(), __FILE__, __LINE__, __METHOD__, 9 );

								//Parse out state.
								preg_match( '/([A-Z]{2})\s*-\s*.*Unemployment\s*Insurance\s*-\s*Employer/i', $cd_obj->getName(), $matches );
								if ( isset( $matches[1] ) && $matches[1] != '' ) {
									$state = $matches[1];

									Debug::text( '    US State Unemployment Insurance Employer Tax / Deduction Matches... Switching to specific calculation ID... State: ' . $state, __FILE__, __LINE__, __METHOD__, 9 );
									$cd_obj->setCalculation( 210 ); //210=US - State Unemployment Insurance (Employer)
									$cd_obj->setCountry( 'US' );
									$cd_obj->setProvince( $state );
									$cd_obj->ignore_column_list = true; //Prevents SQL errors due to new columns being added later on.
									if ( $cd_obj->isValid() ) {
										$cd_obj->Save();
									} else {
										Debug::text( '  ERROR: Failed saving US State Unemployment Insurance Employer Tax / Deduction...', __FILE__, __LINE__, __METHOD__, 9 );
									}
								} else {
									Debug::text( '  ERROR: Unable to parse state from Tax/Deduction name...', __FILE__, __LINE__, __METHOD__, 9 );
								}
								unset( $matches );
							}
						}
					} else {
						Debug::text( '  No legacy US State Unemployment Insurance Employer Tax / Deductions for Company: ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
					}

				}
			}
		}

		//$clf->FailTransaction();
		$clf->CommitTransaction();

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
