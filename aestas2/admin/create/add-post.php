<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'create', 'addpost' );


/* Check for missing elements */

if( !isset( $_POST['title'], $_POST['content'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=addpost&error=missing_data' );
	exit;
}


/* Create post object. */

$post = new ae_Post();

$post->setIsPost( true );
$post->setTitle( $_POST['title'] );
$post->setContent( $_POST['content'] );


/* post_date */

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

$post->setPublish( $publish );

$post->setDate( $date );


/* post_expires */

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

$post->setExpires( $expires );

$post->setAuthorId( ae_Permissions::getIdOfCurrentUser() );

$post->setContentPreview( isset( $_POST['exc-prev'] ) );

$post->setNewsfeedPreview( isset( $_POST['exc-news'] ) );

$post->setExcerpt( $_POST['excerpt'] );

$post->setContentType( isset( $_POST['con-php'] ) ? 'php' : 'html' );

$post->setKeywords( $_POST['tags'], isset( $_POST['tags_js'] ) ? $_POST['tags_js'] : array() );

$post->setDescription( $_POST['desc'] );

$post->setCommentsEnabled( !isset( $_POST['disable-comm'] ) );

$post->setPassword( $_POST['protect'] );

$post->setStatus( isset( $_POST['publish'] ) ? 'published' : 'draft' );


/* Save post to database */

$post->save_new();

$post->setId( ae_Create::LastIdOfPost() );


/* post_permalink */

$post->generate_permalink( $_POST['permalink'] );

$post->update_permalink();


/* Look for used media of the library */

ae_Create::FindMediaInPostOrPage( $post );


/* Categories */

$categories = isset( $_POST['cats'] ) ? $_POST['cats'] : array();
ae_Create::CategoryRelations( $categories, $post->getId() );


/* Trackbacks */
// TODO: Trackbacks for future posts
if( ( !empty( $_POST['tracks'] ) || isset( $_POST['tracks_js'] ) )
		&& $post->getStatus() == 'published' && $post->getPublish() == 'immediately' ) {
	$_POST['tracks'] = explode( ' ', $_POST['tracks'] );

	if( isset( $_POST['tracks_js'] ) ) {
		$_POST['tracks'] = array_merge( $_POST['tracks'], $_POST['tracks_js'] );
	}

	$trackback_excerpt = $post->getContentPreview() ? $post->getExcerpt() : $post->getContent();
	$url = 'http://' . preg_replace( '!/admin/create/?$!', '', ae_URL::Blog() ) . '/' . $post->getPermalink();
	$data = ae_Trackback::BuildMessage( $url, $post->getTitle(), $trackback_excerpt );

	ae_Trackback::Send( $_POST['tracks'], $data, $post->getId() );
}


/* Going home */

if( $post->getStatus() == 'draft' ) {
	header( 'Location: ../junction.php?area=manage&show=posts&edit=' . $post->getId() );
}
else {
	header( 'Location: ../junction.php?area=manage&show=posts' );
}

mysql_close( $db_connect );
