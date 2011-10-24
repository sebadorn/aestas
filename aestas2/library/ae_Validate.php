<?php


class ae_Validate {

	protected static $urlpattern =
		'!^(http://|https://|ftp://|ftps://)?[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$!';
	protected static $protocolpattern = '!^(http|https|ftp|ftps)://!';


	public static function isAuthSystem( $value ) {
		return in_array( $value, ae_GlobalVars::getAuthSystems() );
	}


	public static function isStatus( $value, $type ) {
		if( $type == 'comment' ) {
			return self::isCommentStatus( $value );
		}
		else if( $type == 'post' || $type == 'page' ) {
			return self::isPostStatus( $value );
		}
		else if( $type == 'user' ) {
			return self::isUserStatus( $value );
		}
		else if( $type == 'category' ) {
			return self::isCategoryStatus( $value );
		}
		return false;
	}


	public static function isCommentStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getCommentStatuses() );
	}


	public static function isPostStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getPostStatuses() );
	}


	public static function isMediaStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getMediaStatuses() );
	}


	public static function isMediaType( $value ) {
		$value = explode( '/', $value );
		return in_array( $value[0], ae_GlobalVars::getMediaTypes() );
	}


	public static function isCategoryStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getCategoryStatuses() );
	}


	public static function isRuleStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getRuleStatuses() );
	}


	public static function isRulePrecision( $value ) {
		return in_array( $value, ae_GlobalVars::getRulePrecisions() );
	}


	public static function isUserEditor( $value ) {
		return in_array( $value, ae_GlobalVars::getUserEditors() );
	}


	public static function isUserStatus( $value ) {
		return in_array( $value, ae_GlobalVars::getUserStatuses() );
	}


	public static function isUserRole( $value ) {
		return in_array( $value, ae_GlobalVars::getUserRoles() );
	}


	/**
	 * Validates if value is date format "YYYY-MM-DD".
	 */
	public static function isDate_MySQL( $value ) {
		return preg_match( '/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/', $value ) > 0;
	}


	/**
	 * Validates if value is timestamp format "YYYY-MM-DD HH:II:SS".
	 */
	public static function isTimestamp_MySQL( $value ) {
		return preg_match( '/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value ) > 0;
	}


	/**
	 * Validates if value is either real boolean or a string with the value "true" or "false".
	 */
	public static function isBoolean( $value ) {
		if( $value === 0 ) {
			return false;
		}
		return ( $value == 'true' || $value == 'false' || $value === true || $value === false );
	}


	/**
	 * Validates if value only contains digit characters.
	 * It doesn't matter if the given type of the value is a string.
	 */
	public static function isDigit( $value ) {
		return preg_match( '/^-?[0-9]+$/', $value ) > 0;
	}


	/**
	 * Validates if value is an IP in either IPv4 or IPv6 format.
	 */
	public static function isIp( $value ) {
		if( filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return true;
		}
		if( filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return true;
		}
		return false;
	}


	/**
	 * Validates if value is an email address.
	 */
	public static function isEmail( $value ) {
		return ( filter_var( $value, FILTER_VALIDATE_EMAIL ) !== false );
	}


	/**
	 * Validates if value is an URL.
	 * Also returns true if it is an URL with missing protocol.
	 */
	public static function isUrl( $value ) {
		// Doesn't use filter_var(), because it would return true for "http://nonsense".
		return ( preg_match( self::$urlpattern, $value ) > 0 );
	}


	/**
	 * Validates if value starts with an URL protocol.
	 */
	public static function hasUrlProtocol( $value ) {
		return ( preg_match( self::$protocolpattern, $value ) > 0 );
	}


	public static function isGravatarRating( $value ) {
		return in_array( $value, ae_GlobalVars::getGravatarRatings() );
	}


	public static function isNewsfeedDisplay( $value ) {
		return in_array( $value, ae_GlobalVars::getNewsfeedDisplays() );
	}


	public static function isTableColumnPrefix( $value ) {
		return in_array( $value, ae_GlobalVars::getTableColumnPrefixes() );
	}


}
