<?php

class RestClient
{
    protected $rest_client;
    protected $headers = array();

    protected static $inited = false;
    
    protected static function init()
    {
        if (!self::$inited) {
            self::$inited = true;
            if (!class_exists('PestJSON')) {
                require_once MERCHIUM_PLUGIN_DIR . 'lib/pest/PestJSON.php';
            }
        }
    }

    public function __construct($url, $user = null, $password = null, $auth_type = 'basic', $headers = array())
    {
        self::init();

        $this->rest_client = new PestJSON($url);

        // $this->rest_client->throwJsonExceptions = false;

        if (!empty($user) || !empty($password)) {
            $this->rest_client->setupAuth($user, $password, $auth_type);
        }

        $this->headers = $headers;
    }

    protected function request($method, $url, $data = array())
    {
        if (is_array($url)) {
            $url = '?' . http_build_query($url);
        }

        try {
            $res = $this->rest_client->$method($url, $data, $this->headers);
        } catch (Pest_Exception $e) {
            if (!$res = json_decode($e->getMessage(), true)) {
                $trace = $e->getTrace();
                $last = reset($trace);
                if ($last['function'] == 'jsonDecode') {
                    $res = reset($last['args']);
                } else {
                    $res = $e->getMessage();
                }
            }
        }
        return $res;
    }
    
    public function get($url, $data = array())
    {
        return $this->request('get', $url, $data);
    }
    
    public function post($url, $data = array())
    {
        return $this->request('post', $url, $data);
    }
    
    public function put($url, $data = array())
    {
        return $this->request('put', $url, $data);
    }
    
    public function delete($url)
    {
        return $this->request('delete', $url);
    }

}
