<?php

require( '../../includes/config.php' );


/* Check for call of the script by someone not logged in or with not enough rights */

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'comments' );


/* Check for missing elements */

if( !isset( $_POST['comment_id']) && !ae_Validate::isDigit( $_POST['comment_id'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=comments' );
	exit;
}

$comment = ae_Comment::getCommentById( $_POST['comment_id'] );


if( ae_Permissions::getRoleOfCurrentUser() == 'guest'
		&& $comment->getPostAuthorId() != ae_Permissions::getIdOfCurrentUser()
		&& $comment->getUserId() != ae_Permissions::getIdOfCurrentUser() ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=comments&error=notauthor' );
	exit;
}


$comment->setAuthor( $_POST['name'] );

$comment->setEmail( $_POST['email'] );

$comment->setUrl( $_POST['website'] );

$comment->setContent( $_POST['content'] );
$comment->contentNl2Br();


if( isset( $_POST['change_date'] ) ) {
	$date_array = array(
		'year' => $_POST['year'],
		'month' => $_POST['month'],
		'day' => $_POST['day'],
		'hour' => $_POST['hour'],
		'minute' => $_POST['minute']
	);

	$comment->setDate( ae_Create::Date( $date_array ) );
}

$comment->setStatus( $_POST['status'] );

$comment->setUserId( isset( $_POST['user'] ) ? $_POST['userid'] : 0 );


/* Update changes to database */

$outcome = $comment->update_to_database() ? '&success' : '&error';


mysql_close( $db_connect );

header( 'Location: ../junction.php?area=manage&show=comments' . $outcome );
