<?php

if( !isset( $_POST['username'], $_POST['pwd'], $_POST['pwd-confirm'] ) ) {
	header( 'Location: ./?installcontent=missing' );
	exit;
}
else if( empty( $_POST['pwd'] ) ) {
	header( 'Location: ./?installcontent=pwdempty' );
	exit;
}
else if( $_POST['pwd'] != $_POST['pwd-confirm'] ) {
	header( 'Location: ./?installcontent=pwdconfirm' );
	exit;
}

$GLOBALS['AESTAS_SQL_QUERIES'] = 0;

define( 'INSTALL', true );
require_once( '../includes/config.php' );
require_once( 'InstallDb.php' );

define( 'SALT', $salt );

if( $round_hashing < 1 ) {
	$round_hashing = 4000;
}
define( 'HASH_ROUNDS', $round_hashing );
unset( $round_hashing );

set_include_path(
	'.' . PATH_SEPARATOR .
	'..' . PATH_SEPARATOR .
	'./library' . PATH_SEPARATOR .
	'../library' . PATH_SEPARATOR .
	'../../library' . PATH_SEPARATOR .
	get_include_path()
);

function __autoload( $class_name ) {
	require( $class_name . '.php' );
}

$db_connect = mysql_connect( $db_host, $db_user, $db_pass );
mysql_select_db( $db_name );
mysql_set_charset( 'utf8', $db_connect );

InstallDb::DefineTables();
InstallDb::InsertSettings();
InstallDb::CreateUser( $_POST['username'], $_POST['pwd-confirm'] );
InstallDb::InsertContent();

mysql_close( $db_connect );
header( 'Location: ./' );
