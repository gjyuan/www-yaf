<?php
use vendor\web\controller\ApiBase;
use \vendor\guzzle\AsyncClient;
use \vendor\webgeeker\Validation;

class Api_TestController extends ApiBase {
    private $validation = array(
        'type'=>"Required|IntIn:1,2",
    );
    public function indexAction(){
        $params = $_GET;
        try{
            $params =Validation::validate($params,$this->validation);
        }catch (Exception $e){
            var_dump($e->getMessage());
        }
        exit;
//        $detail = \service\Neirong::app()->getUserInfoByMobile();
//        $recommend = \service\Recommend::app()->getRecommend();
//        $result = ['detail'=>$detail,'recommend'=>$recommend];
//        $test1 = \service\PtUser::app()->getTest1();
//        AsyncClient::app()->addPromises('test1', \service\PtUser::app()->getTest1());
//        AsyncClient::app()->addPromises('test2', \service\PtUser::app()->getTest2());
//        $data = AsyncClient::app()->request();
        $data = \service\PtUser::request('yezhu_trust_test1',array(
            'type'     => 1,
            'page'     => 2,
            'pagesize' => 10,
        ));
        $result = [
          'resultMap'=>$data->getData()
        ];
        $msg = "ddddd";
        $this->success($result,$msg);
    }
    public function hiAction() {
        $data = ["a"=>"b"];
        $msg = "my name is hi";
        $this->success($data,$msg);
    }
}
