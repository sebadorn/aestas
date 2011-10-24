<?php


class ae_MediaFileQuery {

	protected $file;
	protected $count_files;
	protected $limit;
	protected $page;
	protected $dumped_files;

	protected $filter_string;


	public function __construct( $filter ) {
		$this->files = array();
		$this->limit = 14;
		$this->page = ( PAGE < 0 ) ? 0 : PAGE;
		$this->filter_string = self::BuildFilterstring( $filter );

		$sql = '
			SELECT
				media_id,
				media_date,
				media_name,
				media_description,
				media_tags,
				media_type,
				media_dimensions,
				media_uploader,
				media_status,
				(
					SELECT
						COUNT( this_id )
					FROM `' . TABLE_RELATIONS . '`
					WHERE this_id = media_id
					AND relation_type = "file to post"
				) AS in_posts,
				(
					SELECT
						COUNT( this_id )
					FROM `' . TABLE_RELATIONS . '`
					WHERE this_id = media_id
					AND relation_type = "file to page"
				) AS in_pages
			FROM `' . TABLE_MEDIA . '`
			' . $this->filter_string . '
			ORDER BY
				media_date DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit;

		$this->files = ae_Database::Assoc( $sql );

		$this->count_files = $this->count_files();
		$this->dumped_files = 0;
	}


	public function count_files() {
		if( !empty( $this->count_files ) ) {
			return $this->count_files;
		}

		$sql = '
			SELECT
				COUNT( media_id ) AS count
			FROM `' . TABLE_MEDIA . '`
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function have_files() {
		if( count( $this->files ) <= 0 ) {
			unset( $this->files );
			return false;
		}
		return true;
	}


	public function image_dimensions() {
		if( $this->file['media_dimensions'] == '' ) {
			return $this->update_image_dimensions();
		}
		else {
			return str_replace( 'x', '&nbsp;×&nbsp;', $this->file['media_dimensions'] ) . '&nbsp;pixels';
		}
	}


	public function update_image_dimensions() {
		$filepath = '../media/' . $this->file_date( 'Y/m' ) . '/' . $this->file_name();
		if( file_exists( $filepath ) ) {
			$dimensions = getimagesize( $filepath );

			ae_Database::Query( '
				UPDATE `' . TABLE_MEDIA . '`
				SET
					media_dimensions = "' . mysql_real_escape_string( $dimensions[0] . 'x' . $dimensions[1] ) . '"
				WHERE media_id = ' . $this->file['media_id']
			);

			$dimensions = $dimensions[0] . '&nbsp;×&nbsp;' . $dimensions[1] . '&nbsp;pixels'; // TODO: doubled code, @see image_dimensions().
		}
		else {
			$dimensions = '<em>error getting image size: file not found</em>';
		}

		return $dimensions;
	}


	public function the_file() {
		$this->file = $this->files[$this->dumped_files];
		unset( $this->files[$this->dumped_files] );
		$this->dumped_files++;
	}


	public function files_count_filter() {
		return $this->count_files;
	}



	//----------- Static functions


	protected static function BuildFilterstring( $filter ) {
		$out = '';

		if( !empty( $filter['status'] ) ) {
			$out .= ' AND media_status = "'
				. mysql_real_escape_string( $filter['status'] ) . '" ';
		}
		if( !empty( $filter['type'] ) ) {
			$out .= ' AND media_type LIKE "'
				. mysql_real_escape_string( $filter['type'] ) . '/%" ';
		}
		if( !empty( $filter['tag'] ) ) {
			$out .= ' AND media_tags REGEXP "^(.*;)*'
				. mysql_real_escape_string( $filter['tag'] ) . '(;.*)*$" ';
		}
		if( !empty( $filter['date'] ) ) {
			$out .= ' AND media_date LIKE "'
				. mysql_real_escape_string( $filter['date'] ) . ' __:__:__" ';
		}
		if( !empty( $filter['date_from'] ) ) {
			$out .= ' AND DATE_FORMAT( media_date, "%Y-%m-%d" ) >= "'
				. mysql_real_escape_string( $filter['date_from'] ) . '" ';
		}
		if( !empty( $filter['date_till'] ) ) {
			$out .= ' AND DATE_FORMAT( media_date, "%Y-%m-%d" ) <= "'
				. mysql_real_escape_string( $filter['date_till'] ) . '" ';
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 5 );
		}
		return $out;
	}


	public static function CountFilesByType( $type = '' ) {
		if( ae_RequestCache::hasKey( 'countfiles_bytype_' . $type ) ) {
			return ae_RequestCache::Load( 'countfiles_bytype_' . $type );
		}

		if( $type != 'trash' ) {
			$type_sql = empty( $type )
				? ' WHERE media_status = "available"'
				: ' WHERE media_type LIKE "' . mysql_real_escape_string( $type ) . '/%" AND media_status = "available"';

			$sql = '
				SELECT
					COUNT( media_id ) AS types
				FROM `' . TABLE_MEDIA . '`
				' . $type_sql;
		}
		else {
			$sql = '
				SELECT
					COUNT( media_id ) AS types
				FROM `' . TABLE_MEDIA . '`
				WHERE media_status = "trash"
			';
		}

		$count = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		ae_RequestCache::Save( 'countfiles_bytype_' . $type, $count['types'] );

		return $count['types'];
	}


	public static function CountFilesByStatus( $status = '' ) {
		if( !ae_Validate::isMediaStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'media status', $status ) );
		}

		if( !empty( $status ) ) {
			$sql = '
				SELECT
					COUNT( media_id ) AS statuses
				FROM `' . TABLE_MEDIA . '`
				WHERE media_status = "' . mysql_real_escape_string( $status ) . '"
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( media_id ) AS statuses
				FROM `' . TABLE_MEDIA . '`
				WHERE media_status != "trash"
			';
		}

		$count = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $count['statuses'];
	}



	//---------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


	public function file_date( $format = '' ) {
		if( !empty( $format ) ) {
			return date( $format, strtotime( $this->file['media_date'] ) );
		}
		return $this->file['media_date'];
	}


	public function file_description() {
		return nl2br( $this->file['media_description'] );
	}


	public function file_ID() {
		return $this->file['media_id'];
	}


	public function file_name() {
		return $this->file['media_name'];
	}


	/**
	 * @return File name of the tiny preview image
	 */
	public function file_preview() {
		$filename = $this->file_name();

		if( strrchr( $filename, '.' ) ) {
			$filename = substr( $filename, 0, strlen( strrchr( $filename, '.' ) ) * -1 );
		}

		$mime = str_replace( 'image/', '', $this->file_type() );
		$mime = str_replace( 'jpeg', 'jpg', $mime );

		$filename .= '_tiny.' . $mime;

		return $filename;
	}


	public function file_size() {
		$path = ae_URL::DirectoryUps() . 'media/' . $this->file_date( 'Y/m/' ) . $this->file['media_name'];
		$size = filesize( $path );
		return ae_Media::FormatSize( $size );
	}


	public function file_status() {
		return $this->file['media_status'];
	}


	/**
	 * @return Tags for file as links
	 */
	public function file_tags( $query_string, $status = '' ) {
		$out = '';

		if( $this->file['media_tags'] != '' && $this->file['media_tags'] != null ) {
			$tags = explode( ';', $this->file['media_tags'] );
			$uri = !empty( $status ) ? '?status=' . $status : '';
			$uri .= '&amp;tag=';

			foreach( $tags as $tag ) {
				if( empty( $tag ) ) {
					continue;
				}
				$out .= '<a href="' . $query_string . $uri . urlencode( $tag ) . '">' . $tag . '</a>, ';
			}

			$out = substr( $out, 0, -2 );
		}

		return $out;
	}


	public function file_type() {
		return $this->file['media_type'];
	}

	public function file_type_toplevel() {
		$type = explode( '/', $this->file['media_type'] );
		return $type[0];
	}


	public function file_uploader() {
		if( ae_RequestCache::hasKey( 'file_uploader_' . $this->file['media_uploader'] ) ) {
			return ae_RequestCache::Load( 'file_uploader_' . $this->file['media_uploader'] );
		}

		$sql = '
			SELECT
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_id = ' . $this->file['media_uploader'];

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		ae_RequestCache::Save( 'file_uploader_' . $this->file['media_uploader'], $u['user_name'] );

		return $u['user_name'];
	}


	public function file_used_posts() {
		return $this->file['in_posts'];
	}


	public function file_used_pages() {
		return $this->file['in_pages'];
	}


}
