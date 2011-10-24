<?php

// Lost password
if( isset( $_POST['contact'] ) && !empty( $_POST['contact'] ) ) {
	require_once( '../includes/config.php' );

	$sendto = '';
	if( ae_Validate::isEmail( $_POST['contact'] ) ) {
		$id = ae_User::FindByMail( $_POST['contact'] );
	}
	else {
		$id = ae_User::FindByNameInternal( $_POST['contact'] );
	}

	if( !$id ) {
		header( 'Location: index.php?lost_pwd=notfound' );
		mysql_close( $db_connect );
		exit;
	}

	$user = ae_User::getUserById( $id );

	$message =
			'To choose a new password you can visit this site:' . PHP_EOL
			. "\t" . ae_URL::Protocol() . ae_URL::Blog() . '/changepwd.php?'
			. ae_Permissions::ChangePwdKey( $user ) . '=' . ae_Permissions::ChangePwdValue( $user ) . PHP_EOL . PHP_EOL
			. 'If you did not ask for a new password just ignore this mail.' . PHP_EOL . PHP_EOL
			. 'Sincerely yours, aestas';

	$mail = new ae_Mail();
	$mail->add_receiver( $user->getEmail() );
	$mail->setSender( 'aestas (' . ae_URL::Domain() . ')' );
	$mail->setSubject( '[aestas] A little birdie told me you lost your password' );
	$mail->setMessage( $message );

	if( !$mail->send() ) {
		header( 'Location: index.php?lost_pwd=senderror' );
	}
	else {
		header( 'Location: index.php?lost_pwd=ontheway' );
	}

	mysql_close( $db_connect );
	exit;
}


// Log-in
if( !isset( $_POST['name'], $_POST['pass'] ) ) {
	header( 'Location: index.php' );
	exit;
}

require( '../includes/config.php' );


if( !ae_Cookies::TestCookiePossible_Evaluate() ) {
	header( 'Location: index.php?error=nocookies' );
}
else if( ae_Permissions::Login( $_POST['name'], $_POST['pass'] ) ) {
	header( 'Location: junction.php' );
}
else {
	header( 'Location: index.php?error=notfound' );
}

mysql_close( $db_connect );
