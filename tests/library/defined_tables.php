<?php

define( 'SALT', ')23:ru8-19{4%89!j+#j84weq2' );
define( 'LANG', 'de' );

$db_prefix = 'ae_';
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
unset( $db_prefix );

define( 'DB_HOST', '127.0.0.1' );
define( 'DB_USER', 'sebadosebadorn' );
define( 'DB_PASS', 'xhtml10strict' );
define( 'DB_NAME', 'aestas2' );

define( 'HASH_ROUNDS', 4000 );
define( 'URL', 'http://localhost/' );


$_SERVER['REMOTE_ADDR'] = '99.99.99.99';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

set_include_path(
	get_include_path() .
	'.' . PATH_SEPARATOR .
	'..' . PATH_SEPARATOR .
	'./library' . PATH_SEPARATOR .
	'../library' . PATH_SEPARATOR .
	'../../library' . PATH_SEPARATOR .
	'./aestas2/library' . PATH_SEPARATOR .
	'../aestas2/library' . PATH_SEPARATOR .
	'../../aestas2/library' . PATH_SEPARATOR
);

function my_autoload( $class ) {
	require_once( $class . '.php' );
}

spl_autoload_register( 'my_autoload' );