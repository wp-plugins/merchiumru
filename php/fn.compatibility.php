<?php

function merchium_seo_ultimate_compatibility()
{
    global $seo_ultimate;

    if ($seo_ultimate && MerchiumPage::instance()->hasStore()) {
        remove_action('template_redirect', array($seo_ultimate->modules['titles'], 'before_header'), 0);
        remove_action('wp_head', array($seo_ultimate->modules['titles'], 'after_header'), 1000);
        remove_action('su_head', array($seo_ultimate->modules['meta-descriptions'], 'head_tag_output'));
        remove_action('su_head', array($seo_ultimate->modules['canonical'], 'link_rel_canonical_tag'));
        remove_action('su_head', array($seo_ultimate->modules['canonical'], 'http_link_rel_canonical'));
    }

}

function merchium_minify_compatibility()
{
    global $wp_minify;

    if (
        is_object($wp_minify)
        && isset($wp_minify->default_exclude)
        && is_array($wp_minify->default_exclude)
    ) {
        $wp_minify->default_exclude[] = MERCHIUM_COMPATIBILITY_MINIFY_JS;
    }
}

function merchium_seo_compatibility($title)
{
    $page = MerchiumPage::instance();
    if ($page->hasStore() && $page->hasFragment()) {

        // Default wordpress canonical
        remove_action('wp_head', 'rel_canonical');

        // WordPress SEO by Yoast
        global $wpseo_front;
        remove_action('wpseo_head', array($wpseo_front, 'canonical'), 20); // Canonical
        remove_action('get_header', array($wpseo_front, 'force_rewrite_output_buffer')); // Title
        remove_action('wp_footer', array($wpseo_front, 'flush_cache'));
        remove_action('wpseo_head', array($wpseo_front, 'metadesc'), 10); // Description

        // Platinum SEO Pack
        merchium_temporary_override_option('psp_canonical', false); // Canonical
        merchium_temporary_override_option('aiosp_rewrite_titles', false); // Title

        // All in one SEO Pack
        global $aioseop_options;
        $aioseop_options['aiosp_can'] = false; // Canonical
        add_filter('aioseop_title', '__return_null'); // Title
        add_filter('aioseop_description', '__return_null'); // Description

    }

    return $title;
}

function merchium_seo_compatibility_restore()
{
    $page = MerchiumPage::instance();
    if ($page->hasStore() && $page->hasFragment()) {

        merchium_temporary_override_option('psp_canonical');
        merchium_temporary_override_option('aiosp_rewrite_titles');

    }
}
