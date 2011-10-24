<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'comments' );

if( !isset( $_POST['area'] ) || !isset( $_POST['show'] ) || !isset( $_POST['search'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../' );
	exit;
}


$search = new ae_Search( $_POST['search'] );
$search->process_phrase();


mysql_close( $db_connect );

header(
	'Location: ../junction.php?area=' . $_POST['area'] . '&show=' . $_POST['show']
	. $search->get_params_as_url() . '&search_was=' . urlencode( $_POST['search'] )
);
