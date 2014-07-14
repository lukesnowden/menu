<?php namespace Purposemedia\Menu;

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

	public static $containers = array();

	public static function container( $container = 'default' )
	{
		if ( ! isset( static::$containers[$container] ) )
		{
			static::$containers[$container] = new MenuContainer( $container );
		}
		return static::$containers[$container];
	}

	public function __call( $method, $parameters )
	{
		if( preg_match( "#^set([a-z0-9]+)Type$#is", $method, $match ) )
		{
			$method = 'setMenuType';
			if( count( $parameters ) == 2 )
			{
				$parameters[] = false;
			}
			$parameters = array_merge( $parameters, array( 'menu' => \camel_case( $match[1] ) ) );
		}
		return call_user_func_array( array( static::container(), $method ), $parameters );
	}

}

