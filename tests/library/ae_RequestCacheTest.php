<?php

require_once( 'defined_tables.php' );


class ae_RequestCacheTest extends PHPUnit_Framework_TestCase {

	public function setUp() {}

	public function tearDown() {}


	public function testSaveLoad() {
		ae_RequestCache::Save( 'test_üäö', 'öüä' );
		$this->assertTrue( ae_RequestCache::Load( 'test_üäö' ) == 'öüä' );
	}


	public function testDelete() {
		$key = 'test_äöü';
		$key2 = 'test2';

		ae_RequestCache::Save( $key, 128 );
		$this->assertTrue( ae_RequestCache::hasKey( $key ) );
		ae_RequestCache::Delete( $key );
		$this->assertFalse( ae_RequestCache::hasKey( $key ) );

		ae_RequestCache::Save( $key, 128 );
		ae_RequestCache::Save( $key2, 'foo' );
		ae_RequestCache::DeleteAll();
		$this->assertFalse( ae_RequestCache::hasKey( $key ) );
		$this->assertFalse( ae_RequestCache::hasKey( $key2 ) );
	}

}
