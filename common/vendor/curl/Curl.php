<?php
class Vendor_Curl_Curl{
    private $_getParams = [];
    private $_postParams = [];
    private $_headers = [];
    private $_cookies = [];
    private $_files = [];
    private $_connectTimeout = 2;
    private $_timeout = 3;
    private $_isSington = true;
    private $_curl;
    private $_userAgent;
    private $_requestUrl;
    public function __construct($url=""){
        $this->_requestUrl = $url;
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
        $url = $this->getUrl();
        if(!empty($this->_getParams)){
            $query = http_build_query($this->_getParams);
            $url .= (strpos($url, "?") ? "&" : "?") . $query;
        }
        if(!empty($this->_postParams) && is_array($this->_postParams)){
            $opt[CURLOPT_POST] = true;
            $opt[CURLOPT_POSTFIELDS] = http_build_query($this->_postParams);
        }
        if(!empty($this->_headers)){
            $opt[CURLOPT_HTTPHEADER] = $this->_headers;
        }
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

    /**
     * @param bool $isSington
     */
    public function setIsSington(bool $isSington)
    {
        $this->_isSington = $isSington;
    }

    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function httpExecute($url=""){
        try{
            $ch = $this->__curlInit();
            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno == CURLE_COULDNT_CONNECT) {
                $msg = "Curl error [Can not connect " . $url . " error:" . $errno . '-' . curl_error($ch) ."]";
                return [false,$msg];
            }
            if ($errno != CURLE_OK) {
                $msg = "Curl error [errorno:{$errno}-" .curl_error($ch)."]";
                return [false,$msg];
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code != 200) {
                $msg = "Curl error [code:{$code}]";
                return [false,$msg];
            }
            $data = is_string($result) ? json_decode($result, true) : $result;
            return [true,$data];
        }catch (Exception $e){
            return [false,$e->getMessage()];
        }

    }

}