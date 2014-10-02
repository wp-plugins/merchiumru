<?php

class MerchiumPage
{
    protected static $instances = array();

    protected $post = false;

    protected $has_store = false;
    protected $has_fragment = false;
    protected $escaped_fragment = '';
    protected $fragment_data = false;

    protected $sitemap_params = array(
        'products' => array(
            'priority' => 0.6,
            'change_freq' => 'weekly',
        ),
        'categories' => array(
            'priority' => 0.5,
            'change_freq' => 'weekly',
        ),
    );

    protected function __construct($post_id = false)
    {
        if ($post_id) {
            $this->post = get_post($post_id);
            $this->has_store = has_shortcode($this->post->post_content, 'merchium_store');
        }

        $this->has_fragment = isset($_REQUEST['_escaped_fragment_']);
        $this->escaped_fragment = $this->has_fragment ? urldecode($_REQUEST['_escaped_fragment_']) : '/'; // maybe need double urldecode
    }

    protected function __wakeup() {}

    protected function __clone() {}

    public static function instance($post_id = false)
    {
        $post_id = $post_id ? $post_id : get_the_ID();
        
        if (!isset(self::$instances[$post_id])) {
            self::$instances[$post_id] = new self($post_id);
        }

        return self::$instances[$post_id];
    }

    public function hasStore()
    {
        return $this->has_store;
    }

    public function hasFragment()
    {
        return $this->has_fragment;
    }

    public function getFragmentData()
    {
        if (!$this->fragment_data) {
            $url = $this->getCartUrl();
            if ($url) {
                $api = new MerchiumApi($url, 'json');
                $this->fragment_data = $api->get();
            }
        }
        return $this->fragment_data;
    }

    public function getFragmentContent()
    {
        $fragment_data = $this->getFragmentData();
        if (isset($fragment_data['html']['r'])) {
            return $this->parseHtml($fragment_data['html']['r']);
        }

        return false;
    }

    public function getParsedUrl()
    {
        return parse_url($this->getCartUrl(true));
    }

    public function getCanonicalUrl()
    {
        $url = fn_get_current_url();
        $parsed = parse_url($url);
        if (!empty($parsed['query'])) {
            $query = array();
            parse_str($parsed['query'], $query);
            if (!empty($query) && !empty($query['_escaped_fragment_'])) {
                
                $query['_escaped_fragment_'] = fn_query_remove($query['_escaped_fragment_'],
                    'subcats', 'features_hash', 'items_per_page', 'sort_by', 'sort_order'
                );
                $hash = urlencode($query['_escaped_fragment_']);
                unset($query['_escaped_fragment_']);
                $new_url = sprintf('%s://%s%s', $parsed['scheme'], $parsed['host'], $parsed['path']);
                $new_url = fn_prepare_url($new_url, $query);
                $new_url .= '#!' . $hash;
                
                $url = $new_url;
            }
        }

        return $url;
    }

    public function checkWidgetCode($code = false)
    {
        if (!$code) {
            $code = get_option('merchium_widget_code');
        }

        $url = $this->getCartUrl(true, 'http', $code);
        if (!empty($url)) {
            $api = new MerchiumApi($url);
            $result = $api->get('/?version');
            if (strpos($result, MERCHIUM_VERSION_IDENTIFY) === 0) {
                return true;
            }
        }
        return false;
    }

    public function generateSitemap($page_url, $callback)
    {
        $page_url .= '#!';

        $clear_url = $this->getCartUrl(true);
        $url = rtrim($clear_url, '/') . '/api/';

        $api = new MerchiumApi($url, 'json');

        $extra = '?get_frontend_urls=1&items_per_page=0';
        
        foreach (array('products', 'categories') as $object) {
            $result = $api->get($object . $extra);
            
            if ($object == 'products') {
                $items = $result['products'];
            } else {
                $items = $result;
            }

            if (!empty($items)) {
                foreach ($items as $item) {
                    if (strpos($item['url'], $clear_url) === 0) {
                        $url = str_replace($clear_url, '', $item['url']);
                        $url = $page_url . urlencode($url);

                        $callback(
                            $url,
                            $this->sitemap_params[$object]['priority'],
                            $this->sitemap_params[$object]['change_freq'],
                            time()
                        );
                    }
                }
            }

        }
        
        return;
    }

    protected function getCartUrl($clear = false, $protocol = false, $widget_code = false)
    {
        $url = false;
        $data = array();

        if (!$protocol) {
            $protocol = fn_detect_https($_SERVER) ? 'https' : 'http';
        }

        if (!$widget_code) {
            $widget_code = get_option('merchium_widget_code');
        }

        $widget_code = urldecode($widget_code);
        
        if (preg_match("#'$protocol://(.*?)'#", $widget_code, $m)) {
            $url = trim($m[0], "'");

            if (!$clear) {
                $url .= $this->escaped_fragment;
                
                if (preg_match("#layout=(\d+)#", $widget_code, $m)) {
                    $data['s_layout'] = $m[1];
                }
                
                $data['full_render'] = 1;
                $data['skip_result_ids_check'] = 1;
                $data['result_ids'] = 'r';
                $data['is_ajax'] = 1;
                $data['init_context'] = fn_get_current_url();
                $data['force_embedded'] = 1;
            }
        }

        if ($url && !empty($data)) {
            $delimiter = strpos($url, '?') ? '&' : '?';
            $url .= $delimiter . http_build_query($data);
        }

        return $url;
    }

    protected function parseHtml($html)
    {
        $dom_html = new DOMDocument;
        @$dom_html->loadHTML($html);
        $dom_head = $dom_html->getElementsByTagName('head')->item(0);
        $dom_body = $dom_html->getElementsByTagName('body')->item(0);

        $body = '';
        foreach ($dom_body->childNodes as $node) {
            $body .= $dom_html->saveHTML($node) . PHP_EOL;
        }
        $body = $this->parseHtmlBody($body);
        
        $styles = '';
        $dom_head_links = $dom_head->getElementsByTagName('link');
        for ($i = 0; $i < $dom_head_links->length; $i ++) {
            $node = $dom_head_links->item($i);
            if (strtolower($node->getAttribute('type')) == 'text/css') {
                $styles .= $dom_html->saveHTML($node) . PHP_EOL;
            }
        }

        $scripts = '';
        $dom_head_scripts = $dom_head->getElementsByTagName('script');
        for ($i = 0; $i < $dom_head_scripts->length; $i ++) {
            $node = $dom_head_scripts->item($i);
            $scripts .= $dom_html->saveHTML($node) . PHP_EOL;
        }

        // Additional script
        $scripts .= "<script type='text/javascript'>var merchium_store_fragment = true;</script>" . PHP_EOL;

        return $styles . PHP_EOL . $scripts . PHP_EOL . $body;
    }

    protected function parseHtmlBody($body)
    {
        $regexp = '/"[^"]*(_escaped_fragment_=([^"#])*)(#!)?[^"]*"/';
        $body = preg_replace_callback($regexp, function($m) {
            return str_replace($m[1], '', $m[0]);
        }, $body);

        return $body;
    }

}