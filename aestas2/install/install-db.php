<?php

$GLOBALS['AESTAS_SQL_QUERIES'] = 0;

define( 'INSTALL', true );
require_once( '../includes/config.php' );
require_once( 'InstallDb.php' );

define( 'SALT', $salt );

set_include_path(
	get_include_path() .
	'.' . PATH_SEPARATOR .
	'..' . PATH_SEPARATOR .
	'./library' . PATH_SEPARATOR .
	'../library' . PATH_SEPARATOR .
	'../../library' . PATH_SEPARATOR .
	'./includes' . PATH_SEPARATOR
);

function __autoload( $class_name ) {
	require( $class_name . '.php' );
}

$db_connect = mysql_connect( $db_host, $db_user, $db_pass );
mysql_select_db( $db_name );
mysql_set_charset( 'utf8', $db_connect );

InstallDb::DefineTables();
InstallDb::CreateTables();

mysql_close( $db_connect );
header( 'Location: ./' );
