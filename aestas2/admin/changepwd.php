<?php
require( '../includes/config.php' );

$id = ae_Permissions::ChangePwdCheck();

$error = '';

if( isset( $_POST['newpwd'], $_POST['newpwdagain'] ) ) {
	if( $_POST['newpwd'] == $_POST['newpwdagain'] ) {
		$user = ae_User::getUserById( $id );
		$user->setPassword( $_POST['newpwd'] );
		if( $user->update_to_database() ) {
			header( 'Location: index.php' );
			mysql_close( $db_connect );
			exit;
		}
		else {
			$error = 'Failed to save new password, but I cannot say why. :(';
		}
	}
	else {
		$error = 'Password confirmation differed.';
	}
}

unset( $id );


function current_get() {
	$out = '';
	foreach( $_GET as $key => $value ) {
		$out .= $key . '=' . $value;
	}
	return $out;
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
		background-color: #ffffff;
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 10pt;
		line-height: 160%;
	}

	form {
		margin-top: 120px;
		text-align: center;
	}

	fieldset {
		border: 2px solid #f0f0f0;
		border-radius: 7px;
		-moz-border-radius: 7px;
		display: inline-block;
		margin: 0 auto;
		padding: 24px 36px;
		text-align: left;
		width: 220px;
	}

	h1 {
		color: #404040;
		font: italic 240% Georgia, serif;
		margin-bottom: 24px;
		text-shadow: #ffffff 0 1px 0;
	}

	input {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 100%;
	}

	input[type="submit"] {
		background-color: #f0f0f0;
		border: 1px solid #d0d0d0;
		border-radius: 4px;
		-moz-border-radius: 4px;
		box-shadow: inset 0 12px 6px #ffffff;
		-moz-box-shadow: inset 0 12px 6px #ffffff;
		-webkit-box-shadow: inset 0 12px 6px #ffffff;
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
		border-radius: 7px;
		-moz-border-radius: 7px;
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

<form accept-charset="utf-8" action="changepwd.php?<?php echo current_get() ?>" method="post">

	<h1>aestas</h1>

	<fieldset>
		<?php if( empty( $error ) ): ?>
		<p class="success">After setting the new password, the received link from the mail will not work anymore.</p>
		<?php else: ?>
		<p class="wrong"><?php echo $error ?></p>
		<?php endif ?>
		<label for="newpwd">New password</label>
		<input class="data" id="newpwd" name="newpwd" type="password" />
		<label for="newpwdagain">Again for confirmation</label>
		<input class="data" id="newpwdagain" name="newpwdagain" type="password" />
		<input type="submit" value="Set as new password" />
	</fieldset>

	<p class="visit">&#x2190; <a href="../">Visit Site</a></p>

</form>

</body>
</html>