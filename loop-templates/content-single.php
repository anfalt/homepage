<?php

/**
 * Single post partial template.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">
		<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
		<?php echo get_the_term_list(get_the_ID(), 'post_tag', '<span class="event-tag badge badge-secondary">', '</span><span class="event-tag badge badge-secondary">', '</span>'); ?>
	</header><!-- .entry-header -->


	<div class="entry-content">

		<?php the_content(); ?>



	</div><!-- .entry-content -->



</article><!-- #post-## -->