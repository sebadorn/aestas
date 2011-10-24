<?php


class ae_ManagePostQuery extends ae_PostQuery {

	protected $limit;
	protected $page;
	protected $filter_string;


	public function __construct( $filter, $type = 'post' ) {
		$this->querytype = $type;

		$this->filter_string = self::BuildFilterString( $filter, $type );
		$this->limit = 14;
		$this->page = PAGE < 0 ? 0 : PAGE;

		$sql = '
			SELECT
				(
					SELECT
						COUNT( comment_id )
					FROM `' . TABLE_COMMENTS . '`
					WHERE comment_post_id = post_id
				) AS comments,
				post_id AS id,
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
				user_name,
				user_permalink
			FROM `' . TABLE_POSTS . '`
			LEFT JOIN `' . TABLE_USERS . '`
			ON post_author_id = user_id
			' . $this->filter_string . '
			ORDER BY
				post_date DESC,
				post_id DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit;

		$this->posts_of_current_page = ae_Database::Assoc( $sql );

		$this->preload_commentnumbers();

		if( $type == 'post' ) {
			$this->preload_categories();
		}
	}


	public function count_posts() {
		if( !empty( $this->count_posts ) ) {
			return $this->count_posts;
		}

		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function have_posts() {
		if( count( $this->posts_of_current_page ) <= 0 ) {
			unset( $this->post_internal );
			unset( $this->id2index );
			unset( $this->categories );
			unset( $this->id2catname );
			return false;
		}
		return true;
	}


	public function the_post() {
		if( $this->post_internal != null ) {
			$this->previousday = $this->the_time( 'Ymd' );
		}

		$this->post_internal = $this->posts_of_current_page[$this->dumped_posts];
		unset( $this->posts_of_current_page[$this->dumped_posts] );

		$this->dumped_posts++;
	}


	public function count_comments() {
		return $this->post_internal['comments'];
	}


	public function post_has_expired() {
		if( !ae_Validate::isTimestamp_MySQL( $this->post_internal['post_expires'] ) ) {
			return false;
		}
		return ( $this->post_internal['post_expires'] < date( 'Y-m-d H:i:s' ) );
	}



	//---------- Static functions


	public static function BuildFilterString( $filter, $type = 'post' ) {
		$out = '';

		if( !empty( $filter['status'] ) ) {
			if( $filter['status'] == 'expired' ) {
				$out .= ' AND ( post_expires IS NOT NULL'
					. ' AND post_expires != "0000-00-00 00:00:00"'
					. ' AND post_expires <= "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '" ) ';
			}
			else {
				$out .= ' AND post_status = "'
					. mysql_real_escape_string( $filter['status'] ) . '" ';
				if( $filter['status'] != 'trash' ) {
					$out .= ' AND post_status != "trash" ';
				}
			}
		}
		if( !empty( $filter['date'] ) ) {
			$out .= ' AND post_date LIKE "'
				. mysql_real_escape_string( $filter['date'] ) . ' __:__:__" ';
		}
		if( !empty( $filter['date_from'] ) ) {
			$out .= ' AND post_date >= "'
				. mysql_real_escape_string( $filter['date_from'] ) . '" ';
		}
		if( !empty( $filter['date_till'] ) ) {
			$out .= ' AND post_date <= "'
				. mysql_real_escape_string( $filter['date_till'] ) . '" ';
		}
		if( !empty( $filter['with_media'] ) ) {
			$out .= ' AND post_id = ANY( SELECT that_id FROM ' . TABLE_RELATIONS . ' WHERE this_id = '
				. $filter['with_media'] . ' AND relation_type = "file to post" ) ';
		}
		if( !empty( $filter['contains'] ) ) {
			$remove = array( '"', "'", ',', '.', '%' );
			$filter['contains'] = str_replace( $remove, '', $filter['contains'] );
			$filter['contains'] = str_replace( '_', '\_', $filter['contains'] );

			$contains = explode( ' ', $filter['contains'] );
			foreach( $contains as $value ) {
				$out .= ' AND post_content LIKE "%' . mysql_real_escape_string( $value ) . '%" ';
			}
			$out_title = '';
			foreach( $contains as $value ) {
				$out_title .= ' AND post_title LIKE "%' . mysql_real_escape_string( $value ) . '%" ';
			}
			$out_title = substr( $out_title, 5 );
			$out .= ' OR ( ' . $out_title . ' ) ';
		}
		if( !empty( $filter['tag'] ) ) {
			$out .= ' AND post_keywords LIKE "%' . mysql_real_escape_string( $filter['tag'] ) . '%" ';
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 5 );
		}
		else {
			$out = ' WHERE post_status != "trash" ';
		}

		if( $type == 'page' ) {
			$out .= ' AND post_list_page IS NOT NULL ';
		}
		else {
			$out .= ' AND post_list_page IS NULL ';
		}

		return $out;
	}


	public static function CountPagesByStatus( $status = '' ) {
		return self::CountPostsByStatus( $status, 'page' );
	}


	public static function CountPostsByStatus( $status = '', $type = 'post' ) {
		if( ae_RequestCache::hasKey( 'mpq_count_' . $type . 's_' . $status ) ) {
			return ae_RequestCache::Load( 'mpq_count_' . $type . 's_' . $status );
		}

		if( $status == 'expired' ) {
			$sql = '
				SELECT
					COUNT( post_id ) AS count
				FROM `' . TABLE_POSTS . '`
				WHERE post_expires IS NOT NULL
				AND post_expires != "0000-00-00 00:00:00"
				AND post_expires <= "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( post_id ) AS count
				FROM `' . TABLE_POSTS . '`
				WHERE post_status = "' . mysql_real_escape_string( $status ) . '"
			';
		}

		$sql .= ' AND post_list_page IS ';
		$sql .= ( $type == 'page' ) ? ' NOT NULL ' : ' NULL ';

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		ae_RequestCache::Save( 'mpq_count_' . $type . 's_' . $status, $total['count'] );
		return $total['count'];
	}



	//---------- Protected functions


	/**
	 * Instead of retrieving the categories for every post,
	 * we load them all for all posts on the current page.
	 */
	protected function preload_categories() {
		$this->categories = array();
		$this->id2catname = array();

		$query = ae_Database::Query( '
			SELECT
				post_id,
				cat_id,
				cat_name,
				cat_permalink
			FROM `' . TABLE_CATEGORIES . '`
			LEFT JOIN `' . TABLE_RELATIONS . '`
			ON that_id = cat_id
			LEFT JOIN `' . TABLE_POSTS . '`
			ON this_id = post_id
			' . $this->filter_string . '
			AND relation_type = "post to cat"
			ORDER BY post_date DESC
		' );

		$foo = array();

		while( $c = mysql_fetch_assoc( $query ) ) {
			if( !isset( $foo[$c['post_id']] ) ) {
				$foo[$c['post_id']] = array();
			}

			if( !isset( $this->id2catname[$c['cat_id']] ) ) {
				$new_category = array(
					'cat_name' => $c['cat_name'],
					'cat_permalink' => $c['cat_permalink']
				);
				$this->id2catname[$c['cat_id']] = $new_category;
			}

			$foo[$c['post_id']][] = $c['cat_id'];
		}

		$this->categories = $foo;
	}


	/**
	 * Instead of retrieving the number for every post,
	 * we load them all for all posts on the current page.
	 */
	protected function preload_commentnumbers() {
		$all_comment_numbers_query = ae_Database::Query( '
			SELECT
				post_id,
				COUNT( comment_post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			LEFT OUTER JOIN `' . TABLE_COMMENTS . '`
			ON post_id = comment_post_id
			' . $this->filter_string . '
			GROUP BY post_id
			ORDER BY
				post_date DESC,
				post_id DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit
		);

		$this->comment_numbers = array();

		while( $comment_count = mysql_fetch_assoc( $all_comment_numbers_query ) ) {
			$this->comment_numbers[$comment_count['post_id']] = $comment_count['count'];
		}
	}



	//---------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


}
