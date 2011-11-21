<?php


class ae_Rule {


	// Class attributes
	public static $STATUSES = array( 'active', 'trash' );
	public static $PRECISIONS = array( 'contains', 'exact', 'regex' );

	protected static $default_status = 'trash';
	protected static $default_precision = 'exact';


	// Object attributes
	protected $id;
	protected $concern;
	protected $match;
	protected $precision;
	protected $result;
	protected $status;


	public function __construct( $source = null ) {
		if( is_array( $source ) ) {
			$this->id = $source['rule_id'];
			$this->type = $source['rule_type'];
			$this->concern = $source['rule_concern'];
			$this->match = $source['rule_match'];
			$this->precision = $source['rule_precision'];
			$this->result = $source['rule_result'];
			$this->status = $source['rule_status'];
		}
	}


	public function save_new() {
		if( $this->concern == '' || $this->match == '' ) {
			throw new Exception( 'Missing information' );
		}
		if( $this->precision == '' ) {
			$this->precision = self::$default_precision;
		}
		if( $this->status == '' ) {
			$this->status = self::$default_status;
		}

		return ae_Database::Query( '
			INSERT INTO `' . TABLE_RULES . '` (
				rule_added,
				rule_concern,
				rule_match,
				rule_precision,
				rule_result,
				rule_status
			) VALUES (
				"' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '",
				"' . mysql_real_escape_string( $this->concern ) . '",
				"' . mysql_real_escape_string( $this->match ) . '",
				"' . mysql_real_escape_string( $this->precision ) . '",
				"' . mysql_real_escape_string( $this->result ) . '",
				"' . mysql_real_escape_string( $this->status ) . '"
			)
		' );
	}


	/**
	 * Updates the entries in the database for this rule.
	 * It is not possible to delete an entry with this function.
	 * In order to delete have a look at the function update_status or delete.
	 */
	public function update_to_database() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_RULES . '`
			SET
				rule_concern = "' . mysql_real_escape_string( $this->concern ) . '",
				rule_match = "' . mysql_real_escape_string( $this->match ) . '",
				rule_precision = "' . mysql_real_escape_string( $this->precision ) . '",
				rule_result = "' . mysql_real_escape_string( $this->result ) . '",
				rule_status = "' . mysql_real_escape_string( $this->status ) . '"
			WHERE rule_id = "' . mysql_real_escape_string( $this->id ) . '"
		' );
	}


	/**
	 * Updates the status to the database.
	 * If the given value is "delete" the function delete will be called
	 * and, well, deletes the rule from the database.
	 */
	public function update_status( $status ) {
		if( !ae_Validate::isRuleStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'rule status', $status ) );
		}

		if( $status == 'trash' && $this->status == 'trash' ) {
			$this->delete();
		}
		else {
			$outcome = ae_Database::Query( '
				UPDATE `' . TABLE_RULES . '`
				SET
					rule_status = "' . mysql_real_escape_string( $status ) . '"
				WHERE rule_id = "' . mysql_real_escape_string( $this->id ) . '"
			' );
		}

		return $outcome;
	}


	/**
	 * Deletes this rule from the database.
	 */
	public function delete() {
		return ae_Database::Query( '
			DELETE
			FROM `' . TABLE_RULES . '`
			WHERE rule_id = "' . mysql_real_escape_string( $this->id ) . '"
		' );
	}



	//---------- Static functions


	public static function getRuleById( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}

		$sql = '
			SELECT
				rule_id,
				rule_concern,
				rule_match,
				rule_precision,
				rule_result,
				rule_status
			FROM `' . TABLE_RULES . '`
			WHERE rule_id = ' . mysql_real_escape_string( $id );

		$lid = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return new ae_Rule( $lid );
	}



	//---------- Getter/Setter


	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		if( !ae_Validate::isDigit( $id ) ) {
			throw new Exception( ae_ErrorMessages::NotAnId() );
		}
		$this->id = $id;
	}


	public function getRuleConcern() {
		return $this->concern;
	}

	public function setRuleConcern( $rule_concern ) {
		$this->concern = $rule_concern;
	}


	public function getRuleMatch() {
		return $this->match;
	}

	public function setRuleMatch( $rule_match ) {
		$this->match = $rule_match;
	}


	public function getRulePrecision() {
		return $this->precision;
	}

	public function setRulePrecision( $precision ) {
		if( !ae_Validate::isRulePrecision( $precision ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'rule precision', $precision ) );
		}
		$this->precision = $precision;
	}


	public function getRuleResult() {
		return $this->result;
	}

	public function setRuleResult( $result ) {
		$this->result = $result;
	}


	public function getStatus() {
		return $this->status;
	}

	public function setStatus( $status ) {
		if( !ae_Validate::isRuleStatus( $status ) ) {
			throw new Exception( ae_ErrorMessages::Unknown( 'rule status', $status ) );
		}
		$this->status = $status;
	}


}
