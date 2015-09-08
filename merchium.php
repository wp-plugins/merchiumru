<?php
/**
 * Plugin Name: Онлайн-магазин Мерчиум
 * Plugin URI:  http://merchium.ru
 * Description: Merchium
 * Author:      Simtech Ltd.
 * Author URI:  http://merchium.ru
 * Version:     1.0.1
 */

define('MERCHIUM_PLUGIN_FILE', realpath(__FILE__));
define('MERCHIUM_PLUGIN_DIR', plugin_dir_path(MERCHIUM_PLUGIN_FILE));
define('MERCHIUM_PLUGIN_NAME', substr(MERCHIUM_PLUGIN_FILE, strlen(dirname(dirname(MERCHIUM_PLUGIN_FILE))) + 1));

define('MERCHIUM_SITE_URL', 'http://www.merchium.ru/');
define('MERCHIUM_SITE_API_URL', MERCHIUM_SITE_URL . 'wp-json.php/');

define('MERCHIUM_VOTE_URL', 'http://wordpress.org/support/view/plugin-reviews/merchiumRU');
define('MERCHIUM_SHOW_VOTE_MESSAGE_AFTER', 30 * 24 * 60 * 60); // 30 days

define('MERCHIUM_VERSION_IDENTIFY', 'Мерчиум');

define('MERCHIUM_COMPATIBILITY_MINIFY_JS', 'widget.cart-services.com/static/init.js');

require_once(MERCHIUM_PLUGIN_DIR . 'php/fn.core.php');
require_once(MERCHIUM_PLUGIN_DIR . 'php/fn.common.php');
require_once(MERCHIUM_PLUGIN_DIR . 'php/fn.compatibility.php');
require_once(MERCHIUM_PLUGIN_DIR . 'php/class.merchium_page.php');
require_once(MERCHIUM_PLUGIN_DIR . 'php/class.merchium_api.php');
require_once(MERCHIUM_PLUGIN_DIR . 'php/class.rest_client.php');

register_activation_hook(__FILE__, 'merchium_store_activate');
register_deactivation_hook(__FILE__, 'merchium_store_deactivate');

if (is_admin()) { 
    
    add_action('admin_menu', 'merchium_admin_menu');
    add_action('admin_init', 'merchium_admin_init');
    add_action('admin_enqueue_scripts', 'merchium_register_admin_scripts');
    add_action('admin_notices', 'merchium_show_admin_messages');
    add_action('wp_ajax_merchium_hide_vote_message', 'merchium_hide_vote_message');
    add_filter('plugin_action_links_merchium_wp/merchium.php', 'merchium_plugin_actions');
    add_action('pre_update_option_merchium_widget_code', 'merchium_update_option_merchium_widget_code', 10, 2);
    add_action('sm_buildmap', 'merchium_build_sitemap');
    add_action('wp_ajax_merchium_form', 'merchium_ajax_request');
    add_action('wp_ajax_nopriv_merchium_form', 'merchium_ajax_request');

} else {

    add_shortcode('merchium_store', 'merchium_store');
    add_action('wp_title', 'merchium_wp_title');
    add_action('wp_head', 'merchium_wp_head');
    add_action('wp_enqueue_scripts', 'merchium_register_frontend_scripts', 20);
    
    // Compatibility
    add_action('wp', 'merchium_seo_ultimate_compatibility', 0);
    add_action('plugins_loaded', 'merchium_minify_compatibility', 0);
    add_action('wp_title', 'merchium_seo_compatibility', 0);
    add_action('wp_head', 'merchium_seo_compatibility_restore', 1000);
    
}

// Internationalization
add_action('plugins_loaded', 'merchium_load_textdomain');
