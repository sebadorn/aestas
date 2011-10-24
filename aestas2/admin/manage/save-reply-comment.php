<?php

require( '../../includes/config.php' );


/* Check for call of the script by someone not logged in or with not enough rights */

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'comments' );


/* Check for missing elements */

if( !isset( $_POST['reply_to_id'] )
		|| ( isset( $_POST['reply_to_id'] ) && !ae_Validate::isDigit( $_POST['reply_to_id'] ) ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=comments' );
	exit;
}

$comment = new ae_Comment();
$replying_to = ae_Comment::getCommentById( $_POST['reply_to_id'] );

$comment->setParentId( $_POST['reply_to_id'] );

$comment->setPostId( $replying_to->getPostId() );

$comment->setAuthor( $_POST['name'] );

$comment->setUserId( ae_Permissions::getIdOfCurrentUser() );

$comment->setEmail( $_POST['email'] );

$comment->setUrl( $_POST['website'] );

$comment->setContent( $_POST['content'] );
$comment->contentNl2Br();

$comment->setDate();

$comment->setStatus( 'approved' );


/* Save to database */

$comment->save_new();


mysql_close( $db_connect );

header( 'Location: ../junction.php?area=manage&show=comments' );
