<?php namespace LukeSnowden\Menu\Styles;

class Styles
{
	/*
	public static function renderHorzontal( $structure = array(), $depth = 1 )
	{
		?>
		<?php foreach( $structure as $level ) : ?>
			<li class="<?php echo $level['class']; ?> <?php echo $level['parent'] === false ? 'container' : 'nav-node'; ?> node-<?php echo $depth; ?>">
				<a href="<?php echo $level['URL']; ?>"><?php echo $level['text']; ?></a>
				<?php if( ! empty( $level['children'] ) ) : ?>
					<ul>
						<?php self::renderHorzontal( $level['children'], ($depth+1) ); ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		<?php
	}
	*/

	public static function renderNavTabsDropdowns( $structure = array(), $depth = 1 )
	{
		if( $depth === 1 ) ob_start();
		foreach( $structure as $level ) :
			$class = preg_replace( '/current/', 'active', $level['class'] );
			echo '<li class=" ' . $class . ' ' . ( empty( $level['children'] ) ? '' : 'dropdown' ) . '">';
				if( ! empty( $level['children'] ) ) :
					echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $level['text'] . ' <span class="caret"></span></a>';
					echo '<ul class="dropdown-menu" role="menu">';
						echo self::renderNavTabsDropdowns( $level['children'], ($depth+1) );
					echo '</ul>';
				else :
					echo '<a href="' . $level['URL'] . '">' . $level['text'] . '</a>';
				endif;
			echo '</li>';
		endforeach;
		if( $depth === 1 ) return ob_get_clean();
	}

}

?>