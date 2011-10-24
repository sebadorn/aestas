<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'media', 'library' );


$queue = array();

// Scenario 1: Bulk apply for many.
if( isset( $_POST['bulk'], $_POST['id'] ) ) {
	$status = $_POST['bulk'];
	$queue = $_POST['id'];

	if( !ae_Validate::isMediaStatus( $status ) ) {
		mysql_close( $db_connect );
		header( 'Location: ../junction.php?area=media&show=library' );
		exit;
	}
}

// Scenario 2: Only a single one shall be changed.
else if( !empty( $_GET ) ) {
	ae_ManageActions::InitStatusAndId( $_GET, 'media' );
	$queue[] = ae_ManageActions::getId();
	$status = ae_ManageActions::getStatus();
}


if( empty( $queue ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=media&error=emptyqueue' );
	exit;
}



// Now process all the IDs in the queue.
foreach( $queue as $id ) {
	$element = ae_Media::getMediaById( $id );
	$author_id = $element->getUploaderId();

	if( ae_Permissions::getRoleOfCurrentUser() == 'guest'
			&& $author_id != ae_Permissions::getIdOfCurrentUser() ) {
		continue;
	}

	$element->update_status( $status );
}


mysql_close( $db_connect );
$from = isset( $_GET['from'] ) ? '&status=' . $_GET['from'] : '';
$from = isset( $_POST['from'] ) ? '&status=' . $_POST['from'] : $from;
header( 'Location: ../junction.php?area=media&show=library' . $from );
exit;