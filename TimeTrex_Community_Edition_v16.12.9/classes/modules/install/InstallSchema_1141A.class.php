<?php
/*
 * $License$
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1141A extends InstallSchema_Base {

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

		global $COMPANY_DEDUCTION_FILTER_DATE_TYPE_ID_COLUMN;
		$COMPANY_DEDUCTION_FILTER_DATE_TYPE_ID_COLUMN = true;

		return true;
	}
}

?>
