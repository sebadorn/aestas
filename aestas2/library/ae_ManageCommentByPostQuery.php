<?php


class ae_ManageCommentByPostQuery extends ae_CommentQuery {

	protected $posts = array();
	protected $comments = array();
	protected $loaded_posts = array();

	protected $post_id = 0;
	protected $count_comments = 0;
	protected $dumped_posts = 0;
	protected $dumped_comments = 0;
	protected $post_internal;
	protected $comment_internal;

	protected $limit = 14;
	protected $page;
	protected $filter_string;


	// TODO: pages
	public function __construct( $filter ) {
		$this->filter_string = self::BuildFilterString( $filter );
		$this->count_comments = $this->count_comments();
		$this->page = ( PAGE < 0 ) ? 0 : PAGE;

		// comments
		$all_comments_query = ae_Database::Query( '
			SELECT
				comment_id,
				comment_ip,
				comment_user,
				comment_author,
				comment_email,
				comment_url,
				comment_date,
				comment_post_id,
				comment_parent,
				comment_content,
				comment_has_type,
				comment_status
			FROM `' . TABLE_COMMENTS . '`
			' . $this->filter_string . '
			ORDER BY
				comment_date DESC,
				comment_id DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit
		);

		while( $c = mysql_fetch_assoc( $all_comments_query ) ) {
			$this->posts[$c['comment_post_id']][] = $c;
		}

		$this->load_posts();
	}


	public function count_comments() {
		if( !empty( $this->count_comments ) ) {
			return $this->count_comments;
		}

		$sql = '
			SELECT
				COUNT( comment_id ) AS count
			FROM `' . TABLE_COMMENTS . '`
			LEFT JOIN `' . TABLE_POSTS . '`
			ON comment_post_id = post_id
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function have_posts() {
		if( !current( $this->posts ) ) {
			unset( $this->posts );
			return false;
		}
		return true;
	}


	public function have_comments() {
		if( count( $this->post_internal ) <= 0 ) {
			unset( $this->post_internal );
			return false;
		}
		return true;
	}


	public function the_post() {
		$this->post_id = key( $this->posts );
		$this->post_internal = current( $this->posts );
		next( $this->posts );
		$this->dumped_comments = 0;
	}


	public function the_comment() {
		$this->comment_internal = $this->post_internal[$this->dumped_comments];
		unset( $this->post_internal[$this->dumped_comments] );
		$this->dumped_comments++;
	}


	public function post_title() {
		$title = $this->loaded_posts[$this->post_id]['post_title'];
		return htmlspecialchars( $title );
	}


	public function post_date( $format = 'Y-m-d H:i:s' ) {
		$date = $this->loaded_posts[$this->post_id]['post_date'];
		return date( $format, strtotime( $date ) );
	}



	//---------- Protected functions


	protected function load_posts() {
		$post_ids = array_keys( $this->posts );
		$post_ids = implode( ', ', $post_ids );

		if( $post_ids == '' ) {
			return;
		}

		$posts_query = ae_Database::Query( '
			SELECT
				post_id,
				post_title,
				post_permalink,
				post_date
			FROM `' . TABLE_POSTS . '`
			WHERE post_id IN( ' . $post_ids . ' )
		' );

		while( $post = mysql_fetch_assoc( $posts_query ) ) {
			$this->loaded_posts[$post['post_id']] = $post;
		}
	}



	//---------- Static functions


	public static function BuildFilterString( $filter ) {
		$out = '';

		if( !empty( $filter['status'] ) ) {
			$out .= ' AND comment_status = "'
				. mysql_real_escape_string( $filter['status'] ) . '" ';
			if( $filter['status'] != 'trash' ) {
				$out .= ' AND comment_status != "trash" ';
			}
		}
		if( !empty( $filter['to_post'] ) ) {
			$out .= ' AND comment_to_type = "post" AND comment_post_id = '
				. mysql_real_escape_string( $filter['to_post'] ) . ' ';
		}
		if( !empty( $filter['to_page'] ) ) {
			$out .= ' AND comment_to_type = "page" AND comment_post_id = '
				. mysql_real_escape_string( $filter['to_page'] ) . ' ';
		}
		if( !empty( $filter['date'] ) ) {
			$out .= ' AND comment_date LIKE "'
				. mysql_real_escape_string( $filter['date'] ) . ' __:__:__" ';
		}
		if( !empty( $filter['date_from'] ) ) {
			$out .= ' AND comment_date >= "'
				. mysql_real_escape_string( $filter['date_from'] ) . '" ';
		}
		if( !empty( $filter['date_till'] ) ) {
			$out .= ' AND comment_date <= "'
				. mysql_real_escape_string( $filter['date_till'] ) . '" ';
		}
		if( !empty( $filter['author'] ) ) {
			$out .= ' AND comment_author = "'
				. mysql_real_escape_string( $filter['author'] ) . '" ';
		}
		if( !empty( $filter['email'] ) ) {
			$out .= ' AND comment_email = "'
				. mysql_real_escape_string( $filter['email'] ) . '" ';
		}
		if( !empty( $filter['ip'] ) ) {
			$out .= ' AND comment_ip = "'
				. mysql_real_escape_string( $filter['ip'] ) . '" ';
		}
		if( !empty( $filter['url'] ) ) {
			$out .= ' AND comment_url = "'
				. mysql_real_escape_string( $filter['url'] ) . '" ';
		}
		if( !empty( $filter['contains'] ) ) {
			$remove = array( '"', "'", ',', '.', '%' );
			$filter['contains'] = str_replace( $remove, '', $filter['contains'] );
			$filter['contains'] = str_replace( '_', '\_', $filter['contains'] );

			$contains = explode( ' ', $filter['contains'] );
			foreach( $contains as $value ) {
				$out .= ' AND comment_content LIKE "%' . mysql_real_escape_string( $value ) . '%" ';
			}
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 5 );
		}
		else {
			$out = ' WHERE comment_status != "trash" AND comment_status != "spam" ';
		}

		if( ROLE != 'admin' ) {
			$out .= ' AND ( post_pwd = "" OR post_pwd IS NULL ) ';
		}

		return $out;
	}


	public static function count_comments_bystatus( $status = '' ) {
		if( ae_RequestCache::hasKey( 'mcq_count_comments_' . $status ) ) {
			return ae_RequestCache::Load( 'mcq_count_comments_' . $status );
		}

		$filter = '';
		if( ROLE != 'admin' ) {
			$filter = ' AND (
				post_pwd = ""
				OR post_pwd IS NULL
				OR post_author_id = ' . ae_Permissions::getIdOfCurrentUser() . '
			)';
		}

		$sql = '
			SELECT
				COUNT( comment_id ) AS count
			FROM `' . TABLE_COMMENTS . '`
			LEFT JOIN `' . TABLE_POSTS . '`
			ON comment_post_id = post_id
			WHERE comment_status = "' . mysql_real_escape_string( $status ) . '"
			' . $filter;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		ae_RequestCache::Save( 'mcq_count_comments_' . $status, $total['count'] );
		return $total['count'];
	}



	//---------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


}
