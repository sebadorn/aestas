<?php

$page = ae_Post::LoadById( $_GET['edit'] );

if( $page == null ) {
	echo ae_PageStructure::NotExisting( 'page' );
}
else if( !ae_Permissions::hasRights( $area, $content, $page ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	/* Date and expiration date */
	$chk_imm = $chk_set = $chk_expires = '';

	if( $page->getExpires() == '0000-00-00 00:00:00' || $page->getExpires() == null ) {
		$page->setExpires( date( 'Y-m-d H:i:s' ) );
	}
	else {
		$chk_expires = ' checked="checked"';
	}


	if( $page->getPublish() == 'scheduled' ) {
		$chk_set = ' checked="checked"';
	}
	else {
		$chk_imm = ' checked="checked"';
	}


	/* Permalink */
	$permalink_pagename = ae_URL::ExtractPagenameFromPermalink( $page->getPermalink() );
	$permalink_suggestion = str_replace( $permalink_pagename, '<span></span>', $page->getPermalink() );


	/* Other */
	$chk_com = ( $page->getCommentsEnabled() == 'open' ) ? '' : ' checked="checked"';
	$chk_php = ( $page->getContentType() == 'html' ) ? '' : ' checked="checked"';
	$chk_se = ( $page->getRobots() == 'index follow' ) ? '' : ' checked="checked"';
	$chk_nav = $page->getShowInList() ? ' checked="checked"' : '';

?>


<div id="create">

	<h1>Edit Page</h1>

	<form accept-charset="utf-8" action="manage/save-edit-page.php" method="post">


		<div id="part-one">

			<fieldset id="title">
				<legend>Title</legend>
				<div>
					<input name="title" type="text" value="<?php echo $page->getTitleHtml() ?>" />
				</div>
			</fieldset>

			<fieldset id="content">
				<legend>Content</legend>
				<div>
					<textarea id="editor" name="content" cols="40" rows="14"><?php echo $page->getContentForTextarea() ?></textarea>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" name="draft" value="save draft" /> and return
					<input type="submit" name="publish" value="publish" />
					<input name="page_id" type="hidden" value="<?php echo $page->getId() ?>" />
				</div>
			</fieldset>

			<fieldset id="date">
				<legend>Date</legend>
				<div class="when">
					<ul>
						<li>
							<input id="imm" name="date" type="radio" value="imm"<?php echo $chk_imm ?>/>
							<label for="imm">publish immediately</label>
						</li>
						<li>
							<input id="sched" name="date" type="radio" value="set"<?php echo $chk_set ?>/>
							<label for="sched">schedule</label>
						</li>
					</ul>
				</div>
				<div class="manually">
					<select name="month">
						<?php echo ae_Misc::MonthsForSelect( date( 'm', $page->getDateTimestamp() ) ) ?>
					</select>
					<input name="day" type="text" value="<?php echo date( 'd', $page->getDateTimestamp() ) ?>" />,
					<input name="year" class="year" type="text" value="<?php echo date( 'Y', $page->getDateTimestamp() ) ?>" /> at
					<input name="hour" type="text" value="<?php echo date( 'H', $page->getDateTimestamp() ) ?>" /> :
					<input name="minute" type="text" value="<?php echo date( 'i', $page->getDateTimestamp() ) ?>" />
				</div>
				<div class="expires">
					<input id="expires" name="expires" type="checkbox" value="true"<?php echo $chk_expires ?> />
					<label for="expires">Expires</label>
				</div>
				<div class="expires_set">
					<select name="expires_month">
						<?php echo ae_Misc::MonthsForSelect( date( 'm', $page->getExpiresTimestamp() ) ) ?>
					</select>
					<input name="expires_day" type="text" value="<?php echo date( 'd', $page->getExpiresTimestamp() ) ?>" />,
					<input name="expires_year" class="year" type="text" value="<?php echo date( 'Y', $page->getExpiresTimestamp() ) ?>" /> at
					<input name="expires_hour" type="text" value="<?php echo date( 'H', $page->getExpiresTimestamp() ) ?>" /> :
					<input name="expires_minute" type="text" value="<?php echo date( 'i', $page->getExpiresTimestamp() ) ?>" />
				</div>
			</fieldset>

		</div>


		<div id="ext_options">

			<ul id="ext_nav">
				<li class="active">Tags</li>
				<li>Desc</li>
				<li>Tracks</li>
				<li>Protect</li>
				<li>Permalink</li>
				<li>Hierarchy</li>
				<li>Other</li>
			</ul>

			<div class="tags">
				<input name="tags" type="text" value="<?php echo substr( $page->getKeywords(), 0, -1 ) ?>" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>Multiple tags can be seperated with semicolons (;).</p>
			</div>

			<div class="desc hideonload">
				<textarea name="desc" cols="40" rows="4"><?php echo $page->getDescriptionForTextarea() ?></textarea>
				<p>Description. Used in in the meta-tag with the same name. Should briefly summarize the content.</p>
			</div>

			<div class="tracks hideonload">
				<input name="tracks" type="text" value="<?php echo $page->getTrackbacksSend() ?>" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>
					A trackback will leave something like a comment on the listed posts.
					It contains the name of your post, a link to the post and a veeery short excerpt.
				</p>
			</div>

			<div class="protect hideonload">
				<input name="protect" type="password" value="<?php echo $page->getPassword() ?>" />
				<input class="hideifnojs" type="text" />
				<input class="hideifnojs" type="button" value="security check" />
				<ul class="hideifnojs">
					<li><input type="checkbox" id="cleartext" /><label for="cleartext">show as cleartext</label></li>
				</ul>
				<p>Just type your password in here.</p>
			</div>

			<div class="permalink hideonload">
				<p class="sug hideifnojs">
					Suggestion: http://<?php echo URL . '/' . $permalink_suggestion ?>
				</p>
				<input name="permalink" type="text" value="<?php echo $permalink_pagename ?>" />
				<input class="hideifnojs" type="button" value="validate" />
				<p id="permalink"></p>
				<p>
					An address easier to remember.
					You can use alphanumeric characters (a-z and 0-9) and the minus (-).
				</p>
			</div>

			<div class="hierarchy hideonload">
				<select name="parent">
					<optgroup label="None">
						<option value="0">---</option>
					</optgroup>
					<optgroup label="Page">
						<?php echo ae_Misc::PagesForSelect( $page->getParent() ) ?>
					</optgroup>
				</select>
				<p>The new page is a sub page of the selected one.</p>
			</div>

			<div class="other hideonload">
				<ul>
					<li>
						<input name="disable-comm" type="checkbox" value="true" id="disable-comm" <?php echo $chk_com ?>/>
						<label for="disable-comm">disable comments</label>
					</li>
					<li>
						<input name="con-php" type="checkbox" value="true" id="con-php" <?php echo $chk_php ?>/>
						<label for="con-php">contains PHP</label>
					</li>
					<li>
						<input name="list-nav" type="checkbox" value="true" id="list-nav" <?php echo $chk_nav ?>/>
						<label for="list-nav">show page in lists</label>
					</li>
					<li>
						<input name="se-en" type="checkbox" value="true" id="se-en" <?php echo $chk_se ?>/>
						<label for="se-en">do not list in search engines</label>
					</li>
				</ul>
			</div>

		</div>


	</form>

</div>

<?php } ?>