<?php

/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();

$container = get_theme_mod('understrap_container_type');
?>

<div class="wrapper" id="archive-wrapper">

	<div class="<?php echo esc_attr($container); ?>" id="content" tabindex="-1">

		<div class="row">




			<main class="site-main" id="main">

				<?php if (have_posts()) : ?>

					<header class="page-header">
						<?php
						the_archive_title('<h1 class="page-title">', '</h1>');
						the_archive_description('<div class="taxonomy-description">', '</div>');
						?>
					</header><!-- .page-header -->
					<div class="postsContainer" data-tag="<?php echo  get_queried_object()->slug ?>" data-infinite-loading="true"></div>


				<?php else : ?>

					<?php get_template_part('loop-templates/content', 'none'); ?>

				<?php endif; ?>

			</main><!-- #main -->




		</div> <!-- .row -->

	</div><!-- #content -->

</div><!-- #archive-wrapper -->

<?php get_footer(); ?>