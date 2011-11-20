<?php


class ae_ContentOfRules {


	public static function StatusFilterNav() {
		$filter = self::FilterForRules();

		$class = !in_array( $filter['status'], ae_GlobalVars::getRuleStatuses() ) ? ' class="active"' : '';
		$out = '';
		//$out = '<li' . $class . '><a href="?area=set&amp;show=rules">All</a></li>' . PHP_EOL;

		foreach( ae_GlobalVars::getRuleStatuses() as $s ) {
			$class = ( $filter['status'] == $s ) ? ' class="active"' : '';

			if( ae_ManageRules::CountRulesByStatus( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=set&amp;show=rules&amp;status=' . urldecode( $s ) . '">'
						. ucfirst( $s ) . ' (' . ae_ManageRules::CountRulesByStatus( $s ) . ')</a></li>' . PHP_EOL;
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . PHP_EOL;
			}
		}

		return $out;
	}


	public static function Actions( ae_ManageRules $rules ) {
		$out = '';
		$base = '<a href="set/apply-on-rules.php?';
		$from = isset( $_GET['status'] ) ? '&amp;from=' . $rules->status() : '';
		$ran = '&amp;ran=' . rand( 1, 400 );

		if( $rules->status() == 'active' ) {
			$out .= $base . 'inactive=' . $rules->id() . $from . $ran . '">Deactivate</a>' . PHP_EOL;
		}
		else {
			$out .= $base . 'active=' . $rules->id() . $from . $ran . '">Activate</a>' . PHP_EOL;
		}
		$out .= $base . 'delete=' . $rules->id() . $from . $ran . '">Delete</a>' . PHP_EOL;

		return $out;
	}


	/**
	 * Returns an array with all per GET submitted filters.
	 */
	public static function FilterForRules() {
		if( ae_RequestCache::hasKey( 'manage_filterforrules' ) ) {
			return ae_RequestCache::Load( 'manage_filterforrules' );
		}

		$filter = array(
			'status' => 'active',
			'concern' => '',
			'precision' => '',
			'date' => '',
			'date_from' => '',
			'date_till' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'status':
					if( ae_Validate::isRuleStatus( $value ) ) {
						$filter['status'] = $value;
					}
					break;
				case 'concern':
					$filter['concern'] = $value;
					break;
				case 'precision':
					$filter['precision'] = $value;
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

		ae_RequestCache::Save( 'manage_filterforrules', $filter );
		return $filter;
	}


	public static function OptionsForRuleResults() {
		$out = '';
		$statuses = array( 'approved', 'unapproved', 'spam', 'trash', 'delete' );

		$out .= "\t" . '<optgroup label="comment status">' . PHP_EOL;
		foreach( $statuses as $status ) {
			$out .= "\t\t" . '<option value="comment;status;' . $status . '">';
			$out .= $status . '</option>' . PHP_EOL;
		}
		$out .= "\t" . '</optgroup>' . PHP_EOL;

		$user_query = ae_Database::Query( '
			SELECT
				user_id,
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_status != "deleted"
			AND user_status != "trash"
			ORDER BY
				user_name ASC,
				user_id ASC
		' );

		$out .= "\t" . '<optgroup label="comment of user">' . PHP_EOL;
		while( $user = mysql_fetch_assoc( $user_query ) ) {
			$out .= "\t\t" . '<option value="comment;user;' . $user['user_id'] . '">';
			$out .= $user['user_name'] . ' (ID: ' . $user['user_id'] . ')</option>' . PHP_EOL;
		}
		$out .= "\t" . '</optgroup>' . PHP_EOL;

		return $out;
	}


}
