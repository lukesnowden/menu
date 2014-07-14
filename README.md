# lukesnowden/menu


## Usage


Very simple method of building menus from database data (id, parent id), service provider register methods for application modules and much more.

## Standalone

```php
use LukeSnowden\Menu\Menu;
include __DIR__ . '/vendor/autoload.php';
```

## Example 1


```php
<?php
Menu::addItem( array( 'text' => 'Home', 'URL' => '/', 'reference' => '0' ) );
echo Menu::render();
?>
```

##Example 2 - Nesting Children


```php
<?php
Menu::addItem( array( 'text' => 'Services', 'URL' => '/services/', 'reference' => '1', 'parent' => '0' ) );
echo Menu::render();
?>
```

## Example 3 - Multiple Menus


```php
<?php
Menu::addItem( array( 'text' => 'Services', 'URL' => '/services/', 'reference' => '1', 'parent' => '0' ) )->toMenu( 'main' );
echo Menu::render( 'main' );
?>
```

## Auto classes


I have added in some of the most used and required classes for styling menus


```css
.first-item {}
.last-item {}
.current-root {}
.current-parent {}
.current-ancestor {}
.has-children {}
```

## Output


```php
<?php
Menu::addItem( array( 'text' => 'Home', 'URL' => '/menu-test-2/public/', 'reference' => '1', 'class' => 'home-icon', 'weight' => 0 ) )->toMenu( 'main' );
Menu::addItem( array( 'text' => 'Services', 'URL' => '/menu-test-2/public/services/', 'reference' => '2' ) )->toMenu( 'main' );
Menu::addItem( array( 'text' => 'Development', 'URL' => '/menu-test-2/public/services/development/', 'reference' => '3', 'parent' => '2' ) )->toMenu( 'main' );
Menu::addItem( array( 'text' => 'Design', 'URL' => '/menu-test-2/public/services/design/', 'reference' => '4', 'parent' => '2', 'weight' => 0 ) )->toMenu( 'main' );
echo Menu::render( 'main' );
?>
```

```html
<ul class="cf clearfix nav-main pm-menu">
    <li class="home-icon current first-item container node-1">
        <a href="/menu-test-2/public/">Home</a>
    </li>
    <li class=" has-children last-item container node-1">
        <a href="/menu-test-2/public/services/">Services</a>
        <ul>
            <li class=" first-item nav-node node-2">
                <a href="/menu-test-2/public/services/design/">Design</a>
            </li>
            <li class=" last-item nav-node node-2">
                <a href="/menu-test-2/public/services/development/">Development</a>
            </li>
        </ul>
    </li>
</ul>
```

## Custom attributes
$name = false, $attributes = array(), $htmlTag = 'ul'
```php
echo Menu::render( 'main', array( 'class' => 'nav nav-pills nav-stacked', 'role' => 'tablist' ), 'nav' );
```

## Use with third party menu UI through L4 Model
(Please note this is just a general summary of how it would work if you had 2 tables (and models) for navigations and navigation items with a standard hasMany() relationship)


```php
<?php
$navigation = Navigation::with( 'navigationItems' )->where( 'navigation_slug', '=', 'main' )->get();
foreach( $navigation->navigationItems as $item )
{
    Menu::addItem( array( 'text' => , $item->name 'URL' => $item->url, 'reference' => $item->id, 'parent' => $item->parent_id, 'weight' => $item->order ) )->toMenu( $navigation->navigation_slug );
}
echo Menu::render( $navigation->navigation_slug );
?>
```

## Laravel Install

Add the following to you applications composer.json file

```php
composer require lukesnowden/menu:dev-master
```

add the following to your /app/config/app.php's provider array.


```php
'LukeSnowden\Menu\MenuServiceProvider'
```


add the following to your /app/config/app.php's aliases array.


```php
'Menu'      => 'LukeSnowden\Menu\Facades\Menu'
```


and finally back to your terminal and run


```shell
php composer.phar dump-autoload
```


