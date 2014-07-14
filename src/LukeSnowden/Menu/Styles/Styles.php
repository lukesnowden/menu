<?php namespace LukeSnowden\Menu\Styles;

class Styles
{

	public static function renderHorizontal( $structure = array(), $depth = 1 )
	{
		if( $depth === 1 ) ob_start();
		?>
		<?php foreach( $structure as $level ) : ?>
			<li class="<?php echo $level['class']; ?> <?php echo $level['parent'] === false ? 'container' : 'nav-node'; ?> node-<?php echo $depth; ?>">
				<a href="<?php echo $level['URL']; ?>"><?php echo $level['text']; ?></a>
				<?php if( ! empty( $level['children'] ) ) : ?>
					<ul>
						<?php self::renderHorizontal( $level['children'], ($depth+1) ); ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		<?php
		if( $depth === 1 ) return ob_get_clean();
	}

}

?>