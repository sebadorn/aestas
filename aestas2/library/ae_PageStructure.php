<?php

/**
 * Building the HTML interface of the admin area.
 */
class ae_PageStructure {


	protected $path = '';


	/**
	 *
	 */
	public function __construct() {
		//
	}


	/**
	 *
	 * @param String $filepath
	 * @param Object $params
	 */
	public function render( $filepath, $params = null ) {
		include( $this->path . $filepath );
	}


	/**
	 *
	 * @param String $path
	 */
	public function set_path( $path ) {
		$path = trim( $path );
		if( $path != '' && substr( $path, -1 ) != '/' ) {
			$path .= '/';
		}
		$this->path = $path;
	}


}
