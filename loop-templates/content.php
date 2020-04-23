<?php

/**
 * Post rendering content according to caller of get_template_part.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<div class="post container">
		<div class="row no-gutters">

			<?php
			if (get_the_post_thumbnail($post->ID, 'large') == '') {
			} else {
			?>
				<div class="postImageContainer col-4">
					<?php echo get_the_post_thumbnail($post->ID, 'large'); ?>
				</div>
			<?php }; ?>





			<div class="col-8 postTextContainer">
				<div class="px-3">
					<?php
					the_title(
						sprintf('<h4 ><a href="%s" rel="bookmark">', esc_url(get_permalink())),
						'</a></h4>'
					);
					?>
					<?php the_excerpt(); ?>
				</div>

			</div>

		</div>
	</div>

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->