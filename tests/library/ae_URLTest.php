<?php

require_once( 'defined_tables.php' );

$_SERVER['REQUEST_URI'] = '/author/phpunittest';


class ae_URLTest extends PHPUnit_Framework_TestCase {

	protected $dbc;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
		mysql_query( '
			INSERT INTO ' . TABLE_USERS . ' ( user_id, user_name_login, user_name, user_permalink, user_status )
			VALUES ( 9999, "phpunit", "phpunit", "author/phpunittest", "active" )
		' );
	}

	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_USERS . '
			WHERE user_id = 9999
			AND user_name = "phpunit"
		' );
		mysql_close( $this->dbc );
	}


	public function testAuthor() {
		$this->assertTrue( ae_URL::Author() == 9999 );
		ae_URL::DeleteCache();
	}


}
