<?php


class ae_Rules {

	protected static $rules = null;


	/**
	 * Checks for each element of a comment if it matches one or more rules.
	 * Returns an array with all found rule results (actions to take).
	 * If no rules have been found, returns an empty array.
	 */
	public static function Check( $name, $mail, $url, $message, $ip ) {
		if( is_null( self::$rules ) ) {
			self::Preload();
		}

		$rule_actions = array();

		foreach( self::$rules as $rule ) {
			switch( $rule['rule_concern'] ) {
				case 'comment_ip':
					$comparator = $ip;
					break;
				case 'comment_author':
					$comparator = $name;
					break;
				case 'comment_email':
					$comparator = $mail;
					break;
				case 'comment_url':
					$comparator = $url;
					break;
				case 'comment_content':
					$comparator = $message;
					break;
				default:
					continue;
			}

			if( $rule['rule_precision'] == 'exact' ) {
				if( $rule['rule_match'] == $comparator ) {
					$rule_actions[] = $rule['rule_result'];
				}
			}
			else if( $rule['rule_precision'] == 'contains' ) {
				if( stripos( $comparator, $rule['rule_match'] ) !== false ) {
					$rule_actions[] = $rule['rule_result'];
				}
			}
			else if( $rule['rule_precision'] == 'regex' ) {
				if( preg_match( $rule['rule_match'], $comparator ) ) {
					$rule_actions[] = $rule['rule_result'];
				}
			}
		}

		return $rule_actions;
	}



	//---------- protected functions


	/**
	 * Loads all rules from the database and stores them in an array.
	 */
	protected static function Preload() {
		self::$rules = ae_Database::Assoc( '
			SELECT
				rule_concern,
				rule_match,
				rule_precision,
				rule_result
			FROM `' . TABLE_RULES . '`
			WHERE rule_status = "active"
		' );
	}


}
