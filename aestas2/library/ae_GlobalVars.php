<?php


class ae_GlobalVars {


	protected static $user_editors = array(
		'code', 'ckeditor'
	);

	protected static $user_roles = array(
		'admin', 'author', 'guest'
	);

	protected static $user_statuses = array(
		'active', 'suspended', 'trash', 'deleted'
	);

	protected static $post_statuses = array(
		'draft', 'published', 'expired', 'trash'
	);

	protected static $comment_statuses = array(
		'unapproved', 'approved', 'spam', 'trash'
	);

	protected static $media_statuses = array(
		'available', 'trash'
	);

	protected static $media_types = array(
		'image', 'audio', 'video', 'application', 'text'
	);

	protected static $category_statuses = array(
		'active', 'trash'
	);

	protected static $rule_statuses = array(
		'active', 'inactive'
	);

	protected static $rule_precisions = array(
		'contains', 'exact', 'regex'
	);

	protected static $gravatar_ratings = array(
		'g', 'pg', 'r', 'x'
	);

	protected static $newsfeed_displays = array(
		'default', 'excerpt', 'full', 'shorten'
	);

	protected static $comment_do_not_strip_tags = array(
		'a', 'abbr', 'b', 'blockquote', 'cite', 'code', 'del', 'em', 'i', 'strong'
	);

	protected static $comment_allowed_attributes = array(
		'alt', 'cite', 'href', 'name', 'title'
	);

	// I feel really bad about this one.
	// Regex isn't particularly my strong point.
	protected static $comment_remove_bad_attributes = array(
		'pattern' => '/<([a-z0-9]+)((?: *(?:alt|cite|href|name|title) *= *(?:"[^"]*"|\'[^\']*\'|[^>]*))*)(?: *[a-z0-9]+(?<!alt|cite|href|name|title) *= *(?:"[^"]*"|\'[^\']*\'|[^>]*))+((?: *(?:alt|cite|href|name|title) *= *(?:"[^"]*"|\'[^\']*\'|[^>]*))*) *>/i',
		'replace' => '<$1$2$3>'
	);

	protected static $comment_empty_name = 'Anonymous';

	protected static $auth_systems = array(
		'session', 'cookie'
	);

	protected static $tablecolprefixes = array(
		'post', 'page', 'user', 'cat', 'comment', 'media', 'poll', 'rule'
	);


	public static function getUserEditors() {
		return self::$user_editors;
	}

	public static function getUserRoles() {
		return self::$user_roles;
	}

	public static function getUserStatuses() {
		return self::$user_statuses;
	}

	public static function getPostStatuses() {
		return self::$post_statuses;
	}

	public static function getCommentStatuses() {
		return self::$comment_statuses;
	}

	public static function getMediaStatuses() {
		return self::$media_statuses;
	}

	public static function getMediaTypes() {
		return self::$media_types;
	}

	public static function getCategoryStatuses() {
		return self::$category_statuses;
	}

	public static function getRuleStatuses() {
		return self::$rule_statuses;
	}

	public static function getRulePrecisions() {
		return self::$rule_precisions;
	}

	public static function getGravatarRatings() {
		return self::$gravatar_ratings;
	}

	public static function getNewsfeedDisplays() {
		return self::$newsfeed_displays;
	}

	public static function getCommentAllowedTags() {
		return self::$comment_do_not_strip_tags;
	}

	/**
	 * Variants:
	 * strip_tags - &lt;a&gt;&lt;b&gt;
	 * preg_replace - a|b
	 */
	public static function getCommentNotStripTags( $variant = null ) {
		if( $variant == 'strip_tags' ) {
			return '<' . implode( '><', self::$comment_do_not_strip_tags ) . '>';
		}
		return implode( '|', self::$comment_do_not_strip_tags );
	}

	/**
	 * Variants:
	 * preg_replace - a|b
	 * otherwise - as array
	 */
	public static function getCommentAllowedAttributes( $variant = null ) {
		if( $variant == 'preg_replace' ) {
			return implode( '|', self::$comment_allowed_attributes );
		}
		return self::$comment_allowed_attributes;
	}

	/**
	 * Expects "pattern" or "replace" as parameter.
	 */
	public static function getCommentBadAttributes( $return ) {
		return self::$comment_remove_bad_attributes[$return];
	}

	public static function getCommentEmptyName() {
		return self::$comment_empty_name;
	}

	public static function getAuthSystems() {
		return self::$auth_systems;
	}

	/**
	 * Returns an array of table column prefixes of tables that are likely
	 * to have a column for permalinks or to receive such a column in the future.
	 */
	public static function getTableColumnPrefixes() {
		return self::$tablecolprefixes;
	}


	public static function getTableToColumnPrefix( $col ) {
		$table2col = array(
			'comment' => TABLE_COMMENTS,
			'post' => TABLE_POSTS,
			'media' => TABLE_MEDIA,
			'rule' => TABLE_RULES,
			'cat' => TABLE_CATEGORIES,
			'user' => TABLE_USERS,
			'trackback' => TABLE_TRACKS_SEND,
			'stat' => TABLE_STATS,
			'set' => TABLE_SETTINGS,
			'roll' => TABLE_LINKROLL
		);
		if( isset( $table2col[$col] ) ) {
			return $table2col[$col];
		}
		return false;
	}


}
