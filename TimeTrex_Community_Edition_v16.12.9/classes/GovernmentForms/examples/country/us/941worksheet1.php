<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet1_obj = $gf->getFormObject( '941worksheet1', 'US' );
$f941worksheet1_obj->setDebug( TRUE);
$f941worksheet1_obj->setShowBackground( TRUE);

$f941worksheet1_obj->year = 2021;

$f941worksheet1_obj->l1a = 9999.99;
$f941worksheet1_obj->l1b = 9999.98;
$f941worksheet1_obj->l1e = 1999.97;
$f941worksheet1_obj->l1g = 2999.96;
$f941worksheet1_obj->l1i = 1999.95;
$f941worksheet1_obj->l1j = 2999.94;
$f941worksheet1_obj->l1ji = 1999.93;

$f941worksheet1_obj->l2a = 9999.92;
$f941worksheet1_obj->l2ai = 9999.91;
$f941worksheet1_obj->l2aiii = 9999.89;
$f941worksheet1_obj->l2b = 9999.88;

$f941worksheet1_obj->l2e = 9999.87;
$f941worksheet1_obj->l2ei = 9999.86;
$f941worksheet1_obj->l2eiii = 9999.85;
$f941worksheet1_obj->l2f = 9999.84;

$gf->addForm( $f941worksheet1_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet1.pdf', $output );
?>