<?php
use vendor\web\controller\ApiBase;
class Api_TestController extends ApiBase {
    public function indexAction(){
        var_dump(\service\Neirong::app()->getService(),\service\User::app());exit;
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
