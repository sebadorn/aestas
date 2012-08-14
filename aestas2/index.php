<?php

// Timer to use in function timer_stop
$timer = microtime( true );

header( 'content-type: text/html; charset=utf-8' );

require_once( 'includes/config.php' );

ae_Permissions::InitRoleAndStatus( 'blog' );
ae_Statistics::PreloadStatistics();


// Constants
define( 'SINGLE_POST',	ae_URL::SinglePost()			);
define( 'PAGE_ID',		ae_URL::PageId()				);
define( 'AUTHOR',		ae_URL::Author()				);
define( 'CATEGORY',		ae_URL::Category()				);
define( 'FEED',			false							);
define( 'PAGE',			ae_URL::BlogPage()				);
define( 'SEARCH',		ae_URL::Search()				);
define( 'TAG',			ae_URL::Tag()					);
define( 'PROTOCOL',		ae_URL::Protocol()				);
define( 'URL',			PROTOCOL . ae_URL::Blog()		);
define( 'URL_EXTENDED',	PROTOCOL . ae_URL::Complete()	);

$code = 200;
$current_path = preg_replace( '/\/index.php$/', '', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] );
if( ae_URL::Blog() != $current_path
		&& !ae_URL::isPage() && !ae_URL::isPost() && SEARCH === false ) {
	$code = 404;
}
define( 'CODE', $code );

unset( $current_path );
unset( $code );


// Receiving trackbacks
if( ( ae_URL::isPage() || ae_URL::isPost() ) && ae_Trackback::Detect() ) {
	$to = ae_URL::isPost() ? 'post' : 'page';
	ae_Trackback::Receive( $to );
	mysql_close( $db_connect );
	exit;
}


ae_Statistics::CountVisitors();
ae_Statistics::Referrer();


// Get theme

if( ae_Permissions::isLoggedIn() && ae_Cookies::isInThemePreview() ) {
	$theme_system = ae_Cookies::ThemePreviewGetThemeAndSystem();
}
else {
	$settings_ts = ae_Settings::Theme();
	$theme_system['system'] = $settings_ts['blog_theme_system'];
	$theme_system['theme'] = $settings_ts['blog_theme'];
	unset( $settings_ts );
}

define( 'SYSTEM', $theme_system['system'] );
define( 'THEME', $theme_system['theme'] );
define( 'TEMPLATEPATH', PROTOCOL . ae_URL::Blog() . '/themes/' . THEME );

unset( $theme_system );


// Template Engine of WordPress
if( SYSTEM == 'wordpress' ) {

	// All functions
	$include = array(
		'build_basics', 'action-filter-plugin', 'conditional-tags',
		'formatting', 'miscellaneous', 'post-page-attachment',
		'author-user', 'sidebar-widgets', 'post-page-gateway-functions',
		'xmlrpc', 'options', 'localization',
		'time-date', 'cron', 'template', 'comment-gateway-functions'
	);
	foreach( $include as $inc ) {
		include_once( 'includes/cl_wp/' . $inc . '.php' );
	}
	unset( $include );


	// For protected posts look for a submitted password
	if( isset( $_POST['postpwd'] ) ) {
		ae_Cookies::SetPostOrPagePwdCookie();
	}

	// Init PostQuery (also used for pages)
	$post = null; // Needs to be accessed by some WordPress themes.
	$wp_query = new ae_PostQuery( PAGE_ID > 0 ? 'page' : 'post' );
	ae_EngineGateway::Init( $wp_query );

	// Comment query is initialized if needed.

	// For WP theme compatibility
	$authordata = null;
	$user_ID = ae_Permissions::getIdOfCurrentUser();
	$user_ID = ( $user_ID ) > 0 ? $user_ID : false;

	if( $user_ID !== false ) {
		$user = ae_User::getUserById( $user_ID );
		$user_identity = $user->getName();
		unset( $user );
	}


	if( !defined( 'CODE_POSTQUERY' ) ) {
		define( 'CODE_POSTQUERY', 200 );
	}
	if( !defined( 'CODE_PAGEQUERY' ) ) {
		define( 'CODE_PAGEQUERY', 200 );
	}

	// Functions of the current theme
	if( file_exists( 'themes/' . THEME . '/functions.php' ) ) {
		include( 'themes/' . THEME . '/functions.php' );
	}

	if( CODE == 404 || CODE_POSTQUERY == 404 || CODE_PAGEQUERY == 404 ) {
		header( 'HTTP/1.0 404 Not Found' );
		// TODO: If theme doesn't have a 404 file, use a default one.
		include( 'themes/' . THEME . '/404.php' );
	}
	else if( SINGLE_POST > 0 ) {
		include( 'themes/' . THEME . '/single.php' );
	}
	else if( PAGE_ID > 0 ) {
		include( 'themes/' . THEME . '/page.php' );
	}
	else if( SEARCH !== false ) {
		include( 'themes/' . THEME . '/search.php' );
	}
	else {
		include( 'themes/' . THEME . '/index.php' );
	}
}

// Unknown Template Engine
else {
	echo 'Theme uses an unknown template engine.';
}


ae_Database::Close();
