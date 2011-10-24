<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'theme', 'choosetheme' );


if( isset( $_POST['use'] ) ) {
	ae_Theme::UseTheme( $_POST['theme'], $_POST['system'] );
}

mysql_close( $db_connect );
header( 'Location: ../junction.php?area=theme&show=choosetheme' );
