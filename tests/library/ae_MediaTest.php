<?php

require_once( 'defined_tables.php' );


class ae_MediaTest extends PHPUnit_Framework_TestCase {

	protected $dbc;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
		mysql_query( '
			INSERT INTO ' . TABLE_USERS . ' ( user_id, user_name_login, user_name )
			VALUES ( 9999, "phpunit", "phpunit" )
		' );
		mysql_query( '
			INSERT INTO ' . TABLE_MEDIA . ' ( media_date, media_name, media_type, media_dimensions, media_uploader, media_status )
			VALUES ( "2000-01-01 01:00:00", "phpunittest_1", "image/png", "99x99", 9999, "available" )
		' );
	}


	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_MEDIA . '
			WHERE media_date = "2000-01-01 01:00:00"
			AND media_uploader = 9999
		' );
		mysql_query( '
			DELETE FROM ' . TABLE_USERS . '
			WHERE user_id = 9999
			AND user_name = "phpunit"
		' );
		mysql_close( $this->dbc );
	}


	public function testGetUploaderName() {
		$m = new ae_Media();
		$m->setUploaderId( 9999 );

		$uname = $m->getUploaderName();
		$this->assertTrue( $uname === 'phpunit' );
	}


	public function testUpdate() {
		$id = ae_Create::LastIdOfMedia();
		$m = ae_Media::GetMediaById( $id );

		$this->assertTrue( ae_Validate::isTimestamp_MySQL( $m->getDate() ) );
		$this->assertTrue( $m->isImage() );

		$m->setType( 'text/plain' );
		$m->update_to_database();

		unset( $m );
		$m = ae_Media::GetMediaById( $id );

		$this->assertTrue( $m->getType() === 'text/plain' );
		$this->assertFalse( $m->isImage() );
	}


	public function testUpdateStatus() {
		$id = ae_Create::LastIdOfMedia();
		$m = ae_Media::GetMediaById( $id );
		$this->assertTrue( $m->update_status( 'trash' ) );

		unset( $m );
		$m = ae_Media::GetMediaById( $id );
		$this->assertTrue( $m->getStatus() === 'trash' );
	}


	public function testDelete() {
		$id = ae_Create::LastIdOfMedia();
		$m = ae_Media::GetMediaById( $id );
		$m->update_status( 'trash' );
		$this->assertTrue( $m->delete() );

		unset( $m );
		$m = ae_Media::GetMediaById( $id );
		$this->assertTrue( is_null( $m ) );
	}


}
