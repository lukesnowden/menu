<?php namespace LukeSnowden\Menu;

use LukeSnowden\Menu\Helpers\Stringy;

/*
|--------------------------------------------------------------------------
| Menu
|--------------------------------------------------------------------------
|
| @author Luke Snowden
| @description Resolves all static methods from "Menu" calls and passes
| the call onto the PmassetsContainer class.
|
*/

class Menu
{

	/**
	 * [$containers description]
	 * @var array
	 */

	public static $containers = array();

	/**
	 * [container description]
	 * @param  string $container [description]
	 * @return [type]            [description]
	 */

	public static function container( $container = 'default' )
	{
		if ( ! isset( static::$containers[$container] ) )
		{
			static::$containers[$container] = new MenuContainer( $container );
		}
		return static::$containers[$container];
	}

	/**
	 * [__call description]
	 * @param  [type] $method     [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */

	public function __call( $method, $parameters )
	{
		if( preg_match( "#^set([a-z0-9]+)Type$#is", $method, $match ) )
		{
			$method = 'setMenuType';
			if( count( $parameters ) == 2 )
			{
				$parameters[] = false;
			}
			$parameters = array_merge( $parameters, array( 'menu' => Stringy::camel_case( $match[1] ) ) );
		}
		return call_user_func_array( array( static::container(), $method ), $parameters );
	}

	/**
	 * [__callStatic description]
	 * @return [type] [description]
	 */

	public static function __callStatic( $name, $arguments ) {
		return call_user_func_array( array( static::container(), $name ), $arguments );
	}

}

