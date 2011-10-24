<?php

class InstallCheck {


	protected static $status = array(
		'config' => false,
		'media' => false,
		'themes' => false
	);

	protected static $users = null;


	public static function ConfigFile() {
		$error = '';

		if( !file_exists( '../includes/config.php' ) ) {
			$error = 'File <code>config.php</code> not found!';
		}
		else {
			$file = fopen( '../includes/config.php', 'r' );
			if( is_resource( $file ) ) {
				while( !feof( $file ) ) {
					$line = fgets( $file );
					if( strstr( $line, '$db_name = \'\';' ) > -1 ) {
						$error .= '<div class="error"><strong>Database name not specified.</strong><br />'
								. '<code>$db_name = \'\';</code></div>';
					}
					if( strstr( $line, '$db_user = \'\';' ) > -1 ) {
						$error .= '<div class="error"><strong>Database user not specified.</strong><br />'
								. '<code>$db_user = \'\';</code></div>';
					}
					if( strstr( $line, '$db_pass = \'\';' ) > -1 ) {
						$error .= '<div class="error"><strong>Database password not specified.</strong><br />'
								. '<code>$db_pass = \'\';</code></div>';
					}
				}
				fclose( $file );
			}
		}

		if( !empty( $error ) ) {
			return $error;
		}
		self::$status['config'] = true;
		return '<div class="success"><code>config.php</code> – good</div>';
	}


	public static function RightsMedia() {
		$needed_perms = 777;
		$perms = substr( decoct( fileperms( '../media' ) ), 1 );
		if( $perms < $needed_perms ) {
			$error = 'Directory <code>media</code> does not have rights set to <code>0777</code>.';
		}

		if( !empty( $error ) ) {
			return '<div class="error">' . $error . '</div>';
		}
		self::$status['media'] = true;
		return '<div class="success"><code>media</code> – good</div>';
	}


	public static function RightsThemes() {
		$needed_perms = 777;
		$perms = substr( decoct( fileperms( '../themes' ) ), 1 );
		if( $perms < $needed_perms ) {
			$error = 'Directory <code>themes</code> does not have rights set to <code>0777</code>.';
		}

		if( !empty( $error ) ) {
			return '<div class="error">' . $error . '</div>';
		}
		self::$status['themes'] = true;
		return '<div class="success"><code>themes</code> – good</div>';
	}


	public static function Ready() {
		foreach( self::$status as $key => $value ) {
			if( !$value ) {
				return false;
			}
		}
		return true;
	}


	public static function DbReady() {
		$needed = array(
			TABLE_CATEGORIES => false,
			TABLE_COMMENTS => false,
			TABLE_IPS => false,
			TABLE_LINKROLL => false,
			TABLE_MEDIA => false,
			TABLE_POSTS => false,
			TABLE_REFERRER => false,
			TABLE_RELATIONS => false,
			TABLE_RULES => false,
			TABLE_SETTINGS => false,
			TABLE_STATS => false,
			TABLE_TRACKS_SEND => false,
			TABLE_USERS => false
		);
		$show = mysql_query( 'SHOW TABLES' );
		while( $row = mysql_fetch_row( $show ) ) {
			if( array_key_exists( $row[0], $needed ) ) {
				$needed[$row[0]] = true;
			}
		}
		foreach( $needed as $value ) {
			if( !$value ) {
				return false;
			}
		}
		return true;
	}


	public static function UserExists() {
		if( self::$users != null ) {
			return self::$users;
		}
		$users = mysql_fetch_object(
			mysql_query( '
				SELECT COUNT( user_id ) AS count
				FROM ' . mysql_real_escape_string( TABLE_USERS ) . '
				WHERE user_role = "admin"
			' )
		);
		self::$users = ( $users->count > 0 );
		return self::$users;
	}


}
