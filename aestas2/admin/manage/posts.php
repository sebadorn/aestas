<?php

if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
		$_GET['page'] = 1;
	}
	define( 'PAGE', $_GET['page'] - 1 );

	ae_ContentOfPost::Init( 'post' );
	$filter = ae_ContentOfPost::Filter();
	$mpq = new ae_ManagePostQuery( $filter );

?>


<div id="manage">

	<h1>Posts</h1>


	<ul id="status_filter">
		<?php echo ae_ContentOfPost::StatusFilterNav() ?>
	</ul>


	<form accept-charset="utf-8" action="manage/apply.php" class="bulk_apply" method="post">

		<div class="bulk_apply">
		<?php if( ROLE == 'admin' || ROLE == 'author' || ROLE == 'guest' ) : ?>
			Selected:
			<select name="bulk">
				<option value="published">publish</option>
				<option value="draft">mark as draft</option>
				<option value="trash">delete</option>
			</select>

			<input name="from" type="hidden" value="<?php echo $filter['status']; ?>" />
			<input name="show" type="hidden" value="<?php echo ae_PageStructure::getShowContent() ?>" />
			<input type="submit" name="submit" value="Apply" />
		<?php endif; ?>
		</div>


		<?php if( $filter['status'] == 'trash' ) : ?>
		<p class="info warning">
			Posts that are deleted from here will be lost without return.<br />
			<strong>All comments</strong> to these posts will also be deleted.<br />
			Recieved trackbacks will not be affected.
		</p>
		<?php endif; ?>


		<nav class="page-nav page-nav-top">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $mpq->getLimit(), $mpq->count_posts() ); ?>
		</nav>


		<?php if( $mpq->have_posts() ) : ?>

		<table id="posts">
			<thead>
				<tr>
					<th></th>
					<th>ID</th>
					<th>Title</th>
					<th></th>
					<th>Comments</th>
					<th>Published</th>
					<th>Last edit</th>
				</tr>
			</thead>
			<tbody>
			<?php while( $mpq->have_posts() ) : $mpq->the_post() ?>
				<tr class="<?php echo ae_ContentOfPost::Classes( $mpq ) ?>" id="<?php echo $mpq->the_ID() ?>">

					<td class="check">
						<input type="checkbox" name="id[]" value="<?php echo $mpq->the_ID() ?>" />
					</td>

					<td class="id"><?php echo $mpq->the_ID() ?></td>

					<td class="title">
						<?php if( $mpq->post_status() == 'trash' ): ?>
							<?php echo $mpq->the_title( '', '', false ) ?>
						<?php else: ?>
						<a href="http://<?php echo $mpq->the_permalink() ?>">
							<?php echo $mpq->the_title( '', '', false ) ?>
						</a>
						<?php endif; ?>
						<?php echo $mpq->post_has_expired() ? '<span class="expired">expired</span>' : '' ?>
					</td>

					<td class="action_cell">
						<div class="actions_trigger">
							<?php if( ae_Permissions::hasPermissionToTakeActionsForPost( $mpq ) ) : ?>
							<div class="actions">
								<?php echo ae_ContentOfPost::Actions( $mpq ) ?>
							</div>
							<?php endif; ?>
						</div>
					</td>

					<td class="count"><?php echo ae_ContentOfPost::Comments( $mpq ) ?></td>

					<td class="date">
						<span title="<?php echo $mpq->the_time( 'Y-m-d H:i:s' ) ?>">
							<?php echo $mpq->the_time( 'jS M, Y' ) ?>
						</span>
					</td>

					<td class="date">
						<span title="<?php echo $mpq->post_lastedit( 'Y-m-d H:i:s' ) ?>">
							<?php echo $mpq->post_lastedit( 'jS M, Y' ) ?>
						</span>
					</td>

				</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
		<?php endif; ?>


		<div class="page-nav page-nav-bottom">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $mpq->getLimit(), $mpq->count_posts() ); ?>
		</div>


	</form>

</div>

<?php } ?>