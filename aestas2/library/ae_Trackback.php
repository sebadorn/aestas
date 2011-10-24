<?php


class ae_Trackback {


	public static function Send( $track_array, $data, $from_id ) {
		$track_array = self::PrepareTrackbackArray( $track_array );

		foreach( $track_array as $trackback ) {
			if( empty( $trackback ) ) {
				continue;
			}
			$data['target'] = $trackback;
			self::SendTrackback( $data, $from_id );
		}
	}


	/**
	 * Builds the message of the trackback consisting of the URL to the post,
	 * the title of the post, the name of the blog and an excerpt of the post.
	 */
	public static function BuildMessage( $url, $title, $excerpt ) {
		$data['message'] = 'url=' . urlencode( $url )
				. '&title=' . urlencode( $title )
				. '&excerpt=' . urlencode( self::ShortenMessage( $excerpt ) )
				. '&blog_name=' . urlencode( ae_Settings::getSetting( 'bloginfo_title' ) );
		return $data;
	}


	/**
	 * Returns true if $_POST contains the field "url".
	 */
	public static function Detect() {
		return isset( $_POST['url'] );
	}


	/**
	 * Receives and processes a trackback.
	 */
	public static function Receive( $to = 'post' ) {
		$error = '0';
		$message = '';

		if( empty( $_POST['url'] ) ) {
			$error = '1';
			$message = '<message>No URL submitted.</message>' . "\n";
		}
		else {
			// Prepare URL
			$url = urldecode( $_POST['url'] );
			$title = isset( $_POST['title'] ) ? $_POST['title'] : '';
			$blog_name = isset( $_POST['blog_name'] ) ? $_POST['blog_name'] : '';
			$excerpt = isset( $_POST['excerpt'] ) ? $_POST['excerpt'] : '';

			// Prepare title
			if( !empty( $title ) ) {
				$title = urldecode( $title );
				$title = htmlspecialchars( $title );
			}

			// Prepare blog_name
			if( !empty( $blog_name ) ) {
				$blog_name = urldecode( $blog_name );
			}

			// Prepare excerpt
			if( !empty( $excerpt ) ) {
				$excerpt = self::ShortenMessage( $excerpt );
			}

			$id = ( $to == 'post' ) ? SINGLE_POST : PAGE_ID;

			if( !self::ExistsTrackRecv( $url, $id ) ) {
				self::SaveReceived( $url, $title, $blog_name, $excerpt, $id, $to );
			}
			else {
				$error = '1';
				$message = '<message>A trackback from your post has already been received.</message>' . "\n";
			}
		}

		self::SendResponse( $error, $message );
	}


	/**
	 * Shortens a message if necessary and adds an ellipse.
	 */
	public static function ShortenMessage( $message ) {
		if( strlen( $message ) > 255 ) {
			$message = substr( $message, 0, 255 ) . '…';
		}
		return $message;
	}



	//---------- Protected functions


	protected static function PrepareTrackbackArray( $track_array ) {
		$track_array = array_map( 'trim', $track_array );
		$track_array = array_map( 'strtolower', $track_array );
		$track_array = array_unique( $track_array );
		$track_array = self::AddProtocol( $track_array );
		return $track_array;
	}


	/**
	 * Attempts to send a trackback.
	 * Returns true if trackback has been sent, false otherwise.
	 */
	protected static function SendTrackback( $data, $from_id, $from_type ) {
		$url_parts = parse_url( $data['target'] );
		$data['host'] = $url_parts['host'];

		if( !self::ExistsTrackSend( $data['target'], $from_id ) ) {
			$params = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded; charset=utf-8' . "\r\n",
					'content' => $data['message']
				)
			);
			$ctx = stream_context_create( $params );
			if( !is_resource( $ctx ) ) {
				return false;
			}

			$fp = fopen( $data['target'], 'rb', false, $ctx );
			if( !is_resource( $fp ) ) {
				return false;
			}

			$response = stream_get_contents( $fp );
			fclose( $fp );

			$error = self::EvaluateResponse( $response );
			self::SaveSend( $data['target'], $from_id, $error, $from_type );

			return true;
		}
		return false;
	}


	/**
	 * Returns true if a trackback has already been received, false otherwise.
	 */
	protected static function ExistsTrackRecv( $url, $to_id ) {
		$sql = '
			SELECT
				COUNT( comment_id ) AS count
			FROM `' . TABLE_COMMENTS . '`
			WHERE comment_has_type = "trackback"
			AND comment_status != "trash"
			AND comment_url = "' . mysql_real_escape_string( $url ) . '"
			AND comment_post_id = ' . mysql_real_escape_string( $to_id ) . '
		';

		$track_exists = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $track_exists['count'] > 0 );
	}


	/**
	 * Returns true if a trackback has already been sent, false otherwise.
	 */
	protected static function ExistsTrackSend( $to_url, $from_id ) {
		$sql = '
			SELECT
				COUNT( trackback_id ) AS count
			FROM `' . TABLE_TRACKS_SEND . '`
			WHERE trackback_to_url = "' . mysql_real_escape_string( $to_url ) . '"
			AND trackback_from_id = ' . mysql_real_escape_string( $from_id ) . '
		';

		$track_exists = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $track_exists['count'] > 0 );
	}


	/**
	 *
	 */
	protected static function EvaluateResponse( $response ) {
		// Check error code: No error
		if( preg_match( '!<error>0</error>!i', $response ) ) {
			$state = '';
		}
		// Check error code: An error occured
		else if( preg_match( '!<error>1</error>!i', $response ) ) {
			if( preg_match( '/<message>(.*)<\/message>/is', $response, $message ) ) {
				$state = $message[1];
			}
			else {
				$state = 'unknown error';
			}
		}
		// No error code recieved
		else {
			$state = 'not able to evaluate response';
		}

		return $state;
	}


	/**
	 *
	 */
	protected static function SaveSend( $to_url, $from_id, $error ) {
		return ae_Database::Query( '
			INSERT INTO `' . TABLE_TRACKS_SEND . '` (
				trackback_from_id,
				trackback_to_url,
				trackback_error
			) VALUES (
				' . mysql_real_escape_string( $from_id ) . ',
				"' . mysql_real_escape_string( $to_url ) . '",
				"' . mysql_real_escape_string( $error ) . '"
			)
		' );
	}


	/**
	 * If missing adds the "http://" protocol at the begin of the URL.
	 */
	protected static function AddProtocol( $tracks ) {
		foreach( $tracks as &$track ) {
			if( empty( $track ) ) {
				continue;
			}
			if( !preg_match( '/^http[s]?\:\/\//', $track ) ) {
				// If there is some other protocol, remove it
				if( preg_match( '/^.*\:\/\//', $track ) ) {
					$track = preg_replace( '/^.*\:\/\//', '', $track );
				}
				$track = 'http://' . $track;
			}
		}
		return $tracks;
	}


	/**
	 * Saves a received trackback in the DB.
	 */
	protected static function SaveReceived( $url, $title, $blog_name, $excerpt, $post_id ) {
		$status = ( ae_Settings::getSetting( 'comments_moderate' ) == 'true' ) ? 'unapproved' : 'approved';

		$c = new ae_Comment();
		$c->setPostId( $post_id );
		$c->setUrl( $url );
		$c->setAuthor( $blog_name . ' | ' . $title );
		$c->setContent( $excerpt );
		$c->setHasType( 'trackback' );
		$c->setStatus( $status );
		return $c->save_new();
	}


	/**
	 * Send a response for a received trackback.
	 */
	protected static function SendResponse( $error, $message ) {
		header( 'Content-Type: text/xml' );
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<response><error>' . $error . '</error>' . $message . '</response>';
	}


}
