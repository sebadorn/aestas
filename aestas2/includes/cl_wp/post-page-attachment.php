<?php


// Posts


/**
 * WP
 */
/*function get_avatar( $id_or_email, $size = 96, $default = '' ) {
	return ae_User::getAvatar( $id_or_email, $size, $default );
}*/ // Function already exists in CommentQuery.


function get_children() {
	// TODO: get_children
	return array();
}


/**
 * WP
 */
function get_extended( $postcontent ) {
	$split = array(
		'main' => '',
		'extended' => ''
	);

	if( !is_string( $postcontent ) ) {
		return $split;
	}

	$more = explode( '<!--more-->', $postcontent );
	$split['main'] = $more[0];

	if( !empty( $more[1] ) ) {
		$split['extended'] = $more[1];
	}
	return $split;
}


function get_post() {
	
}


function get_post_ancestors() {
	
}


function get_post_mime_type() {
	
}


function get_post_status() {
	
}


function get_post_type() {
	
}


function get_posts() {
	
}


function is_post() {
	
}

// NOT SUPPORT?
function wp_delete_post() {

}

// NOT SUPPORT?
function wp_insert_post() {

}

// NOT SUPPORT?
function wp_publish_post() {

}

// NOT SUPPORT?
function wp_update_post() {

}


function wp_get_recent_posts() {
	
}


function wp_get_single_post() {
	
}



// Pages


function get_all_page_ids() {

}


function get_page() {

}


function get_page_link() {
	
}


function get_page_by_path() {

}


function get_page_by_title() {

}


function get_page_children() {

}


function get_page_hierarchy() {

}


function get_page_uri() {

}


function get_pages( $args = '' ) {

}


function page_uri_index() {
	
}


/**
 * WP
 */
// TODO: wp_list_pages: make it work right (more than basic)
function wp_list_pages( $args = '' ) {
	$p_query = ae_Database::Query( '
		SELECT
			post_id,
			post_title,
			post_permalink
		FROM `' . TABLE_POSTS . '`
		WHERE post_list_page = "true"
		AND post_status = "published"
		AND (
			post_expires IS NULL
			OR post_expires = "0000-00-00 00:00:00"
			OR post_expires > "' . date( 'Y-m-d H:i:s' ) . '"
		)
		ORDER BY post_title ASC
	' );

	$list = '';
	while( $p = mysql_fetch_assoc( $p_query ) ) {
		$list .= '<li>';
		$list .= '<a href="' . ae_URL::PermalinkOfPage( $p['post_id'], $p['post_permalink'] ) . '">';
		$list .= $p['post_title'] . '</a></li>' . PHP_EOL;
	}

	echo ( $list == '' ) ? '' : '<ul>' . $list . '</ul>';
}



// Custom field(s)

// NOT SUPPORT?
function add_post_meta() {

}

// NOT SUPPORT?
function delete_post_meta() {

}


function get_post_custom() {

}


function get_post_custom_keys() {

}


function get_post_custom_values() {

}


function get_post_meta() {

}

// NOT SUPPORT?
function update_post_meta() {

}



// Attachments


function get_attached_file() {

}


function is_local_attachment() {

}


function update_attached_file() {

}


function wp_attachment_is_image() {

}


function wp_insert_attachment() {

}


function wp_delete_attachment() {

}


function wp_get_attachment_image() {

}


function wp_get_attachment_image_src() {

}


function wp_get_attachment_metadata() {

}


function wp_get_attachment_thumb_file() {

}


function wp_get_attachment_thumb_url() {

}


function wp_get_attachment_url() {

}


function wp_check_for_changed_slugs() {

}


function wp_count_posts() {

}


function wp_mime_type_icon() {

}


function wp_update_attachment_metadata() {
	
}



// Bookmarks


function get_bookmark() {

}


function get_bookmarks() {

}


function wp_list_bookmarks() {

}



// Others


function add_meta_box() {
	
}


function wp_get_post_categories() {
	
}

// NOT SUPPORT?
function wp_set_post_categories() {
	
}


function wp_trim_excerpt() {

}
