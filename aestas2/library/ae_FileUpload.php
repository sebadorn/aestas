<?php


class ae_FileUpload {


	protected $uploaded_file;
	protected $uploaded_name;
	protected $uploaded_type_lowlevel;

	protected $path;
	protected static $default_directory = 'media/'; // Default: "media/"
	protected static $default_preview_directory = 'tiny/'; // Default: "tiny/"
	protected $file_saved_to;

	protected $similar_names;

	protected static $preview_width = 80; // Pixel
	protected static $png_compression = 9; // 0: no compression - 9: max
	protected static $jpg_compression = 90; // 100: no compression - 0: pixel sludge

	/**
	 * 0: OK
	 * 1: could not create directory for year
	 * 2: could not create directory for month
	 * 3: could not move file
	 * 4: file saved, but could not set rights
	 * 5: could not make database entry for file
	 * 6: could not create directory for preview images
	 */
	protected $error = 0;
	protected static $error_message = array(
		0 => 'Everything went fine.',
		1 => 'Could not create directory for year. (mkdir)',
		2 => 'There is a directory for the year, but could not create directory for month. (mkdir)',
		3 => 'The target directory exists, but could not move file there. (move_uploaded_file)',
		4 => 'The file was saved to the directory, but could not set rights. (chmod)',
		5 => 'The file was saved to the directory, but could not make an entry in the database. (mysql_query)',
		6 => 'The file was saved to directory and got a database entry, but the directory for preview images could not be created. (mkdir)'
	);



	public function __construct() {
		$this->path = '../../' . self::$default_directory;
	}


	public function set_uploaded_file( $file ) {
		$this->uploaded_file = $file;

		if( $this->has_file_error() ) {
			return false;
		}

		$this->uploaded_name = self::CorrectFilename( $file['name'] );

		$new = explode( '.', $this->uploaded_name );
		$ext = $new[count( $new ) - 1];
		$new = array_slice( $new, 0, -1 );
		$name = implode( '.', $new );

		$count = 0;
		while( $this->does_name_already_exist( $this->uploaded_name ) ) {
			$count++;
			$this->uploaded_name = $name . '_' . self::zeros_for_name( $count ) . $count . '.' . $ext;
		}

		$type = explode( '/', $this->uploaded_file['type'] );
		$this->uploaded_type_lowlevel = $type[1];
	}


	/**
	 * Returns true if the array of the uploaded file contains an error code greater than 0,
	 * false otherwise.
	 */
	public function has_file_error() {
		if( $this->uploaded_file['error'] > 0 ) {
			return true;
		}
		return false;
	}


	/**
	 * Covers only the formats "jpeg", "gif" and "png".
	 */
	public function isImage() {
		$image_types = array( 'jpeg', 'gif', 'png' );
		return in_array( $this->uploaded_type_lowlevel, $image_types );
	}


	/**
	 * If not existing, creates the directory for the current year and month.
	 * Then saves the file to this directory and changes its rights.
	 */
	public function save_to_directory() {
		if( !$this->create_directory() ) {
			return false;
		}

		$move_successful = move_uploaded_file(
			$this->uploaded_file['tmp_name'],
			$this->path . date( 'Y/m/' ) . $this->uploaded_name
		);
		if( !$move_successful ) {
			$this->error = 3;
			return false;
		}

		$chmod_successful = chmod( $this->path . date( 'Y/m/' ) . $this->uploaded_name, 0644 );
		if( !$chmod_successful ) {
			$this->error = 4;
			return false;
		}

		$this->file_saved_to = $this->path . date( 'Y/m/' ) . $this->uploaded_name;
		return true;
	}


	/**
	 * Saves the information of the successfully moved file to the database.
	 */
	public function save_to_database() {
		if( $this->error == 0 || $this->error == 4 ) {
			$save2db_successful = ae_Database::Query( '
				INSERT INTO `' . TABLE_MEDIA . '` (
					media_date,
					media_name,
					media_type,
					media_uploader
				) VALUES (
					"' . date( 'Y-m-d H:i:s' ) . '",
					"' . mysql_real_escape_string( $this->uploaded_name ) . '",
					"' . mysql_real_escape_string( $this->uploaded_file['type'] ) . '",
					' . mysql_real_escape_string( ae_Permissions::getIdOfCurrentUser() ) . '
				)
			' );

			if( !$save2db_successful ) {
				$this->error = 5;
				return false;
			}
		}

		return true;
	}


	/**
	 * Creates a preview image of an uploaded image.
	 * Transparency in PNG or GIF images are kept.
	 */
	public function create_previewimage() {
		if( empty( $this->file_saved_to) || !$this->isImage() ) {
			return false;
		}

		// Image to work with
		$image = $this->source_from_image();

		if( empty( $image ) ) {
			return false;
		}

		// Calculate new size
		$width = imagesx( $image );
		$height = imagesy( $image );
		$ratio = $width / self::$preview_width;
		$fitting_height = floor( $height / $ratio );

		// Preview image. Now only blank, but in the right size.
		$tinyimage = $this->new_blank_image( $image, $fitting_height );

		// Resize original into tiny image
		imagecopyresampled(
			$tinyimage, $image,
			0, 0,									// destiny x and y
			0, 0,									// source x and y
			self::$preview_width, $fitting_height,	// destiny width and height
			$width, $height							// source width and height
		);

		imagedestroy( $image );

		if( !$this->create_preview_directory() ) {
			return false;
		}

		// Filename for the new file
		$filename = $this->create_filename_of_previewimage();

		$successful = $this->save_previewimage( $tinyimage, $filename );

		return $successful;
	}



	//--------- Static functions


	public static function CorrectFilename( $name ) {
		// Those names would pose a problem for Windows
		if( preg_match( '/^(com[1-9]|lpt[1-9]|con|nul|prn)$/', $name ) ) {
			$name = 'file';
		}

		$notthose = array( '?', '/', '\\', '|', ':', '<', '>', '"', '*' );
		$replacethis = array( ' ', 'ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü' );
		$withthis = array( '_', 'ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue' );

		$name = str_replace( $replacethis, $withthis, $name );
		$name = str_replace( $notthose, '', $name );

		return $name;
	}


	protected static function zeros_for_name( $count ) {
		$zeros = '000';
		if( $count >= 10 ) {
			$zeros = '00';
		}
		else if( $count >= 100 ) {
			$zeros = '0';
		}
		else if( $count >= 1000 ) {
			$zeros = '';
		}
		return $zeros;
	}



	//---------- Protected functions


	/**
	 * Checks the database if a file with the given name already has been
	 * uploaded in this year and month.
	 */
	protected function does_name_already_exist( $name ) {
		if( !empty( $this->similar_names) ) {
			return in_array( $name, $this->similar_names );
		}

		$name_wo_ext = explode( '.', $name );
		$name_wo_ext = array_slice( $name_wo_ext, 0, -1 );
		$name_wo_ext = implode( '.', $name_wo_ext );

		$sql = '
			SELECT
				media_name
			FROM `' . TABLE_MEDIA . '`
			WHERE media_date LIKE "' . date( 'Y-m' ) . '-__ __:__:__"
			AND (
				media_name LIKE "' . mysql_real_escape_string( $name_wo_ext ) . '\_%"
				OR media_name = "' . mysql_real_escape_string( $name ) . '"
			)
		';

		$this->similar_names = ae_Database::Assoc( $sql );

		return in_array( $name, $this->similar_names );
	}


	/**
	 * If they do not already exist, creates directories
	 * for the current year and month.
	 */
	protected function create_directory() {
		$mkdir_year_successful = $mkdir_month_successful = true;

		if( !file_exists( $this->path . date( 'Y' ) ) ) {
			$mkdir_year_successful = mkdir( $this->path . date( 'Y' ), 0755 );
		}
		if( !file_exists( $this->path . date( 'Y/m' ) ) ) {
			$mkdir_month_successful = mkdir( $this->path . date( 'Y/m' ), 0755 );
		}

		if( !$mkdir_year_successful ) {
			$this->error = 1;
			return false;
		}
		else if( !$mkdir_month_successful ) {
			$this->error = 2;
			return false;
		}

		return true;
	}


	/**
	 * If not alreay existing, creates a directory for preview
	 * images in the directory of the current month.
	 */
	protected function create_preview_directory() {
		$mkdir_successful = true;

		if( !file_exists( $this->path . date( 'Y/m' ) . '/' . self::$default_preview_directory ) ) {
			$mkdir_successful = mkdir( $this->path . date( 'Y/m' ) . '/' . self::$default_preview_directory, 0755 );
		}

		if( !$mkdir_successful ) {
			$this->error = 6;
			return false;
		}

		return true;
	}


	/**
	 * Creates a blank image in the right size for the preview image.
	 */
	protected function new_blank_image( $image, $fitting_height ) {
		// Create blank image
		$tinyimage = imagecreatetruecolor( self::$preview_width, $fitting_height );

		// PNG: Transparency for background
		if( $this->uploaded_type_lowlevel == 'png' ) {
			$color = imagecolorallocatealpha( $tinyimage, 0, 0, 0, 127 );
			imagefill( $tinyimage, 0, 0, $color );
		}

		// GIF: Transparency for background
		else if( $this->uploaded_type_lowlevel == 'gif' ) {
			$color = imagecolorallocatealpha( $tinyimage, 0, 0, 0, 127 );
			imagefill( $tinyimage, 0, 0, $color );
			imagecolortransparent( $tinyimage, $color );
		}

		return $tinyimage;
	}


	/**
	 * Creates an image source from the uploaded image
	 * for the to be created preview image.
	 */
	protected function source_from_image() {
		switch( $this->uploaded_type_lowlevel ) {
			case 'jpeg':
				$image = imagecreatefromjpeg( $this->file_saved_to );
				break;
			case 'png':
				$image = imagecreatefrompng( $this->file_saved_to );
				break;
			case 'gif':
				$image = imagecreatefromgif( $this->file_saved_to );
				break;
			default:
				$image = null;
		}

		return $image;
	}


	protected function create_filename_of_previewimage() {
		$filename = $this->uploaded_name;
		$thedot = strrchr( $filename, '.' );
		if( $thedot ) {
			$filename = substr( $filename, 0, strlen( $thedot ) * -1 );
		}

		$filename = $this->path . date( 'Y/m' ) . '/tiny/' . $filename . '_tiny';
		return $filename;
	}


	protected function save_previewimage( $tinyimage, $filepath_and_name ) {
		$successful = false;

		switch( $this->uploaded_type_lowlevel ) {
			case 'png':
				imagealphablending( $tinyimage, true );
				imagesavealpha( $tinyimage, true );
				$successful = imagepng(
					$tinyimage,
					$filepath_and_name . '.png',
					self::$png_compression
				);
				break;
			case 'gif':
				$successful = imagegif(
					$tinyimage,
					$filepath_and_name . '.gif'
				);
				break;
			default:
				$successful = imagejpeg(
					$tinyimage,
					$filepath_and_name . '.jpg',
					self::$jpg_compression
				);
		}

		return $successful;
	}



	//---------- Getter/Setter


	public function getErrorCode() {
		return $this->error;
	}

	public function getErrorMessage() {
		return self::$error_message[$this->error];
	}


}
