<?php
class Api_TestController extends Web_Controller_ApiBase {
    public function indexAction(){
        $data = ["a"=>"b"];
        $msg = "hahahaha";
        $result = Utils_Curl::get("http://localhost/");
        $this->success($result,$msg);
    }
    public function hiAction() {
        $data = ["a"=>"b"];
        $msg = "my name is hi";
        $this->success($data,$msg);
    }
}
