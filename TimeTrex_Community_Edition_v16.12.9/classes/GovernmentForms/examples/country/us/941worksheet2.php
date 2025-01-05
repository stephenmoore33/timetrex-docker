<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet2_obj = $gf->getFormObject( '941worksheet2', 'US' );
$f941worksheet2_obj->setDebug( TRUE);
$f941worksheet2_obj->setShowBackground( TRUE);

$f941worksheet2_obj->year = 2021;

$f941worksheet2_obj->l1a = 9999.99;
$f941worksheet2_obj->l1b = 9999.98;
$f941worksheet2_obj->l1c = 9999.97;

$f941worksheet2_obj->l1f = 9999.96;
$f941worksheet2_obj->l1h = 9999.95;
$f941worksheet2_obj->l1j = 9999.94;
$f941worksheet2_obj->l1k = 9999.93;
$f941worksheet2_obj->l1l = 9999.92;

$f941worksheet2_obj->l2a = 9999.91;
$f941worksheet2_obj->l2b = 9999.89;
$f941worksheet2_obj->l2e = 9999.88;
$f941worksheet2_obj->l2f = 9999.87;

$gf->addForm( $f941worksheet2_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet2.pdf', $output );
?>