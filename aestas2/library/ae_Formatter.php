<?php

class ae_Formatter {


	protected static $months = array(
		'',
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);


	/**
	 * Adds a missing '0' for month numbers smaller than 10.
	 */
	public static function MonthToString( $month ) {
		return ( $month < 10 ) ? '0' . $month : (string) $month;
	}


	/**
	 * Switches the number for a month to its name.
	 * Example: 2 -> February
	 */
	public static function MonthNumberToName( $month ) {
		if( $month >= 1 && $month <= 12 ) {
			return self::$months[$month];
		}
		throw new Exception( ae_ErrorMessages::ValueNotExpected( 'number 1-12', $month ) );
	}


	/**
	 * Returns $i tabs.
	 */
	public static function Tabs( $i ) {
		return str_repeat( "\t", $i );
	}


}
