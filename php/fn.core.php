<?php

function merchium_store_activate()
{
    $content = <<<EOT
<!-- Merchium code. Please do not remove this line or your Merchium shopping cart will not work properly. -->
[merchium_store]
<!-- Merchium code end -->
EOT;

    add_option("merchium_store_page_id", false);
    add_option("merchium_widget_code", '');
    
    add_option("merchium_installation_date", time());
    add_option('merchium_show_vote_message', true);
    
    add_option('merchium_widget_is_connected', false);

    $current_page = false;
    if ($store_page_id = get_option("merchium_store_page_id")) { 
        $current_page = get_post($store_page_id);
    }
    
    if ($current_page) {
        wp_update_post(array(
            'ID'          => $store_page_id,
            'post_status' => 'publish',
        ));
    } else {
        $store_page_id =  wp_insert_post(array(
            'post_title'     => __('Store', 'merchium'),
            'post_content'   => $content,
            'post_status'    => 'publish',
            'post_author'    => 1,
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ));
        update_option('merchium_store_page_id', $store_page_id);
    }

}

function merchium_store_deactivate()
{
    if ($store_page_id = get_option("merchium_store_page_id")) {
        $current_page = get_page($store_page_id);
        if ($current_page) {
            wp_update_post(array(
                'ID'          => $store_page_id,
                'post_status' => 'draft',
            ));
        } else {
            update_option('merchium_store_page_id', false);
        }
    }
}

function merchium_admin_init()
{
    register_setting('merchium_options_page', 'merchium_widget_code');
}

function merchium_admin_menu()
{
    add_menu_page(
        __('Merchium shopping cart settings', 'merchium'),
        __('Merchium Store', 'merchium'),
        'manage_options',
        'merchium',
        'merchium_general_settings_do_page'
    );

    add_submenu_page(
        'merchium',
        __('General settings', 'merchium'),
        __('General', 'merchium'),
        'manage_options',
        'merchium',
        'merchium_general_settings_do_page'
    );

}

function merchium_general_settings_do_page()
{
    global $current_user;

    $_store_name = strtolower(get_option('blogname'));
    $_email = $current_user->user_email;

    $is_connected = get_option('merchium_widget_is_connected');
    return include (MERCHIUM_PLUGIN_DIR . 'php/content.admin_merchium.php');
}

function merchium_register_admin_scripts()
{
    // Core scripts
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');

    wp_enqueue_script('merchium-admin-js', plugins_url('js/admin.js', MERCHIUM_PLUGIN_FILE));

    wp_enqueue_style('merchium-admin-css', plugins_url('css/admin.css', MERCHIUM_PLUGIN_FILE));

    if (version_compare(get_bloginfo('version'), '3.8-beta') > 0) {
        wp_enqueue_style('merchium-admin-css-3.8', plugins_url('css/admin-3.8.css', MERCHIUM_PLUGIN_FILE));
    }

    // Init
    $options = json_encode(array(
        'ajax_url'    => admin_url('admin-ajax.php'),
        'ajax_action' => 'merchium_form',
    ));
    echo "
        <script type='text/javascript'>
            merchium_opts = {$options};
        </script>
    \n";

}

function merchium_register_frontend_scripts()
{
    $page = MerchiumPage::instance();
    if ($page->hasStore()) {
        if ($page->hasFragment()) {
            wp_enqueue_script('jquery');

            wp_register_script('merchium-frontend-fragment-js', plugins_url('js/frontend-fragment.js', MERCHIUM_PLUGIN_FILE), 'jquery');
            wp_enqueue_script('merchium-frontend-fragment-js');
        }

        wp_enqueue_style('merchium-frontend-css', plugins_url('css/frontend.css', MERCHIUM_PLUGIN_FILE));
        
        // Theme scripts
        $version = get_bloginfo('version');
        if (version_compare( $version, '3.4' ) < 0) {
            $theme_name = get_current_theme();
        } else {
            $theme = wp_get_theme();
            $theme_name = $theme->get('Name');
        }
        $css_file = strtolower(str_replace(' ', '-', $theme_name));
        $css_file = 'css/frontend/themes/' . $css_file . '.css';
        if (file_exists(MERCHIUM_PLUGIN_DIR . '/' . $css_file)) {
            wp_enqueue_style('merchium-frontend-theme-css', plugins_url($css_file, MERCHIUM_PLUGIN_FILE));
        }
    }
}

function merchium_show_admin_messages()
{
    $install_date = get_option('merchium_installation_date');
    if (!$install_date) {
        add_option('merchium_installation_date', time());
    } elseif ($install_date + MERCHIUM_SHOW_VOTE_MESSAGE_AFTER < time() && get_option('merchium_show_vote_message')) {
        $message = sprintf(
            __('Do you like your Merchium online store? We\'d appreciate it if you <a target="_blank" href="%s">add your review and vote</a> for the plugin on Wordpress site. (<a class="merch-hide-vote-message">Close</a> and do not show this message anymore)', 'merchium'),
            MERCHIUM_VOTE_URL
        );
        merchium_show_admin_message($message);
    }
    
}

function merchium_show_admin_message($message)
{
    echo sprintf('<div class="%s" style="margin-top: 5px">%s</div>',
        'update-nag', $message
    );
}

function merchium_hide_vote_message()
{
    update_option('merchium_show_vote_message', false);
    echo json_encode(array(
        'status' => 'ok'
    ));
    exit;
}

function merchium_store()
{
    $page = MerchiumPage::instance();
    if ($page->hasFragment()) {
        return $page->getFragmentContent();
    } else {
        if ($widget_code = get_option('merchium_widget_code')) {
            return $widget_code;
        } else {
            return sprintf('<h2>%s</h2> <p>%s</p> <p>%s</p>',
                __('Almost there!'),
                __("Just go to your WordPress admin panel and set up your Merchium store."),
                __("It will take just a few seconds, and you'll be ready to start selling!")
            );
        }
    }
}

function merchium_wp_title($title)
{
    $page = MerchiumPage::instance();
    if ($page->hasStore() && $page->hasFragment()) {
        $fragment_data = $page->getFragmentData();
        if (isset($fragment_data['title'])) {
            return $fragment_data['title'];
        }
    }

    return $title;
}

function merchium_wp_head()
{
    $page = MerchiumPage::instance();

    if ($page->hasStore()) {
        if ($page->hasFragment()) {
            echo '<link rel="canonical" href="' . $page->getCanonicalUrl() . '" />' . PHP_EOL;
        } else {
            echo '<meta name="fragment" content="!">' . PHP_EOL;
        }
    }

    $url_parsed = $page->getParsedUrl();
    if (!empty($url_parsed['host'])) {
        echo '<link rel="dns-prefetch" href="//' . $url_parsed['host'] . '/">' . PHP_EOL;
    }
}

function merchium_plugin_actions($links)
{
    $settings_link = sprintf('<a href="%s">%s</a>', 'admin.php?page=merchium', __('Settings'));
    array_unshift($links, $settings_link);
    return $links;
}

function merchium_temporary_override_option($name, $new_value = null)
{
    static $previos_options = array();

    if (!isset($previos_options[$name])) {
        $previos_options[$name] = get_option($name);
    }

    if (!is_null($new_value)) { // override
        update_option($name, $new_value);
    } else { // restore
        update_option($name, $previos_options[$name]);
    }
}

function merchium_update_option_merchium_widget_code($value, $old_value)
{
    if ($old_value != $value) {
        $page = MerchiumPage::instance();

        $is_connected = false;
        if (!empty($value)) {
            $is_connected = $page->checkWidgetCode($value);
        }
        
        update_option('merchium_widget_is_connected', $is_connected);

        if (!$is_connected) {
            $value = '';
        }
    }

    return $value;
}

function merchium_build_sitemap()
{
    $store_page_id = get_option("merchium_store_page_id");
    $store_page_url = get_page_link($store_page_id);

    MerchiumPage::instance($store_page_id)->generateSitemap($store_page_url, 'merchium_build_sitemap_callback');
}

function merchium_build_sitemap_callback($url, $priority = 0.0, $change_freq = 'never', $last_mod = 0)
{
    echo $url . '<br />';

    $generator = & GoogleSitemapGenerator::GetInstance();

    if (is_object($generator)) {
        $page = new GoogleSitemapGeneratorPage($url, $priority, $change_freq, $last_mod);
        $generator->AddElement($page);
    }
}

function merchium_ajax_request()
{
    $result = merchium_process_request($_POST);

    header('Content-Type:text/json; charset=UTF-8');
    echo json_encode($result);
    exit;

}

function merchium_process_request($params)
{
    $api = new RestClient(MERCHIUM_SITE_API_URL);

    if (!empty($params['recover_password'])) {
        $path = 'merchium/recover';
    } elseif (!empty($params['login'])) {
        $path = 'merchium/store/login';
    } else {
        $path = 'merchium/store';
    }

    $result = $api->post($path, $params);

    // Override default redirect
    if (!empty($result['redirect']) || !empty($result['redirect_slow'])) {
        $redirect_url = !empty($result['redirect']) ? $result['redirect'] : $result['redirect_slow'];

        if (!empty($params['login'])) {
            $link_header = __('We have successfully logged in!', 'merchium');
        } else {
            $link_header = __('Thanks for registering at Merchium!', 'merchium');
        }
        $link_text = str_replace('[url]', $redirect_url, __('Please go to your <a href="[url]" target="_blank">admin panel</a> (Design → Layouts), copy the widget code, and come back here.', 'merchium'));

        $result['info'] = sprintf("<strong>%s</strong><br /><br />\n%s", $link_header, $link_text);
        $result['hide_form'] = true;
        
        unset($result['redirect']);
        unset($result['redirect_slow']);
    }
    
    return $result;
}

function merchium_load_textdomain()
{
    load_plugin_textdomain('merchium', false, basename(dirname(MERCHIUM_PLUGIN_FILE)) . '/languages');
}
