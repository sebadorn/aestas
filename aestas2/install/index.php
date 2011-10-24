<?php
define( 'INSTALL', true );
require_once( 'InstallCheck.php' );
?>
<!DOCTYPE html>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>You are about to install aestas</title>
	<meta name="robots" content="noindex" />
	<link type="text/css" rel="stylesheet" href="install.css" />
</head>
<body>

<h1>Installing aestas</h1>

<h2>Config file and directory permissions</h2>
<section id="checks">
	<?php echo InstallCheck::ConfigFile() ?>
	<?php echo InstallCheck::RightsMedia() ?>
	<?php echo InstallCheck::RightsThemes() ?>

	<?php if( InstallCheck::Ready() ): ?>
	<p class="proceed">Everything’s shiny, captain!<span class="arrow"></span></p>
	<?php endif ?>
</section>

<?php if( InstallCheck::Ready() ): ?>
	<?php
	include( '../includes/config.php' );
	$db_connect = @mysql_connect( $db_host, $db_user, $db_pass );

	$error = $success = '';
	if( !$db_connect ) {
		$error .= '<div class="error">Could not etablish a connection.</div>';
	}
	elseif( !mysql_select_db( $db_name ) ) {
		mysql_close( $db_connect );
		$success .= '<div class="success">Connection to database server is possible.</div>';
		$error .= '<div class="error">Could not find database "<code>' . $db_name . '</code>".</div>';
	}
	else {
		$success .= '<div class="success">Connection to database server is possible.</div>';
		$success .= '<div class="success">Database "<code>' . $db_name . '</code>" found.</div>';
	}

	if( empty( $error ) ) {
		include_once( 'InstallDb.php' );
		$error .= InstallDb::CheckPrivileges();
	}
	?>
<h2>Database</h2>
<section id="install-db">
	<?php echo $success ?>
	<?php if( !empty( $error ) ): ?>
	<?php echo $error ?>
	<?php else: ?>
		<?php define( 'INSTALL_STEP_2', true ) ?>
		<?php InstallDb::DefineTables() ?>
		<?php if( !InstallCheck::DbReady() ): ?>
		<a href="install-db.php">create database tables</a>
		<?php else: ?>
		<div class="success">Tables are present.</div>
		<p class="proceed">Nearly there!<span class="arrow"></span></p>
		<?php endif ?>
	<?php endif ?>
</section>
<?php endif ?>

<?php if( InstallCheck::Ready() && empty( $error ) && InstallCheck::DbReady() ): ?>
<h2>Create first user</h2>
<section id="firstuser">
	<?php if( !InstallCheck::UserExists() ): ?>
	<form method="post" action="install-content.php" accept-charset="utf-8">
		<div>
			<input id="username" type="text" name="username" value="admin" />
			<label for="username">Username</label><br />
			<input id="pwd" type="password" name="pwd" />
			<label for="pwd">Password</label><br />
			<input id="pwd-confirm" type="password" name="pwd-confirm" />
			<label for="pwd-confirm">Confirm password</label><br />
			<input type="submit" value="create me" />
		</div>
	</form>
	<?php else: ?>
	<div class="success">An admin user exists.</div>
	<p class="proceed">Yatta!<span class="arrow"></span></p>
	<?php endif ?>
</section>

<?php if( InstallCheck::UserExists() ): ?>
<h2>Finished</h2>
<section id="finished">
	<p>Well done! Now if you liked you could <a href="../admin">log in</a> and start your fresh new blog with a shiny new post!<span class="arrow arrowright"></span></p>
</section>
<?php endif ?>

<?php endif ?>

</body>
</html>