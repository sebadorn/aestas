<?php

/**
 * WP deprecated
 */
// TODO: $author_id and $author_nicename parameters
function get_author_link( $echo = false, $author_id = 0, $author_nicename = '' ) {
	$author_link = ae_EngineGateway::Call( 'the_author_posts_url' );
	if( !$echo ) {
		return $author_link;
	}
	echo $author_link;
}

function get_author_posts_url( $author_id = 0, $author_nicename = '' ) {
	if( $author_id < 1 ) {
		return ae_EngineGateway::Call( 'the_author_posts_url' );
	}
}

function wp_list_authors( $args ) {
	$default = array(
		'optioncount'   => false,
		'exclude_admin' => true,
		'show_fullname' => false,
		'hide_empty'    => true,
		'echo'          => true,
		'feed'          => '',
		'feed_image'    => '',
		'style'         => '',
		'html'          => ''
	);

	$params = args_string_to_array( $args, $default );

	$authors = ae_User::getUserNames();

	$out = '';
	foreach( $authors as $id => $name ) {
		$out .= '<li>' . $name . '</li>';
	}

	echo empty( $out ) ? '' : '<ul>' . $out . '</ul>' . PHP_EOL;
}