<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

ae_Permissions::Check( 'create', 'addcategory' );

?>
<div id="create" class="cr-category">

	<h1>Add Category</h1>


	<form accept-charset="utf-8" action="create/add-category.php" method="post">

		<div id="part-one">
			<fieldset id="title">
				<legend>Title</legend>
				<div>
					<input name="title" type="text" />
				</div>
			</fieldset>
		</div>

		<div id="part-two">
			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" value="save" />
				</div>
			</fieldset>
		</div>

		<div id="ext_options">

			<ul id="ext_nav">
				<li class="active">Parent</li>
				<li>Permalink</li>
			</ul>

			<div class="parent">
				<ul>
					<li class="default">
						<input id="cat-0" name="cat" type="radio" value="0" checked="checked" />
						<label for="cat-0">none</label>
					</li>
					<?php echo ae_Misc::ListCategories( 'radio', array( 1 ) ) ?>
				</ul>
				<p>Parent category for the new one.</p>
			</div>

			<div class="permalink hideonload">
				<p class="sug" class="hideifnojs">
					Suggestion: <?php echo URL . '/' . ae_URL::Category2Permalink( 0, '' ) ?><span></span>
				</p>
				<input name="permalink" type="text" />
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
