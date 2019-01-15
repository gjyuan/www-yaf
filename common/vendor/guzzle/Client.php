<?php
namespace vendor\guzzle;
use vendor\composer\Base;
class Client extends Base {
    protected static $_instance;

    protected $_service;//服务名称 关联配置文件
    protected $_baseUri;
    protected $_host;
    protected $_codeField;
    protected $_msgField;
    protected $_dataField;
    protected $_client;
    protected $_requestMethod  = "GET";
    protected $_headers = [];
    protected $_cookies = [];
    protected $_upload = [];
    protected $_connectTimeout = 1000;//
    protected $_requestTimeout = 1500;//请求超时时间默认1.5s

    protected function init(){
        $config = $this->getServiceConfig($this->getService());
        $this->setBaseUri($config['baseUri'] ?? "");
        $this->setCodeField($config['codeField']);
    }

    /**
     * @return static
     */
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

    protected function getOption(){
        $option = [];

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

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @return mixed
     */
    public function getCodeField()
    {
        return $this->_codeField;
    }

    /**
     * @param mixed $codeField
     */
    public function setCodeField($codeField)
    {
        $this->_codeField = $codeField;
    }

    /**
     * @return mixed
     */
    public function getMsgField()
    {
        return $this->_msgField;
    }

    /**
     * @param mixed $msgField
     */
    public function setMsgField($msgField)
    {
        $this->_msgField = $msgField;
    }

    /**
     * @return mixed
     */
    public function getDataField()
    {
        return $this->_dataField;
    }

    /**
     * @param mixed $dataField
     */
    public function setDataField($dataField)
    {
        $this->_dataField = $dataField;
    }

    /**
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->_requestMethod;
    }

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod(string $requestMethod)
    {
        $this->_requestMethod = $requestMethod;
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->_connectTimeout;
    }

    /**
     * @param int $connectTimeout
     */
    public function setConnectTimeout(int $connectTimeout)
    {
        $this->_connectTimeout = $connectTimeout;
    }

    /**
     * @return int
     */
    public function getRequestTimeout(): int
    {
        return $this->_requestTimeout;
    }

    /**
     * @param int $requestTimeout
     */
    public function setRequestTimeout(int $requestTimeout)
    {
        $this->_requestTimeout = $requestTimeout;
    }


    private function __construct(){}
    private function __clone(){}
}