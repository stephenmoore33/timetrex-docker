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

/**
 * @group UI
 */
class UIScreenShotTest extends TTSeleniumGlobal {

	public $user_name = '';
	public $screenshot_path = '';

	public function setUpPage() {
		//$this->currentWindow()->maximize();
		$this->currentWindow()->size( [ 'width' => $this->width, 'height' => $this->height ] );
	}

	function testUIScreenShot() {
		//$this->screenshot_path = DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . $this->getOSUser() . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'UIScreenShotTest' . DIRECTORY_SEPARATOR . APPLICATION_VERSION . '-' . date( 'Ymd-His' );
		$this->screenshot_path = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'UIScreenShotTest' . DIRECTORY_SEPARATOR . $this->getOSUser() . '-' . APPLICATION_VERSION . '-' . date( 'Ymd-His' );

		$user_login_info = [
				'demoadmin2' => 'demo.de',
				'john.doe2'  => 'demo.jo',
				'jane.doe2'  => 'demo.ja',
		];

		$resolution_array = [
				[ 'w' => 1920, 'h' => 1080 ],
				//array( 'w' => 1440, 'h' => 900), //Not serving much purpose at this time.
				[ 'w' => 1280, 'h' => 800 ],
				//array( 'w' => 1366, 'h' => 768 ),

				//array( 'w' => 320, 'h' => 568 ), //iphone crashes tests, can find anything (maybe because it's off screen?)
				//array( 'w' => 1027, 'h' => 728 ), //too small
				//array( 'w' => 1280 , 'h' => 720 ), //smallest?
		];

		//single entry point to make error trapping easier
		try {
			foreach ( $resolution_array as $resolution ) {
				$this->width = $resolution['w'];
				$this->height = $resolution['h'];
				$win = $this->currentWindow();
				$win->size( [ 'width' => $this->width, 'height' => $this->height ] );

				foreach ( $user_login_info as $user => $pass ) {
					Debug::Text( 'logging in as ' . $user, __FILE__, __LINE__, __METHOD__, 10 );
					$this->startTesting( $user, $pass );
					$this->assertEquals( true, true, 'Test Completed Successfully.' );
				}
			}
		} catch ( Exception $e ) {
			//Do not use $e->getTrace() here or there will be a very hard to diagnose infinite loop and memory exhaustion.
			Debug::Text( $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Arr( $e->getTraceAsString(), 'An error occcured while running automated testing. in ' . $e->getFile() . ' on line: ' . $e->getLine(), __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitForUIInitComplete();
			$this->takeScreenshot( $this->screenshot_path . DIRECTORY_SEPARATOR . 'error.png' );
			$this->assertEquals( false, true, 'The test exited with an error: ' . $e->getMessage() );

			$this->quit(); //Close browser.
		}
	}

	function startTesting( $user, $pass ) {
		$this->user_name = $user;
		//uncomment these to limit the tests to a specific top level menu
		//$debug_menu_item = 'block'; //used for testing user and resolution loops
		//$debug_menu_item = 'main-menu-link-attendance_menu';
		//$debug_menu_item = 'main-menu-link-schedule_menu';
		//$debug_menu_item = 'main-menu-link-employee_menu';
		//$debug_menu_item = 'main-menu-link-company_menu';
		//$debug_menu_item = 'main-menu-link-payroll_menu';
		//$debug_menu_item = 'main-menu-link-policy_menu';
		//$debug_menu_item = 'main-menu-link-invoice_menu';
		//$debug_menu_item = 'main-menu-link-hr_menu';
		//$debug_menu_item = 'main-menu-link-recruitment_menu';
		//$debug_menu_item = 'main-menu-link-document_menu';
		//$debug_menu_item = 'main-menu-link-report_menu';
		//$debug_menu_item = 'main-menu-link-ui_kit';

		$hit_debugger = false;

		$this->Login( $user, $pass );

		//In case users are set to timesheet as default screen, prevents crash
		$this->waitForUIInitComplete();
		$this->goToDashboard();

		//Process profile menu items first.
		if ( isset( $debug_menu_item ) == false || ( isset( $debug_menu_item ) == true && $debug_menu_item == 'profile_menu_items' ) ) {
			$this->processProfileMenu( $this->screenshot_path . DIRECTORY_SEPARATOR . $this->width . 'x' . $this->height . DIRECTORY_SEPARATOR . $this->user_name . DIRECTORY_SEPARATOR . 'profile-menu-items' );
		}

		//Get the top level menu items only.
		$menu_elements = $this->getArrayBySelector( '#main-menu > li > a' );

		foreach ( $menu_elements as $menu_element ) {
			if ( $menu_element->attribute('id') == 'main-menu-link-home' || ( isset( $debug_menu_item ) && $debug_menu_item != $menu_element->attribute( 'id' ) && $hit_debugger == false ) ) {

				Debug::Text( 'Menu skipped or debug limited - B.', __FILE__, __LINE__, __METHOD__, 10 );
				continue;
			} else {
				$hit_debugger = true; // comment this out to test just the debug item. Defualt is to test everything forward of specified debug menu item.
			}

			$resolution = $this->width . 'x' . $this->height;
			$menu_screenshot_path = $this->screenshot_path . DIRECTORY_SEPARATOR . $resolution . DIRECTORY_SEPARATOR . $this->user_name . DIRECTORY_SEPARATOR . $menu_element->attribute( 'id' );
			$screenshot_filename = $menu_screenshot_path . '.png';

			$this->clickMainMenuItem( $menu_element->attribute( 'id' ) );

			$this->waitForUIInitComplete();
			if ( $menu_element->attribute( 'id' ) != 'main-menu-link-home' ) {
				Debug::Text( 'Processing Top Level Menu Element: [' . $menu_element->attribute( 'id' ) . '] screenshot filename: ' . $screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshot_filename );
				$this->processSubMenu( $menu_element, $menu_screenshot_path );
			}

			$this->waitForUIInitComplete();
			$this->clickMainMenuItem( $menu_element->attribute( 'id' ) );
		}

		Debug::Text( 'logging out', __FILE__, __LINE__, __METHOD__, 10 );
		$this->waitForUIInitComplete();
		$this->Logout();
		$this->waitForUIInitComplete();
	}

	function processProfileMenu( $menu_screenshot_path ) {
		//Uncomment to test specific profile menu items.
		//$debug_menu_item = 'profile-in-out';

		$edit_only_menus = [
				'profile-menu-LoginUserPreference',
				'profile-menu-LoginUserContact',
				'profile-menu-ChangePassword',
				'profile-in-out',
				'profile-help',
		];

		$header_icons = [
				'profile-in-out',
				'profile-notifications',
				'profile-help',
		];

		//Get all the profile menu items.
		$menu_elements = $this->getArrayBySelector( '#profile-menu-items > li > button' );

		//Check if topbar header icons are visibiile and add them to list of menu elements to traverse.
		if ( $this->isThere( '#profile-in-out' ) ) {
			$menu_elements[] = $this->byId( 'profile-in-out' );
		}
		if ( $this->isThere( '#profile-notifications' ) ) {
			$menu_elements[] = $this->byId( 'profile-notifications' );
		}

		if ( $this->isThere( '#profile-help' ) ) {
			$menu_elements[] = $this->byId( 'profile-help' );
		}

		foreach ( $menu_elements as $menu_element ) {

			if ( $menu_element->attribute( 'id' ) == 'profile-menu-Logout' || ( isset( $debug_menu_item ) && $debug_menu_item != $menu_element->attribute( 'id' ) ) ) {
				Debug::Text( 'Skipping menu item.', __FILE__, __LINE__, __METHOD__, 10 );
				continue;
			}

			if ( in_array( $menu_element->attribute( 'id' ), $header_icons ) ) {
				$menu_element->click();

				if ( $menu_element->attribute( 'id' ) == 'profile-help' ) {
					if ( $this->isThere( '#profile-menu-About' ) ) {
						$this->byId( 'profile-menu-About' )->click();
					} else {
						//User cannot view About page.
						continue;
					}
				}
			} else {
				$this->clickProfileMenuItem( $menu_element->attribute( 'id' ) );
			}

			$this->waitForUIInitComplete();

			$profile_menu_screenshot_path = $menu_screenshot_path . DIRECTORY_SEPARATOR . $menu_element->attribute( 'id' );
			$profile_menu_screenshot_filename = $profile_menu_screenshot_path . '.png';

			$this->waitForUIInitComplete();

			Debug::Text( 'Taking screenshot for profile menu element: ' . $menu_element->attribute( 'id' ) . ' screenshot filename: ' . $profile_menu_screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
			$this->takeScreenshot( $profile_menu_screenshot_filename );

			if ( in_array( $menu_element->attribute( 'id' ), $edit_only_menus ) === false ) {
				$this->processContextIcons( $profile_menu_screenshot_path, $this->byId( 'profile-menu-items' ), $menu_element );
				$this->processEditScreen( $profile_menu_screenshot_path, $this->byId( 'profile-menu-items' ), $menu_element, false );
			} else {
				$edit_view_context_menu_id = $this->getCurrentContextMenuId();
				$this->processTabs( $profile_menu_screenshot_path, $menu_element->attribute( 'id' ), $edit_view_context_menu_id, true );
			}

			$this->sectionCleanUp( $profile_menu_screenshot_path, $this->byId( 'profile-menu-items' ), $menu_element );
		}
	}

	function processSubMenu( $root_el, $menu_screenshot_path ) {
		$sub_menu_li_id = str_replace( 'main-menu-link', 'main-menu-item', $root_el->attribute( 'id' ) );
		$sub_menu_ul = $this->byCssSelector( '#' . $sub_menu_li_id . ' ul' );

		//array of submenus to limit testing to
		//$debug_sub_menu = array('PayPeriodSchedule');

		//array of sub elements in which we do not want to click any action icons.
		//these are mostly actions that have only save and cancel buttons

		$no_context_menus = [
			//wizards
			'main-menu-link-ProcessPayrollWizard',
			'main-menu-link-PayrollRemittanceAgencyEventWizardController',
			'main-menu-link-ImportCSV',
		];

		$skip_menus = [
				'main-menu-link-QuickStartWizard', //Skip as progressing this wizard modifies the data
		];

		$edit_only_views = [
				'main-menu-link-Company',
				'main-menu-link-InvoiceConfig',
				'main-menu-link-RecruitmentPortalConfig',
		];

		$nested_sub_menu_views = [
				'main-menu-link-report_employee_reports',
				'main-menu-link-report_timesheet_reports',
				'main-menu-link-report_payroll_reports',
				'main-menu-link-report_job_tracking_reports',
				'main-menu-link-report_invoice_reports',
				'main-menu-link-report_tax_reports',
				'main-menu-link-report_hr_reports',
		];

		Debug::Text( 'Processing Submenus at: #' . $root_el->attribute( 'id' ) . ' .ribbon-sub-menu-icon', __FILE__, __LINE__, __METHOD__, 10 );

		//Do not select further nested menus such as Employee Reports, TimeSheet Reports, etc. Those are handled further below.
		$sub_menu_elements = $sub_menu_ul->elements(
				$this->using( 'css selector' )->value( 'ul:not([style="display: none;"]) > li > a' )
		);

		if ( count( $sub_menu_elements ) > 0 ) {
			foreach ( $sub_menu_elements as $sub_menu_element ) {

				if ( in_array( $sub_menu_element->attribute( 'id' ), $skip_menus ) || isset( $debug_sub_menu ) && in_array( $sub_menu_element->attribute( 'id' ), $debug_sub_menu ) ) {
					Debug::Text( 'Menu item debug limited or skipped. - D.', __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				Debug::Text( 'Processing Submenus for selector: ' . $sub_menu_element->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
				$this->clickMainMenuItem( $sub_menu_element->attribute( 'id' ) );

				Debug::Text( $sub_menu_element->attribute( 'id' ) . ' submenu clicked.', __FILE__, __LINE__, __METHOD__, 10 );

				$submenu_screenshot_path = $menu_screenshot_path . DIRECTORY_SEPARATOR . $sub_menu_element->attribute( 'id' );
				$submenu_screenshot_filename = $submenu_screenshot_path . '.png';

				$this->waitForUIInitComplete();

				Debug::Text( 'Taking screenshot for submenu element: ' . $sub_menu_element->attribute( 'id' ) . ' screenshot filename: ' . $submenu_screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $submenu_screenshot_filename );

				if ( in_array( $sub_menu_element->attribute( 'id' ), $no_context_menus ) === false && in_array( $sub_menu_element->attribute( 'id' ), $edit_only_views ) === false && in_array( $sub_menu_element->attribute( 'id' ), $nested_sub_menu_views ) === false ) {
					$this->processContextIcons( $submenu_screenshot_path, $root_el, $sub_menu_element );
				}

				//Reports and other views are nested another layer down
				if ( in_array( $sub_menu_element->attribute( 'id' ), $nested_sub_menu_views ) ) {

					$parent_sub_menu_element = $sub_menu_element->byXPath( "./.." );
					Debug::Text( 'Processing nested sub menus for selector: ' . $parent_sub_menu_element->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
					$nested_sub_menu_elements = $parent_sub_menu_element->elements(
							$this->using( 'css selector' )->value( 'ul > li > a' )
					);

					array_shift( $nested_sub_menu_elements ); //Remove the first element, which is the current submenu

					foreach ( $nested_sub_menu_elements as $nested_sub_menu_element ) {
						Debug::Text( 'Processing nested submenu menus for selector: ' . $nested_sub_menu_element->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
						$this->clickMainMenuItem( $nested_sub_menu_element->attribute( 'id' ) );

						Debug::Text( $nested_sub_menu_element->attribute( 'id' ) . ' nested sub menu clicked.', __FILE__, __LINE__, __METHOD__, 10 );

						$report_submenu_screenshot_path = $menu_screenshot_path . DIRECTORY_SEPARATOR . $nested_sub_menu_element->attribute( 'id' );
						$report_submenu_screenshot_filename = $report_submenu_screenshot_path . '.png';

						$this->waitForUIInitComplete();

						Debug::Text( 'Taking screenshot for nested sub menu element: ' . $nested_sub_menu_element->attribute( 'id' ) . ' screenshot filename: ' . $report_submenu_screenshot_filename, __FILE__, __LINE__, __METHOD__, 10 );
						$this->takeScreenshot( $report_submenu_screenshot_filename );
						$this->waitForUIInitComplete();

						$this->processTabs( $report_submenu_screenshot_path, $nested_sub_menu_element, $this->getCurrentContextMenuId(), true );
						$this->clickCancel( $this->getCurrentContextMenuId() );
					}
				} else {
					$this->sectionCleanUp( $submenu_screenshot_path, $root_el, $sub_menu_element );
					$this->waitForUIInitComplete();
				}


				//Debug::Text( 'Reset to the top-level menu: ' . $sub_menu_element->attribute('id'), __FILE__, __LINE__, __METHOD__, 10 );
				//$this->clickMainMenuItem( $sub_menu_element->attribute('id') );
				//$this->waitForUIInitComplete();
			}
		}
	}

	function getCurrentContextMenuId() {
		if ( $this->isThere( '.edit-view .context-menu-mount-container' ) ) {
			return $this->byCssSelector( '.edit-view .context-menu-mount-container' )->attribute( 'id' );
		}

		$this->waitUntilByCssSelector( '.context-menu-mount-container' );

		return $this->byCssSelector( '.context-menu-mount-container' )->attribute( 'id' );
	}

	function sectionCleanUp( $submenu_screenshot_path, $root_el, $sub_menu_element ) {
		$context_menu_id = $this->getCurrentContextMenuId();
		//cleanup etc before going to the next submenu
		switch ( $sub_menu_element->attribute( 'id' ) ) {
			case 'profile-in-out':
				$this->clickCancel( $context_menu_id );
				$this->waitThenClick( '#yesBtn' );
				break;
			case 'profile-notifications':
				$this->byId( 'context-button-read' )->click();
				break;
			case 'profile-help':
				break;
			case 'main-menu-link-ProcessPayrollWizard':
			case 'main-menu-link-PayrollRemittanceAgencyEventWizardController':
			case 'main-menu-link-ImportCSV':
				$this->processWizard( $submenu_screenshot_path, $root_el, $sub_menu_element );
				break;
			case 'main-menu-link-QuickStartWizard':
				$this->processWizard( $submenu_screenshot_path, $root_el, $sub_menu_element );

				//might need this for a fresh install, but not after shutting off quick start nag screen.
//						if ( $this->byId('yesBtn') ) {
//							$this->byId('yesBtn')->click();
//						}
				break;
			case 'main-menu-link-RecruitmentPortalConfig':
				break;
			default:
				$this->processEditScreen( $submenu_screenshot_path, $root_el, $sub_menu_element, false );
				break;
		}
	}

	function clickRootAndSub( $root_el, $sub_el ) {
		$this->waitForUIInitComplete();
		$this->waitThenClick( '#' . $root_el->attribute( 'id' ) );
		$this->waitForUIInitComplete();
		$this->waitThenClick( '#' . $sub_el->attribute( 'id' ) );
		$this->waitForUIInitComplete();

		return true;
	}

	function clickMinimizedWindow() {
		Debug::Text( 'Clicking minimized tab...', __FILE__, __LINE__, __METHOD__, 10 );
		$this->waitThenClick( '.view-min-tab' );
		sleep( 1 ); //For some reason without this we get this exception often when going to Payroll -> Pay Stubs, Pay Stub Transaction, then clicking the minimized window: #ribbon .context-menu a - stale element reference: element is not attached to the page document

		return true;
	}

	function processContextIcons( $path, $root_el, $sub_el ) {
		Debug::Text( 'processContextIcons: Root ID: ' . $root_el->attribute( 'id' ) . ' Sub Element: ' . $sub_el->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
		//$context_menu_debug_id =  'editclienticon';

		//array of action icons we do not wish to click
		$skip_array = [
			//no need
			'context-button-save',
			'context-button-delete_icon',
			'context-button-cancel',
			'context-button-copy',
			'context-button-move',
			'context-button-print_menu',
			'context-button-export_excel',
			'context-group-export_excel-split-menu',
			'context-group-read',
			'context-group-navigate',
			'context-button-read',
			'context-button-unread',
			'context-button-navigate',
			'context-button-jump_to',
			'context-group-jump_to_header',
			'',

			'context-button-inboxicon',
			'context-button-senticon',
			'context-button-authorizationrequesticon',
			'context-button-authorizationtimesheeticon',
			'context-button-authorizationexpenseicon',

			//disabled for now due to bugs
			//all of these have submenu icons and the context icons are not working yet
			'context-button-re_calculate_timesheet',
			'context-button-generate_pay_stub',

			//'context-button-clientcontacticon',
			//'context-button-invoiceicon',
			//'context-button-transactionicon',
			//'context-button-paymentmethodicon',
			//'context-button-accumulatedtimeicon',
		];

		$this->waitForUIInitComplete();

		$this->waitUntilByCssSelector( '.context-menu-mount-container' );
		$context_menu_id = $this->byCssSelector( '.context-menu-mount-container' )->attribute( 'id' );

		$button_groups = $this->getArrayBySelector( '#' . $context_menu_id . ' div div div' );
		//Parse the button types and add to $action_element_groups

		foreach ( $button_groups as $button_group ) {
			Debug::Text( 'Parsing buttons in button group: ' . $button_group->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );

			//Now get each button in the group if it is not disabled
			$buttons_in_group = $button_group->elements(
					$this->using( 'css selector' )->value( 'button:not(.p-disabled)' )
			);

			//Only click the first button in button group for now
			$buttons_in_group = array_slice( $buttons_in_group, 0, 1 );

			$previous_context_menu_id = '';

			foreach ( $buttons_in_group as $context_button ) {

				if ( in_array( $context_button->attribute( 'id' ), $skip_array ) == false ) {
					if ( ( !isset( $context_menu_debug_id ) || stristr( $context_button->attribute( 'id' ), $context_menu_debug_id ) ) == false ) {
						$this->waitForUIInitComplete();
						continue;
					}

					Debug::Text( 'Clicking: #' . $context_button->attribute( 'id' ), __FILE__, __LINE__, __METHOD__, 10 );
					$context_button->click();

					$this->waitForUIInitComplete();

					//If context button such as Employee -> New opens a dialog, click yes
					if ( $this->isThere( '#yesBtn' ) ) {
						$this->byId( 'yesBtn' )->click();
						$this->waitForUIInitComplete();
					}

					//Get the new context menu on the new view if it exists. This happens when clicking "New" and other actions.
					//Check an edit view was even created in first place
					if ( $this->isThere( '.edit-view .context-menu-mount-container' ) ) {
						$edit_view_context_menu = $this->byCssSelector( '.edit-view .context-menu-mount-container' );
						$edit_view_context_menu_id = $edit_view_context_menu->attribute( 'id' );
					} else {
						$edit_view_context_menu_id = 'no_context_menu';
						$edit_view_context_menu = null;
					}

					if ( $edit_view_context_menu_id != $previous_context_menu_id ) {
						Debug::Text( 'New context menu id: ' . $edit_view_context_menu_id, __FILE__, __LINE__, __METHOD__, 10 );
						$previous_context_menu_id = $edit_view_context_menu_id;
						$new_context_menu = true;
					} else {
						$new_context_menu = false;
					}

					Debug::Text( '********   Taking screenshot for edit context menu #' . $edit_view_context_menu_id . ' screenshot filename: ' . $path . DIRECTORY_SEPARATOR . $edit_view_context_menu_id . '.png', __FILE__, __LINE__, __METHOD__, 10 );
					$this->takeScreenshot( $path . DIRECTORY_SEPARATOR . $edit_view_context_menu_id . '.png', true );

					//process tabs on applicable views
					$this->waitForUIInitComplete();
					//after screenshot is taken, some views need custom closure code
					switch ( $context_button->attribute( 'id' ) ) {
						case 'context-button-in_out':
							Debug::Text( 'Shutting down an inout screen: ' . $edit_view_context_menu_id, __FILE__, __LINE__, __METHOD__, 10 );
							$this->clickCancel( $edit_view_context_menu_id );
							$this->waitThenClick( '#yesBtn' );
							break;
							//TODO: Handle the below buttons as they are in dropdowns now
						//case 'scheduleicon':
						//case 'paystubicon':
						//	$this->clickMinimizedWindow();
						//	break;
						//case 'clientcontacticon':
						//case 'invoiceicon':
						//case 'transactionicon':
						//case 'paymentmethodicon':
						//	$this->processTabs( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id ); //fix
						//	$this->clickMinimizedWindow();
						//	break;
						//case 'recalculatetimesheet':
						//case 'generatepaystub':
						//case 'jobinvoiceicon':
						case 'quickstartwizard':
						case 'context-button-import_icon':
						case 'context-button-share_report':
							$this->processWizard( $path . DIRECTORY_SEPARATOR . $edit_view_context_menu_id, $root_el, $sub_el );
							break;
						case 'accumulatedtimeicon':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id , $sub_el_id, $el->attribute('id'));
							break;
						case 'paystubtransactionicon':
							Debug::Text( 'paystubtransaction context icon...', __FILE__, __LINE__, __METHOD__, 10 );
							$this->clickMinimizedWindow();
							break;
//					case 'remittancesourceaccount':
//						$this->processTabs ( $path . DIRECTORY_SEPARATOR . $id, $root_id, $sub_el_id, $id, TRUE );
//						break;
						default:
							if ( $edit_view_context_menu !== null ) {
								//If there is a new context menu, process the tabs
								if ( $new_context_menu == true ) {
									$this->processTabs( $path . DIRECTORY_SEPARATOR . $edit_view_context_menu_id, $context_button->attribute( 'id' ), $edit_view_context_menu_id, true );
								}

								$this->clickCancel( $edit_view_context_menu_id );
							}
						//$this->byCssSelector( '.context-menu-bar .tticon-cancel_black_24dp' )->byXPath("./..")->click(); //TODO: Fix cancel button for all context menus.
						//no hashtag
						//$this->clickCancel();
//						if ( $this->byCssSelector('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon') ) {
//							Debug::Text( 'clicking: #cancelIcon.', __FILE__, __LINE__, __METHOD__, 10 );
//
//							$this->waitThenClick('#'. $this->byCssSelector('#ribbon .context-menu a')->attribute('ref') .' li:not(.disable-image):not(.invisible-image) #cancelIcon' );
//						}
					}

					//Make sure that cancel icon is not invisible or disabled.
					$this->waitForUIInitComplete();
				}
			}
		}

		Debug::Text( 'Done! Clicking context menu now...', __FILE__, __LINE__, __METHOD__, 10 );
		//$this->waitThenClick( '#ribbon .context-menu a' );
		$this->waitForUIInitComplete();
	}

	function processTabs( $path, $icon_clicked, $context_menu_id = false, $is_edit_view = false ) {

		Debug::Text( 'Retrieving Tabs for: ' . $context_menu_id, __FILE__, __LINE__, __METHOD__, 10 );
		$tabs = $this->getTabs( $icon_clicked, $context_menu_id );

		Debug::Text( 'Processing Tabs for: ' . $context_menu_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( count( $tabs ) > 1 ) {
			$first_tab = array_shift( $tabs );
			foreach ( $tabs as $tab_el ) {
				$name = $tab_el->attribute( 'ref' );

				Debug::Text( 'Processing Tab: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $tab_el->displayed() ) {
					$tab_el->click();
				} else {
					//TODO: Scroll the tab buttons if tab is not visible.
				}

				$this->waitForUIInitComplete();

				if ( $is_edit_view != false ) {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . 'edit_view_' . $name . '.png';
				} else {
					$screenshotFileName = $path . DIRECTORY_SEPARATOR . $name . '.png';
				}
				Debug::Text( 'Taking screenshot for Tab: ' . $name . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
				$this->takeScreenshot( $screenshotFileName, true );
			}

			if ( $first_tab->displayed() ) {
				$first_tab->click(); //click first tab before cancel.
			} else {
				//TODO: Scroll the tab buttons if tab is not visible.
			}
		} else {
			$this->waitForUIInitComplete();
		}
	}

	function getTabs( $clicked_icon_name, $context_menu_id ) {
		$ignore_array = [
			//causes crash as tries to click invisible audit tab
			'profile-in-out',
		];

		if ( in_array( $clicked_icon_name, $ignore_array ) == true ) {
			Debug::Text( 'Not even looking at tabs', __FILE__, __LINE__, __METHOD__, 10 );

			return [];
		}

		$this->waitForUIInitComplete();

		$parent_context_border = $this->byCssSelector( '#' . $context_menu_id )->byXPath( "./.." );

		//Find none hidden tabs
		Debug::Text( 'Finding all tabs for:' . $context_menu_id, __FILE__, __LINE__, __METHOD__, 10 );
		$tabs = $parent_context_border->elements(
				$this->using( 'css selector' )->value( 'ul.ui-tabs-nav > li:not([style="display: none;"]) > a' )
		);

		return $tabs;
	}

	function processWizard( $submenu_screenshot_path, $root_el, $sub_el ) {
		$root_id = $root_el->attribute( 'id' );
		$sub_id = $sub_el->attribute( 'id' );
		Debug::Text( 'Looking At Wizard View: ' . $root_id . ' Sub View: ' . $sub_id, __FILE__, __LINE__, __METHOD__, 10 );

		//Wizards save steps so make sure we go to first step before taking screenshots.
		while ( $this->byId( 'wizard-back-button' )->attribute( 'disabled' ) == false && str_contains( $this->byId( 'wizard-back-button' )->attribute( 'class' ), 'disable-image' ) == false ) {
			$this->byId( 'wizard-back-button' )->click();
			$this->waitForUIInitComplete();
			if ( $this->isThere( '#yesBtn' ) ) {
				$this->byId( 'yesBtn' )->click();
				$this->waitForUIInitComplete();
			}
		}

		//Now go forward to last step in the wizard.
		$step = 1;

		$screenshotFileName = $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'wizard' . $sub_id . 'step' . $step . '.png';
		Debug::Text( 'Taking screenshot for wizard View: ' . $root_id . '=>' . $sub_id . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
		$this->takeScreenshot( $screenshotFileName );

		while ( $this->byId( 'wizard-forward-button' )->attribute( 'disabled' ) == false && str_contains( $this->byId( 'wizard-forward-button' )->attribute( 'class' ), 'disable-image' ) == false ) {
			$this->byId( 'wizard-forward-button' )->click();
			$this->waitForUIInitComplete();
			if ( $this->isThere( '#yesBtn' ) ) {
				$this->byId( 'yesBtn' )->click();
				$this->waitForUIInitComplete();
			}

			if ( $this->isThere( '#t-alert-close' ) ) {
				$this->byId( 't-alert-close' )->click();
				$this->waitForUIInitComplete();
			}

			$step++;

			$screenshotFileName = $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'wizard' . $sub_id . 'step' . $step . '.png';
			Debug::Text( 'Taking screenshot for wizard View: ' . $root_id . '=>' . $sub_id . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
			$this->takeScreenshot( $screenshotFileName );
		}

		$this->byId( 'wizard-close-button' )->click();
		$this->waitForUIInitComplete();

		if ( $this->isThere( '#yesBtn' ) ) {
			$this->byId( 'yesBtn' )->click();
			$this->waitForUIInitComplete();
		}

		if ( $this->isThere( '#t-alert-close' ) ) {
			$this->byId( 't-alert-close' )->click();
			$this->waitForUIInitComplete();
		}
	}

	function processEditScreen( $submenu_screenshot_path, $root_el, $sub_el, $context_el ) {
		$root_id = $root_el->attribute( 'id' );
		$sub_id = $sub_el->attribute( 'id' );
		Debug::Text( 'Looking At Edit View: ' . $root_id . ' Sub View: ' . $sub_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $context_el == false ) {
			$context_menu_id = $this->byCssSelector( '.context-menu-mount-container' )->attribute( 'id' );
		}

		$ignore_list = [
			//popovers that have grids under them so the grid is found and causes a crash.
			'context-button-in_out',
			'profile-menu-ChangePassword',
			'profile-menu-LoginUserPreference',
			'profile-menu-LoginUserContact',

			//overly complex grids that will need special considerations:
			'main-menu-link-TimeSheet',
			'main-menu-link-Schedule',
		];

		if ( in_array( $sub_id, $ignore_list ) ) {
			Debug::Text( 'Skipping due to ignore list...', __FILE__, __LINE__, __METHOD__, 10 );

			return;
		}

		//TODO: Look at this css selector
		$css_selector = '.grid-div .ui-jqgrid .ui-jqgrid-btable tr:nth-child(2) td:nth-child(2)';
		$this->waitForUIInitComplete();
		if ( $this->isThere( '.grid-div .no-result-div' ) === false && $this->isThere( $css_selector ) === true && $this->isThere( '#' . $context_menu_id . ' .left-view' ) == true ) {
			Debug::Text( 'Processing Edit View: ' . $root_id . '=>' . $sub_id, __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitThenClick( $css_selector );
			//Don't click view on Pay Stub or Invoice as it will download a file.
			if ( $sub_id == 'main-menu-link-PayStub' || $sub_id == 'main-menu-link-GovernmentDocument' || $sub_id == 'main-menu-link-Invoice' ) {
				if ( $this->isThere( '#' . $context_menu_id . ' #context-button-edit' ) ) {
					$this->waitThenClick( '#' . $context_menu_id . ' #context-button-edit' );
				}
				$icon_clicked = 'edit';
			} else {
				$this->waitThenClick( '#' . $context_menu_id . ' #context-button-view' );
				$icon_clicked = 'view';
			}

			$this->waitForUIInitComplete();
			$screenshotFileName = $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_' . $sub_id . '.png';

			Debug::Text( 'Taking screenshot for Edit View: ' . $root_id . '=>' . $sub_id . ' screenshot filename: ' . $screenshotFileName, __FILE__, __LINE__, __METHOD__, 10 );
			$this->takeScreenshot( $screenshotFileName );

			if ( $this->isThere( '.edit-view .context-menu-mount-container' ) ) {
				$edit_view_context_menu = $this->byCssSelector( '.edit-view .context-menu-mount-container' );
				$edit_view_context_menu_id = $edit_view_context_menu->attribute( 'id' );
				$this->processTabs( $submenu_screenshot_path . DIRECTORY_SEPARATOR . 'edit_view_' . $context_menu_id, $icon_clicked, $edit_view_context_menu_id, true );
				$this->waitForUIInitComplete();
				Debug::Text( 'Clicking Cancel at end of processEditScreen()', __FILE__, __LINE__, __METHOD__, 10 );
				$this->clickCancel( $edit_view_context_menu_id );
			}

			//does every edit have a cancel? NO. Exceptions is the exception, so it's in the ignore_list.
			$this->waitForUIInitComplete();
		}

		Debug::Text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );
	}
}

?>