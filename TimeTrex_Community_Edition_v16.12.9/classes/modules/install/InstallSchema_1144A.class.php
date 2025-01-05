<?php
/*
 * $License$
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1144A extends InstallSchema_Base {

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

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */

		$clf->StartTransaction();

		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */
				if ( $c_obj->getCountry() == 'US' ) {
					Debug::text( 'Company: ' . $c_obj->getName() .' ('. $c_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 9 );

					//Make sure report -> view_us_eeo permissions are allowed.
					$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
					$pclf->getByCompanyId( $c_obj->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
					if ( $pclf->getRecordCount() > 0 ) {
						foreach ( $pclf as $pc_obj ) {
							$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $c_obj->getId(), $pc_obj->getId(), 'report', 'view_generic_tax_summary', 1 ); //Only return records where permission is ALLOWED.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text( '  Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
								Debug::text( '    Found permission group with report -> view_generic_tax_summary allowed, add view_us_eeo: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission( [ 'report' => [ 'view_us_eeo' => true ] ] );
							}
						}
					}
					unset( $pclf, $plf );

					$sp = new SetupPresets();
					$sp->setCompany( $c_obj->getID() );
					$sp->setUser( $c_obj->getAdminContact() );

					$sp->PayrollRemittanceAgencys( 'US', null, null, null, null, [ '10:US:00:00:0110' ] ); //Just US EEO

					if ( $c_obj->getProvince() == 'CA' ) {
						$sp->PayrollRemittanceAgencys( 'US', 'CA', null, null, null, [ '20:US:CA:00:0110' ] ); //Just CA EEO
					}
				}
			}
		}

		//$clf->FailTransaction();
		$clf->CommitTransaction();

		return true;
	}
}

?>
