<?php

/**
 * Classes implementing this interface can be used in the class Permissions.
 * Their purpose is to log users in, which means to store some data to
 * uniquely identify them.
 */
interface ae_IAuthHandler {

	/**
	 * Logs user in and stores some data to identify him later on.
	 */
	public function login( $name, $pass );

	/**
	 * Logs user out and deletes the identify data.
	 */
	public function logout();

	/**
	 * Returns user ID of currently logged-in user.
	 * The ID is the same as in the database table.
	 */
	public function get_userid();

	/**
	 * Returns true if user is logged-in, false otherwise.
	 */
	public function is_logged_in();

}
