<?php
/*
Plugin Name: URL Rotator
Description: A simple URL rotation plugin for WordPress.
Version: 1.0
Author: Zainulabedin
*/


// Add a custom textarea field for URL rotation on pages and posts
function add_url_rotation_field() {
    add_meta_box(
        'url_rotation_field',
        'URL Rotation',
        'render_url_rotation_field',
        array('post', 'page'), // Add other post types if needed
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_url_rotation_field');

function render_url_rotation_field($post) {
    $rotation_text = get_post_meta($post->ID, 'url_rotation_text', true);
    wp_nonce_field(basename(__FILE__), 'url_rotation_nonce');
    ?>
    <label for="url_rotation_text">Enter URLs to Rotate (one per line):</label>
    <textarea id="url_rotation_text" name="url_rotation_text" rows="4" style="width: 100%;"><?php echo esc_textarea($rotation_text); ?></textarea>
    <input >
    <?php
}

function save_url_rotation_field($post_id) {
    if (!isset($_POST['url_rotation_nonce']) || !wp_verify_nonce($_POST['url_rotation_nonce'], basename(__FILE__)))
        return $post_id;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;

    if (in_array($_POST['post_type'], array('post', 'page'))) {
        if (current_user_can('edit_post', $post_id)) {
            $rotation_text = sanitize_text_field($_POST['url_rotation_text']);
            update_post_meta($post_id, 'url_rotation_text', $rotation_text);
        }
    }
}
add_action('save_post', 'save_url_rotation_field');


function auto_redirect_to_first_url() {
    // Check if it's a single page or post
    if (is_page()) {
        $post_id = get_the_ID();
        $rotation_text = get_post_meta($post_id, 'url_rotation_text', true);

        if (!empty($rotation_text)) {
            $urls = explode(",", $rotation_text);
            $first_url = trim($urls[0]);

            // Check if the first URL is not empty
            if (!empty($first_url)) {
                // Output JavaScript to perform the redirect
                echo '<script type="text/javascript">';
                echo 'window.location.replace("' . esc_url($first_url) . '");';
                echo '</script>';
            }
        }
    }
}
add_action('wp_footer', 'auto_redirect_to_first_url');