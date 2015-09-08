<?php

class MerchiumApi
{
    protected $url;
    protected $format;
    protected $headers;
    protected $extra;

    protected $http;

    const TIMEOUT = 20;

    public function __construct($url, $format = '', $headers = array(), $extra = array())
    {
        $this->url = $url;
        $this->format = $format;
        $this->headers = $headers;
        $this->extra = $extra;

        $this->http = new WP_Http();
    }

    public function get($url = '', $params = array())
    {
        if (!empty($params)) {
            $delimiter = strpos($url, '?') ? '&' : '?';
            $url .= $delimiter . http_build_query($params);
        }

        $body = $this->doRequest($url, 'get');
        $body = $this->processBody($body);

        return $body;
    }

    protected function doRequest($url = '', $method = 'get')
    {
        if ($this->url != $url) {
            $url = $this->url . $url;
        }

        $params = array_merge($this->extra, array(
            'method' => strtoupper($method),
            'timeout' => self::TIMEOUT,
            'headers' => $this->headers
        ));
        $result = $this->http->request($url, $params);

        if (is_array($result) && !empty($result['response']['code']) && $result['response']['code'] == 200) {
            return $result['body'];
        }

        return false;
    }

    protected function processBody($body)
    {
        if ($this->format == 'json') {
            return @json_decode($body, true);
        }

        return $body;
    }

}