<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


if( !isset( $_GET['themedir'] ) ) {
	$_GET['themedir'] = ae_Settings::Theme();
	$_GET['themedir'] = $_GET['themedir']['blog_theme'];
}

if( !isset( $_GET['file'] ) ) {
	$_GET['file'] = 'index.php';
}

?>
<header class="content-menu">
	<h1>Edit Theme</h1>
</header>

<div class="content theme edit">

	<form accept-charset="utf-8" action="theme/edit-themefile.php" method="post">


		<div id="edit">

			<textarea name="content" cols="120" rows="30"><?php echo ae_Theme::ReadFileContent( $_GET['themedir'], $_GET['file'] ) ?></textarea>

			<input name="themedir" type="hidden" value="<?php echo $_GET['themedir'] ?>" />
			<input name="file" type="hidden" value="<?php echo $_GET['file'] ?>" />
			<input type="submit" value="Save file" />

		</div>


		<div id="choose">

			<!--<fieldset id="choosetheme">
				<legend>Themes</legend>
				<ul>
					<?php echo ae_Theme::getThemesForEditList() ?>
				</ul>
			</fieldset>-->

			<fieldset id="choosefile">
				<legend>Files of <strong><?php echo ae_Theme::getName( $_GET['themedir'] ) ?></strong></legend>
				<ul>
					<?php echo ae_Theme::getFilesForEditList( $_GET['themedir'], $_GET['file'] ) ?>
				</ul>
			</fieldset>

		</div>


	</form>

</div>
