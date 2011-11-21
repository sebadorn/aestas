<?php


class ae_ContentOfCategory {


	/**
	 * Navigation to filter categories by their status.
	 */
	public static function StatusFilterCategoryNav() {
		$filter = self::FilterForCategories();

		$class = '';
		if( empty( $filter['status'] ) || !in_array( $filter['status'], ae_Category::$STATUSES ) ) {
			$class = ' class="active"';
		}
		$out = '<li' . $class . '><a href="?area=manage&amp;show=categories">All</a></li>' . PHP_EOL;

		foreach( ae_Category::$STATUSES as $s ) {
			if( empty( $s ) || $s == 'NULL' ) {
				continue;
			}
			$class = ( $filter['status'] == $s ) ? ' class="active"' : '';

			if( ae_ManageCategoryQuery::count_categories_bystatus( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=manage&amp;show=categories&amp;status=' . urldecode( $s ) . '">'
					. ucfirst( $s ) . ' (' . ae_ManageCategoryQuery::count_categories_bystatus( $s ) . ')</a></li>' . PHP_EOL;
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . PHP_EOL;
			}
		}

		return $out;
	}


	public static function CategoryActions( ae_ManageCategoryQuery $mcaq ) {
		$ran = '&amp;ran=' . rand( 1, 1000 );
		$from = isset( $_GET['status'] ) ? '&amp;status=' . $_GET['status'] : '';
		$from .= '&amp;show=' . ae_PageStructure::getShowContent();
	
		$out = '<a href="?area=manage&amp;show=categories&amp;edit=' . $mcaq->cat_ID() . '" class="edit">Edit</a>' . PHP_EOL;

		if( $mcaq->cat_ID() > 1 ) {
			if( $mcaq->cat_status() == 'trash' ) {
				$out .= ' <a href="manage/apply.php?active='
					. $mcaq->cat_ID() . $from . $ran . '" class="restore">Restore</a>' . PHP_EOL;
			}

			$trash_or_delete = ( $mcaq->cat_status() == 'trash' ) ? 'Delete' : 'Trash';
			$out .= ' <a href="manage/apply.php?trash='
				. $mcaq->cat_ID() . $from . $ran . '" class="trash">' . $trash_or_delete . '</a>' . PHP_EOL;
		}

		return $out;
	}


	public static function CategoryTitle( ae_ManageCategoryQuery $mcaq ) {
		$out = $mcaq->cat_name();
		return $out;
	}


	public static function CategoryInfo( ae_ManageCategoryQuery $mcaq ) {
		$out = '<li class="author">';
		$out .= ( $mcaq->cat_ID() > 1 ) ? $mcaq->cat_author() : 'Default for uncategorized posts';
		$out .= '</li>' . PHP_EOL;

		if( $mcaq->cat_ID() > 1 && $mcaq->cat_main() != '' ) {
			$out .= '<li class="main">Main: <span>' . $mcaq->cat_main() . '</span></li>' . PHP_EOL;
		}

		if( $mcaq->cat_ID() > 1 ) {
			$out .= '<li class="sub"><span>' . $mcaq->cat_count_minions() . '</span> Sub-Categories</li>' . PHP_EOL;
		}

		$out .= '<li class="posts">Used for <span>' . $mcaq->cat_count_posts() . '</span> post(s)</li>' . PHP_EOL;

		return $out;
	}


	public static function FilterForCategories() {
		if( ae_RequestCache::hasKey( 'manage_filterforcats' ) ) {
			return ae_RequestCache::Load( 'manage_filterforcats' );
		}

		$filter = array(
			'status' => '',
			'name' => '',
			'parent' => '',
			'author' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'status':
					if( ae_Validate::isCategoryStatus( $value ) ) {
						$filter['status'] = $value;
					}
					break;
				case 'name':
					$filter['name'] = $value;
					break;
				case 'parent':
					if( ae_Validate::isDigit( $value ) ) {
						$filter['parent'] = $value;
					}
					break;
				case 'author':
					if( ae_Validate::isDigit( $value ) ) {
						$filter['author'] = $value;
					}
					break;
			}
		}

		ae_RequestCache::Save( 'manage_filterforcats', $filter );
		return $filter;
	}


}
