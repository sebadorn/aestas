<?php

$c = ae_Comment::getCommentById( $_GET['reply'] );

if( $c == null ) {
	echo ae_PageStructure::NotExisting( 'comment' );
}
else if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

$u = ae_User::getUserById( ae_Permissions::getIdOfCurrentUser() );

?>
<div id="create">

	<h1>Reply to Comment</h1>

	<div class="reply-to">
		<span class="name"><?php echo $c->getAuthor() ?></span>
		<?php echo $c->getContent() ?>
	</div>

	<form accept-charset="utf-8" action="manage/save-reply-comment.php" method="post">


		<div id="part-one">

			<fieldset id="precontent">
				<legend>Author</legend>
				<div>
					<label for="name">Name</label>
					<input id="name" name="name" type="text" value="<?php echo $u->getName() ?>" /><br />
					<label for="email">E-Mail</label>
					<input id="email" name="email" type="text" value="<?php echo $u->getEmail() ?>" /><br />
					<label for="website">Website</label>
					<input id="website" name="website" type="text" value="<?php echo $u->getUrl() ?>" />
				</div>
			</fieldset>

			<fieldset id="content">
				<legend>Content</legend>
				<div>
					<textarea name="content" cols="40" rows="14"></textarea>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" value="save" />
					<input name="reply_to_id" type="hidden" value="<?php echo $c->getId() ?>" />
				</div>
			</fieldset>

		</div>

	</form>

</div>

<?php } ?>