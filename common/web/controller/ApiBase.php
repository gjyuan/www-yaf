<?php
class Web_Controller_ApiBase extends Web_Controller_Base{
    protected function init(){
        $this->setIsApi(true);
    }
}