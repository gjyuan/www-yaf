<?php
use vendor\web\controller\ApiBase;
class Api_TestController extends ApiBase {
    public function indexAction(){
        $detail = \service\Neirong::app()->getUserInfoByMobile();
        $recommend = \service\Recommend::app()->getRecommend();
        $result = ['detail'=>$detail,'recommend'=>$recommend];
        $msg = "ddddd";
        $this->success($result,$msg);
    }
    public function hiAction() {
        $data = ["a"=>"b"];
        $msg = "my name is hi";
        $this->success($data,$msg);
    }
}
