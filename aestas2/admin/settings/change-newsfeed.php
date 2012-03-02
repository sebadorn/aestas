<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'newsfeed' );

$edit = new ae_EditSettings();


/* Newsfeed limit */

$edit->setNewsfeedLimit( $_POST['nf-posts'] );


/* Alternate service */

$edit->setNewsfeedAlternate( $_POST['nf-alternate'] );


/* Newsfeed content */

$edit->setNewsfeedContent( $_POST['nf-content'], $_POST['shorten-to'] );


$edit->update_to_database();


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=set&show=newsfeed&ran=' . rand( 1, 100 ) );
