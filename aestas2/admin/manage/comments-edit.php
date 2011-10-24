<?php

$c = ae_Comment::getCommentById( $_GET['edit'] );

if( $c == null ) {
	echo ae_PageStructure::NotExisting( 'comment' );
}
else if( !ae_Permissions::hasRights( $area, $content, $c ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	$check_appr = $check_spam = $check_trash = $check_unap = '';
	switch( $c->getStatus() ) {
		case 'approved':
			$check_appr = ' checked="checked"';
			break;
		case 'unapproved':
			$check_unap = ' checked="checked"';
			break;
		case 'spam':
			$check_spam = ' checked="checked"';
			break;
		case 'trash':
			$check_trash = ' checked="checked"';
			break;
	}

	$registered_user = ( $c->getUserId() > 0 ) ? ' checked="checked"' : '';

?>
<div id="create">

	<h1>Edit Comment</h1>

	<form accept-charset="utf-8" action="manage/save-edit-comment.php" method="post">

		<div id="part-one">

			<fieldset id="precontent">
				<legend>Author</legend>
				<div>
					<label for="name">Name</label>
					<input id="name" name="name" type="text" value="<?php echo $c->getAuthor() ?>" /><br />
					<?php if( !$c->isTrackback() ): ?>
					<div>
						<input id="user" name="user" type="checkbox" <?php echo $registered_user ?>/>
						<label for="user">Registered User</label>
						<span class="hideonload">
							<select name="userid">
								<?php echo ae_Misc::UsersForSelect( $c->getUserId() ) ?>
							</select>
						</span>
					</div>
					<?php endif; ?>
					<label for="email">E-Mail</label>
					<input id="email" name="email" type="text" value="<?php echo $c->getEmail() ?>" /><br />
					<label for="website">Website</label>
					<input id="website" name="website" type="text" value="<?php echo $c->getUrl() ?>" /><br />
					<label>IP</label>
					<input class="readonly" type="text" readonly="readonly" value="<?php echo $c->getIp() ?>" /><br />
					<label>ID</label>
					<input class="readonly" type="text" readonly="readonly" value="<?php echo $c->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="content">
				<legend>Content</legend>
				<div>
					<textarea name="content" cols="40" rows="14"><?php echo $c->getContentForTextarea() ?></textarea>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" value="save changes" />
					<input name="comment_id" type="hidden" value="<?php echo $c->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="status">
				<legend>Status</legend>
				<div>
					<ul>
						<li>
							<input type="radio" name="status" value="approved" id="approved"<?php echo $check_appr ?>/>
							<label for="approved">Approved</label>
						</li>
						<li>
							<input type="radio" name="status" value="unapproved" id="unapproved"<?php echo $check_unap ?>/>
							<label for="unapproved">Unapproved</label>
						</li>
						<li>
							<input type="radio" name="status" value="spam" id="spam"<?php echo $check_spam ?>/>
							<label for="spam">Spam</label>
						</li>
						<li>
							<input type="radio" name="status" value="trash" id="trash"<?php echo $check_trash ?>/>
							<label for="trash">Trash</label>
						</li>
					</ul>
				</div>
			</fieldset>

			<fieldset id="date">
				<legend>Date</legend>
				<div>
					<ul>
						<li>
							<input id="date_edit" name="change_date" type="checkbox" value="true" />
							<label for="date_edit">Change</label>
						</li>
					</ul>
					<p>Date the comment was submitted.</p>
				</div>
				<div class="manually hideonload">
					<select name="month">
						<?php echo ae_Misc::MonthsForSelect( date( 'm', $c->getDateTimestamp() ) ) ?>
					</select>
					<input name="day" type="text" value="<?php echo date( 'd', $c->getDateTimestamp() ) ?>" />,
					<input name="year" class="year" type="text" value="<?php echo date( 'Y', $c->getDateTimestamp() ) ?>" /> at
					<input name="hour" type="text" value="<?php echo date( 'H', $c->getDateTimestamp() ) ?>" /> :
					<input name="minute" type="text" value="<?php echo date( 'i', $c->getDateTimestamp() ) ?>" />
				</div>
			</fieldset>

		</div>

	</form>

</div>

<?php } ?>