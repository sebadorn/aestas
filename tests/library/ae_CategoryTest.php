<?php

require_once( 'defined_tables.php' );

class ae_CategoryTest extends PHPUnit_Framework_TestCase {

	protected $dbc;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
	}


	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_CATEGORIES . '
			WHERE cat_name LIKE "phpunittest%"
		' );
		mysql_close( $this->dbc );
	}


	public function testSaveNew() {
		$cat = new ae_Category();
		$cat->setName( 'phpunittest_1' );
		$cat->setAuthorId( 1 );
		$cat->save_new();
		unset( $cat );
		$id = ae_Create::LastIdOfCategory();
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( $cat->getName() == 'phpunittest_1' );
	}


	public function testUpdatePermalink() {
		$cat = new ae_Category();
		$cat->setName( 'phpunittest_2' );
		$cat->save_new();
		unset( $cat );
		$id = ae_Create::LastIdOfCategory();
		$cat = ae_Category::GetCategoryById( $id );

		$permalink = $cat->generate_permalink();
		$cat->update_permalink();
		unset( $cat );
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( $cat->getPermalink() == $permalink );
	}


	public function testUpdate() {
		$cat = new ae_Category();
		$cat->setName( 'phpunittest_3' );
		$cat->save_new();
		unset( $cat );
		$id = ae_Create::LastIdOfCategory();
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( $cat->getName() == 'phpunittest_3' );

		$cat->setName( 'phpunittest_3b' );
		$cat->update_to_database();
		unset( $cat );
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( $cat->getName() == 'phpunittest_3b' );
	}


	public function testDelete() {
		$cat = new ae_Category();
		$cat->setName( 'phpunittest_4' );
		$cat->setStatus( 'trash' );
		$cat->save_new();
		unset( $cat );
		$id = ae_Create::LastIdOfCategory();
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( $cat->delete() );

		unset( $cat );
		$cat = ae_Category::GetCategoryById( $id );

		$this->assertTrue( is_null( $cat ) );
	}


}
