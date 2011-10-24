<?php

require( '../../includes/config.php' );


/* Check for call of the script by someone not logged in or with not enough rights */

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'categories' );


/* Check for missing elements */

if( !isset( $_POST['cat_id'] ) || !ae_Validate::isDigit( $_POST['cat_id'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=categories' );
	exit;
}


$category = ae_Category::getCategoryById( $_POST['cat_id'] );

$category->setName( $_POST['title'] );

$_POST['cat'] = !isset( $_POST['cat'] ) ? 0 : $_POST['cat'];
$category->setParent( $_POST['cat'] );

$category->generate_permalink( $_POST['permalink'] );

$category->setStatus( isset( $_POST['trash'] ) ? 'trash' : 'active' );


/* Update category to database */

$category->update_to_database();


header( 'Location: ../junction.php?area=manage&show=categories' );

mysql_close( $db_connect );
