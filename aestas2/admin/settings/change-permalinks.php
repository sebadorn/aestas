<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'permalinks' );


$edit = new ae_EditSettings();


$edit->renew_permalinks_author_on_update( isset( $_POST['pls-author-renew'] ) );

$edit->renew_permalinks_category_on_update( isset( $_POST['pls-cat-renew'] ) );

$edit->renew_permalinks_page_on_update( isset( $_POST['pls-page-renew'] ) );

$edit->renew_permalinks_post_on_update( isset( $_POST['pls-post-renew'] ) );


/* Permalink post */

if( $_POST['permalink-post'] == 'day-title' ) {
	$_POST['permalink-post'] = '%year%/%month%/%day%/%postname%';
}
else if( $_POST['permalink-post'] == 'custom' ) {
	$_POST['permalink-post'] = $_POST['custom-post'];
}

$edit->setPermalinkPost( $_POST['permalink-post'] );


/* Permalink page */

if( $_POST['permalink-page'] == 'title' ) {
	$_POST['permalink-page'] = '%pagename%';
}
else if( $_POST['permalink-page'] == 'custom' ) {
	$_POST['permalink-page'] = $_POST['custom-page'];
}

$edit->setPermalinkPage( $_POST['permalink-page'] );


/* Permalink author */

if( $_POST['permalink-auth'] == 'base-name' ) {
	$_POST['permalink-auth'] = 'author/%authorname%';
}
else if( $_POST['permalink-auth'] == 'custom' ) {
	$_POST['permalink-auth'] = $_POST['custom-auth'];
}

$edit->setPermalinkAuthor( $_POST['permalink-auth'] );


/* Permalink category */

if( $_POST['permalink-cat'] == 'base-title' ) {
	$_POST['permalink-cat'] = 'category/%catname%';
}
else if( $_POST['permalink-cat'] == 'custom' ) {
	$_POST['permalink-cat'] = $_POST['custom-cat'];
}

$edit->setPermalinkCategory( $_POST['permalink-cat'] );


/* Permalink blog page */

if( $_POST['permalink-bp'] == 'base-number' ) {
	$_POST['permalink-bp'] = 'page/%pagenumber%';
}
else if( $_POST['permalink-bp'] == 'custom' ) {
	$_POST['permalink-bp'] = $_POST['custom-bp'];
}

$edit->setPermalinkBlog( $_POST['permalink-bp'] );


/* Permalink tag */

if( $_POST['permalink-tag'] == 'basse-title' ) {
	$_POST['permalink-tag'] = 'tag/%tagname%';
}
else if( $_POST['permalink-tag'] == 'custom' ) {
	$_POST['permalink-tag'] = $_POST['custom-tag'];
}

$edit->setPermalinkTag( $_POST['permalink-tag'] );


$edit->update_to_database();

$edit->update_permalinks();


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=set&show=permalinks&ran=' . rand( 1, 100 ) );
