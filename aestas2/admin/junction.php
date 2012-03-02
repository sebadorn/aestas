<?php

$timer = microtime( true );
header( 'content-type: text/html; charset=utf-8' );
require_once( '../includes/config.php' );
require_once( 'admin-config.php' );


/* Permissions */

ae_Permissions::InitRoleAndStatus();
define( 'ROLE', ae_Permissions::getRoleOfCurrentUser() );
define( 'STATUS', ae_Permissions::getStatusOfCurrentUser() );
define( 'URL', preg_replace( '!/admin$!', '', ae_URL::Blog() ) );


/* Render page */

// Find current page

reset( $admin_config->navigation );
$first_nav_section = current( $admin_config->navigation );

$area = isset( $_GET['area'] ) ? $_GET['area'] : $first_nav_section['link'];
$show = isset( $_GET['show'] ) ? $_GET['show'] : '';

$nav_section = $title = $area_key = '';

foreach( $admin_config->navigation as $name => &$nav ) {
	if( $nav['link'] == $area ) {
		$area_key = $name;
		$nav['css_class'] .= ' active';
		$nav_section =& $nav; // For later, in case "show" has no useable value.

		foreach( $nav['sub_nav'] as &$sub_nav ) {
			if( $sub_nav['link'] == $show ) {
				$title = $name;
				$sub_nav['css_class'] .= ' active';
				break;
			}
		}
	}
}


// Default to first sub navigation element, if "show" wasn't provided.

if( $title == '' ) {
	reset( $nav_section['sub_nav'] );
	$show_key = key( $nav_section['sub_nav'] );
	$nav_section['sub_nav'][$show_key]['css_class'] .= ' active';
	$show = $nav_section['sub_nav'][$show_key]['link'];
}


// Title

$title = $show_key . $admin_config->title_seperator . $admin_config->title_cms;


// Params to use in templates

$params_header = new stdClass;
$params_header->title = $title;
$params_header->css = $admin_config->navigation[$area_key]['include']['css'];

$params_nav = new stdClass;
$params_nav->area = $area;
$params_nav->charset = $admin_config->charset;
$params_nav->current_path = $area;
$params_nav->nav = $admin_config->navigation;
$params_nav->search_was = isset( $_GET['search'] ) ? htmlspecialchars( $_GET['search'] ) : '';
$params_nav->show = $show;

$params_content = new stdClass;

$params_footer = new stdClass;
$params_footer->db_queries = ae_Database::getQueryCount() . '&nbsp;DB&nbsp;queries';
$params_footer->js = $admin_config->navigation[$area_key]['include']['javascript'];
$params_footer->mem_peak = round( memory_get_peak_usage() / 1024 / 1024, 2 ) . '&nbsp;MB';
$params_footer->mem_use = round( memory_get_usage() / 1024 / 1024, 2 ) . '&nbsp;MB';
$params_footer->time_needed = round( microtime( true ) - $timer, 5 ) . '&nbsp;sec';


// Render templates

$page_struct = new ae_PageStructure();
$page_struct->set_path( 'interface/template/' );

$include = '../../' . $area . '/' . $show . '.php';

$page_struct->render( 'html_header.php', $params_header );
$page_struct->render( 'html_nav.php', $params_nav );
$page_struct->render( $include, $params_content );
$page_struct->render( 'html_footer.php', $params_footer );
$page_struct->render( 'html_doc_end.php' );


ae_Database::Close();