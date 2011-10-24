<?php

// Action functions


function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	// TODO: add_action
}

function did_action( $tag ) {
	// TODO: did_action
	return 0;
}

function do_action( $tag, $arg = '' ) {
	// TODO: do_action
}

function do_action_ref_array( $tag, $arg ) {
	// TODO: do_action_ref
}

function remove_action( $tag, $function_to_remove, $priority = 10, $accepted_args = 1 ) {
	// TODO: remove_action
	return true;
}



// Filter functions


function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	// TODO: add_filter
	return false;
}

function apply_filters( $tag, $value ) {
	// TODO: apply_filters
	return $value;
}

function merge_filters( $tag ) {
	// TODO: merge_filters
}

function remove_filters( $tag, $function_to_remove, $priority = 10, $accepted_args = 1 ) {
	// TODO: remove_filters
	return false;
}



// Plugin functions

function plugin_basename( $file ) {
	// TODO: plugin_basename
	return '';
}

function register_activation_hook( $file, $function ) {
	// TODO: register_activation_hook
}

function register_deactivation_hook( $file, $function ) {
	// TODO: register_deactivation_hook
}

function register_setting( $option_group, $option_name, $sanitize_callback = '' ) {
	// TODO: register_setting
}

function settings_fields( $option_group ) {
	// TODO: settings_fields
}

function unregister_setting( $option_group, $option_name, $sanitize_callback ) {
	// TODO: unregister_setting
}



// Shortcodes


function add_shortcode( $tag, $func ) {
	// TODO: add_shortcode
}

function do_shortcode( $content ) {
	// TODO: do_shortcode
	return '';
}

function do_shortcode_tag( $m ) {
	// TODO: do_shortcode_tag
	return false;
}

function get_shortcode_regex() {
	// TODO: get_shortcode_regex
	return '';
}

function remove_shortcode( $tag ) {
	// TODO: remove_shortcode
}

function remove_all_shortcodes() {
	// TODO: remove_all_shortcodes
}

function shortcode_atts( $pairs, $atts ) {
	// TODO_shortcode_atts
	return array();
}

function shortcode_parse_atts( $text ) {
	// TODO: shortcode_parse_atts
	return array();
}

function strip_shortcodes( $content ) {
	// TODO: strip_shortcodes
	return '';
}