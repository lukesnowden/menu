<?php

namespace LukeSnowden\Menu;

use LukeSnowden\Menu\Helpers\Stringy;
use Exception;

/*
|--------------------------------------------------------------------------
| Menu Container
|--------------------------------------------------------------------------
|
| @author Luke Snowden
| @description This IoC containers allows you to inject navigation items
| from anywhere before the render method is called. The output is a simple
| yet flexable navigation structure.
|
*/

class MenuContainer
{

    /**
     * @var string
     */
    private $stylesLocation = 'LukeSnowden\\Menu\\Styles';

    /**
     * @var array
     */
    private $navigations = [];

    /**
     * @var array
     */
    private $renders = [];

    /**
     * @var bool
     */
    private $entrust = false;

    /**
     * @var array
     */
    private $items = array();

    public function useEntrustGuard()
    {
        $this->entrust = true;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function addItem( $params = [] )
    {
        $defaults = array(
            'text' 			=> '',
            'URL' 			=> '#',
            'reference' 	=> 0,
            'parent' 		=> false,
            'weight' 		=> 1,
            'class' 		=> '',
            'children'		=> array(),
            'icon'			=> '',
            'attributes'	=> array(),
            'protected'		=> false
        );
        if( isset( $params['URL'] ) && preg_match( "#^route:(.*)$#is", $params['URL'], $match ) ) {
            if( isset( $params['protected'] ) && $params['protected'] ) {
                $params['protected'] = $match[1];
            }
            $params['URL'] = route( $match[1] );
        }
        $this->items[] = array_merge( $defaults, $params );
        return $this;
    }

    /**
     * @param $name
     */
    public function toMenu( $name )
    {
        $name = Stringy::camel_case( $name );
        if( ! isset( $this->navigations[$name] ) )
        {
            $this->navigations[$name] = new MenuContainerNavigation( $name );
        }
        $this->navigations[$name]->addItem( array_pop( $this->items ) );
    }

    /**
     * @param bool $name
     * @param array $attributes
     * @param string $node
     * @return bool|mixed
     */
    public function render( $name = false, $attributes = array(), $node = 'ul' )
    {
        if( isset( $this->renders[Stringy::camel_case($name)] ) )
        {
            return $this->renders[Stringy::camel_case($name)];
        }
        if( ! $name )
        {
            $this->navigations['pmDefaultMenu'] = new MenuContainerNavigation( 'pmDefaultMenu' );
            while( count( $this->items ) !== 0 )
            {
                $item = array_shift( $this->items );
                $this->navigations['pmDefaultMenu']->addItem( $item );
            }
            $this->renders[$name] = '';
            foreach( $this->navigations as $navigation )
            {
                $this->renders[$name] .= $navigation->render( $attributes, $node );
            }
            return $this->renders[$name];
        }
        else
        {
            $name = Stringy::camel_case( $name );
            if( ! isset( $this->navigations[$name] ) )
            {
                // This gets annoying!
                // Throw new \Exception( "Navigation '{$name}' does not exist. Cannot process render." );
                return false;
            }
            $this->renders[$name] = $this->navigations[$name]->render( $attributes, $node );
            return $this->renders[$name];
        }
    }

    /**
     * @param bool $type
     * @param bool $menu
     * @param bool $location
     * @throws \Exception
     */
    public function setMenuType( $type = false, $menu = false, $location = false )
    {
        if( ! isset( $this->navigations[$menu] ) )
        {
            Throw new Exception( "Menu '{$menu}' does not exist or you have called this method before the menu has been created." );
        }
        if( $location === false )
        {
            $location = $this->stylesLocation;
        }
        $this->navigations[$menu]->setType( $type, $location );
    }


}
