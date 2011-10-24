<?php


function have_comments() {
	return ae_EngineGateway::CallForComments( 'have_comments' );
}

function the_comment() {
	return ae_EngineGateway::CallForComments( 'the_comment' );
}

// TODO: get_comment_pages_count()
function get_comment_pages_count( $comments = null, $per_page = null, $threaded = null ) {
	return 0;
}

function wp_list_comments( $args = '' ) {
	$defaults = array(
		'walker' => null,
		'max_depth' => '',
		'style' => 'ol',
		'callback' => null,
		'end-callback' => null,
		'type' => 'all',
		'page' => '',
		'per_page' => '',
		'avatar_size' => 32,
		'reverse_top_level' => null,
		'reverse_children' => ''
	);

	$params = args_string_to_array( $args, $defaults );

	echo ae_EngineGateway::CallForComments( 'wp_list_comments', $params );
}

function comment_link() {
	echo ae_EngineGateway::CallForComments( 'comment_permalink' );
}

function get_comment_link() {
	return ae_EngineGateway::CallForComments( 'comment_permalink' );
}

function comments_count( $status = '' ) {
	return ae_EngineGateway::CallForComments( 'comments_counts', $status );
}

function comments_link() {
	echo ae_EngineGateway::CallForComments( 'comments_link' );
}

function comments_rss_link() {
	echo ae_EngineGateway::CallForComments( 'comments_rss_link' );
}

function comments_popup_script() {
	echo ae_EngineGateway::CallForComments( 'comments_popup_script' );
}

function comments_popup_link() {
	echo ae_EngineGateway::CallForComments( 'comments_popup_link' );
}

function comment_ID() {
	echo ae_EngineGateway::CallForComments( 'comment_ID' );
}

function comment_id_fields() {
	if( SINGLE_POST > 0 ) {
		$fields = '<input type="hidden" name="comment_post_ID"';
		$fields .= ' value="' . ae_EngineGateway::Call( 'the_ID' ) . '" id="comment_post_ID" />';
	}
	else {
		$fields = '<input type="hidden" name="comment_page_ID"';
		$fields .= ' value="' . ae_EngineGateway::Call( 'the_ID' ) . '" id="comment_post_ID" />';
	}
	echo $fields . PHP_EOL . '<input type="hidden" name="comment_parent" id="comment_parent" value="0" />';
}

function comment_author() {
	echo ae_EngineGateway::CallForComments( 'comment_author' );
}

function comment_author_link() {
	echo ae_EngineGateway::CallForComments( 'comment_author_link' );
}

function comment_author_email() {
	echo ae_EngineGateway::CallForComments( 'comment_author_email' );
}

function comment_author_email_link( $linktext = null, $before = '', $after = '' ) {
	echo ae_EngineGateway::CallForComments( 'comment_author_email_link', $linktext, $before, $after );
}

function comment_author_url() {
	echo ae_EngineGateway::CallForComments( 'comment_author_url' );
}

function comment_author_url_link( $linktext = null, $before = '', $after = '' ) {
	echo ae_EngineGateway::CallForComments( 'comment_author_url_link', $linktext, $before, $after );
}

function comment_author_IP() {
	echo ae_EngineGateway::CallForComments( 'comment_author_IP' );
}

function comment_type() {
	echo ae_EngineGateway::CallForComments( 'comment_type' );
}

function comment_text() {
	echo ae_EngineGateway::CallForComments( 'comment_text' );
}

function comment_excerpt( $length = 20, $dots = true, $html = 'encode' ) {
	echo ae_EngineGateway::CallForComments( 'comment_excerpt', $length, $dots, $html );
}

function comment_date( $format = 'F j, Y \a\t h:i a' ) {
	echo ae_EngineGateway::CallForComments( 'comment_date', $format );
}

function comment_time( $format = 'H:i:s' ) {
	echo ae_EngineGateway::CallForComments( 'comment_time', $format );
}

function comment_status() {
	return ae_EngineGateway::CallForComments( 'comment_status' );
}

function comment_form_title( $noreplytext = '', $replytext = '', $linktoparent = true ) {
	echo ae_EngineGateway::CallForComments( 'comment_form_title', $noreplytext, $replytext, $linktoparent );
}

function comment_author_rss() {
	echo ae_EngineGateway::CallForComments( 'comment_author_rss' );
}

function comment_text_rss() {
	echo ae_EngineGateway::CallForComments( 'comment_text_rss' );
}

function comment_link_rss() {
	echo ae_EngineGateway::CallForComments( 'comment_link_rss' );
}

function comment_comments_rss() {
	echo ae_EngineGateway::CallForComments( 'comment_comments_rss' );
}

function comment_reply_link() {
	echo ae_EngineGateway::CallForComments( 'comment_reply_link' );
}

function cancel_comment_reply_link() {
	echo ae_EngineGateway::CallForComments( 'cancel_comment_reply_link' );
}

function get_avatar( $id_or_email = '', $size = '96', $default = '' ) {
	return ae_EngineGateway::CallForComments( 'get_avatar', $id_or_email, $size, $default );
}

function get_comment_ID() {
	return ae_EngineGateway::CallForComments( 'comment_ID' );
}

function get_comment_date( $format = 'F j, Y \a\t h:i a' ) {
	return ae_EngineGateway::CallForComments( 'comment_date', $format );
}

function is_comment_author_user() {
	return ae_EngineGateway::CallForComments( 'is_comment_author_user' );
}

function is_trackback() {
	return ae_EngineGateway::CallForComments( 'is_trackback' );
}

function previous_comments_link() {
	echo ae_EngineGateway::CallForComments( 'previous_comments_link' );
}

function next_comments_link() {
	echo ae_EngineGateway::CallForComments( 'next_comments_link' );
}

function paginate_comments_links() {
	echo ae_EngineGateway::CallForComments( 'paginate_comments_links' );
}

function comment_post_ID() {
	echo ae_EngineGateway::CallForComments( 'comment_post_ID' );
}

function comment_post_title() {
	echo ae_EngineGateway::CallForComments( 'comment_post_title' );
}

function get_comment_author() {
	return ae_EngineGateway::CallForComments( 'comment_author' );
}

function get_comment_author_email() {
	return ae_EngineGateway::CallForComments( 'comment_author_email' );
}

// TODO: comments_rss()
function comments_rss() {
	return '';
}

// TODO: allowed_tags()
function allowed_tags() {
	return '';
}

/**
 * aestas
 */
function honeypot() {
	echo '
	<div style="display: none !important; visibility: hidden !important;">
			<p>Do not fill in these four fields:</p>
			<input name="honey-author" type="text" />
			<input name="honey-email" type="text" />
			<input name="honey-url" type="text" />
			<textarea name="honey-comment" cols="1" rows="1"></textarea>
			<br /><br /><br /><br />
	</div>
	';
}