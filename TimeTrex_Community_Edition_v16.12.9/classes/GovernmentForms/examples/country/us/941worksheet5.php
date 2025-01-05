<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet5_obj = $gf->getFormObject( '941worksheet5', 'US' );
$f941worksheet5_obj->setDebug( TRUE);
$f941worksheet5_obj->setShowBackground( TRUE);

$f941worksheet5_obj->year = 2021;

$f941worksheet5_obj->l1a = 9999.99;
$f941worksheet5_obj->l1b = 9999.98;
$f941worksheet5_obj->l1d = 2999.97;
$f941worksheet5_obj->l1f = 9999.96;

$f941worksheet5_obj->l2a = 9999.95;
$f941worksheet5_obj->l2c = 1999.94;
$f941worksheet5_obj->l2d = 2999.93;

$gf->addForm( $f941worksheet5_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet5.pdf', $output );
?>