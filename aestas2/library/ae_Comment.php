<?php


class ae_Comment {


	// Class attributes
	public static $STATUSES = array( 'unapproved', 'approved', 'spam', 'trash' );
	public static $DEFAULT_NAME = 'Anonymous';
	public static $GRAVATAR_RATINGS = array( 'g', 'pg', 'r', 'x' );
	public static $ALLOWED_TAGS = array(
		'a', 'abbr', 'b', 'blockquote', 'cite',
		'code', 'del', 'em', 'i', 'strong'
	);
	public static $ALLOWED_ATTRIBUTES = array( 'alt', 'cite', 'href', 'name', 'title' );


	// Object attributes
	protected $id;
	protected $parent_id = 0;
	protected $author = '';
	protected $ip;
	protected $userid = 0;
	protected $content = '';
	protected $email = '';
	protected $url = '';
	protected $date;
	protected $status = 'unapproved';

	protected $post_id;
	protected $post_author_id;
	protected $has_type = 'comment';

	protected $in_database = false;


	public function __construct( $source = '' ) {
		if( is_array( $source ) ) {
			$this->id = $source['comment_id'];
			$this->parent_id = $source['comment_parent'];
			$this->author = $source['comment_author'];
			$this->ip = $source['comment_ip'];
			$this->userid = $source['comment_user'];
			$this->content = $source['comment_content'];
			$this->email = $source['comment_email'];
			$this->url = $source['comment_url'];
			$this->date = $source['comment_date'];
			$this->status = $source['comment_status'];
			$this->post_id = $source['comment_post_id'];
			$this->has_type = $source['comment_has_type'];
			$this->post_author_id = $source['post_author_id'];
		}
	}


	public function save_new() {
		if( $this->ip == null ) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}
		if( $this->date == null ) {
			$this->setDate();
		}

		$outcome = ae_Database::Query( '
			INSERT INTO `' . TABLE_COMMENTS . '` (
				comment_ip,
				comment_date,
				comment_user,
				comment_author,
				comment_email,
				comment_url,
				comment_parent,
				comment_content,
				comment_has_type,
				comment_post_id,
				comment_status
			) VALUES (
				"' . mysql_real_escape_string( $this->ip ) . '",
				"' . mysql_real_escape_string( $this->date ) . '",
				' . $this->userid . ',
				"' . mysql_real_escape_string( $this->author ) . '",
				"' . mysql_real_escape_string( $this->email ) . '",
				"' . mysql_real_escape_string( $this->url ) . '",
				' . $this->parent_id . ',
				"' . mysql_real_escape_string( $this->content ) . '",
				"' . mysql_real_escape_string( $this->has_type ) . '",
				' . $this->post_id . ',
				"' . mysql_real_escape_string( $this->status ) . '"
			)
		' );

		$this->in_database = true;
		return $outcome;
	}


	public function update_status( $status ) {
		if( !ae_Validate::isCommentStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'comment status', $status ) );
		}

		if( $status == 'trash' && $this->getStatus() == 'trash' ) {
			$outcome = $this->delete();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_COMMENTS . '`
				SET
					comment_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE comment_id = ' . mysql_real_escape_string( $this->id )
			);
		}

		return $outcome;
	}


	public function delete() {
		return ae_Database::Query( '
			DELETE
			FROM `' . TABLE_COMMENTS . '`
			WHERE comment_id = ' . $this->id . '
			AND comment_status = "trash"
		' );
	}


	public function update_to_database() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_COMMENTS . '`
			SET
				comment_user = ' . $this->userid . ',
				comment_date = "' . mysql_real_escape_string( $this->date ) . '",
				comment_author = "' . mysql_real_escape_string( $this->author ) . '",
				comment_email = "' . mysql_real_escape_string( $this->email ) . '",
				comment_url = "' . mysql_real_escape_string( $this->url ) . '",
				comment_content = "' . mysql_real_escape_string( $this->content ) . '",
				comment_status = "' . mysql_real_escape_string( $this->status ) . '"
			WHERE comment_id = ' . $this->id
		);
	}


	public function contentNl2Br() {
		$this->content = nl2br( $this->content );
	}


	/**
	 * Removes all allowed tags of a type if:
	 * - The number of opening and closing tags don't match.
	 *
	 * Adds a block element (p) to blockquotes.
	 */
	public function contentCorrectHtml() {
		$this->content = $this->content;

		$problem_tags = array();

		foreach( ae_Comment::$ALLOWED_TAGS as $tag ) {
			$hits_opened = preg_match_all( '#<' . $tag . '( [^>]*)?>#i', $this->content, $opened );
			$hits_closed = preg_match_all( '#</' . $tag . '>#i', $this->content, $closed );

			// Test: not as many opened tags as closed ones
			if( $hits_opened != $hits_closed ) {
				$problem_tags[] = $tag;
			}
		}

		$without_problem_tags = array_diff( ae_Comment::$ALLOWED_TAGS, $problem_tags );

		/* Why not using strip_tags()?
		 * The PHP function strip_tags() is a little overzealous and
		 * would also remove smilies like <3 or <(' ')<.
		 */
		if( !empty( $problem_tags ) ) {
			$problem_tags = implode( '|', $problem_tags );

			$this->content = preg_replace( // opening tags
				'#</?(' . $problem_tags . ')( [^>]*)?>#i',
				'',
				$this->content
			);
		}
	}


	public function contentBlockquoteStrict() {
		// Blockquote needs to contain a block element (strict standard)
		if( strstr( $this->content, 'blockquote') !== false ) {
			$tag_replace = array(
				'!<blockquote( +cite *=["\']?[^"\']*["\']?)? *>(<br />)?!i',
				'!</blockquote>(<br />)?!i'
			);
			$tag_replace_with = array( '<blockquote$1><p>', '</p></blockquote>' );
			$this->content = preg_replace( $tag_replace, $tag_replace_with, $this->content );
		}
	}


	public function contentDefuse() {
		$this->content = htmlspecialchars( $this->content, ENT_NOQUOTES );

		$this->content = str_replace( '&gt;', '>', $this->content );

		$tags = self::getAllowedTags( 'preg_replace' );
		$attributes = self::getAllowedAttributes( 'preg_replace' );

		$this->content = preg_replace(
			'#&lt;(/?(?:' . $tags . ') *(?:(' . $attributes . ') *= *[^>]*)*)>#',
			'<$1>',
			$this->content
		);
	}


	public function hasApprovedCommentFromBefore() {
		if( $this->email == '' ) {
			return false;
		}

		$sql = '
			SELECT
				COUNT( comment_id ) AS hits
			FROM `' . TABLE_COMMENTS . '`
			WHERE comment_email = "' . mysql_real_escape_string( $this->email ) . '"
			AND comment_status = "approved"
		';

		$search = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $search['hits'] > 0 );
	}



	//---------- Static functions


	public static function getCommentById( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				comment_id,
				comment_user,
				comment_ip,
				comment_author,
				comment_email,
				comment_url,
				comment_date,
				comment_content,
				comment_has_type,
				comment_status,
				comment_parent,
				comment_post_id,
				post_author_id
			FROM `' . TABLE_COMMENTS . '`
			LEFT JOIN `' . TABLE_POSTS . '`
			ON comment_post_id = post_id
			WHERE comment_id = ' . mysql_real_escape_string( $id );

		$c = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $c ) ? null : new ae_Comment( $c );
	}


	/**
	* Variants:
	* strip_tags - &lt;a&gt;&lt;b&gt;
	* preg_replace - a|b
	*/
	public static function getAllowedTags( $variant = null ) {
		if( $variant == 'strip_tags' ) {
			return '<' . implode( '><', self::$ALLOWED_TAGS ) . '>';
		}
		return implode( '|', self::$ALLOWED_TAGS );
	}


	/**
	* Variants:
	* preg_replace - a|b
	* otherwise - as array
	*/
	public static function getAllowedAttributes( $variant = null ) {
		if( $variant == 'preg_replace' ) {
			return implode( '|', self::$ALLOWED_ATTRIBUTES );
		}
		return self::$ALLOWED_ATTRIBUTES;
	}



	//---------- Getter/Setter


	public function getAuthor() {
		return $this->author;
	}

	public function setAuthor( $author ) {
		$author = trim( $author );
		if( empty( $author ) ) {
			$author = ae_Comment::$DEFAULT_NAME;
		}
		$this->author = htmlspecialchars( $author );
	}


	public function getId() {
		if( $this->id == null && $this->in_database ) {
			$sql = '
				SELECT
					comment_id
				FROM `' . TABLE_COMMENTS . '`
				WHERE comment_ip = "' . mysql_real_escape_string( $this->ip ) . '"
				AND comment_date = "' . mysql_real_escape_string( $this->date ) . '"
				AND comment_author = "' . mysql_real_escape_string( $this->author ) . '"
				ORDER BY comment_id DESC
				LIMIT 1
			';

			$last = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( !empty( $last ) ) {
				$this->id = $last['comment_id'];
			}
		}
		return $this->id;
	}

	public function setId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->id = $id;
	}


	public function getIp() {
		return $this->ip;
	}

	public function setIp( $ip ) {
		if( !ae_Validate::isIp( $ip ) ) {
			throw new Exception( ae_ErrorMessages::NotAnIp() );
		}
		$this->ip = $ip;
	}


	public function getContent() {
		return $this->content;
	}

	public function getContentForTextarea() {
		$out = str_replace( '<br />', '', $this->content );
		$out = htmlspecialchars( $out );
		return $out;
	}

	public function setContent( $content ) {
		$this->content = $content;
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


	public function getEmail() {
		return $this->email;
	}

	public function setEmail( $email ) {
		$this->email = htmlspecialchars( trim( $email ) );
	}


	public function getHasType() {
		return $this->has_type;
	}

	public function setHasType( $type ) {
		if( $type != 'comment' && $type != 'trackback' ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'comment or trackback', $type ) );
		}
		$this->to_type = $type;
	}

	public function isTrackback() {
		return ( $this->has_type == 'trackback' );
	}


	public function getParentId() {
		return $this->parent_id;
	}

	public function setParentId( $id ) {
		if( !ae_Validate::isDigit( $id ) || $id < 0 ) {
			$id = 0;
		}
		$this->parent_id = $id;
	}


	public function getPostAuthorId() {
		return $this->post_author_id;
	}


	public function getPostId() {
		return $this->post_id;
	}

	public function setPostId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->post_id = $id;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( !ae_Validate::isCommentStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'comment status', $status ) );
		}
		$this->status = $status;
	}


	public function getUrl() {
		return $this->url;
	}

	public function setUrl( $url ) {
		$url = trim( $url );
		if( ae_Validate::isUrl( $url ) ) {
			if( !ae_Validate::hasUrlProtocol( $url ) ) {
				$url = 'http://' . $url;
			}
		}
		$this->url = htmlspecialchars( $url );
	}


	public function getUserId() {
		return $this->userid;
	}

	public function setUserId( $id ) {
		if( $id < 0 ) {
			$id = 0;
		}
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->userid = $id;
	}


}
