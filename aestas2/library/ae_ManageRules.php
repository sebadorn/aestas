<?php


class ae_ManageRules {

	protected $rules;
	protected $rule;
	protected $count_rules;
	protected $limit;
	protected $page;
	protected $dumped_rules;

	protected $filter_string;


	public function __construct( $filter, $limit = 16 ) {
		$this->limit = ae_Validate::isDigit( $limit ) ? $limit : 16;
		$this->page = ( PAGE < 0 ) ? 0 : PAGE;
		$this->filter_string = self::BuildFilterstring( $filter );
		$this->rules = array();

		$sql = '
			SELECT
				rule_id,
				rule_concern,
				rule_match,
				rule_precision,
				rule_result,
				rule_status
			FROM `' . TABLE_RULES . '`
			' . $this->filter_string . '
			ORDER BY
				rule_id DESC
			LIMIT ' . ( $this->limit * $this->page ) . ', ' . $this->limit;

		$this->rules = ae_Database::Assoc( $sql );

		$this->count_rules = $this->count_rules();
		$this->dumped_rules = 0;
	}


	public function count_rules() {
		if( !empty( $this->count_rules ) ) {
			return $this->count_rules;
		}

		$sql = '
			SELECT
				COUNT( rule_id ) AS count
			FROM `' . TABLE_RULES . '`
			' . $this->filter_string;

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $total['count'];
	}


	public function id() {
		return $this->rule['rule_id'];
	}


	public function rule_precision() {
		return $this->rule['rule_precision'];
	}


	public function rule_concern() {
		return htmlspecialchars( $this->rule['rule_concern'] );
	}


	public function rule_match() {
		return htmlspecialchars( $this->rule['rule_match'] );
	}


	public function rule_result() {
		return str_replace( ';', ' ', $this->rule['rule_result'] );
	}


	public function have_rules() {
		if( count( $this->rules ) <= 0 ) {
			unset( $this->rules );
			return false;
		}
		return true;
	}


	/**
	 * Gets all the data of a rule.
	 */
	public function the_rule() {
		$this->rule = $this->rules[$this->dumped_rules];
		unset( $this->rules[$this->dumped_rules] );
		$this->dumped_rules++;
	}


	public function status() {
		return $this->rule['rule_status'];
	}



	//---------- Static functions


	protected static function BuildFilterstring( $filter ) {
		$out = '';

		if( !empty( $filter['status'] ) ) {
			$out .= ' AND rule_status = "' . mysql_real_escape_string( $filter['status'] ) . '" ';
		}
		if( !empty( $filter['rule_concern'] ) ) {
			$out .= ' AND rule_concern = "' . mysql_real_escape_string( $filter['rule_concern'] ) . '" ';
		}
		if( !empty( $filter['precision'] ) ) {
			$out .= ' AND rule_precision = "' . mysql_real_escape_string( $filter['precision'] ) . '" ';
		}
		if( !empty( $filter['date'] ) ) {
			$out .= ' AND rule_added LIKE "' . mysql_real_escape_string( $filter['date'] ) . ' __:__:__" ';
		}
		if( !empty( $filter['date_from'] ) ) {
			$out .= ' AND rule_added >= "' . mysql_real_escape_string( $filter['date_from'] ) . '" ';
		}
		if( !empty( $filter['date_till'] ) ) {
			$out .= ' AND rule_added <= "' . mysql_real_escape_string( $filter['date_till'] ) . '" ';
		}

		if( !empty( $out ) ) {
			$out = ' WHERE ' . substr( $out, 4 );
		}
		else {
			$out = ' WHERE rule_status = "active" ';
		}
		return $out;
	}


	public static function CountRulesByStatus( $status = '' ) {
		if( !empty( $status ) ) {
			$sql = '
				SELECT
					COUNT( rule_id ) AS count
				FROM `' . TABLE_RULES . '`
				WHERE rule_status = "' . mysql_real_escape_string( $status ) . '"
			';
		}
		else {
			$sql = '
				SELECT
					COUNT( rule_id ) AS count
				FROM `' . TABLE_RULES . '`
				WHERE rule_status = "active"
			';
		}

		$c = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $c['count'];
	}



	//---------- Getter/Setter


	public function getLimit() {
		return $this->limit;
	}


}