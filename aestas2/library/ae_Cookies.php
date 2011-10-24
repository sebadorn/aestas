<?php


class ae_Cookies {


	protected static $cooname_login = 'aestas2';
	protected static $cooname_themepreview = 'aestas2-themepreview';
	protected static $cooname_visitor = 'aestas2-visitor';
	protected static $cooname_test = 'aestas2-cookietest';

	protected static $separator_login = ',,';
	protected static $separator_themepreview = ':@:';
	protected static $separator_visitor = '<>';

	// lifetime: if 0 then unlimited, otherwise time() + value
	protected static $login_lifetime = 0;
	protected static $visitor_lifetime = 3600; // time() + self::$visitor_lifetime



	/**
	 * Prepares a simple test to see if the browser accepts cookies.
	 * Here in the _Prepare part a cookie is set.
	 * On ANOTHER page you can then call _Evaluate to see,
	 * if the cookie is still there.
	 *
	 * Notice: These two functions can NOT be used on the SAME page,
	 * since cookies will first show after a browser reload.
	 */
	public static function TestCookiePossible_Prepare() {
		if( isset( $_COOKIE[self::$cooname_test] ) ) {
			return true;
		}
		return setcookie( self::$cooname_test, self::$cooname_test, time() + 300, '/' );
	}


	/**
	 * Evaluates a simple test to see if the browser accepts cookies.
	 * Here in the _Evaluate part it is tested, if the cookie is still there.
	 * On a page BEFORE you have to call _Prepare to set the cookie.
	 *
	 * Notice: These two functions can NOT be used on the SAME page,
	 * since cookies will first show after a browser reload.
	 */
	public static function TestCookiePossible_Evaluate() {
		return isset( $_COOKIE[self::$cooname_test] );
	}



	//----------- Admin area


	/**
	 * Sets a cookie that verifies the user as logged-in.
	 */
	public static function LogInSetCookie( $username, $password ) {
		$content_md5 = md5( $username ) . self::$separator_login . ae_Permissions::HashPassword( $password );
		$lifetime = ( self::$login_lifetime == 0 ) ? 0 : time() + self::$login_lifetime;
		return setcookie( self::$cooname_login, $content_md5, $lifetime, '/' );
	}


	/**
	 * Deletes all previously set cookies: login and theme preview.
	 */
	public static function LogOutDeleteCookies() {
		if( isset( $_COOKIE[self::$cooname_login] ) ) {
			setcookie( self::$cooname_login, $_COOKIE[self::$cooname_login], time() - 90000, '/' );
		}
		if( isset( $_COOKIE[self::$cooname_themepreview] ) ) {
			setcookie( self::$cooname_themepreview, $_COOKIE[self::$cooname_themepreview], time() - 90000, '/' );
		}
		return true;
	}


	/**
	 * Sets a cookie that signals the CMS to use a specific theme.
	 */
	public static function StartThemePreview( $theme, $system = 'wordpress' ) {
		return setcookie( self::$cooname_themepreview, $theme . self::$separator_themepreview . $system, time() + 600, '/' );
	}


	/**
	 * Returns if in theme preview mode.
	 */
	public static function isInThemePreview() {
		return isset( $_COOKIE[self::$cooname_themepreview] );
	}


	/**
	 * Returns an empty array if not in theme preview mode.
	 */
	public static function ThemePreviewGetThemeAndSystem() {
		if( isset( $_COOKIE[self::$cooname_themepreview] ) ) {
			$tp = $_COOKIE[self::$cooname_themepreview];
			$tp = explode( self::$separator_themepreview, $tp );
			return array(
				'theme' => $tp[0],
				'system' => $tp[1]
			);
		}
		return array();
	}


	/**
	 * Deletes a cookie used for theme preview.
	 */
	public static function EndThemePreview() {
		if( isset( $_COOKIE[self::$cooname_themepreview] ) ) {
			setcookie( self::$cooname_themepreview, $_COOKIE[self::$cooname_themepreview], time() - 90000, '/' );
		}
		return isset( $_COOKIE[self::$cooname_themepreview] );
	}


	/**
	 * Returns the user id found out from the cookie set at log-in.
	 * Returns -1 if user could not be found in the database.
	 */
	public static function getUserIdByCookie() {
		if( isset( $_COOKIE['aestas2'] ) ) {
			$cookie_user = explode( ',,', $_COOKIE['aestas2'] );
			$namelogin_md5 = mysql_real_escape_string( $cookie_user[0] );
			$password_md5 = mysql_real_escape_string( $cookie_user[1] );

			$sql = '
				SELECT
					COUNT( user_id ) AS hits,
					user_id AS id
				FROM `' . TABLE_USERS . '`
				WHERE ( user_status != "trash" AND user_status != "deleted" )
				AND MD5( user_name_login ) = "' . $namelogin_md5 . '"
				AND user_pwd = "' . $password_md5 . '"
				GROUP BY user_id
			';

			$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( !empty( $user ) && $user['hits'] == 1 && ae_Validate::isDigit( $user['id'] ) ) {
				return $user['id'];
			}
		}

		return -1;
	}



	//---------- Visitors


	/**
	 * Sets a cookie for someone who left a comment containing his used name, mail and website url.
	 */
	public static function SetCommentAuthorInfo( $author, $email, $url ) {
		$lifetime = ( self::$visitor_lifetime == 0 ) ? 0 : time() + self::$visitor_lifetime;
		setcookie(
			self::$cooname_visitor,
			str_rot13( $author . self::$separator_visitor . $email . self::$separator_visitor . $url ),
			$lifetime,
			'/'
		);
	}


	/**
	 * Returns name, email and url of someone who has commented before on an aestas blog.
	 * Returns an array with empty strings otherwise.
	 */
	public static function CommentAuthorInfo() {
		if( !isset( $_COOKIE[self::$cooname_visitor] ) ) {
			return array(
				0 => '',
				1 => '',
				2 => '',
				'name' => '',
				'email' => '',
				'url' => ''
			);
		}
		$comment_author = explode( self::$separator_visitor, str_rot13( $_COOKIE[self::$cooname_visitor] ) );
		$comment_author['name'] = $comment_author[0];
		$comment_author['email'] = $comment_author[1];
		$comment_author['url'] = $comment_author[2];
		return $comment_author;
	}



	//---------- Posts and pages


	/**
	 * Sets a cookie that verifies, that a correct password for a post or page was entered.
	 */
	public static function SetPostOrPagePwdCookie() {
		if( !isset( $_POST['postpwd'] ) ) {
			return;
		}
		if( SINGLE_POST <= 0 && PAGE_ID <= 0 ) {
			return;
		}

		$id = ( SINGLE_POST > 0 ) ? SINGLE_POST : PAGE_ID;

		$sql = '
			SELECT
				post_pwd AS pwd
			FROM `' . TABLE_POSTS . '`
			WHERE post_id = ' . $id;

		// Generate value and set cookie
		$p = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		if( $p['pwd'] == ae_Post::Encrypt( $_POST['postpwd'] ) ) {
			$value = md5( $id . $p['pwd'] );
			setcookie( 'aestas2-postpwd_' . $id, $value, 0, '/' );
		}
	}


	public static function ValidatePostOrPagePwd( $id, $pwd ) {
		if( !isset( $_COOKIE['aestas2-postpwd_' . $id] ) ) {
			return false;
		}
		if( $_COOKIE['aestas2-postpwd_' . $id] == md5( $id . $pwd ) ) {
			return true;
		}
		return false;
	}


}
