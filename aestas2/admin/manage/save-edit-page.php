<?php

require( '../../includes/config.php' );


/* Check for call of the script by someone not logged in or with not enough rights */

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'pages' );


/* Check for missing elements */

if( !isset( $_POST['page_id'] ) || !ae_Validate::isDigit( $_POST['page_id'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=pages' );
	exit;
}


$page = ae_Post::LoadById( $_POST['page_id'] );

$page->setTitle( $_POST['title'] );

$page->setContent( $_POST['content'] );


$publish = 'immediately';

if( $_POST['date'] == 'set' ) {
	$publish = 'scheduled';

	$date_array = array(
		'year' => $_POST['year'],
		'month' => $_POST['month'],
		'day' => $_POST['day'],
		'hour' => $_POST['hour'],
		'minute' => $_POST['minute']
	);

	$page->setDate( ae_Create::Date( $date_array ) );
}
else if( $page->getDate() > date('Y-m-d H:i:s') ) {
	$page->setDate( date('Y-m-d H:i:s') );
}

$page->setPublish( $publish );


if( isset( $_POST['expires'] ) ) {
	$exp_array = array(
		'year' => $_POST['expires_year'],
		'month' => $_POST['expires_month'],
		'day' => $_POST['expires_day'],
		'hour' => $_POST['expires_hour'],
		'minute' => $_POST['expires_minute']
	);

	$expires = ae_Create::DateExpires( $exp_array );
}
else {
	$expires = null;
}

$page->setExpires( $expires );


$page->generate_permalink( $_POST['permalink'] );

$page->setContentType( isset($_POST['con-php']) ? 'php' : 'html' );

$page->setKeywords( $_POST['tags'], isset( $_POST['tags_js'] ) ? $_POST['tags_js'] : array() );

$page->setDescription( $_POST['desc'] );

$page->setCommentsEnabled( !isset( $_POST['disable-comm'] ) );

$page->setPassword( $_POST['protect'] );

$page->setShowInList( isset( $_POST['list-nav'] ) );

$page->setParent( $_POST['parent'] );

$page->setStatus( isset( $_POST['publish'] ) ? 'published' : 'draft' );

$page->setLastEditBy( ae_Permissions::getIdOfCurrentUser() );


/* Save page to database */

$page->update_to_database();


/* Look for used media of the library */

ae_Create::FindMediaInPostOrPage( $page );


/* Trackbacks */

if( ( !empty( $_POST['tracks'] ) || isset( $_POST['tracks_js'] ) )
		&& $page->getStatus() == 'published' && $page->getPublish() == 'immediately' ) {
	$_POST['tracks'] = explode( ' ', $_POST['tracks'] );

	if( isset( $_POST['tracks_js'] ) ) {
		$_POST['tracks'] = array_merge( $_POST['tracks'], $_POST['tracks_js'] );
	}

	$trackback_excerpt = $page->getContentPreview() ? $page->getExcerpt() : $page->getContent();
	$url = 'http://' . preg_replace( '!/admin/create/?$!', '', ae_URL::Blog() ) . '/' . $page->getPermalink();
	$data = ae_Trackback::BuildMessage( $url, $page->getTitle(), $trackback_excerpt );

	ae_Trackback::Send( $_POST['tracks'], $data, $page->getId() );
}


/* Going home */

if( $page->getStatus() == 'draft' ) {
	header( 'Location: ../junction.php?area=manage&show=pages&edit=' . $page->getId() );
}
else {
	header( 'Location: ../junction.php?area=manage&show=pages' );
}

mysql_close( $db_connect );
