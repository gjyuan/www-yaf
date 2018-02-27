<?php
class Vendor_Curl_MultiCurl{
    private $_getParams = [];
    private $_postParams = [];
    private $_headers = [];
    private $_cookies = [];
    private $_files = [];
    private $_connectTimeout = 2;
    private $_timeout = 3;
    private $_isSington = true;
    private $_curl;
    private $_muti_ch;
    private $_userAgent;
    public function getUserAgent(){
        if(!empty($this->_userAgent)){
            return $this->_userAgent;
        }else{
            return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Yaf curl plugin/0.1";
        }
    }
    public function setUserAgent($ua){
        $this->_userAgent = $ua;
    }
    private function __curlInit(){
        if($this->_isSington && !empty($this->_curl)) {
            return $this->_curl;
        }
        $opt = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_USERAGENT       => $this->getUserAgent(),
            CURLOPT_CONNECTTIMEOUT  => $this->_connectTimeout,   // timeout on connect
            CURLOPT_TIMEOUT         => $this->_timeout,          // timeout on response
        );
        $this->_curl = curl_init();
        curl_setopt_array ($this->_curl, $opt);
        return $this->_curl;
    }
    private function __curlMutiInit($chArr=[]){
        if(!empty($this->_muti_ch)){
            return $this->_muti_ch;
        }
        $this->_muti_ch = curl_multi_init();
        return $this->_muti_ch;
    }



}