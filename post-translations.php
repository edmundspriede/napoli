add_action('rest_api_init', function () {
    register_rest_route('domain/v1', '/post-translations/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'get_wpml_post_translations',
        'permission_callback' => '__return_true',
    ]);
});

function get_wpml_post_translations($request) {
    $post_id = $request['id'];
    
   // if (!function_exists('icl_object_id') || !function_exists('wpml_get_element_translations')) {
    //    return new WP_REST_Response(['error' => 'WPML not active or not fully loaded.'], 500);
    //}

    $type = get_post_type($post_id);
    if (!$type) {
        return new WP_REST_Response(['error' => 'Invalid post ID.'], 404);
    }

    // Get WPML translation group ID (trid)
    $trid = apply_filters('wpml_element_trid', null, $post_id, 'post_' . $type);
    if (!$trid) {
        return new WP_REST_Response(['error' => 'Translation group not found.'], 404);
    }

    $translations = apply_filters('wpml_get_element_translations', null, $trid, 'post_' . $type);
    $data = [];

    foreach ($translations as $lang => $translation) {
        $translated_post = get_post($translation->element_id);
        if ($translated_post) {
            $data[$lang] = [
                'id'    => $translated_post->ID,
                'title' => get_the_title($translated_post),
                'slug'  => $translated_post->post_name,
                'lang'  => $lang,
                'link'  => get_permalink($translated_post),
            ];
        }
    }

    return new WP_REST_Response([
        'original_post_id' => $post_id,
        'translations'     => $data,
    ]);
}



