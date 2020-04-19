<?php

//register custom endpoint for sponsor images
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/images/(?P<cat>\S+)', array(
        'methods' => 'GET',
        'callback' => 'handle_get_startpage_sponsors'
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
        'post__not_in' => get_option('sticky_posts'),
        'posts_per_page' => 10,
        'post_type' => array('post', 'tribe_events'),
        'post_status' => 'publish'

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
            )
        );
    }

    $posts = get_posts(
        $args
    );
    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach ($posts as $post) {
        $id = $post->ID;
        $post_thumbnail = (has_post_thumbnail($id)) ? get_the_post_thumbnail_url($id) : null;

        $posts_data[] = (object) array(
            'id' => $id,
            'slug' => $post->post_name,
            'excerpt' => $post->post_excerpt,
            'content' => $post->post_content,
            'type' => $post->post_type,
            'title' => $post->post_title,
            'imageUrl' => $post_thumbnail,
            'link' => get_post_permalink($id),
            'tags' => get_the_tags($id)
        );
    }
    return $posts_data;
}

function handle_get_startpage_sponsors($data)
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
    return $query_images;
}
