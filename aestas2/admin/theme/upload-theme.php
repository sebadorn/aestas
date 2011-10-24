<?php

require( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'theme', 'uploadtheme' );


if( $_FILES['ae_upload']['error'] != 4 && $_FILES['ae_upload']['type'] == 'application/zip' ) {
	ae_Theme::UnpackZip( $_FILES['ae_upload']['tmp_name'], $_FILES['ae_upload']['name'] );
}


header( 'Location: ../junction.php?area=theme&show=choosetheme' );
mysql_close( $db_connect );
