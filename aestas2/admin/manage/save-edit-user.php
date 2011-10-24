<?php

require( '../../includes/config.php' );


/* Check for call of the script by someone not logged in or with not enough rights */

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'manage', 'users' );


/* Check for missing elements */

if( !isset( $_POST['user_id'] ) || !ae_Validate::isDigit( $_POST['user_id'] ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=users' );
	exit;
}

$user = ae_User::getUserById( $_POST['user_id'] );

if( ae_Permissions::getRoleOfCurrentUser() == 'guest' && $user->getId() != ae_Permissions::getIdOfCurrentUser() ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=manage&show=users&error=notauthor' );
	exit;
}



/* If the password shall be changed: */

if( isset( $_POST['change'] ) && $_POST['change'] == 'pwd' ) {
	// Password not empty
	if( isset( $_POST['chg-pwd'], $_POST['chg-pwd-again'] ) && !empty( $_POST['chg-pwd-again'] ) ) {
		// Password and repetition coincide
		if( $_POST['chg-pwd'] == $_POST['chg-pwd-again'] ) {
			$user->setPassword( $_POST['chg-pwd-again'] );
			$user->update_to_database();

			if( $_POST['user_id'] == ae_Permissions::getIdOfCurrentUser() ) {
				ae_Permissions::Logout();
				ae_Permissions::Login( $user->getNameInternal(), $_POST['chg-pwd-again'] );
			}

			header( 'Location: ../junction.php?area=manage&show=users' );
		}
		else {
			header( 'Location: ../junction.php?area=manage&show=users&edit=' . $user->getId() . '&error=pwdagain' );
		}
	}
	else {
		header( 'Location: ../junction.php?area=manage&show=users&edit=' . $user->getId() . '&error=pwdagain' );
	}

	mysql_close( $db_connect );
	exit;
}



/* Otherwise: */

$error = '';


if( !empty( $_POST['name-internal'] ) && !ae_User::ExistsUserByName( $_POST['name-internal'] ) ) {
	$user->setNameInternal( $_POST['name-internal'] );
}
else if( $user->getNameInternal() != $_POST['name-internal'] ) {
	$error = '&error=nameinternal';
}

$user->setName( $_POST['name-external'] );

$user->generate_permalink( $_POST['name-permalink'] );

if( ae_Permissions::getRoleOfCurrentUser() == 'admin' && ae_Permissions::getIdOfCurrentUser() != $user->getId() ) {
	$user->setRole( $_POST['role'] );
}

$user->setEmail( $_POST['email'] );

$user->setUrl( $_POST['website'] );

$user->setEditor( $_POST['te'] );

if( ae_Permissions::getRoleOfCurrentUser() == 'admin' && ae_Permissions::getIdOfCurrentUser() != $user->getId() ) {
	$user->setStatus( $_POST['status'] );
}


/* Save to database */

$user->update_to_database();


if( !empty( $error ) ) {
	header( 'Location: ../junction.php?area=manage&show=users&edit=' . $user->getId() . $error );
}
else {
	header( 'Location: ../junction.php?area=manage&show=users' );
}

mysql_close( $db_connect );
