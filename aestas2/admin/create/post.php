<?php

if( !defined( 'ROLE') ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

ae_Permissions::Check( 'create', 'addpost' );

?>
<header class="content-menu">
	<h1>Add Post</h1>
</header>


<div class="content create">

	<form accept-charset="utf-8" action="create/add-post.php" method="post">


		<div id="part-one">

			<fieldset id="title">
				<legend>Title</legend>
				<div>
					<input name="title" type="text" />
				</div>
			</fieldset>

			<fieldset id="content">
				<legend>Content</legend>
				<div>
					<textarea id="editor" name="content" cols="40" rows="14"></textarea>
				</div>
			</fieldset>

		</div>


		<div id="part-two">

			<fieldset id="next">
				<legend>Next</legend>
				<div>
					<input type="submit" name="draft" value="save draft" /> and return
					<input type="submit" name="publish" value="publish" />
				</div>
			</fieldset>

			<fieldset id="date">
				<legend>Date</legend>
				<div class="when">
					<ul>
						<li>
							<input type="radio" name="date" value="imm" id="imm" checked="checked" />
							<label for="imm">publish immediately</label>
						</li>
						<li>
							<input type="radio" name="date" value="set" id="sched" />
							<label for="sched">schedule</label>
						</li>
					</ul>
				</div>
				<div class="manually hideonload">
					<select name="month">
						<?php echo ae_Misc::MonthsForSelect() ?>
					</select>
					<input name="day" type="text" value="<?php echo date('d') ?>" />,
					<input name="year" class="year" type="text" value="<?php echo date('Y') ?>" /> at
					<input name="hour" type="text" value="<?php echo date('H') ?>" /> :
					<input name="minute" type="text" value="<?php echo date('i') ?>" />
				</div>
				<div class="expires">
					<input id="expires" name="expires" type="checkbox" value="true" />
					<label for="expires">Expires</label>
				</div>
				<div class="expires_set hideonload">
					<select name="expires_month">
						<?php echo ae_Misc::MonthsForSelect() ?>
					</select>
					<input name="expires_day" type="text" value="<?php echo date('d') ?>" />,
					<input name="expires_year" class="year" type="text" value="<?php echo date('Y') ?>" /> at
					<input name="expires_hour" type="text" value="<?php echo date('H') ?>" /> :
					<input name="expires_minute" type="text" value="<?php echo date('i') ?>" />
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
					<?php echo ae_Misc::ListCategories( 'checkbox' ) ?>
				</ul>
				<span class="clear"></span>
			</div>

			<div class="tags hideonload">
				<input name="tags" type="text" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>Multiple tags can be seperated with semicolons (;).</p>
			</div>

			<div class="desc hideonload">
				<textarea name="desc" cols="40" rows="4"></textarea>
				<p>Description. Used in in the meta-tag with the same name. Should briefly summarize the content.</p>
			</div>

			<div class="excerpt hideonload">
				<textarea name="excerpt" cols="40" rows="5"></textarea>
				<ul>
					<li>
						<input name="exc-prev" type="checkbox" value="true" id="exc-prev" />
						<label for="exc-prev">use excerpt for preview</label>
					</li>
					<li>
						<input name="exc-news" type="checkbox" value="true" id="exc-news" />
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
				<input name="tracks" type="text" />
				<input class="hideifnojs" type="button" value="add to list" />
				<p>
					A trackback will leave something like a comment on the listed posts.
					It contains the name of your post, a link to the post and a veeery short excerpt.
					Multiple URLs can be separated with white spaces.
				</p>
			</div>

			<div class="protect hideonload">
				<input name="protect" type="password" />
				<input class="hideifnojs" type="text" />
				<input class="hideifnojs" type="button" value="security check" />
				<ul class="hideifnojs">
					<li>
						<input type="checkbox" id="cleartext" />
						<label for="cleartext">show as cleartext</label>
					</li>
				</ul>
				<p>Just type your password in here.</p>
			</div>

			<?php if( ae_URL::StructureOfPost() != 'default' ) : ?>
			<div class="permalink hideonload">
				<p class="sug hideifnojs">
					Suggestion: <?php echo URL . '/' . ae_URL::Post2Permalink( 0, '', date( 'Y' ), date( 'm' ), date( 'd' ) ) ?><span></span>
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
			<?php endif; ?>

			<div class="other hideonload">
				<ul>
					<li>
						<input name="disable-comm" type="checkbox" value="true" id="disable-comm" />
						<label for="disable-comm">disable comments</label>
					</li>
					<li>
						<input name="con-php" type="checkbox" value="true" id="con-php" />
						<label for="con-php">contains PHP</label>
					</li>
				</ul>
			</div>

		</div>


	</form>

</div>
