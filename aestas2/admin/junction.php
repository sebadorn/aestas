<?php

$timer = microtime( true );

header( 'content-type: text/html; charset=utf-8' );

require_once( '../includes/config.php' );


ae_Permissions::InitRoleAndStatus();
define( 'ROLE', ae_Permissions::getRoleOfCurrentUser() );
define( 'STATUS', ae_Permissions::getStatusOfCurrentUser() );
define( 'URL', preg_replace( '!/admin$!', '', ae_URL::Blog() ) );

$area = ae_PageStructure::DecideCurrentArea();
$show = ae_PageStructure::DecideContentToShow( $area );

echo ae_PageStructure::Header( $area );
echo ae_PageStructure::Nav( $area, $show );

ae_PageStructure::Content( $area, $show );

echo ae_PageStructure::Footer();
echo ae_PageStructure::IncludeJavascript( $area );
echo ae_PageStructure::End();

mysql_close( $db_connect );
