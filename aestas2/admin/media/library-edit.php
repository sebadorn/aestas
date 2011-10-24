<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

$media = ae_Media::getMediaById( $_GET['edit'] );

if( ROLE == 'guest' && $media->getUploaderId() != ae_Permissions::getIdOfCurrentUser() ) {
	echo ae_PageStructure::MissingRights();
}

if( empty( $media ) ) {
	echo ae_PageStructure::NotExisting( 'media' );
}


$exists = $media->file_exists_inmedia();
?>


<div id="create" class="media">

	<h1>File Info</h1>

	<form accept-charset="utf-8" action="media/edit-file.php" method="post">


		<div id="part-one">

			<?php if( !$exists ) : ?>
			<p class="info warning">
				A file named <code><?php echo $media->getName() ?></code>
				does not exist in <code>media/<?php echo $media->getDateFilepath() ?></code>.
			</p>
			<?php endif; ?>

			<fieldset id="precontent">
				<legend>Info</legend>

				<div class="previewimg">
					<?php if( $media->isImage() && $media->file_exists_inmedia() ): ?>
					<img alt="preview" src="../media/<?php echo $media->getDate( 'Y/m' ) , '/tiny/' , $media->getPreviewImageName() ?>" />
					<?php endif; ?>
				</div>

				<div>
					<label for="name">Name</label>
					<input id="name" name="name" type="text" value="<?php echo $media->getName() ?>" /><br />
					<?php if( $exists ) : ?>
						<?php if( $media->isImage() ): ?>
						<label for="dimensions" style="cursor: default;">Dimensions</label>
						<input class="readonly" id="dimensions" type="text" value="<?php echo $media->getImageDimensions(); ?>" readonly="readonly" /><br />
						<?php endif; ?>
					<label for="size" style="cursor: default;">Size</label>
					<input class="readonly" id="size" type="text" value="<?php echo $media->getFilesize() ?>" readonly="readonly" /><br />
					<?php endif; ?>
					<label for="type" style="cursor: default;">Type</label>
					<input class="readonly" id="type" type="text" value="<?php echo $media->getType() ?>" readonly="readonly" /><br />
					<label for="date" style="cursor: default;">Date</label>
					<input class="readonly" id="date" type="text" value="<?php echo $media->getDate() ?>" readonly="readonly" /><br />
					<label for="uploader" style="cursor: default;">Uploader</label>
					<input class="readonly" id="uploader" type="text" value="<?php echo $media->getUploaderName() ?>" readonly="readonly" /><br />
					<label class="usedin" style="cursor: default;">Used in</label>
					<?php echo $media->used_in() ?>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" value="save changes" />
					<input name="file_id" type="hidden" value="<?php echo $media->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="status">
				<legend>Status</legend>
				<div>
					<?php if( $media->getStatus() == 'trash' ) : ?>
					<input type="checkbox" name="status" value="available" id="restore" />
					<label for="restore">Restore file</label>
					<p>Removes the file’s status as trash.</p>
					<?php else : ?>
					<input type="checkbox" name="status" value="trash" id="trash" />
					<label for="trash">Move to Trash</label>
					<p>The file won’t be removed now. To delete it for good, delete it from the Trash.</p>
					<?php endif; ?>
				</div>
			</fieldset>

		</div>


		<div id="ext_options">

			<ul id="ext_nav">
				<li class="active">Tags</li>
				<li>Desc</li>
			</ul>

			<div class="tags">
				<input name="tags" type="text" value="<?php echo $media->getTags() ?>" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>Multiple tags can be seperated with semicolons (;).</p>
			</div>

			<div class="desc hideonload">
				<textarea name="desc" cols="40" rows="4"><?php echo $media->getDescription() ?></textarea>
				<p>The Description could contain a summary of the content or what the file will be used for.</p>
			</div>

		</div>


	</form>

</div>
