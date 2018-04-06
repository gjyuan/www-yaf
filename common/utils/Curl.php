<?php
class Utils_Curl {
    public static function get($url,$params=[],array $header = [],$withCookie = true){
        $curl = new Vendor_Curl_Curl($url);
        $curl->addGetParams($params);
        $curl->addHeaders($header);
        $withCookie && $curl->addCookies($_COOKIE);
        list($st,$result) = $curl->httpExecute();
        return $st ? $result : false;
    }
    public static function post($url,$params=[],array $header = [],$withCookie = true){
        $curl = new Vendor_Curl_Curl($url);
        $curl->addPostParams($params);
        $curl->addHeaders($header);
        $withCookie && $curl->addCookies($_COOKIE);
        list($st,$result) = $curl->httpExecute();
        return $result;
    }
    public static function forward(){
        $multiCurl = new Vendor_Curl_MultiCurl();
        $multiCurl->addRequest("http://localhost");
        $res = $multiCurl->execute();
        echo json_encode($res);exit;
    }
}
