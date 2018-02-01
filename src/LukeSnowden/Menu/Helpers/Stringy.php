<?php namespace LukeSnowden\Menu\Helpers;

class Stringy {

	/**
	 * [camel_case description]
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */

	public static function camel_case( $str, $capitalise_first_char = false )
	{
		if( $capitalise_first_char ) {
			$str[0] = strtoupper( $str[0] );
		}
        $func = function($c) {
            return strtoupper($c[1]);
        };
		return preg_replace_callback( '/_|-([a-z])/', $func, $str );
	}

}