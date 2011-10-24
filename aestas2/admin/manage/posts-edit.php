<?php

$post = ae_Post::LoadById( $_GET['edit'] );

if( $post == null ) {
	echo ae_PageStructure::NotExisting( 'post' );
}
else if( !ae_Permissions::hasRights( $area, $content, $post ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	/* Date and expiration date */
	$check_imm = $check_set = $expires = '';

	if( $post->getExpires() == '0000-00-00 00:00:00' || $post->getExpires() == null ) {
		$post->setExpires( date( 'Y-m-d H:i:s' ) );
	}
	else {
		$expires = ' checked="checked"';
	}


	if( $post->getPublish() == 'scheduled' ) {
		$check_set = ' checked="checked"';
	}
	else {
		$check_imm = ' checked="checked"';
	}


	/* Excerpt */
	$check_prev = $post->getContentPreview() ? ' checked="checked"' : '';
	$check_news = $post->getNewsfeedPreview() ? ' checked="checked"' : '';


	/* Other */
	$check_com = $post->getCommentsEnabled() ? '' : ' checked="checked"';
	$check_php = ( $post->getContentType() == 'html' ) ? '' : ' checked="checked"';


	/* Categories */
	$query = ae_Database::Query( '
		SELECT
			that_id
		FROM `' . TABLE_RELATIONS . '`
		WHERE this_id = ' . mysql_real_escape_string( $_GET['edit'] ) . '
		AND relation_type = "post to cat"
	' );

	$check = array();
	while( $cat = mysql_fetch_assoc( $query ) ) {
		$check[] = $cat['that_id'];
	}


	/* Permalink */
	$permalink_postname = ae_URL::ExtractPostnameFromPermalink( $post->getPermalink() );
	$permalink_suggestion = str_replace( $permalink_postname, '<span></span>', $post->getPermalink() );

?>
<div id="create">

	<h1>Edit Post</h1>

	<form accept-charset="utf-8" action="manage/save-edit-post.php" method="post">


		<div id="part-one">

			<fieldset id="title">
				<legend>Title</legend>
				<div>
					<input name="title" type="text" value="<?php echo $post->getTitleHtml() ?>" />
				</div>
			</fieldset>

			<fieldset id="content">
				<legend>Content</legend>
				<div>
					<textarea name="content" id="editor" cols="40" rows="14"><?php echo $post->getContentForTextarea() ?></textarea>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" name="draft" value="save draft" /> and return
					<input type="submit" name="publish" value="publish" />
					<input name="post_id" type="hidden" value="<?php echo $post->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="date">
				<legend>Date</legend>
				<div class="when">
					<ul>
						<li>
							<input id="imm" name="date" type="radio" value="imm" <?php echo $check_imm ?>/>
							<label for="imm">publish immediately</label>
						</li>
						<li>
							<input id="sched" name="date" type="radio" value="set" <?php echo $check_set ?>/>
							<label for="sched">schedule</label>
						</li>
					</ul>
				</div>
				<div class="manually">
					<select name="month">
						<?php echo ae_Misc::MonthsForSelect( date( 'm', $post->getDateTimestamp() ) ) ?>
					</select>
					<input name="day" type="text"
						   value="<?php echo date( 'd', $post->getDateTimestamp() ) ?>" />,
					<input name="year" class="year" type="text"
						   value="<?php echo date( 'Y', $post->getDateTimestamp() ) ?>" /> at
					<input name="hour" type="text"
						   value="<?php echo date( 'H', $post->getDateTimestamp() ) ?>" /> :
					<input name="minute" type="text"
						   value="<?php echo date( 'i', $post->getDateTimestamp() ) ?>" />
				</div>
				<div class="expires">
					<input id="expires" name="expires" type="checkbox" value="true" <?php echo $expires ?>/>
					<label for="expires">Expires</label>
				</div>
				<div class="expires_set">
					<select name="expires_month">
						<?php echo ae_Misc::MonthsForSelect( date( 'm', $post->getExpiresTimestamp() ) ) ?>
					</select>
					<input name="expires_day" type="text"
						   value="<?php echo date( 'd', $post->getExpiresTimestamp() ) ?>" />,
					<input name="expires_year" class="year" type="text"
						   value="<?php echo date( 'Y', $post->getExpiresTimestamp() ) ?>" /> at
					<input name="expires_hour" type="text"
						   value="<?php echo date( 'H', $post->getExpiresTimestamp() ) ?>" /> :
					<input name="expires_minute" type="text"
						   value="<?php echo date( 'i', $post->getExpiresTimestamp() ) ?>" />
				</div>
			</fieldset>

		</div>


		<div id="ext_options">

			<ul id="ext_nav">
				<li class="active">Categories</li>
				<li>Tags</li>
				<li>Desc</li>
				<li>Excerpt</li>
				<li>Tracks</li>
				<li>Protect</li>
				<li>Permalink</li>
				<li>Other</li>
			</ul>

			<div class="categories">
				<ul>
					<?php echo ae_Misc::ListCategories( 'checkbox', array( 0 ), $check ) ?>
				</ul>
			</div>

			<div class="tags hideonload">
				<input name="tags" type="text" value="<?php echo substr( $post->getKeywords(), 0, -1 ) ?>" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>Multiple tags can be seperated with semicolons (;).</p>
			</div>

			<div class="desc hideonload">
				<textarea name="desc" cols="40" rows="4"><?php echo $post->getDescriptionForTextarea() ?></textarea>
				<p>Description. Used in in the meta-tag with the same name. Should briefly summarize the content.</p>
			</div>

			<div class="excerpt hideonload">
				<textarea name="excerpt" cols="40" rows="5"><?php echo $post->getExcerptForTextarea() ?></textarea>
				<ul>
					<li>
						<input name="exc-prev" type="checkbox" value="true" id="exc-prev" <?php echo $check_prev ?>/>
						<label for="exc-prev">use excerpt for preview</label>
					</li>
					<li>
						<input name="exc-news" type="checkbox" value="true" id="exc-news" <?php echo $check_news ?>/>
						<label for="exc-news">use excerpt in newsfeed</label>
					</li>
				</ul>
				<p>
					The excerpt can contain for example the first two paragraphs of your content.
					But as long as you do not intend to use it as preview or in the feed you can as well leave it blank.
					The excerpt can contain XHTML.
				</p>
			</div>

			<div class="tracks hideonload">
				<input name="tracks" type="text" value="<?php echo $post->getTrackbacksSend() ?>" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>
					A trackback will leave something like a comment on the listed posts.
					It contains the name of your post, a link to the post and a veeery short excerpt.
					Multiple URLs can be separated with white spaces.
				</p>
			</div>

			<div class="protect hideonload">
				<input name="protect" type="password" value="<?php echo $post->getPassword() ?>" />
				<input class="hideifnojs" type="text" value="<?php echo $post->getPassword() ?>" />
				<input class="hideifnojs" type="button" value="security check" />
				<ul class="hideifnojs">
					<li>
						<input type="checkbox" id="cleartext" />
						<label for="cleartext">show as cleartext</label>
					</li>
				</ul>
				<p>Just type your password in here.</p>
			</div>

			<div class="permalink hideonload">
				<p class="sug hideifnojs">
					Suggestion: http://<?php echo URL . '/' . $permalink_suggestion ?>
				</p>
				<input name="permalink" type="text" value="<?php echo $permalink_postname ?>" />
				<input class="hideifnojs" type="button" value="validate" />
				<p id="permalink"></p>
				<p>
					An address easier to remember.
					You can use alphanumeric characters (a-z and 0-9) and the minus (-).<br />
					<code>mod_rewrite</code> has to be enabled.
				</p>
			</div>

			<div class="other hideonload">
				<ul>
					<li>
						<input name="disable-comm" type="checkbox" value="true" id="disable-comm" <?php echo $check_com ?>/>
						<label for="disable-comm">disable comments</label>
					</li>
					<li>
						<input name="con-php" type="checkbox" value="true" id="con-php" <?php echo $check_php ?>/>
						<label for="con-php">contains PHP</label>
					</li>
				</ul>
			</div>

		</div>


	</form>

</div>

<?php } ?>