<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

ae_Permissions::Check( 'create', 'adduser' );


// Problems with user input

$nameempty = $nametaken = $pwdempty = $pwdagain = '';

if( isset( $_GET['error'] ) ) {
	switch( $_GET['error'] ) {
		case 'nameinternalempty':
			$nameempty = 'class="error" ';
			break;
		case 'nameinternaltaken':
			$nametaken = 'class="error" ';
			break;
		case 'pwdempty':
			$pwdempty = 'class="error" ';
			break;
		case 'pwdagain':
			$pwdagain = 'class="error" ';
			break;
	}
}
?>
<header class="content-menu">
	<h1>Add User</h1>
</header>

<div class="content create user">

	<?php if( !empty( $nametaken ) ) : ?>
	<p class="info error">The requested name already exists. Please choose another one.</p>
	<?php elseif( !empty( $nameempty ) ) : ?>
	<p class="info error">An user needs a name.</p>
	<?php elseif( !empty( $pwdempty ) ) : ?>
	<p class="info error">An user needs a password.</p>
	<?php elseif( !empty( $pwdagain ) ) : ?>
	<p class="info error">The password and its repetition didn’t match.</p>
	<?php endif; ?>

	<form accept-charset="utf-8" action="create/add-user.php" method="post">


		<fieldset id="user-name-internal">
			<legend>User name (internal)</legend>
			<div>
				<input <?php echo $nameempty.$nametaken ?>name="name-internal" type="text" />
				<p><strong>Required!</strong> User name to log in.</p>
			</div>
		</fieldset>


		<fieldset id="email">
			<legend>E-mail</legend>
			<div>
				<input name="email" type="text" />
				<p>To send the password to, if forgotten.</p>
			</div>
		</fieldset>


		<fieldset id="password">
			<legend>Password</legend>
			<div>
				<input <?php echo $pwdempty ?>name="pwd" type="password" />
				<span>Chosen password.</span><br />
				<input <?php echo $pwdagain ?>name="pwd-again" type="password" />
				<span>Please repeat to make sure.</span>
				<p><strong>Required!</strong></p>
			</div>
		</fieldset>


		<div class="right-column">

			<fieldset class="next">
				<legend>Next</legend>
				<div class="submit">
					<input type="submit" value="create new user" />
				</div>
			</fieldset>

			<fieldset id="role">
				<legend>Role</legend>
				<div>
					<select name="role">
						<option value="admin">Administrator</option>
						<option value="author" selected="selected">Author</option>
						<option value="guest">Guest</option>
						<option value="mechanic">Mechanic</option>
					</select>
					<p id="explainrole"></p>
				</div>
			</fieldset>

		</div>

	</form>

</div>
