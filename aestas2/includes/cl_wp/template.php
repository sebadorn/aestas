<?php

// TODO: wp_link_pages
function wp_link_pages() {
	echo '';
}

// TODO: params $args and $post_id
function comment_form( $args = '', $post_id = null ) {
	global $comment_author, $comment_author_email, $comment_author_url;

	echo '<div>';
	comment_id_fields();
	echo '
			<label for="author">Name</label>
			<input id="author" name="author" type="text" value="' . $comment_author . '" /><br />

			<label for="email">Mail</label>
			<input id="email" name="email" type="text" value="' . $comment_author_email . '" /><br />

			<label for="url">URL</label>
			<input id="url" name="url" type="text" value="' . $comment_author_url . '" />

			<textarea name="comment" cols="60" rows="8"></textarea>

			<input type="submit" value="submit" />
		</div>';
}

function comments_template( $file = '', $seperate_comments = false ) {
	global $user_ID, $user_identity, $post;

	if( SINGLE_POST < 1 && PAGE_ID < 2 ) {
		return;
	}
	if( !comments_open() || post_password_required() ) {
		return;
	}

	/* These variables can be filled into the comment form, for example.
	 * They contain the name, email and url of someone if he has commented before.
	 */
	$author = ae_Cookies::CommentAuthorInfo();
	$comment_author = $author['name'];
	$comment_author_email = $author['email'];
	$comment_author_url = $author['url'];

	if( $file == '' ) {
		$file = '/comments.php';
	}
	include( 'themes/' . THEME . $file );
}

function get_footer( $name = '' ) {
	if( $name != '' ) {
		$name = '-' . $name;
	}
	include( 'themes/' . THEME . '/footer' . $name . '.php' );
}

function get_header( $name = '' ) {
	if( $name != '' ) {
		$name = '-' . $name;
	}
	include( 'themes/' . THEME . '/header' . $name . '.php' );
}

function get_search_form( $button_text = 'Search' ) {
	$value = ( SEARCH !== false ) ? htmlspecialchars( SEARCH ) : '';
	echo '
	<form action="' . URL . '" id="searchform" method="get">
		<div>
			<label class="screen-reader-text" for="s">Search for:</label>
			<input id="s" name="s" type="text" value="' . $value . '" />
			<input id="searchsubmit" type="submit" value="' . htmlspecialchars( $button_text ) . '" />
		</div>
	</form>
	';
}

function get_sidebar( $name = '' ) {
	if( $name != '' ) {
		$name = '-'.$name;
	}
	include( 'themes/' . THEME . '/sidebar' . $name . '.php' );
}

// TODO: wp_footer()
function wp_footer() {

}

function wp_logout_url() {
	return URL . '/admin/logout.php';
}

// TODO: register_nav_menus()
function register_nav_menus( $locations ) {
	
}

function get_template_directory() {
	return TEMPLATEPATH;
}

// TODO: callback parameter
function add_custom_image_header( $header_callback, $admin_header_callback, $admin_image_div_callback = null ) {
	$style = $path = $width = $height = '';

	if( defined( 'HEADER_TEXTCOLOR' ) && trim( HEADER_TEXTCOLOR ) != '' ) {
		$style = ' style="color: ' . HEADER_TEXTCOLOR . ' !important;"';
	}
	if( defined( 'HEADER_IMAGE' ) ) {
		$path = str_replace( '%s', TEMPLATEPATH, HEADER_IMAGE );
	}
	if( defined( 'HEADER_IMAGE_WIDTH' ) ) {
		$width = ' width="' . HEADER_IMAGE_WIDTH . 'px"';
	}
	if( defined( 'HEADER_IMAGE_HEIGHT' ) ) {
		$height = ' height="' . HEADER_IMAGE_HEIGHT . 'px"';
	}

	$header = array();
	$header['html'] = '<img src="' . $path . '"' . $style . $width . $height . ' alt="" />';
	$header['path'] = $path;

	ae_RequestCache::Save( 'add_custom_image_header', $header );
}

// TODO: get_header_image()
function get_header_image() {
	
}