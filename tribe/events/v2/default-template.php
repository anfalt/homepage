<?php

/**
 * View: Default Template for Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/default-template.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.0
 */

use Tribe\Events\Views\V2\Template_Bootstrap;

get_header();
?>
<div class="wrapper" id="full-width-page-wrapper">

    <div class="<?php echo esc_attr($container); ?>" id="content">

        <div class="row">

            <div class="col-md-12 content-area tribe-common " id="primary">

                <main class="site-main tribe-common-l-container" id="main" role="main">
                    <?php
                    echo tribe(Template_Bootstrap::class)->get_view_html();
                    ?>
                </main>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
?>