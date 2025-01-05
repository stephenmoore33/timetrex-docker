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

class TTMathTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function testNumericAsString() {
		$this->assertEquals( '3.0E-6', (string)((float)0.000003) ); //Confirm it is being converted to scientific notation.
		$this->assertEquals( '3.0E-10', (string)((float)0.0000000003) ); //Confirm it is being converted to scientific notation.
		$this->assertEquals( '0.0000030000', TTMath::getNumericAsString( (float)0.000003 ) );
		$this->assertEquals( '0.0000000003', TTMath::getNumericAsString( (float)0.0000000003 ) );

		$this->assertEquals( '9223372036854775807', 9223372036854775807 );
		$this->assertEquals( '9223372036854775807', PHP_INT_MAX );
		$this->assertEquals( '9223372036854775806', ( PHP_INT_MAX - 1 ) );
		$this->assertEquals( '9223372036854775806', strval( PHP_INT_MAX - 1 ) );

		$this->assertEquals( '9223372036854775807', TTMath::getNumericAsString( PHP_INT_MAX ) ); //When using INT types, it supports up to the max.
		$this->assertEquals( '9223372036854775806', TTMath::getNumericAsString( ( PHP_INT_MAX - 1 ) ) ); //When using INT types, it supports up to the max.
		$this->assertEquals( '9223372036854765807', TTMath::getNumericAsString( ( PHP_INT_MAX - 10000 ) ) ); //When using INT types, it supports up to the max.

		$this->assertEquals( '99999999999999.0000000000', TTMath::getNumericAsString( (float)99999999999999 ) ); //When using FLOAT types, precision can be lost.
		$this->assertEquals( '999999999999999.0000000000', TTMath::getNumericAsString( (float)999999999999999 ) ); //When using FLOAT types, precision can be lost.

		$this->assertEquals( '0', TTMath::getNumericAsString( (float)9999999999999999 ) ); //When using FLOAT types, precision is lost in this example.
		$this->assertEquals( '0', TTMath::getNumericAsString( (float)PHP_INT_MAX ) ); //When using FLOAT types, precision is lost in this example.

		$this->assertEquals( '18446744073709552274', TTMath::getNumericAsString( '18446744073709552274' ) );
		$this->assertEquals( '18446744073709552274123456789', TTMath::getNumericAsString( '18446744073709552274123456789' ) );

		$this->assertEquals( '0', TTMath::getNumericAsString( 0 ) );
		$this->assertEquals( '0', TTMath::getNumericAsString( null ) );
		$this->assertEquals( '0', TTMath::getNumericAsString( '' ) );
		$this->assertEquals( '0', TTMath::getNumericAsString( true ) );
		$this->assertEquals( '0', TTMath::getNumericAsString( false ) );
		$this->assertEquals( '0', TTMath::getNumericAsString( [] ) );
	}

	public function testAdd() {
		$this->assertEquals( 3, TTMath::add( 1, 2 ) );
		$this->assertEquals( '0.0000000006', TTMath::add( (float)0.0000000003, (float)0.0000000003 ) );
		$this->assertEquals( '18446744073709551614.0000000000', TTMath::add( PHP_INT_MAX, PHP_INT_MAX ) );

		$this->assertEquals( 3, TTMath::multiAdd( 1, 2 ) );
		$this->assertEquals( 28, TTMath::multiAdd( 1, 2, 3, 4, 5, 6, 7 ) );
		$this->assertEquals( 34, TTMath::multiAdd( 1, 2, 3, 4, 5, 6, 7, [ 1, 2, 3 ] ) );
	}

	public function testSub() {
		$this->assertEquals( 2, TTMath::sub( 3, 1 ) );
		$this->assertEquals( '0.0000000006', TTMath::sub( (float)0.0000000009, (float)0.0000000003 ) );
		$this->assertEquals( '0.0000000000', TTMath::sub( PHP_INT_MAX, PHP_INT_MAX ) );
		$this->assertEquals( '9223372036854775806.0000000000', TTMath::sub( PHP_INT_MAX, 1 ) );
		$this->assertEquals( ( PHP_INT_MAX - 1 ), TTMath::sub( PHP_INT_MAX, 1 ) );
		$this->assertEquals( 2, TTMath::multiSub( 3, 1 ) );
		$this->assertEquals( 72, TTMath::multiSub( 100, 1, 2, 3, 4, 5, 6, 7 ) );
		$this->assertEquals( 66, TTMath::multiSub( 100, 1, 2, 3, 4, 5, 6, 7, [ 1, 2, 3 ] ) );
	}

	public function testMul() {
		$this->assertEquals( 3, TTMath::mul( 3, 1 ) );
		$this->assertEquals( 12, TTMath::mul( 6, 2 ) );
		$this->assertEquals( 0, TTMath::mul( 6, 0 ) );

		$this->assertEquals( '0.0000000003', TTMath::mul( 1, (float)0.0000000003 ) );
	}

	public function testDiv() {
		$this->assertEquals( 3, TTMath::div( 3, 1 ) );
		$this->assertEquals( 3, TTMath::div( 6, 2 ) );
		$this->assertEquals( 0, TTMath::div( 6, 0 ) );
		$this->assertEquals( 0, TTMath::div( 6, null ) );
		$this->assertEquals( 0, TTMath::div( 6, true ) );
		$this->assertEquals( 0, TTMath::div( 6, false ) );
		$this->assertEquals( 0, TTMath::div( 6, '' ) );
		$this->assertEquals( 0, TTMath::div( 6, [] ) );


		//$this->assertEquals( 0, TTMath::div( 18446744073709552274, 0 ) );
		$this->assertEquals( TTMath::div( '18446744073709552274', 60), TTMath::div( '18446744073709552274', 60 ) );

		$this->assertEquals( 3333333333.3333333333, TTMath::div( 1, (float)0.0000000003 ) );
	}

	public function testMod() {
		$this->assertEquals( 0, TTMath::mod( 3, 1 ) );
		$this->assertEquals( 1, TTMath::mod( 6, 5 ) );
		$this->assertEquals( TTMath::mod( 6, 5), TTMath::mod( 6, 5 ) );

		$this->assertEquals( TTMath::mod( '18446744073709552274', 60), TTMath::mod( '18446744073709552274', 60 ) );
	}

	public function testComp() {
		$this->assertEquals( 0, TTMath::comp( 1, 1 ) ); //0=Equal, 1=Num1 is larger, -1=Num1 is smaller
		$this->assertEquals( 0, TTMath::comp( 0, 0 ) );
		$this->assertEquals( 0, TTMath::comp( (float)0.0000000003, (float)0.0000000003 ) );

		$this->assertEquals( 0, TTMath::comp( 'test', 0 ) ); //Non-numeric values are converted to 0.
		$this->assertEquals( 0, TTMath::comp( 0, 'test' ) ); //Non-numeric values are converted to 0.
		$this->assertEquals( 0, TTMath::comp( 0, null ) ); //Non-numeric values are converted to 0.

		$this->assertEquals( 1, TTMath::comp( 1, 0 ) );
		$this->assertEquals( -1, TTMath::comp( 0, 1 ) );
	}


	function testArraySum() {
		$amounts = [ 0.000000000000, 0.000000000000, 0.000000000000, 0.000000000000 ];
		$this->assertEquals( 0, TTMath::ArraySum( $amounts ) );

		$amounts = [ '9223372036854775000', '0.000000000005' ];
		$this->assertEquals( '9223372036854775000.0000000000', TTMath::ArraySum( $amounts ) );
		$this->assertEquals( '9223372036854774784', TTMath::ArraySum( $amounts, 0 ) );
		$this->assertEquals( '9223372036854774784.00', TTMath::ArraySum( $amounts, 2 ) );

		$amounts = [ '0.000000000001', '0.000000000001', '0.000000000001', '0.000000000001' ];
		$this->assertEquals( 0, TTMath::ArraySum( $amounts ) );

		$amounts = [ '0.0000000001', '0.0000000001', '0.0000000001', '0.0000000001' ];
		$this->assertEquals( '0.0000000004', TTMath::ArraySum( $amounts ) );

		$amounts = [ '0.0000000001', '0.0000000001', '0.0000000001', '0.0000000001', 'abc' ]; //Non-numeric values are skipped.
		$this->assertEquals( '0.0000000004', TTMath::ArraySum( $amounts ) );
	}

	function testBeforeAndAfterDecimal() {
		$this->assertEquals( '0', TTMath::getBeforeDecimal( 0 ) );
		$this->assertEquals( '1', TTMath::getBeforeDecimal( 1 ) );
		$this->assertEquals( '53', TTMath::getBeforeDecimal( 53 ) );
		$this->assertEquals( '-53', TTMath::getBeforeDecimal( -53 ) );
		$this->assertEquals( '3', TTMath::getBeforeDecimal( 3.14 ) );
		$this->assertEquals( '-3', TTMath::getBeforeDecimal( -3.14 ) );
		$this->assertEquals( '-3', TTMath::getBeforeDecimal( -3.1 ) );
		$this->assertEquals( '510', TTMath::getBeforeDecimal( 510.9 ) );
		$this->assertEquals( '-510', TTMath::getBeforeDecimal( -510.9 ) );

		$this->assertEquals( '123456789012', TTMath::getBeforeDecimal( 123456789012.12 ) );
		$this->assertEquals( '1234567890', TTMath::getBeforeDecimal( 1234567890.1234 ) );
		$this->assertEquals( '123456789', TTMath::getBeforeDecimal( 123456789.123456789 ) );  // Float precision overflow
		$this->assertEquals( '123456789', TTMath::getBeforeDecimal( '123456789.123456789' ) );

		$this->assertEquals( 0, TTMath::getBeforeDecimal( '377777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getBeforeDecimal( '37777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 3777777777777777777, TTMath::getBeforeDecimal( '3777777777777777777' ) ); //Does not Overflow.

		$this->assertEquals( 0, TTMath::getBeforeDecimal( '-377777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getBeforeDecimal( '-37777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( -3777777777777777777, TTMath::getBeforeDecimal( '-3777777777777777777' ) ); //Does not Overflow.

		$this->assertEquals( '-123456789012', TTMath::getBeforeDecimal( -123456789012.12 ) );
		$this->assertEquals( '-1234567890', TTMath::getBeforeDecimal( -1234567890.1234 ) );
		$this->assertEquals( '-123456789', TTMath::getBeforeDecimal( -123456789.123456789 ) );  // Float precision overflow
		$this->assertEquals( '-123456789', TTMath::getBeforeDecimal( '-123456789.123456789' ) );


		$this->assertEquals( '0', TTMath::getAfterDecimal( 0 ) );
		$this->assertEquals( '0', TTMath::getAfterDecimal( 1 ) );
		$this->assertEquals( '0', TTMath::getAfterDecimal( 3 ) );
		$this->assertEquals( '0', TTMath::getAfterDecimal( -3 ) );
		$this->assertEquals( '10', TTMath::getAfterDecimal( -3.1, true ) );
		$this->assertEquals( '1', TTMath::getAfterDecimal( -3.1, false ) );
		$this->assertEquals( '14', TTMath::getAfterDecimal( 3.14 ) );
		$this->assertEquals( '14', TTMath::getAfterDecimal( -3.14 ) );
		$this->assertEquals( '90', TTMath::getAfterDecimal( 510.9 ) );
		$this->assertEquals( '90', TTMath::getAfterDecimal( -510.9 ) );
		$this->assertEquals( '12', TTMath::getAfterDecimal( -123456789.123456789, true ) );

		$this->assertEquals( '12', TTMath::getAfterDecimal( 123456789012.12, false ) );
		$this->assertEquals( '1234', TTMath::getAfterDecimal( 1234567890.1234, false ) );
		$this->assertEquals( '12346', TTMath::getAfterDecimal( 123456789.123456789, false ) );       // Float precision overflow
		$this->assertEquals( '123456789', TTMath::getAfterDecimal( '123456789.123456789', false ) ); //Passed as string, so no float precision overflow.

		$this->assertEquals( '12', TTMath::getAfterDecimal( -123456789012.12, false ) );
		$this->assertEquals( '1234', TTMath::getAfterDecimal( -1234567890.1234, false ) );
		$this->assertEquals( '12346', TTMath::getAfterDecimal( -123456789.123456789, false ) );       // Float precision overflow
		$this->assertEquals( '123456789', TTMath::getAfterDecimal( '-123456789.123456789', false ) ); //Passed as string, so no float precision overflow.

		$this->assertEquals( 0, TTMath::getAfterDecimal( '377777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getAfterDecimal( '37777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getAfterDecimal( '3777777777777777777.123' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getAfterDecimal( '3777777777777777777' ) ); //Does not Overflow.
		$this->assertEquals( 12, TTMath::getAfterDecimal( '37777777777777.123' ) ); //Does not Overflow, but it rounds to two decimals.
		$this->assertEquals( 123, TTMath::getAfterDecimal( '37777777777777.123', false ) ); //Does not Overflow, but it rounds to two decimals.

		$this->assertEquals( 0, TTMath::getAfterDecimal( '-377777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getAfterDecimal( '-37777777777777777777' ) ); //Overflows to 0.
		$this->assertEquals( 0, TTMath::getAfterDecimal( '-3777777777777777777' ) ); //Does not Overflow.


		$this->assertEquals( false, TTMath::getAfterDecimal( '0.377777777777777777777', false ) ); //Overflows to 0.
		$this->assertEquals( 38, TTMath::getAfterDecimal( '0.377777777777777777777', true ) ); //Round to two decimal places.
	}

	function testMoneyRoundDifference() {
		$this->assertEquals( 0.00, TTMath::MoneyRoundDifference( '100.01', 2 ) );
		$this->assertEquals( -0.001, TTMath::MoneyRoundDifference( '100.011', 2 ) ); //Rounded Value=100.01, Different is -0.001
		$this->assertEquals( 0.005, TTMath::MoneyRoundDifference( '100.015', 2 ) );  //Rounded Value=100.02, Different is -0.001
		$this->assertEquals( 0.001, TTMath::MoneyRoundDifference( '100.019', 2 ) );  //Rounded Value=100.02, Different is -0.001
		$this->assertEquals( 0.0000001, TTMath::MoneyRoundDifference( '100.0199999', 2 ) );
	}

	function testMoneyRound() {
		$this->assertEquals( 1.12, TTMath::MoneyRound( 1.1234, 2 ) );
		$this->assertEquals( 1.12, TTMath::MoneyRound( 1.12456, 2 ) );
		$this->assertEquals( 1.13, TTMath::MoneyRound( 1.1256, 2 ) );
		$this->assertEquals( 1234567890.15, TTMath::MoneyRound( 1234567890.145, 2 ) );
		$this->assertEquals( 1234567890123456780.15, TTMath::MoneyRound( 1234567890123456780.145, 2 ) );
		$this->assertEquals( 1234567890123456789123456789.15, TTMath::MoneyRound( 1234567890123456789123456789.145, 2 ) );
		$this->assertEquals( 1000000000000000000000000000000000.15, TTMath::MoneyRound( 1000000000000000000000000000000000.145, 2 ) );

		$currency_obj = new CurrencyFactory();
		$currency_obj->setRoundDecimalPlaces( 3 );

		$this->assertEquals( 1.123, TTMath::MoneyRound( 1.1234, null, $currency_obj ) );
		$this->assertEquals( 1.124, TTMath::MoneyRound( 1.12444, null, $currency_obj ) );
		$this->assertEquals( 1.126, TTMath::MoneyRound( 1.1256, null, $currency_obj ) );

		$this->assertEquals( 1.123, TTMath::MoneyRound( 1.1234, 2, $currency_obj ) );
		$this->assertEquals( 1.124, TTMath::MoneyRound( 1.12444, 2, $currency_obj ) );
		$this->assertEquals( 1.126, TTMath::MoneyRound( 1.1256, 2, $currency_obj ) );
	}

	function testFloatComparison() {
		$float1 = (float)845.92;
		$float2 = (float)14.3;
		$float3 = (float)860.22;
		$added_floats = ( $float1 + $float2 ); //860.22

		if ( $added_floats == $float3 ) {
			$this->assertTrue( false ); //This is to show the float comparison problem. Actual value should be opposite of this.
		} else {
			$this->assertTrue( true );
		}

		if ( $added_floats >= $float3 ) {
			$this->assertTrue( false ); //This is to show the float comparison problem. Actual value should be opposite of this.
		} else {
			$this->assertTrue( true );
		}

		$this->assertEquals( 0, TTMath::comp( $added_floats, $float3 ) );        //0=Equal
		$this->assertEquals( 0, TTMath::comp( $added_floats, (float)860.22 ) );  //0=Equal
		$this->assertEquals( 1, TTMath::comp( $added_floats, (float)860.21 ) );  //1=Greater Than
		$this->assertEquals( -1, TTMath::comp( $added_floats, (float)860.23 ) ); //-1=Less Than

		$this->assertEquals( true, TTMath::compareFloat( $added_floats, $float3, '==' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.22, '==' ) );
		$this->assertEquals( false, TTMath::compareFloat( $added_floats, (float)860.21, '==' ) );

		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.22, '>=' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.21, '>=' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.01, '>=' ) );

		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.22, '<=' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.23, '<=' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.33, '<=' ) );

		$this->assertEquals( false, TTMath::compareFloat( $added_floats, (float)860.22, '>' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.21, '>' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.01, '>' ) );

		$this->assertEquals( false, TTMath::compareFloat( $added_floats, (float)860.22, '<' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.23, '<' ) );
		$this->assertEquals( true, TTMath::compareFloat( $added_floats, (float)860.33, '<' ) );
	}

	function testgetAmountToLimit() {
		//Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( 0, TTMath::getAmountToLimit( 0, 100 ) );
		$this->assertEquals( 1, TTMath::getAmountToLimit( 1, 100 ) );
		$this->assertEquals( 50, TTMath::getAmountToLimit( 50, 100 ) );
		$this->assertEquals( 98, TTMath::getAmountToLimit( 98, 100 ) );
		$this->assertEquals( 99, TTMath::getAmountToLimit( 99, 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 101, 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 200, 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 201, 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 1001, 100 ) );

		//Positive Amount and Negative Limit should always return 0
		$this->assertEquals( 0, TTMath::getAmountToLimit( 101, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 100, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 99, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 98, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 0, -100 ) );

		//Positive amounts and 0 limit should always return the amount.
		$this->assertEquals( 101, TTMath::getAmountToLimit( 101, 0 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, 0 ) );
		$this->assertEquals( 99, TTMath::getAmountToLimit( 99, 0 ) );
		$this->assertEquals( 98, TTMath::getAmountToLimit( 98, 0 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 0, 0 ) );


		//Negative amounts, but positive limits should always return 0.
		$this->assertEquals( 0, TTMath::getAmountToLimit( -101, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( -100, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( -99, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( -98, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 0, 100 ) );

		//Negative amounts and 0 limit should always return the amount.
		$this->assertEquals( -101, TTMath::getAmountToLimit( -101, 0 ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, 0 ) );
		$this->assertEquals( -99, TTMath::getAmountToLimit( -99, 0 ) );
		$this->assertEquals( -98, TTMath::getAmountToLimit( -98, 0 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( 0, 0 ) );


		//Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( -100, TTMath::getAmountToLimit( -1001, -100 ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -200, -100 ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -101, -100 ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, -100 ) );
		$this->assertEquals( -99, TTMath::getAmountToLimit( -99, -100 ) );
		$this->assertEquals( -98, TTMath::getAmountToLimit( -98, -100 ) );
		$this->assertEquals( -50, TTMath::getAmountToLimit( -50, -100 ) );
		$this->assertEquals( -1, TTMath::getAmountToLimit( -1, -100 ) );
		$this->assertEquals( -0, TTMath::getAmountToLimit( -0, -100 ) );

		//Test non-float/integer limit
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, false ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, true ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, null ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, '' ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, false ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, true ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, null ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, '' ) );

		//Test float/int 0 limit
		$this->assertEquals( 100, TTMath::getAmountToLimit( 100, 0 ) );
		$this->assertEquals( -100, TTMath::getAmountToLimit( -100, 0 ) );


		//Amount DIff. - Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( 300, TTMath::getAmountDifferenceToLimit( -200, 100 ) ); //This could be 0, or +300, or 100?
		$this->assertEquals( 101, TTMath::getAmountDifferenceToLimit( -1, 100 ) );   //This could be 0, or +101
		$this->assertEquals( 100, TTMath::getAmountDifferenceToLimit( 0, 100 ) );
		$this->assertEquals( 99, TTMath::getAmountDifferenceToLimit( 1, 100 ) );
		$this->assertEquals( 50, TTMath::getAmountDifferenceToLimit( 50, 100 ) );
		$this->assertEquals( 2, TTMath::getAmountDifferenceToLimit( 98, 100 ) );
		$this->assertEquals( 1, TTMath::getAmountDifferenceToLimit( 99, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 100, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 101, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 200, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 201, 100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 1001, 100 ) );

		//Amount Diff Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( -1001, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( -200, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( -101, -100 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( -100, -100 ) );
		$this->assertEquals( -1, TTMath::getAmountDifferenceToLimit( -99, -100 ) );
		$this->assertEquals( -2, TTMath::getAmountDifferenceToLimit( -98, -100 ) );
		$this->assertEquals( -50, TTMath::getAmountDifferenceToLimit( -50, -100 ) );
		$this->assertEquals( -99, TTMath::getAmountDifferenceToLimit( -1, -100 ) );
		$this->assertEquals( -100, TTMath::getAmountDifferenceToLimit( -0, -100 ) );
		$this->assertEquals( -100, TTMath::getAmountDifferenceToLimit( 0, -100 ) );
		$this->assertEquals( -101, TTMath::getAmountDifferenceToLimit( 1, -100 ) );                               //This could be 0, or -101
		$this->assertEquals( -300, TTMath::getAmountDifferenceToLimit( 200, -100 ) );                             //This could be 0, or -300

		//When limit is 0, the result should be the opposite sign of the amount. Treated as AmountDifferenceDownToLimit essentially.
		$this->assertEquals( 5000, TTMath::getAmountDifferenceToLimit( -5000, 0 ) );
		$this->assertEquals( 50, TTMath::getAmountDifferenceToLimit( -50, 0 ) );
		$this->assertEquals( 2, TTMath::getAmountDifferenceToLimit( -2, 0 ) );
		$this->assertEquals( 1, TTMath::getAmountDifferenceToLimit( -1, 0 ) );
		$this->assertEquals( 0, TTMath::getAmountDifferenceToLimit( 0, 0 ) );
		$this->assertEquals( -1, TTMath::getAmountDifferenceToLimit( 1, 0 ) );
		$this->assertEquals( -2, TTMath::getAmountDifferenceToLimit( 2, 0 ) );
		$this->assertEquals( -50, TTMath::getAmountDifferenceToLimit( 50, 0 ) );
		$this->assertEquals( -5000, TTMath::getAmountDifferenceToLimit( 5000, 0 ) );

		//Mimic how UserDeduction handles Fixed Amount w/Target for Loan amounts.
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 1001, 0 ), 100 ) ); //0 is the amount difference, so the result is 0.
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 101, 0 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 100, 0 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 99, 0 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 1, 0 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 0, 0 ), 100 ) );
		$this->assertEquals( 1, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -1, 0 ), 100 ) );
		$this->assertEquals( 99, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -99, 0 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -100, 0 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -101, 0 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -1001, 0 ), 100 ) );

		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 1001, 1 ), 100 ) ); //0 is the amount difference, so the result is 0.
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 101, 1 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 100, 1 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 99, 1 ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 1, 1 ), 100 ) );
		$this->assertEquals( 1, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( 0, 1 ), 100 ) );
		$this->assertEquals( 2, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -1, 1 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -99, 1 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -100, 1 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -101, 1 ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( TTMath::getAmountDifferenceToLimit( -1001, 1 ), 100 ) );

		//UserDeduction could possibly uses abs() so a limit of 0 will continue to work if the amount is higher or lower than it.
		// Since the Tax/Deduction record is almost always a Employee Deduction, the resulting amount should always be a positive value.
		// This seems like a way to shoot the user in the foot though as it would allow incorrect setup where balance amount is positive rather than negative to "kinda work"
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( -1001, 0 ) ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( -101, 0 ) ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( -100, 0 ) ), 100 ) );
		$this->assertEquals( 99, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( -99, 0 ) ), 100 ) );
		$this->assertEquals( 1, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( -1, 0 ) ), 100 ) );
		$this->assertEquals( 0, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 0, 0 ) ), 100 ) );
		$this->assertEquals( 1, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 1, 0 ) ), 100 ) );
		$this->assertEquals( 99, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 99, 0 ) ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 100, 0 ) ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 101, 0 ) ), 100 ) );
		$this->assertEquals( 100, TTMath::getAmountToLimit( abs( TTMath::getAmountDifferenceToLimit( 1001, 0 ) ), 100 ) );
	}

	function testgetAmountAroundLimit() {
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 100, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 0, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 99, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 50, 'under_limit' => 50, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 50, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 99, 'under_limit' => 1, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 99, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 100, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 101, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 50 ], TTMath::getAmountAroundLimit( 150, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 101 ], TTMath::getAmountAroundLimit( 201, 0, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 90, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 0, 10, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 89, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 10, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 49, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 50, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 2, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 97, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 1, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 98, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 99, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 1, 100, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 1, 101, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 5 ], TTMath::getAmountAroundLimit( 1, 105, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 100 ], TTMath::getAmountAroundLimit( 1, 200, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 10, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 0, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 9, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 1, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 8, 'under_limit' => 2, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 8, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 9, 'under_limit' => 1, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 9, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 10, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 11, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 2 ], TTMath::getAmountAroundLimit( 12, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 40 ], TTMath::getAmountAroundLimit( 50, 90, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( 0, 100, 100 ) );

		//Test with negative amounts around the limit.
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 11, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -11, 'under_limit' => 21, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -11, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -10, 'under_limit' => 15, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -10, 95, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -5, 'under_limit' => 5, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -10, 105, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -10, 110, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 10 ], TTMath::getAmountAroundLimit( -10, 120, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -11, 111, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 10 ], TTMath::getAmountAroundLimit( -10, 120, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -11, 'under_limit' => 11, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -11, 100, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 1 ], TTMath::getAmountAroundLimit( 11, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -1, 101, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -1, 'under_limit' => 1, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -1, 100, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 0, 'over_limit' => 9 ], TTMath::getAmountAroundLimit( 10, 99, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 8 ], TTMath::getAmountAroundLimit( -1, 109, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 3 ], TTMath::getAmountAroundLimit( -5, 108, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => -2, 'under_limit' => 2, 'over_limit' => 0 ], TTMath::getAmountAroundLimit( -5, 103, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 2, 'under_limit' => 0, 'over_limit' => 3 ], TTMath::getAmountAroundLimit( 5, 98, 100 ) );


		//Test an example that may be used in a report to ensure the YTD amount never exceeds the limit.
		$ytd_amount = 0;

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 0, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 100, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 0, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 10, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 90, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 10, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 50, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 50, 'under_limit' => 40, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 60, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 30, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 30, 'under_limit' => 10, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 90, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 9, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 9, 'under_limit' => 1, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 99, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 1, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 0, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 1, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );

		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( 99, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 99 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );


		//Test an example that may be used in a report to ensure the YTD amount never exceeds the limit. Include negative amounts too.
		$ytd_amount = 0;
		$ytd_adjusted_amount = 0;

		$current_amount = 0;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 100, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 0, $ytd_amount );
		$this->assertEquals( 0, $ytd_adjusted_amount );


		$current_amount = 10;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 90, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 10, $ytd_amount );
		$this->assertEquals( 10, $ytd_adjusted_amount );

		$current_amount = 50;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 50, 'under_limit' => 40, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 60, $ytd_amount );
		$this->assertEquals( 60, $ytd_adjusted_amount );

		$current_amount = 30;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 30, 'under_limit' => 10, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 90, $ytd_amount );
		$this->assertEquals( 90, $ytd_adjusted_amount );

		$current_amount = -10;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => -10, 'under_limit' => 20, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 80, $ytd_amount );
		$this->assertEquals( 80, $ytd_adjusted_amount );

		$current_amount = 30;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 20, 'under_limit' => 0, 'over_limit' => 10 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 110, $ytd_amount );
		$this->assertEquals( 100, $ytd_adjusted_amount );

		$current_amount = -5;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 5 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 105, $ytd_amount );
		$this->assertEquals( 100, $ytd_adjusted_amount );

		$current_amount = -10;
		$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $current_amount, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => -5, 'under_limit' => 5, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $current_amount;
		$ytd_adjusted_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 95, $ytd_amount );
		$this->assertEquals( 95, $ytd_adjusted_amount );
	}

	function testRemoveTrailingZeros() {
		TTi18n::setLocale( 'en_US' );

		$this->assertEquals( 12.9, TTMath::removeTrailingZeros( 12.9 ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.90 ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.900 ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.9000 ) );
		$this->assertEquals( '12.900', TTMath::removeTrailingZeros( 12.9000, 3 ) );

		$this->assertEquals( -12.9, TTMath::removeTrailingZeros( -12.9 ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.90 ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.900 ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.9000 ) );
		$this->assertEquals( '-12.900', TTMath::removeTrailingZeros( -12.9000, 3 ) );

		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.9' ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.90' ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.900' ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.9000' ) );
		$this->assertEquals( '12.900', TTMath::removeTrailingZeros( '12.9000', 3 ) );

		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.9' ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.90' ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.900' ) );
		$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.9000' ) );
		$this->assertEquals( '-12.900', TTMath::removeTrailingZeros( '-12.9000', 3 ) );

		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
		$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( 12.9, TTMath::removeTrailingZeros( 12.9 ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.90 ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.900 ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( 12.9000 ) );
			$this->assertEquals( '12.900', TTMath::removeTrailingZeros( 12.9000, 3 ) );

			$this->assertEquals( -12.9, TTMath::removeTrailingZeros( -12.9 ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.90 ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.900 ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( -12.9000 ) );
			$this->assertEquals( '-12.900', TTMath::removeTrailingZeros( -12.9000, 3 ) );

			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.9' ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.90' ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.900' ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( '12.9000' ) );
			$this->assertEquals( '12.900', TTMath::removeTrailingZeros( '12.9000', 3 ) );

			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.9' ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.90' ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.900' ) );
			$this->assertEquals( '-12.90', TTMath::removeTrailingZeros( '-12.9000' ) );
			$this->assertEquals( '-12.900', TTMath::removeTrailingZeros( '-12.9000', 3 ) );

			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );

			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
			$this->assertEquals( '12.90', TTMath::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );
		}
	}
}

?>