<?php


function current_time( $type, $gmt = 0 ) {
	if( $type == 'mysql' ) {
		if( $gmt == 1 || $gmt == true ) {
			return gmdate( 'Y-m-d H:i:s' );
		}
		return date( 'Y-m-d H:i:s' );
	}
	return time();
}

function date_i18n( $dateformatstring, $unixtimestamp = false, $gmt = false ) {
	$utime = !$unixtimestamp ? time() : $unixtimestamp;
	if( $gmt ) {
		return gmdate( $dateformatstring, $utime );
	}
	return date( $dateformatstring, $utime );
}

function get_calendar( $initial = true ) {
	// TODO: get_calendar
}

function get_date_from_gmt( $date, $format = 'Y-m-d H:i:s' ) {
	// TODO: get_date_from_gmt
	// That is wrong, just to return something:
	preg_match( '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $date, $matches );
	$time = mktime( $matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1] );
	return date( $format, $time );
}

/**
 * @param string $timezone 'gmt', 'blog' or 'server'
 */
function get_lastpostdate( $timezone = 'server' ) {
	$sql = '
		SELECT
			post_date
		FROM `' . TABLE_POSTS . '`
		WHERE
			post_list_page IS NULL
			AND post_status = "published"
			AND post_date <= "' . date( 'Y-m-d H:i:s' ) . '"
			AND (
				post_expires > "' . date( 'Y-m-d H:i:s' ) . '"
				OR post_expires = "0000-00-00 00:00:00"
				OR post_expires IS NULL
			)
		ORDER BY post_date DESC
		LIMIT 1
	';

	$post = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	return empty( $post ) ? '' : $post['post_date'];
}

/**
 * @param string $timezone 'gmt', 'blog' or 'server'
 */
function get_lastpostmodified( $timezone = 'server' ) {
	$sql = '
		SELECT post_lastedit
		FROM `' . TABLE_POSTS . '`
		WHERE
			post_list_page IS NULL
			AND post_lastedit IS NOT NULL
			AND post_status = "published"
			AND post_date <= "' . date( 'Y-m-d H:i:s' ) . '"
			AND (
				post_expires > "' . date( 'Y-m-d H:i:s' ) . '"
				OR post_expires = "0000-00-00 00:00:00"
				OR post_expires IS NULL
			)
		ORDER BY post_lastedit DESC
		LIMIT 1
	';

	$post = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	return empty( $post ) ? '' : $post['post_lastedit'];
}

function get_day_link( $year = '', $month = '', $day = '' ) {
	$year = !preg_match( '/^[0-9]{4}$/', $year ) ? date( 'Y' ) : $year;
	$month = !preg_match( '/^[0-9]{2}$/', $month ) ? date( 'm' ) : $month;
	$day = !preg_match( '/^[0-9]{2}$/', $day ) ? date( 'd' ) : $day;

	return URL . '/' . $year . '/' . $month . '/' . $day . '/';
}

function get_gmt_from_date( $date ) {
	// TODO: get_gmt_from_date
}

function get_month_link( $year = '', $month = '' ) {
	$year = !preg_match( '/^[0-9]{4}$/', $year ) ? date( 'Y' ) : $year;
	$month = !preg_match( '/^[0-9]{2}$/', $month ) ? date( 'm' ) : $month;

	return URL . '/' . $year . '/' . $month . '/';
}

function get_weekstartend( $mysqlstring, $start_of_week = '' ) {
	// TODO: get_weekstartend
	return array();
}

function get_year_link( $year = '' ) {
	$year = !preg_match( '/^[0-9]{4}$/', $year ) ? date( 'Y' ) : $year;
	return URL . '/' . $year . '/';
}

function human_time_diff( $from, $to = '' ) {
	$to = !is_int( $to ) ? time() : $to;
	// TODO: human_time_diff
	return '';
}

/**
 * @param string $timezone
 * @return int|float Offset in seconds.
 */
function iso8601_timezone_to_offset( $timezone ) {
	// TODO: iso8601_timezone_to_offset
	return 0;
}

function iso8601_to_datetime( $date_string, $timezone = 'user' ) {
	// TODO: iso8601_to_datetime
	return $date_string;
}

function mysql2date( $dateformatstring, $mysqlstring, $translate = true ) {
	$time = strtotime( $mysqlstring );
	return date( $dateformatstring, $time );
}