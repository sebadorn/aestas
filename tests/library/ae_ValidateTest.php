<?php

require_once( 'defined_tables.php' );


class ae_ValidateTest extends PHPUnit_Framework_TestCase {


	public function setUp() {}

	public function tearDown() {}


	public function testHasUrlProtocol() {
		$this->assertTrue( ae_Validate::hasUrlProtocol( 'http://example.com' ) );
		$this->assertTrue( ae_Validate::hasUrlProtocol( 'https://www.example.com' ) );
		$this->assertTrue( ae_Validate::hasUrlProtocol( 'ftp://www.example.com' ) );
		$this->assertTrue( ae_Validate::hasUrlProtocol( 'ftps://example.com' ) );

		$this->assertFalse( ae_Validate::hasUrlProtocol( 'example.com' ) );
		$this->assertFalse( ae_Validate::hasUrlProtocol( 'www.example.com' ) );
		$this->assertFalse( ae_Validate::hasUrlProtocol( '' ) );
	}


	public function testIsAuthSystem() {
		$this->assertTrue( ae_Validate::isAuthSystem( 'session' ) );
		$this->assertTrue( ae_Validate::isAuthSystem( 'cookie' ) );

		$this->assertFalse( ae_Validate::isAuthSystem( 'bear' ) );
		$this->assertFalse( ae_Validate::isAuthSystem( '' ) );
	}


	public function testIsBoolean() {
		$this->assertTrue( ae_Validate::isBoolean( 'true' ) );
		$this->assertTrue( ae_Validate::isBoolean( 'false' ) );
		$this->assertTrue( ae_Validate::isBoolean( true ) );
		$this->assertTrue( ae_Validate::isBoolean( false ) );

		$this->assertFalse( ae_Validate::isBoolean( 'wrong' ) );
		$this->assertFalse( ae_Validate::isBoolean( 9 ) );

		// Only the string "true/false" or real boolean values
		$this->assertFalse( ae_Validate::isBoolean( 1 ) );
		$this->assertFalse( ae_Validate::isBoolean( 0 ) );
		$this->assertFalse( ae_Validate::isBoolean( '' ) );
	}


	public function testIsCategoryStatus() {
		$this->assertTrue( ae_Validate::isCategoryStatus( 'active' ) );
		$this->assertTrue( ae_Validate::isCategoryStatus( 'trash' ) );

		$this->assertFalse( ae_Validate::isCategoryStatus( 'spam' ) );
		$this->assertFalse( ae_Validate::isCategoryStatus( '' ) );
	}


	public function testIsCommentStatus() {
		$this->assertTrue( ae_Validate::isCommentStatus( 'unapproved' ) );
		$this->assertTrue( ae_Validate::isCommentStatus( 'approved' ) );
		$this->assertTrue( ae_Validate::isCommentStatus( 'spam' ) );
		$this->assertTrue( ae_Validate::isCommentStatus( 'trash' ) );

		$this->assertFalse( ae_Validate::isCommentStatus( 'bear' ) );
		$this->assertFalse( ae_Validate::isCommentStatus( '' ) );
	}


	public function testIsDateMySQL() {
		$this->assertTrue( ae_Validate::isDate_MySQL( '2011-04-17' ) );
		$this->assertTrue( ae_Validate::isDate_MySQL( date( 'Y-m-d' ) ) );

		$this->assertFalse( ae_Validate::isDate_MySQL( date( 'Y-m-d H:i:s' ) ) );
	}


	public function testIsDigit() {
		$this->assertTrue( ae_Validate::isDigit( 999 ) );
		$this->assertTrue( ae_Validate::isDigit( '123' ) );
		$this->assertTrue( ae_Validate::isDigit( 0 ) );
		$this->assertTrue( ae_Validate::isDigit( -10 ) );
		$this->assertTrue( ae_Validate::isDigit( '-49' ) );

		$this->assertFalse( ae_Validate::isDigit( '2bear4' ) );
		$this->assertFalse( ae_Validate::isDigit( 40.9 ) );
		$this->assertFalse( ae_Validate::isDigit( .9 ) );
		$this->assertFalse( ae_Validate::isDigit( -4.9 ) );
		$this->assertFalse( ae_Validate::isDigit( '10/2' ) );
		$this->assertFalse( ae_Validate::isDigit( '' ) );
		$this->assertFalse( ae_Validate::isDigit( '-' ) );
		$this->assertFalse( ae_Validate::isDigit( '+' ) );
	}


	public function testIsEmail() {
		$this->assertTrue( ae_Validate::isEmail( 'honey_bear@example.com' ) );
		$this->assertTrue( ae_Validate::isEmail( 'Bear4@sub.example.org' ) );

		$this->assertFalse( ae_Validate::isEmail( '' ) );
		$this->assertFalse( ae_Validate::isEmail( 'foo[at]example.de' ) );
		$this->assertFalse( ae_Validate::isEmail( '.@example.net' ) );
	}


	public function testIsGravatarRating() {
		$this->assertTrue( ae_Validate::isGravatarRating( 'r' ) );
		$this->assertTrue( ae_Validate::isGravatarRating( 'pg' ) );
		$this->assertTrue( ae_Validate::isGravatarRating( 'g' ) );
		$this->assertTrue( ae_Validate::isGravatarRating( 'x' ) );

		$this->assertFalse( ae_Validate::isGravatarRating( 's' ) );
		$this->assertFalse( ae_Validate::isGravatarRating( 4 ) );
		$this->assertFalse( ae_Validate::isGravatarRating( '' ) );
	}


	public function testIsIp() {
		$this->assertTrue( ae_Validate::isIp( '127.0.0.1' ) );
		$this->assertTrue( ae_Validate::isIp( '4.87.9.255' ) );
		$this->assertTrue( ae_Validate::isIp( '2001:0db8:85a3:0000:0:8a2e:0370:7334' ) );

		$this->assertFalse( ae_Validate::isIp( '-1.0.0.0' ) );
		$this->assertFalse( ae_Validate::isIp( 'bear:9' ) );
		$this->assertFalse( ae_Validate::isIp( '' ) );
		$this->assertFalse( ae_Validate::isIp( '4.87.9.256' ) );
	}


	public function testIsMediaStatus() {
		$this->assertTrue( ae_Validate::isMediaStatus( 'available' ) );
		$this->assertTrue( ae_Validate::isMediaStatus( 'trash' ) );

		$this->assertFalse( ae_Validate::isMediaStatus( 'spam' ) );
		$this->assertFalse( ae_Validate::isMediaStatus( '' ) );
	}


	public function testIsMediaType() {
		$this->assertTrue( ae_Validate::isMediaType( 'image' ) );
		$this->assertTrue( ae_Validate::isMediaType( 'text' ) );
		$this->assertTrue( ae_Validate::isMediaType( 'video' ) );
		$this->assertTrue( ae_Validate::isMediaType( 'application' ) );
		$this->assertTrue( ae_Validate::isMediaType( 'audio' ) );

		$this->assertFalse( ae_Validate::isMediaType( 'bear' ) );
		$this->assertFalse( ae_Validate::isMediaType( '' ) );
	}


	public function testIsNewsfeedDisplay() {
		$this->assertTrue( ae_Validate::isNewsfeedDisplay( 'default' ) );
		$this->assertTrue( ae_Validate::isNewsfeedDisplay( 'excerpt' ) );
		$this->assertTrue( ae_Validate::isNewsfeedDisplay( 'full' ) );
		$this->assertTrue( ae_Validate::isNewsfeedDisplay( 'shorten' ) );

		$this->assertFalse( ae_Validate::isNewsfeedDisplay( 'bear' ) );
		$this->assertFalse( ae_Validate::isNewsfeedDisplay( '' ) );
	}


	public function testPostStatus() {
		$this->assertTrue( ae_Validate::isPostStatus( 'published' ) );
		$this->assertTrue( ae_Validate::isPostStatus( 'draft' ) );
		$this->assertTrue( ae_Validate::isPostStatus( 'expired' ) );
		$this->assertTrue( ae_Validate::isPostStatus( 'trash' ) );

		$this->assertFalse( ae_Validate::isPostStatus( 'bear' ) );
		$this->assertFalse( ae_Validate::isPostStatus( '' ) );
	}


	public function testIsRulePrecision() {
		$this->assertTrue( ae_Validate::isRulePrecision( 'exact' ) );
		$this->assertTrue( ae_Validate::isRulePrecision( 'contains' ) );
		$this->assertTrue( ae_Validate::isRulePrecision( 'regex' ) );

		$this->assertFalse( ae_Validate::isRulePrecision( 'bear' ) );
		$this->assertFalse( ae_Validate::isRulePrecision( '' ) );
	}


	public function testIsRuleStatus() {
		$this->assertTrue( ae_Validate::isRuleStatus( 'active' ) );
		$this->assertTrue( ae_Validate::isRuleStatus( 'inactive' ) );

		$this->assertFalse( ae_Validate::isRuleStatus( 'trash' ) );
		$this->assertFalse( ae_Validate::isRuleStatus( '' ) );
	}


	public function testIsStatus() {
		$this->assertTrue( ae_Validate::isStatus( 'approved', 'comment') );
		$this->assertTrue( ae_Validate::isStatus( 'draft', 'post' ) );

		$this->assertFalse( ae_Validate::isStatus( 'bear', 'comment' ) );
		$this->assertFalse( ae_Validate::isStatus( 'trash', '' ) );
		$this->assertFalse( ae_Validate::isStatus( '', 'page' ) );
		$this->assertFalse( ae_Validate::isStatus( 'draft', 'user' ) );
	}


	public function testIsTableColumnPrefix() {
		$this->assertTrue( ae_Validate::isTableColumnPrefix( 'rule' ) );
		$this->assertTrue( ae_Validate::isTableColumnPrefix( 'post' ) );
		$this->assertTrue( ae_Validate::isTableColumnPrefix( 'cat' ) );

		$this->assertFalse( ae_Validate::isTableColumnPrefix( 'bear' ) );
		$this->assertFalse( ae_Validate::isTableColumnPrefix( '' ) );
	}


	public function testIsTimestampMySQL() {
		$this->assertTrue( ae_Validate::isTimestamp_MySQL( date( 'Y-m-d H:i:s' ) ) );
		$this->assertTrue( ae_Validate::isTimestamp_MySQL( '2011-04-17 13:26:00' ) );

		$this->assertFalse( ae_Validate::isTimestamp_MySQL( '20110417132600' ) );
		$this->assertFalse( ae_Validate::isTimestamp_MySQL( '' ) );
	}


	public function testIsUrl() {
		$this->assertTrue( ae_Validate::isUrl( 'http://example.com' )  );
		$this->assertTrue( ae_Validate::isUrl( 'http://www.example.com' )  );
		$this->assertTrue( ae_Validate::isUrl( 'http://sub1.example.com' )  );
		$this->assertTrue( ae_Validate::isUrl( 'example.com' ) );
		$this->assertTrue( ae_Validate::isUrl( 'www2.example.com' ) );
		$this->assertTrue( ae_Validate::isUrl( 'sub.example.com' ) );

		$this->assertFalse( ae_Validate::isUrl( 'bear' ) );
		$this->assertFalse( ae_Validate::isUrl( 'http://bear' ) );
		$this->assertFalse( ae_Validate::isUrl( 'http://' ) );
		$this->assertFalse( ae_Validate::isUrl( '' ) );
	}


	public function testIsUserEditor() {
		$this->assertTrue( ae_Validate::isUserEditor( 'ckeditor' ) );
		$this->assertTrue( ae_Validate::isUserEditor( 'code' ) );

		$this->assertFalse( ae_Validate::isUserEditor( 'bear' ) );
		$this->assertFalse( ae_Validate::isUserEditor( '' ) );
	}


	public function testIsUserRole() {
		$this->assertTrue( ae_Validate::isUserRole( 'admin' ) );
		$this->assertTrue( ae_Validate::isUserRole( 'guest' ) );
		$this->assertTrue( ae_Validate::isUserRole( 'author' ) );

		$this->assertFalse( ae_Validate::isUserRole( 'administrator' ) );
		$this->assertFalse( ae_Validate::isUserRole( '' ) );
	}


	public function testIsUserStatus() {
		$this->assertTrue( ae_Validate::isUserStatus( 'active' ) );
		$this->assertTrue( ae_Validate::isUserStatus( 'suspended' ) );
		$this->assertTrue( ae_Validate::isUserStatus( 'trash' ) );
		$this->assertTrue( ae_Validate::isUserStatus( 'deleted' ) );

		$this->assertFalse( ae_Validate::isUserStatus( 'bear' ) );
		$this->assertFalse( ae_Validate::isUserStatus( '' ) );
	}


}
