<?php

$c = ae_Category::getCategoryById( $_GET['edit'] );

if( $c == null ) {
	echo ae_PageStructure::NotExisting( 'category' );
}
else if( !ae_Permissions::hasRights( $area, $content, $c ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	$chk_trash = ( $c->getStatus() == 'trash' ) ? ' checked="checked"' : '';

	$permalink_catname = ae_URL::ExtractCatnameFromPermalink( $c->getPermalink() );
	$permalink_suggestion = str_replace( $permalink_catname, '<span></span>', $c->getPermalink() );

?>

<div id="create">

	<h1>Edit Category</h1>

	<form accept-charset="utf-8" action="manage/save-edit-category.php" method="post">


		<div id="part-one">

			<fieldset id="title">
				<legend>Title</legend>
				<div>
					<input name="title" type="text" value="<?php echo $c->getNameHtml() ?>" />
				</div>
			</fieldset>

			<fieldset id="precontent">
				<legend>Info</legend>
				<div>
					<label style="cursor: default;">Author</label>
					<?php if( $c->getId() > 1 ) : ?>
					<input class="readonly" type="text" value="<?php echo $c->getAuthorName() ?>" readonly="readonly" /><br />
					<?php else : ?>
					<p>Default category</p><br />
					<?php endif; ?>
					<label style="cursor: default;">Sub Cats</label>
					<?php if( $c->getId() > 1 ) : ?>
					<input class="readonly" type="text" value="<?php echo $c->count_minions() ?>" readonly="readonly" /><br />
					<?php else : ?>
					<p>Not possible</p><br />
					<?php endif; ?>
					<label style="cursor: default;">Posts</label>
					<input class="readonly" type="text" value="<?php echo $c->count_posts() ?>" readonly="readonly" />
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" value="save" />
					<input name="cat_id" type="hidden" value="<?php echo $c->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="status">
				<legend>Status</legend>
				<div>
					<?php if( $c->getId() > 1 ) : ?>
					<input id="trash" name="trash" type="checkbox" value="true" <?php echo $chk_trash ?>/>
					<label for="trash">Move to Trash</label>
					<?php else : ?>
					<p>This category cannot be deleted.</p>
					<?php endif; ?>
				</div>
			</fieldset>

		</div>


		<div id="ext_options">

			<ul id="ext_nav">
				<?php if( $c->getId() > 1 ) : ?>
				<li class="active">Parent</li>
				<li>Permalink</li>
				<?php endif; ?>
				<?php if( $c->getId() == 1 ) : ?>
				<li class="active">Permalink</li>
				<?php endif; ?>
			</ul>

			<?php if( $c->getId() > 1 ) : ?>
			<div class="parent">
				<ul>
					<li class="default">
						<input id="cat-0" name="cat" type="radio" value="0" checked="checked" />
						<label for="cat-0">none</label>
					</li>
					<?php echo ae_Misc::ListCategories( 'radio', array( 1, $c->getId() ), $c->getParent() ) ?>
				</ul>
				<p>Parent category for the new one.</p>
			</div>
			<?php endif; ?>

			<div class="permalink hideonload">
				<p class="sug hideifnojs">
					Suggestion: http://<?php echo URL . '/' . $permalink_suggestion ?>
				</p>
				<input name="permalink" type="text" value="<?php echo $permalink_catname ?>" />
				<input class="hideifnojs" type="button" value="validate" />
				<p id="permalink"></p>
				<p>
					An address easier to remember.
					You can use alphanumeric characters (a-z and 0-9) and the minus (-).<br />
					<code>mod_rewrite</code> has to be enabled.
				</p>
			</div>

		</div>


	</form>

</div>
<?php }
