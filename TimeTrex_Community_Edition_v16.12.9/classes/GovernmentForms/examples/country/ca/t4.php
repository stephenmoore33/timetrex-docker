<?php
/*
 * $License$
 */

require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );
$gf = new GovernmentForms();

$t4_obj = $gf->getFormObject( 'T4', 'CA' );
$t4_obj->setDebug( true );
//$t4_obj->setShowBackground( true );
$t4_obj->setType( 'employee' );
$t4_obj->setStatus( 'O' );
$t4_obj->year = 2023;
$t4_obj->payroll_account_number = '123456789';
$t4_obj->company_name = 'ABC Company';

$t4_obj->sin = '492746316';
$t4_obj->first_name = 'Gale';
$t4_obj->middle_name = 'J';
$t4_obj->last_name = 'Mench';
$t4_obj->address1 = '2944 Gordon St';
$t4_obj->address2 = 'Unit #960';
$t4_obj->city = 'Vancouver';
$t4_obj->province = 'BC';
$t4_obj->postal_code = 'V4T1E3';
$t4_obj->country = 'Canada';
$t4_obj->country_code = 'CA';
$t4_obj->employment_province = 'CA';

$t4_obj->l14 = 123.12;

$gf->addForm( $t4_obj );

$output = $gf->output( 'pdf' );
file_put_contents( 't4.pdf', $output );
//$output = $gf->output( 'xml' );
//var_dump($output);
//file_put_contents( 'roe.blk', $output );
?>
