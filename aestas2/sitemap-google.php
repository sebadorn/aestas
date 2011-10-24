<?php

include( 'includes/config.php' );

ae_Permissions::InitRoleAndStatus();


// Constants
define( 'SINGLE_POST',	0								);
define( 'PAGE_ID',		0								);
define( 'AUTHOR',		0								);
define( 'CATEGORY',		0								);
define( 'FEED',			false							);
define( 'PAGE',			0								);
define( 'SEARCH',		false							);
define( 'TAG',			''								);
define( 'PROTOCOL',		ae_URL::Protocol()				);
define( 'URL',			PROTOCOL . ae_URL::Blog()		);
define( 'URL_EXTENDED',	PROTOCOL . ae_URL::Complete()	);

include( 'includes/cl_wp/build_basics.php' );

$p_query = new ae_PostQuery( 'post', 1000 );


function get_priority( $i ) {
	return ( $i >= 0 ) ? 0.8 : 0.5;
}



echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
		http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
	<?php if( $p_query->have_posts() ) : $i = 10; ?>
		<?php while( $p_query->have_posts() ) : $p_query->the_post(); ?>
	<url>
		<loc><?php echo $p_query->the_permalink() ?></loc>
		<lastmod><?php
			if( $p_query->post_lastedit( 'Y-m-d' ) == '' ) {
				echo $p_query->the_time( 'Y-m-d' );
			}
			else {
				echo $p_query->post_lastedit( 'Y-m-d' );
			}
		?></lastmod>
		<priority><?php echo get_priority( $i ); $i--; ?></priority>
	</url>
		<?php endwhile; ?>
	<?php endif; ?>
</urlset>
<?php
mysql_close( $db_connect );