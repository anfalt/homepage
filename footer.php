<?php

/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;


?>



<div class="wrapper" id="wrapper-footer">

	<div class="container-fluid">
		<div class="row">

			<div class="col-md-12">

				<footer class="site-footer" id="colophon">

					<div class="site-info row">
						<div class="col-12 col-md-4"></div>
						<div class="footer-links col-12 col-md-4">
							<a href="/kontakt">Kontakt</a>
							<a href="/kontakt#anfahrt">Anfahrt</a>
							<a href="/impressum">Impressum</a>
						</div>
						<div class="col-12 col-md-4 footer-brand"><a href="/">TC 1860 Rosenheim</a></div>


					</div>
			</div><!-- .site-info -->
		</div>
		</footer><!-- #colophon -->


		<!--col end -->

	</div><!-- row end -->


</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>