<?php


function is_404() {
	return ( CODE == 404 );
}

function is_admin() {

}

function is_archive() {

}

function is_attachment() {

}

function is_author() {

}

function is_category() {
	return ( CATEGORY > 0 );
}

function is_comments_popup() {

}

function is_date() {

}

function is_day() {

}

function is_feed() {

}

function is_front_page() {

}

function is_home() {

}

function is_month() {

}

function is_page() {
	return ( PAGE_ID > 0 );
}

function is_page_template() {

}

function is_paged() {

}

function is_preview() {

}

function is_search() {
	return ( SEARCH !== false );
}

function is_single() {
	return ( SINGLE_POST > 0 );
}

// TODO: is_singular
function is_singular() {
	return false;
}

function is_sticky() {

}

function is_tag() {
	return ( TAG != null );
}

function is_tax() {

}

function is_time() {

}

function is_year() {

}

function pings_open() {
	
}