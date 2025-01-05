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


//Each Year:
//  Update below "$this->year = 2021;" to the new year.
//  Change BootStrapSelenium.php $selenium_config to change the host to connect to for selenium (localhost or dev server)
//  Run: ./run_selenium.sh --filter CAPayrollDeductionCRACompareTest::testCRAToCSVFile <-- This will add lines to the above CSV file once its complete.
//  Run: ./run_selenium.sh --filter CAPayrollDeductionCRACompareTest::testCRAFromCSVFile <-- This will test the PDOC numbers against our own.

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverAction;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\FileDetector;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Chrome;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;

/**
 * @group CAPayrollDeductionCRACompareTest
 */
class CAPayrollDeductionCRACompareTest extends PHPUnit\Framework\TestCase {
	private $default_wait_timeout = 4; //4 seconds

	private $selenium_config;
	private $year;
	private $tax_table_file;
	private $cra_deduction_test_csv_file;
	private $company_id;
	private $selenium_test_case_runs;
	public $driver = FALSE;


	function waitUntilByXPath( $xpath, $timeout = null ) {
		if ( $timeout == null ) {
			$timeout = $this->default_wait_timeout;
		}

		try {
			$this->driver->wait( $timeout, 500 )->until( WebDriverExpectedCondition::presenceOfElementLocated( WebDriverBy::xpath( $xpath ) ) );
			$this->driver->wait( $timeout, 500 )->until( WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath( $xpath ) ) );
		} catch( Exception $e ) {
			//echo "ERROR: Login was not successful, unable to find sign-in button!\n";
			Debug::text( 'ERROR: waitUntilByXPath() Failed: ' . $xpath .' Error: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			throw $e;
		}

		return true;
	}

	function findElementByXPath( $xpath, $timeout = null ) {
		if ( $timeout == null ) {
			$timeout = $this->default_wait_timeout;
		}

		if ( $this->waitUntilByXPath( $xpath, $timeout ) == true ) {
			$element = $this->driver->findElement( WebDriverBy::xpath( $xpath ) );
			return $element;
		} else {
			return false;
		}
	}

	public function setUp(): void {
		global $selenium_config;
		$this->selenium_config = $selenium_config;

		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->year = 2024;

		$this->tax_table_file = dirname( __FILE__ ) . '/../payroll_deduction/CAPayrollDeductionTest' . $this->year . '.csv';
		$this->cra_deduction_test_csv_file = dirname( $this->tax_table_file ) . DIRECTORY_SEPARATOR . 'CAPayrollDeductionCRATest' . $this->year . '.csv';

		$this->company_id = PRIMARY_COMPANY_ID;

		$this->selenium_test_case_runs = 0;

		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$options = new ChromeOptions();
		$options->addArguments( array(
									'--incognito',
									'--no-first-run',
									//--remote-debugging-port=9222
								)
		);

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability( ChromeOptions::CAPABILITY, $options );

		$this->driver = RemoteWebDriver::create( $selenium_config['host'], $capabilities );

		$session_id = $this->driver->getSessionID();
		Debug::Text( 'Selenium Server URL: '. $selenium_config['host'], __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'Selenium Session ID: '. $session_id, __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function CRAPayrollDeductionOnlineCalculator( $args = [] ) {
		if ( ENABLE_SELENIUM_REMOTE_TESTS != true ) {
			return false;
		}

		Debug::Arr( $args, 'Args: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( count( $args ) == 0 ) {
			return false;
		}

		try {
			if ( $this->selenium_test_case_runs == 0 ) {
				$url = 'https://www.canada.ca/en/revenue-agency/services/e-services/e-services-businesses/payroll-deductions-online-calculator.html';
				Debug::text( 'Navigating to URL: ' . $url, __FILE__, __LINE__, __METHOD__, 10 );
				$this->driver->get( $url );

				$this->findElementByXPath( '//a[contains(.,\'I accept\')]' )->click(); //Click "I Accept"
				$this->findElementByXPath( '//button[contains(.,\'Next\')]' )->click(); //Type of calculation
			} else {
				$this->findElementByXPath( '//button[contains(.,\'Modify the Current Calculation\')]' )->click();
			}

			$province_options = [
					'AB' => 'ALBERTA',
					'BC' => 'BRITISH_COLUMBIA',
					'SK' => 'SASKATCHEWAN',
					'MB' => 'MANITOBA',
					'QC' => 'QUEBEC',
					'ON' => 'ONTARIO',
					'NL' => 'NEWFOUNDLAND_AND_LABRADOR',
					'NB' => 'NEW_BRUNSWICK',
					'NS' => 'NOVA_SCOTIA',
					'PE' => 'PRINCE_EDWARD_ISLAND',
					'NT' => 'NORTHWEST_TERRITORIES',
					'YT' => 'YUKON',
					'NU' => 'NUNAVUT',
			];
			Debug::Arr( Option::getByKey( $args['province'], $province_options ), 'Attempting to Select Province Value: ', __FILE__, __LINE__, __METHOD__, 10 );

			( new WebDriverSelect( $this->findElementByXPath( '//form/rccr-select[1]/div/select' ) ) )->selectByValue( Option::getByKey( $args['province'], $province_options ) ); // Province

			$pp_options = [
					52 => 'WEEKLY_52PP',
					26 => 'BI_WEEKLY',
					24 => 'SEMI_MONTHLY',
			];
			( new WebDriverSelect( $this->findElementByXPath( '//form/rccr-select[2]/div/select' ) ) )->selectByValue( Option::getByKey( $args['pay_period_schedule'], $pp_options ) ); // Pay Period frequency


			//Date
			( new WebDriverSelect( $this->findElementByXPath( '//*[@id=\'datePaidYear\']' ) ) )->selectByVisibleText( date( 'Y', $args['date'] ) );
			( new WebDriverSelect( $this->findElementByXPath( '//form/fieldset/div[2]/div/div/rccr-select[2]/div/select' ) ) )->selectByValue( date( 'm', $args['date'] ) );
			( new WebDriverSelect( $this->findElementByXPath( '//form/fieldset/div[2]/div/div/rccr-select[3]/div/select' ) ) )->selectByVisibleText( date( 'd', $args['date'] ) );

			$this->findElementByXPath( '//button[contains(.,\'Next\')]' )->click();

			$ae = $this->findElementByXPath( '//form/fieldset[1]/rccr-currency-input[1]/div/div/input' );
			$ae->click();
			$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
			$ae->sendKeys( $args['gross_income'] );

			$this->findElementByXPath( '//button[contains(.,\'Next\')]' )->click();

			if ( isset( $args['federal_claim'] ) ) {
				$ae = $this->findElementByXPath( '//form/fieldset[1]/div/rccr-currency-input[1]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['federal_claim'] . WebDriverKeys::TAB );
			}

			if ( isset( $args['provincial_claim'] ) && $args['province'] != 'QC' ) {
				$ae = $this->findElementByXPath( '//form/fieldset[1]/div/rccr-currency-input[3]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['provincial_claim'] . WebDriverKeys::TAB );
			}

			Debug::text( 'Federal and Provincial claims entered...', __FILE__, __LINE__, __METHOD__, 10 );

			$result_row_offset = 1;

			if ( isset( $args['ytd_cpp_earnings'] ) ) {
				$ae = $this->findElementByXPath( '//form/rccr-button-group[1]/div/fieldset/div[2]/rccr-currency-input[1]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['ytd_cpp_earnings'] . WebDriverKeys::TAB );
			}

			if ( isset( $args['ytd_cpp'] ) ) {
				$ae = $this->findElementByXPath( '//form/rccr-button-group[1]/div/fieldset/div[2]/rccr-currency-input[2]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['ytd_cpp'] . WebDriverKeys::TAB );
			}

			if ( isset( $args['ytd_ei_earnings'] ) ) {
				$ae = $this->findElementByXPath( '//form/rccr-button-group[2]/div/fieldset/div[2]/rccr-currency-input[1]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['ytd_ei_earnings'] . WebDriverKeys::TAB );
			}

			if ( isset( $args['ytd_ei'] ) ) {
				$ae = $this->findElementByXPath( '//form/rccr-button-group[2]/div/fieldset/div[2]/rccr-currency-input[2]/div/div/input' );
				$ae->click();
				$ae->sendKeys( str_repeat( WebDriverKeys::DELETE, 11 ) );
				$ae->sendKeys( $args['ytd_ei'] . WebDriverKeys::TAB );
			}

			$this->findElementByXPath( '//button[contains(.,\'Calculate\')]' )->click();

			//
			//Handle results here
			//
			$this->waitUntilByXPath( '//button[contains(.,\'Next Calculation\')]', 30 );  //Next Calculation Button.

			$screenshot_file_name = '/tmp/cra_result_screenshot-' . $args['province'] . '-' . $args['federal_claim'] . '-' . $args['provincial_claim'] . '-' . $args['gross_income'] . '.png';
			$this->driver->takeScreenshot( $screenshot_file_name );


			//Make sure the gross income matches first.
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[2]/td[4]/strong' );
			Debug::Text( 'AE Text (Total Gross Income) [1]: ' . $ae->getText() . ' Expecting: ' . $args['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( TTi18n::parseFloat( $ae->getText() ), $args['gross_income'] );

			//Make sure Federal Claim Amount matches
			$ae = $this->findElementByXPath( '//rccr-wet-template/div/div/main/app-results/section/dl/dd[6]/td' );
			Debug::Text( 'AE Text (Federal Claim Amount): ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( TTi18n::parseFloat( $ae->getText() ), $args['federal_claim'], 'Federal Claim Expected: '. $args['federal_claim'] );
			//Make sure Provincial Claim Amount matches
			$ae = $this->findElementByXPath( '//rccr-wet-template/div/div/main/app-results/section/dl/dd[7]/td' );
			Debug::Text( 'AE Text (Provincial Claim Amount): ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( TTi18n::parseFloat( $ae->getText() ), $args['provincial_claim'], 'Provincial Claim Expected: '. $args['provincial_claim'] );

			$result_row_offset += 2;

			//Federal Tax
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[1]' ); //Row 6
			Debug::Text( 'AE Text (Federal) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 ); //Row Label
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[2]' ); //Row 6
			Debug::Text( 'AE Text (Federal) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['federal_deduction'] = TTi18n::parseFloat( $ae->getText() );

			$result_row_offset += 1;
			//Provincial Tax
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[1]' ); //Row 7
			Debug::Text( 'AE Text (Province) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 ); //Row Label
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[2]' ); //Row 7
			Debug::Text( 'AE Text (Province) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['provincial_deduction'] = TTi18n::parseFloat( $ae->getText() );

			$result_row_offset += 2;
			//CPP
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[1]' ); //Row 9
			Debug::Text( 'AE Text (CPP) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 ); //Row Label
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[3]' ); //Row 9
			Debug::Text( 'AE Text (CPP) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['cpp_deduction'] = TTi18n::parseFloat( $ae->getText() );

			$result_row_offset += 1;
			//CPP2
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[1]' ); //Row 9
			Debug::Text( 'AE Text (CPP2) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 ); //Row Label
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[3]' ); //Row 9
			Debug::Text( 'AE Text (CPP2) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['cpp2_deduction'] = TTi18n::parseFloat( $ae->getText() );

			$result_row_offset += 1;

			//EI
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[1]' ); //Row 10
			Debug::Text( 'AE Text (EI) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 ); //Row Label
			$ae = $this->findElementByXPath( '//app-results/app-results-salary/table/tbody/tr[' . $result_row_offset . ']/td[3]' ); //Row 10
			Debug::Text( 'AE Text (EI) [' . $result_row_offset . ']: ' . $ae->getText(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['ei_deduction'] = TTi18n::parseFloat( $ae->getText() );

			//Debug::Arr( $this->source(), 'Raw Source: ', __FILE__, __LINE__, __METHOD__, 10);
			//sleep(5);

			$this->selenium_test_case_runs++;
		} catch ( Exception $e ) {
			Debug::Text( 'Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->driver->takeScreenshot( tempnam( '/tmp/', 'cra_result_screenshot_exception' ) . '.png' );
			sleep( 15 );
		}

		if ( isset( $retarr ) ) {
			Debug::Arr( $retarr, 'Retarr: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		Debug::Text( 'ERROR: Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	//Simple control test to ensure the numbers match for the previous year.
	function testCRAControlCurrentYear() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( TTDate::getBeginYearEpoch() );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setForceExactClaimAmount( true ); //Force the exact claim amount to always be used, which matches PDOC.
		$pd_obj->setFederalTotalClaimAmount( $pd_obj->getBasicFederalClaimCodeAmount() ); //1=Basic Claim Amount
		$pd_obj->setProvincialTotalClaimAmount( $pd_obj->getBasicProvinceClaimCodeAmount() ); //1=Basic Claim Amount
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 8738 );

		$args = [
				'date'                => strtotime( $pd_obj->getDate() ), //Must be epoch.
				'province'            => $pd_obj->getProvince(),
				'pay_period_schedule' => $pd_obj->getAnnualPayPeriods(),
				'federal_claim'       => $pd_obj->getBasicFederalClaimCodeAmount(),
				'provincial_claim'    => $pd_obj->getBasicProvinceClaimCodeAmount(),
				'gross_income'        => $pd_obj->getGrossPayPeriodIncome(),
		];

		$retarr = $this->CRAPayrollDeductionOnlineCalculator( $args );

		Debug::text( '    Date: '. TTDate::getDate('DATE', strtotime( $pd_obj->getDate() ) ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( '    Results: CRA Federal: '. $retarr['federal_deduction'] .' Province: ' . $retarr['provincial_deduction'], __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( '    Results: TT Federal: '. $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) .' Province: ' . $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEqualsWithDelta( (float)$this->mf( $pd_obj->getFederalPayPeriodDeductions() ), (float)$retarr['federal_deduction'], 0.02 ); //Allow 1 penny variance
		$this->assertEqualsWithDelta( (float)$this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), (float)$retarr['provincial_deduction'], 0.02 ); //Allow 1 penny variance
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), $retarr['cpp_deduction'] );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeEI() ), $retarr['ei_deduction']  );

		return true;
	}

	//Simple control test to ensure the numbers match for the next year.
	//  Since this would normally be run sometimes in December, this will compare the values for the upcoming tax year.
	function testCRAControlNextYear() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( ( TTDate::getEndYearEpoch( time() ) + 86400 ) ); //**NOTE: this will fail if its run in the most recent tax year.
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setForceExactClaimAmount( true ); //Force the exact claim amount to always be used, which matches PDOC.
		$pd_obj->setFederalTotalClaimAmount( $pd_obj->getBasicFederalClaimCodeAmount() ); //1=Basic Claim Amount
		$pd_obj->setProvincialTotalClaimAmount( $pd_obj->getBasicProvinceClaimCodeAmount() ); //1=Basic Claim Amount
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 8738 );

		$args = [
				'date'                => strtotime( $pd_obj->getDate() ), //Must be epoch.
				'province'            => $pd_obj->getProvince(),
				'pay_period_schedule' => $pd_obj->getAnnualPayPeriods(),
				'federal_claim'       => $pd_obj->getBasicFederalClaimCodeAmount(),
				'provincial_claim'    => $pd_obj->getBasicProvinceClaimCodeAmount(),
				'gross_income'        => $pd_obj->getGrossPayPeriodIncome(),
		];

		$retarr = $this->CRAPayrollDeductionOnlineCalculator( $args );

		Debug::text( '    Date: '. TTDate::getDate('DATE', strtotime( $pd_obj->getDate() ) ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( '    Results: CRA Federal: '. $retarr['federal_deduction'] .' Province: ' . $retarr['provincial_deduction'], __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( '    Results: TT Federal: '. $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) .' Province: ' . $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEqualsWithDelta( (float)$this->mf( $pd_obj->getFederalPayPeriodDeductions() ), (float)$retarr['federal_deduction'], 0.01 ); //Allow up-to penny variance
		$this->assertEqualsWithDelta( (float)$this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), (float)$retarr['provincial_deduction'], 0.01 ); //Allow 1 penny variance
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), $retarr['cpp_deduction'] );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeEI() ), $retarr['ei_deduction']  );

		return true;
	}

	public function mf( $amount ) {
		return TTMath::MoneyRound( $amount );
	}

	function testCRAToCSVFile() {
		$this->assertEquals( true, file_exists( $this->tax_table_file ) );

		if ( file_exists( $this->cra_deduction_test_csv_file ) ) {
			$file = new SplFileObject( $this->cra_deduction_test_csv_file, 'r' );
			$file->seek( PHP_INT_MAX );

			$total_compare_lines = $file->key() + 1;
			unset( $file );
			Debug::text( 'Found existing CRATest file to resume with lines: ' . $total_compare_lines, __FILE__, __LINE__, __METHOD__, 10 );
		}


		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			if ( isset( $total_compare_lines ) && $i < $total_compare_lines ) {
				Debug::text( '  Skipping to line: ' . $total_compare_lines . '/' . $i, __FILE__, __LINE__, __METHOD__, 10 );
				$i++;
				continue;
			}

			if ( $row['province'] == 'QC' ) {
				//QC is not supported, skip it.
				Debug::text( '  Skipping QC line: ' . $total_compare_lines . '/' . $i, __FILE__, __LINE__, __METHOD__, 10 );
				$i++;
				continue;
			}

			Debug::text( 'Province: ' . $row['province'] . ' Income: ' . $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );
			if ( isset( $row['gross_income'] ) && isset( $row['low_income'] ) && isset( $row['high_income'] )
					&& $row['gross_income'] == '' && $row['low_income'] != '' && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}

			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";
				Debug::text( $i . '/' . $total_rows . '. Testing Province: ' . $row['province'] . ' Income: ' . $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );

				$args = [
						'date'                => strtotime( $row['date'] ),
						'province'            => $row['province'],
						'pay_period_schedule' => 26,
						'federal_claim'       => $this->mf( $row['federal_claim'] ),
						'provincial_claim'    => $this->mf( $row['provincial_claim'] ),
						'gross_income'        => $this->mf( $row['gross_income'] ),
				];

				$pd_obj = new PayrollDeduction( 'CA', $args['province'] );
				$pd_obj->setDate( $args['date'] ); //**NOTE: this will fail if its run in the most recent tax year.
				$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
				$pd_obj->setAnnualPayPeriods( $args['pay_period_schedule'] );
				$pd_obj->setForceExactClaimAmount( true ); //Force the exact claim amount to always be used, which matches PDOC.
				$pd_obj->setFederalTotalClaimAmount( $args['federal_claim'] );
				if ( $pd_obj->getFederalTotalClaimAmount() != 0 && $args['federal_claim'] <= $pd_obj->getBasicFederalClaimCodeAmount() ) {
					$row['federal_claim'] = $args['federal_claim'] = $pd_obj->getBasicFederalClaimCodeAmount(); //CRA PDOC doesn't allow entering claim amounts less than the basic amount.
					Debug::text( '  Using Minimum Basic Federal Claim Amount: ' . $args['federal_claim'], __FILE__, __LINE__, __METHOD__, 10 );
				}

				$pd_obj->setProvincialTotalClaimAmount( $args['provincial_claim'] );
				if ( $pd_obj->getProvincialTotalClaimAmount() != 0 && $args['provincial_claim'] < $pd_obj->getBasicProvinceClaimCodeAmount() ) {
					$row['provincial_claim'] = $args['provincial_claim'] = $pd_obj->getBasicProvinceClaimCodeAmount(); //CRA PDOC doesn't allow entering claim amounts less than the basic amount.
					Debug::text( '  Using Minimum Basic Provincial Claim Amount: ' . $args['provincial_claim'], __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Debug::Arr( $row, 'aFinal Row: ', __FILE__, __LINE__, __METHOD__, 10);
				try {
					$tmp_cra_data = $this->CRAPayrollDeductionOnlineCalculator( $args );
					if ( is_array( $tmp_cra_data ) ) {
						$retarr[] = array_merge( $row, $tmp_cra_data );

						//Debug::Arr( $retarr, 'bFinal Row: ', __FILE__, __LINE__, __METHOD__, 10);
						//sleep(2); //Should we be friendly to the Gov't server?
					} else {
						Debug::text( 'ERROR! Data from CRA is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
						break;
					}
				} catch( Exception $e ) {
					$this->writeCRAToCSVFile( $retarr );
					Debug::text( 'ERROR! Data from CRA is invalid, writing out data we have...', __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}

			$i++;
		}

		if ( isset( $retarr ) ) {
			$this->writeCRAToCSVFile( $retarr );

			//Make sure all rows are tested.
			$this->assertEquals( $total_rows, ( $i - 1 ) );
		} else {
			Debug::text( 'NOT Writing out CRA data due to error...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( true, false );
		}
	}

	function writeCRAToCSVFile( $retarr ) {
		$column_keys = array_keys( $retarr[0] );
		foreach ( $column_keys as $column_key ) {
			$columns[$column_key] = $column_key;
		}

		//var_dump($test_data);
		//var_dump($retarr);
		//echo Misc::Array2CSV( $retarr, $columns, FALSE, TRUE );
		Debug::text( 'Writing out CRA data to: ' . $this->cra_deduction_test_csv_file, __FILE__, __LINE__, __METHOD__, 10 );
		file_put_contents( $this->cra_deduction_test_csv_file, Misc::Array2CSV( $retarr, $columns, false, true ), FILE_APPEND );

		return true;
	}

	function testCRAFromCSVFile() {
		$this->assertEquals( true, file_exists( $this->cra_deduction_test_csv_file ) );

		$test_rows = Misc::parseCSV( $this->cra_deduction_test_csv_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( isset( $row['gross_income'] ) && isset( $row['low_income'] ) && isset( $row['high_income'] )
					&& $row['gross_income'] == '' && $row['low_income'] != '' && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setForceExactClaimAmount( true ); //Force the exact claim amount to always be used, which matches PDOC.
				$pd_obj->setFederalTotalClaimAmount( $row['federal_claim'] );
				$pd_obj->setProvincialTotalClaimAmount( $row['provincial_claim'] );

				$pd_obj->setEIExempt( false );
				$pd_obj->setCPPExempt( false );

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setYearToDateCPPContribution( 0 );
				$pd_obj->setYearToDateEIContribution( 0 );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEqualsWithDelta( (float)$this->mf( $row['federal_deduction'] ), (float)$this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.015, 'I: '. $i .' Gross Income: '. $row['gross_income'] .' Province: '. $row['province'] .' Federal Claim: '. $row['federal_claim'] ); //0.015=Allowed Delta
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEqualsWithDelta( (float)$this->mf( $row['provincial_deduction'] ), (float)$this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), 0.015, 'I: '. $i .' Gross Income: '. $row['gross_income'] .' Province: '. $row['province'] .' Provincial Claim: '. $row['provincial_claim'] .' Federal Claim: '. $row['federal_claim'] ); //0.015=Allowed Delta
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}
}

?>