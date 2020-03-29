<?php

/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
$custom_logo_id = get_theme_mod('custom_logo');
$image = wp_get_attachment_image_src($custom_logo_id, 'full');
$container = get_theme_mod('understrap_container_type');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php do_action('wp_body_open'); ?>
	<div class="site" id="page">

		<!-- ******************* The Navbar Area ******************* -->
		<div id="wrapper-navbar" class="container-fluid" itemscope itemtype="http://schema.org/WebSite">
			<div class="row">
				<div id="logo-container" class="col-2 col-md-1 text-center logo-container">
					<a href="/" title="Home"><img src="/wp-content/uploads/2020/03/1860RosenheimLogo.png" /> </a>
				</div>
				<div id="logo-container-inverse" class="col-2 col-md-1 text-center logo-container">
					<a href="/" title="Home"><img src="/wp-content/uploads/2020/03/1860RosenheimLogo_white.png" /> </a>
				</div>
				<div class="nav-background">

				</div>
				<nav class="navbar-custom navbar-expand-md  bg-secondary col-md-10 col-8">
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>











					<!-- The WordPress Menu goes here -->
					<?php

					$arrowHTML = '      <i class="dropdown-menu-arrow"></i>';
					$subMenuItemSeperator = '<span class="nav-item-divider">/</span>';
					wp_nav_menu(
						array(
							'theme_location'  => 'primary',
							'container_class' => 'collapse navbar-collapse',
							'container_id'    => 'nav-container',
							'menu_class'      => 'navbar-nav mr-auto',
							'fallback_cb'     => '',
							'menu_id'         => 'main-menu',
							'depth'           => 2,
							'link_after_isChild' => $subMenuItemSeperator,
							'link_after_hasChildren' => $arrowHTML,
							'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
						)
					); ?>


				</nav><!-- .site-navigation -->
				<div id="social-icon-container" class="col-2 col-md-1">
					<i class="fab fa-instagram"></i>
				</div>
			</div>
		</div><!-- #wrapper-navbar end -->