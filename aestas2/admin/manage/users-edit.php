<?php

$user = ae_User::getUserById( $_GET['edit'] );

if( empty( $user ) ) {
	echo ae_PageStructure::NotExisting( 'user' );
}
else if( !ae_Permissions::hasRights( $area, $content, $user ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	$example_permalink = 'http://' . URL . '/' . ae_URL::StructureOfAuthor();
	$example_permalink = str_replace( '%authorname%', strtolower( $user->getName() ), $example_permalink );

	// Role
	$sel_admin = $sel_author = $sel_guest = $sel_mecha = '';

	switch( $user->getRole() ) {
		case 'admin':
			$sel_admin = ' selected="selected"';
			break;
		case 'author':
			$sel_author = ' selected="selected"';
			break;
		case 'guest':
			$sel_guest = ' selected="selected"';
			break;
	}

	// User status
	$sel_suspended = $sel_trash = $sel_active = '';

	switch( $user->getStatus() ) {
		case 'suspended':
			$userstatus = 'suspended';
			$sel_suspended = ' selected="selected"';
			break;
		case 'trash':
			$userstatus = 'trash';
			$sel_trash = ' selected="selected"';
			break;
		default:
			$userstatus = 'active';
			$sel_active = ' selected="selected"';
	}

	// User editor
	$chk_code = $chk_rte = '';

	switch( $user->getEditor() ) {
		case 'code':
			$chk_code = ' selected="selected"';
			break;
		case 'ckeditor':
			$chk_rte = ' selected="selected"';
			break;
		default:
			$chk_code = ' selected="selected"';
	}

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

	$permalink_authorname = ae_URL::ExtractAuthornameFromPermalink( $user->getPermalink() );

?>


<div id="create" class="user">

	<h1>Add User</h1>

	<?php if( !empty( $nametaken ) ) : ?>
	<p class="info error">The requested name already exists. Please choose another one.</p>
	<?php elseif( !empty( $nameempty ) ) : ?>
	<p class="info error">An user needs a name.</p>
	<?php elseif( !empty( $pwdempty ) ) : ?>
	<p class="info error">An user needs a password.</p>
	<?php elseif( !empty( $pwdagain ) ) : ?>
	<p class="info error">The password and its repetition didn’t match.</p>
	<?php endif; ?>

	<form accept-charset="utf-8" action="manage/save-edit-user.php" method="post">


		<fieldset id="user-name-internal">
			<legend>User name (internal)</legend>
			<div>
				<input <?php echo $nameempty . $nametaken ?>name="name-internal" type="text"
					value="<?php echo $user->getNameInternalHtml() ?>" />
				<p><strong>Required!</strong> User name to log in.</p>
			</div>
		</fieldset>


		<fieldset id="user-name-external">
			<legend>User name (external)</legend>
			<div>
				<input name="name-external" type="text" value="<?php echo $user->getNameHtml() ?>" />
				<p>Name that will be displayed on blog. If left empty the internal name will be used.</p>
			</div>
		</fieldset>


		<fieldset id="user-name-permalink">
			<legend>User permalink</legend>
			<div>
				<input name="name-permalink" type="text" value="<?php echo $permalink_authorname ?>" />
				<p>Name that will show in the URL if a user filters the posts by author.</p>
				<p>Example: <?php echo $example_permalink; ?></p>
			</div>
		</fieldset>


		<fieldset id="email">
			<legend>E-mail</legend>
			<div>
				<input name="email" type="text" value="<?php echo $user->getEmailHtml() ?>" />
				<p>To send the password to, if forgotten.</p>
			</div>
		</fieldset>


		<fieldset id="website">
			<legend>Website</legend>
			<div>
				<input name="website" type="text" value="<?php echo $user->getUrlHtml() ?>" />
				<p>Your main site you participate in.</p>
			</div>
		</fieldset>


		<fieldset id="texteditor">
			<legend>Editor</legend>
			<div>
				<select name="te">
					<option value="code"<?php echo $chk_code ?>>Plain code</option>
					<option value="ckeditor"<?php echo $chk_rte ?>>Text editor</option>
				</select>
				<p>How you like to write your posts.</p>
			</div>
		</fieldset>


		<div class="right-column">

			<fieldset class="next">
				<legend>Next</legend>
				<div class="submit">
					<input type="hidden" name="user_id" value="<?php echo $user->getId() ?>" />
					<input type="submit" value="save changes" />
				</div>
			</fieldset>

			<fieldset id="status">
				<legend>Status</legend>
				<div>
				<?php if( ROLE == 'admin' && ae_Permissions::getIdOfCurrentUser() != $user->getId() ) : ?>
					<select name="status">
						<option value="active"<?php echo $sel_active; ?>>Employed</option>
						<option value="suspended"<?php echo $sel_suspended; ?>>Suspended</option>
						<option value="trash"<?php echo $sel_trash; ?>>Deletion candidate</option>
					</select>
					<p id="explainstatus"></p>
				<?php else : ?>
					<p id="explainstatus" class="nochange"><?php echo $userstatus ?></p>
				<?php endif; ?>
				</div>
			</fieldset>


			<fieldset id="role">
				<legend>Role</legend>
				<div>
				<?php if( ROLE == 'admin' && ae_Permissions::getIdOfCurrentUser() != $user->getId() ) : ?>
					<select name="role">
						<option value="admin"<?php echo $sel_admin ?>>Administrator</option>
						<option value="author"<?php echo $sel_author ?>>Author</option>
						<option value="guest"<?php echo $sel_guest ?>>Guest</option>
						<option value="mechanic"<?php echo $sel_mecha ?>>Mechanic</option>
					</select>
					<p id="explainrole"></p>
				<?php else: ?>
					<p id="explainrole" class="nochange"><?php echo $user->getRole() ?></p>
				<?php endif; ?>
				</div>
			</fieldset>

		</div>

	</form>

	<hr />

	<form accept-charset="utf-8" action="manage/save-edit-user.php" method="post">

		<fieldset id="password">
			<legend>Password</legend>
			<div>
				<input <?php echo $pwdempty ?>name="chg-pwd" type="password" />
				<span>Chosen password.</span><br />
				<input <?php echo $pwdagain ?>name="chg-pwd-again" type="password" />
				<span>Please repeat to make sure.</span>
			</div>
		</fieldset>


		<div class="right-column">

			<fieldset class="next">
				<legend>Next</legend>
				<div class="submit">
					<input type="hidden" name="user_id" value="<?php echo $user->getId() ?>" />
					<input type="hidden" name="change" value="pwd" />
					<input type="submit" value="change password" />
				</div>
			</fieldset>

		</div>

	</form>

</div>

<script type="text/javascript" src="interface/js/jquery-user.js"></script>

<?php } ?>