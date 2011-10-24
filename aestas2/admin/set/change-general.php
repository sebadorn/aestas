<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'general' );

$edit = new ae_EditSettings();


/* Blog */

$edit->setBloginfoTitle( $_POST['blogtitle'] );

$edit->setBloginfoTagline( $_POST['tagline'] );

$edit->setBlogFront( $_POST['front'] );

if( ae_Validate::isDigit( $_POST['post_limit'] ) && $_POST['post_limit'] > 0 ) {
	$edit->setBlogPostLimit( $_POST['post_limit'] );
}


/* Timezone */

$edit->setTimezone( $_POST['timezone'] );


/* Authentification */

$edit->setAuth( $_POST['auth'] );


$edit->update_to_database();


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=set&show=general&ran=' . rand( 1, 100 ) );
