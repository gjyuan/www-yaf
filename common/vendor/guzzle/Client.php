<?php
namespace vendor\guzzle;
use vendor\composer\Base;
class Client extends Base {
    private $_client;
    public function __construct()
    {
        $this->_client = new \GuzzleHttp\Client([
           'base_uri'=>"http://www.ke.com",
            'timeout'=>2,
        ]);
        $response = $this->_client->request('GET', '/city');
        var_dump($response->getBody()->getContents());exit;
    }
}