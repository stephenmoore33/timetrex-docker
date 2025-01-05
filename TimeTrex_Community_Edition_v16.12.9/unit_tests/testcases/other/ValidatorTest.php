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
 * @group ValidatorTest
 */
class ValidatorTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//If using loadbalancer, we need to make a SQL query to initiate at least one connection to a database.
		//This is needed for testTimeZone() to work with the load balancer.
		global $db;
		$db->Execute( 'SELECT 1' );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function testValidatorIsFloat() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( true, $validator->isFloat( 'unit_test', 12.9 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', 12.91 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', 12.9123 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', 12.91234 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '12.9' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '12.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '12.9123' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '12.91234' ) );

		$this->assertEquals( true, $validator->isFloat( 'unit_test', -12.9 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', -12.91 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', -12.9123 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', -12.91234 ) );

		$this->assertEquals( true, $validator->isFloat( 'unit_test', '123.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '30 000.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1 234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1,234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1, 234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91 ' ) );

		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1 234.91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1.234,91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '30 000,91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '1. 234,91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91 ' ) );

		$this->assertEquals( true, $validator->isFloat( 'unit_test', .91 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', ',91' ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', 12, 9 ) );
		$this->assertEquals( true, $validator->isFloat( 'unit_test', '12,9' ) );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( true, $validator->isFloat( 'unit_test', .91 ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ',91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', 12, 9 ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '12,9' ) );

			$this->assertEquals( true, $validator->isFloat( 'unit_test', '123.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1 234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1,234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1, 234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1, 234.91 ' ) );

			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1 234.91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1.234,91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', '1. 234,91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91' ) );
			$this->assertEquals( true, $validator->isFloat( 'unit_test', ' 1. 234,91 ' ) );
		}
	}

	function testValidatorStripNonFloat() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( 12.9, $validator->stripNonFloat( 12.9 ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( 12.91 ) );
		$this->assertEquals( 12.9123, $validator->stripNonFloat( 12.9123 ) );
		$this->assertEquals( 12.91234, $validator->stripNonFloat( 12.91234 ) );
		$this->assertEquals( '12.9', $validator->stripNonFloat( '12.9' ) );
		$this->assertEquals( '12.91', $validator->stripNonFloat( '12.91' ) );
		$this->assertEquals( '12.9123', $validator->stripNonFloat( '12.9123' ) );
		$this->assertEquals( '12.91234', $validator->stripNonFloat( '12.91234' ) );

		$this->assertEquals( -12.9, $validator->stripNonFloat( -12.9 ) );
		$this->assertEquals( -12.91, $validator->stripNonFloat( -12.91 ) );
		$this->assertEquals( -12.9123, $validator->stripNonFloat( -12.9123 ) );
		$this->assertEquals( -12.91234, $validator->stripNonFloat( -12.91234 ) );

		$this->assertEquals( '-123.91', $validator->stripNonFloat( '-123.91' ) );
		$this->assertEquals( '123.91', $validator->stripNonFloat( '123.91' ) );
		$this->assertEquals( '1234.91', $validator->stripNonFloat( '1234.91' ) );
		$this->assertEquals( '1234.91', $validator->stripNonFloat( '1 234.91' ) );
		$this->assertEquals( '1234.91', $validator->stripNonFloat( '1,234.91' ) );    //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1234.91', $validator->stripNonFloat( '1, 234.91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91 ' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( '1234.91', $validator->stripNonFloat( '1 234.91' ) );
		$this->assertEquals( '1.23491', $validator->stripNonFloat( '1.234,91' ) );    //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1.23491', $validator->stripNonFloat( '1. 234,91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91 ' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( .91, $validator->stripNonFloat( .91 ) );
		$this->assertEquals( '91', $validator->stripNonFloat( ',91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		$this->assertEquals( '129', $validator->stripNonFloat( '12,9' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

		$this->assertEquals( '123.91', $validator->stripNonFloat( 'A123.91' ) );
		$this->assertEquals( '123.91', $validator->stripNonFloat( 'A123.91B' ) );
		$this->assertEquals( '123.91', $validator->stripNonFloat( '12A3.91' ) );
		$this->assertEquals( '123.91', $validator->stripNonFloat( '123A.91' ) );
		$this->assertEquals( '123.91', $validator->stripNonFloat( '123.A91' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '12.91-' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '12.91-ABC' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '12.91ABC-' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '12....91' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '---ABC---12...ABC...91' ) );
		$this->assertEquals( 12.91, $validator->stripNonFloat( '---ABC-  --12.  ..ABC.  ..91' ) );
		$this->assertEquals( -12.91, $validator->stripNonFloat( '-12.  ..ABC.  ..91' ) );

		$this->assertEquals( '123.91', $validator->stripNonFloat( '*&#$#\'"123.JKLFDJFL91' ) );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( .91, $validator->stripNonFloat( .91 ) );
			$this->assertEquals( '91', $validator->stripNonFloat( ',91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '129', $validator->stripNonFloat( '12,9' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

			$this->assertEquals( '123.91', $validator->stripNonFloat( '123.91' ) );
			$this->assertEquals( '1234.91', $validator->stripNonFloat( '1234.91' ) );
			$this->assertEquals( '1234.91', $validator->stripNonFloat( '1 234.91' ) );
			$this->assertEquals( '1234.91', $validator->stripNonFloat( '1,234.91' ) );    //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1234.91', $validator->stripNonFloat( '1, 234.91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1234.91', $validator->stripNonFloat( ' 1, 234.91 ' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.

			$this->assertEquals( '1234.91', $validator->stripNonFloat( '1 234.91' ) );
			$this->assertEquals( '1.23491', $validator->stripNonFloat( '1.234,91' ) );    //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1.23491', $validator->stripNonFloat( '1. 234,91' ) );   //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91' ) );  //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
			$this->assertEquals( '1.23491', $validator->stripNonFloat( ' 1. 234,91 ' ) ); //Always strips commas out so it doesn't work in other locales, TTi18n::parseFloat() should be called before this.
		}
	}

	function testValidatorStripNon32bitInteger() {
		$validator = new Validator();

		$this->assertEquals( 0, $validator->stripNon32bitInteger( 0 ) );
		$this->assertEquals( 100, $validator->stripNon32bitInteger( 100 ) );
		$this->assertEquals( 1000, $validator->stripNon32bitInteger( 1000 ) );
		$this->assertEquals( 2147483646, $validator->stripNon32bitInteger( 2147483646 ) );
		$this->assertEquals( 0, $validator->stripNon32bitInteger( 2147483648 ) );
		$this->assertEquals( -2147483647, $validator->stripNon32bitInteger( -2147483647 ) );
		$this->assertEquals( 0, $validator->stripNon32bitInteger( -2147483648 ) );
	}

	function testValidatorIsSIN() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		//
		// SIN - Canada
		//
		$this->assertEquals( true, $validator->isSIN( 'sin', '765 904 024', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '765904024', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', ' 765904024 ', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', ' 765-904-024 ', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', ' 765/904/024 ', null, 'CA' ) );

		$this->assertEquals( true, $validator->isSIN( 'sin', '765 904 024', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '958 752 115', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '046 454 286', null, 'CA' ) ); //As of around 2015, SINs starting with 0 apparently can now be valid rather than just fictitious purposes.

		//Special ones that can be entered if employee does not have one, or its unknown. Some tax documents may require this.
		$this->assertEquals( true, $validator->isSIN( 'sin', '999 999 999', null, 'CA' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '000 000 000', null, 'CA' ) );

		//Bogus ones that should fail.
		$this->assertEquals( false, $validator->isSIN( 'sin', '123 456 789', null, 'CA' ) );
		$this->assertEquals( false, $validator->isSIN( 'sin', '987 654 321', null, 'CA' ) );

		//
		// SSN - US
		//
		$this->assertEquals( true, $validator->isSIN( 'sin', '662-20-0887', null, 'US' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '662/20/0887', null, 'US' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '662 20 0887', null, 'US' ) );
		$this->assertEquals( true, $validator->isSIN( 'sin', '662200887', null, 'US' ) );

		// Foriegn
		$this->assertEquals( true, $validator->isSIN( 'sin', 'ABC662200887', null, 'UK' ) );
	}

	function testValidatorIsEmail() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( true, $validator->isEmail( 'email', 'abc@abc.com' ) );
		$this->assertEquals( true, $validator->isEmail( 'email', 'abc+123@abc.com' ) );
		$this->assertEquals( true, $validator->isEmail( 'email', 'abc+123@abc.xyz.com' ) );

		//Check DNS records.
		$this->assertEquals( true, $validator->isEmailAdvanced( 'email', 'support@timetrex.com', null, true, false ) );
		$this->assertEquals( false, $validator->isEmailAdvanced( 'email', 'bogus@timetrex2323132332.com', null, true, false ) ); //Bogus domain

		//Check SMTP
		$this->assertEquals( true, $validator->isEmailAdvanced( 'email', 'support@timetrex.com', null, true, true ) );

		global $config_vars;
		if ( !isset( $config_vars['mail']['disable_smtp_email_validation'] ) || isset( $config_vars['mail']['disable_smtp_email_validation'] ) && $config_vars['mail']['disable_smtp_email_validation'] == false ) {
			$this->assertEquals( false, $validator->isEmailAdvanced( 'email', 'supportBOGUS@timetrex.com', null, true, true ) );
		} else {
			$this->assertEquals( true, $validator->isEmailAdvanced( 'email', 'supportBOGUS@timetrex.com', null, true, true ) );
		}
	}

	function testIsURL() {
		TTi18n::setLocale( 'en_US' );

		$validator = new Validator();

		$this->assertEquals( true, $validator->isURL( 'url', 'http://www.timetrex.com', '', false ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com', '', false ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/', '', false ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/?test=1&testb=2', '', false ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/5726bc37-1549-452f-8248-9ccf72312960/standard/', '', false ) ); //Returns 404, but the headers are never checked.

		//Check headers.
		$this->assertEquals( true, $validator->isURL( 'url', 'http://www.timetrex.com', '', true ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com', '', true ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/', '', true ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/?test=1&testb=2', '', true ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/?test=1&testb=2#test=3', '', true ) );
		$this->assertEquals( true, $validator->isURL( 'url', 'https://www.timetrex.com/help/developers/community/?test=1&testb=2#/test3', '', true ) );

		$this->assertEquals( false, $validator->isURL( 'url', 'http://www.timetrex.com/5726bc37-1549-452f-8248-9ccf72312960/standard/', '', true ) ); //301 redirect to a 404
		$this->assertEquals( false, $validator->isURL( 'url', 'https://www.timetrex.com/5726bc37-1549-452f-8248-9ccf72312960/standard/', '', true ) ); //This is a 404 error.
	}

}

?>