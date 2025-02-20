<?php
require_once( '../../classes/modules/api/client/TimeTrexClientAPI.class.php' );

/*
  See instructions on registering an API key here: https://www.timetrex.com/workforce-management-api
 */

/*
 Global variables
*/
$TIMETREX_URL = 'https://demo.timetrex.com/next-release/api/json/api.php';
$TIMETREX_API_KEY = 'API216a7c8871dfb45c43c2bc2d23f086d1ab74e0d7'; //API KEY/Session ID to use for all API requests.

//Confirm that the API key is valid and logged in. This is not required and only recommended to call if an error is triggered.
$api_session = new TimeTrexClientAPI();
$is_logged_in = $api_session->isLoggedIn();
if ( $is_logged_in == false ) {
	echo "API key is invalid.<br>\n";
	exit;
}

//
//Get data for two employees by user_name or primary key/ID.
// - Many other filter methods can be used, such as branch, department, province, state, etc...
//
$user_obj = new TimeTrexClientAPI( 'User' );
//$user_obj->setIdempotentKey( $user_obj->generateUUID() ); //Enable idempotent API call with a random UUID. Pass in FALSE to turn idempotency off.
$result = $user_obj->getUser(
		[
				'filter_data' => [
					//'id' => [ '11e817cb-7dcc-7130-b939-5431e6810149','11e817cb-8385-8e50-97f3-5431e6810149' ],
					'user_name' => 'john.doe1',
				],
		]
);

$user_data = $result->getResult();
print $result;

/* //Example returned data:
array(2) {
  [0]=>
  array(98) {
	["id"]=>
	string(36) "11e7fa4c-f3fd-2d90-b320-21ea65522ba3"
	["company_id"]=>
	string(36) "11e7fa4c-e7b5-37d0-87c6-21ea65522ba3"
	["company"]=>
	string(15) "ABC Company (1)"
	["legal_entity_id"]=>
	string(36) "11e7fa4c-e825-8050-8e4a-21ea65522ba3"
	["legal_name"]=>
	string(18) "ACME USA East Inc."
	["status_id"]=>
	string(2) "10"
	["status"]=>
	string(6) "Active"
	["group_id"]=>
	string(36) "11e7fa4c-e980-6090-adff-21ea65522ba3"
	["user_group"]=>
	string(9) "Corporate"
	["ethnic_group_id"]=>
	string(36) "11e7fa4c-e9a8-2880-9b0d-21ea65522ba3"
	["ethnic_group"]=>
	string(5) "White"
	["user_name"]=>
	string(14) "tristen.braun1"
	["phone_id"]=>
	string(0) ""
	["employee_number"]=>
	int(13)
	["title_id"]=>
	string(36) "11e7fa4c-e98e-4fb0-8cc1-21ea65522ba3"
	["title"]=>
	string(9) "Carpenter"
	["default_branch_id"]=>
	string(36) "11e7fa4c-e857-84b0-9ebc-21ea65522ba3"
	["default_branch"]=>
	string(8) "New York"
	["default_branch_manual_id"]=>
	string(1) "1"
	["default_department_id"]=>
	string(36) "11e7fa4c-e870-7660-b2a1-21ea65522ba3"
	["default_department"]=>
	string(12) "Construction"
	["default_department_manual_id"]=>
	string(1) "2"
	["default_job_id"]=>
	string(36) "00000000-0000-0000-0000-000000000000"
	["default_job"]=>
	bool(false)
	["default_job_manual_id"]=>
	bool(false)
	["default_job_item_id"]=>
	string(36) "00000000-0000-0000-0000-000000000000"
	["default_job_item"]=>
	bool(false)
	["default_job_item_manual_id"]=>
	bool(false)
	["permission_control_id"]=>
	string(36) "11e7fa4c-e7bb-a9b0-a40e-21ea65522ba3"
	["permission_control"]=>
	string(31) "Regular Employee (Punch In/Out)"
	["pay_period_schedule_id"]=>
	string(36) "11e7fa4d-0208-8360-a212-21ea65522ba3"
	["pay_period_schedule"]=>
	string(9) "Bi-Weekly"
	["policy_group_id"]=>
	string(36) "11e7fa4d-025e-93e0-bc8b-21ea65522ba3"
	["policy_group"]=>
	string(7) "Default"
	["first_name"]=>
	string(7) "Tristen"
	["first_name_metaphone"]=>
	string(5) "TRSTN"
	["middle_name"]=>
	bool(false)
	["last_name"]=>
	string(6) "BraunD"
	["last_name_metaphone"]=>
	string(4) "BRNT"
	["full_name"]=>
	string(15) "BraunD, Tristen"
	["second_last_name"]=>
	bool(false)
	["sex_id"]=>
	string(2) "20"
	["sex"]=>
	string(6) "Female"
	["address1"]=>
	string(13) "9289 Ethel St"
	["address2"]=>
	string(9) "Unit #560"
	["city"]=>
	string(8) "New York"
	["country"]=>
	string(2) "US"
	["province"]=>
	string(2) "NY"
	["postal_code"]=>
	string(5) "00521"
	["work_phone"]=>
	string(12) "417-268-2473"
	["work_phone_ext"]=>
	string(3) "204"
	["home_phone"]=>
	string(12) "567-570-8135"
	["mobile_phone"]=>
	bool(false)
	["fax_phone"]=>
	bool(false)
	["home_email"]=>
	bool(false)
	["home_email_is_valid"]=>
	bool(true)
	["home_email_is_valid_key"]=>
	bool(false)
	["home_email_is_valid_date"]=>
	bool(false)
	["feedback_rating"]=>
	bool(false)
	["work_email"]=>
	string(30) "tristen.braun1@abc-company.com"
	["work_email_is_valid"]=>
	bool(true)
	["work_email_is_valid_key"]=>
	bool(false)
	["work_email_is_valid_date"]=>
	bool(false)
	["birth_date"]=>
	string(9) "01-Jun-88"
	["birth_date_age"]=>
	int(29)
	["hire_date"]=>
	string(9) "26-May-13"
	["hire_date_age"]=>
	string(4) "4.74"
	["termination_date"]=>
	NULL
	["currency_id"]=>
	string(36) "11e7fa4c-e80b-8110-97b4-21ea65522ba3"
	["currency"]=>
	string(9) "US Dollar"
	["currency_rate"]=>
	string(12) "1.0000000000"
	["sin"]=>
	string(9) "401240815"
	["note"]=>
	bool(false)
	["longitude"]=>
	bool(false)
	["latitude"]=>
	bool(false)
	["tag"]=>
	string(0) ""
	["last_login_date"]=>
	NULL
	["max_punch_time_stamp"]=>
	NULL
	["deleted"]=>
	bool(false)
	["is_owner"]=>
	bool(false)
	["is_child"]=>
	bool(true)
	["created_by_id"]=>
	string(36) "11e7fa4c-e9e5-4bb0-90f9-21ea65522ba3"
	["created_by"]=>
	string(18) "Mr. AdministratorC"
	["created_date"]=>
	string(17) "15-Jan-18 3:36 PM"
	["updated_by_id"]=>
	string(36) "11e7fa4c-e9e5-4bb0-90f9-21ea65522ba3"
	["updated_by"]=>
	string(18) "Mr. AdministratorC"
	["updated_date"]=>
	string(17) "15-Feb-18 3:04 PM"
  }
  [1]=>
  array(98) {
	["id"]=>
	string(36) "11e7fa4c-f8c2-f040-8ad8-21ea65522ba3"
	["company_id"]=>
	string(36) "11e7fa4c-e7b5-37d0-87c6-21ea65522ba3"
	["company"]=>
	string(15) "ABC Company (1)"
	["legal_entity_id"]=>
	string(36) "11e7fa4c-e833-0860-9978-21ea65522ba3"
	["legal_name"]=>
	string(18) "ACME USA West Inc."
	["status_id"]=>
	string(2) "10"
	["status"]=>
	string(6) "Active"
	["group_id"]=>
	string(36) "11e7fa4c-e985-7df0-85ed-21ea65522ba3"
	["user_group"]=>
	string(15) "Human Resources"
	["ethnic_group_id"]=>
	string(36) "11e7fa4c-e9b2-5420-8bd5-21ea65522ba3"
	["ethnic_group"]=>
	string(6) "Indian"
	["user_name"]=>
	string(9) "jane.doe1"
	["phone_id"]=>
	string(5) "12341"
	["employee_number"]=>
	int(20)
	["title_id"]=>
	string(36) "11e7fa4c-e993-9320-8ba8-21ea65522ba3"
	["title"]=>
	string(15) "General Laborer"
	["default_branch_id"]=>
	string(36) "11e7fa4c-e85c-3ba0-b7aa-21ea65522ba3"
	["default_branch"]=>
	string(7) "Seattle"
	["default_branch_manual_id"]=>
	string(1) "2"
	["default_department_id"]=>
	string(36) "11e7fa4c-e870-7660-b2a1-21ea65522ba3"
	["default_department"]=>
	string(12) "Construction"
	["default_department_manual_id"]=>
	string(1) "2"
	["default_job_id"]=>
	string(36) "00000000-0000-0000-0000-000000000000"
	["default_job"]=>
	bool(false)
	["default_job_manual_id"]=>
	bool(false)
	["default_job_item_id"]=>
	string(36) "00000000-0000-0000-0000-000000000000"
	["default_job_item"]=>
	bool(false)
	["default_job_item_manual_id"]=>
	bool(false)
	["permission_control_id"]=>
	string(36) "11e7fa4c-e7bb-a9b0-a40e-21ea65522ba3"
	["permission_control"]=>
	string(31) "Regular Employee (Punch In/Out)"
	["pay_period_schedule_id"]=>
	string(36) "11e7fa4d-0208-8360-a212-21ea65522ba3"
	["pay_period_schedule"]=>
	string(9) "Bi-Weekly"
	["policy_group_id"]=>
	string(36) "11e7fa4d-025e-93e0-bc8b-21ea65522ba3"
	["policy_group"]=>
	string(7) "Default"
	["first_name"]=>
	string(4) "Jane"
	["first_name_metaphone"]=>
	string(2) "JN"
	["middle_name"]=>
	bool(false)
	["last_name"]=>
	string(3) "Doe"
	["last_name_metaphone"]=>
	string(1) "T"
	["full_name"]=>
	string(9) "Doe, Jane"
	["second_last_name"]=>
	bool(false)
	["sex_id"]=>
	string(2) "20"
	["sex"]=>
	string(6) "Female"
	["address1"]=>
	string(15) "4936 Ontario St"
	["address2"]=>
	string(9) "Unit #993"
	["city"]=>
	string(7) "Seattle"
	["country"]=>
	string(2) "US"
	["province"]=>
	string(2) "WA"
	["postal_code"]=>
	string(5) "98867"
	["work_phone"]=>
	string(12) "558-301-1737"
	["work_phone_ext"]=>
	string(3) "308"
	["home_phone"]=>
	string(12) "464-312-4450"
	["mobile_phone"]=>
	bool(false)
	["fax_phone"]=>
	bool(false)
	["home_email"]=>
	bool(false)
	["home_email_is_valid"]=>
	bool(true)
	["home_email_is_valid_key"]=>
	bool(false)
	["home_email_is_valid_date"]=>
	bool(false)
	["feedback_rating"]=>
	bool(false)
	["work_email"]=>
	string(25) "jane.doe1@abc-company.com"
	["work_email_is_valid"]=>
	bool(true)
	["work_email_is_valid_key"]=>
	bool(false)
	["work_email_is_valid_date"]=>
	bool(false)
	["birth_date"]=>
	string(9) "24-Dec-70"
	["birth_date_age"]=>
	int(47)
	["hire_date"]=>
	string(9) "15-Sep-09"
	["hire_date_age"]=>
	string(4) "8.44"
	["termination_date"]=>
	NULL
	["currency_id"]=>
	string(36) "11e7fa4c-e813-c120-9c2c-21ea65522ba3"
	["currency"]=>
	string(15) "Canadian Dollar"
	["currency_rate"]=>
	string(12) "1.2000000000"
	["sin"]=>
	string(9) "695238280"
	["other_id1"]=>
	bool(false)
	["other_id2"]=>
	bool(false)
	["other_id3"]=>
	bool(false)
	["other_id4"]=>
	bool(false)
	["other_id5"]=>
	bool(false)
	["note"]=>
	bool(false)
	["longitude"]=>
	bool(false)
	["latitude"]=>
	bool(false)
	["tag"]=>
	string(5) "check"
	["last_login_date"]=>
	NULL
	["max_punch_time_stamp"]=>
	NULL
	["deleted"]=>
	bool(false)
	["is_owner"]=>
	bool(false)
	["is_child"]=>
	bool(true)
	["created_by_id"]=>
	string(36) "11e7fa4c-e9e5-4bb0-90f9-21ea65522ba3"
	["created_by"]=>
	string(18) "Mr. AdministratorC"
	["created_date"]=>
	string(17) "15-Jan-18 3:36 PM"
	["updated_by_id"]=>
	string(36) "11e7fa4c-e9e5-4bb0-90f9-21ea65522ba3"
	["updated_by"]=>
	string(18) "Mr. AdministratorC"
	["updated_date"]=>
	string(17) "15-Feb-18 3:04 PM"
  }
}
*/

//
//Update data for the second employee, mark their status as Terminated and update Termination Date
//
if ( isset( $user_data[1] ) ) {
	$user_data[1]['status_id'] = 20; //Terminated
	$user_data[1]['termination_date'] = '02-Jan-18';

	$result = $user_obj->setUser( $user_data[1] );
	if ( $result->isValid() === true ) {
		echo "Employee data saved successfully.<br>\n";
	} else {
		echo "Employee save failed.<br>\n";
		print $result; //Show error messages
	}
}

//
//Update employee record in a single operation. Several records can be updated in a single operation as well.
//
$user_data = [
		'id'               => $user_data[1]['id'], //UUID: 11e7fa4c-f8c2-f040-8ad8-21ea65522ba3
		'termination_date' => '02-Jan-18',
];

$result = $user_obj->setUser( $user_data );
if ( $result->isValid() === true ) {
	echo "Employee data saved successfully.<br>\n";
} else {
	echo "Employee save failed.<br>\n";
	print $result; //Show error messages
}

//
//Get new hire defaults so we pull data from that rather than have to manually specify it each time.
//
$new_hire_defaults_obj = new TimeTrexClientAPI( 'UserDefault' );
$new_hire_defaults = $new_hire_defaults_obj->getUserDefault()->getResult()[0];


//
//Add new employee, several new employees can be added in a single operation as well.
//
$user_data = [
		'status_id'       => 10, //Active
		'first_name'      => 'Michael',
		'last_name'       => 'Jackson',
		'employee_number' => rand( 10000, 99999 ),
		'user_name'       => 'mjackson_' . rand( 10000, 99999 ),
		'password'        => 'whiteglove123',
		'hire_date'       => '01-Oct-09',
		'currency_id'     => $new_hire_defaults['currency_id'],
];

$result = $user_obj->setUser( $user_data );
if ( $result->isValid() === true ) {
	echo "Employee added successfully.<br>\n";
	$insert_id = $result->getResult(); //Get employees new ID on success.
} else {
	echo "Employee save failed.<br>\n";
	print $result; //Show error messages
}


//
//Add punch for employee
//
$punch_obj = new TimeTrexClientAPI( 'Punch' );
$punch_data = [
		'user_id' => $insert_id, //ID from above newly added employee

		'type_id'   => 10, //Normal
		'status_id' => 20, //In

		'time_stamp' => strtotime( '19-Aug-2013 5:50PM' ),

		'branch_id'     => $new_hire_defaults['default_branch_id'], //Branch
		'department_id' => $new_hire_defaults['default_department_id'], //Department
];

$result = $punch_obj->setPunch( $punch_data );
if ( $result->isValid() === true ) {
	echo "Punch added successfully.<br>\n";
	$insert_id = $result->getResult(); //Get employees new ID on success.
} else {
	echo "Punch save failed.<br>\n";
	print $result; //Show error messages
}

//
//Get TimeSheet Summary report data in raw PHP native array format. 'csv' and 'pdf' are also valid formats.
//
$report_obj = new TimeTrexClientAPI( 'TimesheetSummaryReport' );
$config = $report_obj->getTemplate( 'by_employee+all_time' )->getResult();
$result = $report_obj->getTimesheetSummaryReport( $config, 'raw' )->getResult();
echo "Report Data: <br>\n";
var_dump( $result );
?>