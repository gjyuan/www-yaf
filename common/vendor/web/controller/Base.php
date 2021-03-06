<?php
namespace vendor\web\controller;
global $_ISAPI;//此处定义全局变量标识是否是API接口，仅限于本文件中使用，其他地方请勿引用并修改，否则后果自负
use \consts\ResponseCode;
class Base extends \Yaf\Controller_Abstract{
    const RESPONSE_CODE = "code";
    const RESPONSE_MSG = "msg";
    const RESPONSE_DATA = "data";
    const RESPONSE_IP = "ip";
    const RESPONSE_ID = "requestId";
    protected function init(){
        \Yaf\Dispatcher::getInstance()->autoRender(false);//确定关闭自动渲染
    }
    protected function setIsApi($isApi){
        global $_ISAPI;
        $_ISAPI = $isApi;
    }
    protected function isApi(){
        global $_ISAPI;
        return $_ISAPI;
    }

    protected function success($data = null,$msg = "success",$code = ResponseCode::RESPONSE_SUCCESS){
        $result = array(
            self::RESPONSE_CODE => $code,
            self::RESPONSE_DATA => $data,
            self::RESPONSE_MSG  => $msg,
            self::RESPONSE_IP   => isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : null,
        );
        $this->echoResponse($result);
    }
    protected function error($msg = "request failed", $code = ResponseCode::RESPONSE_ERROR, mixed $data = null){
        $result = array(
            self::RESPONSE_CODE => $code,
            self::RESPONSE_DATA => $data,
            self::RESPONSE_MSG  => $msg,
            self::RESPONSE_IP   => isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : null,
        );
        $this->echoResponse($result);
    }
    protected function echoResponse($data = null){
        ob_clean();
        $origin = isset($_SERVER["HTTP_ORIGIN"]) ? $_SERVER["HTTP_ORIGIN"] : "*";
        header('Access-Control-Allow-Origin:' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: ' . join(",", array(
                "DNT",
                "X-Mx-ReqToken",
                "Keep-Alive",
                "User-Agent",
                "X-Requested-With",
                "If-Modified-Since",
                "Cache-Control",
                "Content-Type",
                "Lianjia-App-Id",
                "Lianjia-App-Secret",
                "Lianjia-Access-Token",
            )));

        if (isset($_GET["callback"])) {
            $callback = preg_replace('/\W/i', '', $_GET["callback"]);
            if($callback) {
                header("Content-Type: application/javascript");
                header('Access-Control-Allow-Origin:*');
                echo "/**/{$callback}(".json_encode($data).")";
            }
        }else{
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        }
        exit;
    }

}