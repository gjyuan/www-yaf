<?php
class ErrorController extends Web_Controller_Base {
    public function errorAction($exception) {
        if($this->isApi()){
            $msg = $exception->getMessage();
            $this->error($msg);
        }else{
            $this->show();
        }
    }
}