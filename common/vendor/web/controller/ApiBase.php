<?php
namespace vendor\web\controller;
class ApiBase extends Base{
    protected function init(){
        parent::init();
        $this->setIsApi(true);
    }
}