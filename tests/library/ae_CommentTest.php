<?php

require_once( 'defined_tables.php' );


class ae_CommentTest extends PHPUnit_Framework_TestCase {

	protected $dbc;


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
	}


	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_COMMENTS . '
			WHERE comment_user = 999
		' );
		mysql_close( $this->dbc );
	}


	public function testSaveNew() {
		$comment = new ae_Comment();
		$comment->setAuthor( 'phpunit' );
		$comment->setUserId( 999 );
		$comment->setContent( 'lorem ipsum dolor sit amet' );
		$comment->setEmail( 'foobar@example.com' );
		$comment->setUrl( 'example.com' );
		$comment->setPostId( 1 );
		$comment->setHasType( 'comment' );
		$comment->setDate();

		$date = date( 'Y-m-d H:i:s' );
		$this->assertTrue( $comment->getDate() == $date );
		$date = '2000-01-01 01:00:00';
		$comment->setDate( $date );
		$this->assertTrue( $comment->getDate() == $date );
		$this->assertTrue( $comment->getUrl() == 'http://example.com' );

		$comment->save_new();

		unset( $comment );
		$id = ae_Create::LastIdOfComment();
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( $comment->getAuthor() == 'phpunit' );
		$this->assertTrue( $comment->getDate() == $date );
		$this->assertTrue( $comment->getIp() == $_SERVER['REMOTE_ADDR'] );
	}


	public function testUpdate() {
		$comment = new ae_Comment();
		$comment->setUserId( 999 );
		$comment->setContent( 'lorem ipsum dolor sit amet' );
		$comment->setPostId( 1 );
		$comment->save_new();

		unset( $comment );
		$id = ae_Create::LastIdOfComment();
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( ae_Validate::isTimestamp_MySQL( $comment->getDate() ) );
		$this->assertTrue( $comment->getIp() == $_SERVER['REMOTE_ADDR'] );

		$comment->setAuthor( 'phpunit_2' );
		$comment->update_to_database();

		unset( $comment );
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( $comment->getAuthor() == 'phpunit_2' );

		$comment->update_status( 'spam' );

		unset( $comment );
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( $comment->getStatus() == 'spam' );
	}


	public function testDelete() {
		$comment = new ae_Comment();
		$comment->setUserId( 999 );
		$comment->setContent( 'lorem ipsum dolor sit amet' );
		$comment->setPostId( 1 );
		$comment->setStatus( 'trash' );
		$comment->save_new();

		unset( $comment );
		$id = ae_Create::LastIdOfComment();
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( $comment->delete() );

		unset( $comment );
		$comment = ae_Comment::GetCommentById( $id );

		$this->assertTrue( is_null( $comment ) );
	}


}
