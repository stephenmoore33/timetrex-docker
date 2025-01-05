<?php

require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$return941 = $gf->getFormObject( 'RETURN941', 'US' );
$return941->TaxPeriodEndDate = '1967-08-13';
$return941->ReturnType = '941PR';
$return941->ein = '000000000';
$return941->BusinessName1 = '#';
$return941->BusinessNameControl = '-';
$return941->AddressLine = '-';
$return941->City = 'A';
$return941->State = 'WY';
$return941->ZIPCode = '00000';

$gf->addForm( $return941 );

$f941_obj = $gf->getFormObject( '941', 'US' );
$f941_obj->setDebug( true );
$f941_obj->setShowBackground( true );
$f941_obj->year = 2024;
$f941_obj->ein = '12-3456789';
$f941_obj->name = 'John Doe';
$f941_obj->trade_name = 'ABC Company';
$f941_obj->address = '#1232 Main St';
$f941_obj->city = 'New York';
$f941_obj->state = 'NY';
$f941_obj->zip_code = '12345';

$f941_obj->quarter = [ 1, 2, 3, 4 ];
$f941_obj->l1 = 10;
$f941_obj->l2 = 9999.99;
$f941_obj->l3 = 999.99;
$f941_obj->l4 = true;

$f941_obj->l5a = 5999.91;
$f941_obj->l5b = 1999.94;
$f941_obj->l5c = 1999.95;
$f941_obj->l5d = 1999.69;

$f941_obj->l5f = 1.59;

$f941_obj->l7z = 570.10;

$f941_obj->l8 = 1.98;
$f941_obj->l9 = 2000.99;


$f941_obj->l11 = 3.96;


//$f941_obj->l12 = 1.97;
$f941_obj->l13 = 1000.98;

$f941_obj->l14 = 9999.99;
$f941_obj->l15 = 1.99; //Overpayment

$f941_obj->l15a = true;
$f941_obj->l15b = true;

$f941_obj->l16_month1 = 9999.99;
$f941_obj->l16_month2 = 9999.98;
$f941_obj->l16_month3 = 9999.97;

$f941_obj->l17 = true;
$f941_obj->l18 = true;

$gf->addForm( $f941_obj );

//$output = $gf->output( 'xml' );
//file_put_contents( '941.xml', $output );

$output = $gf->output( 'pdf' );
file_put_contents( './941test.pdf', $output );
?>

