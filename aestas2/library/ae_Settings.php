<?php

/**
 * Can be asked for the values of most of the settings.
 */
class ae_Settings {


	protected static $settings = array();
	protected static $preloaded = false;
	protected static $use_cache = true;


	/**
	 * Retrieves all settings from the DB at once, so that
	 * there do not have to be extra DB queries later on.
	 */
	public static function PreloadSettings() {
		self::$preloaded = true;
		if( empty( self::$settings ) ) {
			self::ReloadSettings();
		}
	}


	public static function ReloadSettings() {
		$retrieve = ae_Database::Query( '
			SELECT
				set_name,
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_origin = "aestas"
		' );

		while( $setting = mysql_fetch_object( $retrieve ) ) {
			if( $setting->set_name == 'ignore_agents' ) {
				$setting->set_value = unserialize( $setting->set_value );
			}
			self::$settings[$setting->set_name] = $setting->set_value;
		}
	}


	/**
	 * URL of a newsfeed service that is used.
	 */
	public static function FeedAlternate() {
		if( self::$use_cache && array_key_exists( 'newsfeed_alternate', self::$settings ) ) {
			return self::$settings['newsfeed_alternate'];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "newsfeed_alternate"
		';

		$feed = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		$return = empty( $feed ) ? '' : $feed['set_value'];

		return self::$settings['newsfeed_alternate'] = $return;
	}


	/**
	 * Which user agents shall be ignored.
	 * For example when counting visitors.
	 */
	public static function IgnoreAgents() {
		if( self::$use_cache && array_key_exists( 'ignore_agents', self::$settings ) ) {
			return self::$settings['ignore_agents'];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "ignore_agents"
		';

		$ignore = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return self::$settings['ignore_agents'] = unserialize( $ignore['set_value'] );
	}


	/**
	 * Limit of recent posts/comments to display in the newsfeeds.
	 */
	public static function NewsfeedLimit() {
		if( self::$use_cache && array_key_exists( 'newsfeed_limit', self::$settings ) ) {
			return self::$settings['newsfeed_limit'];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "newsfeed_limit"
		';

		$limit = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return self::$settings['newsfeed_limit'] = unserialize( $limit['set_value'] );
	}


	/**
	 * How many posts to display per blog page.
	 */
	public static function PostLimit() {
		if( self::$use_cache && array_key_exists( 'blog_post_limit', self::$settings ) ) {
			return self::$settings['blog_post_limit'];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "blog_post_limit"
		';

		$limit = ae_Database( $sql, ae_Database::SINGLE_RESULT );

		return self::$settings['blog_post_limit'] = $limit['set_value'];
	}


	public static function PermalinkStructure( $what ) {
		if( self::$use_cache && self::$preloaded ) {
			if( array_key_exists( $what, self::$settings ) ) {
				return self::$settings[$what];
			}
			else {
				throw new Exception( 'Key for permalink structure not found in preloaded settings.' );
			}
		}
		else {
			$sql = '
				SELECT
					set_value
				FROM `' . TABLE_SETTINGS . '`
				WHERE set_name = "' . mysql_real_escape_string( $what ) . '"
			';

			$result = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( !empty( $result ) ) {
				return $result['set_value'];
			}
			else {
				throw new Exception( 'Permalink structure for the given key "' . $what . '" not found in database.' );
			}
		}
	}


	/**
	 * Name of the current theme and the template engine in use.
	 * Returns an array with the keys "blog_theme" and "blog_theme_system".
	 */
	public static function Theme() {
		if( self::$use_cache && array_key_exists( 'blog_theme', self::$settings )
				&& array_key_exists( 'blog_theme_system', self::$settings ) ) {
			return array(
				'blog_theme' => self::$settings['blog_theme'],
				'blog_theme_system' => self::$settings['blog_theme_system'],
				0 => self::$settings['blog_theme'],
				1 => self::$settings['blog_theme_system']
			);
		}

		$sql = '
			SELECT
				set_name,
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "blog_theme"
			OR set_name = "blog_theme_system"
		';

		$query = ae_Database::Query( $sql );

		$theme = array();
		while( $row = mysql_fetch_assoc( $query ) ) {
			$theme[$row['set_name']] = $row['set_value'];
		}

		self::$settings['blog_theme'] = $theme['blog_theme'];
		self::$settings['blog_theme_system'] = $theme['blog_theme_system'];

		return $theme;
	}


	/**
	 * 
	 */
	public static function Timezone() {
		if( self::$use_cache && array_key_exists( 'timezone', self::$settings ) ) {
			return self::$settings['timezone'];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "timezone"
		';

		$timezone = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return self::$settings['timezone'] = $timezone['set_value'];
	}


	/**
	 * Looks if the setting was preloaded and returns it.
	 * Otherwise it will load the setting now.
	 * This function should only be used if there is no other
	 * providing especially the wanted setting.
	 */
	public static function getSetting( $setting ) {
		if( self::$use_cache && array_key_exists( $setting, self::$settings ) ) {
			return self::$settings[$setting];
		}

		$sql = '
			SELECT
				set_value
			FROM `' . TABLE_SETTINGS . '`
			WHERE set_name = "' . mysql_real_escape_string( $setting ) . '"
		';

		$get = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		$return = empty( $get ) ? null : $get['set_value'];

		return self::$settings[$setting] = $return;
	}


	/**
	 * If the preload has been used or a value has already been asked,
	 * the value will be cached for less database traffic.
	 * True: Use this behaviour.
	 * False: Always get the value fresh from the database.
	 */
	public static function UseCache( $value ) {
		self::$use_cache = $value;
	}


}
