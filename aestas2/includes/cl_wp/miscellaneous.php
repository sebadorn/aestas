<?php


function add_query_arg( $key = '', $value = '' ) {
	if( $key == '' && $value == '' ) {
		return $_SERVER['REQUEST_URI'];
	}

	$request_uri = $_SERVER['REQUEST_URI'];
	$questmark = ( strpos($_SERVER['REQUEST_URI'], '?') === false ) ? false : true;

	if( is_array( $key ) ) {
		foreach( $key as $k => $v ) {
			if( !$questmark ) {
				$request_uri .= '?' . urlencode( $k ) . '=' . urlencode( $v );
				$questmark = true;
				continue;
			}
			$request_uri .= '&' . urlencode( $k ) . '=' . urlencode( $v );
		}
	}

	else {
		if( !$questmark ) {
			$request_uri .= '?' . urlencode( $key ) . '=' . urlencode( $value );
		}
		else {
			$request_uri .= '&' . urlencode( $key ) . '=' . urlencode( $value );
		}
	}

	return $request_uri;

}

function bool_from_yn( $yn ) {
	if( $yn == 'y' || $yn == 'Y' ) { return true; }
	return false;
}

function cache_javascript_headers() {
	// TODO: cache_javascript_headers
}

function check_admin_referer( $action, $query_arg = '_wpnonce' ) {
	// TODO: check_admin_referer
}

function check_ajax_referer( $action, $query_arg = false, $die = true ) {
	// TODO: check_ajax_referer
}

function do_robots() {
	// TODO: do_robots
}

function get_num_queries() {
	return ae_Database::getQueryCount();
}

/**
 * @param int $id Parent category ID
 * @return array IDs of sub categories
 */
function get_subcategories( $id ) {
	if( !is_numeric( $id ) ) {
		return array();
	}

	$sub = array();

	$sql = '
		SELECT
			cat_id
		FROM `' . TABLE_CATEGORIES . '`
		WHERE cat_parent = ' . mysql_real_escape_string( $id );

	return ae_Database::Assoc( $sql );
}

function is_blog_installed() {
	// TODO: is_blog_installed
	return true;
}

function make_url_footnote( $content ) {
	// TODO: make_url_footnote
	return strip_tags( $content );
}

function nocache_headers() {
	// TODO: nocache_headers
}

function remove_query_arg( $key, $query = false ) {
	if( !$query ) {
		$query = $_SERVER['REQUEST_URI'];
	}

	if( is_array( $key ) ) {
		foreach( $key as $v ) {
			$query = preg_replace( '/(\?|&)' . urlencode( $v ) . '(=[^&\?=]*)?/', '', $query );
		}
	}
	else {
		$query = preg_replace( '/(\?|&)' . urlencode( $key ) . '(=[^&\?=]*)?/', '', $query );
	}

	return $query;
}

function status_header( $header ) {
	// TODO: status_header
}

function timer_stop( $display = 0, $precision = 3 ) {
	global $timer;

	$timer_end = microtime( true );
	$time = round( $timer_end - $timer, $precision );

	if( $display == 0 ) {
		return $time;
	}
	echo $time;
}

function wp( $query_vars = '' ) {
	// TODO: wp: But does aestas2 even need it?
}

function wp_check_filetype( $filename, $mimes = null ) {
	if( empty( $mimes ) || !is_array( $mimes ) ) {
		$mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'bmp' => 'image/bmp',
			'tif|tiff' => 'image/tiff',
			'ico' => 'image/x-icon',
			'asf|asx|wax|wmv|wmx' => 'video/asf',
			'avi' => 'video/avi',
			'divx' => 'video/divx',
			'flv' => 'video/x-flv',
			'mov|qt' => 'video/quicktime',
			'mpeg|mpg|mpe' => 'video/mpeg',
			'txt|c|cc|h' => 'text/plain',
			'rtx' => 'text/richtext',
			'css' => 'text/css',
			'htm|html' => 'text/html',
			'mp3|m4a' => 'audio/mpeg',
			'mp4|m4v' => 'video/mp4',
			'ra|ram' => 'audio/x-realaudio',
			'wav' => 'audio/wav',
			'ogg' => 'audio/ogg',
			'mid|midi' => 'audio/midi',
			'wma' => 'audio/wma',
			'rtf' => 'application/rtf',
			'js' => 'application/javascript',
			'pdf' => 'application/pdf',
			'doc|docx' => 'application/msword',
			'pot|pps|ppt|pptx' => 'application/vnd.ms-powerpoint',
			'wri' => 'application/vnd.ms-write',
			'xla|xls|xlsx|xlt|xlw' => 'application/vnd.ms-excel',
			'mdb' => 'application/vnd.ms-access',
			'mpp' => 'application/vnd.ms-project',
			'swf' => 'application/x-shockwave-flash',
			'class' => 'application/java',
			'tar' => 'application/x-tar',
			'zip' => 'application/zip',
			'gz|gzip' => 'application/x-gzip',
			'exe' => 'application/x-msdownload',
			'odt' => 'application/vnd.oasis.opendocument.text',
			'odp' => 'application/vnd.oasis.opendocument.presentation',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			'odg' => 'application/vnd.oasis.opendocument.graphics',
			'odc' => 'application/vnd.oasis.opendocument.chart',
			'odb' => 'application/vnd.oasis.opendocument.database',
			'odf' => 'application/vnd.oasis.opendocument.formula'
		);
	}

	foreach( $mimes as $ext => $mime_type ) {
		if( preg_match( '/\.(' . $ext . ')$/i', $filename, $matched_ext ) ) {
			return array( $matched_ext[1], $mime_type );
		}
	}

	return null;
}

/**
 * WP deprecated
 */
function wp_clearcookie() {
	ae_Cookies::LogOutDeleteCookies();
}

function wp_create_nonce( $action ) {
	// TODO: wp_create_nonce
	return '';
}

function wp_die( $message, $title = '', $args = array() ) {
	$title = ( $title != '' && is_string( $title ) ) ? $title : bloginfo( 'name' ) . ' &rsaquo; Error';
	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>' , $title , '</title>
		<link rel="stylesheet" href="' , bloginfo( 'stylesheet_url' ) , '" type="text/css" />
	</head>
	<body id="error-page">
		<p>' , $message , '</p>
	</body>
	</html>
	';
	exit;
}

function wp_explain_nonce( $action ) {
	// TODO: wp_explain_nonce
	return '';
}

/**
 * WP deprecated
 */
function wp_get_cookie_login() {
	return false;
}

function wp_get_http_headers( $url, $deprecated = false ) {
	// TODO: wp_get_http_headers
	return false;
}

function wp_get_original_referer() {
	if( isset( $_SERVER['HTTP_REFERER'] ) ) {
		return $_SERVER['HTTP_REFERER'];
	}
	return false;
}

function wp_get_referer() {
	// TODO: wp_get_referer
	return wp_get_original_referer();
}

if( !function_exists( 'wp_hash' ) ) {
	function wp_hash( $data, $scheme = 'auth' ) {
		return md5( SALT . $data ); // TODO: Should be the same as ae_Permissions::Hash?
	}
}

if( !function_exists( 'wp_mail' ) ) {
	// TODO: wp_mail
	function wp_mail( $to, $subject, $message, $headers = null, $attachments = array() ) {
		/*
		if (!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$/', $to) ) {
			return false;
		}

		if( !empty($headers) ) {
			mail($to, $subject, $message, $headers);
			return true;
		}
		else {
			mail($to, $subject, $message);
			return true;
		}
		*/

		return false;
	}
}

// TODO: wp_mkdir_p
function wp_mkdir_p( $target ) {
	if( file_exists( $target ) ) {
		return true;
	}
	return false;
}

function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
	// TODO: wp_new_user_notification
}

function wp_nonce_ays( $action ) {
	// TODO: wp_nonce_ays
}

function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
	// TODO: wp_nonce_field
	return '';
}

function wp_nonce_url( $actionurl, $action = -1 ) {
	// TODO: wp_nonce_url
	return '';
}

if( !function_exists( 'wp_notify_moderator' ) ) {
	// TODO: wp_notify_moderator
	function wp_notify_moderator( $comment_id ) {
		return true;
	}
}

function wp_notify_postauthor( $comment_id, $comment_type = 'comment' ) {
	// TODO: wp_notify_postauthor
	return false;
}

function wp_original_referer_field( $echo = true, $jump_back_to = 'current' ) {
	// TODO: wp_original_referer_field
	return '';
}

if( !function_exists( 'wp_redirect' ) ) {
	function wp_redirect( $location, $status = 302 ) {
		if( !is_string( $location ) ) {
			return false;
		}
		if( !is_int ($status ) ) {
			$status = 302;
		}
		header( 'Location: '. $location, true, $status );
	}
}

function wp_referer_field($echo = true) {
	// TODO: wp_referer_field
	return '';
}

function wp_remote_fopen( $uri ) {
	$handle = fopen( $uri, 'r' );
	if( is_resource( $handle ) ) {
		$content = '';
		while( !feof( $handle ) ) {
			$content .= fread( $handle, 512 );
		}

		fclose( $handle );
		return $content;
	}
	return '';
}


if( !function_exists( 'wp_salt' ) ) {
	function wp_salt( $scheme = 'auth' ) {
		// NOT SUPPORTED
	}
}


if( !function_exists( 'wp_setcookie' ) ) {
	/**
	 * WP deprecated
	 */
	function wp_setcookie( $username, $password = '', $already_md5 = false, $home = '', $siteurl = '', $remember = false ) {
		// NOT SUPPORTED
	}
}

function wp_upload_bits($name, $deprecated, $bits, $time = null) {
	// TODO: wp_upload_bits
}

function wp_upload_dir($time = null) {
	// TODO: wp_upload_dir
}

function wp_verify_nonce($nonce, $action = -1) {
	// TODO: wp_verify_nonce
	return false;
}

function is_serialized( $data ) {
	// TODO: is_serialized: Make check more accurate
	if( !is_string( $data ) ) { return false; }
	if( $data == 'N;' || preg_match( '/^(a|b|d|i|O|s):[0-9]+:".*";$/', $data ) ) {
		return true;
	}
	return false;
}

function is_serialized_string( $data ) {
	if( !is_string( $data ) ) { return false; }
	if( preg_match( '/^s:[0-9]+:".*";$/', $data ) ) {
		return true;
	}
	return false;
}

function maybe_serialize( $data ) {
	if( is_array( $data ) || is_object( $data ) || is_serialized( $data ) ) {
		return serialize( $data );
	}
	return $data;
}

function maybe_unserialize( $original ) {
	if( is_serialized( $original ) ) {
		return unserialize( $original );
	}
	return $original;
}