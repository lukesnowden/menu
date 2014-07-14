<?php namespace Purposemedia\Menu;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider 
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */

	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register()
	{
		$this->app['menu'] = $this->app->share( function( $app )
		{
			return new Menu;
		});
		$this->app['config']->package( "purposemedia/menu", dirname( __FILE__ ) . "/../../../config" );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */

	public function provides()
	{
		return array();
	}

}