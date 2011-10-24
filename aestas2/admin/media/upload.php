<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

?>
<div id="media" class="upload">

	<h1>Upload</h1>

	<form accept-charset="utf-8" action="media/upload-files.php" enctype="multipart/form-data" method="post">

		<fieldset class="step" id="step-1">
			<legend>Choose files</legend>
			<div>
				<input class="add" style="display: none;" type="button" value="fields +1" /><br />
				<input name="files" type="hidden" value="3" />
				<input name="ae_upload_1" type="file" /><br />
				<input name="ae_upload_2" type="file" /><br />
				<input name="ae_upload_3" type="file" />
			</div>
		</fieldset>

		<fieldset class="next">
			<legend>Next</legend>
			<div>
				<input type="submit" value="upload files" />
			</div>
		</fieldset>

		<fieldset class="info">
			<legend>Info</legend>
			<div>
				<p>The file size limit is <?php echo ae_Misc::SizeLimit() ?>.</p>
			</div>
		</fieldset>

	</form>

</div>
