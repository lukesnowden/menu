<?php namespace Purposemedia\Menu;

use Purposemedia\Menu\Helpers\UTA as UTA;

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

	private $type = 'default';

	private $stylesLocation = '';

	/* @name Items
	 * @author Luke Snowden
	 * @param $items (array)
	 * @decription Stores all navigation node arrays
	*/

	private $items = array();

	/* @name Name
	 * @author Luke Snowden
	 * @param $items (array)
	*/

	private $name = '';

	/*
	 * @method Construct
	 * @author Luke Snowden
	 * @param $name (string)
	*/

	public function __construct( $name )
	{
		$this->name = $name;
	}

	/*
	 * @method Add Item
	 * @author Luke Snowden
	 * @param $text (string), $url (string), $reference (int), $parent (false/int)
	*/

	public function addItem( $item = array() )
	{
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

	/*
	 * @method Render Details
	 * @author Luke Snowden
	 * @param $structure (array), $depth (int)
	*/

	private static function renderAttributes( array $attributes )
	{
		foreach( $attributes as $attribute => $value )
		{
			echo "{$attribute}=\"{$value}\" ";
		}
	}

	private function renderDetail( $structure, $depth = 1 )
	{
		if( $depth === 1 )
		{
			ob_start();
		}
		?>
		<?php foreach( $structure as $level ) : ?>
			<li class="<?php echo $level['class']; ?> <?php echo $level['parent'] === false ? 'p-container' : 'nav-node'; ?> node-<?php echo $depth; ?>">
				<i class="<?php echo $level['icon']; ?>"></i>
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

	/*
	 * @method Array Usort
	 * @author Luke Snowden
	 * @param $array (array)
	 * @param $column (array)
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

	/*
	 * @method Render
	 * @author Luke Snowden
	 * @param (void)
	*/

	public function render()
	{
		$structure = $this->generate();
		$structure = $this->sortItems( $structure );
		$return = '';

		if( $this->type === 'default' )
		{
			$return .= "<ul class=\"cf clearfix nav-{$this->name} pm-menu\">";
			$return .=		$this->renderDetail( $structure );
			$return .= "</ul>";
			return $return;
		}
		else
		{
			$class = $this->stylesLocation . '\\Styles';
			if( ! class_exists( $class ) )
			{
				Throw new \Exception( "{$class} does not exist" );
			}
			$style = new $class();
			$method = \camel_case( "render-{$this->type}" );
			if( ! class_exists( $class, $method ) )
			{
				Throw new \Exception( "{$method} does not exist" );
			}
			$return .= "<ul class=\"cf clearfix nav-type-{$this->type} pm-menu\">";
			$return .=		$style->{$method}( $structure );
			$return .= "</ul>";
			return $return;
		}

	}

	/*
	 * @method Current URI
	 * @author Luke Snowden
	 * @param (void)
	*/

	public static function currentURI()
	{
		$fullLocation = rtrim( \URL::current(), '/' ) . '/';
		$domain = \Config::get( 'app.url' );
		return str_replace( '//', '/', '/' . trim( str_replace( $domain, '', $fullLocation ), '/' ) . '/' );
	}

	/*
	 * @method Get Roots
	 * @author Luke Snowden
	 * @param (void)
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

	/*
	 * @method Get Children
	 * @author Luke Snowden
	 * @param $ref (int)
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
		}
		return $return;
	}

	/*
	 * @method Is An Ancestor
	 * @author Luke Snowden
	 * @param $children (array)
	*/

	private function isAnAncestor( $children )
	{
		$currentURI = self::currentURI();
		foreach( $children as $child )
		{
			if( $currentURI == self::cleanseToURI( $child['URL'] ) )
			{
				return ' current-ancestor';
			}
			if( ! empty( $child['children'] ) )
			{
				if( ! is_null( $this->isAnAncestor( $child['children'] ) ) )
				{
					return ' current-ancestor';
				}
			}
		}
		return NULL;
	}

	/*
	 * @method Is Parent Class
	 * @author Luke Snowden
	 * @param $item (array)
	*/

	private function isParentClass( $item )
	{
		$currentURI = self::currentURI();
		foreach( $item['children'] as $child )
		{
			if( $currentURI == self::cleanseToURI( $child['URL'] ) )
			{
				return ' current-parent';
			}
		}
		return '';
	}

	/*
	 * @method Sort By Weight
	 * @author Luke Snowden
	 * @param $a (array)
	 * @param $b (array)
	*/

	private static function sortByWeight( $a, $b )
	{
	    return $a['weight'] - $b['weight'];
	}

	/*
	 * @method Root class
	 * @author Luke Snowden
	 * @param $children (array)
	*/

	private function rootClass( $children )
	{
		$currentURI = self::currentURI();
		foreach( $children as $child )
		{
			if( $currentURI == self::cleanseToURI( $child['URL'] ) )
			{
				return ' current-root';
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

	/*
	 * @method Cleanse To URI
	 * @author Luke Snowden
	 * @param $url (string)
	*/

	public static function cleanseToURI( $url )
	{
		$domain = \Config::get( 'app.url' );
		if( preg_match( "#^https?://.*#", $url ) )
		{
			return $url;
		}
		else
		{
			return str_replace( '//', '/', '/' . trim( str_replace( \Config::get( 'app.url' ), '', UTA::urlToAbsolute( \URL::current(), $url ) ), '/' ) . '/' );
		}
	}

	/*
	 * @method Set Current Class
	 * @author Luke Snowden
	 * @param (void)
	*/

	private function setCurrentClass()
	{
		$currentURI = self::currentURI();
		foreach( $this->items as $key => $item )
		{
			if( $currentURI == self::cleanseToURI( $item['URL'] ) )
			{
				$this->items[$key]['class'] .= ' current';
			}
		}
	}

	/*
	 * @method Generate
	 * @author Luke Snowden
	 * @param (void)
	*/

	private function generate()
	{
		$this->setCurrentClass();
		$roots = $this->getRoots();
		foreach( $roots as $key => $item )
		{
			$roots[$key]['children'] = $this->getChildren( $item['reference'] );
			$roots[$key]['class'] .= count( $roots[$key]['children'] ) > 0 ? ' has-children' : '';
			$roots[$key]['class'] .= $this->rootClass( $roots[$key]['children'] );
		}
		return $roots;
	}

	/*
	 * @method Set Type
	 * @author Luke Snowden
	 * @param $type (string)
	 * @param $stylesLocation (string)
	*/

	public function setType( $type, $stylesLocation )
	{
		$this->type = $type;
		$this->stylesLocation = $stylesLocation;
	}

}