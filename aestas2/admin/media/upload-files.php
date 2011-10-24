<?php

// TODO: get file-type by looking at ending + exif_imagetype() and NOT $_FILE[]['type']

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'media', 'upload' );


/* Check for missing elements */

if( !isset( $_POST['files'] ) || !ae_Validate::isDigit( $_POST['files'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=media&show=upload' );
	exit;
}


for( $i = 1; $i <= $_POST['files']; $i++ ) {
	$file = new ae_FileUpload();

	$file->set_uploaded_file( $_FILES['ae_upload_' . $i] );

	if( $file->has_file_error() ) {
		unset( $file );
		continue;
	}

	$file->save_to_directory();

	$file->save_to_database();

	$preview = ae_Settings::getSetting( 'media_imagepreview' );

	if( $preview == 'true' ) {
		$file->create_previewimage();
	}
}


mysql_close( $db_connect );

header( 'Location: ../junction.php?area=media&show=library' );
