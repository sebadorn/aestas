<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'comments' );

$edit = new ae_EditSettings();


$edit->setComments( isset( $_POST['comments'] ) ? 'true' : 'false' );

$edit->setPings( isset( $_POST['pings'] ) ? 'true' : 'false' );


$moderate = isset( $_POST['moderate-once'] ) ? 'once' : 'false';

if( !isset( $_POST['moderation'] ) ) {
	$moderate = 'false';
}
else if( isset( $_POST['moderation'] ) && $moderate != 'once' ) {
	$moderate = 'true';
}

$edit->setCommentsModerate( $moderate );


$edit->setGravatar( isset( $_POST['grav'] ) ? 'true' : 'false' );

$edit->setGravatarRating( $_POST['rating'] );

$edit->setGravatarDefault( $_POST['default-ava'] ); // own, blank, mystery


$edit->update_to_database();


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=set&show=comments&ran=' . rand( 1, 100 ) );
