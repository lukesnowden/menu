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

}

?>