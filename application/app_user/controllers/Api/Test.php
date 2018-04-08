<?php
class Api_TestController extends Web_Controller_ApiBase {
    public function indexAction(){
        $data = ["a"=>"b"];
        $msg = "hahahaha";
        $requestMap = array(
            'local'=>array('url'=>"http://localhost"),
        );
        $result = Utils_Curl::multiCurl($requestMap);
        $this->success($result,$msg);
    }
    public function hiAction() {
        $data = ["a"=>"b"];
        $msg = "my name is hi";
        $this->success($data,$msg);
    }
}
