<?php
/*
 * $License$
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1147A extends InstallSchema_Base {

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
								Debug::text( '    Found permission group with report -> view_generic_tax_summary allowed, add view_us_pers: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
								$pc_obj->setPermission( [ 'report' => [ 'view_us_pers' => true ] ] );
							}
						}
					}
					unset( $pclf, $plf );

					$sp = new SetupPresets();
					$sp->setCompany( $c_obj->getID() );
					$sp->setUser( $c_obj->getAdminContact() );

					$sp->PayrollRemittanceAgencys( 'US', strtoupper( $c_obj->getProvince() ), null, null, null, [ '20:US:'. strtoupper( $c_obj->getProvince() ) .':00:0050' ] ); //Just US PERS Retirement.
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
