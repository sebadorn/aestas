<?php

require( '../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
define( 'ROLE', ae_Permissions::getRoleOfCurrentUser() );
define( 'STATUS', ae_Permissions::getStatusOfCurrentUser() );

if( ae_Permissions::isLoggedIn() ) {
	mysql_close( $db_connect );
	header( 'Location: junction.php' );
	exit;
}


function get_message() {
	if( isset( $_GET['error'] ) && $_GET['error'] == 'notfound' ) {
		return '<p class="wrong">Combination of name and password does not exist.</p>';
	}
	else if( isset ($_GET['success'] ) && $_GET['success'] == 'logout' ) {
		return '<p class="success">You successfully logged out.</p>';
	}
	else if( isset( $_GET['error'] ) && $_GET['error'] == 'notloggedin' ) {
		return '<p class="wrong">You are not logged in.</p>';
	}
	else if( isset( $_GET['error'] ) && $_GET['error'] == 'nocookies' ) {
		return '<p class="wrong">Please enable cookies and refresh this page.</p>';
	}
}

?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8" />
	<meta name="robots" content="noindex" />
	<title>Log in &lsaquo; aestas</title>
	<link rel="stylesheet" type="text/css" href="interface/css/login.css" />
</head>
<body>

<form accept-charset="utf-8" action="login.php" method="post">

	<h1>aestas</h1>

	<?php if( isset( $_GET['lost_pwd'] ) && $_GET['lost_pwd'] == 'ontheway' ): ?>
	<fieldset>
		<p class="success">A carrier pigeon is on the way!</p>
	</fieldset>
	<?php elseif( isset( $_GET['lost_pwd'] ) ): ?>
	<fieldset>
		<?php if( $_GET['lost_pwd'] == 'notfound' ): ?>
		<p class="wrong">An entered username either does not exist or has no mail address, or an entered mail address does not belong to any user.</p>
		<?php elseif( $_GET['lost_pwd'] == 'senderror' ): ?>
		<p class="wrong">Sorry, due to technical reasons it was not possible to send the mail.</p>
		<?php else: ?>
		<p class="success">Please enter your username or mail address. You will then receive a mail with further instructions.</p>
		<?php endif ?>
		<label for="user">Name or mail address</label>
		<input class="data" id="user" name="contact" type="text" />
		<input type="submit" value="Send recovery mail" />
	</fieldset>
	<?php else: ?>
	<fieldset>
		<?php echo get_message() ?>
		<label for="name">Name</label>
		<input class="data" id="name" name="name" type="text" />
		<label for="pass">Password</label>
		<input class="data" id="pass" name="pass" type="password" />
		<input type="submit" value="Log in" />
	</fieldset>
	<?php endif ?>

	<p class="visit">&#x2190; <a href="../">Visit Site</a></p>
	<?php if( isset( $_GET['error'] ) && $_GET['error'] == 'notfound' ): ?>
	<p class="lost"><a href="?lost_pwd">Forgot your password?</a></p>
	<?php endif ?>

</form>

<script type="text/javascript">
	document.getElementById( "name" ).focus();
</script>

</body>
</html>
