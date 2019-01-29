<?php
namespace service;
use vendor\guzzle\Client;

class Neirong extends Client {
    protected function init(){
        $this->setService('neirong-api');
        parent::init();
    }

    public function getUserInfoByMobile(){
        $params = array(
            'mobile' => '13521127656'
        );
        $this->setRequestUri('/api/user/getInfoByMobile',self::METHOD_GET);
        $this->setQueryParams($params);
        $response = $this->request();
        if($response->isSuccess()){
            return $response->getData();
        }else{
            return [];
        }
    }
}
