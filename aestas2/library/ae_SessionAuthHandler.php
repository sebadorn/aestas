<?php


/**
 * Authentification handler using sessions.
 * Only for use in class Permissions.
 */
class ae_SessionAuthHandler implements ae_IAuthHandler {


	public function login( $name, $pass ) {
		ae_Sessions::Start();
		return ae_Sessions::Init( $name, $pass );
	}


	public function logout() {
		return ae_Sessions::End();
	}


	public function get_userid() {
		ae_Sessions::Start();
		if( ae_Sessions::isValidSession() ) {
			return $_SESSION['user_id'];
		}
		return -1;
	}


	public function is_logged_in() {
		return ae_Sessions::isValidSession();
	}


}
