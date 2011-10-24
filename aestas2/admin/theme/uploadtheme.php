<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

?>
<div id="media" class="upload theme-archive">

	<h1>Upload Theme</h1>

	<p class="cleaninfo">
		A theme can be uploaded as ZIP archive.<br />
	</p>

	<form accept-charset="utf-8" action="theme/upload-theme.php" enctype="multipart/form-data" method="post">

		<fieldset class="step" id="step-1">
			<legend>Choose file</legend>
			<div>
				<input name="ae_upload" type="file" /><br />
			</div>
		</fieldset>

		<fieldset class="next">
			<legend>Next</legend>
			<div>
				<input type="submit" value="upload and unpack theme" />
			</div>
		</fieldset>

	</form>

</div>
