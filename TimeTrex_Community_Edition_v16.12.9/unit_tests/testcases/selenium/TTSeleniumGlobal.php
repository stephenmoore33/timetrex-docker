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

/**
 * Class TTSeleniumGlobal
 *
 * weird xpath examples:
 * // //*[starts-with(@id, 'ceil_')]
 */
class TTSeleniumGlobal extends PHPUnit\Framework\TestCase {

	private $default_wait_timeout = 4000;//100000;

	public $width = 1440;
	public $height = 900;

	public function setUp(): void {
		global $selenium_config;
		$this->selenium_config = $selenium_config;

		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//$this->setHost( $selenium_config['host'] );
		//$this->setPort( (int)$selenium_config['port'] );
		//$this->setBrowser( $selenium_config['browser'] );
		//$this->setBrowserUrl( $selenium_config['default_url'] );
		//
		//$this->setDesiredCapabilities( [ 'chromeOptions' => [ 'args' => [ '--incognito' ], ] ] ); //Use incognito mode to help prevent caching between sessions and saving passwords and such.
		//
		//
		//$this->setDesiredCapabilities( [
		//									   'goog:chromeOptions' => [
		//											   'w3c' => false,
		//									   ],
		//							   ] );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function goToLogin( $user ) {
		Debug::text( 'Login to: ' . $this->selenium_config['default_url'] . ' Username: ' . $user, __FILE__, __LINE__, __METHOD__, 10 );
		$this->url( $this->selenium_config['default_url'] );

		sleep( 2.5 ); //have to be sure that Global.js is loaded before we start trying to use it.
		$this->setUnitTestMode( $user );

		$this->waitUntilByCssSelector( '#user_name' );
	}

	function Login( $user, $pass ) {
		//disable the overlay to speed up testing

		$this->goToLogin( $user );

		$this->waitThenClick( '#user_name' );
		$this->keys( $user );
		//$this->keys('demoadmin2');

		$this->waitThenClick( '#password' );
		$this->keys( $pass );

		$this->waitThenClick( '#login_btn' );

		sleep( 1 ); //wait for login
		$this->waitForUIInitComplete();
		$this->waitUntilById( 'topbar-company-logo' ); //the css not() selector is there to differentiate the various calls in the server log.

		//needed as development mode reloads and clears the variables.
		$javascript = [ 'script' => 'Global.setUnitTestMode();', 'args' => [] ];
		$this->execute( $javascript );

		Debug::text( 'Login Complete...', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function Logout() {
		//because we could want to log out from any point
		$this->goToDashboard();
		$this->waitForUIInitComplete();

		$this->waitUntilById( 'profile-button' );
		$this->byId( 'profile-button' )->click();

		$this->waitUntilById( 'profile-menu-Logout' );
		$this->byId( 'profile-menu-Logout' )->click();

		$this->waitUntilById( 'user_name' );
		Debug::text( 'Logout...', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function waitUntilById( $id, $timeout = null ) {
		if ( $timeout == null ) {
			$timeout = $this->default_wait_timeout;
		}

		$this->waitUntil( function () use ( $id ) {
			$javascript = [ 'script' => "$('#overlay.overlay:visible').length", 'args' => [] ];
			if ( $this->execute( $javascript ) == 0 && $this->byId( $id ) ) {

				return true;
			}

			return null;
		}, $timeout );
	}

	function changeLanguageLogin( $language_value ) {
		$this->waitUntilByCssSelector( '#language-selector' );

		$language_selector = $this->byId( 'language-selector' );
		$language_selector->click();

		$language_options = $language_selector->elements(
				$this->using( 'css selector' )->value( 'option' )
		);

		foreach ( $language_options as $language_option ) {
			if ( $language_option->value() == $language_value ) {
				$language_option->click();
				break;
			}
		}
	}

	function changeLanguagePreference( $language_value ) {
		$this->goToDashboard();

		$this->waitUntilById( 'profile-button' );
		$this->byId( 'profile-button' )->click();

		$this->waitUntilById( 'profile-menu-LoginUserPreference' );
		$this->byId( 'profile-menu-LoginUserPreference' )->click();

		$this->waitForUIInitComplete();

		$language_selector = $this->byCssSelector( '#tab_preferences_content_div select:first-of-type' );
		$language_selector->click();

		$language_options = $language_selector->elements(
				$this->using( 'css selector' )->value( 'option' )
		);

		foreach ( $language_options as $language_option ) {
			if ( $language_option->value() == $language_value ) {
				$language_option->click();
				break;
			}
		}

		$this->byId( 'context-button-save' )->click();

		$this->waitForUIInitComplete();
	}

	function waitUntilByCssSelector( $selector, $timeout = null ) {
		if ( $timeout == null ) {
			$timeout = $this->default_wait_timeout;
		}

		$this->waitUntil( function () use ( $selector ) {
			$javascript = [ 'script' => "$('#overlay.overlay:visible').length", 'args' => [] ];
			if ( $this->execute( $javascript ) == 0 && $this->byCssSelector( $selector ) ) {
				return true;
			}

			return null;
		}, $timeout );
	}

	function takeScreenshot( $screenshot_file_name, $create_dir = true ) {
		if ( $create_dir === true ) {
			$dirname = dirname( $screenshot_file_name );
			if ( file_exists( $dirname ) == false ) {
				mkdir( $dirname, 0777, true );
			}
		}

		$this->waitForUIInitComplete();
		// get the mousepointer and focus away from hover effects and flashing cursors
		// these cause significant differences in the screenshots.
		$this->waitUntilByCssSelector( '#powered_by,#copy_right_logo' );
		$this->moveto( $this->byCssSelector( '#powered_by,#copy_right_logo' ) );
		$this->waitUntilByCssSelector( '#powered_by,#copy_right_logo' );

		$retval = file_put_contents( $screenshot_file_name, $this->currentScreenshot() );
		chmod( $screenshot_file_name, 0777 );

		return $retval;
	}

	function getOSUser() {
		if ( function_exists( 'posix_geteuid' ) && function_exists( 'posix_getpwuid' ) ) {
			$user = posix_getpwuid( posix_geteuid() );
			Debug::Text( 'Webserver running as User: ' . $user['name'], __FILE__, __LINE__, __METHOD__, 10 );

			return $user['name'];
		}

		return false;
	}

	function goToDashboard() {
		$this->waitUntilById( 'topbar-company-logo' );
		$this->byId( 'topbar-company-logo' )->byXPath("./..")->click(); //click on the logo parent (href) to go to the dashboard
		//dashboard will reliably use init_complete after everything is loaded.
		$this->waitForUIInitComplete();
	}

	function waitForUIInitComplete() {
		$this->waitForUIInitCompleteLoops = 0;
		$this->waitUntil( function ( $_self_ ) {
			//Global.getUIReadyStatus will be == 2 when the screens are finished loading.

			$javascript = [ 'script' => 'if ( ( typeof Global != "undefined" && Global.getUIReadyStatus() == 2 ) && ( typeof TTPromise != "undefined" && TTPromise.isPendingPromises() == false ) ) { return true; } else { return false; }', 'args' => [] ];
			$js_retval = $this->execute( $javascript );
			Debug::Text( 'waitForUI result: ' . var_export( $js_retval, true ), __FILE__, __LINE__, __METHOD__, 10 );

			if ( isset( $js_retval ) && $js_retval == true ) {
				return true;
			} else {
				$ui_ready_status = $this->execute( [ 'script' => 'if ( typeof Global != "undefined" ) { return Global.getUIReadyStatus(); } else { return null; }', 'args' => [] ] );
				$pending_promises = $this->execute( [ 'script' => 'if ( typeof TTPromise != "undefined" ) { return TTPromise.isPendingPromises(); } else { return null; }', 'args' => [] ] );
				Debug::Text( '  waitForUI UIReadyStatus: ' . var_export( $ui_ready_status, true ) . ' Pending Promises: ' . var_export( $pending_promises, true ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $_self_->waitForUIInitCompleteLoops > 10 ) {
					//trigger checking promises again to workaround selenium bug where they resolve without firing function
					$this->execute( [ 'script' => 'TTPromise.wait()', 'args' => [] ] );
					Debug::Text( '  waitForUI Triggering TTPromise.wait()... Loops: ' . $this->waitForUIInitCompleteLoops, __FILE__, __LINE__, __METHOD__, 10 );
				}
				$_self_->waitForUIInitCompleteLoops++;

				return null;
			}
		}, 60000 ); //Wait for up to 60 seconds.
	}

	function setUnitTestMode( $username ) {
		$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */

		$slf->getByStationId( 'UNITTEST' );
		if ( $slf->getRecordCount() == 0 ) {
			$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByUserName( $username );
			if ( $ulf->getRecordCount() > 0 ) {
				$sf->setCompany( $ulf->getCurrent()->getCompany() );
				$sf->setStatus( 20 );
				$sf->setType( 10 );
				$sf->setDescription( 'Unit Testing Rig' );
				$sf->setStation( 'UNITTEST' );
				$sf->setSource( 'ANY' );
				$sf->setBranchSelectionType( 10 ); //enabled all
				$sf->setDepartmentSelectionType( 10 ); //enabled all
				$sf->setGroupSelectionType( 10 ); //enabled all
				if ( $sf->isValid() ) {
					$sf->Save();
				}
			} else {
				Debug::Text( 'username not found in db', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'station exists', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//run necessary js for unit tests
		$javascript = [ 'script' => 'Global.setUnitTestMode();', 'args' => [] ];
		$this->execute( $javascript );

		//$path = Environment::getCookieBaseURL();
		//set the same sessionid for all tests
		//$javascript = [ 'script' => "$.cookie( 'StationID', 'UNITTESTS', {expires: 30, path: '$path'} );", 'args' => [] ];
		$javascript = [ 'script' => "Global.setStationID('UNITTEST')", 'args' => [] ];
		$this->execute( $javascript );
	}

	function isThere( $css_selector ) {
		$result = $this->elements( $this->using( 'css selector' )->value( $css_selector ) );
		if ( count( $result ) > 0 ) {
			foreach ( $result as $el ) {
				if ( $el->displayed() && $el->enabled() ) {
					return true;
					break;
				}
			}
		}

		return false;
	}

	function waitThenClick( $selector ) {
		Debug::Text( 'Attempting to click Selector: ' . $selector, __FILE__, __LINE__, __METHOD__, 10 );

		$javascript = [ 'script' => "return $('#overlay.overlay').length", 'args' => [] ];
		$overlay_shown = $this->execute( $javascript );
		if ( $overlay_shown > 0 ) {
			Debug::Text( '  Overlay status check: ' . $overlay_shown, __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 1 );
			$this->waitThenClick( $selector );

			return;
		}

		try {
			if ( ( substr( $selector, 0, 1 ) == '#' && strstr( $selector, ' ' ) == false ) || strstr( $selector, 'menu:' ) == true ) {
				//need to do this because of malformed ids in the top menu causing wating by selector to fail.
				$id = substr( $selector, 1, strlen( $selector ) );
				Debug::Text( '  Waiting on ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitUntilById( $id, 10000 );
				Debug::Text( '  Clicking ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
				$this->byId( $id )->click();
			} else {
				Debug::Text( '  Waiting on selector: ' . $selector, __FILE__, __LINE__, __METHOD__, 10 );
				$this->waitUntilByCssSelector( $selector, 10000 );
				Debug::Text( '  Clicking selector: ' . $selector, __FILE__, __LINE__, __METHOD__, 10 );
				$el = $this->byCssSelector( $selector );
				//Debug::Text( '     Element about to be clicked: Enabled: '. $el->enabled() .' Displayed: '. $el->displayed(), __FILE__, __LINE__, __METHOD__, 10 );
				$el->click();
			}
		} catch ( Exception $e ) {
			$this->takeScreenshot( $this->screenshot_path . DIRECTORY_SEPARATOR . 'waitThenClickException.png', true );
			Debug::Text( 'Click failed on: ' . $selector . ' Screenshot path: ' . $this->screenshot_path, __FILE__, __LINE__, __METHOD__, 10 );
			//$javascript = array('script' => "$('" . $selector . "').click()", 'args' => array());
			//$this->execute( $javascript );
			throw new Exception( $selector . ' - ' . $e->getMessage() );
		}

		Debug::Text( 'Done: ' . $selector, __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getArrayBySelector( $css_selector ) {
		Debug::Text( 'Getting array by selector: ' . $css_selector, __FILE__, __LINE__, __METHOD__, 10 );
		//$this->waitUntilByCssSelector( $css_selector,10000 );

		//http://stackoverflow.com/questions/16637806/select-all-matching-elements-in-phpunit-selenium-2-test-case
		$retval = $this->elements(
				$this->using( 'css selector' )->value( $css_selector )
		);

		if ( isset( $retval ) ) {
			Debug::Text( count( $retval ) . ' RESULTS FOR: ' . $css_selector, __FILE__, __LINE__, __METHOD__, 10 );
//			foreach ( $retval as $el ) {
//				Debug::Text( '  Element: ' . $el->attribute( 'ref' ) .' ID: '. $el->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
//			}

			return $retval;
		}

		return [];
	}

	function clickCancel( $menu_id = false ) {
		//TODO look at this
		if ( $menu_id !== false ) {
			$selector = '#' . $menu_id . ' .tticon-cancel_black_24dp';
			$this->waitThenClick( $selector );
			Debug::Text( 'Clicking Cancel [' . $selector . ']', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			//TODO: Fix this branchs
			$javascript = [ 'script' => "$('#topContainer .ribbon .ribbon-tab-out-side:visible #cancelIcon').click()", 'args' => [] ];
			$this->execute( $javascript );
			Debug::Arr( $javascript, 'Executing  cancelclick with js', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	function clickMainMenuItem( $menu_id ) {
		//Only works for main menu horizontal default mode currently.
		$this->waitUntilByCssSelector( '#main-menu' );
		$this->byCssSelector( '#main-menu' )->byId( $menu_id )->click();
		Debug::Text( 'Opening Main Menu Item [' . $menu_id . ']', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function clickProfileMenuItem( $profile_menu_id ) {
		$this->waitForUIInitComplete();

		$this->waitUntilById( 'profile-button' );
		$this->byId( 'profile-button' )->click();

		$this->waitUntilById( $profile_menu_id );
		$this->byId( $profile_menu_id )->click();

		$this->waitForUIInitComplete();
	}
}