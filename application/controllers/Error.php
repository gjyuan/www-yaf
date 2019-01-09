<?php
use vendor\web\controller\Base;
class ErrorController extends Base {
    public function errorAction($exception) {
        if($this->isApi()){
            $msg = $exception->getMessage();
            $this->error($msg);
        }else{
            $msg = $exception->getMessage();
            var_dump($msg);exit;
        }
    }
}