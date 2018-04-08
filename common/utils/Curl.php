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
    public static function multiCurl(array $requestArr){
        try{
            if(empty($requestArr)) return [];
            $multiCurl = new Vendor_Curl_MultiCurl();
            foreach($requestArr as $key=>$rqMap){
                if(isset($rqMap['url']) && !empty($rqMap['url'])){
                    $url = $rqMap['url'];
                    $getParams = isset($rqMap['get']) ? $rqMap['get'] : [];
                    $postParams = isset($rqMap['post']) ? $rqMap['post'] : [];
                    $cookieParams = isset($rqMap['cookie']) ? $rqMap['cookie'] : [];
                    $headerParams = isset($rqMap['header']) ? $rqMap['header'] : [];
                    $multiCurl->addRequestWithKey($key,$url,$getParams,$postParams,$headerParams,$cookieParams);
                }else{
                    throw new Exception("Multi curl params must contain url value".var_export($requestArr));
                }
            }
            return $multiCurl->execute();
        }catch (Exception $e){
            var_export($e->getMessage());
            return [false,[]];
        }
    }
}
