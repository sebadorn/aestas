<?php

/**
 * Search class. Doesn't do the actual search in the database.
 * Just takes the submitted values, processes them and passes them
 * on as GET parameters for the filter in manage.
 */
class ae_Search {


	protected $phrase;
	protected $gets = array(
		'date' => '', 'date_from' => '', 'date_till' => '',
		'status' => '',
		'to_post' => '', 'to_page' => '',
		'author' => '', 'email' => '', 'ip' => '',
		'url' => '',
		'role' => '',
		'name' => '', 'parent' => '',
		'with_media' => '',
		'tag' => ''
	);

	protected static $patterns = array(
		'date' => '[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}',
		'digits' => '[1-9][0-9]*',
		'some_string' => '[^\s]+',
		'name' => '".+"|[^\s]+',
		'tag' => '".+"|[^\s]+',
		'ip' => '[a-z0-9\.:]+'
	);


	public function __construct( $phrase ) {
		$this->phrase = $phrase;
	}


	public function process_phrase() {
		$this->look_for_date( $this->phrase );
		$this->look_for_digits( $this->phrase );
		$this->look_for_status( $this->phrase );
		$this->look_for_ip( $this->phrase );
		$this->look_for_author( $this->phrase );
		$this->look_for_email( $this->phrase );
		$this->look_for_url( $this->phrase );
		$this->look_for_role( $this->phrase );
		$this->look_for_tag( $this->phrase );
	}



	//---------- Protected functions


	protected function look_for_date( $phrase ) {
		if( preg_match( '/date (' . self::$patterns['date'] . ')/', $phrase, $hit ) ) {
			$this->gets['date'] = $hit[1];
		}

		if( preg_match( '/from (' . self::$patterns['date'] . ')/', $phrase, $hit ) ) {
			$this->gets['date_from'] = $hit[1];
			$this->gets['date'] = '';
		}

		if( preg_match( '/till (' . self::$patterns['date'] . ')/', $phrase, $hit ) ) {
			$this->gets['date_till'] = $hit[1];
			$this->gets['date'] = '';
		}
	}


	protected function look_for_digits( $phrase ) {
		if( preg_match( '/(post|page|parent) (' . self::$patterns['digits'] . ')/', $phrase, $hit ) ) {
			if( $hit[1] == 'post' || $hit[1] == 'page' ) {
				$hit[1] = 'to_' . $hit[1];
			}
			$this->gets[$hit[1]] = $hit[2];
		}

		if( preg_match( '/media (' . self::$patterns['digits'] . ')/', $phrase, $hit ) ) {
			$this->gets['with_media'] = $hit[1];
		}
	}


	protected function look_for_status( $phrase ) {
		if( preg_match( '/status (' . self::$patterns['some_string'] . ')/', $phrase, $hit ) ) {
			$this->gets['status'] = $hit[1];
		}
	}


	protected function look_for_ip( $phrase ) {
		if( preg_match( '/(ip )?(' . self::$patterns['ip'] . ')/', $phrase, $hit ) ) {
			if( ae_Validate::isIp( $hit[2] ) ) {
				$this->gets['ip'] = $hit[2];
			}
		}
	}


	protected function look_for_author( $phrase ) {
		if( preg_match( '/(author|name) (' . self::$patterns['name'] . ')/', $phrase, $hit ) ) {
			if( substr( $hit[2], 0, 1 ) == '"' ) {
				$hit[2] = substr( $hit[2], 1, -1 );
			}
			$this->gets[$hit[1]] = $hit[2];
		}
	}


	protected function look_for_email( $phrase ) {
		if( preg_match( '/email (' . self::$patterns['some_string'] . ')/', $phrase, $hit ) ) {
			$this->gets['email'] = $hit[1];
		}
	}


	protected function look_for_url( $phrase ) {
		if( preg_match( '/(url|website) (' . self::$patterns['some_string'] . ')/', $phrase, $hit ) ) {
			$this->gets['url'] = $hit[2];
		}
	}


	protected function look_for_role( $phrase ) {
		if( preg_match( '/role (' . self::$patterns['some_string'] . ')/', $phrase, $hit ) ) {
			$this->gets['role'] = $hit[1];
		}
	}


	protected function look_for_tag( $phrase ) {
		if( preg_match( '/tag (' . self::$patterns['tag'] . ')/', $phrase, $hit ) ) {
			if( substr( $hit[1], 0, 1 ) == '"' ) {
				$hit[1] = substr( $hit[1], 1, -1 );
			}
			$this->gets['tag'] = $hit[1];
		}
	}



	//---------- Getter/Setter


	public function get_params_as_url() {
		$out = '';
		foreach( $this->gets as $key => $value ) {
			if( !empty( $value ) ) {
				$out .= '&' . $key . '=' . urlencode( $value );
			}
		}
		if( empty( $out ) ) {
			$out = '&contains=' . urlencode( $this->phrase );
		}
		return $out;
	}


}
