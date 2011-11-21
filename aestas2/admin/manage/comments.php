<?php

if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
		$_GET['page'] = 1;
	}
	define( 'PAGE', $_GET['page'] - 1 );

	$filter = ae_ContentOfComment::FilterForComments();
	$mcq = new ae_ManageCommentByPostQuery( $filter );
	$pageflip = ae_PageStructure::BuildPageflip( PAGE, $mcq->getLimit(), $mcq->count_comments() );

?>


<div id="manage">

	<h1>Comments</h1>

	<ul id="status_filter">
		<?php echo ae_ContentOfComment::StatusFilterCommentNav() ?>
	</ul>


	<form accept-charset="utf-8" action="manage/apply.php" class="bulk_apply" method="post">

		<div class="bulk_apply">
			Selected:
			<select name="bulk">
				<option value="approved">approve</option>
				<option value="unapproved">unapprove</option>
				<option value="edit">edit</option>
				<option value="spam">mark as spam</option>
				<option value="trash">delete</option>
			</select>

			<input name="from" type="hidden" value="<?php echo $filter['status'] ?>" />
			<input name="show" type="hidden" value="<?php echo ae_PageStructure::getShowContent() ?>" />
			<input type="submit" value="Apply" />
		</div>


		<?php if( $filter['status'] == 'trash' ) : ?>
		<p class="info warning">
			Comments that are deleted from here will be lost without return.
		</p>
		<?php endif; ?>


		<nav class="page-nav page-nav-top"><?php echo $pageflip; ?></nav>


		<?php if( $mcq->have_posts() ) : ?>

		<ul id="comments">
		<?php while( $mcq->have_posts() ) : ?>
			<?php $mcq->the_post(); ?>

			<li class="post">
				<div>
					<span class="title"><?php echo $mcq->post_title(); ?></span>
					<span class="date"><?php echo $mcq->post_date( 'd.m.Y H:i' ); ?></span>
				</div>

				<ul>
				<?php while( $mcq->have_comments() ) : ?>
					<?php $mcq->the_comment(); ?>

					<li class="comment <?php echo $mcq->comment_status(); ?>">
						<input type="checkbox" name="id[]" value="<?php echo $mcq->comment_ID(); ?>" />

						<?php if( ae_Permissions::hasPermissionToTakeActionsForComment( $mcq ) ) : ?>
						<div class="actions_trigger">
							<div class="actions">
								<?php echo ae_ContentOfComment::CommentActions( $mcq ) ?>
							</div>
						</div>
						<?php endif; ?>

						<ul class="tabs">
							<li class="active">Author</li>
							<li>Info</li>
							<li>Content</li>
						</ul>

						<div class="tab tab-author">
							<?php if( $mcq->is_trackback() ) : ?>
							<span class="avatar avatar-trackback" title="Trackback"></span>
							<?php else : ?>
								<?php
								$url = 'http://' . URL . '/admin/interface/img/comment.png';
								echo $mcq->get_avatar( $mcq->comment_author_email(), 48, $url );
								?>
							<?php endif; ?>

							<div class="author-info">
								<span class="author">
									<strong><?php echo $mcq->comment_author() ?></strong>
								</span>
								<span class="email">
									<?php echo $mcq->comment_author_email_link(); ?>
								</span>
								<span class="url">
									<?php
									$url = str_replace( 'http://', '', $mcq->comment_author_url() );
									echo $mcq->comment_author_url_link( $url );
									?>
								</span>
							</div>
						</div>

						<div class="tab tab-info">
							<table>
								<tr>
									<th>Comment ID</th>
									<td><?php echo $mcq->comment_ID(); ?></td>
								</tr>
								<tr>
									<th>IP address</th>
									<td><?php echo $mcq->comment_author_IP(); ?></td>
								</tr>
								<tr>
									<th>Timestamp</th>
									<td><?php echo $mcq->comment_date( 'd.m.Y H:i:s' ); ?></td>
								</tr>
								<tr>
									<th>Permalink</th>
									<td>
										<a href="http://<?php echo str_replace( '/admin', '', $mcq->comment_permalink() ); ?>">
											<?php echo $mcq->post_title(); ?>
										</a>
									</td>
								</tr>
							</table>
						</div>

						<div class="tab tab-content">
							<?php echo strip_tags( $mcq->comment_text(), '<br>' ); ?>
						</div>
					</li>

				<?php endwhile; ?>
				</ul>
			</li>

		<?php endwhile; ?>
		</ul>

		<?php endif ?>

		<nav class="page-nav page-nav-bottom"><?php echo $pageflip; ?></nav>

	</form>

</div>

<?php } ?>