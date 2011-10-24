<?php


/**
 * Takes in "query-string style parameters", as WP calls it,
 * and returns a normal array with key => value.
 * Also adds missing default values.
 */
function args_string_to_array( $args, $defaults ) {
	if( !is_array( $args ) ) {
		$args_array = array();
		$parts = explode( '&', $args );
		foreach( $parts as $part ) {
			$kv = explode( '=', $part );
			if( !isset( $kv[1] ) ) {
				$kv[1] = null;
			}
			$args_array[$kv[0]] = $kv[1];
		}
	}
	else {
		$args_array = $args;
	}

	foreach( $defaults as $key => $value ) {
		if( !isset( $args_array[$key] ) ) {
			$args_array[$key] = $value;
		}
	}

	return $args_array;
}



// Other functions


/**
 * WP
 *
 * @param string $show What info is wanted.
 */
function bloginfo( $show ) {
	echo get_bloginfo( $show );
}


/**
 * WP
 */
function bloginfo_rss( $show ) {
	echo get_bloginfo_rss( $show );
}


/**
 * Searches the directory in a WP theme where 'style.css' is located
 */
function find_styledir( $path = '/' ) {
	$result = -1;

	if( is_dir( 'themes/' . THEME . $path ) ) {
		$handle = opendir( 'themes/'.THEME.$path );
		if( is_resource( $handle ) ) {

			if( $path == '/' && file_exists( 'themes/' . THEME . '/style.css' ) ) {
				return URL . '/themes/' . THEME;
			}

			while( $result == -1 && ( $theme_dir = readdir( $handle ) ) !== false ) {
				if( $theme_dir == '.' || $theme_dir == '..' ) {
					continue;
				}
				if( $theme_dir == 'style.css' ) {
					$result = URL . '/themes/' . THEME . $path;
				}
				if( $result == -1 && is_dir( 'themes/' . THEME . $path . $theme_dir ) ) {
					$result = find_styledir( $path . $theme_dir . '/' );
				}
			}

		}
		closedir( $handle );
	}

	return preg_replace( '!/$!', '', $result );
}


/**
 * WP
 * 
 * @param string $show What info is wanted
 * @return string Wanted info
 */
function get_bloginfo( $show ) {
	$get_detail = '';

	switch( $show ) {

		case 'admin_email':
			return ae_User::AdminEmail();

		case 'atom_url':
			return URL . '/feed/atom';

		case 'charset':
			return 'UTF-8';

		case 'comments_atom_url':
			return URL . '/comments/feed/atom';

		case 'comments_rss2_url':
			return URL . '/comments/feed';

		case 'description':
			if( ae_RequestCache::hasKey( 'description' ) ) {
				return ae_RequestCache::Load( 'description' );
			}

			$id = ( SINGLE_POST > 0 ) ? SINGLE_POST : PAGE_ID;

			if( $id == 0 ) {
				$desc['title'] = ae_Settings::getSetting( 'bloginfo_title' );
				$return = ae_Settings::getSetting( 'bloginfo_tagline' );
			}
			else {
				$sql = '
					SELECT
						post_description AS description,
						post_title AS title
					FROM `' . TABLE_POSTS . '`
					WHERE post_id = ' . $id;

				$desc = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

				if( empty( $desc ) ) {
					$return = '';
				}
				else {
					$return = empty( $desc['description'] ) ? $desc['title'] : htmlspecialchars( $desc['description'] );
				}
			}

			ae_RequestCache::Save( 'title', $desc['title'] );
			ae_RequestCache::Save( 'description', $return );
			return $return;

		case 'tagline':
			$get_detail = 'bloginfo_tagline';
			break;

		case 'html_type':
			return 'text/html';

		case 'language':
			return LANG;

		case 'name':
			$get_detail = 'bloginfo_title';
			break;

		case 'pingback_url':
			return URL . '/xmlrpc.php';

		case 'rdf_url':
			return URL . '/feed/rdf';

		case 'rss2_url':
			$alt = ae_Settings::FeedAlternate();
			return empty( $alt ) ? URL . '/feed' : $alt;

		case 'rss_url':
			$alt = ae_Settings::FeedAlternate();
			return empty( $alt ) ? URL . '/feed/rss' : $alt;

		case 'stylesheet_directory':
			return find_styledir();

		case 'stylesheet_url':
			return find_styledir() . '/style.css';

		case 'template_directory':
		case 'template_url':
			return TEMPLATEPATH;

		case 'text_direction':
			return 'ltr';

		case 'home':
		case 'siteurl':
		case 'url':
		case 'wpurl':
			return URL;

		case 'version':
			$get_detail = 'version';
			break;

		default:
			return null;

	}

	return ae_Settings::getSetting( $get_detail );
}


/**
 * WP
 */
function get_bloginfo_rss( $show ) {
	$info = get_bloginfo( $show );
	return strip_tags( $info );
}


/**
 * WP (plugin)
 */
function get_recent_comments( $between = ' to ', $before = '', $after = '', $limit = 7 ) {
	$limit = ( is_int( $limit ) && $limit > 0 ) ? $limit : 7;

	$c_query = ae_Database::Query( '
		SELECT
			comment_id,
			comment_post_id,
			comment_author,
			comment_url,
			post_title,
			post_permalink
		FROM `' . TABLE_COMMENTS . '`
		LEFT JOIN `' . TABLE_POSTS . '`
		ON comment_post_id = post_id
		WHERE comment_status = "approved"
		AND (
			post_pwd = ""
			OR post_pwd IS NULL
		)
		AND (
			post_status = "published"
			OR post_status IS NULL
		)
		AND (
			post_expires > "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
			OR post_expires = "0000-00-00 00:00:00"
			OR post_expires IS NULL
		)
		ORDER BY
			comment_date DESC,
			comment_id DESC
		LIMIT ' . $limit
	);

	$list = '';
	while( $c = mysql_fetch_assoc( $c_query ) ) {
		if( !empty( $c['comment_website'] ) && ae_Validate::isUrl( $c['comment_website'] ) ) {
			$recent_comment = '<a href="' . $c['comment_website'] . '">' . $c['comment_author'] . '</a>';
		}
		else {
			$recent_comment = '<span>' . $c['comment_author'] . '</span>';
		}

		$title = htmlspecialchars( $c['post_title'] );
		$link = ae_URL::PermalinkOfPost( $c['comment_post_id'], $c['post_permalink'] );

		$recent_comment .= $between . '<a href="' . $link . '#comment-' . $c['comment_id'] . '">' . $title . '</a>';
		$list .= '<li>' . $before . $recent_comment . $after .'</li>' . PHP_EOL;
	}

	echo $list;
}


/**
 * WP
 */
function get_theme_data( $theme_filename ) {
	$theme_data = array(
		'Name' => '',
		'Title' => '',
		'Description' => '',
		'Author' => '',
		'Version' => '',
		'Template' => '',
		'Status' => 'publish'
	);

	if( !file_exists( $theme_filename ) || preg_match( '!/style\.css$!', $theme_filename ) ) {
		return $theme_data;
	}


	// Part1: Get those juicy information!

	$wp_theme = array(
		'Theme Name' => '',
		'Theme URI' => '',
		'Description' => '',
		'Author' => '',
		'Version' => '',
		'Template' => ''
	);

	$file = file( $theme_filename );

	// Scope of info comment
	$start = -1;
	$end = -1;

	for( $i = 0; $i < count( $file ); $i++ ) {
		if( $start == -1 ) {
			if( strpos( $file[$i], '/*' ) !== false ) {
				$start = $i;
			}
		}
		if( strpos( $file[$i], '*/' ) !== false ) {
			$end = $i;
			break;
		}
	}

	// If not found there is a problem and we should return
	if( $start == -1 || $end == -1 ) {
		return $theme_data;
	}


	$search = array(
		'Theme Name',
		'Theme URI',
		'Description',
		'Author',
		'Version',
		'Template'
	);


	reset( $wp_theme );

	foreach( $search as $info ) {

		$key = key( $wp_theme );	// Get key at current position

		for( $i = $start; $i <= $end; $i++ ) {
			if( preg_match( '/'.$info.':([^\r\n]*)/', $file[$i], $hit ) ) {
				$wp_theme[$key] = trim($hit[1]);	// At this key fill in the found value
				$i = $start;
				break;
			}
		}

		next( $wp_theme );	// Set internal array pointer to the next element

	} // End foreach()

	unset( $file );


	// Part2: Process those information for the wanted return array

	$theme_data_title = $wp_theme['Theme Name'];

	if( !empty( $wp_theme['Theme URI'] ) ) {
		$theme_data_title = '<a href="'.htmlentities( $wp_theme['Theme URI'] ).'">'
				.htmlentities( $wp_theme['Theme Name'] ).'</a>';
	}

	$theme_data = array(
		'Name' => $wp_theme['Theme Name'],
		'Title' => $theme_data_title,
		'Description' => $wp_theme['Description'],
		'Author' => $wp_theme['Author'],
		'Version' => $wp_theme['Version'],
		'Template' => $wp_theme['Template'],
		'Status' => 'publish'
	);


	// Part3: Delivery

	return $theme_data;
}


/**
 * WP
 */
function body_class( $class = '' ) {
	if( PAGE_ID == 1 ) {
		$class .= ' blog home';
	}
	else if( PAGE_ID > 1 ) {
		$class .= ' page page-id-' . PAGE_ID;
	}
	if( SINGLE_POST > 0 ) {
		$class .= ' single postid-' . SINGLE_POST;
	}
	if( SEARCH !== false ) {
		$class .= ' search search-results';
	}
	if( CODE == 404 ) {
		$class .= ' error404';
	}
	if( ae_Permissions::isLoggedIn() ) {
		$class .= ' logged-in';
	}

	$class = trim( $class );
	if( !empty( $class ) ) {
		echo 'class="' . $class . '"';
	}
}

/**
 * WP
 */
function header_image() {
	// Look for header added with add_custom_image_header()
	if( ae_RequestCache::hasKey( 'add_custom_image_header' ) ) {
		$header = ae_RequestCache::Load( 'add_custom_image_header' );
		echo $header['path'];
		return;
	}

	// Look for header image file in theme
	$path = 'themes/' . THEME . '/images/headers/';
	$size[0] = $size[1] = 0;

	if( is_dir( $path ) ) {

		if( file_exists( $path . 'header.jpg' ) ) {
			$size = getimagesize( $path . 'header.jpg' );
			echo URL . '/' . $path . 'header.jpg';
		}
		else if( file_exists( $path . 'header.png' ) ) {
			$size = getimagesize( $path . 'header.png' );
			echo URL . '/' . $path . 'header.png';
		}
		// Take the first image you can find
		else {
			$handle = opendir( $path );
			if( is_resource( $handle ) ) {
				while( ( $file = readdir( $handle ) ) !== false ) {
					$type = explode( '.', $file );
					$last = strtolower( $type[count( $type ) - 1] );
					if( $last == 'jpg' || $last == 'png' ) {
						$size = getimagesize( $path . $file );
						echo URL . '/' . $path . $file;
						break;
					}
				}
				closedir( $handle );
			}
		}
	}

	if( !defined( 'HEADER_IMAGE_WIDTH' ) ) {
		define( 'HEADER_IMAGE_WIDTH', $size[0] );
		define( 'HEADER_IMAGE_HEIGHT', $size[1] );
	}
}


/**
 * WP
 */
function home_url( $path = '', $scheme = '' ) {
	return URL;
}


/**
 * WP
 */
function language_attributes( $doctype = 'html' ) {
	echo 'dir="' , get_bloginfo( 'text_direction' ) , '" lang="' , LANG , '"';
}


/**
 * WP
 */
function next_posts_link( $label = 'Next Page &raquo;', $max_pages = 0 ) {
	// TODO: next_posts_link
	return '';
}


/**
 * @param string $type author, category, tag
 */
function posts_countbyfilter( $type = '', $id = 0, $use_cache = true ) {
	// Logged-in users can see every post
	// and therefore have to be able to reach those

	$status = '
		WHERE post_status = "published"
		AND post_date <= "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
		AND (
			post_expires > "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
			OR post_expires = "0000-00-00 00:00:00"
			OR post_expires IS NULL
		)
	';

	if( ae_Permissions::isLoggedIn() ) {
		$status = '';
	}

	// Uses the cached result from PostQuery().
	if( ae_RequestCache::hasKey( 'count_posts' ) ) {
		return ae_RequestCache::Load( 'count_posts' );
	}

	if( $type == '' ) {
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			' . $status;
	}

	else if( $type == 'category' ) {
		$cat = ( $id > 0 ) ? $id : CATEGORY;
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			LEFT JOIN `' . TABLE_RELATIONS . '`
			ON post_id = this_id
			WHERE relation_type = "post to cat"
			AND that_id = ' . $cat
			. str_replace( 'WHERE', 'AND', $status );
	}

	else if( $type == 'tag' ) {
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			WHERE post_keywords LIKE "%' . mysql_real_escape_string( TAG ) . ';%"'
			. str_replace( 'WHERE', 'AND', $status );
	}

	else if( $type == 'author' ) {
		$author = ( $id > 0 ) ? $id : AUTHOR;
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			WHERE post_author_id = ' . $author
			. str_replace( 'WHERE', 'AND', $status );
	}

	else {
		return 0;
	}


	$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	return $total['count'];
}


/**
 * WP
 */
function previous_posts_link( $label = '&laquo; Previous Page', $max_pages = 0 ) {
	// TODO: previous_posts_link
	return '';
}


/**
 * WP
 */
function get_query_var( $var ) {
	return '';
}


/**
 * WP
 */
function wp_get_archives( $args = '' ) {
	// TODO: wp_get_archives
	return '';
}


/**
 * WP
 */
function wp_head() {
	// TODO: wp_head
	echo '<link rel="stylesheet" href="' , get_bloginfo( 'stylesheet_url' ) , '" />';
}


/**
 * WP
 */
// TODO: wp_nav_menu
function wp_nav_menu( $args = array() ) {
	if( empty( $args ) ) {
		$defaults = array(
			'theme_location'  => '',
			'menu'            => '',
			'container'       => 'div',
			'container_class' => 'menu-{menu slug}-container',
			'container_id'    => '',
			'menu_class'      => 'menu',
			'menu_id'         => '',
			'echo'            => true,
			'fallback_cb'     => 'wp_page_menu',
			'before'          => '',
			'after'           => '',
			'link_before'     => '',
			'link_after'      => '',
			'depth'           => 0,
			'walker'          => ''
		);
	}
}


/**
 * WP
 */
// TODO: wp_page_menu
function wp_page_menu( $args = array() ) {
	wp_list_pages();
}


/**
 * WP
 */
function get_template_part( $slug, $name = '' ) {
	global $wp_query;

	if( !empty( $name ) ) {
		if( file_exists( 'themes/' . THEME . '/' . $slug . '-' . $name . '.php' ) ) {
			require( 'themes/' . THEME . '/' . $slug . '-' . $name . '.php' );
		}
	}
	if( file_exists( 'themes/' . THEME . '/' . $slug . '.php' ) ) {
		require( 'themes/' . THEME . '/' . $slug . '.php' );
	}

}



// Title tags


/**
 * WP
 */
function single_cat_title( $prefix = '', $display = true ) {
	// TODO: single_cat_title
	return '';
}


/**
 * WP
 */
function single_month_title( $prefix = '', $display = true ) {
	// TODO: single_month_title
	return '';
}


/**
 * WP
 */
function single_post_title( $prefix = '', $display = true ) {
	if( SINGLE_POST > 0 ) {
		if( !$display ) {
			return the_title( $prefix, '', false );
		}
		the_title( $prefix, '', true );
	}
}


/**
 * WP
 */
function single_tag_title( $prefix = '', $display = true ) {
	// TODO: single_tag_title
	return '';
}


/**
 * WP
 */
function the_search_query() {
	echo '';
}


/**
 * WP
 */
function wp_list_categories( $args = '' ) {
	$params = array(
		'show_option_all'    => '',
		'orderby'            => 'name',
		'order'              => 'ASC',
		'show_last_update'   => 0,
		'style'              => 'list',
		'show_count'         => 0,
		'hide_empty'         => 1,
		'use_desc_for_title' => 1,
		'child_of'           => 0,
		'feed'               => '',
		'feed_type'          => '',
		'feed_image'         => '',
		'exclude'            => '',
		'exclude_tree'       => '',
		'include'            => '',
		'current_category'   => 0,
		'hierarchical'       => true,
		'title_li'           => '',
		'number'             => NULL,
		'echo'               => 1,
		'depth'              => 0
	);

	if( !empty( $args ) ) {
		$args = explode( '&', $args );
		$tmp = array();
		foreach( $args as $value ) {
			$param = explode( '=', $value );
			$tmp[$param[0]] = $param[1];
		}
		$params = array_merge( $params, $tmp );
	}


	// Evaluate params

	switch( $params['orderby'] ) {
		case 'ID':
			$params['orderby'] = 'id';
			break;
		case 'slug':
			$params['orderby'] = 'name';
			break;
		case 'count':
			$params['orderby'] = 'count';
			break;
		case 'term_group':
			$params['orderby'] = 'name';
			break;
		default:
			$params['orderby'] = 'name';
	}

	if( $params['order'] != 'DESC' ) {
		$params['order'] = 'ASC';
	}

	if( $params['style'] != 'list' ) {
		$before = '';
		$after = '<br />' . PHP_EOL;
	}
	else {
		$before = '<li>';
		$after = '</li>' . PHP_EOL;
	}


	$result = '';

	// MySQL query with post count
	if( $params['show_count'] != 0 ) {
		$cats_count = ae_Database::Query( '
			SELECT
				cat_id,
				cat_name,
				cat_permalink,
				COUNT( cat_id ) AS cat_count
			FROM `' . TABLE_CATEGORIES . '`
			LEFT JOIN `' . TABLE_RELATIONS . '`
			ON cat_id = that_id
			WHERE cat_status != "trash"
			AND relation_type = "post to cat"
			GROUP BY
				cat_id,
				cat_name,
				cat_permalink
			ORDER BY cat_' . $params['orderby'] . ' ' . $params['order']
		);

		while( $cat = mysql_fetch_assoc( $cats_count ) ) {
			$link = ae_URL::PermalinkOfCategory( $cat['cat_id'], $cat['cat_permalink'] );
			$result .= $before . '<a href="'. $link . '">';
			$result .= $cat['cat_name'] . ' (' . $cat['cat_count'] . ')</a>' . $after;
		}
	}

	// MySQL query without post count
	else {
		$cats = ae_Database::Query( '
			SELECT
				cat_id,
				cat_name,
				cat_permalink
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_status != "trash"
			ORDER BY cat_' . $params['orderby'] . ' ' . $params['order']
		);

		while( $cat = mysql_fetch_assoc( $cats ) ) {
			$link = ae_URL::PermalinkOfCategory( $cat['cat_id'], $cat['cat_permalink'] );
			$result .= $before . '<a href="' . $link .'">';
			$result .= $cat['cat_name'] . '</a>' . $after;
		}
	}

	if( $params['echo'] == 0 ) {
		return $result;
	}
	echo $result;
}


/**
 * WP (Plugin)
 */
function wp_page_numbers( $first = 'First', $last = 'Last', $numbers = 7 ) {
	// Number of posts. Maybe with a filter applied.
	$type = '';
	if( CATEGORY > 0 ) { $type = 'category'; }
	else if( TAG != '' ) { $type = 'tag'; }
	else if( AUTHOR > 0 ) { $type = 'author'; }

	$total = posts_countbyfilter( $type );


	// Number of pages = total of pages / posts per page
	$limit = ae_Settings::PostLimit();
	$pages = ceil( $total / $limit );

	if( $pages == 1 ) {
		return;
	}

	echo '<a class="first" href="http://' , ae_URL::CompleteWithoutBlogpage()
			, ae_URL::BlogpageNavAppendix( 1 ) , '">' , $first , '</a>' , PHP_EOL;


	// Number of page links
	$plusminus = floor( ( $numbers - 1 ) / 2 );

	$limit_bottom = PAGE - $plusminus;
	$limit_top = PAGE + $plusminus;

	if( $numbers % 2 == 0 ) { $limit_top++; }

	while( $limit_bottom <= 0 ) {
		$limit_bottom += 1;
		$limit_top += 1;
	}

	while( $limit_top > $pages ) {
		$limit_top -= 1;
		if( $limit_bottom > 1 ) { $limit_bottom -= 1; }
	}

	// All the numbers in between
	for( $i = $limit_bottom; $i <= $limit_top; $i++ ) {
		$act = ( $i == PAGE ) ? 'class="act" ' : '';
		if( $i == 1 && PAGE == 0 ) {
			$act = 'class="act" ';
		}

		echo '<a ' , $act , 'href="http://' , ae_URL::CompleteWithoutBlogpage()
				, ae_URL::BlogpageNavAppendix( $i ) , '">' , $i , '</a>' , PHP_EOL;
	}

	echo '<a class="last" href="http://' , ae_URL::CompleteWithoutBlogpage() ,
			ae_URL::BlogpageNavAppendix( $pages ) , '">' , $last , '</a>' , PHP_EOL;
}


/**
 * WP
 */
function wp_title( $sep = '&raquo;', $echo = true, $seplocation = '' ) {
	if( ae_RequestCache::hasKey( 'title' ) ) {
		$title = ae_RequestCache::Load( 'title' );
	}
	else {
		if( SINGLE_POST > 0 ) {
			$title = single_post_title( '', false );
		}
		else {
			if( PAGE_ID > 0 ) {
				$sql = '
					SELECT
						post_title,
						post_description
					FROM `' . TABLE_POSTS . '`
					WHERE post_id = ' . PAGE_ID;

				$gett = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );
				ae_RequestCache::Save( 'description', $gett['post_description'] );
				$title = $gett['post_title'];
			}
			else {
				$title = '';
			}
		}

		ae_RequestCache::Save( 'title', $title );
	}

	$title .= empty( $title ) ? '' : ' ' .$sep . ' ';

	if( !$echo ) {
		return $title;
	}
	echo $title;
}

function site_url() {
	return get_bloginfo( 'siteurl' );
}