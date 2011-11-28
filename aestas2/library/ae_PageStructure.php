<?php

/**
 * Building the HTML interface of the admin area.
 */
class ae_PageStructure {

	protected static $title_seperator = ' &lsaquo; ';
	protected static $show_content;

	protected static $localenav_links = array(
		'welcome' => array(
			'dashboard' => '',
			'statistics' => '',
			'blogroll' => ''
		),
		'create' => array(
			'add post' => '',
			'add page' => '',
			'add category' => '',
			'add user' => ''
		),
		'manage' => array(
			'comments' => '',
			'posts' => '',
			'categories' => '',
			'pages' => '',
			'users' => ''
		),
		'media' => array(
			'library' => '',
			'upload' => ''
		),
		'theme' => array(
			'choose theme' => '',
			'upload theme' => ''
		),
		'set' => array(
			'general' => '',
			'comments' => '',
			'newsfeed' => '',
			'permalinks' => '',
			'rules' => '',
			'database' => ''
		)
	);
	protected static $mainnav_links = array(
		'welcome' => '',
		'create' => '',
		'manage' => '',
		'media' => '',
		'theme' => '',
		'set' => ''
	);

	protected static $default_content = array(
		'welcome' => 'dashboard',
		'create' => 'addpost',
		'manage' => 'comments',
		'media' => 'library',
		'theme' => 'choosetheme',
		'set' => 'general'
	);

	protected static $js_includepath = 'interface/js/';
	protected static $default_rte_name = 'ckeditor';
	protected static $default_rte_path = 'ckeditor';
	protected static $javascripts = array(
		'default' => array( 'jquery', 'everywhere' ),
		'welcome' => array( 'stats' ),
		'create' => array( 'create', 'jquery.indent-1.0.min', 'user', 'ckeditor/ckeditor' ),
		'manage' => array( 'jquery.indent-1.0.min', 'manage', 'create', 'user', 'ckeditor/ckeditor' ),
		'media' => array( 'media', 'manage', 'create' ),
		'theme' => array( 'jquery.indent-1.0.min', 'theme' ),
		'set' => array( 'manage', 'set' )
	);


	/**
	 * Returns the area as string. Which to display is decided by the GET parameters.
	 * Defaults to the welcome page.
	 */
	public static function DecideCurrentArea() {
		$area = 'welcome';
		if( isset( $_GET['area'] ) && isset( self::$mainnav_links[$_GET['area']] ) ) {
			$area = $_GET['area'];
		}
		return $area;
	}


	/**
	 * Returns doctype, opening html tag and the head tag area.
	 */
	public static function Header( $area ) {
		$title = self::GetTitle( $area );

		return '<!DOCTYPE html>

			<html>
			<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<title>' . $title . '</title>
				<link rel="stylesheet" type="text/css" href="interface/css/screen.css" />
				<script type="text/javascript" src="interface/js/loaded-in-head.js"></script>
			</head>

		';
	}


	/**
	 * Returns the complete nav: main nav, locale/sub nav and go-to-blog and log-out link.
	 */
	public static function Nav( $current_nav_point, $current_nav_point_locale ) {
		self::InitMainNavLinks();
		$mainnav_css_class = self::CssClassesActive( $current_nav_point );

		$ran = rand( 1, 100 );

		self::InitLocalNavLinks();
		$localenav = self::LocalNav( $current_nav_point, $current_nav_point_locale );

		// TODO: Own method for building search.
		// TODO: Implement missing searches.
		// TODO: Build through loop instead of "by hand"
		$search_was = isset( $_GET['search_was'] ) ? urldecode( $_GET['search_was'] ) : '';
		return '
			<nav id="main">
				<form class="search" action="' . $current_nav_point . '/search.php" method="post" accept-charset="utf-8">
					<input type="text" name="search" value="' . $search_was . '" />
					<input type="submit" value="" />
					<input type="hidden" name="area" value="' . $current_nav_point . '" />
					<input type="hidden" name="show" value="' . $current_nav_point_locale . '" />
				</form>

				<ul id="areanav">
					<li class="welcome' . $mainnav_css_class['welcome'] . '">
						<a href="junction.php">Welcome</a>
						' . self::LocalNav( 'welcome', $current_nav_point_locale ) . '
					</li>
					<li class="create' . $mainnav_css_class['create'] . '">
						' . self::$mainnav_links['create'] . '
						' . self::LocalNav( 'create', $current_nav_point_locale ) . '
					</li>
					<li class="manage' . $mainnav_css_class['manage'] . '">
						' . self::$mainnav_links['manage'] . '
						' . self::LocalNav( 'manage', $current_nav_point_locale ) . '
					</li>
					<li class="media' . $mainnav_css_class['media'] . '">
						' . self::$mainnav_links['media'] . '
						' . self::LocalNav( 'media', $current_nav_point_locale ) . '
					</li>
					<li class="theme' . $mainnav_css_class['theme'] . '">
						' . self::$mainnav_links['theme'] . '
						' . self::LocalNav( 'theme', $current_nav_point_locale ) . '
					</li>
					<li class="set' . $mainnav_css_class['set'] . '">
						' . self::$mainnav_links['set'] . '
						' . self::LocalNav( 'set', $current_nav_point_locale ) . '
					</li>
				</ul>

				<ul class="goto-blog">
					<li class="visit"><a href="../">Visit blog</a></li>
					<li class="logout"><a href="logout.php?ran=' . $ran . '">Log out</a></li>
				</ul>
			</nav>
		';
	}


	/**
	 * Returns the filename for the area to include.
	 */
	public static function DecideContentToShow( $area ) {
		$area = isset( self::$default_content[$area] ) ? $area : 'welcome';
		self::$show_content =  isset( $_GET['show'] ) ? $_GET['show'] : self::$default_content[$area];
		return self::$show_content;
	}


	/**
	 * Includes the content for this area.
	 */
	public static function Content( $area, $content ) {
		if( isset( $_GET['edit'] ) ) {
			$content .= '-edit';
		}
		else if( isset( $_GET['reply'] ) ) {
			$content .= '-reply';
		}

		$filepath = '../admin/' . $area . '/' . $content . '.php';
		if( file_exists( $filepath ) ) {
			include( $filepath );
		}
		else {
			echo '<div><strong>File could not be found: <code>' , $filepath , '</code></strong></div>';
		}
	}


	/**
	 * Returns the footer.
	 */
	public static function Footer() {
		global $timer;
		$timer_end = microtime( true );
		$time = round( $timer_end - $timer, 5 );
		return '
			<footer id="footer">
				<div class="stats">
					<strong>' . $time . '</strong> seconds. '
					. 'Memory use at <strong>' . round( memory_get_usage() / 1024 / 1024, 2 ) . '</strong> MB. '
					. 'Memory peak at <strong>' . round( memory_get_peak_usage() / 1024 / 1024, 2 ) . '</strong> MB. <strong>'
					. ae_Database::getQueryCount() . '</strong> queries.
				</div>
			</footer>
		';
	}


	/**
	 * Returns closing body and html tag.
	 */
	public static function End() {
		return '</body>' . PHP_EOL . '</html>';
	}


	/**
	 * Returns the html to include the javascripts for the area.
	 */
	public static function IncludeJavascript( $area ) {
		$scriptarray = array_merge( self::$javascripts['default'], self::$javascripts[$area] );
		$includescripts = '';
		$user = ae_User::getUserById( ae_Permissions::getIdOfCurrentUser() );
		$editor_of_user = $user->getEditor();

		foreach( $scriptarray as $script ) {
			if( strpos( $script, self::$default_rte_path ) !== false && $editor_of_user != self::$default_rte_name ) {
				continue;
			}

			$includescripts .=
				'<script type="text/javascript" src="' . self::$js_includepath . $script . '.js"></script>' . PHP_EOL;

			if( $script == 'jquery.indent-1.0.min' ) {
				$includescripts .= '
					<script type="text/javascript">
						$( function() { $( "textarea[name=\'content\']" ).indent(); } );
					</script>' . PHP_EOL;
			}

			if( strpos( $script, self::$default_rte_path ) !== false ) {
				$includescripts .= '
					<script type="text/javascript">
						$( function() { CKEDITOR.replace( "editor" ); } );
					</script>' . PHP_EOL;
			}
		}

		return $includescripts . PHP_EOL;
	}


	public static function MissingRights() {
		return '<div id="permission">'
			. '<p>I’m sorry, but in your role as <span>' . ROLE . '</span> you don’t have the needed rights.</p>'
			. '</div>';
	}


	public static function NotExisting( $what ) {
		return '<div id="permission"><p>This ' . $what . ' does not exist.</p></div>';
	}


	public static function BuildPageflip( $page, $limit, $total, $links = 9 ) {
		if( $total <= $limit ) {
			return;
		}

		$up_down = ( $links - 1 ) / 2;

		$query_string = $_SERVER['REQUEST_URI'];

		// Remove status codes from query string
		$del_from_query = array( '/&page=[1-9][0-9]*/', '/&?(success[_a-z]*|error[_a-z]*)=[a-z]+/' );
		$query_string = preg_replace( $del_from_query, '', $query_string );
		$query_string = str_replace( '&', '&amp;', $query_string );

		$pages = ceil( $total / $limit );

		$out = '<a class="first" href="' . $query_string . '">«</a>' . PHP_EOL;

		$limit_bottom = $page - $up_down;
		$limit_top = $page + $up_down;

		while( $limit_bottom <= 0 ) {
			$limit_bottom += 1;
			$limit_top += 1;
		}

		while( $limit_top > $pages ) {
			$limit_top -= 1;
			if( $limit_bottom > 1 ) {
				$limit_bottom -= 1;
			}
		}

		for( $i = $limit_bottom; $i <= $limit_top; $i++ ) {
			$act = ( $i == PAGE + 1 ) ? 'class="act" ' : '';
			$out .= '<a ' . $act . 'href="' . $query_string . '&amp;page=' . $i . '">' . $i . '</a>' . PHP_EOL;
		}

		$out .= '<a class="last" href="' . $query_string . '&amp;page=' . $pages . '">»</a>' . PHP_EOL;
		return $out;
	}



	//---------- Protected functions


	/**
	 * Returns an array with each main navigation element as key
	 * and the css classes for it.
	 */
	protected static function CssClassesActive( $current_nav_point ) {
		$nav_css_class = array(
			'welcome' => '',
			'create' => '',
			'manage' => '',
			'media' => '',
			'theme' => '',
			'set' => ''
		);
		$nav_css_class[$current_nav_point] = ' active';
		return $nav_css_class;
	}


	/**
	 * Returns the title of the page for the title tag.
	 */
	protected static function GetTitle( $area ) {
		include_once( '../includes/cl_wp/build_basics.php' );
		$title = ucwords( $area ) . self::$title_seperator . get_bloginfo( 'name' );
		return $title;
	}


	/**
	 * Enables locale/sub navigation links depending on the user role
	 * and the resulting permissions.
	 */
	protected static function InitLocalNavLinks() {
		if( ROLE == 'admin' ) {
			foreach( self::$localenav_links as $mainkey => $array ) {
				foreach( $array as $key => $value ) {
					self::$localenav_links[$mainkey][$key] =
						'<a href="?area=' . $mainkey . '&amp;show=' . str_replace( ' ', '', $key ) . '">'
						. ucwords( $key ) . '</a>';
				}
			}
		}
		else {
			// TODO: Permissions for ALL areas.
			foreach( self::$localenav_links as $mainkey => $array ) {
				foreach( $array as $key => $value ) {
					if( $mainkey == 'manage' || $mainkey == 'media' || $mainkey == 'theme' || $mainkey == 'set' ) {
						self::$localenav_links[$mainkey][$key] =
							'<a href="?area=' . $mainkey . '&amp;show=' . str_replace( ' ', '', $key ) . '">'
							. ucwords( $key ) . '</a>';
					}
					else {
						self::$localenav_links[$mainkey][$key] = '<span>' . ucwords( $key ) . '</span>';
					}
				}
			}

			// TODO: ROLE values as constants in Permission
			self::$localenav_links['welcome']['dashboard'] = '<a href="?area=welcome&amp;show=dashboard">Dashboard</a>';
			if( ROLE == 'author' ) {
				self::$localenav_links['welcome']['blogroll'] = '<a href="?area=welcome&amp;show=blogroll">Blogroll</a>';
				self::$localenav_links['create']['add page'] = '<a href="?area=create&amp;show=addpage">Add Page</a>';
				self::$localenav_links['create']['add category'] = '<a href="?area=create&amp;show=addcategory">Add Category</a>';
			}
			if( ROLE == 'author' || ROLE == 'guest' ) {
				self::$localenav_links['create']['add post'] = '<a href="?area=create&amp;show=addpost">Add Post</a>';
				self::$localenav_links['create']['add poll'] = '<a href="?area=create&amp;show=addpoll">Add Poll</a>';
			}
			if( ROLE == 'author' || ROLE == 'mechanic' ) {
				self::$localenav_links['welcome']['statistics'] = '<a href="?area=welcome&amp;show=statistics">Statistics</a>';
			}
		}
	}


	/**
	 * Enables main navigation links depending on the user role
	 * and the resulting permissions.
	 */
	protected static function InitMainNavLinks() {
		foreach( self::$mainnav_links as $key => $value ) {
			self::$mainnav_links[$key] = '<span>' . ucwords( $key ) . '</span>';
		}

		if( ROLE == 'admin' || ROLE == 'author' || ROLE == 'guest' ) {
			self::$mainnav_links['create'] = '<a href="junction.php?area=create">Create</a>';
			self::$mainnav_links['manage'] = '<a href="junction.php?area=manage">Manage</a>';
			self::$mainnav_links['media'] = '<a href="junction.php?area=media">Media</a>';
		}
		if( ROLE == 'admin' || ROLE == 'mechanic' ) {
			self::$mainnav_links['theme'] = '<a href="junction.php?area=theme">Theme</a>';
			self::$mainnav_links['set'] = '<a href="junction.php?area=set">Set</a>';
		}
	}


	/**
	 * Returns the local/sub nav for the given area.
	 */
	protected static function LocalNav( $nav_point ) {
		$localnav = '<div class="subnav">' . PHP_EOL;
		$localnav .= '<span class="arrow"></span>' . PHP_EOL;
		$localnav .= '<ul>' . PHP_EOL;

		$active_class_isset = false;
		reset( self::$mainnav_links );
		$area = isset( $_GET['area'] ) ? $_GET['area'] : key( self::$mainnav_links );

		foreach( self::$localenav_links[$nav_point] as $name => $value ) {
			$class = '';
			if( $nav_point == $area ) {
				if( !$active_class_isset && !isset( $_GET['show'] ) ) {
					$class = ' class="active"';
					$active_class_isset = true;
				}
				else if( isset( $_GET['show'] ) && str_replace( ' ', '', $name ) == $_GET['show'] ) {
					$class = ' class="active"';
					$active_class_isset = true;
				}
			}

			$localnav .= "\t" . '<li' . $class . '><a href="?area=' . $nav_point . '&amp;show=';
			$localnav .= str_replace( ' ', '', $name ) . '">' . ucwords( $name ) . '</a></li>' . PHP_EOL;
		}

		$localnav .= '</ul></div>' . PHP_EOL;
		return $localnav;
	}



	//---------- Getter/Setter


	public static function getShowContent() {
		return self::$show_content;
	}

	public static function getSeperator() {
		return trim( self::$title_seperator );
	}

	public static function setSeperator( $seperator ) {
		self::$title_seperator = ' ' . $seperator . ' ';
	}


}
