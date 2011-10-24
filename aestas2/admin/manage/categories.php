<?php

if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
		$_GET['page'] = 1;
	}
	define( 'PAGE', $_GET['page'] - 1 );

	$filter = ae_ContentOfCategory::FilterForCategories();

	$mcaq = new ae_ManageCategoryQuery( $filter );
	$cats_count = ae_ManageCategoryQuery::count_categories_bystatus( $filter['status'] );

?>


<div id="manage">

	<h1>Categories</h1>


	<ul id="status_filter">
		<?php echo ae_ContentOfCategory::StatusFilterCategoryNav() ?>
	</ul>


	<form accept-charset="utf-8" action="manage/apply.php" class="bulk_apply" method="post">

		<div class="bulk_apply">
		<?php if( ROLE == 'admin' || ROLE == 'author' ) : ?>
		Selected:
			<select name="bulk">
				<?php if( $filter['status'] == 'trash' ) : ?>
				<option value="active">restore</option>
				<?php endif; ?>
				<option value="trash">delete</option>
				<option value="merge">merge into one</option>
			</select>

			<input name="from" type="hidden" value="<?php echo $filter['status']; ?>" />
			<input name="show" type="hidden" value="<?php echo ae_PageStructure::getShowContent() ?>" />
			<input type="submit" value="Apply" />
		<?php endif; ?>
		</div>


		<?php if( $filter['status'] == 'trash' ) : ?>
		<p class="info warning">
			Categories that are deleted from here will be lost without return.
		</p>
		<?php endif; ?>


		<nav class="page-nav page-nav-top">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $mcaq->getLimit(), $cats_count ) ?>
		</nav>


		<?php if( $mcaq->have_cats() ) : ?>
		<table id="cats">
			<thead>
				<tr>
					<th></th>
					<th>Title</th>
					<th></th>
					<th>Posts in category</th>
					<th>Parent</th>
					<th>Sub categories</th>
				</tr>
			</thead>
			<tbody>
			<?php while( $mcaq->have_cats() ) : $mcaq->the_cat() ?>
				<tr class="cat<?php if( $mcaq->cat_ID() == 1 ) { echo ' uncategorized'; } ?>"
					id="<?php echo $mcaq->cat_ID() ?>">

					<td class="check">
						<input id="cat-<?php echo $mcaq->cat_ID() ?>" name="id[]"
								type="checkbox" value="<?php echo $mcaq->cat_ID() ?>" />
					</td>

					<td class="title">
						<a href="<?php echo $mcaq->cat_absolute_link() ?>">
							<?php echo $mcaq->cat_name() ?>
						</a>
					</td>

					<td>
						<span class="actions_trigger">
							<?php if( ROLE == 'admin' || ROLE == 'author' ) : ?>
							<div class="actions">
								<?php echo ae_ContentOfCategory::CategoryActions( $mcaq ) ?>
							</div>
							<?php endif; ?>
						</span>
					</td>

					<td class="count"><?php echo $mcaq->cat_count_posts() ?></td>

					<td class="parent"><?php echo $mcaq->cat_main() ?></td>

					<td class="count"><?php echo $mcaq->cat_count_minions() ?></td>

				</tr>
			<?php endwhile ?>
			</tbody>
		</table>
		<?php endif ?>


		<nav class="page-nav page-nav-bottom">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $mcaq->getLimit(), $cats_count ) ?>
		</nav>

	</form>

</div>

<?php } ?>