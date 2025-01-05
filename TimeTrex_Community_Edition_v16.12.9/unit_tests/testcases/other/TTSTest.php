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

class TTSTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	/**
	 * @group TTS
	 */
	function testTTSLogical() {
		$schema_data = new TTS(); //Loads all the TTS classes.

		$this->assertEquals( true, TTSLogical::new( 'and', true, true )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', true, false )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', false, false )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', false, true )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'and', true, true, true, true, true )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', true, true, true, true, false )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', false, false, false, false, false )->eval() );

		$this->assertEquals( true, TTSLogical::new( 'and', TTSLogical::new( 'and', true, true ), TTSLogical::new( 'and', true, true ), TTSLogical::new( 'and', true, true ) )->eval() );     //Nested
		$this->assertEquals( false, TTSLogical::new( 'and', TTSLogical::new( 'and', true, true ), TTSLogical::new( 'and', true, true ), TTSLogical::new( 'and', true, false ) )->eval() );   //Nested
		$this->assertEquals( false, TTSLogical::new( 'and', TTSLogical::new( 'and', true, false ), TTSLogical::new( 'and', true, false ), TTSLogical::new( 'and', true, false ) )->eval() ); //Nested
		$this->assertEquals( true, TTSLogical::new( 'and', TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, true ) ) ) ) )->eval() );     //Nested
		$this->assertEquals( false, TTSLogical::new( 'and', TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, TTSLogical::new( 'and', true, false ) ) ) ) )->eval() );     //Nested

		$this->assertEquals( true, TTSLogical::new( 'and', TTSComparison::new( true, '==', true ), TTSComparison::new( true, '==', true ) )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'and', TTSComparison::new( true, '==', true ), TTSComparison::new( true, '==', false ) )->eval() );


		$this->assertEquals( true, TTSLogical::new( 'or', true, true )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'or', true, false )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'or', false, false )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'or', false, true )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'or', true, true, true, true, true )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'or', true, true, true, true, false )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'or', false, false, false, false, false )->eval() );

		$this->assertEquals( true, TTSLogical::new( 'or', TTSLogical::new( 'or', true, true ), TTSLogical::new( 'or', true, true ), TTSLogical::new( 'or', true, true ) )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'or', TTSLogical::new( 'or', false, false ), TTSLogical::new( 'or', false, false ), TTSLogical::new( 'or', false, false ) )->eval() );

		$this->assertEquals( true, TTSLogical::new( 'or', TTSComparison::new( true, '==', true ), TTSComparison::new( true, '==', true ) )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'or', TTSComparison::new( true, '==', false ), TTSComparison::new( true, '==', false ) )->eval() );


		$this->assertEquals( false, TTSLogical::new( 'not', true, null )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'not', false, null )->eval() );
	}

	function testTTSComparison() {
		$schema_data = new TTS(); //Loads all the TTS classes.

		$this->assertEquals( true, TTSComparison::new( true, 'and', true )->eval() );
		$this->assertEquals( false, TTSComparison::new( true, 'and', false )->eval() );
		$this->assertEquals( false, TTSComparison::new( false, 'and', false )->eval() );
		$this->assertEquals( false, TTSComparison::new( false, 'and', true )->eval() );


		$this->assertEquals( true, TTSComparison::new( true, 'or', true )->eval() );
		$this->assertEquals( true, TTSComparison::new( true, 'or', false )->eval() );
		$this->assertEquals( true, TTSComparison::new( false, 'or', true )->eval() );
		$this->assertEquals( false, TTSComparison::new( false, 'or', false )->eval() );


		$this->assertEquals( false, TTSComparison::new( true, 'not', null )->eval() );
		$this->assertEquals( true, TTSComparison::new( false, 'not', null )->eval() );


		$this->assertEquals( true, TTSComparison::new( 1, '==', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '==', 0 )->eval() );
		$this->assertEquals( true, TTSComparison::new( 1, '===', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '===', '1' )->eval() );

		$this->assertEquals( true, TTSComparison::new( 1, '>', 0 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '>', 1 )->eval() );

		$this->assertEquals( true, TTSComparison::new( 0, '<', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '<', 1 )->eval() );

		$this->assertEquals( true, TTSComparison::new( 1, '>=', 0 )->eval() );
		$this->assertEquals( true, TTSComparison::new( 1, '>=', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '>=', 2 )->eval() );

		$this->assertEquals( true, TTSComparison::new( 0, '<=', 1 )->eval() );
		$this->assertEquals( true, TTSComparison::new( 1, '<=', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 2, '<=', 1 )->eval() );

		$this->assertEquals( true, TTSComparison::new( 0, '!=', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '!=', 1 )->eval() );

		$this->assertEquals( true, TTSComparison::new( 0, '!==', 1 )->eval() );
		$this->assertEquals( false, TTSComparison::new( 1, '!==', 1 )->eval() );
		$this->assertEquals( true, TTSComparison::new( 1, '!==', '1' )->eval() );

		$this->assertEquals( true, TTSComparison::new( 10, 'in_array', [ 10, 20 ] )->eval() );
		$this->assertEquals( false, TTSComparison::new( 15, 'in_array', [ 10, 20 ] )->eval() );
		$this->assertEquals( false, TTSLogical::new( 'not', TTSComparison::new( 10, 'in_array', [ 10, 20 ] ) )->eval() );
		$this->assertEquals( true, TTSLogical::new( 'not', TTSComparison::new( 15, 'in_array', [ 10, 20 ] ) )->eval() );
	}

	function testTTSHandlers() {
		$schema_data = new TTS();
		$schema_data->setRecordData( [ 'type_id' => 10 ] );

		$schema_data->setTabs(
				TTSTabs::new(
						TTSTab::new( 'tab_employee' )->setLabel( TTi18n::getText( 'Employee' ) )->setMultiColumn( true )->setFields(
								TTSFields::new(
										TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) ),
										TTSField::new( 'type_10' )->setType( 'text' )->setLabel( TTi18n::getText( 'Type10' ) )
												->setHandlers( [ 'visible' => TTSComparison::new( 10, '==', TTSData::get( 'type_id' ) ), 'readonly' => TTSComparison::new( 20, '==', TTSData::get( 'type_id' ) ) ] ),
										TTSField::new( 'type_20' )->setType( 'text' )->setLabel( TTi18n::getText( 'Type20' ) )
												->setHandlers( [ 'visible' => TTSComparison::new( 20, '==', TTSData::get( 'type_id' ) ), 'readonly' => TTSComparison::new( 10, '==', TTSData::get( 'type_id' ) ) ] ),
										TTSField::new( 'type_10_and_20' )->setType( 'text' )->setLabel( TTi18n::getText( 'Type10And20' ) )
												->setHandlers( [ 'visible' => TTSComparison::new( TTSData::get( 'type_id' ), 'in_array', [ 10, 20 ] ), 'readonly' => TTSLogical::new( 'not', TTSComparison::new( TTSData::get( 'type_id' ), 'in_array', [ 10, 20 ] ) ) ] ),

								)
						)
				)
		);

		$result = $schema_data->applyFieldFilters( 'visible' );
		$this->assertEquals( 3, count( $result ) );
		$this->assertArrayHasKey( 'type_id', $result );
		$this->assertArrayHasKey( 'type_10', $result );
		$this->assertArrayHasKey( 'type_10_and_20', $result );

		$result = $schema_data->applyFieldFilters( 'readonly' );
		$this->assertEquals( 2, count( $result ) );
		$this->assertArrayHasKey( 'type_id', $result );
		$this->assertArrayHasKey( 'type_20', $result );


		$schema_data->setRecordData( [ 'type_id' => 20 ] );
		$result = $schema_data->applyFieldFilters( 'visible' );
		$this->assertEquals( 3, count( $result ) );
		$this->assertArrayHasKey( 'type_id', $result );
		$this->assertArrayHasKey( 'type_20', $result );
		$this->assertArrayHasKey( 'type_10_and_20', $result );

		$result = $schema_data->applyFieldFilters( 'readonly' );
		$this->assertEquals( 2, count( $result ) );
		$this->assertArrayHasKey( 'type_id', $result );
		$this->assertArrayHasKey( 'type_10', $result );
	}

	function testTTSHandlersWithPermissions() {
		global $dd;

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$company_id = $dd->createCompany();
		$legal_entity_id = $dd->createLegalEntity( $company_id, 10 );
		Debug::text( 'Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertTrue( TTUUID::isUUID( $company_id ) );

		$dd->createPermissionGroups( $company_id ); //Create all permissions.

		$dd->createCurrency( $company_id, 10 );

		$dd->createUserWageGroups( $company_id );

		$user_id = $dd->createUser( $company_id, $legal_entity_id, 100 );

		$this->assertTrue( TTUUID::isUUID( $company_id ) );
		$this->assertTrue( TTUUID::isUUID( $user_id ) );

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$user_obj = $ulf->getById( $user_id )->getCurrent();

		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$permission = TTnew( 'Permission' ); /** @var Permission $permission */
		$permission_arr = $permission->getPermissions( $user_id, $company_id );
		$this->assertGreaterThan( 40, count( $permission_arr ) ); //Needs to be low enough for community edtion.

		//Check bogus permission
		$retval = $permission->Check( 'foobarinvalid', 'view', $user_id, $company_id );
		$this->assertEquals( false, $retval );

		$retval = $permission->Check( 'user', 'view', $user_id, $company_id );
		$this->assertEquals( true, $retval );



		$schema_data = new TTS(); //Loads TTS class
		$this->assertEquals( true, TTSPermissionCheck::new( 'user', 'view' )->eval( [], $user_id, $company_id ) );
		$this->assertEquals( true, TTSPermissionCheck::new( 'user', 'view' )->eval() );
		$this->assertEquals( false, TTSPermissionCheck::new( 'user', 'bogus_permission_that_fails' )->eval() );


		$schema_data = new TTS();
		$schema_data->setRecordData( [] );
		$schema_data->setTabs(
				TTSTabs::new(
						TTSTab::new( 'tab_employee' )->setLabel( TTi18n::getText( 'Employee' ) )->setMultiColumn( true )->setFields(
								TTSFields::new(
										TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )
												->setHandlers( [ 'visible' =>
																		 TTSLogical::new( 'and', TTSPermissionCheck::new( 'user', 'enabled' ),
																						  TTSLogical::new( 'or', TTSPermissionCheck::new( 'user', 'view' ), TTSPermissionCheck::new( 'user', 'view_own' ), TTSPermissionCheck::new( 'user', 'view_child' ) ) ) ] ),
								)
						)
				)
		);

		$result = $schema_data->applyFieldFilters( 'visible' );
		$this->assertEquals( 1, count( $result ) );
		$this->assertArrayHasKey( 'type_id', $result );


		$schema_data = new TTS();
		$schema_data->setRecordData( [] );
		$schema_data->setTabs(
				TTSTabs::new(
						TTSTab::new( 'tab_employee' )->setLabel( TTi18n::getText( 'Employee' ) )->setMultiColumn( true )->setFields(
								TTSFields::new(
										TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )
												->setHandlers( [ 'visible' =>
																		 TTSLogical::new( 'and', TTSPermissionCheck::new( 'user', 'bogus_permission_that_fails' ),
																						  TTSLogical::new( 'or', TTSPermissionCheck::new( 'user', 'view' ), TTSPermissionCheck::new( 'user', 'view_own' ), TTSPermissionCheck::new( 'user', 'view_child' ) ) ) ] ),
								)
						)
				)
		);

		$result = $schema_data->applyFieldFilters( 'visible' );
		$this->assertEquals( 0, count( $result ) );

	}
}

?>