<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'create', 'adduser' );


// Check for missing elements
if( !isset( $_POST['name-internal'], $_POST['role'], $_POST['pwd'], $_POST['pwd-again'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=adduser&error=missing_data' );
	exit;
}

// Check if internal name has been filled out
else if( empty( $_POST['name-internal'] ) ) {
	mysql_close( $db_connect );
	header('Location: ../junction.php?area=create&show=adduser&error=nameinternalempty');
	exit;
}

// Check if name already taken
else if( ae_User::ExistsUserByName( $_POST['name-internal'] ) == 1 ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=adduser&error=nameinternaltaken' );
	exit;
}

// No password set
else if( empty( $_POST['pwd'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=adduser&error=pwdempty' );
	exit;
}

// Password and control-password are not identical
else if( $_POST['pwd'] != $_POST['pwd-again'] ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=create&show=adduser&error=pwdagain' );
	exit;
}


$user = new ae_User();

$user->setNameInternal( $_POST['name-internal'] );

$user->setName( $_POST['name-internal'] );

$user->setPassword( $_POST['pwd'] );

$user->setRole( $_POST['role'] );

$user->setEmail( $_POST['email'] );

$user->setStatus( 'active' );


/* Save user to database */

$user->save_new();

$user->setId( ae_Create::LastIdOfUser() );


/* Permalink */

$user->generate_permalink();

$user->update_permalink();


/* Going home */

header( 'Location: ../junction.php?area=manage&show=users' );
mysql_close( $db_connect );
