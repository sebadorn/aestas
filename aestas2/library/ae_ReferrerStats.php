<?php


class ae_ReferrerStats {

	protected static $year;
	protected static $month;
	protected static $limit = 20;
	protected static $shorten_urls_to = 100; // characters

	protected static $newest_referrer = array();
	protected static $top_referrer = array();
	protected static $newest_searchqueries = array();
	protected static $top_searchqueries = array();

	protected static $search_engines = array(
		'google' => 'q',
		'yahoo' => 'p',
		'bing' => 'q',
		'ask' => 'q',
		'web.de' => 'su',
		'altavista' => 'q',
		'lycos' => 'query',
		'fireball' => 'q'
	);


	public static function InitStats( $year = '', $month = '' ) {
		self::$year = empty( $year ) ? date( 'Y' ) : $year;
		self::$month = empty( $month ) ? date( 'm' ) : $month;
		if( self::$month < 10 && strpos( self::$month, '0') != 0 ) {
			self::$month = '0' . self::$month;
		}

		self::InitNewReferrer();
		self::InitTopReferrer();
		self::InitNewSearchKeys();
		self::InitTopSearchKeys();
	}


	public static function NewSearchesForTable() {
		$out = '';
		foreach( self::$newest_searchqueries as $ref ) {
			$url = parse_url( $ref['referrer'] );
			$host = empty( $url['host'] ) ? '' : $url['host'];
			$host = preg_replace( '!^www\.?!', '', $host );
			$host = ucfirst( $host );
			$out .= '
				<tr>
					<td class="link"><a href="' . htmlspecialchars( $ref['referrer'] ) . '">' . $host . '</a></td>
					<td class="keywords">' . htmlspecialchars( $ref['search'] ) . '</td>
					<td class="date">' . date( 'd.m.Y&\n\b\s\p;H:i', strtotime( $ref['date'] ) ) . '</td>
				</tr>';
		}
		return $out;
	}


	public static function TopSearchesForTable() {
		$out = '';
		foreach( self::$top_searchqueries as $ref ) {
			$out .= '
				<tr>
					<td class="keywords">' . htmlspecialchars( $ref['search'] ) . '</td>
					<td class="date">' . $ref['count'] . '</td>
				</tr>';
		}
		return $out;
	}


	public static function NewReferrerForTable() {
		$out = '';
		foreach( self::$newest_referrer as $ref ) {
			$shorturl = urldecode( $ref['referrer'] );
			$shorturl = preg_replace( '!^http://(www\.)?|/$!', '', $shorturl );
			if( strlen( $shorturl ) > self::$shorten_urls_to ) {
				$shorturl = substr( $shorturl, 0, self::$shorten_urls_to ) . '…';
			}
			$shorturl = htmlspecialchars( $shorturl );
			$shorturl = ucfirst( $shorturl );
			$out .= '
				<tr>
					<td class="link"><a href="' . htmlspecialchars( $ref['referrer'] ) . '">' . $shorturl . '</a></td>
					<td class="date">' . date( 'd.m.Y&\n\b\s\p;H:i', strtotime( $ref['date'] ) ) . '</td>
				</tr>';
		}
		return $out;
	}


	public static function TopReferrerForTable() {
		$out = '';
		foreach( self::$top_referrer as $ref ) {
			$shorturl = urldecode( $ref['referrer'] );
			$shorturl = preg_replace( '!^http://(www\.)?|/$!', '', $shorturl );
			if( strlen( $shorturl ) > self::$shorten_urls_to ) {
				$shorturl = substr( $shorturl, 0, self::$shorten_urls_to ) . '…';
			}
			$shorturl = htmlspecialchars( $shorturl );
			$shorturl = ucfirst( $shorturl );
			$out .= '
				<tr>
					<td class="link"><a href="' . htmlspecialchars( $ref['referrer'] ) . '">' . $shorturl . '</a></td>
					<td class="date">' . $ref['count'] . '</td>
				</tr>';
		}
		return $out;
	}



	//---------- Protected functions


	protected static function InitNewReferrer() {
		$query_newrefs = ae_Database::Query( '
			SELECT
				referrer_entry,
				http_referrer
			FROM `' . TABLE_REFERRER . '`
			WHERE referrer_entry LIKE "' . self::$year . '-' . self::$month . '-__ __:__:__"
			AND search_request = ""
			AND http_referrer NOT LIKE "%google.%/imgres?imgurl=%"
			ORDER BY referrer_entry DESC
			LIMIT ' . self::$limit
		);

		while( $ref = mysql_fetch_assoc( $query_newrefs ) ) {
			$url = parse_url( $ref['http_referrer'] );
			$host = empty( $url['host'] ) ? '' : preg_replace( '/^www./', '', $url['host'] );
			$host = ucfirst( $host );

			$new_ref = array(
				'date' => $ref['referrer_entry'],
				'host' => $host,
				'referrer' => $ref['http_referrer']
			);
			self::$newest_referrer[] = $new_ref;
		}
	}


	protected static function InitTopReferrer() {
		$query_toprefs = ae_Database::Query( '
			SELECT
				http_referrer,
				COUNT( http_referrer ) AS count
			FROM `' . TABLE_REFERRER . '`
			WHERE referrer_entry LIKE "' . self::$year . '-' . self::$month . '-__ __:__:__"
			AND search_request = ""
			AND http_referrer NOT LIKE "%google.%/imgres?imgurl=%"
			GROUP BY http_referrer
			ORDER BY
				count DESC,
				referrer_entry DESC
			LIMIT ' . self::$limit
		);

		while( $ref = mysql_fetch_assoc( $query_toprefs ) ) {
			$url = parse_url( $ref['http_referrer'] );
			$host = empty( $url['host'] ) ? '' : preg_replace( '/^www./', '', $url['host'] );
			$host = ucfirst( $host );

			$top_ref = array(
				'referrer' => $ref['http_referrer'],
				'host' => $host,
				'count' => $ref['count']
			);
			self::$top_referrer[] = $top_ref;
		}
	}


	protected static function InitNewSearchKeys() {
		$query_newsearches = ae_Database::Query( '
			SELECT
				referrer_entry,
				http_referrer,
				search_request
			FROM `' . TABLE_REFERRER . '`
			WHERE referrer_entry LIKE "' . self::$year . '-' . self::$month . '-__ __:__:__"
			AND search_request != ""
			ORDER BY referrer_entry DESC
			LIMIT ' . self::$limit
		);

		while( $ref = mysql_fetch_assoc( $query_newsearches ) ) {
			$new_search = array(
				'search' => $ref['search_request'],
				'referrer' => $ref['http_referrer'],
				'date' => $ref['referrer_entry']
			);
			self::$newest_searchqueries[] = $new_search;
		}
	}


	/**
	 *
	 */
	protected static function InitTopSearchKeys() {
		$query_topsearches = ae_Database::Query( '
			SELECT
				search_request,
				COUNT( search_request ) AS count
			FROM `' . TABLE_REFERRER . '`
			WHERE referrer_entry LIKE "' . self::$year . '-' . self::$month . '-__ __:__:__"
			AND search_request != ""
			GROUP BY search_request
			ORDER BY
				count DESC,
				referrer_entry DESC
			LIMIT ' . self::$limit
		);

		while( $ref = mysql_fetch_assoc( $query_topsearches ) ) {
			$top_search = array(
				'search' => $ref['search_request'],
				'count' => $ref['count']
			);
			self::$top_searchqueries[] = $top_search;
		}
	}


}
