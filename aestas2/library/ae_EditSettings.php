<?php


class ae_EditSettings {

	protected $settings = array(
		// 'blog_theme' => null,
		// 'blog_theme_system' => null,
		// 'media_imagepreview' => null,
		// 'version' => null,
		'auth_system' => null,
		'bloginfo_tagline' => null,
		'bloginfo_title' => null,
		'blog_comment_limit' => null,
		'blog_front' => null,
		'blog_post_limit' => null,
		'comments' => null,
		'comments_moderate' => null,
		'gravatar' => null,
		'gravatar_default' => null,
		'gravatar_rating' => null,
		'ignore_agents' => null,
		'newsfeed_alternate' => null,
		'newsfeed_content' => null,
		'newsfeed_limit' => null,
		'permalink_structure_archive' => null,
		'permalink_structure_author' => null,
		'permalink_structure_blog' => null,
		'permalink_structure_cat' => null,
		'permalink_structure_page' => null,
		'permalink_structure_post' => null,
		'permalink_structure_tag' => null,
		'pings' => null,
		'timezone' => null,
		'ui_manage_cats_limit' => null,
		'ui_manage_comments_limit' => null,
		'ui_manage_pages_limit' => null,
		'ui_manage_posts_limit' => null,
		'ui_manage_users_limit' => null,
		'ui_media_files_limit' => null
	);

	protected $renew_permalinks = array(
		'author' => false,
		'category' => false,
		'page' => false,
		'post' => false
	);


	public function __construct() {
		ae_Settings::PreloadSettings();
	}


	public function update_to_database() {
		foreach( $this->settings as $setting => $value ) {
			if( $value === null ) {
				continue;
			}

			ae_Database::Query( '
				UPDATE `' . TABLE_SETTINGS . '`
				SET
					set_value = "' . mysql_real_escape_string( $value ) . '"
				WHERE set_name = "' . mysql_real_escape_string( $setting ) . '"
			' );
		}
	}


	public function update_permalinks() {
		// Oh man, this much queries can't be healthy.
		$this->update_permalinks_post();
		$this->update_permalinks_page();
		$this->update_permalinks_author();
		$this->update_permalinks_category();
	}


	public function update_permalinks_author() {
		ae_Settings::UseCache( false );

		if( $this->renew_permalinks['author']
				&& $this->settings['permalink_structure_author'] != null
				&& $this->settings['permalink_structure_author'] != 'default' ) {
			$authors = ae_Database::Query( '
				SELECT
					user_id,
					user_name
				FROM `' . TABLE_USERS . '`
				WHERE user_status != "deleted"
				ORDER BY user_id ASC
			' );

			while( $a = mysql_fetch_assoc( $authors ) ) {
				$permalink = ae_URL::Author2Permalink( $a['user_id'], $a['user_name'] );

				ae_Database::Query( '
					UPDATE `' . TABLE_USERS . '`
					SET
						user_name_permalink = "' . mysql_real_escape_string( $permalink ) . '"
					WHERE user_id = ' . $a['user_id']
				);
			}
		}
	}


	public function update_permalinks_category() {
		ae_Settings::UseCache( false );

		if( $this->renew_permalinks['category']
				&& $this->settings['permalink_structure_cat'] != null
				&& $this->settings['permalink_structure_cat'] != 'default' ) {
			$categories = ae_Database::Query( '
				SELECT
					cat_id,
					cat_name
				FROM `' . TABLE_CATEGORIES . '`
				ORDER BY cat_name DESC
			' );

			while( $c = mysql_fetch_assoc( $categories ) ) {
				$permalink = ae_URL::Category2Permalink( $c['cat_id'], $c['cat_name'] );

				ae_Database::Query( '
					UPDATE `' . TABLE_CATEGORIES . '`
					SET
						cat_permalink = "' . mysql_real_escape_string( $permalink ) . '"
					WHERE cat_id = ' . $c['cat_id']
				);
			}
		}
	}


	public function update_permalinks_page() {
		ae_Settings::UseCache( false );

		if( $this->renew_permalinks['page']
				&& $this->settings['permalink_structure_page'] != null
				&& $this->settings['permalink_structure_page'] != 'default' ) {
			$posts = ae_Database::Query( '
				SELECT
					post_id,
					post_date,
					post_title
				FROM `' . TABLE_POSTS . '`
				WHERE post_list_page IS NOT NULL
				ORDER BY post_date DESC
			' );

			while( $p = mysql_fetch_assoc( $pages ) ) {
				$permalink = ae_URL::Page2Permalink_Datestring( $p['post_id'], $p['post_title'], $p['post_date'] );

				ae_Database::Query( '
					UPDATE `' . TABLE_POSTS . '`
					SET
						post_permalink = "' . mysql_real_escape_string( $permalink ) . '"
					WHERE post_id = ' . $p['post_id']
				);
			}
		}
	}


	public function update_permalinks_post() {
		ae_Settings::UseCache( false );

		if( $this->renew_permalinks['post']
				&& $this->settings['permalink_structure_post'] != null
				&& $this->settings['permalink_structure_post'] != 'default' ) {
			$posts = ae_Database::Query( '
				SELECT
					post_id,
					post_date,
					post_title
				FROM `' . TABLE_POSTS . '`
				WHERE post_list_page IS NULL
				ORDER BY post_date DESC
			' );

			while( $p = mysql_fetch_assoc( $posts ) ) {
				$permalink = ae_URL::Post2Permalink_Datestring( $p['post_id'], $p['post_title'], $p['post_date'] );

				ae_Database::Query( '
					UPDATE `' . TABLE_POSTS . '`
					SET
						post_permalink = "' . mysql_real_escape_string( $permalink ) . '"
					WHERE post_id = ' . $p['post_id']
				);
			}
		}
	}



	//---------- Protected functions


	protected static function CorrectPermalink( $permalink ) {
		if( $permalink == 'default' ) {
			return $permalink;
		}

		$permalink = trim( $permalink );
		$permalink = str_replace( '\\', '/', $permalink );		// Turn backslashes around
		$permalink = preg_replace( '!^/|/$!', '', $permalink );	// Remove leading and ending slash
		$permalink = strtolower( $permalink );
		$permalink = preg_replace( '![^a-z0-9-+_/%~]+!', '', $permalink );

		return $permalink;
	}



	//---------- Getter/Setter


	public function setAuth( $value ) {
		if( !ae_Validate::isAuthSystem( $value ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'authentification system', $value ) );
		}
		if( ae_Settings::getSetting( 'auth_system') != $value ) {
			$this->settings['auth_system'] = $value;
			ae_Permissions::Logout();
		}
	}


	public function setBloginfoTagline( $value ) {
		$value = htmlspecialchars( $value );
		if( ae_Settings::getSetting( 'bloginfo_tagline' ) != $value ) {
			$this->settings['bloginfo_tagline'] = $value;
		}
	}


	public function setBloginfoTitle( $value ) {
		$value = htmlspecialchars( $value );
		if( ae_Settings::getSetting( 'bloginfo_title' ) != $value ) {
			$this->settings['bloginfo_title'] = $value;
		}
	}


	public function setBlogCommentLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'blog_comment_limit' ) != $value ) {
			$this->settings['blog_comment_limit'] = $value;
		}
	}


	public function setBlogFront( $value ) {
		if( ae_Settings::getSetting( 'blog_front' ) != $value ) {
			$this->settings['blog_front'] = $value;
		}
	}


	public function setBlogPostLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'number', $value ) );
		}
		if( ae_Settings::getSetting( 'blog_post_limit' ) != $value ) {
			$this->settings['blog_post_limit'] = $value;
		}
	}


	public function setComments( $value ) {
		if( !ae_Validate::isBoolean( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'true or false', $value ) );
		}
		if( ae_Settings::getSetting( 'comments' ) != $value ) {
			$this->settings['comments'] = $value;
		}
	}


	public function setCommentsModerate( $value ) {
		if( $value != 'once' && !ae_Validate::isBoolean( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'true, false or once', $value ) );
		}
		if( ae_Settings::getSetting( 'comments_moderate' ) != $value ) {
			$this->settings['comments_moderate'] = $value;
		}
	}


	public function setGravatar( $value ) {
		if( ae_Settings::getSetting( 'gravatar' ) != $value ) {
			$this->settings['gravatar'] = $value;
		}
	}


	public function setGravatarDefault( $value ) {
		if( ae_Settings::getSetting( 'gravatar_default' ) != $value ) {
			$this->settings['gravatar_default'] = $value;
		}
	}


	public function setGravatarRating( $value ) {
		if( !ae_Validate::isGravatarRating( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'g, pg, r or x', $value ) );
		}
		if( ae_Settings::getSetting( 'gravatar_rating' ) != $value ) {
			$this->settings['gravatar_rating'] = $value;
		}
	}


	public function setIgnoreAgents( $value ) {
		if( is_array( $value ) ) {
			$value = serialize( $value );
		}
		if( ae_Settings::getSetting( 'ignore_agents' ) != $value ) {
			$this->settings['ignore_agents'] = $value;
		}
	}


	public function setNewsfeedAlternate( $value ) {
		if( ae_Settings::getSetting( 'newsfeed_alternate' ) != $value ) {
			$this->settings['newsfeed_alternate'] = $value;
		}
	}


	public function setNewsfeedContent( $value, $shorten = 255 ) {
		if( !ae_Validate::isNewsfeedDisplay( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( '', $value ) );
		}
		if( $value == 'shorten' ) {
			$value .= ';' . $shorten;
		}
		if( ae_Settings::getSetting( 'newsfeed_content' ) != $value ) {
			$this->settings['newsfeed_content'] = $value;
		}
	}


	public function setNewsfeedLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::NewsfeedLimit() != $value ) {
			$this->settings['newsfeed_limit'] = $value;
		}
	}


	public function setPermalinkArchive( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_archive' ) != $value ) {
			$this->settings['permalink_structure_archive'] = $value;
		}
	}


	public function setPermalinkAuthor( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_author' ) != $value ) {
			$this->settings['permalink_structure_author'] = $value;
		}
	}


	public function setPermalinkBlog( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_blog' ) != $value ) {
			$this->settings['permalink_structure_blog'] = $value;
		}
	}


	public function setPermalinkCategory( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_cat' ) != $value ) {
			$this->settings['permalink_structure_cat'] = $value;
		}
	}


	public function setPermalinkPage( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_page' ) != $value ) {
			$this->settings['permalink_structure_page'] = $value;
		}
	}


	public function setPermalinkPost( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_post' ) != $value ) {
			$this->settings['permalink_structure_post'] = $value;
		}
	}


	public function setPermalinkTag( $value ) {
		$value = self::CorrectPermalink( $value );
		if( ae_Settings::getSetting( 'permalink_structure_tag' ) != $value ) {
			$this->settings['permalink_structure_tag'] = $value;
		}
	}


	public function setPings( $value ) {
		if( !ae_Validate::isBoolean( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'true or false', $value ) );
		}
		if( ae_Settings::getSetting( 'pings' ) != $value ) {
			$this->settings['pings'] = $value;
		}
	}


	public function setTimezone( $value ) {
		if( ae_Settings::getSetting( 'timezone' ) != $value ) {
			$this->settings['timezone'] = $value;
		}
	}


	public function setUiManageCatsLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_manage_cats_limit' ) != $value ) {
			$this->settings['ui_manage_cats_limit'] = $value;
		}
	}


	public function setUiManageCommentsLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_manage_comments_limit' ) != $value ) {
			$this->settings['ui_manage_comments_limit'] = $value;
		}
	}


	public function setUiManagePagesLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_manage_pages_limit' ) != $value ) {
			$this->settings['ui_manage_pages_limit'] = $value;
		}
	}


	public function setUiManagePostsLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_manage_posts_limit' ) != $value ) {
			$this->settings['ui_manage_posts_limit'] = $value;
		}
	}


	public function setUiManageUsersLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_manage_users_limit' ) != $value ) {
			$this->settings['ui_manage_users_limit'] = $value;
		}
	}


	public function setUiMediaFilesLimit( $value ) {
		if( !ae_Validate::isDigit( $value ) ) {
			throw new Exception( ae_ErrorMessages::ValueNotExpected( 'integer', $value ) );
		}
		if( ae_Settings::getSetting( 'ui_media_files_limit' ) != $value ) {
			$this->settings['ui_media_files_limit'] = $value;
		}
	}


	public function renew_permalinks_author_on_update( $value ) {
		if( !is_bool( $value ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', gettype( $value ) ) );
		}
		$this->renew_permalinks['author'] = $value;
	}

	public function renew_permalinks_category_on_update( $value ) {
		if( !is_bool( $value ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', gettype( $value ) ) );
		}
		$this->renew_permalinks['category'] = $value;
	}

	public function renew_permalinks_page_on_update( $value ) {
		if( !is_bool( $value ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', gettype( $value ) ) );
		}
		$this->renew_permalinks['page'] = $value;
	}

	public function renew_permalinks_post_on_update( $value ) {
		if( !is_bool( $value ) ) {
			throw new Exception( ae_ErrorMessages::TypeNotExpected( 'boolean', gettype( $value ) ) );
		}
		$this->renew_permalinks['post'] = $value;
	}


}
