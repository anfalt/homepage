<?php


//* Remove NextGen Fontawesome CSS
add_action('wp_print_styles', 'theme_remove_nextgen_fontawesome_css', 100);
function theme_remove_nextgen_fontawesome_css()
{
    wp_dequeue_style('fontawesome');
    wp_dequeue_style('fontawesome_v4_shim_style');
}

// Remove NextGen Fontawesome JS
add_action('wp_print_scripts', 'theme_remove_nextgen_fontawesome_js', 100);
function theme_remove_nextgen_fontawesome_js()
{
    wp_dequeue_script('fontawesome_v4_shim');
    wp_deregister_script('fontawesome_v4_shim');
}

//register custom endpoint for sponsor images
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/images/(?P<cat>\S+)', array(
        'methods' => 'GET',
        'callback' => 'handle_get_images'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/allPosts', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_posts_callback'
    ));
});

add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_tax()) { //for custom post types
        $title = sprintf(__('%1$s'), single_term_title('', false));
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    }
    return $title;
});
add_filter('term_link', 'term_link_filter', 10, 3);
function term_link_filter($url, $term, $taxonomy)
{
    if ($term->description) {
        return $term->description;
    }
    if (strpos($term->slug, "-tag") === false) {
        return $url;
    } else {
        $url = str_replace("tag/", "", $url);
        return str_replace("-tag", "", $url);
    }
}



function custom_api_get_posts_callback($request)
{

    $ids = explode(",", $request->get_param('postId'));



    $args =  array(
        'posts_per_page' => 100,
        'post_type' => array('post', 'tribe_events'),
        'post_status' => 'publish',
        'post__in' => $ids,
    );



    $query = new WP_Query($args);
    $posts = $query->posts;

    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach ($posts as $post) {
        $id = $post->ID;
        $post_thumbnail = (has_post_thumbnail($id)) ? get_the_post_thumbnail_url($id) : null;

        $allowedTags = array(
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'b' => array(),
        );
        $posts_data[] = (object) array(
            'id' => $id,
            'slug' => $post->post_name,
            'excerpt' => $post->post_excerpt,
            'content' => wp_kses($post->post_content, $allowedTags),
            'type' => $post->post_type,
            'title' => $post->post_title,
            'imageUrl' => $post_thumbnail,
            'link' => get_post_permalink($id),
            'tags' => get_the_tags($id),
            'publish_date' => $post->post_date,
            'custom_fields' => get_post_custom($id)
        );
    }

    return    array('posts' => $posts_data, 'page' => $paged);
}

function custom_api_get_all_posts_callback($request)
{
    // Initialize the array that will receive the posts' data. 
    $posts_data = array();
    // Receive and set the page parameter from the $request for pagination purposes
    $paged = $request->get_param('page');
    $paged = (isset($paged) || !(empty($paged))) ? $paged : 1;
    // Get the posts using the 'post' and 'news' post types


    $args =  array(
        'paged' => $paged,
        'posts_per_page' => 10,
        'post_type' => array('post', 'tribe_events'),
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_EventStartDate', //Compare using the event's start date
                'value' => date('Y-m-d H:i:s'), //Compare against today's date
                'compare' => '>=', //Get events that are set to the value's date or in the future
                'type' => 'DATETIME' //This is a date query
            ),
            array(
                'key'      => '_EventStartDate',
                'compare'  => 'NOT EXISTS'
            ),
            'relation' => 'OR',
        ),
        'orderby' => array(
            '_EventStartDate' => 'ASC',
            'post_date' => 'DESC'
        ),

    );

    $tags = $request->get_param('tags');
    if (isset($tags)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => explode(',', $tags)
            )
        );
    }

    $categories = $request->get_param('categories');
    if (isset($categories)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => explode(',', $categories)
            ),

        );
    }

    $query = new WP_Query($args);
    $posts = $query->posts;

    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach ($posts as $post) {
        $id = $post->ID;
        $post_thumbnail = (has_post_thumbnail($id)) ? get_the_post_thumbnail_url($id) : null;

        $allowedTags = array(
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'b' => array(),
        );
        $posts_data[] = (object) array(
            'id' => $id,
            'slug' => $post->post_name,
            'excerpt' => $post->post_excerpt,
            'content' => wp_kses($post->post_content, $allowedTags),
            'type' => $post->post_type,
            'title' => $post->post_title,
            'imageUrl' => $post_thumbnail,
            'link' => get_post_permalink($id),
            'tags' => get_the_tags($id),
            'publish_date' => $post->post_date,
            'custom_fields' => get_post_custom($id)
        );
    }

    return    array('posts' => $posts_data, 'page' => $paged);
}

add_filter('redirect_canonical', 'my_redirect_canonical', 10, 2);
function my_redirect_canonical($redirect_url, $requested_url)
{
    return  $requested_url;
}

function handle_get_images($data)
{
    $cat = $data['cat'];


    $images = getImagesFromFolder($cat);

    return $images;
}

function getImagesFromFolder($cat)
{
    $query_images_args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'attachment_category',
                'field'    => 'slug',
                'terms'    => $cat,
            ),
        ),
    );
    $query_images = new WP_Query($query_images_args);
    $images = array();
    foreach ($query_images->posts as $image) {
        $images[] =
            (object) array(
                'id' => $image->ID,
                'title' => $image->post_title,
                'link' => $image->post_excerpt,
                'content' =>  $image->post_content,
                'imageURL' =>    wp_get_attachment_url($image->ID),
                'menu-order' => $image->menu_order

            );
    }
    return $images;
}


function the_breadcrumb()
{
    $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
    $delimiter = '<i class="fa fa-angle-double-right breadcrumb-delimiter"></i>'; // delimiter between crumbs
    $home = 'TC 1860 Rosenheim'; // text for the 'Home' link
    $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
    $before = '<h6 class="current">'; // tag before the current crumb
    $after = '</h6>'; // tag after the current crumb

    global $post;
    $homeLink = get_bloginfo('url');
    if (is_home() || is_front_page()) {
        if ($showOnHome == 1) {
            echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a></div>';
        }
    } else {
        echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
        if (is_category()) {
            $thisCat = get_category(get_query_var('cat'), false);
            if ($thisCat->parent != 0) {
                echo get_category_parents($thisCat->parent, true, ' ' . $delimiter . ' ');
            }
            echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
        } elseif (is_search()) {
            echo $before . 'Search results for "' . get_search_query() . '"' . $after;
        } elseif (is_day()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo '<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('d') . $after;
        } elseif (is_month()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('F') . $after;
        } elseif (is_year()) {
            echo $before . get_the_time('Y') . $after;
        } elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post') {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->label . '</a>';
                if ($showCurrent == 1) {
                    echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
                }
            } elseif (has_tag()) {
                echo '<a href="' . get_tag_link(get_the_tags()[0]) . '">' . get_the_tags()[0]->name  . '</a>' . $delimiter . $before  . get_the_title()  . $after;
            } else {
                echo $before . get_the_title() . $after;
            }
        } elseif (!is_tag() && !is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
            $post_type = get_post_type_object(get_post_type());
            echo $before . $post_type->label . $after;
        } elseif (is_attachment()) {
            $parent = get_post($post->post_parent);
            $cat = get_the_category($parent->ID);
            $cat = $cat[0];
            echo get_category_parents($cat, true, ' ' . $delimiter . ' ');
            echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
            if ($showCurrent == 1) {
                echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            }
        } elseif (is_page() && !$post->post_parent) {
            if ($showCurrent == 1) {
                echo $before . get_the_title() . $after;
            }
        } elseif (is_page() && $post->post_parent) {
            $parent_id  = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                $parent_id  = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            for ($i = 0; $i < count($breadcrumbs); $i++) {
                echo $breadcrumbs[$i];
                if ($i != count($breadcrumbs) - 1) {
                    echo ' ' . $delimiter . ' ';
                }
            }
            if ($showCurrent == 1) {
                echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            }
        } elseif (is_tag()) {
            echo $before  . single_tag_title('', false)  . $after;
        } elseif (is_author()) {
            global $author;
            $userdata = get_userdata($author);
            echo $before . 'Articles posted by ' . $userdata->display_name . $after;
        } elseif (is_404()) {
            echo $before . 'Seite nicht gefunden ' . $after;
        }
        if (get_query_var('paged')) {
            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ' (';
            }
            echo __('Page') . ' ' . get_query_var('paged');
            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ')';
            }
        }
        echo '</div>';
    }
}
