<?php

class ae_Media {


	// Class attributes
	public static $STATUSES = array( 'available', 'trash' );
	public static $TYPES = array( 'image', 'audio', 'video', 'application', 'text' );


	// Object attributes
	protected $id;
	protected $name = '';
	protected $date;
	protected $description = '';
	protected $dimensions = '';
	protected $tags = '';
	protected $type = '';
	protected $uploader_id = 0;
	protected $status = 'available';

	protected $loaded_name = '';


	public function __construct( $source = null ) {
		$this->date = date( 'Y-m-d H:i:s' );

		if( is_array( $source ) ) {
			$this->id = $source['media_id'];
			$this->name = $source['media_name'];
			$this->loaded_name = $source['media_name'];
			$this->date = $source['media_date'];
			$this->description = $source['media_description'];
			$this->dimensions = $source['media_dimensions'];
			$this->tags = $source['media_tags'];
			$this->type = $source['media_type'];
			$this->uploader_id = $source['media_uploader'];
			$this->status = $source['media_status'];
		}
	}


	/**
	 * Does not update the name.
	 * Therefore use "rename_file()" to keep the DB and actual file synchron.
	 */
	public function update_to_database() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_MEDIA . '`
			SET
				media_type = "' . mysql_real_escape_string( $this->type ) . '",
				media_description = "' . mysql_real_escape_string( $this->description ) . '",
				media_tags = "' . mysql_real_escape_string( $this->tags ) . '",
				media_uploader = ' . mysql_real_escape_string( $this->uploader_id ) . ',
				media_status = "' . mysql_real_escape_string( $this->status ) . '"
			WHERE media_id = ' . $this->id
		);
	}


	public function file_exists_inmedia() {
		return file_exists( $this->getFilepathRelative() );
	}


	public function used_in() {
		$relations = array();

		if( $this->count_file_used() > 0 ) {
			$rel_posts = ae_Database::Query( '
				SELECT
					that_id
				FROM `' . TABLE_RELATIONS . '`
				WHERE this_id = ' . $this->id . '
				AND relation_type = "file to post"
				ORDER BY that_id DESC
			' );

			$rel_pages = ae_Database::Query( '
				SELECT
					that_id
				FROM `' . TABLE_RELATIONS . '`
				WHERE this_id = ' . $this->id . '
				AND relation_type = "file to page"
				ORDER BY that_id DESC
			' );

			while( $p_id = mysql_fetch_assoc( $rel_posts ) ) {
				$sql = '
					SELECT
						post_title,
						post_permalink
					FROM `' . TABLE_POSTS . '`
					WHERE post_id = ' . $p_id['that_id'];

				$p_title = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

				$relations['post_' . $p_id['that_id']] = array(
					'type' => 'post',
					'id' => $p_id['that_id'],
					'title' => $p_title['post_title'],
					'permalink' => $p_title['post_permalink']
				);
			}
		}


		$out = '<ul>';

		foreach( $relations as $p ) {
			$out .= '<li>' . $p['title']
				. ' (<a href="http://' . ae_URL::Blog() . '/../' . $p['permalink'] . '">Open</a>)'
				. ' (<a href="?area=manage&amp;show=' . $p['type'] . 's&amp;edit=' . $p['id'] . '">Edit</a>)'
				. '</li>' . PHP_EOL;
		}

		return ( $out == '<ul>' ) ? '' : $out . '</ul>';
	}


	public function count_file_used() {
		$sql = '
			SELECT
				COUNT( this_id ) AS count
			FROM `' . TABLE_RELATIONS . '`
			WHERE this_id = ' . $this->id . '
			AND ( relation_type = "file to post" OR relation_type = "file to page" )
		';

		$f2p = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $f2p['count'];
	}


	/**
	 * Updates the status of the element to the database.
	 * However, if the new status is "trash" and the current status is
	 * already "trash" the file gets DELETED from database and file system!
	 */
	public function update_status( $status ) {
		if( !ae_Validate::isMediaStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'media status', $status ) );
		}

		if( $status == 'trash' && $this->status == 'trash' ) {
			$outcome = $this->delete();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_MEDIA . '`
				SET
					media_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE media_id = ' . $this->id
			);
		}

		return $outcome;
	}


	public function delete() {
		if( !$this->delete_db_entry() ) {
			return false;
		}
		if( !$this->delete_file() ) {
			return false;
		}
		if( $this->isImage() && !$this->delete_preview_image() ) {
			return false;
		}
		return true;
	}


	public function rename_file() {
		if( $this->name != $this->loaded_name ) {
			$outcome_rename = rename(
				'../../media/' . $this->getDateFilepath() . $this->loaded_name,
				'../../media/' . $this->getDateFilepath() . $this->name
			);

			if( $outcome_rename ) {
				$outcome_query = ae_Database::Query( '
					UPDATE `' . TABLE_MEDIA . '`
					SET
						media_name = "' . mysql_real_escape_string( $this->name ) . '"
					WHERE media_id = ' . $this->id
				);
			}
			else {
				return false;
			}

			if( $this->isImage() ) {
				$outcome_tiny = rename(
					'../../media/' . $this->getDateFilepath() . 'tiny/' . $this->getPreviewImageName( $this->loaded_name ),
					'../../media/' . $this->getDateFilepath() . 'tiny/' . $this->getPreviewImageName( $this->name )
				);

				return $outcome_tiny;
			}
		}

		return true;
	}



	//---------- Static functions


	public static function getMediaById( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				media_id,
				media_name,
				media_date,
				media_description,
				media_dimensions,
				media_tags,
				media_type,
				media_uploader,
				media_status
			FROM `' . TABLE_MEDIA . '`
			WHERE media_id = ' . mysql_real_escape_string( $id );

		$m = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $m ) ? null : new ae_Media( $m );
	}


	public static function FormatSize( $size = 0 ) {
		$ending = ' Byte';

		if( $size >= 1024 ) {
			$size /= 1024;
			$ending = ' KB';
		}

		if( $size >= 1024 ) {
			$size /= 1024;
			$ending = ' MB';
		}

		if( $size >= 1024 ) {
			$size /= 1024;
			$ending = ' GB';
		}

		$size = round( $size, 2 );
		return $size . $ending;
	}



	//---------- Protected functions


	/**
	 * Deletes the database entry of a media element.
	 * However, the status must already be set to "trash".
	 * Or in other words: Only "trash" is deletable.
	 */
	protected function delete_db_entry() {
		// Delete library entry
		$outcome_media = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_MEDIA . '`
			WHERE media_id = ' . $this->id . '
			AND media_status = "trash"
		' );

		if( !$outcome_media ) {
			return false;
		}

		// Delete relations
		$outcome_relation = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_RELATIONS . '`
			WHERE this_id = ' . $this->id . '
			AND (
				relation_type = "file to post"
				OR relation_type = "file to page"
			)
		' );

		return $outcome_relation;
	}


	protected function delete_file() {
		if( $this->file_exists_inmedia() ) {
			return unlink( $this->getFilepathRelative() );
		}
		return true;
	}


	protected function delete_preview_image() {
		$file = '../media/' . $this->getDateFilepath() . 'tiny/' . $this->getPreviewImageName();
		if( file_exists( $file ) ) {
			return unlink( $file );
		}
		return true;
	}



	//---------- Getter/Setter


	public function isImage() {
		$image_types = array( 'jpeg', 'gif', 'png' );
		$lowlevel = explode( '/', $this->type );
		if( count( $lowlevel ) < 2 ) {
			return false;
		}
		return in_array( $lowlevel[1], $image_types );
	}


	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
	}


	public function getName() {
		return $this->name;
	}

	public function setName( $name ) {
		$this->name = ae_FileUpload::CorrectFilename( $name );
	}


	public function getDate( $format = '' ) {
		if( empty( $format ) ) {
			return $this->date;
		}
		return date( $format, $this->getDateTimestamp() );
	}

	public function getDateFilepath() {
		$date = $this->getDateTimestamp();
		return date( 'Y/m/', $date );
	}

	public function getDateTimestamp() {
		return strtotime( $this->date );
	}

	public function setDate( $date ) {
		if( !ae_Validate::isDate_MySQL( $date ) ) {
			throw new Exception( ae_ErrorMessages::NotADate_MySQL() );
		}
		$this->date = $date;
	}


	public function getDescription() {
		return $this->description;
	}

	public function getDescriptionForTextarea() {
		return htmlspecialchars( $this->description );
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}


	public function getFilepathAbsolute() {
		return 'http://' . ae_URL::Blog() . '/../media/' . $this->getDateFilepath() . $this->getName();
	}

	public function getFilepathRelative() {
		return ae_URL::DirectoryUps() . 'media/' . $this->getDateFilepath() . $this->getName();
	}


	public function getFilesize() {
		if( !$this->file_exists_inmedia() ) {
			return 'error on getting filesize: file not found';
		}
		return self::FormatSize( filesize( $this->getFilepathRelative() ) );
	}


	public function getImageDimensions() {
		if( !$this->file_exists_inmedia() ) {
			return 'error on getting image dimensions: file not found';
		}
		// TODO: doubled code, @see ae_MediaFileQuery::image_dimensions().
		return str_replace( 'x', '&nbsp;×&nbsp;', $this->dimensions ) . '&nbsp;pixels';
	}


	public function getPreviewImageName( $name = '' ) {
		if( empty( $name ) && !$this->isImage() ) {
			return '';
		}

		$filename = empty( $name ) ? $this->name : $name;

		if( strrchr( $filename, '.' ) ) {
			$filename = substr( $filename, 0, strlen( strrchr( $filename, '.' ) ) * -1 );
		}

		$mime = str_replace( 'image/', '', $this->type );
		$mime = str_replace( 'jpeg', 'jpg', $mime );

		$filename .= '_tiny.' . $mime;

		return $filename;
	}


	public function getTags() {
		return $this->tags;
	}

	public function setTags( $tags,  $tags_js = array() ) {
		if( is_string( $tags ) && empty( $tags_js ) ) {
			$this->tags = $tags;
		}
		else if( is_array( $tags ) && empty( $tags_js ) ) {
			$this->tags = ae_Misc::ProcessTags2String( $tags );
		}
		else if( is_string( $tags ) && is_array( $tags_js ) ) {
			$this->tags = ae_Misc::ProcessTags2String( $tags, $tags_js );
		}
	}


	public function getType() {
		return $this->type;
	}

	public function setType( $type ) {
		if( !ae_Validate::isMediaType( $type ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'media type', $type ) );
		}
		$this->type = $type;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( !ae_Validate::isMediaStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'media status', $status ) );
		}
		$this->status = $status;
	}


	public function getUploaderId() {
		return $this->uploader_id;
	}

	public function getUploaderName() {
		$sql = '
			SELECT
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_id = ' . $this->uploader_id;

		$user = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $user['user_name'];
	}

	public function setUploaderId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->uploader_id = $id;
	}


}
