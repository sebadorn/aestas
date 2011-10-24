<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'create', 'addcategory' );


/* Check for missing elements */

if( !isset( $_POST['title'], $_POST['cat'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=addcategory&error=missing_data' );
	exit;
}


/* Create category object. */

$category = new ae_Category();

$category->setName( $_POST['title'] );

$category->setParent( $_POST['cat'] );


/* Save category to database */

$category->save_new();

$category->setId( ae_Create::LastIdOfCategory() );


/* Before we didn't have the ID. Now we can generate the permalink. */

$category->generate_permalink( $_POST['permalink'] );

$category->update_permalink();


/* Going home */

header( 'Location: ../junction.php?area=manage&show=categories' );
mysql_close( $db_connect );
