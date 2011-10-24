<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'theme', 'choosetheme' );


// End preview

if( isset( $_POST['endpreview'] ) ) {
	ae_Cookies::EndThemePreview();
}


// Show a preview of the theme

if( isset( $_POST['preview'] ) && isset( $_POST['theme'], $_POST['system'] ) ) {
	ae_Cookies::StartThemePreview( $_POST['theme'], $_POST['system'] );
}

mysql_close( $db_connect );
header( 'Location: ../junction.php?area=theme&show=choosetheme' );
