<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


$ch_talk = ( ae_Settings::getSetting( 'aestas_talk' ) == 'true' ) ? ' checked="checked"' : '';

$sel_session = '';
$sel_cookie = ' selected="selected"';
if( ae_Settings::getSetting( 'auth_system' ) == 'session' ) {
	$sel_session = ' selected="selected"';
	$sel_cookie = '';
}
?>


<div id="set">

	<h1>General Settings</h1>

	<form accept-charset="utf-8" action="set/change-general.php" method="post">

		<fieldset id="blog-title">
			<legend>Blog title</legend>
			<div>
				<input type="text" name="blogtitle" value="<?php echo ae_Settings::getSetting( 'bloginfo_title' ) ?>" />
			</div>
		</fieldset>

		<fieldset id="tagline">
			<legend>Tagline</legend>
			<div>
				<input type="text" name="tagline" value="<?php echo ae_Settings::getSetting( 'bloginfo_tagline' ) ?>" />
				Find some fancy words for your blog.
			</div>
		</fieldset>

		<fieldset id="front">
			<legend>Front page</legend>
			<div>
				<select name="front">
					<optgroup label="Blog">
						<option value="posts">Your posts</option>
					</optgroup>
					<optgroup label="A page">
						<?php echo ae_Misc::PagesForSelect( ae_Settings::getSetting( 'blog_front' ) ) ?>
					</optgroup>
				</select> This shall be on the first page when visiting your site.
			</div>
		</fieldset>

		<fieldset>
			<legend>Posts per page</legend>
			<div>
				<input class="shorter" type="text" name="post_limit" value="<?php echo ae_Settings::PostLimit() ?>" /> posts
			</div>
		</fieldset>

		<hr />

		<fieldset id="timezone">
			<legend>Timezone</legend>
			<div>
				<select name="timezone">
					<?php echo ae_Misc::Timezones() ?>
				</select>
				<span>Actual set time: <code><?php echo date( 'Y-m-d H:i:s' ) ?></code></span>
			</div>
		</fieldset>

		<hr />

		<fieldset id="auth">
			<legend>Authentification</legend>
			<div>
				<select name="auth">
					<option value="cookie"<?php echo $sel_cookie ?>>Cookie</option>
					<option value="session"<?php echo $sel_session ?>>Session</option>
				</select>
				<span>Changing this setting will log you out immediately.</span>
			</div>
		</fieldset>

		<hr />

		<div class="submit">
			<input type="submit" value="save changes" />
		</div>

	</form>

</div>
