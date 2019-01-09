<?php
namespace Web\Controller;
class ApiBase extends Base{
    protected function init(){
        parent::init();
        $this->setIsApi(true);
    }
}