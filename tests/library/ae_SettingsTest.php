<?php

require_once( 'defined_tables.php' );


class ae_SettingsTest extends PHPUnit_Framework_TestCase {

	protected $dbc;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
		mysql_query( '
			INSERT INTO ' . TABLE_SETTINGS . ' ( set_name, set_value, set_origin )
			VALUES
				( "phpunittest", "19", "aestas" )
		' );
	}


	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_SETTINGS . '
			WHERE set_name = "phpunittest"
		' );
		mysql_close( $this->dbc );
	}



	public function testLoad() {
		ae_Settings::PreloadSettings();
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == 19 );

		mysql_query( '
			UPDATE ' . TABLE_SETTINGS . '
			SET set_value = "I\'m a bear!"
			WHERE set_name = "phpunittest"
		' );

		unset( $set );
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == '19' );
	}


	public function testLoadCacheBehaviour() {
		ae_Settings::UseCache( false );
		ae_Settings::PreloadSettings();
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == 19 );

		mysql_query( '
			UPDATE ' . TABLE_SETTINGS . '
			SET set_value = "I\'m a bear!"
			WHERE set_name = "phpunittest"
		' );

		unset( $set );
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == 'I\'m a bear!' );

		ae_Settings::UseCache( true );
		mysql_query( '
			UPDATE ' . TABLE_SETTINGS . '
			SET set_value = "20"
			WHERE set_name = "phpunittest"
		' );

		unset( $set );
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == 'I\'m a bear!' );

		ae_Settings::ReloadSettings();
		unset( $set );
		$set = ae_Settings::getSetting( 'phpunittest' );
		$this->assertTrue( $set == 20 );
	}


}
