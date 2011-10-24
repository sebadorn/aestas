<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'media', 'library' );


/* Check for missing elements */

if( !isset( $_POST['file_id'] ) || !ae_Validate::isDigit( $_POST['file_id'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=media&show=library' );
	exit;
}


$media = ae_Media::getMediaById( $_POST['file_id'] );

$media->setName( $_POST['name'] );

$media->rename_file();

if( !isset( $_POST['tags_js'] ) ) {
	$_POST['tags_js'] = array();
}
$media->setTags( $_POST['tags'], $_POST['tags_js'] );

$media->setDescription( $_POST['desc'] );

if( isset( $_POST['status'] ) ) {
	$media->setStatus( $_POST['status'] );
}

$media->update_to_database();


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=media&show=library' );
