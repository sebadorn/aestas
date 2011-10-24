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
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-style-type" content="text/css" />
	<meta http-equiv="content-script-type" content="text/javascript" />
	<meta name="robots" content="noindex" />
	<title>Log in &lsaquo; aestas</title>
	<style type="text/css">
	* {
		margin: 0;
		padding: 0;
	}

	a {
		color: #3080e0 !important;
		text-decoration: none;
	}

	a:hover {
		border-bottom: 1px solid #70c0ff;
	}

	p.visit,
	p.lost {
		color: #303030;
		font-size: 80%;
		margin: 0 auto;
		text-align: left;
		width: 220px;
	}

	p.lost {
		margin-top: -20px;
		text-align: right;
	}

	body {
		background-color: #f4f4f4;
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 10pt;
		line-height: 160%;
	}

	form {
		margin-top: 120px;
		text-align: center;
	}

	fieldset {
		background-color: #ffffff;
		border: 0;
		-moz-border-radius: 7px;
		border-radius: 7px;
		-moz-box-shadow: 0 0 40px 0 #e0e0e0;
		-webkit-box-shadow: 0 0 40px 0 #e0e0e0;
		box-shadow: 0 0 40px 0 #e0e0e0;
		display: inline-block;
		margin: 0 auto 4px;
		padding: 20px 24px;
		text-align: left;
		width: 220px;
	}

	h1 {
		background: url("interface/img/aestas-23x32.png") left center no-repeat;
		color: #bababa;
		font: italic 240% Georgia, serif;
		margin: 0 auto 24px;
		position: relative;
		right: -19px;
		text-shadow: 0 1px 0 #ffffff;
		width: 152px;
	}

	input {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 100%;
	}

	input[type="submit"] {
		background-color: #f0f0f0;
		border: 1px solid #d0d0d0;
		-moz-border-radius: 4px;
		border-radius: 4px;
		-moz-box-shadow: inset 0 12px 6px #ffffff;
		-webkit-box-shadow: inset 0 12px 6px #ffffff;
		box-shadow: inset 0 12px 6px #ffffff;
		color: #000000;
		display: block;
		float: right;
		margin-top: 12px;
		padding: 4px 15px 5px;
	}

	input[type="submit"]:hover {
		border-color: #606060;
	}

	input[type="submit"],
	label {
		cursor: pointer;
	}

	input[type="text"],
	input[type="password"] {
		border: 1px solid #d0d0d0;
		padding: 4px 6px;
		width: 206px;
	}

	.data {
		margin-bottom: 16px;
	}

	label {
		color: #b0b0b0;
		display: block;
	}

	.success,
	.wrong {
		background-color: #e08060;
		-moz-border-radius: 7px;
		border-radius: 7px;
		color: #ffffff;
		margin: 0 auto 16px;
		padding: 8px 14px;
	}

	.success {
		background-color: #90c0e8;
	}
	</style>
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
