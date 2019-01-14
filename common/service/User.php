<?php
namespace service;
use vendor\guzzle\Client;

class User extends Client {
    protected function init(){
        parent::init();
        $this->setService('user-api');
    }

}
