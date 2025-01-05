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
require_once( 'TTSeleniumGlobal.php' );

use \PHPUnit\Extensions\Selenium2TestCase\Keys as Keys;

/**
 * @group UI
 */
class UIQUnitTest extends TTSeleniumGlobal {
	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function setUpPage() {
		//$this->currentWindow()->maximize();
		$this->currentWindow()->size( [ 'width' => $this->width, 'height' => $this->height ] );
	}

	function testUIQUnit() {
		$user = 'demoadmin2';
		$pass = 'demo.de';

		$this->Login( $user, $pass );
		//WebDriverKeys::PAGE_DOWN;
		$this->keys( Keys::CONTROL . Keys::ALT . Keys::SHIFT . Keys::F12 );

		$this->waitUntilByCssSelector( '#tt_debug_console #qunit_test_button' );
		$this->byId( 'qunit_test_button' )->click();

		$this->waitUntilByCssSelector( '#qunit-banner.qunit-pass', 60000 );

		$this->assertTrue( $this->byCssSelector( '#qunit-banner.qunit-pass' )->displayed() );
	}
}

?>