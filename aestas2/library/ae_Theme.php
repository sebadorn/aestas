<?php


require_once( 'pclzip/pclzip.lib.php' );

/**
 * Supported archive formats: ZIP.
 */
class ae_Theme {

	protected static $themes_dir = '../themes/';
	protected static $not_theme_dirs = array( '.', '..', '.svn' );
	protected static $regex_editable_files = '/\.(css|js|php[0-9]?|sql|txt|x?html?|xml|xsl)$/i';


	public static function getThemes() {
		$themes = array();

		if( is_dir( self::$themes_dir ) ) {
			$handle = opendir( self::$themes_dir );

			if( $handle ) {
				while( ( $theme_dir = readdir( $handle ) ) !== false ) {
					if( self::isThemeDir( $theme_dir ) ) {
						$name = self::getName( self::$themes_dir . $theme_dir );
						$themes[$theme_dir] = $name;
					}
				}

				closedir( $handle );
			}
		}

		natcasesort( $themes );

		return $themes;
	}


	/**
	 * Reads theme information from the style.css file of the theme.
	 * Typical for WP themes.
	 */
	public static function getWordpressInfo( $theme_dir ) {
		$theme_dir = self::$themes_dir . $theme_dir;

		$wp_theme = array(
			'uri' => '',
			'description' => '',
			'author' => '',
			'author_uri' => '',
			'template' => '',
			'version' => '',
			'tags' => ''
		);

		$search = array(
			'Theme URI',
			'Description',
			'Author',
			'Author URI',
			'Template',
			'Version',
			'Tags'
		);


		if( file_exists( $theme_dir . '/style.css' ) ) {
			$file = file( $theme_dir . '/style.css' );

			$wp_info_scope = self::FindStartAndEndOfFirstComment( $file );

			// If not found, there are no information
			if( $wp_info_scope['start'] == -1 || $wp_info_scope['end'] == -1 ) {
				return $wp_theme;
			}

			reset( $wp_theme );

			foreach( $search as $info ) {
				$key = key( $wp_theme );
				$wp_theme[$key] = self::FindInfoInFile( $file, $wp_info_scope['start'], $wp_info_scope['end'], $info );
				next( $wp_theme );
			}

		}

		return $wp_theme;
	}


	public static function getScreenshot( $system, $theme_dir ) {
		$theme_dir = self::$themes_dir . $theme_dir;

		// WordPress, screenshot.png
		if( $system == 'wordpress' ) {
			if( file_exists( $theme_dir . '/screenshot.png' ) ) {
				$ext = 'png';
			}
			else if( file_exists( $theme_dir . '/screenshot.jpg' ) ) {
				$ext = 'jpg';
			}
			return '<img src="' . $theme_dir . '/screenshot.' .  $ext . '" alt="What the theme looks like." />';
		}
	}


	public static function getName( $theme_dir, $system = null ) {
		$system = ($system == null) ? self::getSystem( $theme_dir ) : $system;
		$theme_dir = self::$themes_dir . $theme_dir;

		// WordPress
		if( $system == 'wordpress' ) {
			$file = file( $theme_dir . '/style.css' );

			for( $i = 0; $i <= 4; $i++ ) {
				if( preg_match( '/Theme Name:([^\r\n]*)/', $file[$i], $hit ) ) {
					return trim( $hit[1] );
				}
			}
		}

		// For unknown systems use the directory name
		else {
			$parts = explode( '/', $theme_dir );
			$name = end( $parts );

			return ucfirst( $name );
		}
	}


	/**
	 * Theme engine, for example "wordpress" or "expressionengine".
	 */
	public static function getSystem( $theme_dir ) {
		$theme_dir = self::$themes_dir . $theme_dir;

		// Checking for WordPress
		if( file_exists( $theme_dir . '/style.css' ) ) {
			$file = file( $theme_dir . '/style.css' );

			for( $i = 0; $i < 4; $i++ ) {
				if( strpos( $file[$i], 'Theme Name:' ) !== false ) {
					return 'wordpress';
				}
			}
		}

		return 'unknown';
	}



	public static function getThemesForEditList() {
		$themes = array();
		$out = '';
		$ran = rand( 1, 1000 );

		if( is_dir( self::$themes_dir ) ) {
			$handle = opendir( self::$themes_dir );
			if( $handle ) {
				while( ( $theme_dir = readdir( $handle ) ) !== false ) {
					if( self::isThemeDir( $theme_dir ) ) {
						$name = self::getName( $theme_dir );
						$themes[$theme_dir] = $name;
					}
				}
			}
		}

		natcasesort( $themes );

		foreach( $themes as $dir => $theme ) {
			if( isset( $_GET['themedir'] ) ) {
				$used = ( $dir == $_GET['themedir'] ) ? ' class="used"' : '';
			}

			$out .= '<li' . $used . '><a href="?area=theme&amp;show=edittheme&amp;themedir='
				. urlencode( $dir ) . '&amp;ran=' . $ran . '">' . $theme . '</a></li>' . "\n";
		}

		return $out;
	}


	public static function getFilesForEditList( $dir, $used_file ) {
		$out = '';
		$files = array();

		if( isset( $dir ) && file_exists( self::$themes_dir . $dir ) ) {
			$theme = opendir( self::$themes_dir . $dir );

			if( $theme ) {
				$ran = rand( 1001, 2000 );
				while( ( $loop_file = readdir( $theme ) ) !== false ) {

					if( !self::isThemeDir( $loop_file ) || !preg_match( self::$regex_editable_files, $loop_file ) ) {
						continue;
					}

					if( !is_dir( self::$themes_dir . $dir . '/' . $loop_file ) ) {
						$used = ( $used_file == $loop_file ) ? ' class="used"' : '';
						$files[$loop_file] = $used;
					}
				}

				closedir( $theme );
			}
		}

		ksort( $files );

		foreach( $files as $file => $used ) {
			$out .= '<li' . $used . '>' . "\n"
				. '<a href="?area=theme&amp;show=edittheme&amp;themedir=' . urlencode( $dir ) . '&amp;file='
				. urlencode( $file ) . '&amp;rand=' . $ran . '">' . $file . '</a>'
				. '</li>' . "\n";
		}

		return $out;
	}


	public static function ReadFileContent( $theme, $file ) {
		$filepath = self::$themes_dir . $theme . '/' . $file;

		if( file_exists( $filepath ) ) {
			$handle = fopen( $filepath, 'r' );

			if( $handle ) {
				$filesize = filesize( $filepath );
				$fcontent = fread( $handle, $filesize );
				$fcontent = htmlspecialchars( $fcontent );
				fclose( $handle );

				return $fcontent;
			}
		}

		return false;
	}


	/**
	 * Unpacks a ZIP file into "themes/".
	 * In "themes/" will only be one directory for the archive
	 * containing all the files and directories of the ZIP.
	 */
	public static function UnpackZip( $zip_file, $dir_name ) {
		$themes_dir = '../' . self::$themes_dir;

		$zip = new PclZip( $zip_file );
		$zip_list = $zip->listContent();

		$file_count = self::ZipCountFilesOnTopLevel( $zip_list );

		// If there are files on top level make a directory for the theme
		if( $file_count > 0 ) {
			$name_parts = explode( '.', $dir_name );
			$dir_name = substr( $dir_name, 0, ( strlen( end( $name_parts ) ) + 1 ) * -1 );

			if( !file_exists( $themes_dir . $dir_name ) ) {
				mkdir( $themes_dir . $dir_name, 0755 );
			}

			$dir_name .= '/';
		}
		else {
			$dir_name = self::ZipNameOfFirstDir( $zip_list );
			mkdir( $themes_dir . $dir_name, 0755 );
			$dir_name = '';
		}

		$path = $themes_dir . $dir_name;
		$skip_first = ( $file_count > 0 );
		self::ZipUnpackTheme( $zip, $path );
	}


	public static function SaveEditedFile( $filepath, $content ) {
		$filepath = '../' . self::$themes_dir . $filepath;

		if( file_exists( $filepath ) ) {
			$file = fopen( $filepath, 'w' );
			if( $file ) {
				fwrite( $file, $content );
				fclose( $file );
				return true;
			}
		}

		return false;
	}


	/**
	 * Deletes all files and directories of a theme.
	 */
	public static function DeleteTheme( $theme ) {
		if( !empty( $theme ) ) {
			$files = glob( $theme . '*', GLOB_MARK );

			foreach( $files as $file ) {
				$file = str_replace( '\\', '/', $file );
				if( substr( $file, -1 ) == '/' ) {
					self::DeleteTheme( $file );
				}
				else {
					unlink( $file );
				}
			}

			if( is_dir( $theme ) ) {
				rmdir( $theme );
			}
		}
	}


	public static function UseTheme( $theme, $system ) {
		$query_theme = ae_Database::Query( '
			UPDATE `' . TABLE_SETTINGS . '`
			SET
				set_value = "' . mysql_real_escape_string( $theme ) . '"
			WHERE set_name = "blog_theme"
		' );

		$query_system = ae_Database::Query( '
			UPDATE `' . TABLE_SETTINGS . '`
			SET
				set_value = "' . mysql_real_escape_string( $system ) . '"
			WHERE set_name = "blog_theme_system"
		' );

		return ( $query_theme && $query_system );
	}


	public static function FindFavicon( $filename = 'favicon' ) {
		$formats = array( 'png', 'jpg', 'gif', 'ico' );
		$favicon_absolute = false;
		$theme = ae_Settings::Theme();
		$ups = ae_URL::DirectoryUps();

		foreach( $formats as $format ) {
			if( file_exists( $ups . $filename . '.' . $format ) ) {
				$favicon_absolute = URL . '/' . $filename . '.' . $format;
				break;
			}
			else if( file_exists( $ups . 'themes/' .  $theme['blog_theme'] . '/' . $filename . '.' . $format ) ) {
				$favicon_absolute = URL . '/themes/' . $theme['blog_theme'] . '/' . $filename . '.' . $format;
				break;
			}
		}

		return $favicon_absolute;
	}



	//---------- Protected functions


	protected static function isThemeDir( $name ) {
		return !in_array( $name, self::$not_theme_dirs );
	}


	protected static function FindStartAndEndOfFirstComment( $filearray ) {
		$start = -1;
		$end = -1;

		$filearraysize = count( $filearray );
		for( $i = 0; $i < $filearraysize; $i++) {
			if( $start == -1 ) {
				if( strpos( $filearray[$i], '/*' ) !== false ) {
					$start = $i;
				}
			}
			if( strpos( $filearray[$i], '*/' ) !== false ) {
				$end = $i;
				break;
			}
		}

		return array(
			'start' => $start,
			'end' => $end
		);
	}


	protected static function FindInfoInFile( $file, $comment_start, $comment_end, $info ) {
		for( $i = $comment_start; $i <= $comment_end; $i++ ) {
			if( preg_match( '/' . $info . ':([^\r\n]*)/', $file[$i], $hit ) ) {
				return trim( $hit[1] );
			}
		}
		return '';
	}


	protected static function ZipCountFilesOnTopLevel( $zip_list ) {
		$i = 0;
		foreach( $zip_list as $entry ) {
			if( !strpos( $entry['filename'], '/' ) ) {
				$i++;
			}
		}
		return $i;
	}


	protected static function ZipNameOfFirstDir( $zip_list ) {
		foreach( $zip_list as $entry ) {
			if( strpos( $entry['filename'], '/' ) ) {
				return $entry['filename'];
			}
		}
		return '';
	}


	protected static function ZipUnpackTheme( $zip, $path ) {
		$zip->extract( $p_path = $path );
	}


}
