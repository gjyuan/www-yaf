<?php
namespace service;
use vendor\guzzle\Client;

class Neirong extends Client {
    protected function init(){
        $this->setService('neirong-api');
        parent::init();
    }

    public function getUserInfoByMobile(){
        $uri = '/api/user/getInfoByMobile';
        $params = array(
            'mobile' => '13521127656'
        );
        $this->setRequestUri($uri);
        $this->setRequestMethod(self::METHOD_GET);
        $this->setQueryParams($params);
        $response = $this->send();
        echo ($response->getBody()->getContents());exit;
    }
}
