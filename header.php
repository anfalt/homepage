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
				<div id="nav-btn" class="col-4">
					<div class="nav-burger-menu"></div>
				</div>
				<div id="logo-container" class="col-4  col-md-1 text-center logo-container">
					<a href="/" title="Home"><img src="/wp-content/uploads/2020/03/1860RosenheimLogo.png" /> </a>
				</div>
				<div id="logo-container-inverse" class="col-4  col-md-1 text-center logo-container">
					<a href="/" title="Home"><img src="/wp-content/uploads/2020/03/1860RosenheimLogo_white.png" /> </a>
				</div>
				<div class="nav-background">

				</div>

				<nav class="navbar-custom navbar-expand-md col-md-9 col-lg-9 col-xl-9 ">












					<!-- The WordPress Menu goes here -->
					<?php

					$arrowHTML = '      <i class="dropdown-menu-arrow"></i>';
					wp_nav_menu(
						array(
							'theme_location'  => 'primary',
							'container_class' => '',
							'container_id'    => 'nav-container',
							'menu_class'      => 'navbar-nav mr-auto',
							'fallback_cb'     => '',
							'menu_id'         => 'main-menu',
							'depth'           => 2,
							'link_after_hasChildren' => $arrowHTML,
							'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
						)
					); ?>


				</nav><!-- .site-navigation -->
				<div id="social-icon-container" class="col-4 col-md-2 col-lg-2 col-xl-2">
					<div>
						<i class="fa fa-instagram icon-instagram" title="Instagram"></i>
						<i class="fa fa-facebook icon-facebook" title="Facebook"></i>
					</div>
					<div>
						<i class="fa fa-envelope icon-contact" title="Kontakt"></i>
						<i class="fa fa-map-marker icon-maps" title="Anfahrt"></i>
					</div>
				</div>
			</div>
		</div><!-- #wrapper-navbar end -->