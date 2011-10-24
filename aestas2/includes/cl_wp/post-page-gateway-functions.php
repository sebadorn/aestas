<?php

function comments_number( $zero = '0', $one = '1', $more = '%' ) {
	echo ae_EngineGateway::Call( 'comments_number', $zero, $one, $more );
}

function get_comments_number( $zero = '0', $one = '1', $more = '%' ) {
	return ae_EngineGateway::Call( 'comments_number', $zero, $one, $more );
}

function comments_open( $post_id = 0 ) {
	return ae_EngineGateway::Call( 'comments_open', $post_id );
}

function edit_post_link( $link = 'Edit This', $before = '', $after = '' ) {
	// TODO: edit_post_link
	return $before . $link . $after;
}

/**
 * WP deprecated
 */
function get_the_author_ID() {
	return get_the_author_meta( 'ID' );
}

function get_the_author_meta( $field = '', $userID = false ) {
	// TODO: get_the_author_meta: most of the fields
	$meta = '';
	if( ( (int) $userID ) > 0 ) {
		$user = ae_User::getUserById( $userID );

		switch( $field ) {
			case 'ID':
				$meta = $userID;
				break;
			case 'user_login':
				$meta = $user->getNameInternal();
				break;
			case 'user_pass':
				// ... I DON'T LIKE THIS
				break;
			case 'user_nicename':
			case 'display_name':
			case 'nickname':
				$meta = $user->getName();
				break;
			case 'user_email':
				$meta = $user->getEmail();
				break;
			case 'user_url':
				$meta = $user->getUrl();
				break;
			case 'user_registered':
				break;
			case 'user_activation_key':
				break;
			case 'user_status':
				break;
			case 'first_name':
			case 'user_firstname':
				break;
			case 'last_name':
			case 'user_lastname':
				break;
			case 'description':
			case 'user_description':
				break;
			case 'jabber':
				break;
			case 'aim':
				break;
			case 'yim':
				break;
			case 'user_level':
				break;
			case 'rich_editing':
				break;
			case 'comment_shortcuts':
				break;
			case 'admin_color':
				break;
			case 'plugins_per_page':
				break;
			case 'plugins_last_view':
				break;
		}
	}
	else {
		$meta = ae_EngineGateway::Call( 'get_the_author_meta', $field );
	}
	return $meta;
}

function get_the_category( $seperator = '', $parents = '' ) {
	return ae_EngineGateway::Call( 'the_category', $seperator, $parents );
}

// TODO: get_the_category_list
function get_the_category_list( $seperator = '', $parents = '', $post_id = false ) {
	$cats = ae_Database::Query( '
		SELECT
			cat_name,
			cat_permalink
		FROM `' . TABLE_CATEGORIES . '`
		WHERE cat_status IS NULL
		ORDER BY cat_name
	' );

	$clist = '';
	if( $seperator == '' ) {
		while( $c = mysql_fetch_assoc( $cats ) ) {
			$clist .= '<li><a href="' . URL . '/' . $c['cat_permalink'] . '">' . $c['cat_name'] . '</a></li>';
		}
	}

	return $clist;
}

function get_the_content( $more_link_text = '(more …)', $strip_teaser = false, $more_file = '', $more_class = '' ) {
	return ae_EngineGateway::Call( 'the_content', $more_link_text, $strip_teaser, $more_file, $more_class );
}

/**
 * aestas2
 */
function get_the_content_full() {
	return ae_EngineGateway::Call( 'the_content_full' );
}

/**
 * aestas2
 */
function get_the_lastedit( $format = 'F j, Y' ) {
	return ae_EngineGateway::Call( 'the_lastedit', $format );
}

function get_the_ID() {
	return ae_EngineGateway::Call( 'the_ID' );
}

function get_the_permalink() {
	return ae_EngineGateway::Call( 'the_permalink' );
}

function get_permalink( $id = 0 ) {
	if( $id < 1 ) {
		get_the_permalink();
	}
}

function get_the_tags( $before = '', $seperator = ', ', $after = '' ) {
	return ae_EngineGateway::Call( 'get_the_tags', $before, $seperator, $after );
}

function get_the_tag_list( $before = '', $seperator = ', ', $after = '' ) {
	return ae_EngineGateway::Call( 'get_the_tags', $before, $seperator, $after );
}

function get_the_time( $format = 'F j, Y' ) {
	return ae_EngineGateway::Call( 'the_time', $format );
}

/**
 * @see has_pwd
 */
function has_php( $id = 0 ) {
	try {
		return ae_EngineGateway::Call( 'has_php', $id );
	}
	catch( Exception $exc ) {
		// pass
	}

	if( ae_RequestCache::hasKey( 'has_php_' . $id ) ) {
		return ae_RequestCache::Load( 'has_php_' . $id );
	}

	$sql = '
		SELECT
			post_type AS type,
			post_pwd AS pwd
		FROM `' . TABLE_POSTS . '`
		WHERE post_id = ' . mysql_real_escape_string( $id );

	$has = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	ae_RequestCache::Save( 'has_pwd_' . $id, !empty( $has['pwd'] ) );
	ae_RequestCache::Save( 'has_php_' . $id, !empty( $has['type'] ) );

	return ( $has['type'] == 'php' );
}

/**
 * @see has_php
 */
function has_pwd( $id = 0 ) {
	try {
		return ae_EngineGateway::Call( 'has_pwd', $id );
	}
	catch( Exception $exc ) {
		// pass
	}

	if( ae_RequestCache::hasKey( 'has_pwd_' . $id ) ) {
		return ae_RequestCache::Load( 'has_pwd_' . $id );
	}

	$sql = '
		SELECT
			post_type AS type,
			post_pwd AS pwd
		FROM `' . TABLE_POSTS . '`
		WHERE post_id = ' . mysql_real_escape_string( $id );

	$has = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	ae_RequestCache::Save( 'has_pwd_' . $id, !empty( $has['pwd'] ) );
	ae_RequestCache::Save( 'has_php_' . $id, !empty( $has['type'] ) );

	return !empty( $has['pwd'] );
}

function has_tag( $tag = '' ) {
	return ae_EngineGateway::Call( 'has_tag', $tag );
}

function have_posts() {
	return ae_EngineGateway::Call( 'have_posts' );
}

function in_category( $category, $_post = null ) {
	if( !empty( $_post ) && is_int( $_post ) ) {

		// Any of the IDs or category names in the array
		if( is_array( $category ) ) {
			foreach( $category as $cat ) {
				if( in_category( $cat, $_post ) ) {
					return true;
				}
			}
			return false;
		}

		// The category with this ID
		if( ae_Validate::isDigit( $category ) ) {
			$sql = '
				SELECT COUNT( this_id ) AS hits
				FROM `' . TABLE_RELATIONS . '`
				WHERE relation_type = "post to cat"
				AND this_id = ' . $this->post_internal['id'] . '
				AND that_id = ' . $category;

			$getrel = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( $getrel['hits'] > 0 ) {
				return true;
			}
		}

		// The category with this name
		else if( is_string( $category ) ) {
			$category = mysql_real_escape_string( $category );
			$sql = '
				SELECT cat_id
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_name = "' . $category . '"
			';

			$getid = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( !empty( $getid ) ) {
				return in_category( (int) $getid['cat_id'], $_post );
			}
		}

	}
	else {
		return ae_EngineGateway::Call( 'in_category', $category );
	}
	return false;
}

function is_new_day() {
	return ae_EngineGateway::Call( 'is_new_day' );
}

function post_class( $ownClasses = '', $postID = 0 ) {
	echo ae_EngineGateway::Call( 'post_class', $ownClasses );
}

/**
 * aestas2
 */
function post_expires( $format = 'd-m-Y H:i:s' ) {
	echo ae_EngineGateway::Call( 'post_expires', $format );
}

/**
 * aestas2
 */
function post_feedpreview() {
	return ae_EngineGateway::Call( 'post_feedpreview' );
}

/**
 * aestas2
 */
function posts_count( $status = '' ) {
	return ae_EngineGateway::Call( 'posts_count', $status );
}

function posts_nav_link( $sep = ' — ', $prelabel = '« Previous Page', $nxtlabel = 'Next Page »' ) {
	echo ae_EngineGateway::Call( 'posts_nav_link', $sep, $prelabel, $nxtlabel );
}

function get_the_author() {
	return ae_EngineGateway::Call( 'the_author' );
}

function the_author() {
	echo ae_EngineGateway::Call( 'the_author' );
}

function the_author_meta( $field = '', $userID = false ) {
	echo get_the_author_meta( $field, $userID );
}

function the_author_posts_link( $idmode = false ) {
	echo ae_EngineGateway::Call( 'the_author_posts_link' );
}

function the_category( $seperator = ' ', $parents = '' ) {
	echo ae_EngineGateway::Call( 'the_category', $seperator, $parents );
}

function the_content( $more_link_text = '(more …)', $strip_teaser = false, $more_file = '', $more_class = '' ) {
	if( ae_EngineGateway::Call( 'has_php' ) && ( !ae_EngineGateway::Call( 'has_pwd' ) || validate_postpwd() ) ) {
		@eval( ae_EngineGateway::Call( 'the_content', $more_link_text, $strip_teaser, $more_file, $more_class ) );
	}
	else {
		echo ae_EngineGateway::Call( 'the_content', $more_link_text, $strip_teaser, $more_file, $more_class );
	}
}

/**
 * aestas2
 */
function the_lastedit( $format = 'F j, Y' ) {
	echo ae_EngineGateway::Call( 'the_lastedit', $format );
}

function the_tags( $before = '', $seperator = ', ', $after = '' ) {
	echo ae_EngineGateway::Call( 'get_the_tags', $before, $seperator, $after );
}

function the_ID() {
	echo ae_EngineGateway::Call( 'the_ID' );
}

function the_permalink() {
	echo ae_EngineGateway::Call( 'the_permalink' );
}

function the_post() {
	ae_EngineGateway::Call( 'the_post' );
}

function get_the_date( $format = 'M d, Y' ) {
	return the_time( $format, false );
}

function the_date( $format, $before, $after, $echo ) {
	$time = the_time( $format, false );
	if( !$echo ) {
		return $before . $time . $after;
	}
	echo $before . $time . $after;
}

function the_time( $format = 'M d, Y', $echo = true ) {
	$time = ae_EngineGateway::Call( 'the_time', $format );
	if( !$echo ) {
		return $time;
	}
	echo $time;
}

function the_title( $before = '', $after = '', $display = true ) {
	if( !$display ) {
		return ae_EngineGateway::Call( 'the_title', $before, $after, false );
	}
	echo ae_EngineGateway::Call( 'the_title', $before, $after, false );
}

function get_the_title( $before = '', $after = '' ) {
	return ae_EngineGateway::Call( 'the_title', $before, $after, false );
}

function the_title_attribute( $args = '' ) {
	$default = array(
		'before' => '',
		'after' => '',
		'echo' => 1
	);
	$args = args_string_to_array( $args, $default );

	$title = the_title( $args['before'], $args['after'], false );
	$title = strip_tags( $title );
	$title = htmlspecialchars( $title );

	if( $args['echo'] != 1 ) {
		return $title;
	}
	echo $title;
}

// TODO: $post parameter
function post_password_required( $post = null ) {
	return ae_EngineGateway::Call( 'post_password_required' );
}

// TODO: previous_post_link()
function previous_post_link( $format, $link, $in_same_cat = false, $excluded_categories = '' ) {
	echo '';
}

// TODO: previous_post_link()
function next_post_link( $format, $link, $in_same_cat = false, $excluded_categories = '' ) {
	echo '';
}

// TODO: get_trackback_url()
function get_trackback_url() {
	return ae_EngineGateway::Call( 'the_permalink' );
}