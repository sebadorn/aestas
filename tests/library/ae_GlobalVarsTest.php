<?php

require_once( 'defined_tables.php' );


class ae_GlobalVarsTest extends PHPUnit_Framework_TestCase {


	public function setUp() {}


	public function tearDown() {}


	public function testGetCommentAllowedAttributes() {
		$result1 = ae_GlobalVars::getCommentAllowedAttributes();
		$result2 = ae_GlobalVars::getCommentAllowedAttributes( 'preg_replace' );

		$this->assertTrue( is_array( $result1 ) );
		$this->assertTrue( preg_match( '/([a-zA-Z0-9]+|)*(a-zA-Z0-9)?/', $result2 ) > 0 );
	}


	public function testGetCommentNotStripTags() {
		$result1 = ae_GlobalVars::getCommentNotStripTags();
		$result2 = ae_GlobalVars::getCommentNotStripTags( 'strip_tags' );

		$this->assertTrue( preg_match( '/(<[a-zA-Z0-9]+>)*/', $result1 ) > 0 );
		$this->assertTrue( preg_match( '/([a-zA-Z0-9]+|)*(a-zA-Z0-9)?/', $result2 ) > 0 );
	}


	public function testGetTableToColumnPrefix() {
		$result1 = ae_GlobalVars::getTableToColumnPrefix( 'rule' );
		$this->assertTrue( $result1 === TABLE_RULES );

		$result2 = ae_GlobalVars::getTableToColumnPrefix( 'post' );
		$this->assertTrue( $result2 === TABLE_POSTS );

		$result3 = ae_GlobalVars::getTableToColumnPrefix( 'bear' );
		$this->assertFalse( $result3 );
	}


}
