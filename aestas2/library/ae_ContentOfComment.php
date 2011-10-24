<?php


class ae_ContentOfComment {


	public static function CommentAuthor( ae_ManageCommentQuery $mcq ) {
		$out = '<li class="author"><span>' . $mcq->comment_author() . '</span>';

		if( empty( $filter['author'] ) ) {
				$out .= '<a class="filter" href="?area=manage&amp;show=comments&amp;author='
					. urlencode( $mcq->comment_author() ) . '" title="filter by author">f</a>' . PHP_EOL;
		}
		$out .= '</li>' . PHP_EOL;

		$out .= '<li class="date"><span title="' . $mcq->comment_date( 'Y-m-d H:i:s' ) . '">'
			. $mcq->comment_date( 'jS M, Y' ) . '</span></li>' . PHP_EOL;

		if( ae_Permissions::hasPermissionToTakeActionsForComment( $mcq ) ) {
			$out .= '<li class="ip">IP: ' . PHP_EOL . $mcq->comment_author_IP();
			if( empty( $filter['ip'] ) ) {
				$out .= '<a class="filter" href="?area=manage&amp;show=comments&amp;ip='
					. $mcq->comment_author_IP() . '" title="filter by IP address">f</a>' . PHP_EOL;
			}
			$out .= '</li>';

			if( $mcq->comment_author_email() != '' ) {
				$out .= '<li class="email">Mail: ' . $mcq->comment_author_email_link();
				if( empty( $filter['email'] ) ) {
					$out .= '<a class="filter" href="?area=manage&amp;show=comments&amp;email='
						. $mcq->comment_author_email() . '" title="filter by eMail address">f</a>' . PHP_EOL;
				}
				$out .= '</li>' . PHP_EOL;
			}
		}

		if( $mcq->comment_author_url() != '' ) {
			$out .= '<li class="website">URL: ' . $mcq->comment_author_url_link();
			if( empty( $filter['url'] ) ) {
				$out .= '<a class="filter" href="?area=manage&amp;show=comments&amp;url='
					. urlencode( $mcq->comment_author_url() ) . '" title="filter by URL">f</a>' . PHP_EOL;
			}
			$out .= '</li>' . PHP_EOL;
		}

		return $out;
	}


	/**
	 * Links to possible actions to take for comment.
	 */
	public static function CommentActions( ae_CommentQuery $mcq ) {
		// Random number submitted as GET to prevent some browsers showing a cached version.
		$ran = '&amp;ran=' . rand( 1, 1000 );
		$base = '<a href="manage/apply.php?';
		$from = isset( $_GET['status'] ) ? '&amp;from=' . $mcq->comment_status() : '';
		$from .= '&amp;show=' . ae_PageStructure::getShowContent();

		$out = '<a href="?area=manage&amp;show=comments&amp;edit='
			. $mcq->comment_ID() . $from . $ran . '" class="edit">Edit</a>' . PHP_EOL;

		if( $mcq->comment_status() != 'trash' ) {
			$out .= ' <a href="?area=manage&amp;show=comments&amp;reply='
				. $mcq->comment_ID() . $from .$ran . '" class="reply">Reply</a>' . PHP_EOL;
		}

		$action = ( $mcq->comment_status() == 'approved' ) ? 'unapprove' : 'approve';
		$out .= $base . $action . '=' . $mcq->comment_ID() . $from. $ran . '" class="' . $action . '">'
			. ucfirst( $action ) . '</a>' . PHP_EOL;

		if( $mcq->comment_status() != 'spam' ) {
			$out .= $base . 'spam=' . $mcq->comment_ID() . $from . $ran . '" class="spam">Spam</a>' . PHP_EOL;
		}

		$trash_or_delete = ( $mcq->comment_status() == 'trash' ) ? 'Delete' : 'Trash';
		$out .= $base . 'trash=' . $mcq->comment_ID() . $from . $ran . '" class="trash">'
			. $trash_or_delete . '</a>';

		return $out;
	}


	/**
	 * Navigation to filter comments by their status.
	 */
	public static function StatusFilterCommentNav() {
		$filter = self::FilterForComments();

		$class = !in_array( $filter['status'], ae_GlobalVars::getCommentStatuses() ) ? ' class="active"' : '';
		$out = '<li' . $class . '><a href="?area=manage&amp;show=comments">All</a></li>' . PHP_EOL;

		foreach( ae_GlobalVars::getCommentStatuses() as $s ) {
			$class = ( $filter['status'] == $s ) ? ' class="active"' : '';

			if( ae_ManageCommentByPostQuery::count_comments_bystatus( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=manage&amp;show=comments&amp;status=' . urldecode( $s ) . '">'
					. ucfirst( $s ) . ' (' . ae_ManageCommentByPostQuery::count_comments_bystatus( $s ) . ')</a></li>' . PHP_EOL;
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . PHP_EOL;
			}
		}

		return $out;
	}


	/**
	 * @return Array with all per GET submitted filters.
	 */
	public static function FilterForComments() {
		if( ae_RequestCache::hasKey( 'manage_filterforcomments' ) ) {
			return ae_RequestCache::Load( 'manage_filterforcomments' );
		}

		$filter = array(
			'status' => '',
			'to_post' => '',
			'to_page' => '',
			'date' => '',
			'date_from' => '',
			'date_till' => '',
			'author' => '',
			'email' => '',
			'ip' => '',
			'url' => '',
			'contains' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'status':
					if( ae_Validate::isCommentStatus( $value ) ) {
						$filter['status'] = $value;
					}
					break;
				case 'to_post':
					if( ae_Post::ExistsId( $value ) ) {
						$filter['to_post'] = $value;
					}
					break;
				case 'to_page':
					if( ae_Page::ExistsId( $value ) ) {
						$filter['to_page'] = $value;
					}
					break;
				case 'date':
					if(  ae_Validate::isDate_MySQL( $value ) ) {
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
				case 'name':
					$filter['author'] = $value;
					break;
				case 'email':
					$filter['email'] = $value;
					break;
				case 'ip':
					if( ae_Validate::isIp( $value ) ) {
						$filter['ip'] = $value;
					}
					break;
				case 'url':
					$filter['url'] = $value;
					break;
				case 'contains':
					$filter['contains'] = $value;
					break;
			}
		}

		ae_RequestCache::Save( 'manage_filterforcomments', $filter );
		return $filter;
	}


}
