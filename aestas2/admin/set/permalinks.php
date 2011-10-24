<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


$user = ae_User::getUserById( ae_Permissions::getIdOfCurrentUser() );
$author_default = ae_URL::ProcessString( $user->getName() );
$user = ae_User::getUserById( ae_Permissions::getIdOfCurrentUser() );
$author_pl = ae_URL::ProcessString( $user->getName() );


// Permalink post

$ch_post_de = $ch_post_dt = $ch_post_cu = '';

switch( ae_URL::StructureOfPost() ) {
	case 'default':
		$ch_post_de = ' checked="checked"';
		break;
	case '%year%/%month%/%day%/%postname%':
		$ch_post_dt = ' checked="checked"';
		break;
	default:
		$ch_post_cu = ' checked="checked"';
}


// Permalink page

$ch_page_de = $ch_page_ti = $ch_page_cu = '';

switch( ae_URL::StructureOfPage() ) {
	case 'default':
		$ch_page_de = ' checked="checked"';
		break;
	case '%pagename%':
		$ch_page_ti = ' checked="checked"';
		break;
	default:
		$ch_page_cu = ' checked="checked"';
}


// Permalink author

$ch_auth_de = $ch_auth_bn = $ch_auth_cu = '';

switch( ae_URL::StructureOfAuthor() ) {
	case 'default':
		$ch_auth_de = ' checked="checked"';
		break;
	case 'author/%authorname%':
		$ch_auth_bn = ' checked="checked"';
		break;
	default:
		$ch_auth_cu = ' checked="checked"';
}


// Permalink category

$ch_cat_de = $ch_cat_bt = $ch_cat_cu = '';

switch( ae_URL::StructureOfCategory() ) {
	case 'default':
		$ch_cat_de = ' checked="checked"';
		break;
	case 'category/%catname%':
		$ch_cat_bt = ' checked="checked"';
		break;
	default:
		$ch_cat_cu = ' checked="checked"';
}


// Permalink blog page

$ch_bp_de = $ch_bp_bn = $ch_bp_cu = '';

switch( ae_URL::StructureOfBlogpage() ) {
	case 'default':
		$ch_bp_de = ' checked="checked"';
		break;
	case 'page/%pagenumber%':
		$ch_bp_bn = ' checked="checked"';
		break;
	default:
		$ch_bp_cu = ' checked="checked"';
}


// Permalink tag

$ch_tag_de = $ch_tag_bt = $ch_tag_cu = '';

switch( ae_URL::StructureOfTag() ) {
	case 'default':
		$ch_tag_de = ' checked="checked"';
		break;
	case 'tag/%tagname%':
		$ch_tag_bt = ' checked="checked"';
		break;
	default:
		$ch_tag_cu = ' checked="checked"';
}

?>


<div id="set">

	<h1>Permalinks</h1>

	<p class="cleaninfo">
		Permalink structures other than the default one need the PHP module <code>mod_rewrite</code> to work.
	</p>


	<form accept-charset="utf-8" action="set/change-permalinks.php" method="post">

		<fieldset class="permalinks">
			<legend>Permalink post</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-post" value="default" id="post-default"<?php echo $ch_post_de ?> />
						<label for="post-default">Default <code><span><?php echo URL ?></span>/?p=48</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-post" value="day-title" id="post-day-title"<?php echo $ch_post_dt ?> />
						<label for="post-day-title">Day and title <code><span><?php echo URL ?></span>/2009/11/21/my-post/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-post" value="custom" id="post-custom"<?php echo $ch_post_cu ?> />
						<label for="post-custom">Custom</label>
						<ul<?php echo empty( $ch_post_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-post" type="text" value="<?php echo ae_URL::StructureOfPost() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-post<?php echo empty( $ch_post_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%id%</code>, <code>%year%</code>, <code>%month%</code>,
					<code>%day%</code> and <code>%postname%</code>.
				</p>
			</div>
		</fieldset>

		<fieldset>
			<legend>Existing post permalinks</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" id="pls-post-renew" name="pls-post-renew" value="true" />
						<label for="pls-post-renew">Renew all old post permalinks when changing the pattern.</label>
					</li>
				</ul>
			</div>
		</fieldset>

		<hr />

		<fieldset class="permalinks">
			<legend>Permalink page</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-page" value="default" id="page-default"<?php echo $ch_page_de ?> />
						<label for="page-default">Default <code><span><?php echo URL ?></span>/?page_id=2</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-page" value="title" id="page-title"<?php echo $ch_page_ti ?> />
						<label for="page-title">Title <code><span><?php echo URL ?></span>/my-page/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-page" value="custom" id="page-custom"<?php echo $ch_page_cu ?> />
						<label for="page-custom">Custom</label>
						<ul<?php echo empty( $ch_page_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-page" type="text" value="<?php echo ae_URL::StructureOfPage() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-page<?php echo empty( $ch_page_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%id%</code>, <code>%year%</code>, <code>%month%</code>,
					<code>%day%</code> and <code>%pagename%</code>.
				</p>
			</div>
		</fieldset>

		<fieldset>
			<legend>Existing page permalinks</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" id="pls-page-renew" name="pls-page-renew" value="true" />
						<label for="pls-page-renew">Renew all old page permalinks when changing the pattern.</label>
					</li>
				</ul>
			</div>
		</fieldset>

		<hr />

		<fieldset class="permalinks">
			<legend>Permalink page (in blog)</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-bp" value="default" id="bp-default"<?php echo $ch_bp_de ?> />
						<label for="bp-default">Default <code><span><?php echo URL ?></span>/?page=4</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-bp" value="base-number" id="bp-base-number"<?php echo $ch_bp_bn ?> />
						<label for="bp-base-number">Base and number <code><span><?php echo URL ?></span>/page/4/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-bp" value="custom" id="bp-custom"<?php echo $ch_bp_cu ?> />
						<label for="bp-custom">Custom</label>
						<ul<?php echo empty( $ch_bp_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-bp" type="text" value="<?php echo ae_URL::StructureOfBlogpage() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-bp<?php echo empty( $ch_bp_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%pagenumber%</code>.
				</p>
			</div>
		</fieldset>

		<hr />

		<fieldset class="permalinks">
			<legend>Permalink author</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-auth" value="default" id="auth-default"<?php echo $ch_auth_de ?> />
						<label for="auth-default">Default <code><span><?php echo URL ?></span>/?author=<?php echo $author_default ?></code></label>
					</li>
					<li>
						<input type="radio" name="permalink-auth" value="base-name" id="auth-base-name"<?php echo $ch_auth_bn ?> />
						<label for="auth-base-name">Base and name <code><span><?php echo URL ?></span>/author/<?php echo $author_pl ?>/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-auth" value="custom" id="auth-custom"<?php echo $ch_auth_cu ?> />
						<label for="auth-custom">Custom</label>
						<ul<?php echo empty( $ch_auth_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-auth" type="text" value="<?php echo ae_URL::StructureOfAuthor() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-auth<?php echo empty( $ch_auth_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%authorname%</code>.
				</p>
			</div>
		</fieldset>

		<fieldset>
			<legend>Existing author permalinks</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" id="pls-author-renew" name="pls-author-renew" value="true" />
						<label for="pls-author-renew">Renew all old author permalinks when changing the pattern.</label>
					</li>
				</ul>
			</div>
		</fieldset>

		<hr />

		<fieldset class="permalinks">
			<legend>Permalink category</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-cat" value="default" id="cat-default"<?php echo $ch_cat_de ?> />
						<label for="cat-default">Default <code><span><?php echo URL ?></span>/?category=3</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-cat" value="base-title" id="cat-base-title"<?php echo $ch_cat_bt ?> />
						<label for="cat-base-title">Base and title <code><span><?php echo URL ?></span>/category/my-category/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-cat" value="custom" id="cat-custom"<?php echo $ch_cat_cu ?> />
						<label for="cat-custom">Custom</label>
						<ul<?php echo empty( $ch_cat_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-cat" type="text" value="<?php echo ae_URL::StructureOfCategory() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-cat<?php echo empty( $ch_cat_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%catname%</code>.
				</p>
			</div>
		</fieldset>

		<fieldset>
			<legend>Existing category permalinks</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" id="pls-cat-renew" name="pls-cat-renew" value="true" />
						<label for="pls-cat-renew">Renew all old category permalinks when changing the pattern.</label>
					</li>
				</ul>
			</div>
		</fieldset>

		<hr />

		<fieldset class="permalinks">
			<legend>Permalink tags</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="permalink-tag" value="default" id="tag-default"<?php echo $ch_tag_de ?> />
						<label for="tag-default">Default <code><span><?php echo URL ?></span>/?tag=my-tag</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-tag" value="base-title" id="tag-base-title"<?php echo $ch_tag_bt ?> />
						<label for="tag-base-title">Base and title <code><span><?php echo URL ?></span>/tag/my-tag/</code></label>
					</li>
					<li>
						<input type="radio" name="permalink-tag" value="custom" id="tag-custom"<?php echo $ch_tag_cu ?> />
						<label for="tag-custom">Custom</label>
						<ul<?php echo empty( $ch_tag_cu ) ? ' class="hideonload"' : ''; ?>>
							<li><input name="custom-tag" type="text" value="<?php echo ae_URL::StructureOfTag() ?>" /></li>
						</ul>
					</li>
				</ul>
				<p class="patterns-tag<?php echo empty( $ch_tag_cu ) ? ' hideonload' : ''; ?>">
					Patterns: <code>%tagname%</code>.
				</p>
			</div>
		</fieldset>

		<hr />

		<div class="submit">
			<input type="submit" value="save changes" />
		</div>

	</form>

</div>
