<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'theme', 'edittheme' );


/* Check for missing elements */

if( !isset( $_POST['themedir'] ) || !isset( $_POST['file'] ) || !isset( $_POST['content'] ) ) {
	header( 'Location: ../junction.php?area=theme&show=edittheme' );
	exit;
}


$filepath = $_POST['themedir'] . '/' . $_POST['file'];
$result = ae_Theme::SaveEditedFile( $filepath, $_POST['content'] );

$result = $result ? 'success' : 'error';


header(
	'Location: ../junction.php?area=theme&show=edittheme&themedir=' . urlencode( $_POST['themedir'] )
	. '&file=' . urlencode( $_POST['file'] ) . '&ran=' . rand( 1, 200 ) . '&result=' . $result
);
