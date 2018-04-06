<?php
class Vendor_Curl_Instance{
    private $_getParams = [];
    private $_postParams = [];
    private $_headers = [];
    private $_cookies = [];
    private $_files = [];
    private $_connectTimeout = 2;
    private $_timeout = 3;
    private $_userAgent;
    private $_requestUrl;
    private $_curl;
    public function __construct($url=""){
        $this->_requestUrl = $url;
    }
    protected function __curlInit(){
        $opt = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_USERAGENT       => $this->getUserAgent(),
            CURLOPT_CONNECTTIMEOUT  => $this->_connectTimeout,   // timeout on connect
            CURLOPT_TIMEOUT         => $this->_timeout,          // timeout on response
        );
        $url = $this->getUrl();
        //GET 参数
        if(!empty($this->_getParams)){
            $query = http_build_query($this->_getParams);
            $url .= (strpos($url, "?") ? "&" : "?") . $query;
        }
        //POST 参数
        if(!empty($this->_postParams) && is_array($this->_postParams)){
            $opt[CURLOPT_POST] = true;
            $opt[CURLOPT_POSTFIELDS] = http_build_query($this->_postParams);
        }
        //HEADER 参数
        if(!empty($this->_headers)){
            $opt[CURLOPT_HTTPHEADER] = $this->_headers;
        }
        //COOKIE 参数
        if(!empty($this->_cookies)){
            $tempArr = [];
            foreach($this->_cookies as $k => $val){
                $tempArr[] = $k . '=' . $val;
            }
            $opt[CURLOPT_COOKIE] = implode(',',$tempArr);
        }
        $opt[CURLOPT_URL] = $url;
        $this->_curl = curl_init();
        curl_setopt_array ($this->_curl, $opt);
        return $this->_curl;
    }
    public function getCurlHandle(){
        if(!empty($this->_curl)){
            return $this->_curl;
        }
        $this->_curl = $this->__curlInit();
        return $this->_curl;
    }
    public function getUrl(){
        if(empty($this->_requestUrl)){
            throw new Exception("curl request url is empty");
        }
        $protocol = parse_url($this->_requestUrl, PHP_URL_SCHEME);
        if(!in_array($protocol,['http','https'])){
            throw new Exception("httpExcute only allow http/https protocal");
        }
        return $this->_requestUrl;
    }
    public function setUrl($url){
        $this->_requestUrl = $url;
    }
    public function addGetParams(array $getParams){
        $this->_getParams = array_merge($this->_getParams,$getParams);
        if(!empty($this->_getParams)){
            $url = $this->getUrl();
            $query = http_build_query($this->_getParams);
            $url .= (strpos($url, "?") ? "&" : "?") . $query;
            curl_setopt($this->getCurl(),CURLOPT_URL,$url);
        }
    }
    public function addPostParams(array $postParams){
        $this->_postParams = array_merge($this->_postParams,$postParams);
    }
    public function addHeaders(array $headers){
        $this->_headers = array_merge($this->_headers,$headers);
    }
    public function addCookies(array $cookies){
        $this->_cookies = array_merge($this->_cookies,$cookies);
    }
    public function addFiles(array $files){
        $this->_files = array_merge($this->_files,$files);
    }
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
    /**
     * @param int $connectTimeout
     */
    public function setConnectTimeout(int $connectTimeout){
        $this->_connectTimeout = $connectTimeout;
    }
    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout){
        $this->_timeout = $timeout;
    }

}