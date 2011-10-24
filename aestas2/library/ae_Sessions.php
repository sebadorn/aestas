<?php

class ae_Sessions {

	protected static $SESSION_NAME = null;


	/**
	 * Starts the session. It is not possible to start more than one,
	 */
	public static function Start() {
		if( self::isSessionStarted() ) {
			return false;
		}
		session_name( self::GenerateSessionName() );
		return session_start();
	}


	/**
	 * Initializes the session with the user ID as information.
	 */
	public static function Init( $name, $pass ) {
		if( !self::isSessionStarted() ) {
			if( !self::Start() ) {
				return false;
			}
		}
		$_SESSION['user_id'] = ae_User::getUserId( $name, $pass );
		return true;
	}


	/**
	 * Ends the session.
	 */
	public static function End() {
		self::Start();
		unset( $_SESSION );
		return session_destroy();
	}


	/**
	 * Checks if the session has a logged in user.
	 */
	public static function isValidSession() {
		if( !self::isSessionStarted() ) {
			return false;
		}
		$sess_name_set = ( session_name() == self::GenerateSessionName() );
		$sess_uid_set = isset( $_SESSION['user_id'] );
		$valid = ( $sess_name_set && $sess_uid_set );
		if( !$valid ) {
			self::End();
		}
		return $valid;
	}


	/**
	 * Checks if currently a session exists.
	 */
	public static function isSessionStarted() {
		return ( session_id() != '' );
	}


	/**
	 * Returns a generated session name for the user.
	 * Uses the user agent.
	 */
	protected static function GenerateSessionName() {
		if( self::$SESSION_NAME != null ) {
			return self::$SESSION_NAME;
		}
		$verwurscht = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : $_SERVER['REMOTE_ADDR'];
		self::$SESSION_NAME = 'aestas2_' . substr( md5( $verwurscht . SALT ), 0, 8 );
		return self::$SESSION_NAME;
	}


}
