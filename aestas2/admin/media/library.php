<?php

if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
		$_GET['page'] = 1;
	}
	define( 'PAGE', $_GET['page'] - 1 );

	$filter = ae_ContentOfMedia::FilterForMedia();

	$media = new ae_MediaFileQuery( $filter );
	$media_count = $media->count_files();
	$pageflip = ae_PageStructure::BuildPageflip( PAGE, $media->getLimit(), $media_count );
	$pageflip_empty = ( $media_count <= $media->getLimit() ) ? ' empty' : '';

?>


<div id="manage" class="media">

	<h1>Library</h1>


	<ul id="status_filter">
		<?php echo ae_ContentOfMedia::TypeFilterMediaNav() ?>
	</ul>


	<form accept-charset="utf-8" action="media/apply.php" class="bulk_action" method="post">

		<div class="bulk_apply">
			Selected:
			<select name="bulk">
				<?php if( $filter['status'] == 'trash' ) : ?>
				<option value="available">Restore</option>
				<?php endif; ?>
				<option value="trash">Delete</option>
			</select>

			<input name="from" type="hidden" value="<?php echo $filter['status']; ?>" />
			<input type="submit" name="submit" value="Apply" />
		</div>


		<?php if( $filter['status'] == 'trash' ) : ?>
		<p class="info warning">
			Files that are deleted from here will be lost without return.
		</p>
		<?php endif; ?>


		<div class="page-nav page-nav-top<?php echo $pageflip_empty ?>">
			<?php echo $pageflip; ?>
		</div>


		<?php $before = ''; ?>
		<?php if( $media->have_files() ) : ?>
		<ul id="files">
			<?php while( $media->have_files() ) : $media->the_file() ?>

				<?php
					// Dividing uploads by their date of upload
					$today = false;
					if( $before == '' || $media->file_date( 'Ymd' ) < $before ) {
						$today = true;
						$before = $media->file_date( 'Ymd' );
						$class_today = ( $before == date( 'Ymd' ) ) ? ' grouptoday' : '';
					}

					// CSS class
					$class = '';
					if( !file_exists( '../media/' . $media->file_date( 'Y/m/' ) . $media->file_name() ) ) {
						$class = ' notfound';
					}
					else if( $media->file_date( 'Y-m-d' ) == date( 'Y-m-d' ) ) {
						$class = ' today';
					}

					// Tags and description
					$qs = '?area=media';
					if( isset( $_GET['type'] ) ) {
						$qs .= '&amp;type=' . $_GET['type'];
					}

					$tags = $media->file_tags( $qs, $filter['status'] );
					$tags = empty( $tags ) ? '<em>none</em>' : $tags;

					$desc = $media->file_description();
					$desc = empty( $desc ) ? '<em>none</em>' : $desc;
				?>

				<?php if( $today ): ?>
				<li class="uploadday<?php echo $class_today ?>">
					<span class="date">
						<?php echo empty( $class_today ) ? $media->file_date( 'jS F, Y' ) : 'Today'; ?>
					</span>
				</li>
				<?php endif; ?>

				<li class="file<?php echo $class ?>" id="<?php echo $media->file_ID() ?>">

					<div class="box check">
						<input name="id[]" type="checkbox" value="<?php echo $media->file_ID() ?>" />
						<div class="actions_trigger">
							<div class="actions">
								<?php echo ae_ContentOfMedia::MediaActions( $media ) ?>
							</div>
						</div>
						<?php echo ae_ContentOfMedia::MediaPreviewImage( $media ); ?>
					</div>

					<div class="maininfo">
						<?php echo ae_ContentOfMedia::MediaTitle( $media ) ?>
						<!--
						<div class="uploader">Uploader: <strong><?php echo $media->file_uploader(); ?></strong></div>
						-->
						<?php if( $media->file_type_toplevel() == 'image' ): ?>
							<div class="format">Dimensions: <?php echo $media->image_dimensions(); ?></div>
						<?php endif; ?>
						<div class="filesize">Filesize: <?php echo $media->file_size(); ?></div>
						<div class="tags">Tags: <?php echo $tags; ?></div>
						<div class="desc">Description: <?php echo $desc; ?></div>
					</div>

				</li>

			<?php endwhile; ?>
		</ul>
		<?php endif; ?>

		<div class="clear"></div>

		<div class="page-nav page-nav-bottom<?php echo $pageflip_empty ?>">
			<?php echo $pageflip; ?>
		</div>

	</form>

</div>

<?php } ?>