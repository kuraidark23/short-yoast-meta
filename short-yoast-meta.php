<?php
/*
Plugin Name: Short Yoast Meta
Description: Modify Yoast SEO behavior to truncate titles and meta descriptions for selected post types. Includes debug mode.
Version: 2.1
Author: KD23 (modified by Assistant)
*/

// Add admin menu
function short_yoast_meta_menu() {
    add_options_page('Short Yoast Meta Settings', 'Short Yoast Meta', 'manage_options', 'short-yoast-meta', 'short_yoast_meta_options_page');
}
add_action('admin_menu', 'short_yoast_meta_menu');

// Create options page
function short_yoast_meta_options_page() {
    ?>
    <div class="wrap">
        <h1>Short Yoast Meta Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('short-yoast-meta-settings');
            do_settings_sections('short-yoast-meta-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function short_yoast_meta_register_settings() {
    register_setting('short-yoast-meta-settings', 'short_yoast_meta_options');
    
    add_settings_section('short_yoast_meta_main', 'Post Types', null, 'short-yoast-meta-settings');
    
    $post_types = array('post' => 'Posts', 'page' => 'Pages', 'product' => 'Products');
    $taxonomies = array('category' => 'Categories', 'product_cat' => 'Product Categories');
    
    foreach ($post_types as $key => $label) {
        add_settings_field(
            'short_yoast_meta_' . $key,
            $label,
            'short_yoast_meta_checkbox_callback',
            'short-yoast-meta-settings',
            'short_yoast_meta_main',
            array('key' => $key)
        );
    }
    
    foreach ($taxonomies as $key => $label) {
        add_settings_field(
            'short_yoast_meta_' . $key,
            $label,
            'short_yoast_meta_checkbox_callback',
            'short-yoast-meta-settings',
            'short_yoast_meta_main',
            array('key' => $key)
        );
    }

    // Add debug mode option
    add_settings_field(
        'short_yoast_meta_debug',
        'Debug Mode',
        'short_yoast_meta_checkbox_callback',
        'short-yoast-meta-settings',
        'short_yoast_meta_main',
        array('key' => 'debug')
    );
}
add_action('admin_init', 'short_yoast_meta_register_settings');

// Checkbox callback
function short_yoast_meta_checkbox_callback($args) {
    $options = get_option('short_yoast_meta_options');
    $key = $args['key'];
    $checked = isset($options[$key]) ? $options[$key] : 0;
    echo "<input type='checkbox' name='short_yoast_meta_options[$key]' value='1' " . checked(1, $checked, false) . "/>";
}

// Truncate the title to 55 characters
function gmedia_truncate_title($title) {
    if (should_truncate()) {
        $original_title = $title;
        if (strlen($title) > 55) {
            $title = substr($title, 0, 55) . '...';
        }
        debug_log('Title', $original_title, $title);
    }
    return $title;
}
add_filter('wpseo_title', 'gmedia_truncate_title');

// Truncate the meta description to 155 characters
function gmedia_truncate_metadesc($metadesc) {
    if (should_truncate()) {
        $original_metadesc = $metadesc;
        if (strlen($metadesc) > 155) {
            $metadesc = substr($metadesc, 0, 155) . '...';
        }
        debug_log('Meta Description', $original_metadesc, $metadesc);
    }
    return $metadesc;
}
add_filter('wpseo_metadesc', 'gmedia_truncate_metadesc');

// Helper function to check if we should truncate for the current post/term
function should_truncate() {
    $options = get_option('short_yoast_meta_options');
    
    if (is_singular()) {
        $post_type = get_post_type();
        return isset($options[$post_type]) && $options[$post_type];
    } elseif (is_category() && isset($options['category'])) {
        return $options['category'];
    } elseif (is_tax('product_cat') && isset($options['product_cat'])) {
        return $options['product_cat'];
    }
    
    return false;
}

// Debug logging function
function debug_log($type, $original, $truncated) {
    $options = get_option('short_yoast_meta_options');
    if (isset($options['debug']) && $options['debug']) {
        add_action('wp_footer', function() use ($type, $original, $truncated) {
            echo "<script>
                console.log('Short Yoast Meta Debug:');
                console.log('Type: " . esc_js($type) . "');
                console.log('Original: " . esc_js($original) . "');
                console.log('Truncated: " . esc_js($truncated) . "');
                console.log('-----------------------');
            </script>";
        });
    }
}

// Enqueue admin scripts
function short_yoast_meta_admin_scripts($hook) {
    if ('settings_page_short-yoast-meta' !== $hook) {
        return;
    }
    wp_enqueue_script('short-yoast-meta-admin', plugins_url('admin.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'short_yoast_meta_admin_scripts');