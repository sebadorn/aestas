<?php

/**
 * Manages user roles, their permissions and validates log-in values.
 */
class ae_Permissions {

	protected static $ID = -1;
	protected static $ROLE;
	protected static $STATUS;
	protected static $auth_handler = null;

	protected static $rightsmap = array(
		'admin' => array(
			'welcome' => array(
				'dashboard' => true,
				'statistics' => true,
				'blogroll' => true
			),
			'create' => array(
				'addpost' => true,
				'addpage' => true,
				'addcategory' => true,
				'adduser' => true
			),
			'manage' => array(
				'comments' => true,
				'comments-edit' => true,
				'comments-reply' => true,
				'posts' => true,
				'posts-edit' => true,
				'pages' => true,
				'pages-edit' => true,
				'users' => true,
				'users-edit' => true,
				'categories' => true,
				'categories-edit'=> true
			),
			'media' => array(
				'library' => true,
				'upload' => true
			),
			'theme' => array(
				'choosetheme' => true,
				'uploadtheme' => true,
				'edittheme' => true
			),
			'set' => array(
				'general' => true,
				'comments' => true,
				'rules' => true,
				'newsfeed' => true,
				'database' => true,
				'permalinks' => true
			)
		),
		'author' => array(
			'welcome' => array(
				'dashboard' => true,
				'statistics' => true,
				'blogroll' => true
			),
			'create' => array(
				'addpost' => true,
				'addpage' => true,
				'addcategory' => true
			),
			'manage' => array(
				'comments' => true,
				'comments-edit' => true,
				'comments-reply' => true,
				'posts' => true,
				'posts-edit' => true,
				'pages' => true,
				'pages-edit' => true,
				'users' => true,
				'categories' => true,
				'categories-edit' => true
			),
			'media' => array(
				'library' => true,
				'upload' => true
			),
			'theme' => array(),
			'set' => array(
				'newsfeed' => true
			)
		),
		'guest' => array(
			'welcome' => array(
				'dashboard' => true
			),
			'create' => array(
				'addpost' => true,
				'addcategory' => true
			),
			'manage' => array(
				'comments' => true,
				'comments-reply' => true,
				'posts' => true,
				'users' => true,
				'categories' => true
			),
			'media' => array(
				'library' => true,
				'upload' => true
			),
			'theme' => array(),
			'set' => array()
		)
	);


	/**
	 * Looks up role and status of the current user.
	 * If sessions are used for authentification, it also starts the session.
	 */
	public static function InitRoleAndStatus( $where = 'admin' ) {
		if( $where == 'blog' ) {
			self::$rightsmap = array();
		}

		ae_Cookies::TestCookiePossible_Prepare();

		if( self::$ID == -1 ) {
			self::$auth_handler = self::LoadAuthHandler();
			self::$ID = self::$auth_handler->get_userid();

			if( self::$ID > 0 ) {
				$user = ae_User::getUserById( self::$ID );
				self::$ROLE = $user->getRole();
				self::$STATUS = $user->getStatus();
			}
		}
	}


	/**
	 * Checks if an user has the needed rights to access a certain area.
	 */
	public static function Check( $area, $content, $errorpath = '../admin/' ) {
		if( !self::hasRights( $area, $content, null, $errorpath ) ) {
			echo '<div><strong>Sorry, but you do not have enough rights.</strong></div>';
			//header( 'Location: failedpermissioncheck.php' );
			exit; // TODO: "exit" is too rough.
		}
	}


	/**
	 * The same as the Check function, but for use in scripts without user interface.
	 */
	public static function CheckInScript( $area, $content ) {
		self::Check( $area, $content, '../' );
	}


	/**
	 * Checks if an user has the needed rights to access a certain area.
	 * Returns true if has rights, false otherwise.
	 */
	public static function hasRights( $area, $content, $add_obj = null, $errorpath = '../admin/' ) {
		if( empty( self::$ROLE ) ) {
			header( 'Location: ' . $errorpath . 'index.php?error=notloggedin' );
			exit;
		}

		if( !isset( self::$rightsmap[self::$ROLE][$area] ) ) {
			return false;
		}
		if( !isset( self::$rightsmap[self::$ROLE][$area][$content] )
				|| !self::$rightsmap[self::$ROLE][$area][$content] ) {

			// Everyone is always allowed to edit his/her own things.
			$last_part = explode( '-', $content );
			if( count( $last_part ) > 1 && $last_part[1] == 'edit' ) {
				if( $add_obj instanceof ae_Post ) {
					if( $add_obj->getAuthorId() == self::$ID ) {
						return true;
					}
				}
				else if( $add_obj instanceof ae_Comment ) {
					if( $add_obj->getUserId() == self::$ID ) {
						return true;
					}
				}
				else if( $add_obj instanceof ae_Category ) {
					if( $add_obj->getAuthorId() == self::$ID ) {
						return true;
					}
				}
				else if( $add_obj instanceof ae_Page ) {
					if( $add_obj->getAuthorId() == self::$ID ) {
						return true;
					}
				}
				else if( $add_obj instanceof ae_User ) {
					if( $add_obj->getId() == self::$ID ) {
						return true;
					}
				}
			}

			return false;
		}

		return true;
	}


	/**
	 * Checks if an user has the right to edit/delete a comment.
	 */
	public static function hasPermissionToTakeActionsForComment( ae_CommentQuery $mcq ) {
		if( ROLE == 'admin' || ROLE == 'author' ) {
			return true;
		}
		else if( ROLE == 'guest' && $mcq->comment_post_author_id = ae_Permissions::getIdOfCurrentUser() ) {
			return true;
		}
		return false;
	}


	/**
	 * Checks if an user has the right to edit/delete a post.
	 */
	public static function hasPermissionToTakeActionsForPost( ae_ManagePostQuery $mpq ) {
		if( ROLE == 'admin' || ROLE == 'author' ) {
			return true;
		}
		else if( ROLE == 'guest' && $mpq->get_the_author_meta( 'ID' ) == ae_Permissions::getIdOfCurrentUser() ) {
			return true;
		}
		return false;
	}


	/**
	 * Generates a md5 hash with added salt.
	 */
	public static function HashPassword( $string ) {
		$hash = md5( SALT . $string );
		for( $i = 0; $i < HASH_ROUNDS; $i++ ) {
			$hash = md5( $hash );
		}
		return $hash;
	}


	/**
	 * Logs an user in after verifying name and password.
	 */
	public static function Login( $name, $pass ) {
		if( !ae_User::ExistsUser( $name, $pass ) ) {
			return false;
		}

		if( self::$auth_handler == null ) {
			self::InitRoleAndStatus();
		}
		return self::$auth_handler->login( $name, $pass );
	}


	/**
	 * Logs an user out.
	 */
	public static function Logout() {
		if( self::$auth_handler == null ) {
			self::InitRoleAndStatus();
		}
		return self::$auth_handler->logout();
	}


	/**
	 * Checks if a user is logged in.
	 */
	public static function isLoggedIn() {
		return self::$auth_handler->is_logged_in();
	}


	/**
	 * Generates a key (think of the GET method) for the lost password page.
	 */
	public static function ChangePwdKey( ae_User $user ) {
		return substr( sha1( $user->getPassword() . $user->getId() . SALT ), 2, 9 );
	}


	/**
	 * Generates the value to the in ChangePwdKey() generated key.
	 */
	public static function ChangePwdValue( ae_User $user ) {
		return md5( $user->getId() );
	}


	/**
	 * Checks if the given GET variables are correct and the person is
	 * allowed to change the password. If not, the person is forwarded
	 * to the normal login page and execution of the script ends.
	 * Returns the user ID if no check failed.
	 */
	public static function ChangePwdCheck() {
		if( count( $_GET ) != 1 ) {
			header( 'Location: index.php' );
			mysql_close( $db_connect );
			exit;
		}

		$k = $v = null;
		foreach( $_GET as $key => $value ) {
			$k = $key;
			$v = $value;
		}

		$sql = '
			SELECT
				user_id,
				user_pwd,
				user_email
			FROM `' . TABLE_USERS . '`
			WHERE MD5( user_id ) = "' . mysql_real_escape_string( $v ) . '"
		';

		$user_array = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		$user = new ae_User( $user_array );

		if( self::ChangePwdKey( $user ) != $k ) {
			header( 'Location: index.php' );
			mysql_close( $db_connect );
			exit;
		}

		return $user->getId();
	}



	//---------- Protected functions


	/**
	 * Creates an instance of the fitting authentification handler
	 * according to the chosen way of authentification.
	 * Returns the instance.
	 */
	protected static function LoadAuthHandler() {
		$auth_system = ae_Settings::getSetting( 'auth_system' );

		if( $auth_system == 'session' ) {
			return new ae_SessionAuthHandler();
		}
		else if( $auth_system == 'cookie' ) {
			return new ae_CookieAuthHandler();
		}
		else {
			throw new Exception( ae_ErrorMessages::Unknown( 'authentification system', $auth_system ) );
		}
	}



	//---------- Getter/Setter


	public static function getIdOfCurrentUser() {
		return self::$ID;
	}

	public static function getRoleOfCurrentUser() {
		return self::$ROLE;
	}

	public static function getStatusOfCurrentUser() {
		return self::$STATUS;
	}


}
