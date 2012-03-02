<?php

if( !defined( 'INSTALL' ) || defined( 'INSTALL_STEP_2' ) ) {


	/* Defining the SALT */
	define( 'SALT', $salt );
	unset( $salt );


	/* Defining HASH_ROUNDS */
	if( $round_hashing < 1 ) {
		$round_hashing = 4000;
	}
	define( 'HASH_ROUNDS', $round_hashing );
	unset( $round_hashing );


	/* Defining the LANG */
	define( 'LANG', $lang );
	unset( $lang );


	/* Set include paths */
	set_include_path(
		'.' . PATH_SEPARATOR .
		'..' . PATH_SEPARATOR .
		'./library' . PATH_SEPARATOR .
		'../library' . PATH_SEPARATOR .
		'../../library' . PATH_SEPARATOR .
		get_include_path()
	);


	/* Autoloader for classes in library. */

	function autoload( $class_name ) {
		require( $class_name . '.php' );
	}

	spl_autoload_register( 'autoload' );


	/* Making contact */

	$db_connected = ae_Database::Connect( $db_host, $db_user, $db_pass, 'utf8' );

	if( !$db_connected ) { // If connection failed
		echo ae_ErrorMessages::MySQLConnectFail();
		exit;
	}

	if( !ae_Database::SelectDatabase( $db_name ) ) { // If specific database is not available
		ae_Database::Close( $db_connect );
		echo ae_ErrorMessages::MySQLDbNameFail();
		exit;
	}

	unset( $db_name );
	unset( $db_user );
	unset( $db_pass );
	unset( $db_host );


	/* Tablenames */

	// Shorts for tablenames
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


	/* Check if tables are reachable */

	try {
		ae_Database::Query( 'SELECT cat_id FROM `' . TABLE_CATEGORIES . '` LIMIT 1' );
	}
	catch( Exception $e ) {
		ae_Database::Close();
		echo $e;
		echo ae_ErrorMessages::MySQLTableFail();
		exit;
	}

	ae_Database::incQueryCount();


	/* Check for excessively long request strings and bad content
	 *
	 * Source: http://perishablepress.com/press/2009/12/22/protect-wordpress-against-malicious-url-requests/
	 */

	if( strlen( $_SERVER['REQUEST_URI'] ) > 255 ||
			stripos( $_SERVER['REQUEST_URI'], 'eval(' ) ||
			stripos( $_SERVER['REQUEST_URI'], 'CONCAT' ) ||
			stripos( $_SERVER['REQUEST_URI'], 'UNION+SELECT' ) ||
			stripos( $_SERVER['REQUEST_URI'], 'base64' ) ) {
		@header( 'HTTP/1.1 414 Request-URI Too Long' );
		@header( 'Status: 414 Request-URI Too Long' );
		@header( 'Connection: Close' );
		@exit;
	}


	/* Time settings for scripts
	 *
	 * This is solely for PHP functions as date() or mktime().
	 * For different timezones look here: http://www.php.net/manual/en/timezones.php
	 */

	ae_Settings::PreloadSettings();

	$timezone = ae_Settings::Timezone();
	$timezone = empty( $timezone ) ? 'Europe/Berlin' : $timezone;

	if( function_exists( 'date_default_timezone_set' ) ) {
		date_default_timezone_set( $timezone );
	}
	else {
		ini_set( 'date.timezone', $timezone );
	}

	mysql_query( 'SET time_zone = "' . mysql_real_escape_string( $timezone ) . '"' );
	ae_Database::incQueryCount();

	unset( $timezone );


	/* "Disabling" Magic Quotes
	 *
	 * Undo changes done by Magic Quotes
	 */

	if( get_magic_quotes_gpc() ) {

		function stripslashes_deep( $value ) {
			$value = is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );
			return $value;
		}

		$_POST = array_map( 'stripslashes_deep', $_POST );
		$_GET = array_map( 'stripslashes_deep', $_GET );
		$_COOKIE = array_map( 'stripslashes_deep', $_COOKIE );
		$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
	}


	/* Cutting off slash at the end of request uri
	 * Simplifies the analysis of permalinks
	 */

	if( substr( $_SERVER['REQUEST_URI'], -1 ) == '/' ) {
		$_SERVER['REQUEST_URI'] = substr( $_SERVER['REQUEST_URI'], 0, -1 );
	}

}
