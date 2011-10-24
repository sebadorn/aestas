<?php

class ae_Database {

	const SINGLE_RESULT = 1;

	protected static $query_count = 0;


	/**
	 * Executes a given SQL statement and returns the query.
	 * Throws an exception in error case.
	 */
	public static function Query( $sql ) {
		$query = mysql_query( $sql );

		self::$query_count++;

		if( $query === false ) {
			throw new Exception(
				ae_ErrorMessages::MySQLQuery( mysql_errno(), mysql_error() )
			);
		}

		return $query;
	}


	/**
	 * Executes a given SQL statement and returns an associated array.
	 * If $opt is set to SINGLE_RESULT, the returned array contains directly the first result.
	 */
	public static function Assoc( $sql, $opt = 0 ) {
		$query = self::Query( $sql );
		$result = array();

		while( $a = mysql_fetch_assoc( $query ) ) {
			$result[] = $a;
		}

		return ( $opt == self::SINGLE_RESULT ) ? reset( $result ) : $result;
	}


	/**
	 * Increases the query counter by one.
	 */
	public static function incQueryCount() {
		self::$query_count++;
	}


	/**
	 * Returns the number of queries executed.
	 */
	public static function getQueryCount() {
		return self::$query_count;
	}


}
