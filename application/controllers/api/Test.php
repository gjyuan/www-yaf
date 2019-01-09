<?php
use vendor\web\controller\ApiBase;
class Api_TestController extends ApiBase {
    public function indexAction(){
        $result = ['a'=>1,'b'=>2];
        $msg = "ddddd";
        $this->success($result,$msg);
    }
    public function hiAction() {
        $data = ["a"=>"b"];
        $msg = "my name is hi";
        $this->success($data,$msg);
    }
}
