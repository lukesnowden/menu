<?php

namespace LukeSnowden\Menu;

use LukeSnowden\Menu\Helpers\UTA as UTA;
use LukeSnowden\Menu\Helpers\URL;
use LukeSnowden\Menu\Helpers\Stringy;

/*
|--------------------------------------------------------------------------
| Menu Container Navigation
|--------------------------------------------------------------------------
|
| @author Luke Snowden
| @description Stores individual navigations
|
*/

class MenuContainerNavigation
{

    /**
     * @var string
     */
    private $type = 'default';

    /**
     * @var string
     */
    private $stylesLocation = '';

    /**
     * @var array
     */
    private $items = array();

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $entrust = false;

    public function useEntrustGuard()
    {
        $this->entrust = true;
    }

    /**
     * MenuContainerNavigation constructor.
     * @param $name
     */
    public function __construct( $name )
    {
        $this->name = $name;
    }

    /**
     * @param array $item
     */
    public function addItem( $item = array() )
    {
        if( !is_array( $item ) ) return;
        $defaults = array(
            'reference' 	=> 0,
            'text' 			=> '',
            'URL' 			=> '#',
            'parent' 		=> false,
            'children' 		=> array(),
            'class' 		=> '',
            'weight' 		=> 1,
            'icon'			=> '',
            'attributes'	=> array()
        );
        $this->items[] = array_merge( $defaults, $item );
    }

    /**
     * @param array $attributes
     */
    private static function renderAttributes( array $attributes )
    {
        foreach( $attributes as $attribute => $value )
        {
            echo "{$attribute}=\"{$value}\" ";
        }
    }

    /**
     * @param $structure
     * @param int $depth
     * @return string
     */
    private function renderDetail( $structure, $depth = 1 )
    {
        if( $depth === 1 )
        {
            ob_start();
        }
        ?>
        <?php foreach( $structure as $level ) : ?>
        <li class="<?php echo $level['class']; ?> <?php echo $level['parent'] === false ? 'p-container' : 'nav-node'; ?> node-<?php echo $depth; ?>">
            <?php if( $level['icon'] !== '' ) : ?>
                <i class="<?php echo $level['icon']; ?>"></i>
            <?php endif; ?>
            <a href="<?php echo $level['URL']; ?>" <?php self::renderAttributes( $level['attributes'] ); ?>><?php echo $level['text']; ?></a>
            <?php if( ! empty( $level['children'] ) ) : ?>
                <ul>
                    <?php $this->renderDetail( $level['children'], ($depth+1) ); ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
        <?php
        if( $depth === 1 )
        {
            return ob_get_clean();
        }
    }

    /**
     * @param $array
     * @param $column
     * @return mixed
     */
    private static function ausort( $array, $column )
    {
        usort( $array, 'self::sortByWeight' );
        foreach( $array as $key => $elements )
        {
            if( isset( $elements[$column] ) && is_array( $elements[$column] ) && ! empty( $elements[$column] ) )
            {
                $array[$key][$column] = self::ausort( $elements[$column], $column );
            }
        }
        return $array;
    }

    /**
     * @param $structure
     * @return mixed
     */
    private function sortItems( $structure )
    {
        $structure = self::ausort( $structure, 'weight' );
        foreach( $structure as $key => $item )
        {
            $structure[$key]['class'] .= $key === 0 ? ' first-item' : '';
            $structure[$key]['class'] .= ! isset( $structure[$key+1] ) ? ' last-item' : '';
            if( ! empty( $item['children'] ) )
            {
                $structure[$key]['children'] = $this->sortItems( $structure[$key]['children'] );
            }
        }
        return $structure;
    }

    /**
     * @param $structure
     * @return mixed
     */
    protected function removeUnAurthorised( $structure )
    {
        $permissions = collect();
        auth( 'web' )->user()->roles->each( function( $role ) use( &$permissions ) {
            $permissions = $permissions->merge( $role->permissions );
        });
        $permissions = $permissions->pluck( 'name', 'route_name' )->toArray();
        foreach( $structure as $key => $item ) {
            if( $item[ 'protected' ] ) {
                if( ! auth( 'web' )->check() ) {
                    unset( $structure[ $key ] );
                    continue;
                }
                if( ! isset( $permissions[ $item[ 'protected' ] ] ) ) {
                    unset( $structure[ $key ] );
                    continue;
                }
            }
            if( isset( $item[ 'children' ] ) && ! empty( $item[ 'children' ] ) ) {
                $structure[ $key ][ 'children' ] = $this->removeUnAurthorised( $item[ 'children' ] );
            }
            $item = $structure[ $key ];
            if( in_array( $item['URL'], [ 'javascript:;', '#' ] ) ) {
                if( isset( $item['children'] ) && empty( $item['children'] ) ) {
                    unset( $structure[ $key ] );
                    continue;
                }
                if( ! isset( $item['children'] ) ) {
                    unset( $structure[ $key ] );
                    continue;
                }
            }
        }
        return $structure;
    }

    /**
     * @param $attributes
     * @param $node
     * @return string
     * @throws \Exception
     */
    public function render( $attributes, $node )
    {
        $structure = $this->generate();
        $structure = $this->sortItems( $structure );
        $structure = $this->removeUnAurthorised( $structure );

        if( ! isset( $attributes['class'] ) ) {
            $attributes['class'] = '';
        }
        $attributesString = '';
        $return = '';

        if( $this->type === 'default' )
        {
            $attributes['class'] .= " nav-{$this->name}";
            foreach( $attributes as $attribute => $value ) {
                $attributesString .= " {$attribute}=\"{$value}\" ";
            }
            $return .= "<{$node} {$attributesString}>";
            $return .=		$this->renderDetail( $structure );
            $return .= "</{$node}>";
            return $return;
        }
        else
        {
            $attributes['class'] .= " nav-{$this->name}";
            foreach( $attributes as $attribute => $value ) {
                $attributesString .= " {$attribute}=\"{$value}\" ";
            }
            $class = $this->stylesLocation . '\\Styles';
            if( ! class_exists( $class ) )
            {
                Throw new \Exception( "{$class} does not exist" );
            }
            $style = new $class();
            $method = Stringy::camel_case( "render-{$this->type}" );
            if( ! class_exists( $class, $method ) )
            {
                Throw new \Exception( "{$method} does not exist" );
            }
            $return .= "<{$node} {$attributesString}>";
            $return .=		$style->{$method}( $structure );
            $return .= "</{$node}>";
            return $return;
        }

    }

    /**
     * @return mixed
     */
    public static function currentURI()
    {
        $fullLocation = rtrim( URL::current(), '/' ) . '/';
        $domain = URL::domain();
        return str_replace( '//', '/', '/' . trim( str_replace( $domain, '', $fullLocation ), '/' ) . '/' );
    }

    /**
     * @return array
     */
    private function getRoots()
    {
        $x = 0;
        $return = array();
        $count = count( $this->items );

        while( $count >= $x )
        {
            $item = array_shift($this->items);
            if( $item['parent'] === false )
            {
                $return[] = $item;
            }
            else
            {
                array_push( $this->items, $item );
            }
            $x++;
        }
        return $return;
    }

    /**
     * @param $ref
     * @return array
     */
    private function getChildren( $ref )
    {
        $x = 0;
        $return = array();
        $count = count( $this->items );
        while( $count > $x )
        {
            $item = array_shift( $this->items );
            if( (string)$item['parent'] == (string)$ref )
            {
                $item['children'] = $this->getChildren( $item['reference'] );
                $return[] = $item;
            }
            else
            {
                array_push( $this->items, $item );
            }
            $x++;
        }
        foreach( $return as $key => $item )
        {
            $return[$key]['class'] .= count( $item['children'] ) > 0 ? ' has-children' : '';
            $return[$key]['class'] .= $this->isAnAncestor( $item['children'] );
            $return[$key]['class'] .= $this->isParentClass( $item );
            $return[$key]['class'] .= $this->isUrlParentClass( $item );
        }
        return $return;
    }

    /**
     * @param $children
     * @return null|string
     */
    private function isAnAncestor( $children )
    {
        $currentURI = self::currentURI();
        foreach( $children as $child )
        {
            if( $currentURI == self::cleanseToURI( $child['URL'] ) )
            {
                return ' active-ancestor';
            }
            if( ! empty( $child['children'] ) )
            {
                if( ! is_null( $this->isAnAncestor( $child['children'] ) ) )
                {
                    return ' active-ancestor';
                }
            }
        }
        return NULL;
    }

    /**
     * @param $item
     * @return string
     */
    private function isParentClass( $item )
    {
        $currentURI = self::currentURI();
        foreach( $item['children'] as $child )
        {
            if( $currentURI == self::cleanseToURI( $child['URL'] ) )
            {
                return ' active-parent';
            }
        }
        return '';
    }

    /***
     * @param $item
     * @return string
     */
    public function isUrlParentClass( $item )
    {
        $currentURI = self::currentURI();
        if( preg_match( "#^" . preg_quote( rtrim( self::cleanseToURI( $item['URL'] ), '/' ), "#" ) . "/[^/]+$#is", rtrim( $currentURI, '/' ) ) ) {
            return ' active-url-parent';
        }
        return '';
    }

    /**
     * @param $item
     * @return string
     */
    public function isUrlAncestorClass( $item )
    {
        $currentURI = self::currentURI();
        if( preg_match( "#^" . preg_quote( rtrim( self::cleanseToURI( $item['URL'] ), '/' ), "#" ) . "/[^/]+/.+$#is", rtrim( $currentURI, '/' ) ) ) {
            return ' url-ancestor';
        }
        return '';
    }

    /**
     * @param $a
     * @param $b
     * @return mixed
     */
    private static function sortByWeight( $a, $b )
    {
        return $a['weight'] - $b['weight'];
    }

    /**
     * @param $children
     * @return null|string
     */
    private function rootClass( $children )
    {
        $currentURI = self::currentURI();
        foreach( $children as $child )
        {
            if( $currentURI == self::cleanseToURI( $child['URL'] ) )
            {
                return ' active-root';
            }
            if( ! empty( $child['children'] ) )
            {
                if( ! is_null( $class = $this->rootClass( $child['children'] ) ) )
                {
                    return $class;
                }
            }
        }
        return NULL;
    }

    /**
     * @param $url
     * @return mixed|string
     */
    public static function cleanseToURI( $url )
    {
        if( preg_match( "#^https?://.*#", $url ) )
        {
            return rtrim( str_replace( URL::domain(), '', $url ), '/' ) . '/';
        }
        else
        {
            return str_replace( '//', '/', '/' . trim( str_replace( URL::domain(), '', UTA::urlToAbsolute( URL::current(), $url ) ), '/' ) . '/' );
        }
    }

    private function setCurrentClass()
    {
        $currentURI = self::currentURI();
        foreach( $this->items as $key => $item )
        {
            if( $currentURI == self::cleanseToURI( $item['URL'] ) )
            {
                $this->items[$key]['class'] .= ' active';
            }
        }
    }

    /**
     * @return array
     */
    private function generate()
    {
        $this->setCurrentClass();
        $roots = $this->getRoots();
        $topWeight = 0;
        foreach( $roots as $key => $item )
        {
            $topWeight = max( $topWeight, $item[ 'weight' ] );
            $roots[ $key ][ 'children' ] = $this->getChildren( $item[ 'reference' ] );
            $roots[ $key ][ 'class' ] .= count( $roots[ $key ][ 'children' ] ) > 0 ? ' has-children' : '';
            $roots[ $key ][ 'class' ] .= $this->rootClass( $roots[ $key ][ 'children' ] );
            $roots[ $key ][ 'class' ] .= $this->isUrlParentClass( $item );
            $roots[ $key ][ 'class' ] .= $this->isUrlAncestorClass( $item );
        }
        if( $this->items ) {
            foreach( $this->items as $item ) {
                $item[ 'weight' ] = $topWeight + 1;
                $roots[] = $item;
                $topWeight++;
            }
        }
        return $roots;
    }

    /**
     * @param $type
     * @param $stylesLocation
     */
    public function setType( $type, $stylesLocation )
    {
        $this->type = $type;
        $this->stylesLocation = $stylesLocation;
    }

}
