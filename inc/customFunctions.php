<?php

//register custom endpoint for sponsor images
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/images/(?P<cat>\S+)', array(
        'methods' => 'GET',
        'callback' => 'handle_get_startpage_sponsors'
    ));
});

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
