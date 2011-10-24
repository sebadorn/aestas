<?php


class ae_ManageCategoryQuery {

	protected $category;
	protected $categories;
	protected $count_cats;
	protected $limit;
	protected $page;
	protected $dumped_categories;

	protected $filter_string;


	public function __construct( $filter ) {
		$this->categories = array();
		$this->limit = 14;
		$this->page = ( PAGE < 0 ) ? 0 : PAGE;
		$this->filter_string = self::build_filterstring( $filter );

		$sql = '
			SELECT
				cat_id,
				cat_name,
				cat_permalink,
				cat_parent,
				cat_author_id,
				cat_status
			FROM `' . TABLE_CATEGORIES . '`
			' . $this->filter_string . '
			ORDER BY
				cat_name ASC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit;

		$this->categories = ae_Database::Assoc( $sql );

		$this->count_cats = $this->count_categories();
		$this->dumped_categories = 0;
	}


	public function count_categories() {
		if( !empty( $this->count_cats ) ) {
			return $this->count_cats;
		}

		$sql = '
			SELECT
				COUNT( cat_id ) AS count
			FROM `' . TABLE_CATEGORIES . '`
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function cat_author() {
		$sql = '
			SELECT
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_id = ' . $this->category['cat_author_id'];

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $u['user_name'];
	}


	/**
	 * @return Count of categories that have this one as parent
	 */
	public function cat_count_minions() {
		$where = empty( $this->filter_string ) ? ' WHERE ' : $this->filter_string . ' AND ';

		$sql = '
			SELECT
				COUNT( cat_id ) AS count
			FROM `' . TABLE_CATEGORIES . '`
			' . $where . '
			cat_parent = ' . $this->category['cat_id'];

		$minions = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $minions['count'];
	}


	/**
	 * @return Number of posts in this category
	 */
	public function cat_count_posts() {
		if( $this->category['cat_id'] == 1) {
			$sql = '
				SELECT
					COUNT( post_id ) AS count
				FROM `' . TABLE_POSTS . '`
				LEFT JOIN `' . TABLE_RELATIONS . '`
				ON post_id = this_id
				WHERE relation_type IS NULL
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( this_id ) AS count
				FROM `' . TABLE_RELATIONS . '`
				WHERE that_id = ' . $this->category['cat_id'] . '
				AND relation_type = "post to cat"
			';
		}

		$posts = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $posts['count'];
	}


	/**
	 * @return ID of category
	 */
	public function cat_ID() {
		return $this->category['cat_id'];
	}


	/**
	 * @return If there is one, returns the name of the parent category.
	 */
	public function cat_main() {
		if( !empty( $this->category['cat_parent'] ) ) {
			$sql = '
				SELECT
					cat_name
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_id = ' . $this->category['cat_parent'];

			$main = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			return $main['cat_name'];
		}
		else {
			return '';
		}
	}


	/**
	 * @return Name of category
	 */
	public function cat_name() {
		return htmlspecialchars( $this->category['cat_name'] );
	}


	/**
	 * @return Permalink of category
	 */
	public function cat_permalink() {
		return $this->category['cat_permalink'];
	}


	/**
	 * @return Absolute link to filter posts on the blog by this category.
	 */
	public function cat_absolute_link() {
		return 'http://' . str_replace( '/admin', '', ae_URL::Blog() ) . '/' . $this->category['cat_permalink'];
	}


	/**
	 * @return Status of category
	 */
	public function cat_status() {
		return $this->category['cat_status'];
	}


	/**
	 * Boolean if there are categories
	 */
	public function have_cats() {
		if( count( $this->categories ) <= 0 ) {
			unset( $this->categories );
			return false;
		}
		return true;
	}


	public function the_cat() {
		$this->category = $this->categories[$this->dumped_categories];
		unset( $this->categories[$this->dumped_categories] );
		$this->dumped_categories++;
	}



	//---------- Static functions


	protected static function build_filterstring( $filter ) {
		$out = '';

		if( !empty( $filter['name'] ) ) {
			$out .= ' AND cat_name = "'
				. mysql_real_escape_string( $filter['name'] ) . '" ';
		}
		if( !empty( $filter['parent'] ) ) {
			$out .= ' AND cat_parent = '
				. mysql_real_escape_string( $filter['parent'] ) . ' ';
		}
		if( !empty( $filter['status'] ) ) {
			$out .= ' AND cat_status = "'
				. mysql_real_escape_string( $filter['status'] ) . '" ';
			if( $filter['status'] != 'trash' ) {
				$out .= ' AND cat_status != "trash" ';
			}
		}
		if( !empty( $filter['author'] ) ) {
			$out .= ' AND cat_author_id = '
				. mysql_real_escape_string( $filter['author'] ) . ' ';
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 5 );
		}
		else {
			$out = ' WHERE cat_status != "trash" ';
		}
		return $out;
	}


	public static function count_categories_bystatus( $status = '' ) {
		if( !empty( $status ) ) {
			$sql = '
				SELECT
					COUNT( cat_id ) AS count
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_status = "' . mysql_real_escape_string( $status ) . '"
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( cat_id ) AS count
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_status != "trash"
			';
		}

		$c = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $c['count'];
	}



	//---------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


}
