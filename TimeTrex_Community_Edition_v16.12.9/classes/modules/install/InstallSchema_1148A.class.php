<?php

/**
 * @package Modules\Install
 */
class InstallSchema_1148A extends InstallSchema_Base {

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
				Debug::text( 'Company: ' . $c_obj->getName() .' ('. $c_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 9 );

				//Make sure pay_stub_amendment -> edit/edit_own/edit_child permissions are allowed.
				$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
				$pclf->getByCompanyId( $c_obj->getId(), null, null, null, [ 'name' => 'asc' ] ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {
						$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $c_obj->getId(), $pc_obj->getId(), 'pay_stub_amendment', [ 'edit', 'edit_own', 'edit_child' ], 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
							Debug::text( '    Found permission group with pay_stub_amendment -> edit/edit_own/edit_child allowed, add cost center fields: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission( [
															'pay_stub_amendment' => [
																	'edit_branch' => true,
																	'edit_department' => true,
																	'edit_job' => true,
																	'edit_job_item' => true,
															],
													] );
						}

						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $c_obj->getId(), $pc_obj->getId(), 'punch', [ 'edit', 'edit_child' ], 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text( '  Permission Group: ' . $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9 );
							Debug::text( '    Found permission group with punch -> edit_child allowed, add edit_disable_rounding: ' . $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9 );
							$pc_obj->setPermission( [
															'punch' => [
																	'edit_disable_rounding' => true,
															],
													] );
						}

					}
				}
				unset( $pclf, $plf );
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
