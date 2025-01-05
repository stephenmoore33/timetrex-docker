<?php /** @noinspection PhpMissingDocCommentInspection */
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 *
 ********************************************************************************/

class CustomFieldTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $currency_id = null;
	protected $user_id = null;
	protected $user_ids = [];
	protected $custom_fields = [];
	protected $custom_field_report_values = [];

	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		//TTDate::setTimeZone( 'America/Vancouver', true );
		TTDate::setTimeZone( 'GMT', true ); //Use GMT otherwise DST causes these tests to fail due to PST/PDT timezone changing.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		//Permissions are required so the user has permissions to run reports.
		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );

		$this->custom_fields[100] = $dd->createCustomField( $this->company_id, 'Custom Text', 'users', 100 );
		$this->custom_fields[110] = $dd->createCustomField( $this->company_id, 'Custom Textarea', 'users', 110 );
		$this->custom_fields[400] = $dd->createCustomField( $this->company_id, 'Custom Integer', 'users', 400 );
		$this->custom_fields[410] = $dd->createCustomField( $this->company_id, 'Custom Decimal', 'users', 410 );
		$this->custom_fields[420] = $dd->createCustomField( $this->company_id, 'Custom Currency', 'users', 420 );
		$this->custom_fields[500] = $dd->createCustomField( $this->company_id, 'Custom Checkbox', 'users', 500 );
		$this->custom_fields[1000] = $dd->createCustomField( $this->company_id, 'Custom Date', 'users', 1000 );
		$this->custom_fields[1010] = $dd->createCustomField( $this->company_id, 'Custom Date Range', 'users', 1010 );
		$this->custom_fields[1100] = $dd->createCustomField( $this->company_id, 'Custom Time', 'users', 1100 );
		$this->custom_fields[1200] = $dd->createCustomField( $this->company_id, 'Custom Datetime', 'users', 1200 );
		$this->custom_fields[1300] = $dd->createCustomField( $this->company_id, 'Custom Time Unit', 'users', 1300 );
		$this->custom_fields[2100] = $dd->createCustomField( $this->company_id, 'Custom Single-select', 'users', 2100, [ 'validation' => [ 'multi_select_items' => [ [ 'id' => 'val1', 'label' => 'label1' ], [ 'id' => 'val2', 'label' => 'label2' ] ] ] ] );
		$this->custom_fields[2110] = $dd->createCustomField( $this->company_id, 'Custom Multi-select', 'users', 2110, [ 'validation' => [ 'multi_select_items' => [ [ 'id' => 'val1', 'label' => 'label1' ], [ 'id' => 'val2', 'label' => 'label2' ] ] ] ] );

		$this->user_ids[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10, null, null, null, null, null, null, null, null, null, null, null, null, null, $this->createCustomFieldData() );
		$this->user_ids[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 11, null, null, null, null, null, null, null, null, null, null, null, null, null, $this->createCustomFieldData() );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	/**
	 * @return array
	 */
	function createCustomFieldData() {
		$custom_field_data = [];
		foreach ( $this->custom_fields as $custom_field_type_id => $custom_field_id ) {
			switch ( $custom_field_type_id ) {
				case 100:
					$custom_field_data['custom_field-'. $custom_field_id] = 'Custom Text';
					break;
				case 110:
					$custom_field_data['custom_field-'. $custom_field_id] = 'Custom Textarea';
					break;
				case 400:
					$custom_field_data['custom_field-'. $custom_field_id] = 123;
					break;
				case 410:
					$custom_field_data['custom_field-'. $custom_field_id] = 123.45;
					break;
				case 420:
					$custom_field_data['custom_field-'. $custom_field_id] = 55.99;
					break;
				case 500:
					$custom_field_data['custom_field-'. $custom_field_id] = true;
					break;
				case 1000:
					$custom_field_data['custom_field-'. $custom_field_id] = '2010-01-01';
					break;
				case 1010:
					$custom_field_data['custom_field-'. $custom_field_id] = ['2010-01-01', '2015-01-01'];
					break;
				case 1100:
					$custom_field_data['custom_field-'. $custom_field_id] = '12:00';
					break;
				case 1200:
					$custom_field_data['custom_field-'. $custom_field_id] = '2010-01-01 12:00';
					break;
				case 1300:
					$custom_field_data['custom_field-'. $custom_field_id] = '40260';
					break;
				case 2100:
					$custom_field_data['custom_field-'. $custom_field_id] = 'val1';
					break;
				case 2110:
					$custom_field_data['custom_field-'. $custom_field_id] = ['val1', 'val2'];
					break;
			}
		}

		return $custom_field_data;
	}

	/**
	 * @return array
	 */
	function getExpectedCustomFieldData() {
		$custom_field_data = [];
		foreach ( $this->custom_fields as $custom_field_type_id => $custom_field_id ) {
			switch ( $custom_field_type_id ) {
				case 100:
					$custom_field_data['custom_field-' . $custom_field_id] = 'Custom Text';
					break;
				case 110:
					$custom_field_data['custom_field-' . $custom_field_id] = 'Custom Textarea';
					break;
				case 400:
					$custom_field_data['custom_field-' . $custom_field_id] = 123;
					break;
				case 410:
					$custom_field_data['custom_field-' . $custom_field_id] = 123.45;
					break;
				case 420:
					$custom_field_data['custom_field-' . $custom_field_id] = '55.99'; //Currency, use a string rather than float()
					break;
				case 500:
					$custom_field_data['custom_field-' . $custom_field_id] = 'Yes';
					$custom_field_data['custom_field-' . $custom_field_id . '_id'] = true;
					break;
				case 1000:
					$custom_field_data['custom_field-' . $custom_field_id] = '01-Jan-10';
					$custom_field_data['custom_field-' . $custom_field_id . '_id'] = '01-Jan-10';
					break;
				case 1010:
					$custom_field_data['custom_field-' . $custom_field_id] = '01-Jan-10 - 01-Jan-15';
					$custom_field_data['custom_field-' . $custom_field_id . '_id'] = [ '2010-01-01', '2015-01-01' ];
					break;
				case 1100:
					$custom_field_data['custom_field-' . $custom_field_id] = '12:00 PM GMT';
					break;
				case 1200:
					$custom_field_data['custom_field-' . $custom_field_id] = '01-Jan-10 12:00 PM GMT';
					break;
				case 1300:
					$custom_field_data['custom_field-' . $custom_field_id] = '40260';
					$this->custom_field_report_values['custom_field-' . $custom_field_id] = '11.1833333333';
					break;
				case 2100:
					$custom_field_data['custom_field-' . $custom_field_id] = 'label1';
					$custom_field_data['custom_field-' . $custom_field_id . '_id'] = [ 'val1' ];
					break;
				case 2110:
					$custom_field_data['custom_field-' . $custom_field_id] = 'label1, label2';
					$custom_field_data['custom_field-' . $custom_field_id . '_id'] = [ 'val1', 'val2' ];
					break;
			}
		}

		return $custom_field_data;
	}

	/**
	 * @group CustomField_testAddCustomFieldOnRecord
	 */
	function testAddCustomFieldOnRecord() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $this->user_ids[0], $this->company_id );

		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$custom_fields = $u_obj->getCustomFields( $this->company_id, [] );
		}

		$this->assertCount( 18, $custom_fields );
		$this->assertTrue( @array_diff_assoc( $this->getExpectedCustomFieldData(), $custom_fields ) === [] );
	}

	/**
	 * @group CustomField_testEditCustomFieldOnRecord
	 */
	function testEditCustomFieldOnRecord() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_ids[0] );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */

			//Get current custom fields and change two and save again.
			$custom_fields = $u_obj->getCustomFields( $this->company_id, [] );
			$custom_fields[ 'custom_field-' . $this->custom_fields[100]] = 'Custom Text Edited';
			$custom_fields[ 'custom_field-' . $this->custom_fields[1000] . '_id'] = $custom_fields[ 'custom_field-' . $this->custom_fields[1000] ] = '15-Dec-22';
			$custom_fields[ 'custom_field-' . $this->custom_fields[2100] . '_id'] = 'val2';
			$u_obj->parseCustomFieldsFromArray( $custom_fields );

			if ( $u_obj->isValid() ) {
				$u_obj->Save();
			}
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_ids[0] );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */

			//Get expected custom fields and compare to actual after editing.
			$custom_fields = $this->getExpectedCustomFieldData();
			$custom_fields[ 'custom_field-' . $this->custom_fields[100]] = 'Custom Text Edited';
			$custom_fields[ 'custom_field-' . $this->custom_fields[1000] . '_id'] = $custom_fields[ 'custom_field-' . $this->custom_fields[1000] ] = '15-Dec-22';
			$custom_fields[ 'custom_field-' . $this->custom_fields[2100]] = 'label2';

			$this->assertTrue( @array_diff_assoc( $custom_fields, $u_obj->getCustomFields( $this->company_id, [] ) ) === [] );

		}

		return true;
	}

	/**
	 * @group CustomField_testEditBlankCustomFieldOnRecord
	 */
	function testEditBlankCustomFieldOnRecord() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_ids[0] );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */

			//Get current custom fields and change blank out several fields to ensure they save as that.
			$custom_fields = $u_obj->getCustomFields( $this->company_id, [] );
			$custom_fields[ 'custom_field-' . $this->custom_fields[100]] = '';
			$custom_fields[ 'custom_field-' . $this->custom_fields[1000] . '_id'] = $custom_fields[ 'custom_field-' . $this->custom_fields[1000] ] = '';
			$custom_fields[ 'custom_field-' . $this->custom_fields[2100] . '_id'] = '';
			$u_obj->parseCustomFieldsFromArray( $custom_fields );

			if ( $u_obj->isValid() ) {
				$u_obj->Save();
			}
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_ids[0] );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */

			//Get expected custom fields and compare to actual after editing.
			$custom_fields = $this->getExpectedCustomFieldData();
			$custom_fields[ 'custom_field-' . $this->custom_fields[100]] = '';
			$custom_fields[ 'custom_field-' . $this->custom_fields[1000] . '_id'] = $custom_fields[ 'custom_field-' . $this->custom_fields[1000] ] = '';
			$custom_fields[ 'custom_field-' . $this->custom_fields[2100]] = '';

			$this->assertTrue( @array_diff_assoc( $custom_fields, $u_obj->getCustomFields( $this->company_id, [] ) ) === [] );

		}

		return true;
	}

	/**
	 * @group CustomField_testCustomFieldsOnEmployeeSummaryReport
	 */
	function testCustomFieldsOnEmployeeSummaryReport() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		$ulf = new UserListFactory();
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();

		$report_obj = TTnew( 'UserSummaryReport' ); /** @var UserSummaryReport $report_obj */
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( $user_obj->getPermissionObject() );
		$form_config = $report_obj->getCompanyFormConfig();
		$report_obj->setFormConfig( $form_config );

		$report_config['columns'] = [ 'first_name'];
		foreach ( $this->custom_fields as $custom_field_id ) {
			$report_config['columns'][] = 'custom_field-' . $custom_field_id;
		}

		$report_config['include_user_id'] = [ $this->user_ids[0], $this->user_ids[1] ];
		$report_config['sort'] = [ [ 'last_name' => 'asc' ], [ 'first_name' => 'asc' ]	 ];
		$report_config['template'] = 'by_employee+contact';


		$report_obj->setConfig( $report_config );

		$report_output = $report_obj->getOutput( 'raw' );

		foreach ( $this->getExpectedCustomFieldData() as $key => $value ) {
			if ( substr( $key, -3 ) == '_id' ) {
				continue; //Skip checking backed _id custom field as report only uses the display values.
			}

			//Time unit custom fields are stored as seconds, but converted to human-readable format in the report and JavaScript.
			//Due to that we need to compare against $this->custom_field_report_values for certain values.
			if ( isset( $this->custom_field_report_values[$key] ) === true && $report_output[0][$key] !== $value ) {
				$value = $this->custom_field_report_values[$key];
			}

			$this->assertTrue( $report_output[0][$key] === $value );
		}

		//Test grand totals
		$this->assertTrue( $report_output[2]['custom_field-' . $this->custom_fields[400]] === '246.0000000000' );
		$this->assertTrue( $report_output[2]['custom_field-' . $this->custom_fields[420]] === '111.9800000000' );
		$this->assertTrue( $report_output[2]['custom_field-' . $this->custom_fields[1300]] === '22.3666666666' );
	}

	/**
	 * @group CustomField_testCustomFieldsOnEmployeeSummaryReport
	 */
	function testSearchCustomField() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		//Correct filter data, matches 2 employees.
		$filter_data = [
			'permission_current_user_id' =>	$this->user_id,
			'custom_field-' . $this->custom_fields[100] => 'Custom Text', //Text
			'custom_field-' . $this->custom_fields[500] => true, //Checkbox
			'custom_field-' . $this->custom_fields[1000] => '2010-01-01', //Date
			'custom_field-' . $this->custom_fields[1100] => '12:00', //Time
			'custom_field-' . $this->custom_fields[1200] => '2010-01-01 12:00', //Datetome
			'custom_field-' . $this->custom_fields[2100] => ['val1'], //Single Select Dropdown
			'custom_field-' . $this->custom_fields[2110] => ['val2'],  //Multi-Select Dropdown
		];

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );

		$this->assertTrue( $ulf->getRecordCount() === 2 );

		//Incorrect filter data, matches 0 employees.
		$filter_data = [
				'permission_current_user_id' =>	$this->user_id,
				'custom_field-' . $this->custom_fields[100] => 'Custom Text Wrong', //Text
				'custom_field-' . $this->custom_fields[1000] => '2010-01-09', //Date
				'custom_field-' . $this->custom_fields[2100] => ['val6'], //Single Select Dropdown
				'custom_field-' . $this->custom_fields[2110] => ['val7'],  //Multi-Select Dropdown
		];

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );

		$this->assertTrue( $ulf->getRecordCount() === 0 );
	}

	/**
	 * @group CustomField_testCustomFieldDefaultValues
	 */
	function testCustomFieldDefaultValues() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return true;
		}

		$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieleListFactory $clf */
		$cflf->getById( $this->custom_fields[100] )->getCurrent(); //Text
		if ( $cflf->getRecordCount() > 0 ) {
			$cf_obj = $cflf->getCurrent();
			$cf_obj->setDefaultValue( 'Default Text' );
			$cf_obj->Save();
		}

		$ulf = TTnew( 'UserFactory' ); /** @var UserFactory $ulf */
		$data = $ulf->getCustomFieldsDefaultData( $this->company_id, [] );

		$this->assertTrue( $data['custom_field-' . $this->custom_fields[100]] === 'Default Text' );
	}
}

?>
