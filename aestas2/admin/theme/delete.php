<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'theme', 'edittheme' );


if( isset( $_POST['del_theme'], $_POST['delete'] ) ) {
	$dir = '../../themes/' . $_POST['del_theme'];

	if( file_exists( $dir ) ) {
		ae_Theme::DeleteTheme( $dir );
	}
}

mysql_close( $db_connect );
header( 'Location: ../junction.php?area=theme&show=choosetheme' );