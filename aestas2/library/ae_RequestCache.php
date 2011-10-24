<?php

/**
 * Little cache with only the lifetime of a request.
 * Goal of this class is to prevent the system from doing
 * sql queries or expensive functions more than once.
 */
class ae_RequestCache {


	protected static $store = array();
	protected static $overwrite = true; // If a key already exists.


	public static function Save( $key, $value ) {
		if( empty( $key ) ) {
			throw new Exception( 'Key is empty.' );
		}
		else if( array_key_exists( $key, self::$store ) && !self::$overwrite ) {
			throw new Exception( 'Given key ' . $key . ' is already cached and overwriting disabled.' );
		}
		self::$store[$key] = $value;
	}


	public static function Load( $key ) {
		if( !array_key_exists( $key, self::$store ) ) {
			throw new Exception( 'Given key ' . $key . ' cannot be found in cache.' );
		}
		return self::$store[$key];
	}


	public static function hasKey( $key ) {
		return array_key_exists( $key, self::$store );
	}


	public static function Delete( $key ) {
		unset( self::$store[$key] );
	}


	public static function DeleteAll() {
		foreach( self::$store as $key => $value ) {
			unset( self::$store[$key] );
		}
	}


}
