<?php


/**
 * Authentification handler using cookies.
 * Only for use in class Permissions.
 */
class ae_CookieAuthHandler implements ae_IAuthHandler {


	public function login( $name, $pass ) {
		return ae_Cookies::LogInSetCookie( $name, $pass );
	}


	public function logout() {
		return ae_Cookies::LogOutDeleteCookies();
	}


	public function get_userid() {
		return ae_Cookies::getUserIdByCookie();
	}


	public function is_logged_in() {
		return ( ae_Cookies::getUserIdByCookie() > 0 );
	}


}
