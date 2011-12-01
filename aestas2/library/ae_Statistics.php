<?php


class ae_Statistics {

	protected static $preloaded = false;
	protected static $statistics;

	protected static $ipLifetime = 6; // in hours
	protected static $updateIpEarliest = 2; // in hours
	protected static $counterLifetime = 3; // in years
	protected static $referrerLifetime = 100; // in days
	protected static $searchEngines = array(
		'google' => 'q',
		'yahoo' => 'p',
		'bing' => 'q',
		'ask' => 'q',
		'web.de' => 'su',
		'altavista' => 'q',
		'lycos' => 'query',
		'fireball' => 'q'
	);


	/**
	 * Preloads some most likely wanted statistics
	 */
	public static function PreloadStatistics() {
		self::$preloaded = true;
		if( empty( self::$statistics ) ) {
			$retrieve = ae_Database::Query( '
				SELECT
					stat_name,
					stat_value
				FROM `' . TABLE_STATS . '`
				WHERE stat_name = "' . mysql_real_escape_string( date( 'Y-m-d' ) ) . '"
				OR stat_name = "all"
			' );

			while( $stat = mysql_fetch_object( $retrieve ) ) {
				self::$statistics[$stat->stat_name] = $stat->stat_value;
			}
		}
	}


	/**
	 * Creates und updates daily counters. Identifies visitors by their IP address.
	 */
	public static function CountVisitors() {
		// Don't count logged-in users
		ae_Permissions::InitRoleAndStatus();

		if( ae_Permissions::isLoggedIn() ) {
			return false;
		}

		// Make visitor counting more unique
		if( self::CheckAgent() || self::CheckBlacklist() || self::CheckIp() ) {
			return false;
		}

		if( !self::CounterForTodayExists() ) {
			self::CounterForTodayCreate();
		}
		else {
			self::CounterForTodayAndTotalInc();
		}

		self::DeleteOldCounters();
	}


	/**
	 * Analyses the referrer of the visitor.
	 */
	public static function Referrer() {
		if( empty( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		$url = parse_url( $_SERVER['HTTP_REFERER'] );

		// If it is the current domain ignore the referrer
		if( str_replace( 'www.', '', $_SERVER['SERVER_NAME'] ) == str_replace( 'www.', '', $url['host'] ) ) {
			return false;
		}

		$search_request = '';
		$search_engine = self::LookForSearchEngine( $url['host'] );

		if( !empty( $url['query']) && !empty( $search_engine ) ) {
			$search_key = self::$searchEngines[$search_engine];
			parse_str( $url['query'], $query );

			if( isset( $query[$search_key] ) ) {
				$search_request = $query[$search_key];
			}
		}

		self::DeleteOldReferrers();

		self::AddReferrer( $search_request );
	}


	/**
	 * Returns the number of visitors for a certain day.
	 * @param $date string Date you want the counter of. For example: '2010-08-14'. Defaults to today.
	 */
	public static function getVisitorsDate( $date = '' ) {
		if( !ae_Validate::isDate_MySQL( $date ) ) {
			if( isset( self::$statistics[date( 'Y-m-d' )] ) ) {
				return self::$statistics[date( 'Y-m-d' )];
			}

			$sql = '
				SELECT
					stat_value
				FROM `' . TABLE_STATS . '`
				WHERE stat_name = "' . mysql_real_escape_string( date( 'Y-m-d' ) ) . '"
			';

			$today = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			$return = empty( $today ) ? 0 : $today['stat_value'];

			return self::$statistics[date( 'Y-m-d' )] = $return;
		}

		else {
			if( isset( self::$statistics[$date] ) ) {
				return self::$statistics[$date];
			}

			$sql = '
				SELECT
					stat_value
				FROM `' . TABLE_STATS . '`
				WHERE stat_name = "' . mysql_real_escape_string( $date ) . '"
			';

			$visits = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

			$return = empty( $visits ) ? 0 : $visits['stat_value'];

			return self::$statistics[$date] = $return;
		}
	}


	/**
	 * Returns the number of visitors since starting to count.
	 */
	public static function getVisitorsTotal() {
		if( isset( self::$statistics['all'] ) ) {
			return self::$statistics['all'];
		}

		$sql = '
			SELECT
				stat_value
			FROM `' . TABLE_STATS . '`
			WHERE stat_name = "all"
		';

		$total = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return self::$statistics['all'] = $total['stat_value'];
	}


	public static function HoneypotInc() {
		return ae_Database::Query( '
			UPDATE `' . TABLE_STATS . '`
			SET
				stat_value = stat_value + 1
			WHERE stat_name = "honeypot"
		' );
	}


	//---------- Protected functions


	/**
	 * Validates the user agent against a list of agents that
	 * shall be ignored. Returns true if the user agent is in the list.
	 */
	protected static function CheckAgent( $agent = '' ) {
		if( empty( $agent ) && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		$ignore = ae_Settings::IgnoreAgents();

		foreach( $ignore as $ignoreagent ) {
			if( stripos( $agent, $ignoreagent ) !== false ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Checks if the IP address is on the blacklist.
	 */
	protected static function CheckBlacklist( $ip = 0 ) {
		if( !ae_Validate::isIp( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$check = ae_Rules::Check( null, null, null, null, $ip );

		if( in_array( 'comment;status;spam', $check ) ) {
			return true;
		}
		return false;
	}


	/**
	 * Checks if the IP address has already been counted.
	 */
	protected static function CheckIp( $ip = 0 ) {
		if( $ip == 0 || !ae_Validate::isIp( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// IP address already in list of recent visitors.
		$last_visit = self::LastVisit( $ip );
		if( $last_visit != false ) {
			if( time() - strtotime( $last_visit ) >= self::$updateIpEarliest * 3600 ) {
				self::UpdateIp( $ip );
			}
			return true;
		}

		self::DeleteOldIps();

		// Else: It's a new one! Add IP address.
		self::AddIp( $ip );

		return false;
	}


	/**
	 * Checks if an IP address is already in the list of recent visitors.
	 * Returns the date of the last visit if true, returns boolean false otherwise.
	 */
	protected static function LastVisit( $ip ) {
		$date = mysql_real_escape_string( date( 'Y-m-d H:i:s' ) );

		$sql = '
			SELECT
				last_visit
			FROM `' . TABLE_IPS . '`
			WHERE ip = "' . mysql_real_escape_string( $ip ) . '"
			AND DATE_ADD( last_visit, INTERVAL ' . self::$ipLifetime . ' HOUR ) > "' . $date . '"
		';

		$ips = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return empty( $ips ) ? false : $ips['last_visit'];
	}


	/**
	 * Adds an IP address to the list of recent visitors.
	 */
	protected static function AddIp( $ip ) {
		ae_Database::Query( '
			INSERT INTO `' . TABLE_IPS . '` ( ip, last_visit )
			VALUES (
				"' . mysql_real_escape_string( $ip ) . '",
				"' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
			)
		' );

		if( isset( self::$statistics[date( 'Y-m-d' )] ) ) {
			self::$statistics[date( 'Y-m-d' )]++;
		}
		if( isset( self::$statistics['all'] ) ) {
			self::$statistics['all']++;
		}
	}


	/**
	 * Updates the time of the last vist of an IP address to the current timestamp.
	 */
	protected static function UpdateIp( $ip ) {
		ae_Database::Query( '
			UPDATE `' . TABLE_IPS . '`
			SET last_visit = "' . mysql_real_escape_string( date( 'Y-m-d H:i:s' ) ) . '"
			WHERE ip = "' . mysql_real_escape_string( $ip ) . '"
		' );
	}


	/**
	 * Deletes old IP addresses from the list.
	 */
	protected static function DeleteOldIps() {
		$date = mysql_real_escape_string( date( 'Y-m-d H:i:s' ) );

		ae_Database::Query( '
			DELETE FROM `' . TABLE_IPS . '`
			WHERE DATE_ADD( last_visit, INTERVAL ' . self::$ipLifetime . ' HOUR ) <= "' . $date . '"
		' );
	}


	/**
	 * Checks if there is already a counter for today.
	 */
	protected static function CounterForTodayExists() {
		if( self::$preloaded ) {
			return isset( self::$statistics[date( 'Y-m-d' )] );
		}

		$sql = '
			SELECT
				COUNT( * ) AS count
			FROM `' . TABLE_STATS . '`
			WHERE stat_name = "' . mysql_real_escape_string( date( 'Y-m-d' ) ) . '"
		';

		$exists = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return ( $exists['count'] > 0 );
	}


	/**
	 * Creates a counter for today and initialises it with 1.
	 */
	protected static function CounterForTodayCreate() {
		ae_Database::Query( '
			INSERT INTO `' . TABLE_STATS . '`
				( stat_name, stat_value )
			VALUES
				( "' . mysql_real_escape_string( date( 'Y-m-d' ) ) . '", 1 )
		' );
	}


	/**
	 * Increases the counter for today by one.
	 */
	protected static function CounterForTodayAndTotalInc() {
		ae_Database::Query( '
			UPDATE `' . TABLE_STATS . '`
			SET
				stat_value = stat_value + 1
			WHERE stat_name = "' . mysql_real_escape_string( date( 'Y-m-d' ) ) . '"
			OR stat_name = "all"
		' );
	}


	/**
	 * Deletes old counters.
	 */
	protected static function DeleteOldCounters() {
		ae_Database::Query( '
			DELETE FROM `'.TABLE_STATS.'`
			WHERE stat_name LIKE "' . mysql_real_escape_string( ( date( 'Y' ) - self::$counterLifetime ) ) . '-__-__"
		' );
	}


	/**
	 * Looks for some known search engines in the host part of the URL.
	 */
	protected static function LookForSearchEngine( $host ) {
		$pattern = '/(';
		foreach( self::$searchEngines as $name => $get_param ) {
			$pattern .= str_replace( '.', '\.', $name ) . '|';
		}
		$pattern = substr( $pattern, 0, -1 ) . ')/';

		preg_match( $pattern, $host, $search_engine );
		if( count( $search_engine ) > 0 ) {
			return $search_engine[1];
		}

		return '';
	}


	/**
	 * Adds a referrer. But the same not more than once per minute.
	 */
	protected static function AddReferrer( $search_request ) {
		// No duplicate entries in the same minute
		$sql = '
			SELECT
				COUNT( referrer_entry ) AS hits
			FROM `' . TABLE_REFERRER . '`
			WHERE referrer_entry LIKE "' . mysql_real_escape_string( date( 'Y-m-d H:i:' ) ) . '__"
			AND http_referrer = "' . mysql_real_escape_string( $_SERVER['HTTP_REFERER'] ) . '"
		';

		$ref = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		if( $ref['hits'] == 0 ) {
			// Add new referrer
			ae_Database::Query( '
				INSERT INTO `' . TABLE_REFERRER . '` (
					http_referrer,
					search_request
				) VALUES (
					"' . mysql_real_escape_string( $_SERVER['HTTP_REFERER'] ) . '",
					"' . mysql_real_escape_string( $search_request ) . '"
				)
			' );
		}
	}


	/**
	 * Deletes old referrer entries.
	 */
	protected static function DeleteOldReferrers() {
		$date = mysql_real_escape_string( date( 'Y-m-d H:i:s' ) );

		ae_Database::Query( '
			DELETE FROM `' . TABLE_REFERRER . '`
			WHERE DATE_ADD( referrer_entry, INTERVAL ' . self::$referrerLifetime . ' DAY ) <= "' . $date . '"
		' );
	}


}
