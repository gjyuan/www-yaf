<?php
class Vendor_Curl_MultiCurl{
    private $_multi_ch = null;
    private $_ch_arr = [];
    public function __construct($chArr=[]){
        $this->__curlMutiInit($chArr);
    }
    private function __curlMutiInit($chArr=[]){
        if(!empty($this->_multi_ch)){
            return $this->_multi_ch;
        }
        $this->_multi_ch = curl_multi_init();
        if(!empty($chArr)){
            foreach ($chArr as $key=>$ch){
                if(get_resource_type($ch) == "curl"){
                    curl_multi_add_handle($this->_multi_ch,$ch);
                    $this->_ch_arr[$key] = $ch;
                }else{
                    $this->_ch_arr[$key] = "The value of index '{$key}' is not a curl handler";
                }
            }
        }
        return $this->_multi_ch;
    }
    public function addRequest($url,$getParams=[],$postParams=[],$header=[],$cookie=[]){
        $curl = new Vendor_Curl_Instance($url);
        $curl->addGetParams($getParams);
        $curl->addPostParams($postParams);
        $curl->addHeaders($header);
        $curl->addCookies($cookie);
        $instance = $curl->getCurlHandle();
        curl_multi_add_handle($this->_multi_ch,$instance);
        $this->_ch_arr[] = $instance;
    }
    //执行curl
    public function execute(){
        try{
            $active = null;$response = [];
            do {
                $mrc = curl_multi_exec($this->_multi_ch, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($this->_multi_ch) == -1) {
                    usleep(50);
                }
                do {
                    $mrc = curl_multi_exec($this->_multi_ch, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
            //4.关闭子curl 并获取结果
            foreach($this->_ch_arr as $k=>$ch){
                if(get_resource_type($ch) == "curl"){
                    $response[$k] = curl_multi_getcontent($ch);
                    curl_multi_remove_handle($this->_multi_ch, $ch);
                }else{
                    $response[$k] = $ch;
                }
            }
            //5.关闭父curl_multi
            curl_multi_close($this->_multi_ch);
            return [true,$response];
        }catch (Exception $e){
            return[false,$e->getMessage()];
        }
    }


}