<?php


class ae_Category {

	protected $id;
	protected $name = '';
	protected $parent = 0;
	protected $permalink = '';
	protected $author_id = 0;
	protected $status;


	public function __construct( $source = '' ) {
		if( is_array( $source ) ) {
			$this->id = $source['cat_id'];
			$this->name = $source['cat_name'];
			$this->parent = $source['cat_parent'];
			$this->permalink = $source['cat_permalink'];
			$this->author_id = $source['cat_author_id'];
			$this->status = $source['cat_status'];
		}
	}


	/**
	 * Saves the new category to the database.
	 * If the $force_id flag is set to false (default), the database
	 * will generate the ID. It is not recommended to force an ID.
	 */
	public function save_new() {
		if( $this->name == '' ) {
			throw new Exception( 'Missing information.' );
		}

		if( $this->author_id < 1 ) {
			$this->author_id = ae_Permissions::getIdOfCurrentUser();
		}
		if( !ae_Validate::isCategoryStatus( $this->status ) ) {
			$this->status = 'active';
		}

		return ae_Database::Query( '
			INSERT INTO `' . TABLE_CATEGORIES . '` (
				cat_author_id,
				cat_name,
				cat_parent,
				cat_status
			) VALUES (
				' . mysql_real_escape_string( $this->author_id ) . ',
				"' . mysql_real_escape_string( $this->name ) . '",
				' . mysql_real_escape_string( $this->getParentForMySQL() ) . ',
				"' . mysql_real_escape_string( $this->status ) . '"
			)
		' );
	}


	/**
	 * Generates a permalink for this object.
	 * However, the new permalink is not yet saved to the database.
	 * Returns the new permalink.
	 */
	public function generate_permalink( $permalink_string = '' ) {
		$permalink_string = trim( $permalink_string );
		if( empty( $this->id ) || ( empty( $this->name ) && empty( $permalink_string ) ) ) {
			throw new Exception( ae_ErrorMessages::CouldNotGeneratePermalink( 'category' ) );
		}

		$name = empty( $permalink_string ) ? $this->name : $permalink_string;
		$suggested_permalink = ae_URL::Category2Permalink( $this->id, $name );
		while( self::ExistsPermalink( $suggested_permalink, $this->id ) ) {
			$suggested_permalink .= date( '-YmdHis' );
		}
		return $this->permalink = $suggested_permalink;
	}

	
	/**
	 * Saves the current permalink of the object to the database.
	 */
	public function update_permalink() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_CATEGORIES . '`
			SET
				cat_permalink = "' . mysql_real_escape_string( $this->permalink ) . '"
			WHERE cat_id = ' . $this->id
		);
	}


	public function update_to_database() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_CATEGORIES . '`
			SET
				cat_name = "' . mysql_real_escape_string( $this->name ) . '",
				cat_permalink = "' . mysql_real_escape_string( $this->permalink ) . '",
				cat_parent = ' . mysql_real_escape_string( $this->getParentForMySQL() ) . ',
				cat_author_id = ' . $this->author_id . ',
				cat_status = "' . mysql_real_escape_string( $this->status ) . '"
			WHERE cat_id = ' . $this->id
		);
	}


	public function count_minions() {
		$sql = '
			SELECT
				COUNT( cat_id ) AS count
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_parent = ' . $this->id;

		$m = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $m['count'];
	}


	public function count_posts() {
		$sql = '
			SELECT
				COUNT( this_id ) AS count
			FROM `' . TABLE_RELATIONS . '`
			WHERE that_id = ' . $this->id . '
			AND relation_type = "post to cat"
		';

		$p = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $p['count'];
	}


	public function update_status( $status ) {
		if( !ae_Validate::isCategoryStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'category status', $status ) );
		}

		if( $status == 'trash' && $this->getStatus() == 'trash' ) {
			$outcome = $this->delete();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_CATEGORIES . '`
				SET
					cat_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE cat_id = ' . mysql_real_escape_string( $this->id )
			);
		}

		return $outcome;
	}


	public function delete() {
		if( $this->id == 1 ) {
			return false;
		}

		$outcome_cat = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_id = ' . $this->id . '
			AND cat_status = "trash"
		' );

		if( !$outcome_cat ) {
			return false;
		}

		$outcome_rel = ae_Database::Query( '
			DELETE
			FROM `' . TABLE_RELATIONS . '`
			WHERE that_id = ' . $this->id
		);

		return $outcome_rel;
	}



	//---------- Static functions


	/**
	 * Returns an instance of Category or null,
	 * if no category exists for the given ID.
	 */
	public static function getCategoryById( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				cat_id,
				cat_name,
				cat_permalink,
				cat_parent,
				cat_author_id,
				cat_status
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_id = ' . mysql_real_escape_string( $id );

		$c = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $c ) ? null : new ae_Category( $c );
	}


	public static function ExistsPermalink( $permalink, $not_id = 0 ) {
		return ae_URL::ExistsPermalink( $permalink, 'cat', $not_id );
	}


	/**
	 * Merges two or more categories to one.
	 * The main category which takes the other(s) in, is the first in the array.
	 */
	public static function Merge( $array ) {
		if( count( $array ) < 2 ) {
			return false;
		}

		$main_id = array_shift( $array );
		if( !ae_Validate::isDigit( $main_id ) ) {
			return false;
		}

		foreach( $array as $sub_id ) {
			if( !ae_Validate::isDigit( $sub_id ) ) {
				continue;
			}

			// Change category ID to the new main ID,
			// if the post already is not already in this category.
			$outcome_update = ae_Database::Query( '
				UPDATE `' . TABLE_RELATIONS . '` r1
				SET
					r1.that_id = ' . $main_id . '
				WHERE r1.relation_type = "post to cat"
				AND r1.that_id = ' . $sub_id . '
				AND (
					SELECT
						COUNT( r2.this_id )
					FROM `' . TABLE_RELATIONS . '` r2
					WHERE r2.that_id = ' . $sub_id . '
				) = 0
			' );

			// Delete all relations of this to be gone category.
			// Oh, and of course delete the category.
			if( $outcome_update ) {
				ae_Database::Query( '
					DELETE
					FROM `' . TABLE_RELATIONS . '`
					WHERE that_id = ' . $sub_id . '
					AND relation_type = "post to cat"
				' );

				ae_Database::Query( '
					DELETE
					FROM `' . TABLE_CATEGORIES . '`
					WHERE cat_ID = ' . $sub_id
				);
			}
		}

		return true;
	}



	//---------- Getter/Setter


	public function getAuthorId() {
		return $this->author_id;
	}

	public function getAuthorName() {
		$sql = '
			SELECT
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_id = ' . $this->author_id;

		$u = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $u ) ? 'user not found' : $u['user_name'];
	}

	public function setAuthorId( $id ) {
		if( !ae_Validate::isDigit( $id )) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->author_id = $id;
	}


	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		if( !ae_Validate::isDigit( $id )) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->id = $id;
	}


	public function getName() {
		return $this->name;
	}

	public function getNameHtml() {
		return htmlspecialchars( $this->name );
	}

	public function setName( $name ) {
		$this->name = $name;
	}


	public function getParent() {
		return $this->parent;
	}

	public function getParentForMySQL() {
		if( $this->parent == 0 ) {
			return 'NULL';
		}
		return $this->parent;
	}

	public function setParent( $id ) {
		if( !ae_Validate::isDigit( $id )) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->parent = $id;
	}


	public function getPermalink() {
		return $this->permalink;
	}

	public function setPermalink( $permalink ) {
		$this->permalink = $permalink;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( !ae_Validate::isCategoryStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'category status', $status ) );
		}

		$this->status = $status;
	}


}
