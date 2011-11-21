<?php


class ae_Post {


	// Class attributes
	public static $STATUSES = array( 'draft', 'published', 'expired', 'trash' );


	// Object attributes
	protected $id;
	protected $author_id = 0;
	protected $title = '';
	protected $permalink = '';
	protected $content = '';
	protected $excerpt = '';
	protected $keywords = '';
	protected $description = '';

	protected $publish = 'immdiately';
	protected $date;
	protected $expires;
	protected $lastedit;
	protected $lastedit_by;

	protected $comments_enabled = true;
	protected $content_preview = false;
	protected $newsfeed_preview = false;

	protected $content_type = 'html';
	protected $password = '';
	protected $status = 'draft';

	// page properties
	protected $robots = 'index follow';
	protected $list_page;
	protected $parent = 0;


	public function __construct() {
		$this->date = date( 'Y-m-d H:i:s' );
	}


	public function save_new() {
		if( $this->title == '' ) {
			throw new Exception( 'Missing information' );
		}

		if( $this->list_page === null ) {
			$list_page = 'NULL';
			$robots = 'NULL';
			$parent = 'NULL';
		}
		else {
			$list_page = '"' . ( $this->list_page ? 'true' : 'false' ) . '"';
			$robots = '"' . $this->robots . '"';
			$parent = $this->parent;
		}

		return ae_Database::Query( '
			INSERT INTO `' . TABLE_POSTS . '` (
				post_author_id,
				post_date,
				post_publish,
				post_expires,
				post_title,
				post_content,
				post_content_preview,
				post_newsfeed_preview,
				post_excerpt,
				post_type,
				post_keywords,
				post_description,
				post_comment_status,
				post_pwd,
				post_status,
				post_robots,
				post_list_page,
				post_parent
			) VALUES (
				' . mysql_real_escape_string( $this->author_id ) . ',
				"' . mysql_real_escape_string( $this->getDate() ) . '",
				"' . mysql_real_escape_string( $this->getPublish() ) . '",
				"' . mysql_real_escape_string( $this->getExpires() ) . '",
				"' . mysql_real_escape_string( $this->title ) . '",
				"' . mysql_real_escape_string( $this->content ) . '",
				"' . mysql_real_escape_string( $this->getContentPreview() ) . '",
				"' . mysql_real_escape_string( $this->getNewsfeedPreview() ) . '",
				"' . mysql_real_escape_string( $this->excerpt ) . '",
				"' . mysql_real_escape_string( $this->getContentType() ) . '",
				"' . mysql_real_escape_string( $this->keywords ) . '",
				"' . mysql_real_escape_string( $this->description ) . '",
				"' . mysql_real_escape_string( $this->getCommentsEnabled() ) . '",
				"' . mysql_real_escape_string( $this->password ) . '",
				"' . mysql_real_escape_string( $this->getStatus() ) . '",
				' . $robots . ',
				' . $list_page . ',
				' . $parent . '
			)
		' );
	}


	/**
	 * Generates a permalink for this object.
	 * However, the new permalink is not yet saved to the database.
	 */
	public function generate_permalink( $permalink_string = '' ) {
		$permalink_string = trim( $permalink_string );
		if( empty( $this->id ) || empty( $this->date ) || ( empty( $this->title ) && empty( $permalink_string ) ) ) {
			throw new Exception( ae_ErrorMessages::CouldNotGeneratePermalink( 'post' ) );
		}

		$title = empty( $permalink_string ) ? $this->title : $permalink_string;
		if( $this->list_page === null ) {
			$suggested_permalink = ae_URL::Post2Permalink_Datestring( $this->id, $title, $this->date );
		}
		else {
			$suggested_permalink = ae_URL::Page2Permalink_Datestring( $this->id, $title, $this->date );
		}
		while( self::ExistsPermalink( $suggested_permalink, $this->id ) ) {
			$suggested_permalink .= date( '-YmdHis' );
		}
		return $this->permalink = $suggested_permalink;
	}


	/**
	 * Saves the current permalink of the object to the database.
	 */
	public function update_permalink() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_POSTS . '`
			SET
				post_permalink = "' . mysql_real_escape_string( $this->permalink ) . '"
			WHERE post_id = ' . $this->id
		);
	}


	/**
	 * Updates
	 */
	public function update_to_database() {
		if( $this->list_page === null ) {
			$list_page = 'NULL';
			$robots = 'NULL';
			$parent = 'NULL';
		}
		else {
			$list_page = '"' . ( $this->list_page ? 'true' : 'false' ) . '"';
			$robots = '"' . $this->robots . '"';
			$parent = $this->parent;
		}

		return ae_Database::Query( '
			UPDATE `' . TABLE_POSTS . '`
			SET
				post_date = "' . mysql_real_escape_string( $this->getDate() ) . '",
				post_publish = "' . mysql_real_escape_string( $this->getPublish() ) . '",
				post_expires = "' . mysql_real_escape_string( $this->getExpires() ) . '",
				post_lastedit = "' . mysql_real_escape_string( $this->getLastEdit() ) . '",
				post_lastedit_by = ' . mysql_real_escape_string( $this->getLastEditBy() ) . ',
				post_title = "' . mysql_real_escape_string( $this->getTitle() ) . '",
				post_permalink = "' . mysql_real_escape_string( $this->getPermalink() ) . '",
				post_content = "' . mysql_real_escape_string( $this->getContent() ) . '",
				post_content_preview = "' . mysql_real_escape_string( $this->getContentPreview() ) . '",
				post_newsfeed_preview = "' . mysql_real_escape_string( $this->getNewsfeedPreview() ) . '",
				post_excerpt = "' . mysql_real_escape_string( $this->getExcerpt() ) . '",
				post_type = "' . mysql_real_escape_string( $this->content_type ) . '",
				post_keywords = "' . mysql_real_escape_string( $this->getKeywords() ) . '",
				post_description = "' . mysql_real_escape_string( $this->getDescription() ) . '",
				post_comment_status = "' . mysql_real_escape_string( $this->getCommentsEnabled() ) . '",
				post_pwd = "' . mysql_real_escape_string( $this->password ) . '",
				post_status = "' . mysql_real_escape_string( $this->getStatus() ) . '",
				post_robots = ' . $robots . ',
				post_list_page = ' . $list_page . ',
				post_parent = ' . $parent . '
			WHERE post_id = ' . $this->id
		);
	}


	public function update_status( $status ) {
		if( !ae_Validate::isPostStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'status', $status ) );
		}

		if( $status == 'trash' && $this->getStatus() == 'trash' ) {
			$outcome = $this->delete_plus_relations();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_POSTS . '`
				SET
					post_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE post_id = ' . mysql_real_escape_string( $this->id )
			);
		}

		return $outcome;
	}


	public function delete_plus_relations() {
		// Delete post
		$outcome_post = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_POSTS . '`
			WHERE post_id = ' . $this->id . '
			AND post_status = "trash"
		' );

		if( !$outcome_post ) {
			return false;
		}

		if( $this->list_page === null ) {
			return true;
		}

		// Delete comments to post (trackbacks stay)
		$outcome_comments = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_COMMENTS . '`
			WHERE comment_post_id = ' . $this->id . '
		' );

		if( !$outcome_comments ) {
			return false;
		}

		// Delete relations between post and categories and files and this post
		$outcome_categories = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_RELATIONS . '`
			WHERE ( this_id = ' . $this->id . ' AND relation_type = "post to cat" )
			OR ( that_id = ' . $this->id . ' AND relation_type = "file to post" )
		' );

		return $outcome_categories;
	}


	/**
	 * Returns the trackback URLs that were sent from this post divided by a blank.
	 */
	public function getTrackbacksSend() {
		$query = ae_Database::Query( '
			SELECT
				trackback_to_url
			FROM `' . TABLE_TRACKS_SEND . '`
			WHERE trackback_from_id = ' . $this->id
		);

		$out = '';
		while( $t = mysql_fetch_assoc( $query ) ) {
			$out .= $t['trackback_to_url'] . ' ';
		}

		return $out;
	}



	//---------- Static functions


	public static function LoadById( $id ) {
		if( !ae_Permissions::isLoggedIn() ) {
			throw new Exception( ae_ErrorMessages::NotCallableWithoutLogIn() );
		}

		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				post_id,
				post_author_id,
				post_date,
				post_publish,
				post_expires,
				post_lastedit,
				post_lastedit_by,
				post_title,
				post_permalink,
				post_content,
				post_content_preview,
				post_newsfeed_preview,
				post_excerpt,
				post_type,
				post_keywords,
				post_description,
				post_comment_status,
				post_pwd,
				post_status,
				post_list_page,
				post_robots,
				post_parent
			FROM `' . TABLE_POSTS . '`
			WHERE post_id = ' . mysql_real_escape_string( $id );

		$s = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		if( empty( $s ) ) {
			return null;
		}

		$p = new ae_Post();
		$p->id = $s['post_id'];
		$p->author_id = $s['post_author_id'];
		$p->title = $s['post_title'];
		$p->permalink = $s['post_permalink'];
		$p->content = $s['post_content'];
		$p->excerpt = $s['post_excerpt'];
		$p->keywords = $s['post_keywords'];
		$p->description = $s['post_description'];
		$p->publish = $s['post_publish'];
		$p->date = $s['post_date'];
		$p->expires = $s['post_expires'];
		$p->lastedit = $s['post_lastedit'];
		$p->lastedit_by = $s['post_lastedit_by'];
		$p->comments_enabled = $s['post_comment_status'];
		$p->content_preview = $s['post_content_preview'];
		$p->newsfeed_preview = $s['post_newsfeed_preview'];
		$p->content_type = $s['post_type'];
		$p->password = $s['post_pwd'];
		$p->status = $s['post_status'];

		if( !empty( $s['post_robots'] ) ) {
			$p->robots = $s['post_robots'];
		}
		if( $s['post_list_page'] == 'true' || $s['post_list_page'] == 'false' ) {
			$p->list_page = ( $s['post_list_page'] == 'true' );
		}
		if( !empty( $s['post_parent'] ) ) {
			$p->parent = $s['post_parent'];
		}

		return $p;
	}


	public static function ExistsId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				post_id
			FROM `' . TABLE_POSTS . '`
			WHERE post_id = ' . mysql_real_escape_string( $id );

		$p = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return !empty( $p );
	}


	public static function ExistsPermalink( $permalink, $not_id = 0, $is_page = false ) {
		$type = $is_page ? 'page' : 'post';
		return ae_URL::ExistsPermalink( $permalink, $type, $not_id );
	}


	/**
	 * Encrypts a given string.
	 */
	public static function Encrypt( $string ) {
		$encrypt = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			md5( SALT ),
			$string,
			MCRYPT_MODE_CBC,
			md5( md5( SALT ) )
		);
		return base64_encode( $encrypt );
	}



	//---------- Protected static functions


	/**
	 * Decrypts a given string.
	 */
	protected static function Decrypt( $string ) {
		$decrypt = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			md5( SALT ),
			base64_decode( $string ),
			MCRYPT_MODE_CBC,
			md5( md5( SALT ) )
		);
		return rtrim( $decrypt, "\0" );
	}



	//---------- Getter/Setter


	public function getAuthorId() {
		return $this->author_id;
	}

	public function setAuthorId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->author_id = $id;
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


	public function getCommentsEnabled() {
		return ( $this->comments_enabled ? 'open' : 'closed' );
	}

	public function setCommentsEnabled( $comments ) {
		if( !is_bool( $comments ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', $comments ) );
		}
		$this->comments_enabled = $comments;
	}


	public function getContent() {
		return $this->content;
	}

	public function getContentForTextarea() {
		return htmlspecialchars( $this->content );
	}

	public function setContent( $content ) {
		$this->content = $content;
	}


	public function getContentPreview() {
		return $this->content_preview;
	}

	public function setContentPreview( $content_preview ) {
		if( !is_bool( $content_preview ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', $content_preview ) );
		}
		$this->content_preview = $content_preview;
	}


	public function getContentType() {
		return $this->content_type;
	}

	public function setContentType( $type ) {
		if( $type != 'html' && $type != 'php' ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( '"html" or "php"', $type ) );
		}
		$this->content_type = $type;
	}


	public function getDate() {
		return $this->date;
	}

	public function getDateTimestamp() {
		return strtotime( $this->date );
	}

	public function setDate( $date = '' ) {
		if( empty( $date ) ) {
			$date = date( 'Y-m-d H:i:s' );
		}
		else if( !ae_Validate::isTimestamp_MySQL( $date ) ) {
			throw new Exception( ae_ErrorMessages::NotADate_MySQL() );
		}
		$this->date = $date;
	}


	public function getDescription() {
		return $this->description;
	}

	public function getDescriptionForTextarea() {
		return htmlspecialchars( $this->description );
	}

	public function setDescription( $description ) {
		$this->description = strip_tags( $description );
	}


	public function getExcerpt() {
		return $this->excerpt;
	}

	public function getExcerptForTextarea() {
		return htmlspecialchars( $this->excerpt );
	}

	public function setExcerpt( $excerpt ) {
		$this->excerpt = $excerpt;
	}


	public function getExpires() {
		return $this->expires;
	}

	public function getExpiresTimestamp() {
		return strtotime( $this->expires );
	}

	public function setExpires( $expires = '' ) {
		if( empty( $expires ) || $expires == 'NULL' ) {
			$this->expires = 'NULL';
		}
		else if( !ae_Validate::isTimestamp_MySQL( $expires) ) {
			throw new Exception( ae_ErrorMessages::NotADate_MySQL() );
		}
		$this->expires = $expires;
	}


	public function getKeywords() {
		return $this->keywords;
	}

	public function setKeywords( $tags,  $tags_js = array() ) {
		if( is_string( $tags ) && empty( $tags_js ) ) {
			$this->keywords = $tags;
		}
		else if( is_array( $tags ) && empty( $tags_js ) ) {
			$this->keywords = ae_Misc::ProcessTags2String( $tags );
		}
		else if( is_string( $tags ) && is_array( $tags_js ) ) {
			$this->keywords = ae_Misc::ProcessTags2String( $tags, $tags_js );
		}
	}


	public function getLastEdit() {
		return $this->lastedit;
	}

	public function getLastEditTimestamp() {
		return strtotime( $this->lastedit );
	}

	public function setLastEdit( $date = '' ) {
		if( empty( $date ) || $date == 'NULL' ) {
			$this->lastedit = 'NULL';
		}
		else if( !ae_Validate::isTimestamp_MySQL( $date ) ) {
			throw new Exception( ae_ErrorMessages::NotADate_MySQL() );
		}
		$this->lastedit = $date;
	}


	public function getLastEditBy() {
		return $this->lastedit_by;
	}

	public function setLastEditBy( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->lastedit_by = $id;
	}


	public function getNewsfeedPreview() {
		return $this->newsfeed_preview;
	}

	public function setNewsfeedPreview( $newsfeed_preview ) {
		if( !is_bool( $newsfeed_preview ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', $newsfeed_preview ) );
		}
		$this->newsfeed_preview = $newsfeed_preview;
	}


	public function getPassword() {
		if( empty( $this->password ) ) {
			return '';
		}
		return self::Decrypt( $this->password );
	}

	public function setPassword( $password ) {
		if( empty( $password ) ) {
			$this->password = '';
		}
		else {
			$this->password = self::Encrypt( $password );
		}
	}


	public function getPermalink() {
		return $this->permalink;
	}

	public function setPermalink( $permalink ) {
		$this->permalink = $permalink;
	}


	public function getPublish() {
		return $this->publish;
	}

	public function setPublish( $publish ) {
		if( $publish != 'immediately' && $publish != 'scheduled' ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'way of publishing', $publish ) );
		}
		$this->publish = $publish;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( $status != 'published' && $status != 'draft' && $status != 'trash' ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'status', $status ) );
		}
		$this->status = $status;
	}


	public function getTitle() {
		return $this->title;
	}

	public function getTitleHtml() {
		return htmlspecialchars( $this->title );
	}

	public function setTitle( $title ) {
		if( empty( $title ) ) {
			$title = 'Untitled';
		}
		$this->title = $title;
	}


	public function getRobots() {
		return $this->robots;
	}

	public function setRobots( $robots ) {
		if( $robots != 'index follow' && $robots != 'noindex nofollow' ) {
			throw new Exception(
				ae_ErrorMessages::ValueNotExpected( '"index follow" or "noindex nofollow"', $robots )
			);
		}
		$this->robots = $robots;
	}


	public function getShowInList() {
		return ( $this->list_page == 'true' );
	}

	public function setShowInList( $list_page ) {
		if( !is_bool( $list_page ) ) {
			throw new Exception(
				ae_ErrorMessages::TypeNotExpected( 'boolean', $list_page )
			);
		}
		$this->list_page = $list_page;
	}

	public function setIsPage( $bool ) {
		$this->list_page = $bool ? false : null;
	}

	public function setIsPost( $bool ) {
		$this->list_page = $bool ? null : false;
	}


	public function getParent() {
		return $this->parent;
	}

	public function getParentForMySQL() {
		return ( $this->parent == 0 ) ? 'NULL' : $this->parent;
	}

	public function setParent( $parent ) {
		if( !ae_Validate::isDigit( $parent ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->parent = $parent;
	}


}
