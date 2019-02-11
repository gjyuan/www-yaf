<?php
namespace service;
use vendor\guzzle\Client;

class Recommend extends Client {
    protected function init(){
        $this->setService('recommend-api');
        parent::init();
    }

    public function getRecommend(){
        try{
            $params = array(
                'city_id' => ['110000'],
                'ershou_house_id'=>['101102750376'],
                'reco_cnt'=>3,
                'ext_info'=>array('special_city'=>['110000']),
                'request_id'=>"eac98a4ae9fd2348d35d6d0948480448",
                'unique_id'=>'eac98a4ae9fd2348d35d6d0948480448',
                'client_ip'=>'127.0.0.1',
                'req_source_type'=>'beike_app',
                'uuid'=>'98e20149-b48a-4b07-a05e-830f1e9f9f6f',
            );
            $this->setRequestUri('/recommend/100034',self::METHOD_POST);
            $this->addJsonParams($params);
            $result = $this->request();
            if($result->isSuccess()){
                return $result->getData();
            }else{
                return [];
            }
        }catch (\Exception $e){
            var_dump($e->getMessage());
            //TODO 记录日志
            return [];
        }

    }
}
