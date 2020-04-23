<?php

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
    if (strpos($term->slug, "-tag") !== false) {
        return $url;
    } else {
        $url = str_replace("tag/", "", $url);
        return str_replace("-tag/", "", $url);
    }
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
            '_EventStartDate' => 'ASC'
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
            'custom_fields' => $post->custom_fields
        );
    }

    return    array('posts' => $posts_data, 'page' => $paged);
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
