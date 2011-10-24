<?php

/*---------*/
/* RSS 2.0 */
/*---------*/

header( 'content-type: application/rss+xml' );

define( 'CONTEXT', 'comments-rss-feed' );

require_once( '../../includes/config.php' );
include_once( '../../includes/cl_wp/build_basics.php' );

ae_Permissions::InitRoleAndStatus();


define( 'PROTOCOL',		ae_URL::Protocol()			);
define( 'URL',			PROTOCOL . ae_URL::Blog()	);
define( 'FEED',			true						);
define( 'SINGLE_POST',	0							);
define( 'PAGE',			0							);
define( 'CATEGORY',		0							);
define( 'TAG',			''							);
define( 'AUTHOR',		0							);
define( 'PAGE_ID',		1							);



/*----------------------------*/
/* Start generating the feed. */
/*----------------------------*/

$c = new ae_CommentQuery( true );

$feed = new ae_Newsfeed();
$feed->setTitle( get_bloginfo_rss( 'name' ) );
$feed->setDescription( get_bloginfo_rss( 'description' ) );

$favicon = ae_Theme::FindFavicon();
if( $favicon ) {
	$feed->setImage( $favicon, get_bloginfo_rss( 'name' ), URL );
}

$feed->setBlogLink( URL );
$feed->setFeedLink( get_bloginfo_rss( 'comments_rss2_url' ) );

if( $c->have_comments() ) {
	while( $c->have_comments() ) {
		$c->the_comment();
		if( $c->comment_post_has_pwd() ) {
			continue;
		}

		$item = array(
			'title' => '[' . $c->comment_post_title() . '] ' . $c->comment_author(),
			'link' => $c->comment_permalink(),
			'date_created' => $c->comment_date( 'U' ),
			'date_modified' => $c->comment_date( 'U' ),
			'author' => $c->comment_author(),
			'categories' => array( 'Comment' ),
			'content' => $c->comment_text()
		);

		$feed->add_item( $item );
	}
}

echo $feed->generate();

mysql_close( $db_connect );
