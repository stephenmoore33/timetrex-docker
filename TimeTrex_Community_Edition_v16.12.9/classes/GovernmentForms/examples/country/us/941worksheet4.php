<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet4_obj = $gf->getFormObject( '941worksheet4', 'US' );
$f941worksheet4_obj->setDebug( TRUE);
$f941worksheet4_obj->setShowBackground( TRUE);

$f941worksheet4_obj->year = 2021;

$f941worksheet4_obj->l1a = 9999.99;
$f941worksheet4_obj->l1b = 9999.98;
$f941worksheet4_obj->l1d = 9999.97;
$f941worksheet4_obj->l1f = 9999.96;

$f941worksheet4_obj->l2a = 9999.95;
$f941worksheet4_obj->l2b = 9999.94;
$f941worksheet4_obj->l2f = 9999.93;

$gf->addForm( $f941worksheet4_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet4.pdf', $output );
?>