<?php


class ae_ManageUserQuery {

	protected $user;
	protected $count_users;
	protected $limit;
	protected $page;
	protected $dumped_users;

	protected $filter_string;


	public function __construct( $filter ) {
		$this->users = array();
		$this->limit = 14;
		$this->page = ( PAGE < 0 ) ? 0 : PAGE;
		$this->filter_string = self::build_filterstring( $filter );

		$this->users = ae_Database::Assoc( '
			SELECT
				user_id,
				user_name_login,
				user_name,
				user_permalink,
				user_role,
				user_pwd,
				user_email,
				user_url,
				user_editor,
				user_status
			FROM `' . TABLE_USERS . '`
			' . $this->filter_string . '
			ORDER BY
				user_name ASC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit
		);

		$this->count_users = $this->count_users();
		$this->dumped_users = 0;
	}


	public function count_users() {
		if( !empty( $this->count_users ) ) {
			return $this->count_users;
		}

		$sql = '
			SELECT
				COUNT( user_id ) AS count
			FROM `' . TABLE_USERS . '`
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function user_count_media() {
		$sql = '
			SELECT
				COUNT( media_id ) AS count
			FROM `' . TABLE_MEDIA . '`
			WHERE media_uploader = ' . $this->user['user_id'];

		$media = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $media['count'];
	}


	public function user_count_pages() {
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			WHERE post_author_id = ' . $this->user['user_id'] . '
			AND post_list_page IS NOT NULL
		';

		$pages = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $pages['count'];
	}


	public function user_count_posts() {
		$sql = '
			SELECT
				COUNT( post_id ) AS count
			FROM `' . TABLE_POSTS . '`
			WHERE post_author_id = ' . $this->user['user_id'] . '
			AND post_list_page IS NULL
		';

		$posts = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $posts['count'];
	}


	public function user_edit_comments() {
		return $this->user['user_edit_comments'];
	}


	public function user_email() {
		return htmlspecialchars( $this->user['user_email'] );
	}


	public function user_email_link( $linktext = '' ) {
		if( $this->user['user_email'] == '' ) {
			return '';
		}
		if( empty( $linktext ) ) {
			$linktext = $this->user['user_email'];
		}

		$out = '<a href="mailto:' . htmlspecialchars( $this->user['user_email'] ) . '">';
		$out .= htmlspecialchars( $linktext ) . '</a>';
		return $out;
	}


	public function user_ID() {
		return $this->user['user_id'];
	}


	/**
	 * @param string $view External or internal name
	 */
	public function user_name( $view = 'external' ) {
		if( $view == 'internal' ) {
			return htmlspecialchars( $this->user['user_name_login'] );
		}
		return htmlspecialchars( $this->user['user_name'] );
	}


	public function user_role() {
		return $this->user['user_role'];
	}


	public function user_status() {
		return $this->user['user_status'];
	}


	public function user_texteditor() {
		return $this->user['user_texteditor'];
	}


	public function user_url() {
		return htmlspecialchars( $this->user['user_url'] );
	}


	public function user_url_link( $linktext = '' ) {
		if( $this->user['user_url'] == '' ) {
			return '';
		}
		if( empty( $linktext ) ) {
			$linktext = $this->user['user_url'];
		}

		$out = '<a href="' . htmlspecialchars( $this->user['user_url'] ) . '">';
		$out .= htmlspecialchars( $linktext ) . '</a>';
		return $out;
	}


	public function have_users() {
		if( count( $this->users ) <= 0 ) {
			unset( $this->users );
			return false;
		}
		return true;
	}


	public function the_user() {
		$this->user = $this->users[$this->dumped_users];
		unset( $this->users[$this->dumped_users] );
		$this->dumped_users++;
	}



	//--------- Static functions


	protected static function build_filterstring( $filter ) {
		$out = '';

		if( !empty( $filter['role'] ) ) {
			$out .= ' AND user_role = "'
				. mysql_real_escape_string( $filter['role'] ) . '" ';
		}
		if( !empty( $filter['name'] ) ) {
			$out .= ' AND user_name = "'
				. mysql_real_escape_string( $filter['name'] ) . '" ';
		}
		if( !empty( $filter['status'] ) ) {
			$out .= ' AND user_status = "'
				. mysql_real_escape_string( $filter['status'] ) . '" ';
			if( $filter['status'] != 'trash' ) {
				$out .= ' AND user_status != "trash" ';
			}
		}
		if( !empty( $filter['url'] ) ) {
			$out .= ' AND user_url = '
				. mysql_real_escape_string( $filter['url'] ) . ' ';
		}
		if( !empty( $filter['email'] ) ) {
			$out .= ' AND user_email = '
				. mysql_real_escape_string( $filter['email'] ) . ' ';
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 5 );
		}
		else {
			$out = ' WHERE user_status != "trash" ';
		}
		$out .= ' AND user_status != "deleted" ';
		return $out;
	}


	public static function count_users_bystatus( $status = '' ) {
		if( empty( $status ) ) {
			$sql = '
				SELECT
					COUNT( user_id ) AS count
				FROM `' . TABLE_USERS . '`
				WHERE user_status != "deleted"
			';
		}
		else {
			$sql ='
				SELECT
					COUNT( user_id ) AS count
				FROM `' . TABLE_USERS . '`
				WHERE user_status = "' . mysql_real_escape_string( $status ) . '"
			';
		}

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $u['count'];
	}


	public static function count_users_byrole( $role = '' ) {
		if( empty( $role ) ) {
			$sql = '
				SELECT
					COUNT( user_id ) AS count
				FROM `' . TABLE_USERS . '`
				WHERE user_status != "deleted"
				AND user_status != "trash"
			';
		}
		else {
			$role = strtolower( $role );

			$sql = '
				SELECT
					COUNT( user_id ) AS count
				FROM `' . TABLE_USERS . '`
				WHERE user_role = "' . mysql_real_escape_string( $role ) . '"
				AND user_status != "deleted"
				AND user_status != "trash"
			';
		}

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $u['count'];
	}



	//--------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


}
