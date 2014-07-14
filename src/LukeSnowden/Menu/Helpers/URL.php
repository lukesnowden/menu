<?php namespace LukeSnowden\Menu\Helpers;

class URL {

	/**
	 * [current description]
	 * @return [type] [description]
	 */

	public static function current()
	{
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$pageURL .= "s";
		$pageURL .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" )
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		else
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		return $pageURL;
	}

	/**
	 * [domain description]
	 * @return [type] [description]
	 */

	public function domain()
	{
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
			$pageURL .= "s";
		$pageURL .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" )
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
		else
			$pageURL .= $_SERVER["SERVER_NAME"];
		return $pageURL;
	}

}