<?php

require_once( '../../includes/config.php' );

$show = isset( $_GET['show'] ) ? $_GET['show'] : '';
$show = isset( $_POST['show'] ) ? $_POST['show'] : $show;

if( empty( $show ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage' );
	exit;
}

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', $show );


/* Rare case: Merge categories */

if( isset( $_POST['bulk'] ) && $_POST['bulk'] == 'merge' ) {
	ae_Category::Merge( $_POST['id'] );

	mysql_close( $db_connect );
	header( 'Location:: ../junction.php?area=manage&show=categories' );
	exit;
}


/* Most common case: Change status */

$queue = array();

// Scenario 1: Bulk apply for many.
if( isset( $_POST['bulk'], $_POST['id'] ) ) {
	$status = $_POST['bulk'];
	$queue = $_POST['id'];

	$type = ( $show == 'categories' ) ? 'category' : substr( $show, 0, -1 );
	if( !ae_Validate::isStatus( $status, $type ) ) {
		mysql_close( $db_connect );
		header( 'Location: ../junction.php?area=manage&show=' . $show );
		exit;
	}
}

// Scenario 2: Only a single one shall be changed.
else if( !empty( $_GET ) ) {
	ae_ManageActions::InitStatusAndId( $_GET, $show );
	$queue[] = ae_ManageActions::getId();
	$status = ae_ManageActions::getStatus();
}


if( empty( $queue ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=' . $show );
	exit;
}


// Now process all the IDs in the queue.
foreach( $queue as $id ) {
	$element = null;
	$author_id = $user_id = 0;

	if( $show == 'comments' ) {
		$element = ae_Comment::getCommentById( $id );
		$author_id = $element->getPostAuthorId();
		$user_id = $element->getUserId();
	}
	else if( $show == 'posts' || $show == 'pages' ) {
		$element = ae_Post::LoadById( $id );
		$author_id = $element->getAuthorId();
	}
	else if( $show == 'categories' ) {
		$element = ae_Category::getCategoryById( $id );
		$author_id = $element->getAuthorId();
	}
	else if( $show == 'users' ) {
		$element = ae_User::getUserById( $id );
		$author_id = $element->getId();
	}

	if( ae_Permissions::getRoleOfCurrentUser() == 'guest'
			&& $author_id != ae_Permissions::getIdOfCurrentUser()
			&& $user_id != ae_Permissions::getIdOfCurrentUser() ) {
		continue;
	}

	$element->update_status( $status );
}


mysql_close( $db_connect );
$from = isset( $_GET['from'] ) ? '&status=' . $_GET['from'] : '';
$from = isset( $_POST['from'] ) ? '&status=' . $_POST['from'] : $from;
header( 'Location: ../junction.php?area=manage&show=' . $show . $from );
