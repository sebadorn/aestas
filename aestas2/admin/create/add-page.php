<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'create', 'addpage' );


/* Check for missing elements */

if( !isset( $_POST['title'], $_POST['content'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=addpage&error=missing_data' );
	exit;
}


/* Create page object. */

$page = new ae_Post();
$page->setIsPage( true );
$page->setTitle( $_POST['title'] );
$page->setContent( $_POST['content'] );


/* page_date */

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
	$date = ae_Create::Date( $date_array );
}
else {
	$date = date( 'Y-m-d H:i:s' );
}

$page->setPublish( $publish );

$page->setDate( $date );


/* page_expires */

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


$post->setAuthorId( ae_Permissions::getIdOfCurrentUser() );

$page->setRobots( isset( $_POST['se-en'] ) ? 'noindex nofollow' : 'index follow' );

$page->setParent( $_POST['parent'] );

$page->setContentType( isset( $_POST['con-php'] ) ? 'php' : 'html' );

$page->setKeywords( $_POST['tags'], isset( $_POST['tags_js'] ) ? $_POST['tags_js'] : array() );

$page->setDescription( $_POST['desc'] );

$page->setCommentsEnabled( !isset( $_POST['disable-comm'] ) );

$page->setPassword( $_POST['protect'] );

$page->setShowInList( isset( $_POST['list-nav'] ) );

$page->setStatus( isset( $_POST['publish'] ) ? 'published' : 'draft' );


/* Save post to database */

$page->save_new();

$page->setId( ae_Create::LastIdOfPage() );


/* Permalink */

$page->generate_permalink( $_POST['permalink'] );

$page->update_permalink();


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
