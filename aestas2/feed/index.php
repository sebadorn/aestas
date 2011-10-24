<?php

/*---------*/
/* RSS 2.0 */
/*---------*/

header( 'content-type: application/rss+xml' );

define( 'CONTEXT', 'posts-rss-feed' );

require_once( '../includes/config.php' );
include_once( '../includes/cl_wp/build_basics.php' );

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
define( 'SEARCH',		false						);



/*----------------------------*/
/* Start generating the feed. */
/*----------------------------*/

$p_query = new ae_PostQuery( 'post', ae_Settings::NewsfeedLimit() );


$feed = new ae_Newsfeed();
$feed->setTitle( get_bloginfo_rss( 'name' ) );
$feed->setDescription( get_bloginfo_rss( 'description' ) );

$favicon = ae_Theme::FindFavicon();
if( $favicon ) {
	$feed->setImage( $favicon, get_bloginfo_rss( 'name' ), URL );
}

$feed->setBlogLink( URL );
$feed->setFeedLink( get_bloginfo_rss( 'rss2_url' ) );

if( $p_query->have_posts() ) {
	while( $p_query->have_posts() ) {
		$p_query->the_post();

		$item = array(
			'title' => $p_query->the_title( '', '', false ),
			'link' => $p_query->the_permalink(),
			'comment_link' => $p_query->the_permalink() . '#comments',
			'date_created' => $p_query->the_time( 'U' ),
			'date_modified' => $p_query->post_lastedit( 'U' ),
			'author' => $p_query->get_the_author_meta( 'display_name' ),
			'categories' => $p_query->feed_entry_categories(),
			'content' => $p_query->feed_entry_description(),
			'comments' => $p_query->comments_number()
		);

		$feed->add_item( $item );
	}
}

echo $feed->generate();


mysql_close( $db_connect );
