<?php


class ae_ContentOfMedia {


	public static function TypeFilterMediaNav() {
		$filter = self::FilterForMedia();

		$class = !in_array( $filter['type'], ae_Media::$TYPES ) ? ' class="active"' : '';
		$out = '<li' . $class . '><a href="?area=media">All</a></li>' . PHP_EOL;

		foreach( ae_Media::$TYPES as $s ) {
			$class = ( $filter['type'] == $s ) ? ' class="active"' : '';

			if( ae_MediaFileQuery::CountFilesByType( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=media&amp;type=' . urldecode( $s ) . '">'
					. ucfirst( $s ) . ' (' . ae_MediaFileQuery::CountFilesByType( $s ) . ')</a></li>' . PHP_EOL;
			}
		}

		if( ae_MediaFileQuery::CountFilesByStatus( 'trash' ) > 0 ) {
			$class = ( $filter['status'] == 'trash' ) ? ' class="active"' : '';
			$out .= '<li' . $class . '>'
				. '<a href="?area=media&amp;status=trash">'
				. 'Trash (' . ae_MediaFileQuery::CountFilesByStatus( 'trash' ) . ')</a></li>' . PHP_EOL;
		}
		else {
			$out .= '<li><span>Trash (0)</span></li>' . PHP_EOL;
		}
		return $out;
	}


	/**
	 * @return Array with all per GET submitted filters.
	 */
	public static function FilterForMedia() {
		if( ae_RequestCache::hasKey( 'manage_filterformedia' ) ) {
			return ae_RequestCache::Load( 'manage_filterformedia' );
		}

		$filter = array(
			'status' => '',
			'type' => '',
			'tag' => '',
			'date' => '',
			'date_from' => '',
			'date_till' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'status':
					if( ae_Validate::isMediaStatus( $value ) ) {
						$filter['status'] = $value;
					}
					break;
				case 'type':
					if( ae_Validate::isMediaType( $value ) ) {
						$filter['type'] = $value;
					}
					break;
				case 'tag':
					$filter['tag'] = $value;
					break;
				case 'date':
					if( ae_Validate::isDate_MySQL( $value ) ) {
						$filter['date'] = $value;
					}
					break;
				case 'date_from':
					if( ae_Validate::isDate_MySQL( $value ) ) {
						$filter['date_from'] = $value;
					}
					break;
				case 'date_till':
					if( ae_Validate::isDate_MySQL( $value ) ) {
						$filter['date_till'] = $value;
					}
					break;
			}
		}

		ae_RequestCache::Save( 'manage_filterformedia', $filter );
		return $filter;
	}


	public static function MediaTitle( ae_MediaFileQuery $media ) {
		$out = '<a class="title" href="../media/' . $media->file_date( 'Y/m/' ) . $media->file_name() . '">';
		$out .= $media->file_name() . '</a>' . PHP_EOL;

		return $out;
	}


	public static function MediaPreviewImage( ae_MediaFileQuery $media ) {
		$class = $out = '';
		if( !file_exists( '../media/' . $media->file_date( 'Y/m/' ) . $media->file_name() ) ) {
			$class = ' notfound';
		}
		else if( $media->file_date( 'Y-m-d' ) == date( 'Y-m-d' ) ) {
			$class = ' today';
		}
		$image = preg_match( '/jpeg|gif|png/', $media->file_type() ) ? true : false;

		if( $image ) {
			if( $class == ' notfound' ) {
				$out .= '<div class="filetype notfound" title="The file could not be found"></div>' . PHP_EOL;
			}
			else {
				$preview_path = '../media/' . $media->file_date( 'Y/m' ) . '/tiny/' . $media->file_preview();
				if( !file_exists( $preview_path ) ) {
					$out .= '<div class="filetype ' . $media->file_type_toplevel() . '"></div>';
				}
				else {
					$out .= '<img alt="preview" src="' . $preview_path . '" />' . PHP_EOL;
				}
			}
		}
		else {
			$type = $media->file_type_toplevel();
			$out .= '<div class="filetype ' . $type .'"></div>' . PHP_EOL;
		}
		return $out;
	}


	public static function MediaActions( ae_MediaFileQuery $media ) {
		$ran = '&amp;ran=' . rand( 1, 1000 );
		$filter = self::FilterForMedia();

		$out = '<a class="edit" href="?area=media&amp;edit=' . $media->file_ID() . '" ';
		$out .= 'title="show info and edit">Edit</a>' . PHP_EOL;

		if( $filter['status'] == 'trash' ) {
			$out .= '<a href="media/apply.php?available=' . $media->file_ID();
			$out .= $ran . '" title="restore">Restore</a>' . PHP_EOL;
		}

		$trash_or_delete = ( $media->file_status() == 'trash' ) ? 'Delete' : 'Trash';
		$out .= '<a class="trash" href="media/apply.php?trash=' . $media->file_ID();
		$out .= $ran . '" title="delete">' . $trash_or_delete . '</a>' . PHP_EOL;

		return $out;
	}


	public static function MediaInfo( ae_MediaFileQuery $media ) {
		$out = '<li class="author">Uploaded by <strong>' . $media->file_uploader() . '</strong></li>' . PHP_EOL;

		$out .= '<li class="mime">' . $media->file_type() . '</li>' . PHP_EOL;

		if( strstr( $media->file_type(), 'image/' ) ) {
			$out .= '<li class="dimensions">' . $media->image_dimensions() . '</li>' . PHP_EOL;
		}

		$out .= '<li class="count">';
		if( $media->file_used_posts() == 0 ) {
			$out .= 'Found in <span>0</span> posts';
		}
		else {
			$out .= 'Found in <a href="?area=manage&amp;show=posts&amp;with_media=' . $media->file_ID() . '">'
				. $media->file_used_posts() . '</a>' . "\n";
			$out .= ( $media->file_used_posts() == 1 ) ? 'post' : 'posts';

			if( $media->file_used_pages() > 0 ) {
				$out .= 'and <span>' . $media->file_used_pages() . '</span>';
				$out .= ( $media->file_used_pages() == 1 ) ? 'page' : 'pages';
			}
		}
		$out .= '</li>' . PHP_EOL;

		return $out;
	}


}
