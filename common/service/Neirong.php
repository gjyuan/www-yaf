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

    }
}
