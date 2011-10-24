<?php

require_once( 'defined_tables.php' );


class ae_RulesTest extends PHPUnit_Framework_TestCase {

	protected $dbc;
	protected $comment = array(
		'name' => 'Claudette',
		'mail' => 'foobar@example.com',
		'url' => 'claudettchen.somedomain.net',
		'message' => 'Dating powellville. Dating guadalupe. Agency dating london online service.
Black and asian dating sites.   <a href=http://ru.jimdo.com/?33>юмористический проект</a>. Dating turnersville.
West indian dating. Dating leechburg. Dating gapland.',
		'ip' => '95.27.6.224'
	);


	public function setUp() {
		$this->dbc = mysql_connect( DB_HOST, DB_USER, DB_PASS );
		mysql_select_db( DB_NAME );
		mysql_set_charset( 'utf8' );
		mysql_query( '
			INSERT INTO ' . TABLE_RULES . ' ( rule_concern, rule_added, rule_match, rule_precision, rule_result, rule_status )
			VALUES
			( "comment_email", "2000-01-01 01:00:00", "foobar@example.com", "exact", "comment;status;approved", "active" ),
			( "comment_url", "2000-01-01 01:00:00", "/[a-z]+\.somedomain\.net/", "regex", "comment;status;approved", "active" ),
			( "comment_ip", "2000-01-01 01:00:00", "' . $this->comment['ip'] . '", "exact", "comment;status;spam", "active" ),
			( "comment_content", "2000-01-01 01:00:00", "проект", "contains", "comment;status;spam", "active" )
		' );
	}

	public function tearDown() {
		mysql_query( '
			DELETE FROM ' . TABLE_RULES . '
			WHERE rule_added = "2000-01-01 01:00:00"
		' );
		mysql_close( $this->dbc );
	}


	public function testWhiteMailExact() {
		$whitelisted = ae_Rules::Check( null, $this->comment['mail'], null, null, null );
		$this->assertTrue( $whitelisted[0] == 'comment;status;approved' );
	}


	public function testWhiteUrlRegex() {
		$whitelisted = ae_Rules::Check( null, null, $this->comment['url'], null, null );
		$this->assertTrue( $whitelisted[0] == 'comment;status;approved' );
	}


	public function testBlackIpExact() {
		$blacklisted = ae_Rules::Check( null, null, null, null, $this->comment['ip'] );
		$this->assertTrue( $blacklisted[0] == 'comment;status;spam' );
	}


	public function testBlackContentContains() {
		$blacklisted = ae_Rules::Check( null, null, null, $this->comment['message'], null );
		$this->assertTrue( $blacklisted[0] == 'comment;status;spam' );
	}


}
