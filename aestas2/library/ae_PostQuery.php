<?php

class ae_PostQuery {

	protected $posts_of_current_page = array();
	protected $post_internal;
	protected $count_posts;
	protected $dumped_posts = 0;
	protected $limit;
	protected $page;

	protected $querytype;
	protected $id = 0;

	protected $comment_numbers;
	protected $categories = array();
	protected $id2catname = array();
	protected $id2index = array();
	protected $user2post = array();

	protected $previousday;

	// Filter
	protected $filter_cat1;
	protected $filter_cat2;

	// WP compatibility
	public $max_num_pages;


	public function __construct( $type = 'post', $limit = 0 ) {
		$this->page = ( PAGE < 1 ) ? 0 : PAGE - 1;
		$this->querytype = $type;
		$this->id = ( $type == 'page' ) ? PAGE_ID : SINGLE_POST;

		// Load single post
		if( $this->id > 0 ) {
			$this->count_posts = 1;
			$this->limit = 1;
			if( !$this->loadPost( $this->id ) ) {
				return false;
			}
		}
		// Load a specific interval of posts
		else {
			$this->limit = ( $limit > 0 ) ? $limit : ae_Settings::PostLimit();
			if( !$this->loadPosts_forPage() ) {
				return false;
			}

			$this->count_posts = $this->count_posts();
			$this->max_num_pages = $this->count_posts / $this->limit;

			// For use in build_basics.php > posts_countbyfilter().
			ae_RequestCache::Save( 'count_' . $type . 's', $this->count_posts );
		}

		$this->preload_commentnumbers();
		$this->preload_categories();
	}


	/**
	 * WP
	 */
	public function have_posts() {
		if( $this->id > 0 && $this->dumped_posts > 0 ) {
			return false;
		}
		else if( $this->id <= 0 && count( $this->posts_of_current_page ) <= 0 ) {
			unset( $this->post_internal );
			unset( $this->id2index );
			unset( $this->categories );
			unset( $this->id2catname );
			unset( $this->user2post );
			return false;
		}

		return true;
	}


	/**
	 * WP
	 */
	public function the_post() {
		global $post, $authordata;

		if( $this->post_internal != null ) {
			$this->previousday = $this->the_time( 'Ymd' );
		}

		if( $this->id > 0 ) {
			/* WordPress-like $post */
			$post['ID'] = $this->post_internal['id'];
			$post['comment_status'] = $this->post_internal['post_comment_status'];
			$post['ping_status'] = $post['comment_status'];
			$post = (object) $post;
			// TODO: Not complete, probably.
		}

		else {
			$this->post_internal = $this->posts_of_current_page[$this->dumped_posts];
			unset( $this->posts_of_current_page[$this->dumped_posts] );
		}

		$this->dumped_posts++;

		$authordata = ae_User::getAuthorDataObject( $this->post_internal['post_author_id'] );
	}


	/**
	 * WP
	 */
	public function comments_number( $zero = '0', $one = '1', $more = '%' ) {
		$number = $this->comment_numbers[$this->post_internal['id']];

		switch( $number ) {
			case 0:
				$string = $zero;
				break;
			case 1:
				$string = $one;
				break;
			default:
				$string = $more;
		}

		$string = str_replace( '%', $number, $string );

		return $string;
	}


	/**
	 * WP
	 */
	public function comments_open( $post_id = 0 ) {
		if( $this->post_internal['post_comment_status'] == 'open' ) {
			return true;
		}
		return false;
	}


	/**
	 * WP
	 */
	public function get_the_author_meta( $field = '' ) {
		if( !function_exists( 'get_the_author_meta' ) ) {
			include_once( ae_URL::DirectoryUps() . 'includes/cl_wp/post-page-gateway-functions.php' );
		}
		return get_the_author_meta( $field, $this->post_internal['post_author_id'] );
	}


	/**
	 * WP
	 */
	public function get_the_tags( $before = '', $seperator = ', ', $after = '' ) {
		if( $this->post_password_required() ) {
			return $before . $after;
		}

		$tags = explode( ';', substr( $this->post_internal['post_keywords'], 0, -1 ) );

		if( !empty( $tags[0] ) ) {
			sort( $tags, SORT_STRING );
			foreach( $tags as &$tag ) {
				$link = URL . '/' . ae_URL::Tag2Permalink( $tag );
				$tag = '<a href="' . $link . '">' . $tag.'</a>';
			}
		}

		$tags = implode( $seperator, $tags );
		return $before . $tags . $after;
	}


	/**
	 *
	 */
	public function has_php( $id = 0 ) {
		if( $id == 0 ) {
			return ( $this->post_internal['post_type'] == 'php' );
		}

		if( !empty( $this->posts_of_current_page ) ) {
			$post = $this->get_post_for_has( $id );
			return ( $post['post_type'] == 'php' );
		}

		if( $this->post_internal['id'] != $id ) {
			throw new Exception( 'No post for this ID currently loaded.' );
		}
		return ( $this->post_internal['post_type'] == 'php' );
	}


	/**
	 *
	 */
	public function has_pwd( $id = 0 ) {
		if( $id == 0 ) {
			return !empty( $this->post_internal['post_pwd'] );
		}

		if( !empty( $this->posts_of_current_page ) ) {
			$post = $this->get_post_for_has( $id );
			return !empty( $post['post_pwd'] );
		}

		if( $this->post_internal['id'] != $id ) {
			throw new Exception( 'No post for this ID currently loaded.' );
		}
		return !empty( $this->post_internal['post_pwd'] );
	}


	/**
	 * WP
	 */
	public function has_tag( $tag = '' ) {
		$tags = explode( ';', $this->post_internal['post_keywords'] );
		if( is_array( $tag ) ) {
			foreach( $tag as $value ) {
				if( in_array( value, $tags ) ) {
					return true;
				}
			}
		}
		else if( in_array( $tag, $tags ) ) {
			return true;
		}
		return false;
	}


	/**
	 * WP
	 */
	public function in_category( $category ) {
		// Any of the IDs or category names in the array
		if( is_array( $category ) ) {
			foreach( $category as $cat ) {
				if( in_category( $cat ) ) {
					return true;
				}
			}
			return false;
		}

		// The category with this ID
		if( is_int( $category ) ) {
			$sql = '
				SELECT
					COUNT( this_id ) AS hits
				FROM `' . TABLE_RELATIONS . '`
				WHERE relation_type = "post to cat"
				AND this_id = ' . $this->post_internal['id'] . '
				AND that_id = ' . mysql_real_escape_string( $category );

			$getrel = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( $getrel['hits'] > 0 ) {
				return true;
			}
		}

		// The category with this name
		else if( is_string( $category ) ) {
			$sql = '
				SELECT
					cat_id
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_name = "' . mysql_real_escape_string( $category ) . '"
			';
			$getid = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( !empty( $getid ) ) {
				return in_category( (int) $getid['cat_id'] );
			}
		}

		return false;
	}


	/**
	 * WP
	 */
	public function is_new_day() {
		if( $this->the_time( 'Ymd' ) < $this->previousday ) {
			return true;
		}
		return false;
	}


	/**
	 * WP
	 */
	public function post_class( $ownClasses = '' ) {
		$ownClasses = trim( $ownClasses );

		if( $this->post_internal['post_status'] != 'published' ) {
			$ownClasses .= ' ' . $this->post_internal['post_status'];
			$ownClasses = trim( $ownClasses );
		}

		if( $this->post_internal['post_expires'] != '0000-00-00 00:00:00'
				&& !empty( $this->post_internal['post_expires'] )
				&& $this->post_internal['post_expires'] < date( 'Y-m-d H:i:s' ) ) {
			$ownClasses .= ' expired';
			$ownClasses = trim( $ownClasses );
		}

		if( $this->post_internal['post_date'] > date( 'Y-m-d H:i:s' ) ) {
			$ownClasses .= ' future';
			$ownClasses = trim( $ownClasses );
		}

		if( !empty( $ownClasses ) ) {
			$ownClasses = ' ' . $ownClasses;
		}

		return 'class="post' . $ownClasses . '"';
	}


	/**
	 *
	 */
	public function post_expires( $format = 'Y-m-d H:i:s' ) {
		if( $this->post_internal['post_expires'] == '0000-00-00 00:00:00' ) {
			return '';
		}

		$date = ( $format != 'Y-m-d H:i:s' && $this->post_internal['post_expires'] != null )
			? date( $format, strtotime( $this->post_internal['post_expires'] ) )
			: $this->post_internal['post_expires'];

		return $date;
	}


	/**
	 *
	 */
	public function post_feedpreview() {
		return $this->post_internal['post_newsfeed_preview'];
	}


	/**
	 * 
	 */
	public function post_lastedit( $format = 'Y-m-d H:i:s' ) {
		if( $this->post_internal['post_lastedit'] == '0000-00-00 00:00:00' ) {
			return '';
		}

		$date = ( $format != 'Y-m-d H:i:s' && $this->post_internal['post_lastedit'] != null )
			? date( $format, strtotime( $this->post_internal['post_lastedit'] ) )
			: $this->post_internal['post_lastedit'];

		return $date;
	}


	/**
	 * WP
	 * @param string $sep Seperator
	 * @param string $prelabel Label text for previous posts
	 * @param string $nxtlabel Label text for newer posts
	 * @return string Navigation to browse from post page to page
	 */
	public function posts_nav_link( $sep = ' — ', $prelabel = '« Previous Page', $nxtlabel = 'Next Page »' ) {

		$prev_page = $this->page;
		$next_page = $this->page + 2;

		$upper_limit = ceil( ( $this->count_posts + $this->limit ) / $this->limit );

		// Page structure
		$page_struct = ae_URL::StructureOfBlogpage();

		if( preg_match( '/%pagenumber%/', $page_struct->set_value ) ) {
			$link = $this->pnl_nice_permalink();
		}
		else {
			$link = $this->pnl_GET_permalink();
		}


		// Classes to assign the html

		$prevclass = 'class="first"';
		$nxtclass = 'class="last"';


		// Previous and Next

		if( $prev_page < 1 && $next_page <= $upper_limit ) {
			return '<a ' . $nxtclass . 'href="' . $link['next'] . '">' . $nxtlabel . '</a>';
		}

		else if( $prev_page >= 1 && $next_page > $upper_limit ) {
			return '<a ' . $prevclass . 'href="' . $link['prev'] . '">' . $prelabel . '</a>';
		}

		else if( $prev_page >= 1 && $next_page <= $upper_limit ) {
			return '<a ' . $prevclass . 'href="' . $link['prev'] . '">' . $prelabel . '</a>'
				. $sep . '<a ' . $nxtclass . 'href="' . $link['next'] . '">' . $nxtlabel . '</a>';
		}
	}


	public function post_status() {
		return $this->post_internal['post_status'];
	}


	/**
	 * WP
	 */
	public function the_author( $permalink = false ) {
		if( !isset( $this->post_internal['user_name'] ) ) {
			$user = $this->user2post[$this->post_internal['post_author_id']];
			return $permalink ? $user['user_permalink'] : $user['user_name'];
		}
		return $permalink ? $this->post_internal['user_permalink'] : $this->post_internal['user_name'];
	}


	/**
	 * WP
	 */
	public function the_author_posts_url() {
		$author = $this->the_author( true );

		if( !isset( $this->post_internal['user_name'] ) ) {
			$user = $this->user2post[$this->post_internal['post_author_id']];
			$u_permalink = $user['user_permalink'];
		}
		else {
			$u_permalink = $this->post_internal['user_permalink'];
		}

		return ae_URL::PermalinkOfAuthor( $this->post_internal['post_author_id'], $u_permalink );
	}


	/**
	 * WP
	 */
	public function the_author_posts_link() {
		return '<a href="' . $this->the_author_posts_url() . '">' . $this->the_author() . '</a>';
	}


	/**
	 * WP
	 */
	public function the_category( $seperator = ' ', $parent = '' ) {
		if( $this->post_password_required() ) {
			return 'protected';
		}

		$cats = '';

		if( isset( $this->categories[$this->post_internal['id']] ) ) {
			$categories = $this->categories[$this->post_internal['id']];

			$cat_elements = array();
			foreach( $categories as $catid ) {
				$index = $this->id2catname[$catid]['cat_name'];
				$cat_elements[$index] = 
					'<a href="' . ae_URL::PermalinkOfCategory( $catid, $this->id2catname[$catid]['cat_permalink'] ) . '">'
					. $this->id2catname[$catid]['cat_name'] . '</a>' . $seperator;
			}

			ksort( $cat_elements );
			$cats = implode( '', $cat_elements );
		}

		if( strlen( $cats ) > 0 ) {
			return substr( $cats, 0, strlen( $seperator ) * -1 );
		}

		if( ae_RequestCache::hasKey( 'cat_uncategorized' ) ) {
			$cats = ae_RequestCache::Load( 'cat_uncategorized' );
		}
		else {
			$sql = '
				SELECT
					cat_name,
					cat_permalink
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_id = 1
			';
			$cat = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			$cats = '<a href="' . ae_URL::PermalinkOfCategory( 1, $cat['cat_permalink'] ) . '">'
					. $cat['cat_name'] . '</a>';
			ae_RequestCache::Save( 'cat_uncategorized', $cats );
		}

		return $cats;
	}


	/**
	 * WP
	 */
	public function the_content( $more_link_text = '(more …)', $strip_teaser = false, $more_file = '', $more_class = '' ) {
		// TODO: the_content: $more_file

		if( $this->post_password_required() ) {
			return '
				<div class="protected">
					<p>To read this post you need a password.</p>
					<form accept-charset="utf-8" action="' . $this->the_permalink() . '" method="post">
						<fieldset>
							<label for="postpwd">Password:</label>
							<input id="postpwd" name="postpwd" type="password" />
							<input type="submit" value="send" />
						</fieldset>
					</form>
				</div>
			';
		}

		if( $this->id > 0 ) {
			return $this->post_internal['post_content'];
		}

		$more_permalink = $this->more_permalink( $more_link_text, $more_class );

		// aestas2

		if( ( FEED == false && $this->post_internal['post_content_preview'] == 'true' )
				|| ( FEED == true && $this->post_internal['post_newsfeed_preview'] == 'true' ) ) {
			return $this->post_internal['post_excerpt'] . $more_permalink;
		}

		// WordPress

		$content = explode( '<!--more-->', $this->post_internal['post_content'] );

		// No <!--more--> tag used
		if( empty( $content[1] ) ) {
			return $content[0];
		}

		// No teaser desired
		if( $strip_teaser ) {
			return $more_permalink;
		}

		// Teaser with more link text
		$content[0] .= $more_permalink;

		return $content[0];
	}


	/**
	 * aestas2
	 * @return string
	 */
	public function the_content_full() {
		return $this->post_internal['post_content'];
	}


	/**
	 * WP
	 * @return int ID of post
	 */
	public function the_ID() {
		return $this->post_internal['id'];
	}


	/**
	 * WP
	 * @return string Permalink to post
	 */
	public function the_permalink() {
		$id = $this->post_internal['id'];
		$permalink = $this->post_internal['post_permalink'];

		if( $this->post_password_required() ) {
			if( $this->querytype == 'page' ) {
				return ae_URL::PermalinkOfPage( $id, '', false );
			}
			return ae_URL::PermalinkOfPost( $id, '', false );
		}

		if( $this->querytype == 'page' ) {
			return ae_URL::PermalinkOfPage( $id, $permalink );
		}
		return ae_URL::PermalinkOfPost( $id, $permalink );
	}


	/**
	 * WP
	 * @param string $format Format of date/time
	 * @return string formatted date of when the post was published
	 */
	public function the_time( $format = 'F j, Y' ) {
		$date = date( $format, strtotime( $this->post_internal['post_date'] ) );
		return $date;
	}


	/**
	 * WP
	 */
	public function the_title( $before = '', $after = '', $display = true ) {
		if( $this->post_password_required() ) {
			$title = 'Protected post';
		}
		else {
			$title = $before . htmlspecialchars( $this->post_internal['post_title'] ) . $after;
		}
		if( !$display ) {
			return $title;
		}
		echo $title;
	}


	/**
	 * Returns false if person is allowed to have access or there is no password to begin with.
	 * True otherwise.
	 */
	public function post_password_required() {
		if( !$this->has_pwd() ) {
			return false;
		}

		// Admins have access
		if( ae_Permissions::getRoleOfCurrentUser() == 'admin' ) {
			return false;
		}
		// The author of the post has access
		if( ae_Permissions::getIdOfCurrentUser() == $this->post_internal['post_author_id'] ) {
			return false;
		}

		// Validate given password
		return !ae_Cookies::ValidatePostOrPagePwd(
			$this->post_internal['id'],
			$this->post_internal['post_pwd']
		);
	}



	//---------- Protected functions


	/**
	 * Loads a single post from the DB.
	 * @see __construct
	 */
	protected function loadPost( $id ) {
		if( empty( $id ) ) {
			throw new Exception( 'No ID given to load a post for.' );
		}

		$sql = '
			SELECT
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
				post_robots,
				post_parent,
				post_list_page,
				user_name,
				user_permalink
			FROM `' . TABLE_POSTS . '`
			LEFT JOIN `' . TABLE_USERS . '`
			ON post_author_id = user_id
			' . $this->build_sql( 'post_id = ' . mysql_real_escape_string( $id ) );

		$this->post_internal = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		if( $this->post_internal == null ) {
			define( 'CODE_POSTQUERY', 404 );
			return false;
		}

		return true;
	}


	/**
	 * Loads posts from the DB and stores them in an array.
	 * @see __construct
	 */
	protected function loadPosts_forPage() {
		$user_ids = array();
		$i = 0;

		$sql = '
			SELECT
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
				post_status
			FROM `' . TABLE_POSTS . '`
			' . $this->build_sql() . '
			GROUP BY post_id
			ORDER BY
				post_date DESC,
				post_id DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit;

		$all_posts_query = ae_Database::Query( $sql );

		while( $post = mysql_fetch_assoc( $all_posts_query ) ) {
			$this->posts_of_current_page[] = $post;
			$this->id2index[$post['id']] = $i++;
			$user_ids[] = $post['post_author_id'];
		}

		if( count( $this->posts_of_current_page ) < 1 ) {
			return true;
		}

		// Authors
		$this->preload_authors( $user_ids );

		if( $i == 0 ) {
			define( 'CODE_POSTQUERY', 404 );
			return false;
		}

		return true;
	}


	/**
	 * Counts all posts that will be available through the PostQuery.
	 */
	protected function count_posts() {
		$sql = '
			SELECT
				COUNT( post_id ) AS count
				FROM `' . TABLE_POSTS . '`
				' . $this->build_sql();

		$posts = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );
		return $posts['count'];
	}


	/**
	 * Loads author information to the posts that will be displayed.
	 * @see loadPosts_forPage()
	 */
	protected function preload_authors( $user_ids ) {
		$user_ids = array_unique( $user_ids );

		$sql = '
			SELECT
				user_id,
				user_name,
				user_permalink
			FROM `' . TABLE_USERS . '`
			WHERE user_id IN( ' . implode( ', ', $user_ids ) . ' )
			AND user_status != "trash"
			AND user_status != "deleted"
		';

		$query = ae_Database::Query( $sql );

		while( $u = mysql_fetch_assoc( $query ) ) {
			$this->user2post[$u['user_id']] = $u;
		}
	}


	/**
	 * Builds the WHERE clause part considering filter and user login status.
	 * @param string $start SQL part that has to be at the beginning of the WHERE clause.
	 */
	protected function build_sql( $start = '' ) {
		if( ae_RequestCache::hasKey( 'PostQuery_build_sql' ) ) {
			return ae_RequestCache::Load( 'PostQuery_build_sql' );
		}

		$where = '';

		$filter = $this->build_filter();

		// filter_cat1: LEFT JOIN TABLE_RELATIONS
		if( !empty( $this->filter_cat1 ) ) {
			$where .= $this->filter_cat1;
		}

		$where .= ' WHERE ';
		$withoutAND = true;

		if( !empty( $start ) ) {
			$where .= mysql_real_escape_string( $start ).' ';
			$withoutAND = false;
		}

		// filter
		if( !empty( $filter ) ) {
			$where .= $withoutAND ? $filter : ' AND ' . $filter;
			$withoutAND = false;
		}

		// filter_cat2
		if( !empty( $this->filter_cat2 ) ) {
			$where .= $withoutAND ? $this->filter_cat2 : ' AND ' . $this->filter_cat2;
			$withoutAND = false;
		}

		// loggedin_status
		if( self::getConstraintStatus() != '' ) {
			$where .= $withoutAND ? self::getConstraintStatus() : ' AND ' . self::getConstraintStatus();
			$withoutAND = false;
		}

		// loggedin_date
		if( self::getConstraintDate() != '' ) {
			$where .= $withoutAND ? self::getConstraintDate() : ' AND ' . self::getConstraintDate();
			$withoutAND = false;
		}

		$pages_filter = 'post_list_page IS NULL ';
		if( $this->querytype == 'page' ) {
			$pages_filter = 'post_list_page IS NOT NULL ';
		}

		$where .= $withoutAND ? $pages_filter : ' AND ' . $pages_filter;

		ae_RequestCache::Save( 'PostQuery_build_sql', $where );
		return $where;
	}


	/**
	 * @see build_sql
	 */
	protected function build_filter() {
		$filter = '';
		if( TAG != '' ) {
			$filter .= ' post_keywords LIKE "%' . mysql_real_escape_string( TAG ) . ';%" ';
		}
		if( AUTHOR > 0 ) {
			$filter .= ' post_author_id = ' . mysql_real_escape_string( AUTHOR ) . ' ';
		}
		if( CATEGORY != 0 ) {
			if( CATEGORY > 1 ) {
				$subcats_array = get_subcategories( CATEGORY );
				$subcats = '';
				foreach( $subcats_array as $c ) {
					$subcats .= ' OR that_id = ' . $c['cat_id'] . ' ';
				}
				$this->filter_cat1 = ' LEFT JOIN `' . TABLE_RELATIONS . '` ';
				$this->filter_cat1 .= ' ON post_id = this_id ';
				$this->filter_cat2 = ' relation_type = "post to cat" ';
				$this->filter_cat2 .= ' AND ( that_id = ' . mysql_real_escape_string( CATEGORY ) . $subcats . ' ) ';
			}
			else {
				$this->filter_cat1 = ' LEFT OUTER JOIN `' . TABLE_RELATIONS . '` ';
				$this->filter_cat1 .= ' ON post_id = this_id ';
				$this->filter_cat2 = ' ( relation_type = "post to cat" OR relation_type IS NULL ) ';
				$this->filter_cat2 .= ' AND that_id IS NULL ';
			}
		}
		if( SEARCH !== false ) {
			$filter .= ' ( post_title LIKE "%' . mysql_real_escape_string( SEARCH ) . '%" ';
			$filter .= ' OR post_description LIKE "%' . mysql_real_escape_string( SEARCH ) . '%" )';
		}

		return $filter;
	}


	/**
	 * Returns a post out of the loaded posts for the current page on the blog for a given ID.
	 * @see has_php
	 * @see has_pwd
	 */
	protected function get_post_for_has( $id ) {
		if( !empty( $this->posts_of_current_page ) ) {
			if( !isset( $this->id2index[$id] ) ) {
				throw new Exception( 'No post for this ID currently loaded.' );
			}
			return $this->posts_of_current_page[$this->id2index[$id]];
		}

		throw new Exception( 'No list of posts loaded to retrieve a post from.' );
	}


	/**
	 * Creates a permalink leading to the full version of a post.
	 */
	protected function more_permalink( $more_link_text, $more_class ) {
		$more_link_text = ( is_string( $more_link_text ) ) ? $more_link_text : '(more …)';
		$more_class_text = empty( $more_class ) ? '' : ' class="'.$more_class.'"';
		$out = '<span' . $more_class_text . '>' . PHP_EOL;
		$out .= '<a href="' . $this->the_permalink() . '">' . $more_link_text . '</a>' . PHP_EOL;
		$out .= '</span>' . PHP_EOL;

		return $out;
	}


	/**
	 * @see posts_nav_link
	 */
	protected function pnl_nice_permalink() {
		$structure_regex = get_structure( 'blogpage' );
		$structure_regex = str_replace( '%pagenumber%', '[1-9][0-9]*', $structure_regex );

		$url = preg_replace( '!/' . $structure_regex . '$!', '', URL_EXTENDED );

		$link_prev = $url . '/' . str_replace( '%pagenumber%', $prev_page, $page_struct->set_value ) . '/';
		$link_next = $url . '/' . str_replace( '%pagenumber%', $next_page, $page_struct->set_value ) . '/';

		return array( 'prev' => $link_prev, 'next' => $link_next );
	}


	/**
	 * @see posts_nav_link
	 */
	protected function pnl_GET_permalink() {
		$query_string = '?';

		if( $_SERVER['QUERY_STRING'] != '' ) {
			$query_string .= preg_replace( '/(&|\?)?blogpage=[1-9][0-9]*/', '', $_SERVER['QUERY_STRING'] );

			if( $query_string != '?' ) {
				$query_string .= '&';
			}
		}

		$link_prev = URL_EXTENDED . '/' . $query_string . 'blogpage=' . $prev_page;
		$link_next = URL_EXTENDED . '/' . $query_string . 'blogpage=' . $next_page;

		return array( 'prev' => $link_prev, 'next' => $link_next );
	}


	/**
	 * Returns true if the posts exists, false otherwise.
	 */
	protected function post_exists() {
		$sql = '
			SELECT
				COUNT( post_id ) AS matches
			FROM `' . TABLE_POSTS . '`
			' . $this->build_sql( false, 'post_id = ' . $this->id );

		$post_exists = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $post_exists['matches'] > 0 );
	}


	/**
	 * Instead of retrieving the categories for every post,
	 * we load them all for all posts on the current page.
	 */
	protected function preload_categories() {
		$this->categories = array();
		$this->id2catname = array();

		if( $this->id > 0 ) {
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
				WHERE relation_type = "post to cat"
				AND post_id = ' . $this->id . '
			' );
		}
		else {
			$in_id = implode( ', ', array_keys( $this->id2index ) );
			if( $in_id == '' ) {
				$in_id = ' 0 ';
			}

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
				WHERE relation_type = "post to cat"
				AND post_id IN( ' . $in_id . ' )
				ORDER BY
					post_date DESC,
					post_id DESC
			' );
		}

		$foo = array();

		while( $c = mysql_fetch_assoc( $query ) ) {
			if( !isset( $foo[$c['post_id']] ) ) {
				$foo[$c['post_id']] = array();
			}

			if( !isset( $this->id2catname[$c['cat_id']] ) ) {
				$new_category = array(
					'cat_name' => htmlspecialchars( $c['cat_name'] ),
					'cat_permalink' => $c['cat_permalink']
				);
				$this->id2catname[$c['cat_id']] = $new_category;

				// @see ae_URL::PermalinkOf()
				ae_RequestCache::Save( 'cat_' . $c['cat_id'], $new_category['cat_permalink'] );
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
		$post_filter = $this->build_sql();

		if( $this->id > 0 ) {
			$all_comment_numbers_query = ae_Database::Query( '
				SELECT
					"' . $this->post_internal['id'] . '" AS post_id,
					COUNT( comment_post_id ) AS count
				FROM `' . TABLE_COMMENTS . '`
				WHERE comment_post_id = ' . $this->post_internal['id'] . '
				AND comment_status = "approved"
			' );
		}
		else {
			$in_id = implode( ', ', array_keys( $this->id2index ) );
			if( $in_id == '' ) {
				$in_id = ' 0 ';
			}

			$all_comment_numbers_query = ae_Database::Query( '
				SELECT
					comment_post_id AS post_id,
					COUNT( comment_post_id ) AS count
				FROM `' . TABLE_COMMENTS . '`
				WHERE comment_post_id IN( ' . $in_id . ' )
				AND comment_status = "approved"
				GROUP BY post_id
			' );
		}

		$this->comment_numbers = array();
		foreach( $this->id2index as $post_id => $value ) {
			$this->comment_numbers[$post_id] = 0;
		}

		while( $comment_count = mysql_fetch_assoc( $all_comment_numbers_query ) ) {
			$this->comment_numbers[$comment_count['post_id']] = $comment_count['count'];
		}
	}



	//---------- feed specific


	/**
	 * aestas2
	 */
	public function feed_entry_categories() {
		$cats = array();
		$query = ae_Database::Query( '
			SELECT
				cat_name
			FROM `' . TABLE_RELATIONS . '`
			LEFT JOIN `' . TABLE_CATEGORIES . '`
			ON that_id = cat_id
			WHERE this_id = ' . $this->post_internal['id'] . '
			AND relation_type = "post to cat"
			ORDER BY cat_name
		' );

		while( $c = mysql_fetch_assoc( $query ) ) {
			$cats[] = $c['cat_name'];
		}

		// If in no category use the default one for uncategorized posts
		if( count( $cats ) == 0 ) {
			$sql = '
				SELECT
					cat_name
				FROM `' . TABLE_CATEGORIES . '`
				WHERE cat_id = 1
			';
			$c = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			$cats[] = $c['cat_name'];
		}

		return $cats;
	}


	/**
	 * Getting the description for a feed entry of a post.
	 * @return string Description for this feed entry.
	 */
	public function feed_entry_description() {
		$pwd = $this->has_pwd();
		if( !$pwd && !$this->has_php() ) {
			return $this->feed_entry_content( $this->post_feedpreview() );
		}
		else if( $pwd ) {
			return '<p><em>This post is protected by a password.</em></p>';
		}
		return '<p><em>This post contains PHP and therefore will not be executed in the newsfeed.</em></p>';
	}


	/**
	 * aestas2
	 */
	protected function feed_entry_content( $preview ) {
		$string = '';
		$feedcontent = explode( ';', ae_Settings::getSetting( 'newsfeed_content' ) );

		switch( $feedcontent[0] ) {
			case 'default':
				$string = ( $preview == 'true' ) ? $this->the_content( '' ) : $this->the_content_full();
				break;
			case 'excerpt':
				$string = $this->the_content( '' );
				break;
			case 'full':
				$string = $this->the_content_full();
				break;
			case 'short':
				$string = $this->the_content_full();
				$string = strip_tags( $string );
				if( strlen( $string ) > $feedcontent[1] ) {
					$string = substr( $string, 0, $feedcontent[1] ).' […]';
				}
				break;
			default:
				$string = $this->the_content( '' );
		}

		return $string;
	}



	//---------- Static functions


	public static function getConstraintDate() {
		if( ae_Permissions::isLoggedIn() ) {
			return '';
		}

		$date = mysql_real_escape_string( date( 'Y-m-d H:i:s' ) );
		return '
				post_date <= "' . $date . '"
				AND (
					post_expires > "' . $date . '"
					OR post_expires = "0000-00-00 00:00:00"
					OR post_expires IS NULL
				) ';
	}


	public static function getConstraintStatus() {
		if( ae_Permissions::isLoggedIn() ) {
			return ' post_status != "trash" ';
		}
		return ' post_status = "published" ';
	}


}
