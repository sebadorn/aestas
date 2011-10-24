<?php

require_once( 'defined_tables.php' );


class ae_SearchTest extends PHPUnit_Framework_TestCase {


	public function setUp() {}


	public function tearDown() {}


	public function testSearchDate() {
		$should_be = '&date=' . urlencode( '2010-01-01' );
		$search = new ae_Search( 'date 2010-01-01' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchDateIntervall() {
		$should_be = '&date_from=' . urlencode( '2010-02-03' ) . '&date_till=' . urlencode( '2010-03-03' );
		$search = new ae_Search( 'from 2010-02-03 till 2010-03-03' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchDigits() {
		$should_be = '&to_post=999';
		$search = new ae_Search( 'post 999' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );

		$should_be = '&with_media=2';
		$search = new ae_Search( 'media 2' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchStatus() {
		$should_be = '&status=trash';
		$search = new ae_Search( 'status trash' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchIp() {
		$should_be = '&ip=' . urlencode( '87.34.5.99' );
		$search = new ae_Search( 'ip 87.34.5.99' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchContains() {
		$should_be = '&contains=' . urlencode( 'phpunittest 999' );
		$search = new ae_Search( 'phpunittest 999' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchAuthor() {
		$should_be = '&author=' . urlencode( 'foo bar' );
		$search = new ae_Search( 'author "foo bar"' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchMail() {
		$should_be = '&email=' . urlencode( 'someone@example.com' );
		$search = new ae_Search( 'email someone@example.com' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchUrl() {
		$should_be = '&url=' . urlencode( 'sebadorn.de' );
		$search = new ae_Search( 'url sebadorn.de' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


	public function testSearchRole() {
		$should_be = '&role=' . urlencode( 'author' );
		$search = new ae_Search( 'role author' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}



	public function testSearchTag() {
		$should_be = '&tag=' . urlencode( 'php & mysql' );
		$search = new ae_Search( 'tag "php & mysql"' );
		$search->process_phrase();
		$this->assertTrue( $search->get_params_as_url() === $should_be );
	}


}
