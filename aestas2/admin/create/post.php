<?php
if( !defined( 'ROLE') ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}

ae_Permissions::Check( 'create', 'addpost' );
?>

<script src="interface/js/tab.js"></script>
<script src="interface/js/tags.js"></script>

<form class="main-content-wrapper create" accept-charset="utf-8" action="create/add-post.php" method="post">

	<div class="midth">
		<fieldset class="title">
			<legend>title</legend>
			<div>
				<input name="title" type="text" />
			</div>
		</fieldset>

		<fieldset class="content">
			<legend>Content</legend>
			<div>
				<textarea name="content"></textarea>
			</div>
		</fieldset>
	</div>

	<aside class="save">
		<button class="button icon icon-docfill" type="submit" name="draft">save draft</button>
		<button class="button icon icon-penfill" type="submit" name="publish">publish</button>
	</aside>

	<div class="timing" style="display: none;">
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

	<hr />

	<section class="tab-section">
		<!-- tabs -->
		<ul class="tab-trigger">
			<li data-tab-trigger="categories" class="active">Categories</li>
			<li data-tab-trigger="tags">Tags</li>
			<li data-tab-trigger="desc">Desc</li>
			<li data-tab-trigger="excerpt">Excerpt</li>
			<li data-tab-trigger="tracks">Tracks</li>
			<li data-tab-trigger="protect">Protect</li>
			<li data-tab-trigger="permalink">Permalink</li>
			<li data-tab-trigger="more">More</li>
		</ul>

		<!-- tab panel -->
		<div data-tab-panel="categories" class="tab-panel categories active">
			<ul>
				<?php echo ae_Misc::ListCategories( 'checkbox' ) ?>
			</ul>
		</div>

		<!-- tab panel -->
		<div data-tab-panel="tags" class="tab-panel tags">
			<input id="add-tags" name="tags" type="text" />
			<ul id="tag-listing"></ul>
			<p class="hint">Multiple tags can be seperated with semicolons (;).</p>
		</div>

		<!-- tab panel -->
		<div data-tab-panel="desc" class="tab-panel desc">
			<textarea name="desc"></textarea>
			<p class="hint">Description. Used in in the <code>meta</code> tag with the same name. Should briefly summarize the content.</p>
		</div>

		<!-- tab panel -->
		<div data-tab-panel="excerpt" class="tab-panel excerpt">
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
			<p class="hint">
				The excerpt can contain for example the first two paragraphs of your content.
				But as long as you do not intend to use it as preview or in the feed, you can as well leave it blank.
				The excerpt can contain HTML.
			</p>
		</div>

		<!-- tab panel -->
		<div data-tab-panel="tracks" class="tab-panel tracks">
			<input name="tracks" type="text" />
			<input class="hideifnojs" type="button" value="add to list" />
			<p class="hint">
				A trackback will leave something like a comment on the listed posts.
				It contains the name of your post, a link to the post and a veeery short excerpt.
				Multiple URLs can be separated with white spaces.
			</p>
		</div>

		<!-- tab panel -->
		<div data-tab-panel="protect" class="tab-panel protect">
			<input name="protect" type="password" />
			<input class="hideifnojs" type="text" />
			<input class="hideifnojs" type="button" value="security check" />
			<ul class="hideifnojs">
				<li>
					<input type="checkbox" id="cleartext" />
					<label for="cleartext">show as cleartext</label>
				</li>
			</ul>
			<p class="hint">Just type your password in here.</p>
		</div>

		<!-- tab panel -->
		<?php if( ae_URL::StructureOfPost() != 'default' ) : ?>
		<div data-tab-panel="permalink" class="tab-panel permalink">
			<p class="sug hideifnojs">
				Suggestion: <?php echo URL . '/' . ae_URL::Post2Permalink( 0, '', date( 'Y' ), date( 'm' ), date( 'd' ) ) ?><span></span>
			</p>
			<input name="permalink" type="text" />
			<input class="hideifnojs" type="button" value="validate" />
			<p id="permalink"></p>
			<p class="hint">
				An address easier to remember.
				You can use alphanumeric characters (a-z and 0-9) and the minus (-).<br />
				<code>mod_rewrite</code> has to be enabled.
			</p>
		</div>
		<?php endif; ?>

		<!-- tab panel -->
		<div data-tab-panel="more" class="tab-panel more">
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
	</section>

</form>