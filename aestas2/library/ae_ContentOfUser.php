<?php


class ae_ContentOfUser {


	public static function UserActions( ae_ManageUserQuery $muq ) {
		$base = '<a href="manage/apply.php?';
		$ran = '&amp;ran=' . rand( 1, 1000 );
		$from = isset( $_GET['role'] ) ? '&amp;role=' . $_GET['role']: '';
		$from .= '&amp;show=' . ae_PageStructure::getShowContent();

		$out = '<a href="?area=manage&amp;show=users&amp;edit='
				. $muq->user_ID() . '" class="edit">Edit</a>' . PHP_EOL;

		if( ae_Permissions::getIdOfCurrentUser() != $muq->user_ID() ) {
			if( $muq->user_status() == 'suspended' ) {
				$out .= $base . 'active=' . $muq->user_ID() . $from
						. $ran . '" class="employ">Employ</a>' . PHP_EOL;
			}
			else {
				$out .= $base . 'suspended=' . $muq->user_ID() . $from
						. $ran . '" class="suspend">Suspend</a>' . PHP_EOL;
			}

			$trash_or_delete = ( $muq->user_status() == 'trash' ) ? 'Delete' : 'Trash';
			$out .= $base . 'trash=' . $muq->user_ID() . $from . $ran . '" class="trash">'
					. $trash_or_delete . '</a>' . PHP_EOL;
		}

		return $out;
	}


	public static function UserWrittenPosts( ae_ManageUserQuery $muq ) {
		if( $muq->user_count_posts() > 0 ) {
			$out = '<a href="?area=manage&amp;show=posts&amp;author=' . $muq->user_ID() . '">'
				. $muq->user_count_posts() . '</a>';
		}
		else {
			$out = '<span>0</span>';
		}

		return $out;
	}


	public static function UserWrittenPages( ae_ManageUserQuery $muq ) {
		if( $muq->user_count_pages() > 0 ) {
			$out = '<a href="?area=manage&amp;show=pages&amp;author=' . $muq->user_ID() . '">'
				. $muq->user_count_pages() . '</a>';
		}
		else {
			$out = '<span>0</span>';
		}

		return $out;
	}


	public static function UserUploadedFiles( ae_ManageUserQuery $muq ) {
		if( $muq->user_count_media() > 0 ) {
			$out = '<a href="?area=media&amp;show=library&amp;uploader=' . $muq->user_ID() . '">'
				. $muq->user_count_media() . '</a>';
		}
		else {
			$out = '<span>0</span>';
		}

		return $out;
	}


	public static function StatusFilterUserNav() {
		$filter = self::FilterForUsers();

		$class = !in_array( $filter['status'], ae_User::$STATUSES ) ? ' class="active"' : '';
		$out = '<li' . $class . '><a href="?area=manage&amp;show=users">All</a></li>' . "\n";

		foreach( ae_User::$STATUSES as $s ) {
			$class = ( $filter['status'] == $s ) ? ' class="active"' : '';

			if( ae_ManageUserQuery::count_users_bystatus( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=manage&amp;show=users&amp;status=' . urldecode( $s ) . '">'
					. ucfirst( $s ) . ' (' . ae_ManageUserQuery::count_users_bystatus( $s ) . ')</a></li>' . "\n";
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . "\n";
			}
		}

		return $out;
	}


	public static function RoleFilterUserNav() {
		$filter = self::FilterForUsers();

		$class = !in_array( $filter['role'], ae_User::$ROLES ) ? ' class="active"' : '';
		$out = '<li' . $class . '><a href="?area=manage&amp;show=users">All</a></li>' . "\n";

		foreach( ae_User::$ROLES as $s ) {
			$class = ( $filter['role'] == $s ) ? ' class="active"' : '';

			if( ae_ManageUserQuery::count_users_byrole( $s ) > 0 ) {
				$out .= '<li' . $class . '><a href="?area=manage&amp;show=users&amp;role=' . urldecode( $s ) . '">'
					. ucfirst( $s ) . ' (' . ae_ManageUserQuery::count_users_byrole( $s ) . ')</a></li>' . "\n";
			}
			else {
				$out .= '<li><span>' . ucfirst( $s ) . ' (0)</span></li>' . "\n";
			}
		}

		if( ae_ManageUserQuery::count_users_bystatus( 'trash' ) > 0 ) {
			$class = ( $filter['status'] == 'trash' ) ? ' class="active"' : '';
			$out .= '<li' . $class . '><a href="?area=manage&amp;show=users&amp;status=trash">'
				. 'Trash (' . ae_ManageUserQuery::count_users_bystatus( 'trash' ) . ')</a></li>' . "\n";
		}
		else {
			$out .= '<li><span>Trash (0)</span></li>' . "\n";
		}

		return $out;
	}


	public static function FilterForUsers() {
		if( ae_RequestCache::hasKey( 'manage_filterforusers' ) ) {
			return ae_RequestCache::Load( 'manage_filterforusers' );
		}

		$filter = array(
			'status' => '',
			'role' => '',
			'name' => '',
			'url' => '',
			'email' => ''
		);

		foreach( $_GET as $key => $value ) {
			$value = urldecode( $value );

			switch( $key ) {
				case 'role':
					if( ae_Validate::isUserRole( $value ) ) {
						$filter['role'] = $value;
					}
					break;
				case 'status':
					if( ae_Validate::isUserStatus( $value ) ) {
						$filter['status'] = $value;
					}
					break;
				case 'name':
					$filter['name'] = $value;
					break;
				case 'url':
					$filter['url'] = $value;
					break;
				case 'email':
					$filter['email'] = $value;
					break;
			}
		}

		ae_RequestCache::Save( 'manage_filterforusers', $filter );
		return $filter;
	}


}
