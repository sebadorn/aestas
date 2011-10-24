<?php

function add_option( $name, $value = '', $deprecated = '', $autoload = 'yes' ) {
	$name = strtolower( $name );
	$name = mysql_real_escape_string( $name );
	$value = mysql_real_escape_string( $value );

	$sql = '
		SELECT
			COUNT( set_name ) AS hits
		FROM `' . TABLE_SETTINGS . '`
		WHERE set_name = "' . $name . '"
	';

	$getsets = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	if( $getsets['hits'] > 0 ) {
		return false;
	}

	return ae_Database::Query( '
		INSERT INTO `' . TABLE_SETTINGS . '`
			( set_name, set_value, set_origin )
		VALUES
			( "' . $name . '", "' . $value . '", "wp_options" )
	' );
}

function delete_option( $name ) {
	$name = mysql_real_escape_string( $name );

	$sql = '
		SELECT
			COUNT( set_name ) AS hits
		FROM `' . TABLE_SETTINGS . '`
		WHERE set_name = "' . $name . '"
	';

	// Nothing to delete
	$getsets = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	if( $getsets['hits'] == 0 ) {
		return true;
	}

	// Delete
	$outcome = ae_Database::Query( '
		DELETE FROM `' . TABLE_SETTINGS . '`
		WHERE set_origin = "wp_options"
		AND set_name = "' . $name . '"
	' );

	return ( $outcome > 0 ) ? true : false;
}

function form_option( $option ) {
	// TODO: form_option
	echo esc_attr( $option );
}

function get_alloptions() {
	$allopts = array();

	$getopts = ae_Database::Query( '
		SELECT
			set_name,
			set_value
		FROM `' . TABLE_SETTINGS . '`
		WHERE set_origin = "wp_options"
	' );

	while( $opt = mysql_fetch_object( $getopts ) ) {
		$allopts[$opt->set_name] = $opt->set_value;
	}

	return $allopts;
}

function get_user_option( $option, $user = 0, $check_blog_options = true ) {
	// TODO: get_user_option
	return null;
}

// TODO: $show = 'date_format'
function get_option( $show, $default = false ) {
	if( $show == 'date_format' ) {
		return 'd.m.y H:i';
	}

	$show = strtolower( $show );
	$show = mysql_real_escape_string( $show );

	$sql = '
		SELECT set_value
		FROM `' . TABLE_SETTINGS . '`
		WHERE set_name = "' . $show . '"
	';

	$getset = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

	if( empty( $getset ) ) {
		return '';
	}
	else if( is_serialized( $getset['set_value'] ) ) {
		return unserialize( $getset['set_value'] );
	}
	return $getset['set_value'];
}

/**
 * WP deprecated
 */
function get_settings( $show ) {
	return get_option( $show );
}

function update_option( $option_name, $newvalue ) {
	$option_name = mysql_real_escape_string( $option_name );
	if( is_array( $newvalue ) ) {
		$newvalue = serialize( $newvalue );
	}
	$newvalue = mysql_real_escape_string( $newvalue );

	ae_Database::Query( '
		UPDATE `' . TABLE_SETTINGS . '`
		SET set_value = "' . $newvalue . '"
		WHERE set_origin = "wp_options"
		AND set_name = "' . $option_name . '"
	' );
}

function update_user_option( $user_id, $option_name, $newvalue, $global = false ) {
	return null;
}