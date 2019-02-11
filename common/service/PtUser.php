<?php
namespace service;
use vendor\guzzle\Client;

class PtUser extends Client {
    protected $_service = 'pt-user-api';
    protected function init(){
        parent::init();
    }

    protected function beforeRequest(){
        parent::beforeRequest();
        $this->addQueryParams(['source'=>'ljapi']);
    }

    public function getTest1(){
        $this->setRequestUri('/yezhu/entrust/test1',self::METHOD_GET);
        return $this->requestAsync();
    }

    public function getTest2(){
        $this->setRequestUri('/yezhu/entrust/test2',self::METHOD_GET);
        return $this->requestAsync();
    }

}