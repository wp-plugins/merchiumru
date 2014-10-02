<?php


/**
 * Prints any data like a print_r function
 * @param mixed ... Any data to be printed
 */
function fn_print_r()
{
    static $count = 0;
    $args = func_get_args();

    if (defined('DOING_AJAX')) {
        $prefix = "\n";
        $suffix = "\n\n";
    } else {
        $prefix = '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
        $suffix = '</ol><div style="clear:left;"></div>';
    }

    if (!empty($args)) {
        fn_echo($prefix);
        foreach ($args as $k => $v) {

            if (defined('DOING_AJAX')) {
                fn_echo(print_r($v, true) . "\n");
            } else {
                fn_echo('<li><pre>' . htmlspecialchars(print_r($v, true)) . "\n" . '</pre></li>');
            }
        }
        fn_echo($suffix);
    }
    $count++;
}

function fn_print_die()
{
    $args = func_get_args();
    call_user_func_array('fn_print_r', $args);
    die();
}

function fn_echo($value)
{
    if (defined('CONSOLE')) {
        $value = str_replace(array('<br>', '<br />'), "\n", $value);
        $value = strip_tags($value);
    }

    echo $value;

    fn_flush();
}

function fn_flush()
{
    if (function_exists('ob_flush')) {
        @ob_flush();
    }

    flush();
}

function fn_redirect($location)
{
    $meta_redirect = false;
    $delay = 0;

    if (!ob_get_contents() && !headers_sent() && !$meta_redirect) {
        header('Location: ' . $location);
        exit;
    } else {
        if ($delay != 0) {
            fn_echo('<a href="' . htmlspecialchars($location) . '" style="text-transform: lowercase;">' . __('continue') . '</a>');
        }
        fn_echo('<meta http-equiv="Refresh" content="' . $delay . ';URL=' . htmlspecialchars($location) . '" />');
    }

    fn_flush();
    exit;
}

/**
 * Detects HTTPS mode
 * @param array $server SERVER superglobal array
 */
function fn_detect_https($server)
{
    if (isset($server['HTTPS']) && ($server['HTTPS'] == 'on' || $server['HTTPS'] == '1')) {
        return true;
    } elseif (isset($server['HTTP_X_FORWARDED_SERVER']) && ($server['HTTP_X_FORWARDED_SERVER'] == 'secure' || $server['HTTP_X_FORWARDED_SERVER'] == 'ssl')) {
        return true;
    } elseif (isset($server['SCRIPT_URI']) && (strpos($server['SCRIPT_URI'], 'https') === 0)) {
        return true;
    } elseif (isset($server['HTTP_HOST']) && (strpos($server['HTTP_HOST'], ':443') !== false)) {
        return true;
    }
    return false;
}

function fn_get_current_url()
{
    $url = fn_detect_https($_SERVER) ? 'https' : 'http';
    $url .= '://';
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['REQUEST_URI'];

    return $url;
}

/**
 * Parse the URN query part
 *
 * @param string $urn URN (Uniform Resource Name or Query String)
 * @return string parse query
 */
function fn_parse_urn($urn)
{
    $escaped = false;
    $path = '';
    if (($i = strpos($urn, '?')) !== false) { // full url with query string
        $qs = substr($urn, $i + 1);
        $path = str_replace('?' . $qs, '', $urn);
    } elseif (strpos($urn, '&') !== false || strpos($urn, '=') !== false) { // just query string
        $qs = $urn;
    } else { // no query string
        $qs = '';
        $path = $urn;
    }

    if (strpos($qs, '&amp;') !== false) {
        $escaped = true;
        $qs = str_replace('&amp;', '&', $qs);
    }

    parse_str($qs, $params);

    return array($path, $params, $escaped);
}

/**
 * Build the URN
 *
 * @param string $path
 * @param string $params
 * @param bool $escaped
 * @return string $urn URN (Uniform Resource Name or Query String)
 */
function fn_build_urn($path, $params, $escaped)
{
    $urn = $path;
    if (!empty($params)) {
        $res = http_build_query($params, '', ($escaped ? '&amp;' : '&'));
        $urn .= (!empty($path)) ? ('?' . $res) : $res;
    }

    return $urn;
}

/**
 * Remove parameter from the URL query part
 *
 * @param string ... query
 * @param string ... parameters to remove
 * @return string modified query
 */
function fn_query_remove()
{
    $args = func_get_args();
    $url = array_shift($args);

    if (!empty($args)) {
        list($path, $params, $escaped) = fn_parse_urn($url);

        foreach ($args as $param_name) {
            unset($params[$param_name]);
        }

        $url = fn_build_urn($path, $params, $escaped);
    }

    return $url;
}

function fn_prepare_url($url, $data = array())
{
    if ($data) {
        $delimiter = strpos($url, '?') ? '&' : '?';
        $url .= $delimiter . http_build_query($data);
    }

    return $url;
}

