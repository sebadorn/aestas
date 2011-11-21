<?php


class ae_URL {

	protected static $blog;
	protected static $is_page = true;
	protected static $is_post = true;

	protected static $structures = array(
		'archive' => null,
		'author' => null,
		'blog' => null,
		'cat' => null,
		'page' => null,
		'post' => null,
		'tag' => null
	);

	protected static $structuretags = array(
		'archive' => array(),
		'author' => array(
			'id' => '%id%',
			'name' => '%authorname%'
		),
		'blog' => array(
			'number' => '%pagenumber%'
		),
		'cat' => array(
			'id' => '%id%',
			'name' => '%catname%'
		),
		'page' => array(
			'day' => '%day%',
			'id' => '%id%',
			'month' => '%month%',
			'name' => '%pagename%',
			'year' => '%year%'
		),
		'post' => array(
			'day' => '%day%',
			'id' => '%id%',
			'month' => '%month%',
			'name' => '%postname%',
			'year' => '%year%'
		),
		'tag' => array(
			'name' => '%tagname%'
		)
	);

	protected static $structuretags_patterns = array(
		'archive' => array(),
		'author' => array(
			'id' => '[0-9]+',
			'name' => '([a-z0-9-\+]+)'
		),
		'blog' => array(
			'number' => '[0-9]+'
		),
		'cat' => array(
			'id' => '[0-9]+',
			'name' => '([a-z0-9-\+]+)'
		),
		'page' => array(
			'day' => '[0-9]{2}',
			'id' => '[0-9]+',
			'month' => '[0-9]{2}',
			'name' => '([a-z0-9-\+]+)',
			'year' => '[0-9]{4}'
		),
		'post' => array(
			'day' => '[0-9]{2}',
			'id' => '[0-9]+',
			'month' => '[0-9]{2}',
			'name' => '([a-z0-9-\+]+)',
			'year' => '[0-9]{4}'
		),
		'tag' => array(
			'name' => '([^/]+)'
		)
	);

	protected static $absolutePath = null;
	protected static $pageId;
	protected static $singlePostId;
	protected static $blogpage;
	protected static $category;
	protected static $tag;
	protected static $author;


	/**
	 * Domain plus installation path of the content management system.
	 */
	public static function Blog() {
		if( !empty( self::$blog ) ) {
			return self::$blog;
		}

		$url = parse_url( $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] );

		if( !isset( $url['host'] ) ) {
			$url['host'] = '';
		}

		$filename = explode( '/', $_SERVER['SCRIPT_NAME'] );
		$filename = $filename[count( $filename ) - 1];

		$url['path'] = substr( $url['path'], 0, -1 * strlen( $filename ) );
		$url['path'] = preg_replace( '!/$!', '', $url['path'] );

		if( defined( 'CONTEXT' ) ) {
			switch( CONTEXT ) {
				case 'comments-post':
					$url['path'] = preg_replace( '!/comments$!', '', $url['path'] );
					break;
				case 'posts-rss-feed':
					$url['path'] = preg_replace( '!/feed$!', '', $url['path'] );
					break;
				case 'comments-rss-feed':
					$url['path'] = preg_replace( '!/comments/feed$!', '', $url['path'] );
					break;
			}
		}

		self::$blog = $url['host'] . $url['path'];

		return self::$blog;
	}


	/**
	 * Absolute path to the installation path of the content management system.
	 */
	public static function CMSPathAbsolute() {
		if( self::$absolutePath != null ) {
			return self::$absolutePath;
		}
		$script_path = explode( '/', $_SERVER['SCRIPT_FILENAME'] );
		array_pop( $script_path );
		self::$absolutePath = implode( '/', $script_path );

		return self::$absolutePath;
	}


	/**
	 * Installation path of the content management system.
	 */
	public static function CMSPath() {
		return preg_replace( '!^' . self::Domain() . '!', '', self::Blog() );
	}


	/**
	 * Domain name.
	 */
	public static function Domain() {
		return $_SERVER['SERVER_NAME'];
	}


	/**
	 * Complete URL.
	 */
	public static function Complete() {
		return $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}


	public static function CompleteWithoutGet() {
		return $_SERVER['SERVER_NAME'] . str_replace( '?' . $_SERVER['QUERY_STRING'], '',  $_SERVER['REQUEST_URI'] );
	}


	/**
	 * Complete URL except for the blogpage.
	 */
	public static function CompleteWithoutBlogpage() {
		return self::RemoveBlogpageFromURL( self::CompleteWithoutGet() );
	}


	public static function QueryString() {
		if( empty( $_SERVER['QUERY_STRING'] ) ) {
			return '';
		}
		return '?' . $_SERVER['QUERY_STRING'];
	}


	/**
	 * Examples: "http://" or "https://"
	 */
	public static function Protocol() {
		$protocol = explode( '/', $_SERVER['SERVER_PROTOCOL'] );
		return strtolower( $protocol[0] ) . '://';
	}


	/**
	 * Current page ID decided by permalink.
	 */
	public static function PageId() {
		if( !empty( self::$pageId ) ) {
			return self::$pageId;
		}

		$page = 0;

		if( self::BlogPage() > 0 || self::Author() > 0 ||
				self::Category() > 0 || self::SinglePost() > 0 || self::Tag() != '' ) {
			return $page;
		}

		if( isset( $_GET['page_id'] ) && ae_Validate::isDigit( $_GET['page_id'] ) ) {
			$page = $_GET['page_id']; // TODO: Does page exist?
		}
		else if( self::StructureOfPage() != 'default' ) {
			$page = self::Permalink2Page();
			if( $page < 1 ) {
				self::$is_page = false;
				$page = 0;
			}
		}

		self::$pageId = $page;
		return self::$pageId;
	}


	/**
	 * Current post ID decided by permalink.
	 */
	public static function SinglePost() {
		if( !empty( self::$singlePostId ) ) {
			return self::$singlePostId;
		}

		$post = 0;

		if( isset( $_GET['p'] ) && ae_Validate::isDigit( $_GET['p'] ) ) {
			$post = $_GET['p'];
		}
		else if( self::StructureOfPost() != 'default' ) {
			$post = self::Permalink2Post();
		}

		if( $post < 1 ) {
			self::$is_post = false;
		}

		self::$singlePostId = $post;
		return self::$singlePostId;
	}


	/**
	 * Search phrase submitted by using the search box
	 * which sends per GET method.
	 */
	public static function Search() {
		if( !isset( $_GET['s'] ) ) {
			return false;
		}

		$_GET['s'] = trim( $_GET['s'] );
		if( empty( $_GET['s'] ) ) {
			return false;
		}

		return urldecode( $_GET['s'] );
	}


	/**
	 * Current blog page decided by permalink.
	 */
	public static function BlogPage() {
		if( !empty( self::$blogpage ) ) {
			return self::$blogpage;
		}

		$blogpage = null;

		if( isset( $_GET['page'] ) && ae_Validate::isDigit( $_GET['page'] ) ) {
			$blogpage = $_GET['page'];
		}
		else if( self::StructureOfBlogpage() != 'default' ) {
			$blogpage = self::Permalink2Blogpage();
		}

		self::$blogpage = $blogpage;
		return self::$blogpage;
	}


	/**
	 * Current category decided by permalink.
	 */
	public static function Category() {
		if( !empty( self::$category ) ) {
			return self::$category;
		}

		$category = null;

		if( isset( $_GET['category'] ) && ae_Validate::isDigit( $_GET['category'] ) ) {
			$category = $_GET['category'];
		}
		else if( self::StructureOfCategory() != 'default' ) {
			$category = self::Permalink2Category();
		}

		self::$category = $category;
		return self::$category;
	}


	/**
	 * Current tag decided by permalink.
	 */
	public static function Tag() {
		if( !empty( self::$tag ) ) {
			return self::$tag;
		}

		$tag = null;

		if( isset( $_GET['tag'] ) ) {
			$tag = $_GET['tag'];
		}
		else if( self::StructureOfTag() != 'default' ) {
			$tag = self::Permalink2Tag();
		}

		$tag = urldecode( $tag );

		self::$tag = $tag;
		return self::$tag;
	}


	/**
	 * Current author decided by permalink.
	 */
	public static function Author() {
		if( !empty( self::$author ) ) {
			return self::$author;
		}

		$author = null;

		if( isset( $_GET['author'] ) ) {
			$author = $_GET['author'];
			$author = ae_User::FindByNameExternal( $author );
		}
		else if( self::StructureOfAuthor() != 'default' ) {
			$author = self::Permalink2Author();
		}

		self::$author = $author;
		return self::$author;
	}


	public static function DirectoryUps( $stop_at = 10 ) {
		$ups = '';

		$qs = str_replace( array( '?', '!' ), array( '\\?', '\\!' ), $_SERVER['QUERY_STRING'] );
		$qs = !empty( $qs ) ? '\\?' . $qs : '';

		$url = str_replace( URL, '', self::Protocol() . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] );
		$url = preg_replace( '!(https?://)?!', '', $url );
		$url = preg_replace( '!/([a-z0-9]+.php)?' . $qs . '$!i', '', $url );

		$dirs = explode( '/', $url );
		$dirs = array_reverse( $dirs );
		foreach( $dirs as $step ) {
			if( $step == $stop_at ) {
				break;
			}
			if( !empty( $step ) ) {
				$ups .= '../';
			}
		}
		return $ups;
	}


	public static function BlogpageNavAppendix( $blogpage ) {
		$appendix =  '/' . self::Blogpage2Permalink( $blogpage ) . '/' . self::QueryString();
		return str_replace( '//', '', $appendix );
	}


	public static function NicePermalinks( $type ) {
		$permalink_set = ae_Settings::PermalinkStructure( $type );
		if( $permalink_set == 'default' || empty( $permalink_set ) ) {
			return false;
		}
		if( !file_exists( self::CMSPathAbsolute() . '/.htaccess' ) ) {
			return false;
		}
		return true;
	}


	public static function DeleteCache() {
		self::$author = null;
		self::$blog = null;
		self::$blogpage = null;
		self::$category = null;
		self::$pageId = null;
		self::$singlePostId = null;
	}



	//---------- Extracting title/name from permalink


	public static function ExtractPostnameFromPermalink( $permalink ) {
		return self::ExtractTitleFromPermalink( $permalink, 'post' );
	}


	public static function ExtractPagenameFromPermalink( $permalink ) {
		return self::ExtractTitleFromPermalink( $permalink, 'page' );
	}


	public static function ExtractAuthornameFromPermalink( $permalink ) {
		return self::ExtractTitleFromPermalink( $permalink, 'author' );
	}


	public static function ExtractCatnameFromPermalink( $permalink ) {
		return self::ExtractTitleFromPermalink( $permalink, 'cat' );
	}


	public static function ExtractTagnameFromPermalink( $permalink ) {
		return self::ExtractTagnameFromPermalink( $permalink, 'tag' );
	}



	//---------- Structures of permalinks


	public static function StructureOfArchive() {
		return self::StructureOf( 'archive' );
	}


	public static function StructureOfAuthor() {
		return self::StructureOf( 'author' );
	}


	public static function StructureOfBlogpage() {
		return self::StructureOf( 'blog' );
	}


	public static function StructureOfCategory() {
		return self::StructureOf( 'cat' );
	}


	public static function StructureOfPage() {
		return self::StructureOf( 'page' );
	}


	public static function StructureOfPost() {
		return self::StructureOf( 'post' );
	}


	public static function StructureOfTag() {
		return self::StructureOf( 'tag' );
	}



	//---------- Getting existing permalinks


	public static function PermalinkOfAuthor( $id, $permalink = '' ) {
		if( !empty( $permalink ) && ae_URL::NicePermalinks( 'permalink_structure_author' ) ) {
			return URL . '/' . $permalink;
		}
		return URL . '/' . self::PermalinkOf( 'author', $id );
	}


	public static function PermalinkOfCategory( $id, $permalink = '' ) {
		if( !empty( $permalink ) && ae_URL::NicePermalinks( 'permalink_structure_cat' ) ) {
			return URL . '/' . $permalink;
		}
		return URL . '/' . self::PermalinkOf( 'cat', $id );
	}


	public static function PermalinkOfPage( $id, $permalink = '' ) {
		if( !empty( $permalink ) && ae_URL::NicePermalinks( 'permalink_structure_page' ) ) {
			return URL . '/' . $permalink;
		}
		return URL . '/' . self::PermalinkOf( 'page', $id );
	}


	public static function PermalinkOfPost( $id, $permalink = '', $use_nice = true ) {
		if( $use_nice && !empty( $permalink ) && ae_URL::NicePermalinks( 'permalink_structure_post' ) ) {
			return URL . '/' . $permalink;
		}
		return URL . '/' . self::PermalinkOf( 'post', $id, $use_nice );
	}


	public static function ExistsPermalink( $permalink, $type, $not_id = 0 ) {
		if( !ae_Validate::isDigit( $not_id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		if( !ae_Validate::isTableColumnPrefix( $type ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'table column prefix', $type ) );
		}

		$type = mysql_real_escape_string( $type );
		$table = ae_Settings::getTableToColumnPrefix( $type );

		$sql = '
			SELECT COUNT( ' . $type . '_id ) AS count
			FROM `' . mysql_real_escape_string( $table ) . '`
			WHERE ' . $type . '_permalink = "' . mysql_real_escape_string( $permalink ) . '"
			AND ' . $type . '_id != ' . $not_id;

		$result = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $result['count'] > 0 );
	}



	//---------- Translate permalink to what it may represent


	protected static function Permalink2Author( $permalink = '' ) {
		return self::Permalink2Id( 'author', $permalink );
	}


	protected static function Permalink2Blogpage( $permalink = '' ) {
		$permalink = self::StripCmsPathFromPermalink( $permalink );
		$pattern = self::StructureOfBlogpage();

		$regex_pattern = str_replace(
			self::$structuretags['blog']['number'],
			'(' . self::$structuretags_patterns['blog']['number'] . ')',
			$pattern
		);
		preg_match( '!' . $regex_pattern . '!', $permalink, $page_number );

		if( count( $page_number ) >= 1 ) {
			return $page_number[1];
		}
		return 0;
	}


	protected static function Permalink2Category( $permalink = '' ) {
		return self::Permalink2Id( 'cat', $permalink );
	}


	protected static function Permalink2Page( $permalink = '' ) {
		return self::Permalink2Id( 'page', $permalink );
	}


	protected static function Permalink2Post( $permalink = '' ) {
		return self::Permalink2Id( 'post', $permalink );
	}


	/**
	 * Extracts the tag from the URL and alters it for search in the database.
	 */
	protected static function Permalink2Tag( $permalink = '' ) {
		$permalink = self::StripCmsPathFromPermalink( $permalink );
		$pattern = self::StructureOfTag();

		$permalink = self::RemoveBlogpageFromURL( $permalink );

		$regex_pattern = str_replace(
			self::$structuretags['tag']['name'],
			self::$structuretags_patterns['tag']['name'],
			$pattern
		);
		preg_match( '!^' . $regex_pattern . '$!', $permalink, $tag );

		if( count( $tag ) >= 1 ) {
			return $tag[1];
		}
		return '';
	}



	//---------- Translate to permalink


	public static function Author2Permalink( $id, $name ) {
		$pattern = self::StructureOfAuthor();
		if( $pattern != 'default' ) {
			$data = array( $id, self::ProcessString( $name ) );
			return str_replace( self::$structuretags['author'], $data, $pattern );
		}
		return '?author=' . $id;
	}


	public static function Blogpage2Permalink( $number ) {
		if( $number == 1 ) {
			return '';
		}
		$pattern = self::StructureOfBlogpage();
		if( $pattern != 'default' ) {
			return str_replace( '%pagenumber%', $number, $pattern );
		}
		return '?page=' . $number;
	}


	public static function Category2Permalink( $id, $name ) {
		$pattern = self::StructureOfCategory();
		if( $pattern != 'default' ) {
			$data = array( $id, self::ProcessString( $name ) );
			return str_replace( self::$structuretags['cat'], $data, $pattern );
		}
		return '?category=' . $id;
	}


	public static function Page2Permalink( $id, $title, $year, $month, $day ) {
		$pattern = self::StructureOfPage();
		if( $pattern != 'default' ) {
			$data = array( $day, $id, $month, self::ProcessString( $title ), $year );
			return str_replace( self::$structuretags['page'], $data, $pattern );
		}
		return '?page_id=' . $id;
	}


	public static function Page2Permalink_Datestring( $id, $title, $date ) {
		$parts = explode( ' ', $date );
		$parts1 = explode( '-', $parts[0] );
		$parts2 = explode( ':', $parts[1] );
		return self::Page2Permalink( $id, $title, $parts1[0], $parts1[1], $parts1[2] );
	}


	public static function Post2Permalink( $id, $title, $year, $month, $day ) {
		$pattern = self::StructureOfPost();
		if( $pattern != 'default' ) {
			$data = array( $day, $id, $month, self::ProcessString( $title ), $year );
			return str_replace( self::$structuretags['post'], $data, $pattern );
		}
		return '?p=' . $id;
	}


	public static function Post2Permalink_Datestring( $id, $title, $date ) {
		$parts = explode( ' ', $date );
		$parts1 = explode( '-', $parts[0] );
		$parts2 = explode( ':', $parts[1] );
		return self::Post2Permalink( $id, $title, $parts1[0], $parts1[1], $parts1[2] );
	}


	public static function Tag2Permalink( $tag ) {
		if( empty( $tag ) ) {
			throw new Exception( 'Cannot create permalink for an empty tag.' );
		}

		if( self::NicePermalinks( 'permalink_structure_tag' ) ) {
			$pattern = self::StructureOfTag();
			$tag = urlencode( $tag );

			if( $pattern != 'default' ) {
				return str_replace( '%tagname%', $tag, $pattern );
			}
		}

		return '?tag=' . $tag;
	}


	/**
	 * Turns a string into a more URL suitable string.
	 */
	public static function ProcessString( $string ) {
		$umlaute = array( 'ä', 'ö', 'ü', 'ß' );
		$deumlauted = array( 'ae', 'oe', 'ue', 'ss' );

		$string = strtolower( $string );
		$string = str_ireplace( $umlaute, $deumlauted, $string );

		$string = preg_replace( '!\s|\.|_|:|,|;|/|\\\|\*|%|~|\^!', '-', $string );
		$string = preg_replace( '/[^a-z0-9-\+]/', '', $string );
		$string = preg_replace( '/[-]{2,}/', '-', $string );
		$string = preg_replace( '/^-|-$/', '', $string );

		if( $string == '' ) {
			$string = 'tag-1';
		}

		return $string;
	}



	//---------- Protected functions


	/**
	 * Returns the ID for either a post, page, category or author for an URL.
	 * @param $what string Either 'author', 'cat', 'page' or 'post'.
	 * @return integer If found an ID greater than zero, otherwise 0.
	 */
	protected static function Permalink2Id( $what, $permalink = '' ) {
		$permalink = self::StripCmsPathFromPermalink( $permalink );
		$permalink = self::RemoveBlogpageFromURL( $permalink );

		if( empty( $permalink ) || $permalink == 'index.php'
				|| self::$singlePostId > 0 || self::$pageId > 1 ) {
			return 0;
		}

		$filter = '';
		switch( $what ) {
			case 'cat':
				$select = $where = 'cat';
				$table = TABLE_CATEGORIES;
				break;
			case 'author':
				$select = $where = 'user';
				$table = TABLE_USERS;
				break;
			case 'page':
				$select = $where = 'post';
				$table = TABLE_POSTS;
				$filter = ' AND post_list_page IS NOT NULL ';
				break;
			case 'post':
				$select = $where = 'post';
				$table = TABLE_POSTS;
				$filter = ' AND post_list_page IS NULL ';
				break;
			default:
				throw new Exception( 'Trying to get the ID of a permalink of a not existing type.' );
		}

		$sql = '
			SELECT
				' . $select . '_id AS id
			FROM `' . $table . '`
			WHERE ' . $where . '_permalink = "' . mysql_real_escape_string( $permalink ) . '"
			' . $filter . '
		';

		$search = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $search) ? 0 : $search['id'];
	}


	/**
	 * Removes the installation path of the CMS from the URL.
	 */
	protected static function StripCmsPathFromPermalink( $permalink = '' ) {
		if( empty( $permalink ) ) {
			$permalink = str_replace( '?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] );
		}
		$permalink = strtolower( $permalink );
		$permalink = preg_replace( '!^' . self::CMSPath() . '!', '', $permalink );
		$permalink = preg_replace( '!^/!', '', $permalink );
		return $permalink;
	}


	/**
	 * Returns the permalink structure as saved in the DB.
	 * @param $what string Either 'archive', 'author', 'blog', 'cat', 'page' or 'post'.
	 */
	protected static function StructureOf( $what ) {
		if( !empty( self::$structures[$what] ) ) {
			return self::$structures[$what];
		}

		$structure_bysettings = ae_Settings::PermalinkStructure( 'permalink_structure_' . $what );

		if( !empty( $structure_bysettings ) ) {
			self::$structures[$what] = $structure_bysettings;
			return $structure_bysettings;
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "permalink_structure_' . mysql_real_escape_string( $what ) . '"
		';

		$set = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return self::$structures[$what] = $set['value'];
	}


	/**
	 * Returns the permalink of either a page, post, author or category.
	 * @param $what string Either 'author', 'cat', 'page' or 'post'.
	 */
	protected static function PermalinkOf( $what, $id, $use_nice = true ) {
		if( ae_RequestCache::hasKey( $what . '_' . $id ) ) {
			return ae_RequestCache::Load( $what . '_' . $id );
		}

		switch( $what ) {
			case 'page':
				$select = $where = 'post';
				$get_base = 'page_id';
				$table = TABLE_POSTS;
				break;
			case 'cat':
				$select = $where = 'cat';
				$get_base = 'category';
				$table = TABLE_CATEGORIES;
				break;
			case 'author':
				$select = 'user';
				$where = 'user';
				$get_base = 'author';
				$table = TABLE_USERS;
				break;
			case 'page':
			case 'post':
				$select = $where = 'post';
				$get_base = 'p';
				$table = TABLE_POSTS;
				break;
			default:
				throw new Exception( 'Trying to fetch permalink of not existing type.' );
		}

		if( !$use_nice || !ae_URL::NicePermalinks( 'permalink_structure_' . $what ) ) {
			$return = '?' . $get_base . '=' . $id;
		}
		else {
			$sql = '
				SELECT
					' . $select . '_permalink AS permalink
				FROM `' . $table . '`
				WHERE ' . $where . '_id = ' . mysql_real_escape_string( $id );

			$search = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			$return = empty( $search ) ? '?' . $get_base . '=' . $id : $search['permalink'];
		}

		ae_RequestCache::Save( $what . '_' . $id, $return );

		return $return;
	}


	protected static function ExtractTitleFromPermalink( $permalink, $type ) {
		$pattern = self::StructureOf( $type );
		$pattern = str_replace( self::$structuretags[$type], self::$structuretags_patterns[$type], $pattern );
		preg_match( '!' . $pattern . '!', $permalink, $title );

		return $title[1];
	}


	protected static function RemoveBlogpageFromURL( $url ) {
		$structure = self::StructureOfBlogpage();

		$blogpage_regex_pattern = str_replace(
			self::$structuretags['blog']['number'],
			self::$structuretags_patterns['blog']['number'],
			$structure
		);

		$url = preg_replace( '!/(' . $blogpage_regex_pattern . '|\?page=[0-9]+)!', '', $url );
		return preg_replace( '!/$!', '', $url );
	}



	//---------- Getter/Setter


	public static function isPage() {
		return self::$is_page;
	}


	public static function isPost() {
		return self::$is_post;
	}


}
