<?php


class ae_ContentOfPost {

	protected static $type = 'post';


	public static function Init( $type = 'post' ) {
		self::$type = $type;
	}


	public static function StatusFilterNav() {
		$filter = self::Filter();

		$class = !in_array( $filter['status'], ae_GlobalVars::getPostStatuses() ) ? ' class="active"' : '';
		$out = '<li' . $class . '><a href="?area=manage&amp;show=' . self::$type . 's">All</a></li>' . PHP_EOL;

		foreach( ae_GlobalVars::getPostStatuses() as $s ) {
			$class = ( $filter['status'] == $s ) ? ' class="active"' : '';

			if( ae_ManagePostQuery::CountPostsByStatus( $s, self::$type ) > 0 ) {
				$out .= '<li' . $class . '>';
				$out .= '<a href="?area=manage&amp;show=' . self::$type . 's&amp;status=' . urldecode( $s ) . '">';
				$out .= ucfirst( $s ) . ' (' . ae_ManagePostQuery::CountPostsByStatus( $s, self::$type ) . ')</a>';
				$out .= '</li>' . PHP_EOL;
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . PHP_EOL;
			}
		}

		return $out;
	}


	public static function Actions( ae_ManagePostQuery $mpq ) {
		$ran = '&amp;ran=' . rand( 1, 1000 );
		$base = '<a href="manage/apply.php?';
		$from = isset( $_GET['status'] ) ? '&amp;from=' . $mpq->post_status() : '';
		$from .= '&amp;show=' . ae_PageStructure::getShowContent();

		$out = '<a href="?area=manage&amp;show=' . self::$type . 's';
		$out .= '&amp;edit=' . $mpq->the_ID() . '" class="edit">Edit</a>' . PHP_EOL;

		if( $mpq->post_status() == 'draft' ) {
			$out .= $base . 'publish=' . $mpq->the_ID() . $from . $ran . '" class="publish">Publish</a>' . PHP_EOL;
		}
		else {
			$out .= $base . 'draft=' . $mpq->the_ID() . $from . $ran . '" class="draft">Mark as Draft</a>' . PHP_EOL;
		}

		$trash_or_delete = ( $mpq->post_status() == 'trash' ) ? 'Delete' : 'Trash';
		$out .= $base . 'trash=' . $mpq->the_ID() . $from . $ran . '" class="trash">' . $trash_or_delete . '</a>' . PHP_EOL;

		return $out;
	}


	public static function Classes( ae_ManagePostQuery $mpq ) {
		$exp = $mpq->post_has_expired() ? ' expired' : '';
		return 'post ' . $mpq->post_status() . $exp;
	}


	public static function Comments( ae_ManagePostQuery $mpq ) {
		$out = '';

		if( $mpq->comments_number() > 0 ) {
			$out .= '<a href="?area=manage&amp;show=comments&amp;to_' . self::$type . '=' . $mpq->the_ID() . '"'
					. 'title="' . $mpq->comments_number() . ' comment(s)">'
					. $mpq->comments_number() . '</a>';
		}
		else {
			$out .= '<span title="No comments so far">0</span>';
		}

		return $out;
	}


	/**
	 * @return Array with all per GET submitted filters.
	 */
	public static function Filter() {
		if( ae_RequestCache::hasKey( 'manage_filterfor' . self::$type . 's' ) ) {
			return ae_RequestCache::Load( 'manage_filterfor' . self::$type . 's' );
		}

		$filter = array(
			'status' => '',
			'date' => '',
			'date_from' => '',
			'date_till' => '',
			'author' => '',
			'with_media' => '',
			'contains' => '',
			'tag' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'status':
					if( ae_Validate::isPostStatus( $value ) ) {
						$filter['status'] = $value;
					}
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
				case 'author':
					if( ae_Validate::isDigit( $value ) ) {
						$filter['author'] = $value;
					}
					break;
				case 'with_media':
					if( ae_Validate::isDigit( $value ) ) {
						$filter['with_media'] = $value;
					}
					break;
				case 'contains':
					$filter['contains'] = $value;
					break;
				case 'tag':
					$filter['tag'] = $value;
					break;
			}
		}

		ae_RequestCache::Save( 'manage_filterfor' . self::$type . 's', $filter );
		return $filter;
	}


}
