<?php
class Utils_Curl {
    public static function get($url,$params=[],array $header = [],$withCookie = true){
        $curl = new Vendor_Curl_Curl($url);
        $curl->addGetParams($params);
        $curl->addHeaders($header);
        $withCookie && $curl->addCookies($_COOKIE);
        list($st,$result) = $curl->httpExecute();
        return $result;
    }
    public static function post($url,$params=[],array $header = [],$withCookie = true){

    }
}
