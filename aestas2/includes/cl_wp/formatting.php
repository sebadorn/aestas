<?php


// Formatting functions


/**
 * WP deprecated
 * @param string $text
 */
function attribute_escape( $text ) {
	esc_attr( $text );
}

/**
 * @param string $text
 */
function esc_attr( $text ) {
	// Undo previous escaping
	$replace = array( '&#039;', '&quot;', '&amp;', '&lt;', '&gt;' );
	$with = array( '\'', '"', '&', '<', '>' );
	$text = str_replace( $replace, $with, $text );

	// Escape html entities
	$text = htmlentities( $text, ENT_QUOTES, 'UTF-8' );

	echo $text;
}

function esc_attr__( $text ) {
	return htmlentities( $text, ENT_QUOTES );
}

function esc_url( $url ) {
	return $url;
}

/**
 * @param string $title
 * @param string $fallback_title
 * @return string
 */
function sanitize_title( $title, $fallback_title = '' ) {
	if( empty( $title ) ) {
		return strip_tags( $fallback_title );
	}
	return strip_tags( $title );
}

/**
 * WP deprecated
 */
// TODO: $quotes parameter
function wp_specialchars( $text, $quotes = 0 ) {
	return esc_html( $text );
}

// TODO: esc_html filter
function esc_html( $text ) {
	return htmlspecialchars( $text );
}