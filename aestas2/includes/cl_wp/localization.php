<?php


function __( $text, $domain = 'default' ) {
	return $text;
}

function _e( $text, $domain = 'default' ) {
	echo $text;
}

// TODO: parameter $domain
function _n( $single, $plural, $number, $domain = 'default' ) {
	return ( $number == 1 ) ? $single : $plural;
}

function _x( $context, $text, $domain = 'default' ) {
	return $text;
}

function __ngettext( $single, $plural, $number, $domain = 'default' ) {
	if( $number == 1 ) { return $single; }
	return $plural;
}

function esc_attr_e( $text, $textdomain = 'default' ) {
	esc_attr( $text );
}

function get_locale() {
	return '';
}

function load_default_textdomain() {

}

function load_plugin_textdomain( $domain, $abs_rel_path = false, $plugin_rel_path = false ) {

}

function load_textdomain( $domain, $mofile ) {
	return null;
}

function load_theme_textdomain( $domain, $path = false ) {

}

// TODO: number_format_i18n
function number_format_i18n( $number ) {
	return $number;
}