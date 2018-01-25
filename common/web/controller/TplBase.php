<?php

class Web_Controller_TplBase extends Web_Controller_Base{
    protected function init(){
        $this->setIsApi(false);
    }

}