<?php


class ae_CommentQuery {

	protected $comments;
	protected $comment_internal;
	protected $post_to_comment;
	protected $count_comments;
	protected $dumped_comments;
	protected $unbound = false;

	protected $related_id;


	/**
	 * The $unbound flag indicates if comments shall be loaded for
	 * a specific post or if just all comments shall be loaded. The later case
	 * should only be interesting for the newsfeed and uses the set newsfeed limit.
	 */
	public function __construct( $unbound = false ) {
		$this->unbound = $unbound;
		$this->comments = array();

		if( !$unbound ) {
			$this->related_id = ae_EngineGateway::Call( 'the_ID' );
		}

		$this->count_comments = $this->count_comments();
		$this->dumped_comments = 0;

		$bounds = ' WHERE ';
		$order = 'ASC';
		$limit = '';
		if( !$this->unbound ) {
			$bounds = ' WHERE comment_post_id = ' . $this->related_id . ' AND ';
		}
		else {
			$order = 'DESC';
			$limit = ' LIMIT ' . ae_Settings::NewsfeedLimit();
		}

		$sql = '
			SELECT
				comment_id,
				comment_ip,
				comment_user,
				comment_author,
				comment_email,
				comment_url,
				comment_date,
				comment_has_type,
				comment_post_id,
				comment_parent,
				comment_content,
				comment_status
			FROM `' . TABLE_COMMENTS . '`
			' . $bounds
			. ' comment_status = "approved"
			ORDER BY
				comment_date ' . $order . ',
				comment_id ' . $order
			. $limit;

		$this->comments = ae_Database::Assoc( $sql );
	}


	// More Comment related


	public function count_comments() {
		if( $this->unbound ) {
			$sql = '
				SELECT
					COUNT( comment_id ) AS count
				FROM `' . TABLE_COMMENTS . '`
				WHERE comment_status = "approved"
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( comment_id ) AS count
				FROM `' . TABLE_COMMENTS . '`
				WHERE comment_post_id = ' . $this->related_id . '
				AND comment_status = "approved"
			';
		}

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function have_comments() {
		if( count( $this->comments ) <= 0 ) {
			unset( $this->comments );
			return false;
		}
		return true;
	}


	public function the_comment() {
		$this->comment_internal = $this->comments[$this->dumped_comments];
		unset( $this->comments[$this->dumped_comments] );
		if( $this->unbound ) {
			$this->post_to_comment = null;
		}
		$this->dumped_comments++;
	}


	/**
	 * WP
	 */
	public function wp_list_comments( $args = '' ) {
		while( $this->have_comments() ) {
			$this->the_comment();
			echo '
			<li class="comment byuser comment-author-admin bypostauthor odd alt thread-odd thread-alt depth-1" id="comment-' , $this->comment_ID() , '">
				<div id="div-comment-' , $this->comment_ID() , '">
					<div class="comment-author vcard">
						' , $this->get_avatar( '', 40 ) , '
						<cite class="fn">' , $this->comment_author_link() , '</cite>
						<span class="says">says:</span>
					</div>
					<div class="comment-meta commentmetadata">
						<a href="' , $this->comment_permalink() , '">' , $this->comment_date() , '</a>
					</div>
					<p>' , $this->comment_text() , '</p>
					<div class="reply">
					</div>
				</div>
			</li>';
		}
	}


	// TODO: comments_link
	public function comments_link() {
		
	}


	// TODO: comments_rss_link
	public function comments_rss_link() {
		
	}


	// TODO: comments_popup_script
	public function comments_popup_script() {
		
	}


	// TODO: comments_popup_link
	public function comments_popup_link() {
		
	}


	public function comment_ID() {
		return $this->comment_internal['comment_id'];
	}


	public function comment_author() {
		return $this->comment_internal['comment_author'];
	}


	/**
	 * WP
	 * @return string Name of the author and, if given, linked to his/her website.
	 */
	public function comment_author_link() {
		if( $this->comment_internal['comment_url'] != ''
				&& ae_Validate::isUrl( $this->comment_internal['comment_url'] ) ) {
			return '<a href="' . $this->comment_author_url() . '" rel="external" class="url">'
				. $this->comment_author() . '</a>';
		}
		else {
			return $this->comment_author();
		}
	}


	/**
	 * NOT RECOMMENDED! Spambots will very likely farm those addresses.
	 * @return string If given, the E-mail-address of the author.
	 */
	public function comment_author_email() {
		return $this->comment_internal['comment_email'];
	}


	/**
	 * NOT RECOMMENDED! Spambots will very likely farm those addresses.
	 * WP
	 * @param string $linktext Linktext. Default is the e-mail-address.
	 * @param string $before String before link.
	 * @param string $after String after link.
	 * @return string If given, the e-mail-address of the author as mailto-link.
	 */
	public function comment_author_email_link( $linktext = '', $before = '', $after = '' ) {
		if( !ae_Validate::isEmail( $this->comment_internal['comment_email'] ) ) {
			return $this->comment_author_email();
		}
		if( $linktext == null ) {
			$linktext = $this->comment_internal['comment_email'];
		}
		return $before . '<a href="mailto:' . $this->comment_author_email() . '">' . $linktext . '</a>' . $after;
	}


	/**
	 * @return If given, the website URL of the author.
	 */
	public function comment_author_url() {
		return $this->comment_internal['comment_url'];
	}


	/**
	 * @param $linktext String Linktext. Default is the URL.
	 * @param $before String before link.
	 * @param $after String after link.
	 * @return If given, the website URL of the author as link.
	 */
	public function comment_author_url_link( $linktext = '', $before = '', $after = '' ) {
		if( !ae_Validate::isUrl( $this->comment_internal['comment_url'] ) ) {
			return $this->comment_author_url();
		}
		if( $linktext == null ) {
			$linktext = $this->comment_internal['comment_url'];
		}
		return $before . '<a href="' . $this->comment_author_url() . '">' . $linktext . '</a>' . $after;
	}


	/**
	 * @return IP of the author.
	 */
	public function comment_author_IP() {
		return $this->comment_internal['comment_ip'];
	}


	public function comment_to_type() {
		return $this->comment_internal['comment_to_type'];
	}


	/**
	 * @return "comment", "trackback" or "pingback"
	 */
	public function comment_type() {
		return 'comment';
	}


	/**
	 * @return Text of the comment.
	 */
	public function comment_text() {
		$text = $this->correctTypography( $this->comment_internal['comment_content'] );
		return $text;
	}


	/**
	 * comment_excerpt
	 * Strips all tags but while outputting it replaces line breaks with their (X)HTML equivalent
	 * @param $length Number of words after that will be cut
	 * @param $html strip, encode, keep
	 * @return Excerpt of the text of the comment.
	 */
	public function comment_excerpt( $length = 20, $dots = true, $html = 'encode' ) {
		switch( $html ) {
			case 'strip':
				$this->comment_internal['comment_content'] = strip_tags( $this->comment_internal['comment_content'] );
				break;
			case 'encode':
				$this->comment_internal['comment_content'] = str_replace(
					'&lt;br /&gt;',
					'',
					htmlspecialchars( $this->comment_internal['comment_content'] )
				);
				break;
			case 'keep':
				break;
			default:
				$this->comment_internal['comment_content'] = str_replace(
					'&lt;br /&gt;',
					'',
					htmlspecialchars( $this->comment_internal['comment_content'] )
				);
		}

		$excerpt = explode( ' ', $this->comment_internal['comment_content'] );
		$excerpt_length = count( $excerpt );

		$excerpt_return = '';

		for( $i = 0; $i < $length && $i < $excerpt_length; $i++ ) {
			$excerpt_return .= nl2br( $excerpt[$i] ) . ' ';

			if( $i == $length - 1 && $i < $excerpt_length - 1 ) {
				$excerpt_return .= '<span class="cropped">[…]</span>';
			}
		}

		return str_replace( '&', '&amp;', $excerpt_return );
	}


	/**
	 * @param $format Default: "F j, Y \a\t h:i a".
	 * @return Date when the comment was sent.
	 */
	public function comment_date( $format = 'F j, Y \a\t h:i a' ) {
		return date( $format, strtotime( $this->comment_internal['comment_date'] ) );
	}


	/**
	 * @param $format Default: "H:i:s".
	 * @return Time when the comment was sent.
	 */
	public function comment_time( $format ) {
		return date( $format, strtotime( $this->comment_internal['comment_date'] ) );
	}


	public function comment_status() {
		return $this->comment_internal['comment_status'];
	}


	/**
	 * WP
	 */
	public function comment_form_title($noreplytext = '', $replytext = '', $linktoparent = true) {
		return 'Leave a Reply';
	}


	// TODO: comment_author_rss
	public function comment_author_rss() {
		
	}


	public function comment_text_rss() {
		return $this->comment_text();
	}


	// TODO: comment_link_rss
	public function comment_link_rss() {
		
	}


	// TODO: comment_comments_rss
	public function comment_comments_rss() {
		
	}


	// TODO: comment_reply_link
	public function comment_reply_link() {
		
	}


	// TODO: cancel_comment_reply_link
	public function cancel_comment_reply_link() {
		
	}


	// TODO: previous_comments_link
	public function previous_comments_link() {
		
	}


	// TODO: next_comments_link
	public function next_comments_link() {
		
	}


	// TODO: paginate_comments_link
	public function paginate_comments_links() {
		
	}


	public function comment_parent() {
		return $this->comment_internal['comment_parent'];
	}


	public function comment_permalink() {
		if( !isset( $this->post_to_comment['is_page'] ) ) {
			$this->preload_post_info();
		}

		if( $this->post_to_comment['is_page'] != 'true' ) {
			return ae_URL::PermalinkOfPost( $this->comment_post_ID() ) . '#comment-' . $this->comment_internal['comment_id'];
		}
		return ae_URL::PermalinkOfPage( $this->comment_post_ID() ) . '#comment-' . $this->comment_internal['comment_id'];
	}


	public function comment_post_ID() {
		return $this->comment_internal['comment_post_id'];
	}


	public function comment_post_has_pwd() {
		if( !isset( $this->post_to_comment['post_pwd'] ) ) {
			$this->preload_post_info();
		}

		return ( $this->post_to_comment['post_pwd'] != '' );
	}


	public function comment_post_author_id() {
		if( !isset( $this->post_to_comment['post_author_id'] ) ) {
			$this->preload_post_info();
		}

		return $this->post_to_comment['post_author_id'];
	}


	public function comment_post_title() {
		if( !isset( $this->post_to_comment['post_title'] ) ) {
			$this->preload_post_info();
		}

		return $this->post_to_comment['post_title'];
	}


	/**
	 * WP
	 */
	public function get_avatar( $id_or_email = '', $size = '96', $default = '' ) {
		$id_or_email = ( $id_or_email == '' ) ? $this->comment_internal['comment_email'] : $id_or_email;

		return ae_User::getAvatar( $id_or_email, $size, $default );
	}


	/**
	 * Checks if a comment was written by a registered user.
	 */
	public function is_comment_author_user() {
		if( $this->comment_internal['comment_user'] > 0 ) {
			return true;
		}
		return false;
	}


	/**
	 * Returns true if the comment is a trackback, false otherwise.
	 */
	public function is_trackback() {
		return ( $this->comment_internal['comment_has_type'] == 'trackback' );
	}



	//---------- Protected functions


	protected function preload_post_info() {
		if( isset( $this->post_to_comment['post_id'] )
				&& $this->post_to_comment['post_id'] == $this->comment_internal['comment_post_id'] ) {
			return;
		}

		$sql = '
			SELECT
				post_id,
				post_pwd,
				post_author_id,
				post_title,
				IF( post_list_page IS NULL, "false", "true" ) AS is_page
			FROM `' . TABLE_POSTS . '`
			WHERE post_id = ' . $this->comment_internal['comment_post_id'];

		$this->post_to_comment = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );
	}


	protected function correctTypography( $text ) {
		$typo = array(
			'opening double quote' => '„',
			'closing double quote' => '“',
			'opening single quote' => '‚',
			'closing single quote' => '‘',
			'apostrophe' => '’',
			'ellipsis' => '…',
			'dash' => '–'
		);

		$typo_replace = array(
			'/([a-z0-9]+=)"([^"]*)"/i',
			'/([a-z]+)\'([a-z]+)/i',
			'/(\s?)"([a-z0-9]+)/i',
			'/(\s?)"(\s*)/i',
			'/(\s?)\'([a-z0-9]+)/i',
			'/((?:\s?)[' . $typo['opening single quote'] . '])([^' . $typo['closing single quote'] . ']+)\'(\s*)/i',
			'/\.\.\./',
			'/([^0-9 ]+) - ([^0-9 ]+)/',
			'/&quot;/'
		);
		$typo_replace_with = array(
			'$1&quot;$2&quot;',
			'$1' . $typo['apostrophe'] . '$2',
			'$1' . $typo['opening double quote'] . '$2',
			'$1' . $typo['closing double quote'] . '$2',
			'$1' . $typo['opening single quote'] . '$2',
			'$1$2' . $typo['closing single quote'] . '$3',
			$typo['ellipsis'],
			'$1 ' . $typo['dash'] . ' $2',
			'"'
		);

		return preg_replace( $typo_replace, $typo_replace_with, $text );
	}


}