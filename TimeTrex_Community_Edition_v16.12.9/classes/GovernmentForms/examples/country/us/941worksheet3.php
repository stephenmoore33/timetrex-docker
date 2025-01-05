<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet3_obj = $gf->getFormObject( '941worksheet3', 'US' );
$f941worksheet3_obj->setDebug( TRUE);
$f941worksheet3_obj->setShowBackground( TRUE);

$f941worksheet3_obj->year = 2021;

$f941worksheet3_obj->l1a = 90000.01;
$f941worksheet3_obj->l1c = 500.02;
$f941worksheet3_obj->l1e = 50.03;

$f941worksheet3_obj->l2a = 9999.99;
$f941worksheet3_obj->l2ai = 9999.89;
$f941worksheet3_obj->l2aiii = 9999.79;
$f941worksheet3_obj->l2b = 9999.98;
$f941worksheet3_obj->l2c = 9999.97;

$f941worksheet3_obj->l2g = 9999.96;
$f941worksheet3_obj->l2gi = 9999.86;
$f941worksheet3_obj->l2giii = 9999.76;
$f941worksheet3_obj->l2h = 9999.95;
$f941worksheet3_obj->l2i = 9999.94;

$f941worksheet3_obj->l2n = 9999.93;
$f941worksheet3_obj->l2o = 9999.92;

$gf->addForm( $f941worksheet3_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet3.pdf', $output );
?>