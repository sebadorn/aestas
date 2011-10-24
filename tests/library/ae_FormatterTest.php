<?php

require_once( 'defined_tables.php' );


class ae_FormatterTest extends PHPUnit_Framework_TestCase {

	public function setUp() {}

	public function tearDown() {}


	public function testMonthToString() {
		$april = ae_Formatter::MonthToString( 4 );
		$this->assertTrue( $april === '04' );
		$november = ae_Formatter::MonthToString( 11 );
		$this->assertTrue( $november === '11' );
	}


	public function testMonthNumberToName() {
		$march = ae_Formatter::MonthNumberToName( 3 );
		$this->assertTrue( $march === 'March' );
		$december = ae_Formatter::MonthNumberToName( 12 );
		$this->assertTrue( $december === 'December' );
	}


}
