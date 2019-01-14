<?php
namespace vendor\guzzle;
use vendor\composer\Base;
class Client extends Base {
    protected static $_instance;

    protected $_service;//服务名称 关联配置文件
    protected $_baseUri;
    protected $_client;
    protected $_requestMethod  = "GET";
    protected $_headers = [];
    protected $_cookies = [];
    protected $_upload = [];
    protected $_connectTimeout = 1000;//
    protected $_requestTimeout = 1500;//请求超时时间默认1.5s

    protected function init(){}
    public static function app(){
        $class = get_called_class();
        if(empty(self::$_instance[$class])){
            $instance = new static();
            $instance->init();
            self::$_instance[$class] = $instance;
        }
        return self::$_instance[$class];
    }
    protected function getClient(){
        if(!$this->_client instanceof \GuzzleHttp\Client){
            $this->_client = new \GuzzleHttp\Client();
        }
        return $this->_client;
    }

    protected function send(){

    }
    protected function getServiceConfig($configKey){
        if(empty($configKey)) return [];
        return \Config::getByPath(CONFIG_PATH . DIRECTORY_SEPARATOR . "service", $configKey);
    }
    /**
     * @return mixed
     */
    protected function getBaseUri()
    {
        return $this->_baseUri;
    }

    /**
     * @param mixed $baseUri
     */
    protected function setBaseUri($baseUri)
    {
        $this->_baseUri = $baseUri;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    private function __construct(){}
    private function __clone(){}
}