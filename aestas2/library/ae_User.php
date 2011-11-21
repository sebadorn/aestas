<?php


class ae_User {


	// Class attributes
	public static $EDITORS = array( 'code', 'ckeditor' );
	public static $ROLES = array( 'admin', 'author', 'guest' );
	public static $STATUSES = array( 'active', 'suspended', 'trash', 'deleted' );

	protected static $user_cache = array();


	// Object attributes
	protected $id = 0;
	protected $name_external = '';
	protected $name_internal = '';
	protected $role = 'guest';
	protected $email = '';
	protected $url = '';
	protected $editor = 'code';
	protected $permalink = '';
	protected $status = 'suspended';
	protected $password_md5 = '';


	public function __construct( $ua = array() ) {
		if( !empty( $ua ) ) {
			$this->id = isset( $ua['user_id'] ) ? $ua['user_id'] : $this->id;
			$this->name_external = isset( $ua['user_name'] ) ? $ua['user_name'] : $this->name_external;
			$this->name_internal = isset( $ua['user_name_login'] ) ? $ua['user_name_login'] : $this->name_internal;
			$this->role = isset( $ua['user_role'] ) ? $ua['user_role'] : $this->role;
			$this->email = isset( $ua['user_email'] ) ? $ua['user_email'] : $this->email;
			$this->url = isset( $ua['user_url'] ) ? $ua['user_url'] : $this->url;
			$this->editor = isset( $ua['user_editor'] ) ? $ua['user_editor'] : $this->editor;
			$this->permalink = isset( $ua['user_permalink'] ) ? $ua['user_permalink'] : $this->permalink;
			$this->status = isset( $ua['user_status'] ) ? $ua['user_status'] : $this->status;
			$this->password_md5 = isset( $ua['user_pwd'] ) ? $ua['user_pwd'] : $this->password_md5;
		}
	}


	/**
	 * Saves the new user to the database.
	 * If the $force_id flag is set to false (default), the database
	 * will generate the ID. It is not recommended to force an ID.
	 */
	public function save_new( $force_id = false ) {
		if( $this->name_internal == '' || $this->password_md5 == '' ) {
			throw new Exception( 'Missing information.' );
		}

		if( $this->name_external == '' ) {
			$this->name_external = $this->name_internal;
		}

		if( $force_id ) {
			if( !ae_Validate::isDigit( $this->id ) || $this->id < 1 ) {
				$this->id = rand( 100, 1000 );
			}
			if( $this->permalink == '' ) {
				$this->generate_permalink();
			}

			$outcome = ae_Database::Query( '
				INSERT INTO `' . TABLE_USERS . '` (
					user_id,
					user_name_login,
					user_name,
					user_permalink,
					user_role,
					user_pwd,
					user_email,
					user_editor,
					user_status
				) VALUES (
					' . mysql_real_escape_string( $this->id ) . ',
					"' . mysql_real_escape_string( $this->name_internal ) . '",
					"' . mysql_real_escape_string( $this->name_external ) . '",
					"' . mysql_real_escape_string( $this->permalink ) . '",
					"' . mysql_real_escape_string( $this->role ) . '",
					"' . mysql_real_escape_string( $this->password_md5 ) . '",
					"' . mysql_real_escape_string( $this->email ) . '",
					"' . mysql_real_escape_string( $this->editor ) . '",
					"' . mysql_real_escape_string( $this->status ) . '"
				)
			' );
		}
		else {
			$outcome = ae_Database::Query( '
				INSERT INTO `' . TABLE_USERS . '` (
					user_name_login,
					user_name,
					user_role,
					user_pwd,
					user_email,
					user_editor,
					user_status
				) VALUES (
					"' . mysql_real_escape_string( $this->name_internal ) . '",
					"' . mysql_real_escape_string( $this->name_external ) . '",
					"' . mysql_real_escape_string( $this->role ) . '",
					"' . mysql_real_escape_string( $this->password_md5 ) . '",
					"' . mysql_real_escape_string( $this->email ) . '",
					"' . mysql_real_escape_string( $this->editor ) . '",
					"' . mysql_real_escape_string( $this->status ) . '"
				)
			' );
		}

		return $outcome;
	}


	/**
	 * Generates a permalink for this object.
	 * However, the new permalink is not yet saved to the database.
	 */
	public function generate_permalink( $permalink_string = '' ) {
		$permalink_string = trim( $permalink_string );
		if( empty( $this->id ) || ( empty( $this->name_external ) && empty( $permalink_string ) ) ) {
			throw new Exception(
				ae_ErrorMessages::CouldNotGeneratePermalink( 'user' )
			);
		}

		$name = empty( $permalink_string ) ? $this->name_external : $permalink_string;
		$suggested_permalink = ae_URL::Author2Permalink( $this->id, $name );

		while( self::ExistsPermalink( $suggested_permalink, $this->id ) ) {
			$suggested_permalink .= date( '-YmdHis' );
		}

		return $this->permalink = $suggested_permalink;
	}


	/**
	 * Saves the current permalink of the object to the database.
	 */
	public function update_permalink() {
		ae_Database::Query( '
			UPDATE `' . TABLE_USERS . '`
			SET
				user_permalink = "' . mysql_real_escape_string( $this->permalink ) . '"
			WHERE user_id = ' . $this->id
		);
	}


	/**
	 * Changes the status of an user.
	 * If the user is already in the trash and the new status is again "trash",
	 * the user will be deleted.
	 */
	public function update_status( $status ) {
		if( !ae_Validate::isUserStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'user status', $status ) );
		}

		if( $status == 'trash' && $this->getStatus() == 'trash' ) {
			$outcome = $this->delete();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_USERS . '`
				SET
					user_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE user_id = ' . mysql_real_escape_string( $this->id )
			);
		}

		return $outcome;
	}


	public function update_to_database() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_USERS . '`
			SET
				user_name_login = "' . mysql_real_escape_string( $this->name_internal ) . '",
				user_name = "' . mysql_real_escape_string( $this->name_external ) . '",
				user_permalink = "' . mysql_real_escape_string( $this->permalink ) . '",
				user_role = "' . mysql_real_escape_string( $this->role ) . '",
				user_email = "' . mysql_real_escape_string( $this->email ) . '",
				user_url = "' . mysql_real_escape_string( $this->url ) . '",
				user_editor = "' .mysql_real_escape_string( $this->editor ) . '",
				user_pwd = "' . mysql_real_escape_string( $this->password_md5 ) . '",
				user_status = "' . mysql_real_escape_string( $this->status ) . '"
			WHERE user_id = ' . $this->id
		);
	}


	/**
	 * Deletes the personal information of the user, but an entry with the ID remains.
	 * Also deletes relations between comments and the user.
	 * Relations between post/page/category and user ID remain.
	 */
	public function delete() {
		// Delete the specific user settings, but keep the ID
		$outcome_user = ae_Database::Query( '
			UPDATE `' . TABLE_USERS . '`
			SET
				user_status = "deleted",
				user_name_login = "",
				user_name = "deleted person",
				user_permalink = "",
				user_role = "guest",
				user_pwd = "",
				user_email = "",
				user_url = "",
				user_editor = "code"
			WHERE user_id = ' . $this->id . '
			AND user_status = "trash"
		' );

		if( !$outcome_user ) {
			return false;
		}

		// Delete relation user to comment
		$outcome_comments = ae_Database::Query( '
			UPDATE `' . TABLE_COMMENTS . '`
			SET
				comment_user = 0
			WHERE comment_user = ' . $this->id
		);

		return $outcome_comments;
	}



	//---------- Static functions


	/**
	 * Loaded users get cached. This method clears the cache.
	 */
	public static function ClearCache() {
		self::$user_cache = array();
	}


	/**
	 * Returns an instance of User for the given ID.
	 */
	public static function getUserById( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		if( isset( self::$user_cache[$id] ) ) {
			return new ae_User( self::$user_cache[$id] );
		}

		$sql = '
			SELECT
				user_id,
				user_name,
				user_name_login,
				user_permalink,
				user_pwd,
				user_email,
				user_url,
				user_editor,
				user_role,
				user_status
			FROM `' . TABLE_USERS . '`
			WHERE user_id = ' . mysql_real_escape_string( $id );

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );
		self::$user_cache[$id] = $u;

		return new ae_User( $u );
	}


	/**
	 * Returns true if an user with the given permalink already exists, false otherwise.
	 */
	public static function ExistsPermalink( $permalink, $not_id = 0 ) {
		return ae_URL::ExistsPermalink( $permalink, 'user', $not_id );
	}


	/**
	 * Returns the e-mail address of the admin user with the lowest ID.
	 */
	public static function AdminEmail() {
		$sql = '
			SELECT
				user_email
			FROM `' . TABLE_USERS . '`
			WHERE user_role = "admin"
			AND ( user_email != "" OR user_email IS NOT NULL )
			ORDER BY user_id ASC
			LIMIT 1
		';

		$admin = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $admin ) ? '' : $admin['user_email'];
	}


	/**
	 * 
	 */
	public static function getAvatar( $id_or_email, $size = '96', $default = '' ) {
		if( ae_Settings::getSetting( 'gravatar' ) == 'false' ) {
			$img = '<img alt="avatar" class="avatar no-gravatar"';
			$img .= ' src=""'; // TODO: Default avatar image
			$img .= ' style="width: ' . $size . 'px; height: ' . $size . 'px;" />' . PHP_EOL;

			return $img;
		}

		if( ae_Validate::isDigit( $id_or_email ) ) {
			$sql = '
				SELECT
					user_email
				FROM `' . TABLE_USERS . '`
				WHERE user_id = ' . $id_or_email;

			$get = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );
			$email = $get['user_email'];
		}
		else {
			$email = $id_or_email;
		}

		$grav_default = ae_Settings::getSetting( 'gravatar_default' );
		if( $grav_default != 'own' && $grav_default != '404' ) {
			$default = $grav_default;
		}

		$rating = ae_Settings::getSetting( 'gravatar_rating' );
		$img = '<img alt="avatar" class="avatar avatar-' . $size . '"';
		$img .= ' src="http://www.gravatar.com/avatar/'. md5( $email );
		$img .= '?d=' . $default . '&amp;s=' . $size . '&amp;r=' . $rating . '"';
		$img .= ' style="width: ' . $size . 'px; height: ' . $size . 'px;" />' . PHP_EOL;

		return $img;
	}


	/**
	 * Attempts to find an user ID by the users e-mail address.
	 */
	public static function FindByMail( $lookup ) {
		if( !ae_Validate::isEmail( $lookup ) ) {
			return false;
		}

		$sql = '
			SELECT user_id
			FROM `' . TABLE_USERS . '`
			WHERE user_email = "' . mysql_real_escape_string( $lookup ) . '"
		';

		$mail = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $mail ) ? false : $mail['user_id'];
	}


	/**
	 * Attempts to find an user ID by the users internal name.
	 */
	public static function FindByNameInternal( $name ) {
		$sql = '
			SELECT user_id
			FROM `' . TABLE_USERS . '`
			WHERE user_name_login = "' . mysql_real_escape_string( $name ) . '"
		';

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $user ) ? false : $user['user_id'];
	}


	/**
	 * Attempts to find an user ID by the users external name.
	 */
	public static function FindByNameExternal( $name ) {
		$sql = '
			SELECT user_id
			FROM `' . TABLE_USERS . '`
			WHERE user_name = "' . mysql_real_escape_string( $name ) . '"
		';

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $user ) ? false : $user['user_id'];
	}


	/**
	 * Returns the user ID to a given name and password or
	 * -1 if such an user does not exist.
	 */
	public static function getUserId( $name, $pass ) {
		$sql = '
			SELECT
				COUNT( user_id ) AS hits,
				user_id AS id
			FROM `' . TABLE_USERS . '`
			WHERE ( user_status != "trash" AND user_status != "deleted" )
			AND user_name_login = "' . mysql_real_escape_string( $name ) . '"
			AND user_pwd = "' . mysql_real_escape_string( ae_Permissions::HashPassword( $pass ) ) . '"
			GROUP BY user_id
		';

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		if( !empty( $user ) && $user['hits'] == 1 && ae_Validate::isDigit( $user['id'] ) ) {
			return $user['id'];
		}
		return -1;
	}


	/**
	 * Checks if one user with the given log-in name and password exists.
	 * Returns true if true, false otherwise.
	 * The difference between this function and GetUserId is, that
	 * this function also considers the user status.
	 */
	public static function ExistsUser( $name, $password ) {
		$password = ae_Permissions::HashPassword( $password );

		$sql = '
			SELECT
				COUNT( user_id ) AS hits
			FROM `' . TABLE_USERS . '`
			WHERE ( user_status != "trash" AND user_status != "deleted" )
			AND user_name_login = "' . mysql_real_escape_string( $name ) . '"
			AND user_pwd = "' . mysql_real_escape_string( $password ) . '"
		';

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $user['hits'] == 1 ) ? true : false;
	}


	/**
	 * Checks if users with the given log-in name exist.
	 * Returns true if true, false otherwise.
	 */
	public static function ExistsUserByName( $name ) {
		$sql = '
			SELECT
				COUNT( user_id ) AS hits
			FROM `' . TABLE_USERS . '`
			WHERE ( user_status != "trash" AND user_status != "deleted" )
			AND user_name_login = "' . mysql_real_escape_string( $name ) . '"
		';

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $user['hits'] >= 1 ) ? true : false;
	}


	/**
	 * Returns all extern user names in array with the user ID as key.
	 */
	public static function getUserNames() {
		$query = ae_Database::Query( '
			SELECT
				user_id,
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE
			user_status != "trash"
			AND user_status != "deleted"
			ORDER BY user_name ASC
		' );

		$names = array();
		while( $user = mysql_fetch_assoc( $query ) ) {
			$names[$user['user_id']] = $user['user_name'];
		}

		return $names;
	}


	/**
	 * Returns an object with some user information.
	 * Used for WP theme compatibility.
	 * @see ae_PostQuery::the_post()
	 */
	public static function getAuthorDataObject( $id ) {
		$user = self::getUserById( $id );

		$authordata = array(
			'ID' => $user->getId(),
			'user_nicename' => $user->getName(),
			'display_name' => $user->getName()
		);

		return (object) $authordata;
	}



	//---------- Getter/Setter


	public function getEditor() {
		return $this->editor;
	}

	public function setEditor( $editor ) {
		if( !ae_Validate::isUserEditor( $editor ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'editor', $editor ) );
		}
		$this->editor = $editor;
	}


	public function getEmail() {
		return $this->email;
	}

	public function getEmailHtml() {
		return htmlspecialchars( $this->email );
	}

	public function setEmail( $email ) {
		$this->email = $email;
	}


	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->id = $id;
	}


	public function getName() {
		return $this->name_external;
	}

	public function getNameHtml() {
		return htmlspecialchars( $this->name_external );
	}

	public function setName( $name ) {
		$this->name_external = $name;
	}


	public function getNameInternal() {
		return $this->name_internal;
	}

	public function getNameInternalHtml() {
		return htmlspecialchars( $this->name_internal );
	}

	public function setNameInternal( $name ) {
		$name = trim( $name );
		if( empty( $name ) ) {
			throw new Exception( 'Name should not be empty and consist of more than just white space.' );
		}
		$this->name_internal = $name;
	}


	public function getPassword() {
		return $this->password_md5;
	}

	public function setPassword( $password ) {
		if( empty( $password ) ) {
			throw new Exception( 'Password cannot be left empty.' );
		}
		$this->password_md5 = ae_Permissions::HashPassword( $password );
	}


	public function getPermalink() {
		return $this->permalink;
	}

	public function setPermalink( $permalink ) {
		$permalink = trim( $permalink );
		if( empty( $permalink ) ) {
			throw new Exception( 'An empty or white space permalink will most likely lead to trouble.' );
		}
		$this->permalink = $permalink;
	}


	public function getRole() {
		return $this->role;
	}

	public function setRole( $role ) {
		if( !ae_Validate::isUserRole( $role ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'user role', $role ) );
		}
		$this->role = $role;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( !ae_Validate::isUserStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'user status', $status ) );
		}
		$this->status = $status;
	}


	public function getUrl() {
		return $this->url;
	}

	public function getUrlHtml() {
		return htmlspecialchars( $this->url );
	}

	public function setUrl( $url ) {
		if( ae_Validate::isUrl( $url ) ) {
			if( !ae_Validate::hasUrlProtocol( $url ) ) {
				$url = 'http://' . $url;
			}
		}
		$this->url = $url;
	}


}
