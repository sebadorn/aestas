<?php

require_once( 'defined_tables.php' );


class ae_UserTest extends PHPUnit_Framework_TestCase {

	protected $dbc;
	protected $delete_id = 0;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
	}


	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_USERS . '
			WHERE user_name_login LIKE "phpunittest%"
			OR user_id = ' . mysql_real_escape_string( $this->delete_id )
		);
		mysql_close( $this->dbc );
	}


	public function testSaveNew() {
		$user = new ae_User();
		$user->setName( '<put>' );
		$user->setNameInternal( 'phpunittest_1' );
		$user->setPassword( 'phpunittest' );
		$user->save_new();
		unset( $user );
		$id = ae_Create::LastIdOfUser();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->getNameInternal() == 'phpunittest_1' );
		$this->assertTrue( $user->getName() == '<put>' );
		$this->assertTrue( $user->getNameHtml() == '&lt;put&gt;' );
	}


	public function testUpdatePermalink() {
		$user = new ae_User();
		$user->setNameInternal( 'phpunittest_2' );
		$user->setPassword( 'phpunittest' );
		$user->save_new();
		unset( $user );
		$id = ae_Create::LastIdOfUser();
		$user = ae_User::getUserById( $id );

		$permalink = $user->generate_permalink();
		$user->update_permalink();
		unset( $user );
		ae_User::ClearCache();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->getPermalink() == $permalink );
	}


	public function testUpdate() {
		$user = new ae_User();
		$user->setNameInternal( 'phpunittest_3' );
		$user->setPassword( 'phpunittest' );
		$user->save_new();
		unset( $user );
		$id = ae_Create::LastIdOfUser();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->getNameInternal() == 'phpunittest_3' );

		$user->setNameInternal( 'phpunittest_3b' );
		$user->update_to_database();
		unset( $user );
		ae_User::ClearCache();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->getNameInternal() == 'phpunittest_3b' );
	}


	public function testDelete() {
		$user = new ae_User();
		$user->setNameInternal( 'phpunittest_4' );
		$user->setPassword( 'phpunittest' );
		$user->setStatus( 'active' );
		$user->save_new();
		unset( $user );
		$this->delete_id = $id = ae_Create::LastIdOfUser();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->update_status( 'trash' ) );
		$this->assertTrue( $user->delete() );

		unset( $user );
		ae_User::ClearCache();
		$user = ae_User::getUserById( $id );

		$this->assertTrue( $user->getStatus() === 'deleted' );
	}


}
