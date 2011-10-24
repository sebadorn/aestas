<?php


/**
 * @param boolean $deprecated
 */
// TODO: wp_get_sidebars_widgets()
function wp_get_sidebars_widgets( $deprecated = true ) {
	return array();
}

// TODO: wp_loginout()
function wp_loginout() {
	
}

// TODO: wp_meta()
function wp_meta() {
	
}

function wp_register( $before = '<li>', $after = '</li>' ) {
	if( ae_Permissions::getIdOfCurrentUser() == -1 ) {
		echo $before , '<a href="' , URL , '/admin">Register</a>' , $after;
	}
	else {
		echo $before , '<a href="' , URL , '/admin">Site Admin</a>' , $after;
	}
}

// TODO: dynamic_sidebar()
function dynamic_sidebar( $index = 1 ) {
	return false;
}

// TODO: is_active_sidebar()
function is_active_sidebar( $index = 1 ) {
	return false;
}

// TODO: wp_tag_cloud()
function wp_tag_cloud() {

}