<?php


class ae_ManageActions {

	protected static $status = null;
	protected static $can_expire = null;
	protected static $id;


	public static function InitStatusAndId( $array, $type ) {
		if( $type == 'comments' ) {
			self::InitForComment( $array );
		}
		else if( $type == 'posts' || $type == 'pages' ) {
			self::InitForPostOrPage( $array );
		}
		else if( $type == 'users' ) {
			self::InitForUser( $array );
		}
		else if( $type == 'categories' ) {
			self::InitForCategory( $array );
		}
		else if( $type == 'media' ) {
			self::InitForMedia( $array );
		}
		else if( $type == 'rules' ) {
			self::InitForRules( $array );
		}
		else {
			throw new Exception( ae_ErrorMessages::Unknown( 'type', $type ) );
		}
	}



	//---------- Protected functions


	protected static function InitForComment( $array ) {
		if( isset( $array['approve'] ) && ae_Validate::isDigit( $array['approve'] ) ) {
			self::$status = 'approved';
			self::$id = $array['approve'];
		}
		else if( isset( $array['unapprove'] ) && ae_Validate::isDigit( $array['unapprove'] ) ) {
			self::$status = 'unapproved';
			self::$id = $array['unapprove'];
		}
		else if( isset( $array['spam'] ) && ae_Validate::isDigit( $array['spam'] ) ) {
			self::$status = 'spam';
			self::$id = $array['spam'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}


	protected static function InitForPostOrPage( $array ) {
		if( isset( $array['draft'] ) && ae_Validate::isDigit( $array['draft'] ) ) {
			self::$status = 'draft';
			self::$id = $array['draft'];
		}
		else if( isset( $array['publish'] ) && ae_Validate::isDigit( $array['publish'] ) ) {
			self::$status = 'published';
			self::$id = $array['publish'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}


	protected static function InitForUser( $array ) {
		if( isset( $array['active'] ) && ae_Validate::isDigit( $array['active'] ) ) {
			self::$status = 'active';
			self::$id = $array['active'];
		}
		else if( isset( $array['suspended'] ) && ae_Validate::isDigit( $array['suspended'] ) ) {
			self::$status = 'suspended';
			self::$id = $array['suspended'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}


	protected static function InitForCategory( $array ) {
		if( isset( $array['active'] ) && ae_Validate::isDigit( $array['active'] ) ) {
			self::$status = 'active';
			self::$id = $array['active'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}


	protected static function InitForMedia( $array ) {
		if( isset( $array['available'] ) && ae_Validate::isDigit( $array['available'] ) ) {
			self::$status = 'available';
			self::$id = $array['available'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}


	protected static function InitForRules( $array ) {
		if( isset( $array['active'] ) && ae_Validate::isDigit( $array['active'] ) ) {
			self::$status = 'active';
			self::$id = $array['active'];
		}
		else if( isset( $array['trash'] ) && ae_Validate::isDigit( $array['trash'] ) ) {
			self::$status = 'trash';
			self::$id = $array['trash'];
		}
		else if( isset( $array['delete'] ) && ae_Validate::isDigit( $array['delete'] ) ) {
			self::$status = 'delete';
			self::$id = $array['delete'];
		}
		else {
			throw new Exception( ae_ErrorMessages::ProblemWithStatusOrId() );
		}
	}



	//---------- Getter


	public static function getId() {
		return self::$id;
	}

	public static function getIp() {
		return self::$id;
	}

	public static function getStatus() {
		return self::$status;
	}

	public static function getIpExpires() {
		return self::$can_expire;
	}


}
