<?php


class ae_VisitorStats {


	/**
	 * Number of visitors for the past $number_of_months months shown as bar diagram.
	 */
	public static function MonthlyBars( $year, $number_of_months = 12 ) {
		$out = '<tbody>' . PHP_EOL;
		$months = self::PastMonths( $year, $number_of_months );
		$hundred_percent = self::FindHundredPercent( $months );

		$out .= '<tr class="bars">' . PHP_EOL;
		foreach( $months as $month => $visitors ) {
			$height = floor( $visitors / $hundred_percent * 100 );
			$out .= '<td><span class="bar" style="height: ' . $height . 'px;">';
			$out .= '<span>' . number_format( $visitors ) . '</span>';
			$out .= '</span></td>' . PHP_EOL;
		}
		$out .= '</tr>' . PHP_EOL;

		$out .= '<tr class="months">' . PHP_EOL;
		foreach( $months as $month => $visitors ) {
			$out .= '<td>' . substr( ae_Formatter::MonthNumberToName( $month ), 0, 3 ) . '</td>';
		}
		$out .= '</tr>' . PHP_EOL;

		return $out . '</tbody>' . PHP_EOL;
	}


	/**
	 * Number of visitors for the given month in the current year.
	 */
	public static function TotalOfMonth( $month, $year = 0 ) {
		$year = empty( $year ) ? date( 'Y' ) : $year;
		$month = ae_Formatter::MonthToString( $month );

		$sql = '
			SELECT IFNULL( SUM( stat_value ), 0 ) AS total
			FROM `' . TABLE_STATS . '`
			WHERE stat_name LIKE "' . $year . '-' . $month . '-__"
		';

		$result = ae_Database::Assoc( $sql, ae_Database::SINGLE_RESULT );

		return $result['total'];
	}


	/**
	 * Navigational links for MonthlyBars.
	 */
	public static function VisitorStatsBrowseLinks() {
		$out = '';
		$qstring = preg_replace( '!&?visstats=[0-9]*!', '', $_SERVER['QUERY_STRING'] );
		$current_page = isset( $_GET['visstats'] ) ? $_GET['visstats'] : date( 'Y' );
		$next = $current_page + 1;
		$prev = $current_page - 1;
		$out .= '<a class="visits-browse" href="junction.php?' . $qstring . '&amp;visstats=' . $prev . '">&lt;</a>';
		if( $next <= date( 'Y' ) ) {
			$out .= '<a class="visits-browse" href="junction.php?' . $qstring . '&amp;visstats=' . $next . '">&gt;</a>';
		}
		return $out;
	}



	//---------- Protected functions


	protected static function PastMonths( $base_year, $number_of_months = 12 ) {
		$months = array();
		$month = ( $base_year < date( 'Y' ) ) ? 12 : date( 'm' );

		for( $i = $number_of_months - 1; $i >= 0; $i-- ) {
			$loop_month = $month - $i;
			if( $loop_month < 1 ) {
				$loop_month += 12;
				$year = $base_year - 1;
			}
			else {
				$year = $base_year;
			}
			$months[$loop_month] = self::TotalOfMonth( $loop_month, $year );
		}

		return $months;
	}


	protected static function FindHundredPercent( $stats, $step = 1000 ) {
		$max = 0;
		foreach( $stats as $month => $visitors ) {
			$max = ( $visitors > $max ) ? $visitors : $max;
		}
		$test = 0;
		while( true ) {
			if( $test - $max > 0 ) {
				return $test;
			}
			$test += $step;
		}
	}


}
