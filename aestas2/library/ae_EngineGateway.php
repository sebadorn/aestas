<?php


class ae_EngineGateway {

	protected static $p;
	protected static $p_id;
	protected static $c;


	public static function Init( $o ) {
		if( get_class( $o ) == 'ae_CommentQuery' ) {
			self::$c = $o;
			return;
		}
		if( get_class( $o ) == 'ae_PostQuery' ) {
			self::$p = $o;
			return;
		}
		throw new Exception(
			ae_ErrorMessages::TypeNotExpected(
				'class ae_PostQuery or ae_CommentQuery', get_class( $o )
			)
		);
	}


	public static function Call( $function ) {
		$num_args = func_num_args();
		switch( $num_args ) {
			case 1:
				return self::$p->{$function}();
			case 2:
				$arg1 = func_get_arg( 1 );
				return self::$p->{$function}( $arg1 );
			case 3:
				$arg1 = func_get_arg( 1 );
				$arg2 = func_get_arg( 2 );
				return self::$p->{$function}( $arg1, $arg2 );
			case 4:
				$arg1 = func_get_arg( 1 );
				$arg2 = func_get_arg( 2 );
				$arg3 = func_get_arg( 3 );
				return self::$p->{$function}( $arg1, $arg2, $arg3 );
			case 5:
				$arg1 = func_get_arg( 1 );
				$arg2 = func_get_arg( 2 );
				$arg3 = func_get_arg( 3 );
				$arg4 = func_get_arg( 4 );
				return self::$p->{$function}( $arg1, $arg2, $arg3, $arg4 );
		}
		throw new Exception(
			ErrorMessages::Unknown( 'function', $function . '() with ' . func_num_args() . ' parameters' )
		);
	}


	public static function CallForComments( $function ) {
		$current_p_id = self::Call( 'the_ID' );
		if( self::$c == null || self::$p_id != $current_p_id ) {
			self::$c = new ae_CommentQuery();
			self::$p_id = $current_p_id;
		}

		$num_args = func_num_args();
		switch( $num_args ) {
			case 1:
				return self::$c->{$function}();
			case 2:
				$arg1 = func_get_arg( 1 );
				return self::$c->{$function}( $arg1 );
			case 3:
				$arg1 = func_get_arg( 1 );
				$arg2 = func_get_arg( 2 );
				return self::$c->{$function}( $arg1, $arg2 );
			case 4:
				$arg1 = func_get_arg( 1 );
				$arg2 = func_get_arg( 2 );
				$arg3 = func_get_arg( 3 );
				return self::$c->{$function}( $arg1, $arg2, $arg3 );
		}
		throw new Exception(
			ErrorMessages::Unknown( 'function', $function . '() with ' . func_num_args() . ' parameters' )
		);
	}


}
