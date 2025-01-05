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

class MiscTest extends PHPUnit\Framework\TestCase {
	/**
	 * @var bool
	 */
	private $legal_entity_id;

	/**
	 * @var bool
	 */
	private $company_id;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTi18n::setLocale( 'en_US', LC_ALL, true ); //This fixes problems with NumberFormat when the locale is changed and not changed back.

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	/**
	 * @group testEncryptionA
	 */
	function testEncryptionA() {
		//Make sure we force the salt so its consistent even when the timetrex.ini.php is not.
		global $config_vars;
		$config_vars['other']['salt'] = 'f0328b0863222ff98b848537fe1038b2';

		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$decrypted_str = Misc::decrypt( $encrypted_str );
		$this->assertEquals( $str, $decrypted_str );


		//check the case for the colon.
		$x = 'x:z';
		$this->assertEquals( $x, Misc::decrypt( $x ) );

		//Test that changing the salt will not decrypt the string.
		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$config_vars['other']['salt'] = 'zzzzzzzzzzzzzzzzzzzzzzzzzz';
		$decrypted_str = Misc::decrypt( $encrypted_str );
		$this->assertNotEquals( $str, $decrypted_str );
	}

	/**
	 * @group testEncryptionMultiKeyA
	 */
	function testEncryptionMultiKeyA() {
		//Make sure we force the salt so its consistent even when the timetrex.ini.php is not.
		global $config_vars;
		$config_vars['other']['salt'] = [];
		$config_vars['other']['salt'][TTUUID::getNotExistID( 1 )] = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
		$config_vars['other']['salt'][TTUUID::getNotExistID( 2 )] = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
		$config_vars['other']['salt'][TTUUID::getZeroID()] = 'cccccccccccccccccccccccccccccc';

		//Use the default salt under zero UUID.
		$str = 'This is yet another sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$decrypted_str = Misc::decrypt( $encrypted_str );
		$this->assertEquals( $str, $decrypted_str );

		//Company 1
		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str, null, TTPassword::getPasswordSalt( TTUUID::getNotExistID( 1 ) ) );
		$decrypted_str = Misc::decrypt( $encrypted_str, null, TTPassword::getPasswordSalt( TTUUID::getNotExistID( 1 ) ) );
		$this->assertEquals( $str, $decrypted_str );

		//Company 2
		$str = 'This is another sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str, null, TTPassword::getPasswordSalt( TTUUID::getNotExistID( 2 ) ) );
		$decrypted_str = Misc::decrypt( $encrypted_str, null, TTPassword::getPasswordSalt( TTUUID::getNotExistID( 2 ) ) );
		$this->assertEquals( $str, $decrypted_str );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerA
	 */
	function testDatabaseLoadBalancerA() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );


		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 100 ],
				[ $host, 'write', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'write' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 400, $retarr[0] );
		$this->assertLessThan( 600, $retarr[0] );

		$this->assertGreaterThan( 400, $retarr[1] );
		$this->assertLessThan( 600, $retarr[1] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerB
	 */
	function testDatabaseLoadBalancerB() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 100 ],
				[ $host, 'write', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'write' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 200, $retarr[0] );
		$this->assertLessThan( 450, $retarr[0] );

		$this->assertGreaterThan( 450, $retarr[1] );
		$this->assertLessThan( 800, $retarr[1] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerC
	 */
	function testDatabaseLoadBalancerC() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 0 ],
				[ $host, 'write', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'write' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);
		$diff = abs( $retarr[0] - $retarr[1] );

		$this->assertEquals( 1000, $diff );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerD
	 */
	function testDatabaseLoadBalancerD() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 0 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'write' );
			$connection_id = $db->getLoadBalancedConnection( 'write' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);
		$diff = abs( $retarr[0] - $retarr[1] );

		$this->assertEquals( 1000, $diff );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerE
	 */
	function testDatabaseLoadBalancerE() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 10 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'readonly' );
			//$connection_id = $db->getLoadBalancedConnection( 'write' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 1, $retarr[0] );
		$this->assertLessThan( 100, $retarr[0] );

		$this->assertGreaterThan( 250, $retarr[1] );
		$this->assertLessThan( 400, $retarr[1] );

		$this->assertGreaterThan( 550, $retarr[2] );
		$this->assertLessThan( 750, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerF
	 */
	function testDatabaseLoadBalancerF() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 10 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$db->removeConnection( 1 ); //Remove first slave to test failover.

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'readonly' );
			$connection_id = $db->getLoadBalancedConnection( 'readonly' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 1, $retarr[0] );
		$this->assertLessThan( 100, $retarr[0] );

		$this->assertEquals( 0, $retarr[1] );

		$this->assertGreaterThan( 800, $retarr[2] );
		$this->assertLessThan( 1000, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerG
	 */
	function testDatabaseLoadBalancerG() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 100 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			$db->Execute( 'SELECT 1' );
			//$connection_id = $db->getConnectionByWeight( 'readonly' );
			$connection_id = $db->getLoadBalancedConnection( 'readonly' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		if ( $retarr[0] > 0 ) {
			$this->assertEquals( 100, $retarr[0] );
			$this->assertEquals( 0, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} else if ( $retarr[1] > 0 ) {
			$this->assertEquals( 0, $retarr[0] );
			$this->assertEquals( 100, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} else if ( $retarr[2] > 0 ) {
			$this->assertEquals( 0, $retarr[0] );
			$this->assertEquals( 0, $retarr[1] );
			$this->assertEquals( 100, $retarr[2] );
		}
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerH
	 */
	function testDatabaseLoadBalancerH() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 0 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of transactions to make sure they are pinned to the master properly.
			if ( $i == 10 || $i == 80 ) {
				$db->BeginTrans();
			}
			$db->Execute( 'SELECT 1' );

			if ( $i == 20 || $i == 90 ) {
				$db->CommitTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'readonly' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertEquals( 20, $retarr[0] ); //20 transaction in total pinned to master.

		$this->assertGreaterThanOrEqual( 0, $retarr[1] );
		$this->assertLessThanOrEqual( 100, $retarr[1] );

		$this->assertGreaterThanOrEqual( 0, $retarr[2] );
		$this->assertLessThanOrEqual( 100, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerI
	 */
	function testDatabaseLoadBalancerI() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 0 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of *nested* transactions to make sure they are pinned to the master properly.
			if ( $i == 10 || $i == 15 ) {
				$db->StartTrans();
			}
			$db->Execute( 'SELECT 1' );

			if ( $i == 20 || $i == 25 ) {
				$db->CompleteTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'readonly' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertEquals( 15, $retarr[0] ); //15 transaction in total pinned to master.

		$this->assertGreaterThanOrEqual( 0, $retarr[1] );
		$this->assertLessThanOrEqual( 100, $retarr[1] );

		$this->assertGreaterThanOrEqual( 0, $retarr[2] );
		$this->assertLessThanOrEqual( 100, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerSessionVarsA
	 */
	function testDatabaseLoadBalancerSessionVarsA() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 100 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		if ( strncmp( $db->databaseType, 'postgres', 8 ) == 0 ) {
			$db->getConnectionById( 0 );
			$db->getConnectionById( 1 );

			$time_zone = 'America/New_York';

			//SET calls should be intercepted and run on the entire cluster automatically.
			$db->Execute( 'SET SESSION TIME ZONE ' . '\''. $time_zone .'\'' );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, true ); //Only existing connections.
			//var_dump($result);
			$this->assertCount( 2, $results );                                     //Only two connections established so far.
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//
			//Test cluster wide execution.
			//

			$db->ClusterExecute( 'SET SESSION TIME ZONE ' . $db->qstr( $time_zone ), false, true, false );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}
		}
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerSessionVarsB
	 */
	function testDatabaseLoadBalancerSessionVarsB() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'write', 100 ],
				[ $host, 'readonly', 100 ],
				[ $host, 'readonly', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		if ( strncmp( $db->databaseType, 'postgres', 8 ) == 0 ) {
			$time_zone = 'America/New_York';
			$db->setSessionVariable( 'TIME ZONE', '\''. $time_zone .'\'' );

			$db->getConnectionById( 0 );
			$db->getConnectionById( 1 );

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, true ); //Only existing connections.
			//var_dump($result);
			$this->assertCount( 2, $results );                                     //Only two connections established so far.
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			$db->getConnectionById( 2 );

			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//Change timezone, make sure it happens across all connections.

			$time_zone = 'America/Chicago';
			$db->setSessionVariable( 'TIME ZONE', '\''. $time_zone .'\'' );
			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}
		}
	}

	/**
	 * @group MiscTest_testUnitConvert
	 */
	function testUnitConvert() {
		$this->assertEquals( 1, UnitConvert::convert( 'mm', 'mm', 1 ) );
		$this->assertEquals( 1000, UnitConvert::convert( 'm', 'mm', 1 ) );
		$this->assertEquals( 0.001, UnitConvert::convert( 'mm', 'm', 1 ) );

		$this->assertEquals( 1000, UnitConvert::convert( 'km', 'm', 1 ) );
		$this->assertEquals( 0.001, UnitConvert::convert( 'm', 'km', 1 ) );

		$this->assertEquals( 1609344, UnitConvert::convert( 'mi', 'mm', 1 ) );
		$this->assertEquals( UnitConvert::convert( 'mm', 'mi', 1 ), ( 1 / 1609344 ) );

		$this->assertEquals( 0.62137119223733395, UnitConvert::convert( 'km', 'mi', 1 ) );
		$this->assertEquals( 1.6093439999999999, UnitConvert::convert( 'mi', 'km', 1 ) );
		$this->assertEquals( 0.00062137119223733392, UnitConvert::convert( 'm', 'mi', 1 ) );
	}

	/**
	 * @group MiscTest_testPasswordStrength
	 */
	function testPasswordStrength() {
		//Numbers
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567890' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678901' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789012' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567890123' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678901234' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789012345' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '987654321' ) ); //Backwards

		//Letters
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'a' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ab' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcde' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdef' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefg' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefgh' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghi' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghij' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijk' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijkl' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklm' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklmn' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklmno' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ihgfedcba' ) ); //Backwards

		//Half letters, half numbers
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'a1' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ab12' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc123' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd1234' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcde12345' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdef123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefg1234567' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefgh12345678' ) );


		//All the same char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaaaaa' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaabbb' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaaccc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111111' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111222' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111333' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123123' ) );

		//Some what real passwords.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'test' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'pear' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'orange' ) );

		$this->assertEquals( 2, TTPassword::getPasswordStrength( '!Qa12' ) ); //Unique, but not enough characters to make it difficult.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '2000' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( '696969' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( 'trustno1' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abababababababab' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcabcabcabcabc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdabcdabcdabcd' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd.abcd^abcd#abcd' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc123' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( 'test123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'admin123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear1234' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear12345' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'orange123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'car123456789' ) );           //Too many consecutive.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'cars123456789' ) );          //Too many consecutive.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'orange123456789' ) );        //Too many consecutive.
		$this->assertEquals( 6, TTPassword::getPasswordStrength( 'superabundant123456789' ) ); //Too many consecutive.

		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'cars.8.apple' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'cars.8#apple' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password' ) );  //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'Password' ) );  //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password1' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'password11' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1password' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password!' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '!password' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'qwerty' ) );    //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'dragon' ) );    //Dictionary word

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'superabundant' ) );     //Dictionary word
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super.Abundant#41' ) ); //Dictionary word
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pearappleorange' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'pear.apple@orange#strawberry' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'superabundant123' ) );

		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Superabundant.123' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super^91Pear.87' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super^91Bop.87' ) );

		$this->assertEquals( 7, TTPassword::getPasswordStrength( 'a1j8U4y7K2qA.#@5.' ) );
	}

	/**
	 * @group MiscTest_testRandomPasswordStregth
	 */
	function testRandomPasswordStregth() {
		for ( $i = 0; $i < 10000; $i++ ) {
			$random_password = TTPassword::generateRandomPassword( 14 );
			$this->assertGreaterThan( 3, TTPassword::getPasswordStrength( $random_password ), $random_password ); //14 character random password should always be above 3 on the password strength.
		}
	}

	/**
	 * @group testLockFile
	 */
	function testLockFile() {
		global $config_vars;

		$lock_file_name = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'unit_test' . '.lock';
		@unlink( $lock_file_name );

		//Test with default timeout.
		$lock_file = new LockFile( $lock_file_name );
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( true, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( true, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );

		//Test with really short timeout
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			Debug::Text( '  Sleeping...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 2 );

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( true, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( true, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );


		//Test without PID
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->use_pid = false;
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			sleep( 2 );

			$this->assertEquals( false, $lock_file->getCurrentPID() );
			$this->assertEquals( false, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( false, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );
	}

	/**
	 * @group testRemoteHTTP
	 */
	function testRemoteHTTP() {
		global $config_vars;

		$url = 'coreapi.timetrex.com/blank.html';

		$header_size = (int)Misc::getRemoteHTTPFileSize( 'http://' . $url );
		$this->assertEquals( 30, (int)$header_size ); //30 Bytes.

		$header_size = (int)Misc::getRemoteHTTPFileSize( 'https://' . $url );
		$this->assertEquals( 30, (int)$header_size ); //30 Bytes.


		$temp_file_name = tempnam( $config_vars['cache']['dir'], 'unit_test_http_' );
		$size = Misc::downloadHTTPFile( 'http://' . $url, $temp_file_name );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );

		$temp_file_name = tempnam( $config_vars['cache']['dir'], 'unit_test_http_' );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );


		//Test downloading to the same file that should already exist from above.
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );


		//Test downloading to a directory without permissions, or one that doesn't exist.
		$temp_file_name = '/root' . tempnam( $config_vars['cache']['dir'], 'unit_test_http_' );
		Debug::Text( ' Temp File Name: ' . $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );

		$this->assertEquals( false, $retval ); //Download should fail without PHP warnings.
		@unlink( $temp_file_name );
	}

	/**
	 * @group testIsSubDirectory
	 */
	function testIsSubDirectory() {
		$parent_dir = '/';
		$child_dir = '/var';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var';
		$child_dir = '/usr';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var';
		$child_dir = '/usr/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/';
		$child_dir = '/usr/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		//Test with directories that do not exist.
		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688Test';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688Test/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );


		$parent_dir = '/var/www/TimeTrex556688Test';
		$child_dir = '/var/www/TimeTrex556688';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688/storage';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688/storage/';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		//This directory should exist for this test to be accurate.
		$parent_dir = '/etc/cron.d';
		$child_dir = '/etc/cron.daily';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );
	}

	/**
	 * @group testSOAPClient
	 */
	function testSOAPClient() {
		$ttsc = TTnew( 'TimeTrexSoapClient' ); /** @var TimeTrexSoapClient $ttsc */
		$this->assertEquals( true, $ttsc->ping() );
	}

	/**
	 * @group testCensorString
	 */
	function testCensorString() {
		$this->assertEquals( '*', Misc::censorString( '0' ) );
		$this->assertEquals( '**', Misc::censorString( '00' ) );
		$this->assertEquals( '0*0', Misc::censorString( '000' ) );
		$this->assertEquals( '0**0', Misc::censorString( '0000' ) );
		$this->assertEquals( '0***0', Misc::censorString( '00000' ) );
		$this->assertEquals( '00**00', Misc::censorString( '000000' ) );
		$this->assertEquals( '00***00', Misc::censorString( '0000000' ) );
		$this->assertEquals( '00****00', Misc::censorString( '00000000' ) );
		$this->assertEquals( '000***000', Misc::censorString( '000000000' ) );
		$this->assertEquals( '123***789', Misc::censorString( '123456789' ) );
		$this->assertEquals( '*****6789', Misc::censorString( '123456789', '*', 0, 0, 4, 4 ) );
		$this->assertEquals( '123456********567890', Misc::censorString( '12345678901234567890' ) );

		//censorString( $str, $censor_char = '*', $min_first_chunk_size = NULL, $ma*_first_chunk_size = NULL, $min_last_chunk_size = NULL, $ma*_last_chunk_size = NULL )
		$this->assertEquals( '4111********4444', Misc::censorString( '4111222233334444', '*', 4, 4, 4, 4 ) );

		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$this->assertEquals( '*', $uf->getSecureSIN( '0' ) );
		$this->assertEquals( '**', $uf->getSecureSIN( '00' ) );
		$this->assertEquals( '***', $uf->getSecureSIN( '000' ) );
		$this->assertEquals( '****', $uf->getSecureSIN( '0000' ) );
		$this->assertEquals( '*****', $uf->getSecureSIN( '00000' ) );
		$this->assertEquals( '******', $uf->getSecureSIN( '000000' ) );
		$this->assertEquals( '***0000', $uf->getSecureSIN( '0000000' ) );
		$this->assertEquals( '****0000', $uf->getSecureSIN( '00000000' ) );
		$this->assertEquals( '*****0000', $uf->getSecureSIN( '000000000' ) );
		$this->assertEquals( '*****6789', $uf->getSecureSIN( '123456789' ) );
	}

	/**
	 * @group testUUID
	 */
	function testUUIDUniqueness() {
		//Make sure UUIDs are unique at least across 1 million tight iterations.
		$max = 1000000;
		for ( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$unique_uuid_arr = array_unique( $uuid_arr );

		$this->assertSameSize( $uuid_arr, $unique_uuid_arr );
		unset( $uuid_arr, $unique_uuid_arr );
	}

	/**
	 * @group testUUIDTruncate
	 */
	function testUUIDTruncate() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( '000000192136', TTUUID::truncateUUID( TTUUID::getConversionPrefix() . '-000000192136', 12, false ) );
		$this->assertEquals( '000000191922', TTUUID::truncateUUID( TTUUID::getConversionPrefix() . '-000000191922', 12, false ) );
		$this->assertEquals( '9af47bc0af20', TTUUID::truncateUUID( '11e7b349-9af4-7bc0-af20-999999191922', 12, false ) );
		$this->assertEquals( '24dc7bc0af20', TTUUID::truncateUUID( '11e7b349-24dc-7bc0-af20-21ea65522ba3', 12, false ) );
		$this->assertEquals( '9af4e9e0b077', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 12, false ) );
		$this->assertEquals( '9af4-e9e0-b0', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 12, true ) );
		$this->assertEquals( '9af4-e9e0-b077', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 15, true ) ); //Only 14 chars due to trailing dash being removed.
	}

	/**
	 * @group testUUIDParsing
	 */
	function testUUIDParsing() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( '11e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ) );
		$this->assertEquals( '11e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) );
		/** @noinspection PhpParamsInspection */
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( [ '11e7b349-9af4-7bc0-af20-999999191922' ] ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( '' ) );
		$this->assertEquals( null, TTUUID::castUUID( null, true ) );                                    //Allow NULLs
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( null, false ) ); //Don't allow NULLs
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( false ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( true ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( 0 ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( '0' ) );

		$this->assertEquals( true, TTUUID::isUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) );
		/** @noinspection PhpParamsInspection */
		$this->assertEquals( false, TTUUID::isUUID( [ '11e7b349-9af4-7bc0-af20-999999191922' ] ) );
		$this->assertEquals( false, TTUUID::isUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ) ); //This is not trimmed as it has to be able to go straight into PostgreSQL without complaint.


		global $PRIMARY_KEY_IS_UUID;
		$tmp_primary_key_is_uuid = $PRIMARY_KEY_IS_UUID; //Save current UUID key setting.

		$PRIMARY_KEY_IS_UUID = false;
		$this->assertEquals( 0, TTUUID::castUUID( '' ) );
		$this->assertEquals( null, TTUUID::castUUID( null, true ) ); //Allow NULLs
		$this->assertEquals( 0, TTUUID::castUUID( null, false ) );   //Don't allow NULLs
		$this->assertEquals( 0, TTUUID::castUUID( false ) );
		$this->assertEquals( 1, TTUUID::castUUID( true ) );
		$this->assertEquals( 0, TTUUID::castUUID( 0 ) );
		$this->assertEquals( 0, TTUUID::castUUID( '0' ) );
		$this->assertEquals( 'a1e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( 'a1e7b349-9af4-7bc0-af20-999999191922' ) ); //UUID's should pass through unchanged.
		$this->assertEquals( '11e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) ); //UUID's should pass through unchanged.
		$this->assertEquals( 123456789, TTUUID::castUUID( 123456789 ) );
		$this->assertEquals( 123456789, TTUUID::castUUID( '123456789' ) );

		$PRIMARY_KEY_IS_UUID = $tmp_primary_key_is_uuid; //Restore original UUID key setting.
	}

	/**
	 * @group testUUIDSorting
	 */
	function testUUIDSorting() {
		//Make sure UUIDs can be sorted and appear in time order as they were created.
		$max = 10000;
		for ( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$sorted_uuid_arr = $uuid_arr;

		sort( $sorted_uuid_arr );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertCount( 0, $diff_uuid_arr );

		//Reverse the sort and confirm all differences.
		rsort( $sorted_uuid_arr );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertEquals( count( $diff_uuid_arr ), $max );

		//Use a strcmp sort and confirm it still is in the correct order.
		usort( $sorted_uuid_arr, 'strcmp' );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertCount( 0, $diff_uuid_arr );

		//Natural sort will be the wrong order and therefore have many differences.
		usort( $sorted_uuid_arr, 'strnatcasecmp' );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertGreaterThan( 0, count( $diff_uuid_arr ) );

		unset( $uuid_arr, $sorted_uuid_arr, $diff_uuid_arr );
	}

	/**
	 * @group testStringToUUID
	 */
	function testStringToUUID() {
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( false ) );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( null ) );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( '') );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-fffffffffff1', TTUUID::convertStringToUUID( '1') );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-123456789012', TTUUID::convertStringToUUID( '123456789012') );

		$this->assertEquals( '12345678-9012-3456-7890-123456789012', TTUUID::convertStringToUUID( '12345678901234567890123456789012') );
		$this->assertEquals( '12345678-9012-3456-7890-123456789012', TTUUID::convertStringToUUID( '12345678901234567890123456789012ZZZZZZ') );
	}

	/**
	 * @group testInArrayKey
	 */
	function testOptionGetByValue() {
		$options = [
				10 => 'test1',
				20 => 'Test2',
				30 => 'TEST3',
		];

		$this->assertEquals( 10, Option::getByValue( 'test1', $options ) );
		$this->assertEquals( 10, Option::getByValue( 'Test1', $options ) ); //Test case insensitive match
		$this->assertEquals( 10, Option::getByValue( 'TEST1', $options ) ); //Test case insensitive match

		$this->assertEquals( 20, Option::getByValue( 'Test2', $options ) );
		$this->assertEquals( 20, Option::getByValue( 'test2', $options ) ); //Test case insensitive match
		$this->assertEquals( 20, Option::getByValue( 'TEST2', $options ) ); //Test case insensitive match

		$this->assertEquals( 30, Option::getByValue( 'TEST3', $options ) );
	}

	/**
	 * @group testStripDuplicateSlashes
	 */
	function testStripDuplicateSlashes() {
		$this->assertEquals( 'http://www.domain.com/test/test2/test3/api.php', Environment::stripDuplicateSlashes( 'http://www.domain.com//test//test2//test3/api.php' ) );
		$this->assertEquals( 'www.domain.com/test/test2/test3/api.php', Environment::stripDuplicateSlashes( 'www.domain.com//test//test2//test3/api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '/api//json//api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '//api//json//api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '//////api///////json//////api.php' ) );
	}

	/**
	 * @group testAuthenticationParseEndPointAPI
	 */
	function testAuthenticationParseEndPointAPI() {
		global $config_vars;
		define( 'TIMETREX_JSON_API', true ); //Need to have at least API define() set.

		$authentication = new Authentication;

		$config_vars['path']['base_url'] = '/interface';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface/';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface//';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '//interface//';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface//////';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '//////interface//////';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/timetrex/interface';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/timetrex//api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/timetrex//api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/timetrex//api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/timetrex//api/time_clock/api.php' ) );
	}

	/**
	 * @group testAuthenticationParseEndPointLegacySOAP
	 */
	function testAuthenticationParseEndPointLegacySOAP() {
		global $config_vars;
		define( 'TIMETREX_LEGACY_SOAP_API', true ); //Its possible TIMETREX_JSON_API is still defined when this run, if the above function runs first.

		$authentication = new Authentication;

		$config_vars['path']['base_url'] = '/interface';
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/timetrex/interface';
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/timetrex//soap/server.php' ) );
	}

	/**
	 * @group testHumanSizeToBytes
	 */
	function testHumanSizeToBytes() {
		$this->assertEquals( -1, convertHumanSizeToBytes( '-1' ) );
		$this->assertEquals( 1, convertHumanSizeToBytes( '1' ) );
		$this->assertEquals( 1000, convertHumanSizeToBytes( '1000' ) );
		$this->assertEquals( 1000, convertHumanSizeToBytes( '1K' ) );
		$this->assertEquals( 1000000, convertHumanSizeToBytes( '1M' ) );
		$this->assertEquals( 1000000000, convertHumanSizeToBytes( '1G' ) );
	}

	/**
	 * @group testFilesDelete
	 */
	function testFilesDelete() {
		//Check to make sure all files listed in files.delete don't exist, as they should already be deleted or this should be a fresh GIT checkout.
		// This ensures that a files isn't added to files.delete that is still in GIT.
		$file_list = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files.delete';

		if ( file_exists( $file_list ) ) {
			$file_list_data = file_get_contents( $file_list );
			$files = explode( "\n", $file_list_data );
			unset( $file_list_data );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( $file != '' ) {
						$file = Environment::getBasePath() . str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $file ); //Prefix base path to all files.
						$this->assertEquals( false, file_exists( $file ), 'File still exists in GIT: '. $file );
					}
				}
			}
		}
	}

	/**
	 * @group testMimeType
	 */
	function testMimeType() {
		$this->assertEquals( 'text/x-php', Misc::getMimeType( __FILE__ ) );
		$this->assertEquals( 'image/jpeg', Misc::getMimeType( Environment::getBasePath() .'/interface/images/powered_by.jpg' ) );
	}

	/**
	 * @group testSharedMemory
	 */
	function testSharedMemory() {
		$random_key = uniqid( 'unit_test', true );
		$data = 'test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123test123';

		$prev_umask = umask( 0117 ); //Make cached files group read/write so unit tests can be run as someone other than www-data.

		$shared_memory = new SharedMemory();
		$save_result = $shared_memory->set( $random_key, $data );
		$this->assertEquals( true, $save_result );

		$get_result = $shared_memory->get( $random_key );
		$this->assertEquals( $data, $get_result );

		$delete_result = $shared_memory->delete( $random_key );
		$this->assertEquals( true, $delete_result );

		//Key should be deleted and not return anything now.
		$get_result = $shared_memory->get( $random_key );
		$this->assertEquals( false, $get_result );

		umask( $prev_umask );
	}

	/**
	 * @group testComposerPackages
	 */
	function testComposerPackages() {
		//Non-Pear packages
		$this->assertEquals( true, class_exists('Browser') );
		require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
		$this->assertEquals( true, class_exists('Browser') );


		//
		// Composer PEAR packages. These should not need to be required() first, but should still work if they are.
		//
		$this->assertEquals( true, class_exists('PEAR') );
		require_once( 'PEAR.php' );
		$this->assertEquals( true, class_exists('PEAR') );

		$this->assertEquals( true, class_exists('Mail') );
		$this->assertEquals( true, class_exists('Mail_mime') );
		$this->assertEquals( true, class_exists('Mail_mimePart') );
		$this->assertEquals( true, class_exists('MIME_Type') );

		//
		//PEAR packages in classes/pear/  -- Make sure they don't conflict with Composer packages.
		//  *NOTE* These need to be manually required and can't use the autoloader directly.
		$this->assertEquals( false, class_exists('HTTP_Download') );
		require_once( 'HTTP/Download.php' );
		$this->assertEquals( true, class_exists('HTTP_Download') );

		$this->assertEquals( false, class_exists('Config') );
		require_once( 'Config.php' );
		$this->assertEquals( true, class_exists('Config') );

		$this->assertEquals( false, class_exists('Validate') );
		require_once('Validate.php');
		$this->assertEquals( true, class_exists('Validate') );

		$this->assertEquals( false, class_exists('Validate_Finance_CreditCard') );
		require_once('Validate/Finance/CreditCard.php');
		$this->assertEquals( true, class_exists('Validate_Finance_CreditCard') );

		$this->assertEquals( false, class_exists('Payment_Process') );
		require_once( 'Payment/Process.php' );
		$this->assertEquals( true, class_exists('Payment_Process') );
	}

	/**
	 * @group testOptionGetByFuzzyValue
	 */
	function testOptionGetByFuzzyValue() {
		$options = [
				10 => 'Active',
				20 => 'Inactive',
				30 => 'Leave - Maternity',
				40 => 'Leave - Illness',
		];

		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'A', $options ) );
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'Activ', $options ) );
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'Active', $options ) );
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'Active|', $options ) );
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( '"Active"', $options ) );
		$this->assertEquals( false, Option::getByFuzzyValue( '"Act"', $options ) );
		$this->assertEquals( false, Option::getByFuzzyValue( '"Aktive"', $options ) ); //Exact match, so ignore metaphone and fail this match.
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'Aktive', $options ) ); //Metaphone match
		$this->assertEquals( [ 0 => 10 ], Option::getByFuzzyValue( 'Aktiv', $options ) ); //Metaphone ending wildcard match.

		$this->assertEquals( [ 0 => 30, 1 => 40 ], Option::getByFuzzyValue( 'Leave', $options ) );
		$this->assertEquals( [ 0 => 30, 1 => 40 ], Option::getByFuzzyValue( 'Leeve', $options ) ); //Metaphone ending wildcard match.
		$this->assertEquals( [ 0 => 40 ], Option::getByFuzzyValue( 'Leave - Illness', $options ) );
		$this->assertEquals( false, Option::getByFuzzyValue( 'Leave|', $options ) );
		$this->assertEquals( [ 0 => 40 ], Option::getByFuzzyValue( 'Leave*Illness', $options ) );
		$this->assertEquals( [ 0 => 40 ], Option::getByFuzzyValue( 'Leave%Illness', $options ) );
	}

	/**
	 * @group testCountFuturePayPeriodsBiWeekly
	 */
	function testCountFuturePayPeriodsBiWeekly() {
		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		$dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );
		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );

		//Create Bi-Weekly (26 Pay Periods Schedule)
		$pps_obj = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $pps_obj */

		$pps_obj->setName( 'Bi-Weekly (26)' );
		$pps_obj->setType( 20 );
		$pps_obj->setCompany( $this->company_id );
		$pps_obj->setAnchorDate( TTDate::strtotime( '01-Jan-2000' ) );
		$pps_obj->setShiftAssignedDay( 10 );
		$pps_obj->setDayStartTime( 0 );
		$pps_obj->setNewDayTriggerTime( 14400 );
		$pps_obj->setMaximumShiftTime( 57600 );
		$pps_obj->setTimeZone( 'America/Vancouver' );
		$pps_obj->setStartWeekDay( 0 );
		$pps_obj->setStartDayOfWeek( 0 );
		$pps_obj->setTimeSheetVerifyType( 10 );
		$pps_obj->setTimeSheetVerifyBeforeEndDate( 0 );
		$pps_obj->setTimeSheetVerifyBeforeTransactionDate( 0 );
		$pps_obj->setCreateDaysInAdvance( 16 );
		$pps_obj->setAutoCloseAfterDays( 3 );
		$pps_obj->setPrimaryDayOfMonth( 1 );
		$pps_obj->setPrimaryTransactionDayOfMonth( 1 );
		$pps_obj->setSecondaryDayOfMonth( 1 );
		$pps_obj->setSecondaryTransactionDayOfMonth( 1 );
		$pps_obj->setAnnualPayPeriods( false );
		$pps_obj->setTransactionDate( 7 );
		$pps_obj->setTransactionDateBusinessDay( 2 );

		//Get ID so we can use it later.
		$pay_period_schedule_id = $pps_obj->getNextInsertId();
		$pps_obj->setId( $pay_period_schedule_id );

		if ( $pps_obj->isValid() ) {
			$pps_obj->Save( false, true );
		}

		$pp_obj = TTnew( 'PayPeriodFactory' ); /** @var PayPeriodFactory $pp_obj */
		$pp_obj->setCompany( $this->company_id );
		$pp_obj->setPayPeriodSchedule( $pay_period_schedule_id );
		$pp_obj->setStartDate( TTDate::strtotime( '16-Dec-2000' ) );
		$pp_obj->setEndDate( TTDate::strtotime( '31-Dec-2000' ) );
		$pp_obj->setTransactionDate( TTDate::strtotime( '01-Jan-2001' ) );
		$pp_obj->setPrimary( true );
		$pp_obj->setEnableImportOrphanedData( false );
		$pp_obj->setStatus( 10 );
		if ( $pp_obj->isValid() ) {
			$pp_obj->Save( false );
		}

		$this->assertEquals( 26, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( $pp_obj->getTransactionDate() ) ) );
		$this->assertEquals( 52, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 1, 'year' ) ) ) );
		$this->assertEquals( 78, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 2, 'year' ) ) ) );
		$this->assertEquals( 104, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 3, 'year' ) ) ) );
		$this->assertEquals( 130, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 4, 'year' ) ) ) );
		$this->assertEquals( 156, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 5, 'year' ) ) ) );
		$this->assertEquals( 183, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 6, 'year' ) ) ) ); //This year has 27 PP.
		$this->assertEquals( 209, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 7, 'year' ) ) ) );
		$this->assertEquals( 235, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 8, 'year' ) ) ) );
		$this->assertEquals( 261, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 9, 'year' ) ) ) );
		$this->assertEquals( 287, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 10, 'year' ) ) ) );
		$this->assertEquals( 313, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 11, 'year' ) ) ) );
		$this->assertEquals( 339, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 12, 'year' ) ) ) );
		$this->assertEquals( 365, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 13, 'year' ) ) ) );
		$this->assertEquals( 391, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 14, 'year' ) ) ) );
		$this->assertEquals( 417, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 15, 'year' ) ) ) );
		$this->assertEquals( 443, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 16, 'year' ) ) ) );
		$this->assertEquals( 470, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 17, 'year' ) ) ) ); //This year has 27 PP.
		$this->assertEquals( 496, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 18, 'year' ) ) ) );
		$this->assertEquals( 522, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 19, 'year' ) ) ) );
		$this->assertEquals( 548, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 20, 'year' ) ) ) );
		$this->assertEquals( 574, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 21, 'year' ) ) ) );
		$this->assertEquals( 600, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 22, 'year' ) ) ) );
		$this->assertEquals( 626, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 23, 'year' ) ) ) );
		$this->assertEquals( 652, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 24, 'year' ) ) ) );
		$this->assertEquals( 678, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 25, 'year' ) ) ) );
		$this->assertEquals( 704, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 26, 'year' ) ) ) );
		$this->assertEquals( 730, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 27, 'year' ) ) ) );
		$this->assertEquals( 757, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 28, 'year' ) ) ) ); //This year has 27 PP.
		$this->assertEquals( 783, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 29, 'year' ) ) ) );
		$this->assertEquals( 809, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 30, 'year' ) ) ) );
	}

	/**
	 * @group testCountFuturePayPeriodsBiWeeklyTwoYearsInFuture
	 */
	function testCountFuturePayPeriodsBiWeeklyTwoYearsInFuture() {
		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		$dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );
		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );

		//Create Bi-Weekly (26 Pay Periods Schedule)
		$pps_obj = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $pps_obj */

		//Get 01-Jan two years in the future.
		$start_period_epoch = TTDate::getBeginYearEpoch( TTDate::incrementDate( TTDate::getEndYearEpoch( strtotime('31-Dec-2022') ), 380, 'day' ) );

		$pps_obj->setName( 'Bi-Weekly (26)' );
		$pps_obj->setType( 20 );
		$pps_obj->setCompany( $this->company_id );
		$pps_obj->setAnchorDate( $start_period_epoch );
		$pps_obj->setShiftAssignedDay( 10 );
		$pps_obj->setDayStartTime( 0 );
		$pps_obj->setNewDayTriggerTime( 14400 );
		$pps_obj->setMaximumShiftTime( 57600 );
		$pps_obj->setTimeZone( 'America/Vancouver' );
		$pps_obj->setStartWeekDay( 0 );
		$pps_obj->setStartDayOfWeek( 0 );
		$pps_obj->setTimeSheetVerifyType( 10 );
		$pps_obj->setTimeSheetVerifyBeforeEndDate( 0 );
		$pps_obj->setTimeSheetVerifyBeforeTransactionDate( 0 );
		$pps_obj->setCreateDaysInAdvance( 16 );
		$pps_obj->setAutoCloseAfterDays( 3 );
		$pps_obj->setPrimaryDayOfMonth( 1 );
		$pps_obj->setPrimaryTransactionDayOfMonth( 1 );
		$pps_obj->setSecondaryDayOfMonth( 1 );
		$pps_obj->setSecondaryTransactionDayOfMonth( 1 );
		$pps_obj->setAnnualPayPeriods( false );
		$pps_obj->setTransactionDate( 7 );
		$pps_obj->setTransactionDateBusinessDay( 2 );

		//Get ID so we can use it later.
		$pay_period_schedule_id = $pps_obj->getNextInsertId();
		$pps_obj->setId( $pay_period_schedule_id );

		if ( $pps_obj->isValid() ) {
			$pps_obj->Save( false, true );
		}

		$pp_obj = TTnew( 'PayPeriodFactory' ); /** @var PayPeriodFactory $pp_obj */
		$pp_obj->setCompany( $this->company_id );
		$pp_obj->setPayPeriodSchedule( $pay_period_schedule_id );
		$pp_obj->setStartDate( TTDate::incrementDate( $start_period_epoch, -15, 'day' ) );
		$pp_obj->setEndDate( TTDate::incrementDate( $start_period_epoch, -1, 'day' ) );
		$pp_obj->setTransactionDate( $start_period_epoch );
		$pp_obj->setPrimary( true );
		$pp_obj->setEnableImportOrphanedData( false );
		$pp_obj->setStatus( 10 );
		if ( $pp_obj->isValid() ) {
			$pp_obj->Save( false );
		}

		$this->assertEquals( 26, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( $pp_obj->getTransactionDate() ) ) );
		$this->assertEquals( 52, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 1, 'year' ) ) ) );
		$this->assertEquals( 78, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 2, 'year' ) ) ) );
		$this->assertEquals( 104, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 3, 'year' ) ) ) );
		$this->assertEquals( 130, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 4, 'year' ) ) ) );
		$this->assertEquals( 157, $pps_obj->countFuturePayPeriods( $pp_obj, $start_period_epoch, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 5, 'year' ) ) ) ); //This year has 27 PP.
	}

	/**
	 * @group testCountFuturePayPeriodsWeekly
	 */
	function testCountFuturePayPeriodsWeekly() {
		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		$dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );
		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );

		//Create Bi-Weekly (26 Pay Periods Schedule)
		$pps_obj = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $pps_obj */

		$pps_obj->setName( 'Weekly (52)' );
		$pps_obj->setType( 10 );
		$pps_obj->setCompany( $this->company_id );
		$pps_obj->setAnchorDate( TTDate::strtotime( '01-Jan-2000' ) );
		$pps_obj->setShiftAssignedDay( 10 );
		$pps_obj->setDayStartTime( 0 );
		$pps_obj->setNewDayTriggerTime( 14400 );
		$pps_obj->setMaximumShiftTime( 57600 );
		$pps_obj->setTimeZone( 'America/Vancouver' );
		$pps_obj->setStartWeekDay( 0 );
		$pps_obj->setStartDayOfWeek( 0 );
		$pps_obj->setTimeSheetVerifyType( 10 );
		$pps_obj->setTimeSheetVerifyBeforeEndDate( 0 );
		$pps_obj->setTimeSheetVerifyBeforeTransactionDate( 0 );
		$pps_obj->setCreateDaysInAdvance( 16 );
		$pps_obj->setAutoCloseAfterDays( 3 );
		$pps_obj->setPrimaryDayOfMonth( 1 );
		$pps_obj->setPrimaryTransactionDayOfMonth( 1 );
		$pps_obj->setSecondaryDayOfMonth( 1 );
		$pps_obj->setSecondaryTransactionDayOfMonth( 1 );
		$pps_obj->setAnnualPayPeriods( false );
		$pps_obj->setTransactionDate( 7 );
		$pps_obj->setTransactionDateBusinessDay( 2 );

		//Get ID so we can use it later.
		$pay_period_schedule_id = $pps_obj->getNextInsertId();
		$pps_obj->setId( $pay_period_schedule_id );

		if ( $pps_obj->isValid() ) {
			$pps_obj->Save( false, true );
		}

		$pp_obj = TTnew( 'PayPeriodFactory' ); /** @var PayPeriodFactory $pp_obj */
		$pp_obj->setCompany( $this->company_id );
		$pp_obj->setPayPeriodSchedule( $pay_period_schedule_id );
		$pp_obj->setStartDate( TTDate::strtotime( '24-Dec-2000' ) );
		$pp_obj->setEndDate( TTDate::strtotime( '31-Dec-2000' ) );
		$pp_obj->setTransactionDate( TTDate::strtotime( '01-Jan-2001' ) );
		$pp_obj->setPrimary( true );
		$pp_obj->setEnableImportOrphanedData( false );
		$pp_obj->setStatus( 10 );
		if ( $pp_obj->isValid() ) {
			$pp_obj->Save( false );
		}

		$this->assertEquals( 52, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( $pp_obj->getTransactionDate() ) ) );
		$this->assertEquals( 104, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 1, 'year' ) ) ) );
		$this->assertEquals( 156, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 2, 'year' ) ) ) );
		$this->assertEquals( 208, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 3, 'year' ) ) ) );
		$this->assertEquals( 260, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 4, 'year' ) ) ) );
		$this->assertEquals( 312, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 5, 'year' ) ) ) );
		$this->assertEquals( 365, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 6, 'year' ) ) ) ); //This year has 53 PP.
		$this->assertEquals( 417, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 7, 'year' ) ) ) );
		$this->assertEquals( 469, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 8, 'year' ) ) ) );
		$this->assertEquals( 521, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 9, 'year' ) ) ) );
		$this->assertEquals( 573, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 10, 'year' ) ) ) );
		$this->assertEquals( 626, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 11, 'year' ) ) ) ); //This year has 53 PP.
		$this->assertEquals( 678, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 12, 'year' ) ) ) );
		$this->assertEquals( 730, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 13, 'year' ) ) ) );
		$this->assertEquals( 782, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 14, 'year' ) ) ) );
		$this->assertEquals( 834, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 15, 'year' ) ) ) );
		$this->assertEquals( 886, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 16, 'year' ) ) ) );
		$this->assertEquals( 939, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 17, 'year' ) ) ) ); //This year has 53 PP.
		$this->assertEquals( 991, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 18, 'year' ) ) ) );
		$this->assertEquals( 1043, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 19, 'year' ) ) ) );
		$this->assertEquals( 1095, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 20, 'year' ) ) ) );
		$this->assertEquals( 1147, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 21, 'year' ) ) ) );
		$this->assertEquals( 1199, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 22, 'year' ) ) ) );
		$this->assertEquals( 1252, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 23, 'year' ) ) ) ); //This year has 53 PP.
		$this->assertEquals( 1304, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 24, 'year' ) ) ) );
		$this->assertEquals( 1356, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 25, 'year' ) ) ) );
		$this->assertEquals( 1408, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 26, 'year' ) ) ) );
		$this->assertEquals( 1460, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 27, 'year' ) ) ) );
		$this->assertEquals( 1513, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 28, 'year' ) ) ) ); //This year has 53 PP.
		$this->assertEquals( 1565, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 29, 'year' ) ) ) );
		$this->assertEquals( 1617, $pps_obj->countFuturePayPeriods( $pp_obj, null, TTDate::getEndYearEpoch( TTDate::incrementDate( $pp_obj->getTransactionDate(), 30, 'year' ) ) ) );
	}

	/**
	 * @group testSanitizeFileName
	 */
	function testSanitizeFileName() {
		$this->assertEquals( 'my_company', Misc::sanitizeFileName( 'My Company' ) );
		$this->assertEquals( 'my_company-123_abc', Misc::sanitizeFileName( 'My Company-123_ABC' ) );
		$this->assertEquals( 'my_company-123_abc.pdf', Misc::sanitizeFileName( 'My Company-123_ABC.pdf' ) ); //Make sure extension is maintained.

		$this->assertEquals( 'My Company-123_ABC.pdf', Misc::stripDirectoryTraversal( 'My Company-123_ABC.pdf' ) );
		$this->assertEquals( '......My Company-123_ABC.pdf', Misc::stripDirectoryTraversal( '../../../My Company-123_ABC.pdf' ) );
		$this->assertEquals( 'My Company-123_ABC.......pdf', Misc::stripDirectoryTraversal( 'My Company-123_ABC../../../.pdf' ) );
		$this->assertEquals( 'My Company-123_ABC.pdf..', Misc::stripDirectoryTraversal( 'My Company-123_ABC.pdf../' ) );
		$this->assertEquals( 'My Company..123..ABC.pdf', Misc::stripDirectoryTraversal( 'My Company../123../ABC.pdf' ) );

		$this->assertEquals( '......My Company-123_ABC.pdf', Misc::stripDirectoryTraversal( '..\..\..\My Company-123_ABC.pdf' ) );
		$this->assertEquals( 'My Company-123_ABC.......pdf', Misc::stripDirectoryTraversal( 'My Company-123_ABC..\..\..\.pdf' ) );
		$this->assertEquals( 'My Company-123_ABC.pdf..', Misc::stripDirectoryTraversal( 'My Company-123_ABC.pdf..\\' ) );
		$this->assertEquals( 'My Company..123..ABC.pdf', Misc::stripDirectoryTraversal( 'My Company..\123..\ABC.pdf' ) );
	}

	/**
	 * @group testSortPrefix
	 */
	function testSortPrefix() {
		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-0000-test1' => 'Test1'] ) );
		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-00000-test1' => 'Test1'] ) );
		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-000000000-test1' => 'Test1'] ) ); //Handle up to 9 digits.

		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-1234-test1' => 'Test1'] ) );
		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-12345-test1' => 'Test1'] ) );
		$this->assertEquals( [ 'test1' => 'Test1'], Misc::trimSortPrefix( [ '-123456789-test1' => 'Test1'] ) );

		$this->assertEquals( [ '-123A-test1' => 'Test1'], Misc::trimSortPrefix( [ '-123A-test1' => 'Test1'] ) );

		$this->assertEquals( [ '-123A-MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'], Misc::trimSortPrefix( [ '-123A-MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'] ) );
		$this->assertEquals( [ 'MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'], Misc::trimSortPrefix( [ '-1234-MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'] ) );
		$this->assertEquals( [ 'MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'], Misc::trimSortPrefix( [ '-123456789-MyReportColumn:05d3d547-0e2b-9233-65ba-4e229fd72bc0' => 'Test1'] ) );
	}

	function testGroupByA() {
		$data = [
				[ 'name' => 'John', 'sum' => null, 'avg' => null, 'median' => 1, 'min' => null, 'min_not_null' => null, 'max' => null, 'max_not_null' => null, 'first' => null, 'last' => null, 'count' => null, 'concat' => null ],
				[ 'name' => 'John', 'sum' => 1, 'avg' => 1, 'median' => 2, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'John', 'sum' => 2, 'avg' => 2, 'median' => 3, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],

				[ 'name' => 'Jane', 'sum' => null, 'avg' => null, 'median' => 4, 'min' => null, 'min_not_null' => null, 'max' => null, 'max_not_null' => null, 'first' => null, 'last' => null, 'count' => null, 'concat' => null ],
				[ 'name' => 'Jane', 'sum' => 1, 'avg' => 1, 'median' => 5, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'Jane', 'sum' => 2, 'avg' => 2, 'median' => 3, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],
		];

		$group_by = [ 'name' => true, 'sum' => 'sum', 'avg' => 'avg', 'median' => 'median', 'min' => 'min', 'min_not_null' => 'min_not_null', 'max' => 'max', 'max_not_null' => 'max', 'first' => 'first', 'last' => 'last', 'count' => 'count', 'concat' => 'concat' ];

		$result = Group::GroupBy( $data, $group_by );

		$expected = [
				0 =>
						array (
								'name' => 'John',
								'sum' => '3.0000000000',
								'avg' => '1.0000000000',
								'median' => 2,
								'min' => NULL,
								'min_not_null' => 1,
								'max' => 2,
								'max_not_null' => 2,
								'first' => NULL,
								'last' => 2,
								'count' => 3,
								'concat' => 'one -- two',
						),
				1 =>
						array (
								'name' => 'Jane',
								'sum' => '3.0000000000',
								'avg' => '1.0000000000',
								'median' => 4,
								'min' => NULL,
								'min_not_null' => 1,
								'max' => 2,
								'max_not_null' => 2,
								'first' => NULL,
								'last' => 2,
								'count' => 3,
								'concat' => 'one -- two',
						),
		];

		$this->assertEquals( $expected, $result );
	}

	function testGroupByB() {
		$data = [
				[ 'name' => 'John', 'sum' => 3, 'avg' => 3, 'min' => 3, 'min_not_null' => 3, 'max' => 3, 'max_not_null' => 3, 'first' => 3, 'last' => 3, 'count' => 3, 'concat' => 'three' ],
				[ 'name' => 'John', 'sum' => 1, 'avg' => 1, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'John', 'sum' => 2, 'avg' => 2, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],

				[ 'name' => 'Jane', 'sum' => 3, 'avg' => 3, 'min' => 3, 'min_not_null' => 3, 'max' => 3, 'max_not_null' => 3, 'first' => 3, 'last' => 3, 'count' => 3, 'concat' => 'three' ],
				[ 'name' => 'Jane', 'sum' => 1, 'avg' => 1, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'Jane', 'sum' => 2, 'avg' => 2, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],
		];

		$group_by = [ 'name' => true, 'sum' => 'sum', 'avg' => 'avg', 'min' => 'min', 'min_not_null' => 'min_not_null', 'max' => 'max', 'max_not_null' => 'max', 'first' => 'first', 'last' => 'last', 'count' => 'count', 'concat' => 'concat' ];

		$result = Group::GroupBy( $data, $group_by );

		$expected = [
				0 => array (
						'name' => 'John',
						'sum' => '6.0000000000',
						'avg' => '2.0000000000',
						'min' => 1,
						'min_not_null' => 1,
						'max' => 3,
						'max_not_null' => 3,
						'first' => 3,
						'last' => 2,
						'count' => 3,
						'concat' => 'three -- one -- two',
				),
				1 =>
						array (
								'name' => 'Jane',
								'sum' => '6.0000000000',
								'avg' => '2.0000000000',
								'min' => 1,
								'min_not_null' => 1,
								'max' => 3,
								'max_not_null' => 3,
								'first' => 3,
								'last' => 2,
								'count' => 3,
								'concat' => 'three -- one -- two',
						),

		];

		$this->assertEquals( $expected, $result );
	}

	function testGroupByC() {
		$data = [
				[ 'name' => 'John', 'sum' => 3, 'avg' => 3, 'min' => 3, 'min_not_null' => 3, 'max' => 3, 'max_not_null' => 3, 'first' => 3, 'last' => 3, 'count' => 3, 'concat' => 'three' ],
				[ 'name' => 'John', 'sum' => 1, 'avg' => 1, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'John', 'sum' => 2, 'avg' => 2, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],
				[ 'name' => 'John', 'sum' => null, 'avg' => null, 'min' => null, 'min_not_null' => null, 'max' => null, 'max_not_null' => null, 'first' => null, 'last' => null, 'count' => null, 'concat' => null ],

				[ 'name' => 'Jane', 'sum' => 3, 'avg' => 3, 'min' => 3, 'min_not_null' => 3, 'max' => 3, 'max_not_null' => 3, 'first' => 3, 'last' => 3, 'count' => 3, 'concat' => 'three' ],
				[ 'name' => 'Jane', 'sum' => 1, 'avg' => 1, 'min' => 1, 'min_not_null' => 1, 'max' => 1, 'max_not_null' => 1, 'first' => 1, 'last' => 1, 'count' => 1, 'concat' => 'one' ],
				[ 'name' => 'Jane', 'sum' => 2, 'avg' => 2, 'min' => 2, 'min_not_null' => 2, 'max' => 2, 'max_not_null' => 2, 'first' => 2, 'last' => 2, 'count' => 2, 'concat' => 'two' ],
				[ 'name' => 'Jane', 'sum' => null, 'avg' => null, 'min' => null, 'min_not_null' => null, 'max' => null, 'max_not_null' => null, 'first' => null, 'last' => null, 'count' => null, 'concat' => null ],
		];

		$group_by = [ 'name' => true, 'sum' => 'sum', 'avg' => 'avg', 'min' => 'min', 'min_not_null' => 'min_not_null', 'max' => 'max', 'max_not_null' => 'max', 'first' => 'first', 'last' => 'last', 'count' => 'count', 'concat' => 'concat' ];

		$result = Group::GroupBy( $data, $group_by );

		$expected = [
				0 => array (
						'name' => 'John',
						'sum' => '6.0000000000',
						'avg' => '1.5000000000',
						'min' => null,
						'min_not_null' => 1,
						'max' => 3,
						'max_not_null' => 3,
						'first' => 3,
						'last' => null,
						'count' => 4,
						'concat' => 'three -- one -- two',
				),
				1 =>
						array (
								'name' => 'Jane',
								'sum' => '6.0000000000',
								'avg' => '1.5000000000',
								'min' => null,
								'min_not_null' => 1,
								'max' => 3,
								'max_not_null' => 3,
								'first' => 3,
								'last' => null,
								'count' => 4,
								'concat' => 'three -- one -- two',
						),

		];

		$this->assertEquals( $expected, $result );
	}

	function testValidFromDomains() {
		global $config_vars;

		$config_vars['other']['allowed_from_email_domains'] = '';
		$this->assertEquals( true, TTMail::isValidFromEmailDomain( '"John Doe" <test@'. Misc::getEmailDomain() .'>' ) ); //Use default email domain without one specified in .ini file.
		$this->assertEquals( true, TTMail::isValidFromEmailDomain( 'test@'. Misc::getEmailDomain() ) ); //Use default email domain without one specified in .ini file.
		$this->assertEquals( false, TTMail::isValidFromEmailDomain( '"John Doe" <test@bogusdomain.com>' ) );

		$config_vars['other']['allowed_from_email_domains'] = 'timetrex.com';
		$this->assertEquals( true, TTMail::isValidFromEmailDomain( 'test@timetrex.com' ) );
		$this->assertEquals( false, TTMail::isValidFromEmailDomain( '"John Doe" <test@bogusdomain.com>' ) );
		$this->assertEquals( false, TTMail::isValidFromEmailDomain( 'test@bogusdomain.com' ) );

		$config_vars['other']['allowed_from_email_domains'] = 'timetrex.com, timetrex.cloud';
		$this->assertEquals( true, TTMail::isValidFromEmailDomain( 'test@timetrex.com' ) );
		$this->assertEquals( true, TTMail::isValidFromEmailDomain( 'test@timetrex.cloud' ) );
		$this->assertEquals( false, TTMail::isValidFromEmailDomain( '"John Doe" <test@bogusdomain.com>' ) );
		$this->assertEquals( false, TTMail::isValidFromEmailDomain( 'test@bogusdomain.com' ) );
	}

	function testGetHostName() {
		global $config_vars;

		$config_vars['other']['hostname'] = 'desktop:8085';
		$this->assertEquals( 'desktop:8085', Misc::getHostName() );
		$this->assertEquals( 'desktop', Misc::getHostName( false ) );

		$config_vars['other']['hostname'] = 'timetrex.mycompany.com:8085';
		$this->assertEquals( 'timetrex.mycompany.com:8085', Misc::getHostName() );
		$this->assertEquals( 'timetrex.mycompany.com', Misc::getHostName( false ) );
	}

	function testMobileAppUserAgentParsing() {
		$this->assertEquals( 		[
											'app_version' => '5.5.0',
											'station_type' => '65',
											'os_type' => 'ipados',
											'os_version' => '16.7.2',
											'os_arch' => '',
											'device_model' => 'ipad',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.5.0; StationType: 65; OS: iPadOS; OSVersion: 16.7.2; OSArch: ; DeviceModel: iPad' )  );

		$this->assertEquals( 		[
											'app_version' => '5.3.2',
											'station_type' => '65',
											'os_type' => 'ios',
											'os_version' => '14.4.2',
											'os_arch' => '',
											'device_model' => 'ipad',
									], Misc::parseMobileAppUserAgent( ' TimeTrex Mobile App: v5.3.2; StationType: 65; OS: iOS; OSVersion: 14.4.2; OSArch: ; DeviceModel: iPad' )  );

		$this->assertEquals( 		[
											'app_version' => '5.4.5',
											'station_type' => '28',
											'os_type' => 'ios',
											'os_version' => '17.3.1',
											'os_arch' => '',
											'device_model' => 'iphone',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.4.5; StationType: 28; OS: iOS; OSVersion: 17.3.1; OSArch: ; DeviceModel: iPhone' )  );

		$this->assertEquals( 		[
											'app_version' => '5.2.8',
											'station_type' => '65',
											'os_type' => 'android',
											'os_version' => '13',
											'os_arch' => 'aarch64',
											'device_model' => 'sm-t220',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.2.8; StationType: 65; OS: Android; OSVersion: 13; OSArch: aarch64; DeviceModel: SM-T220' )  );

		$this->assertEquals( 		[
											'app_version' => '5.4.5',
											'station_type' => '65',
											'os_type' => 'android',
											'os_version' => '6.0.1',
											'os_arch' => 'arm',
											'device_model' => 'sm-t350',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.4.5; StationType: 65; OS: Android; OSVersion: 6.0.1; OSArch: ARM; DeviceModel: SM-T350' )  );

		$this->assertEquals( 		[
											'app_version' => '5.4.5',
											'station_type' => '65',
											'os_type' => 'android',
											'os_version' => '5.1',
											'os_arch' => 'unknown',
											'device_model' => 'lenovo tab 2 a8-50f',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.4.5; StationType: 65; OS: Android; OSVersion: 5.1; OSArch: UNKNOWN; DeviceModel: Lenovo TAB 2 A8-50F' )  );

		$this->assertEquals( 		[
											'app_version' => '4.6.15',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v4.6.15' )  );

		//Bogus versions to test flexibility.
		$this->assertEquals( 		[
											'app_version' => '7',
											'station_type' => '65',
											'os_type' => 'ipados',
											'os_version' => '16.7.2',
											'os_arch' => '',
											'device_model' => 'ipad',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v7; StationType: 65; OS: iPadOS; OSVersion: 16.7.2; OSArch: ; DeviceModel: iPad' )  );

		$this->assertEquals( 		[
											'app_version' => '5.5',
											'station_type' => '65',
											'os_type' => 'ipados',
											'os_version' => '16.7.2',
											'os_arch' => '',
											'device_model' => 'ipad',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v5.5; StationType: 65; OS: iPadOS; OSVersion: 16.7.2; OSArch: ; DeviceModel: iPad' )  );

		$this->assertEquals( 		[
											'app_version' => '123.456.789',
											'station_type' => '65',
											'os_type' => 'ipados',
											'os_version' => '16.7.2',
											'os_arch' => '',
											'device_model' => 'ipad',
									], Misc::parseMobileAppUserAgent( 'TimeTrex Mobile App: v123.456.789; StationType: 65; OS: iPadOS; OSVersion: 16.7.2; OSArch: ; DeviceModel: iPad' )  );

	}

	function testAdvisoryLockSession() {
		global $config_vars;

		//This won't work when using a load balancer due to the host name having multiple servers on it.
		if ( stripos( $config_vars['database']['host'], ',' ) !== false ) {
			return true;
		}

		$lock_key = 'testAdvisoryLockSession';

		$ulf = TTNew( 'UserListFactory' );

		try {
			$lock_result = $ulf->acquireAdvisoryLock( $lock_key, false, 3, 1 );
			$this->assertEquals( true, $lock_result[0] );
			$this->assertGreaterThan( 0, $lock_result[1] );
		} catch ( Exception $e ) {
			$this->assertTrue( false, 'Lock could not be acquired!' );
		}

		$db2 = pg_connect( sprintf( "host=%s dbname=%s user=%s password=%s", $config_vars['database']['host'], $config_vars['database']['database_name'], $config_vars['database']['user'], $config_vars['database']['password'] ), PGSQL_CONNECT_FORCE_NEW );

		$db2_result = pg_query( $db2, 'SET lock_timeout = \'1s\';SELECT pg_try_advisory_lock(' . $ulf->convertStringTo64BitInteger( $lock_key ) . ')' );
		$this->assertEquals( 'f', pg_fetch_assoc( $db2_result )['pg_try_advisory_lock'], '2nd connection lock should fail.' );

		$ulf->releaseAdvisoryLock( $lock_key );

		//
		// Reverse the test, so the 2nd connection acquires the lock first.
		//

		$db2_result = pg_query( $db2, 'SET lock_timeout = \'1s\';SELECT pg_try_advisory_lock(' . $ulf->convertStringTo64BitInteger( $lock_key ) . ')' );
		$this->assertEquals( 't', pg_fetch_assoc( $db2_result )['pg_try_advisory_lock'], '2nd connection lock acquired.' );

		try {
			$lock_result = $ulf->acquireAdvisoryLock( $lock_key, false, 3, 1 );
			$this->fail( 'Lock should not be acquired!' );
		} catch ( PHPUnit\Framework\AssertionFailedError $e ) {
			$this->fail( 'Lock should not be acquired!' );
		} catch ( Exception $e ) {
			$this->assertTrue( true, 'Lock could not be acquired!' );
		}


		//
		// Test blocking while waiting for lock timeout.
		//
		try {
			$lock_result = $ulf->acquireAdvisoryLock( $lock_key, false, -1, 1 );
			$this->fail( 'Lock should not be acquired!' );
		} catch ( PHPUnit\Framework\AssertionFailedError $e ) {
			$this->fail( 'Lock should not be acquired!' );
		} catch ( Exception $e ) {
			$this->assertTrue( true, 'Lock could not be acquired!' );
		}


		//
		// Test blocking then the lock is released while waiting.
		//
		$db2_result = pg_query( $db2, 'SELECT pg_sleep( 1 ); SET lock_timeout = \'1s\';SELECT pg_advisory_unlock(' . $ulf->convertStringTo64BitInteger( $lock_key ) . ')' );
		$this->assertEquals( 't', pg_fetch_assoc( $db2_result )['pg_advisory_unlock'], '2nd connection lock released.' );
		try {
			$lock_result = $ulf->acquireAdvisoryLock( $lock_key, false, -1, 3 ); //Timeout 3seconds, should be completed within 1 second due to above pg_sleep()
			$this->assertEquals( true, $lock_result[0] );
			$this->assertGreaterThan( 0, $lock_result[1] );
		} catch ( Exception $e ) {
			$this->assertTrue( false, 'Lock could not be acquired!' );
		}

	}

	function testAdvisoryLockTransaction() {
		global $config_vars;

		//This won't work when using a load balancer due to the host name having multiple servers on it.
		if ( stripos( $config_vars['database']['host'], ',' ) !== false ) {
			return true;
		}

		$lock_key = 'testAdvisoryLockTransaction';

		$ulf = TTNew( 'UserListFactory' );
		$ulf->StartTransaction();

		try {
			$lock_result = $ulf->acquireAdvisoryLock( $lock_key, true, 3, 1 );
			$this->assertEquals( true, $lock_result[0] );
			$this->assertGreaterThan( 0, $lock_result[1] );
		} catch ( Exception $e ) {
			$this->assertTrue( false, 'Lock could not be acquired!' );
		}

		$db2 = pg_connect( sprintf( "host=%s dbname=%s user=%s password=%s", $config_vars['database']['host'], $config_vars['database']['database_name'], $config_vars['database']['user'], $config_vars['database']['password'] ), PGSQL_CONNECT_FORCE_NEW );

		$db2_result = pg_query( $db2, 'SET lock_timeout = \'1s\';SELECT pg_try_advisory_lock(' . $ulf->convertStringTo64BitInteger( $lock_key ) . ')' );
		$this->assertEquals( 'f', pg_fetch_assoc( $db2_result )['pg_try_advisory_lock'], '2nd connection lock should fail.' );

		$ulf->CommitTransaction();

		$db2_result = pg_query( $db2, 'SET lock_timeout = \'1s\';SELECT pg_try_advisory_lock(' . $ulf->convertStringTo64BitInteger( $lock_key ) . ')' );
		$this->assertEquals( 't', pg_fetch_assoc( $db2_result )['pg_try_advisory_lock'], '2nd connection lock should be acquired.' );
	}
}

?>


