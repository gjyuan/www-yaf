<?php
namespace service;
use vendor\guzzle\Client;

class Neirong extends Client {
    protected function init(){
        parent::init();
        $this->setService('neirong-api');
    }

}
