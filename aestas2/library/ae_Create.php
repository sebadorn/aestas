<?php


class ae_Create {


	/**
	 * Takes an array with the keys: year, month, day, hour, minute.
	 * If a value to a key does not match the pattern of a number,
	 * it will be replaced with the current date value.
	 */
	public static function Date( $date ) {
		if( !preg_match( '/^[0-9]{4}$/', $date['year'] ) ) {
			$date['year'] = date( 'Y' );
		}

		if( !preg_match( '/^[0-9]{2}$/', $date['month'] ) ) {
			$date['month'] = date( 'm' );
		}

		if( !preg_match( '/^[0-9]{2}$/', $date['day'] ) ) {
			$date['day'] = date( 'd' );
		}

		if( !preg_match( '/^[0-9]{2}$/', $date['hour'] ) ) {
			$date['hour'] = date( 'H' );
		}

		if( !preg_match( '/^[0-9]{2}$/', $date['minute'] ) ) {
			$date['minute'] = date( 'i' );
		}

		// Building date MySQL-compatible
		return $date['year'] . '-' . $date['month'] . '-' . $date['day']
			. ' ' . $date['hour'] . ':' . $date['minute'] . ':00';
	}


	/**
	 * Takes an array with the keys: year, month, day, hour, minute.
	 * If a value to a key does not match the pattern of a number,
	 * the function returns null.
	 */
	public static function DateExpires( $date ) {
		if( !preg_match( '/^[0-9]{4}$/', $date['year'] )
				|| !preg_match( '/^[0-9]{2}$/', $date['month'] )
				|| !preg_match( '/^[0-9]{2}$/', $date['day'] )
				|| !preg_match( '/^[0-9]{2}$/', $date['hour'] )
				|| !preg_match( '/^[0-9]{2}$/', $date['minute'] ) ) {
			return null;
		}

		// Building date MySQL-compatible
		return $date['year'] . '-' . $date['month'] . '-' . $date['day']
			. ' ' . $date['hour'] . ':' . $date['minute'] . ':00';
	}


	public static function FindMediaInPostOrPage( ae_Post $post ) {
		$content = str_replace( '\\', '/', $post->getContent() );

		preg_match_all(
			'!media/[[a-zA-Z0-9_/]*/]?[^:\'"<>\?/\|\\\]*!',	// Would find "media/2009/11/example.png"
			$content,
			$mentioned_media
		);


		foreach( $mentioned_media[0] as $used ) {
			$used = substr( $used, 6 );
			$date_string = '';

			if( preg_match( '!^[0-9]+/[0-9]{2}/!', $used, $date_media ) ) {
				$date_parts = explode( '/', $date_media[0] );
				$date_string = $date_parts[0] . '-' . $date_parts[1] . '-__ __:__:__';
			}
			else {
				continue;
			}

			$name = explode( '/', $used );
			$name = end( $name );

			$sql = '
				SELECT
					COUNT( media_id ) AS hits,
					media_id,
					COUNT( this_id ) AS relations
				FROM `' . TABLE_MEDIA . '`
				LEFT OUTER JOIN `' . TABLE_RELATIONS . '`
				ON media_id = this_id
				WHERE media_name = "' . mysql_real_escape_string( $name ) . '"
				AND media_date LIKE "' . mysql_real_escape_string( $date_string ) . '"
				AND relation_type = "file to post"
			';

			$media = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			if( $media['hits'] > 0 && $media['relations'] == 0 && ae_Validate::isDigit( $media['media_id'] ) ) {
				ae_Database::Query( '
					INSERT INTO `' . TABLE_RELATIONS . '` (
						this_id,
						that_id,
						relation_type
					) VALUES (
						' . $media['media_id'] . ',
						' . $post->getId() . ',
						"file to post"
					)
				' );
			}
		}
	}


	public static function CategoryRelations( $categories, $post_id ) {
		if( empty( $categories ) ) {
			return true;
		}

		$posts_cats = '';

		foreach( $categories as $cat ) {
			if( ae_Validate::isDigit( $cat ) ) {
				$posts_cats .= '( ' . $post_id . ', ' . $cat . ', "post to cat" ), ';
			}
		}
		$posts_cats = substr( $posts_cats, 0, -2 );

		return ae_Database::Query( '
			INSERT INTO `' . TABLE_RELATIONS . '` (
				this_id,
				that_id,
				relation_type
			) VALUES
				' . $posts_cats
		);
	}


	/**
	 * Returns the ID of the last inserted user.
	 */
	public static function LastIdOfUser() {
		return self::LastIdOf( 'user' );
	}

	/**
	 * Returns the ID of the last inserted category.
	 */
	public static function LastIdOfCategory() {
		return self::LastIdOf( 'category' );
	}

	/**
	 * Returns the ID of the last inserted comment.
	 */
	public static function LastIdOfComment() {
		return self::LastIdOf( 'comment' );
	}

	/**
	 * Returns the ID of the last inserted page.
	 */
	public static function LastIdOfPage() {
		return self::LastIdOf( 'page' );
	}

	/**
	 * Returns the ID of the last inserted post.
	 */
	public static function LastIdOfPost() {
		return self::LastIdOf( 'post' );
	}

	/**
	 * Returns the ID of the last inserted media.
	 */
	public static function LastIdOfMedia() {
		return self::LastIdOf( 'media' );
	}



	//---------- Protected static functions


	protected static function LastIdOf( $what ) {
		switch( $what ) {
			case 'category':
				$table = TABLE_CATEGORIES;
				break;
			case 'comment':
				$table = TABLE_COMMENTS;
				break;
			case 'media':
				$table = TABLE_MEDIA;
				break;
			case 'page':
			case 'post':
				$table = TABLE_POSTS;
				break;
			case 'user':
				$table = TABLE_USERS;
				break;
			default:
				throw new Exception( ae_ErrorMessages::Unknown( 'db table', $what ) );
		}

		$sql = '
			SELECT
				LAST_INSERT_ID() AS id
			FROM `' . $table . '`
		';

		$last = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $last['id'];
	}


}
