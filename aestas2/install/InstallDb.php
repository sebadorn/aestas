<?php

class InstallDb {

	protected static $cache = array();

	public static function CheckPrivileges() {
		if( array_key_exists( 'privileges', self::$cache ) ) {
			return self::$cache['privileges'];
		}
		$error = '';
		$prevs = array( 'SELECT', 'INSERT', 'DELETE', 'UPDATE', 'CREATE', 'ALTER' );

		$grants = mysql_fetch_row(
			mysql_query( 'SHOW GRANTS' )
		);

		if( strstr( $grants[0], 'GRANT ALL PRIVILEGES' ) !== false ) {
			return $error;
		}

		foreach( $prevs as $value ) {
			if( strstr( $grants[0], $value ) !== false ) {
				$error .= '<div class="error">Missing privilege for MySQL user: <code>' . $value . '</code></div>';
			}
		}
		self::$cache['privileges'] = $error;
		return $error;
	}


	public static function DefineTables() {
		global $db_prefix;
		if( !defined( 'TABLE_POSTS' ) ) {
			define( 'TABLE_POSTS',			$db_prefix . 'posts' );
			define( 'TABLE_COMMENTS',		$db_prefix . 'comments' );
			define( 'TABLE_CATEGORIES',		$db_prefix . 'categories' );
			define( 'TABLE_MEDIA',			$db_prefix . 'media_library' );
			define( 'TABLE_RELATIONS',		$db_prefix . 'relations' );
			define( 'TABLE_SETTINGS',		$db_prefix . 'settings' );
			define( 'TABLE_STATS',			$db_prefix . 'stats' );
			define( 'TABLE_IPS',			$db_prefix . 'ips' );
			define( 'TABLE_REFERRER',		$db_prefix . 'referrer' );
			define( 'TABLE_USERS',			$db_prefix . 'users' );
			define( 'TABLE_RULES',			$db_prefix . 'rules' );
			define( 'TABLE_TRACKS_SEND',	$db_prefix . 'trackbacks_send' );
			define( 'TABLE_LINKROLL',		$db_prefix . 'linkroll' );
		}
	}


	public static function CreateTables() {
		$tables['categories'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_CATEGORIES ) . "` (
			  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `cat_name` varchar(255) DEFAULT NULL,
			  `cat_permalink` varchar(255) NOT NULL DEFAULT '',
			  `cat_parent` mediumint(8) unsigned DEFAULT NULL,
			  `cat_author_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
			  `cat_status` enum('active','trash') NOT NULL DEFAULT 'active',
			  PRIMARY KEY (`cat_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['comments'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_COMMENTS ) . "` (
			  `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `comment_ip` tinytext NOT NULL,
			  `comment_user` mediumint(8) unsigned DEFAULT NULL,
			  `comment_author` tinytext,
			  `comment_email` tinytext,
			  `comment_url` tinytext,
			  `comment_date` datetime DEFAULT NULL,
			  `comment_to_type` enum('post','page') NOT NULL DEFAULT 'post',
			  `comment_post_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `comment_parent` bigint(20) unsigned DEFAULT NULL,
			  `comment_content` longtext,
			  `comment_has_type` enum('comment','trackback') NOT NULL DEFAULT 'comment',
			  `comment_status` enum('approved','unapproved','spam','trash') NOT NULL DEFAULT 'approved',
			  PRIMARY KEY (`comment_id`),
			  KEY `post_id_type_status` (`comment_post_id`,`comment_to_type`,`comment_status`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['ips'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_IPS ) . "` (
			  `ip` varchar(20) NOT NULL DEFAULT '',
			  `last_visit` timestamp NULL DEFAULT NULL,
			  PRIMARY KEY (`ip`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['linkroll'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_LINKROLL ) . "` (
			  `roll_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `roll_name` tinytext,
			  `roll_url` text,
			  PRIMARY KEY (`roll_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['media'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_MEDIA ) . "` (
			  `media_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `media_date` datetime DEFAULT '0000-00-00 00:00:00',
			  `media_name` varchar(255) NOT NULL DEFAULT '',
			  `media_description` text,
			  `media_tags` text,
			  `media_type` varchar(60) DEFAULT NULL,
			  `media_dimensions` varchar(80) DEFAULT NULL,
			  `media_uploader` mediumint(8) unsigned NOT NULL DEFAULT '0',
			  `media_status` enum('available','trash') DEFAULT 'available',
			  PRIMARY KEY (`media_id`),
			  KEY `media_name` (`media_name`,`media_type`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['posts'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_POSTS ) . "` (
				`post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`post_author_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
				`post_date` datetime DEFAULT '0000-00-00 00:00:00',
				`post_publish` enum('immediately','scheduled') NOT NULL DEFAULT 'immediately',
				`post_expires` datetime DEFAULT '0000-00-00 00:00:00',
				`post_lastedit` datetime DEFAULT NULL,
				`post_lastedit_by` mediumint(8) unsigned DEFAULT NULL,
				`post_title` text,
				`post_permalink` varchar(250) NOT NULL DEFAULT '',
				`post_content` longtext,
				`post_content_preview` enum('true','false') NOT NULL DEFAULT 'false',
				`post_newsfeed_preview` enum('true','false') NOT NULL DEFAULT 'false',
				`post_excerpt` text,
				`post_type` enum('html','php') NOT NULL DEFAULT 'html',
				`post_keywords` text,
				`post_description` text,
				`post_comment_status` enum('open','closed') NOT NULL DEFAULT 'open',
				`post_pwd` varchar(255) DEFAULT NULL,
				`post_status` enum('published','draft','trash') NOT NULL DEFAULT 'published',
				`post_parent` int(10) unsigned DEFAULT NULL,
				`post_robots` enum('index follow','noindex nofollow') DEFAULT NULL,
				`post_list_page` enum('true','false') DEFAULT NULL,
			  PRIMARY KEY (`post_id`),
			  KEY `post_permalink` (`post_permalink`),
			  KEY `post_status` (`post_status`),
			  KEY `post_date` (`post_date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['referrer'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_REFERRER ) . "` (
			  `referrer_entry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `http_referrer` text NOT NULL,
			  `search_request` text,
			  PRIMARY KEY (`referrer_entry`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['relations'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_RELATIONS ) . "` (
			  `this_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `that_id` int(10) unsigned DEFAULT NULL,
			  `relation_type` enum('post to cat','file to post') NOT NULL DEFAULT 'post to cat',
			  UNIQUE KEY `this_that_id` (`this_id`,`that_id`,`relation_type`),
			  KEY `relation_type` (`relation_type`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['rules'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_RULES ) . "` (
				`rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`rule_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				`rule_concern` varchar(120) NOT NULL,
				`rule_match` varchar(255) NOT NULL,
				`rule_precision` enum('contains','exact','regex') NOT NULL DEFAULT 'contains',
				`rule_result` varchar(120) NOT NULL,
				`rule_status` enum('active','inactive') NOT NULL DEFAULT 'active',
				PRIMARY KEY (`rule_id`),
				KEY `rule_type` (`rule_result`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		" );

		$tables['settings'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_SETTINGS ) . "` (
			  `set_name` varchar(40) NOT NULL DEFAULT '',
			  `set_value` text,
			  `set_origin` enum('aestas','wp_options') NOT NULL DEFAULT 'aestas',
			  KEY `set_name` (`set_name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['stats'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_STATS ) . "` (
			  `stat_name` varchar(26) NOT NULL DEFAULT '',
			  `stat_value` int(10) unsigned DEFAULT NULL,
			  PRIMARY KEY (`stat_name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['trackback'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_TRACKS_SEND ) . "` (
			  `trackback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `trackback_type` enum('post','page') NOT NULL DEFAULT 'post',
			  `trackback_from_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `trackback_to_url` text NOT NULL,
			  `trackback_error` text,
			  PRIMARY KEY (`trackback_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );

		$tables['users'] = mysql_query( "
			CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( TABLE_USERS ) . "` (
			  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `user_name_login` tinytext NOT NULL,
			  `user_name` tinytext NOT NULL,
			  `user_permalink` tinytext,
			  `user_role` enum('admin','author','guest') NOT NULL DEFAULT 'guest',
			  `user_pwd` tinytext,
			  `user_email` tinytext,
			  `user_url` tinytext,
			  `user_editor` enum('code','ckeditor') NOT NULL DEFAULT 'code',
			  `user_status` enum('active','suspended','trash','deleted') NOT NULL DEFAULT 'active',
			  PRIMARY KEY (`user_id`),
			  KEY `user_role` (`user_role`,`user_status`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		" );
	}


	public static function InsertContent() {
		$content['stats'] = mysql_query( "
			INSERT INTO `" . mysql_real_escape_string( TABLE_STATS ) . "` (`stat_name`, `stat_value`) VALUES
				('all', 0),
				('honeypot', 0)
		" );

		$cat = new ae_Category();
		$cat->setName( 'uncategorized' );
		$cat->setStatus( 'active' );
		$cat->setAuthorId( self::$cache['userid'] );
		$cat->save_new();
		$cat->setId( 1 );
		$cat->generate_permalink();
		$cat->update_permalink();

		$post = new ae_Post();
		$post->setIsPost( true );
		$post->setAuthorId( self::$cache['userid'] );
		$post->setTitle( 'First Post' );
		$post->setContent( '<p>This is a post!</p>' );
		$post->setStatus( 'published' );
		$post->setPublish( 'immediately' );
		$post->setCommentsEnabled( true );
		$post->save_new();
		$post->setId( 1 );
		$post->generate_permalink();
		$post->update_permalink();

		$page = new ae_Post();
		$page->setIsPage( true );
		$page->setAuthorId( self::$cache['userid'] );
		$page->setTitle( 'Some page' );
		$page->setContent( '<p>This is a page!</p>' );
		$page->setStatus( 'published' );
		$page->setCommentsEnabled( true );
		$page->setRobots( 'index follow' );
		$page->save_new();
		$page->setId( 2 );
		$page->generate_permalink();
		$page->update_permalink();

		ae_Create::CategoryRelations( array( 1 ), $post->getId() );
	}


	public static function InsertSettings() {
		$settings = mysql_fetch_object(
				mysql_query( '
					SELECT COUNT( set_name ) AS count
					FROM ' . mysql_real_escape_string( TABLE_SETTINGS )
				)
		);
		if( $settings->count > 0 ) {
			return false;
		}

		$content['settings'] = mysql_query( "
			INSERT INTO `" . mysql_real_escape_string( TABLE_SETTINGS ) . "` (`set_name`, `set_value`, `set_origin`) VALUES
				('bloginfo_tagline', 'It sure is!', 'aestas'),
				('bloginfo_title', 'It\'s a blog', 'aestas'),
				('blog_comment_limit', '0', 'aestas'),
				('blog_front', 'posts', 'aestas'),
				('blog_post_limit', '6', 'aestas'),
				('blog_theme', 'default', 'aestas'),
				('blog_theme_system', 'wordpress', 'aestas'),
				('comments', 'true', 'aestas'),
				('comments_moderate', 'false', 'aestas'),
				('gravatar', 'true', 'aestas'),
				('gravatar_default', 'mm', 'aestas'),
				('gravatar_rating', 'g', 'aestas'),
				('ignore_agents', 'a:26:{i:0;s:6:\"google\";i:1;s:5:\"yahoo\";i:2;s:5:\"slurp\";i:3;s:9:\"altavista\";i:4;s:5:\"crawl\";i:5;s:13:\"mediapartners\";i:6;s:7:\"inktomi\";i:7;s:11:\"ia_archiver\";i:8;s:4:\"snap\";i:9;s:9:\"bloglines\";i:10;s:3:\"bot\";i:11;s:4:\"news\";i:12;s:8:\"netvibes\";i:13;s:5:\"fetch\";i:14;s:7:\"bloggsi\";i:15;s:4:\"feed\";i:16;s:9:\"microsoft\";i:17;s:6:\"search\";i:18;s:4:\"bing\";i:19;s:6:\"spider\";i:20;s:4:\"walk\";i:21;s:4:\"cuil\";i:22;s:5:\"tagoo\";i:23;s:6:\"pawler\";i:24;s:4:\"find\";i:25;s:3:\"ask\";}', 'aestas'),
				('media_imagepreview', 'true', 'aestas'),
				('newsfeed_alternate', '', 'aestas'),
				('newsfeed_content', 'default', 'aestas'),
				('newsfeed_limit', '14', 'aestas'),
				('permalink_structure_archive', 'archiv/%year%/%month%/%day%', 'aestas'),
				('permalink_structure_author', 'author/%authorname%', 'aestas'),
				('permalink_structure_blog', 'seite/%pagenumber%', 'aestas'),
				('permalink_structure_cat', 'category/%catname%', 'aestas'),
				('permalink_structure_page', '%pagename%', 'aestas'),
				('permalink_structure_post', '%year%/%month%/%day%/%postname%', 'aestas'),
				('permalink_structure_tag', 'stichwort/%tagname%', 'aestas'),
				('pings', 'true', 'aestas'),
				('timezone', 'Europe/Berlin', 'aestas'),
				('version', '2.0.0', 'aestas'),
				('auth_system', 'session', 'aestas')
		" );
	}


	public static function CreateUser( $name, $pwd ) {
		$name = empty( $name ) ? 'admin' : $name;
		$name = mysql_real_escape_string( $name );

		$user = new ae_User();
		$user->setName( $name );
		$user->setNameInternal( $name );
		$user->setPassword( $pwd );
		$user->setRole( 'admin' );
		$user->setStatus( 'active' );
		$user->setEditor( 'ckeditor' );
		$user->setId( rand( 100, 1000 ) );
		self::$cache['userid'] = $user->getId();
		$user->generate_permalink();

		return $user->save_new( true );
	}


}
