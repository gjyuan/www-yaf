<?php
namespace vendor\guzzle;
use vendor\composer\Base;
class Client extends Base {
    const METHOD_GET = "GET";
    const METHOD_POST = "POST";
    protected static $_instance;

    protected $_service;//服务名称 关联配置文件
    protected $_baseUri;
    protected $_host;
    protected $_codeField;
    protected $_msgField;
    protected $_dataField;
    protected $_client;
    protected $_requestMethod  = "GET";
    protected $_requestUri;
    protected $_queryParams = [];
    protected $_postParams = [];
    protected $_headers = [];
    protected $_cookies = [];
    protected $_upload = [];
    protected $_connectTimeout = 1;//
    protected $_requestTimeout = 1.5;//请求超时时间默认1.5s

    protected function init(){
        $config = $this->getServiceConfig($this->getService());
        $this->setBaseUri($config['baseUri'] ?? "");
        $this->setCodeField($config['codeField'] ?? "");
        $this->setDataField($config['dataField'] ?? "");
        $this->setMsgField($config['msgField'] ?? "");
        $this->setConnectTimeout($config['connectTimeout'] ?? 1);
        $this->setConnectTimeout($config['requestTimeout'] ?? 1.5);
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
            $config = ['base_uri'=>$this->getBaseUri()];
            $this->_client = new \GuzzleHttp\Client($config);
        }
        return $this->_client;
    }

    protected function getOption(){
        $option = [];
        if(!empty($this->getQueryParams())){
            $option['query'] = $this->getQueryParams();
        }
        return $option;
    }

    protected function send(){
        $option = $this->getOption();
        $response = $this->getClient()->request($this->getRequestMethod(),$this->getRequestUri(),$option);
        return $response;
    }

    protected function getServiceConfig($configKey){
        if(empty($configKey)) return [];
        return \Config::getByPath(CONFIG_PATH . DIRECTORY_SEPARATOR . "service", $configKey);
    }

    /**
     * @return array
     */
    protected function getQueryParams(): array
    {
        return $this->_queryParams;
    }

    /**
     * @param array $queryParams
     */
    protected function setQueryParams(array $queryParams)
    {
        $this->_queryParams = $queryParams;
    }

    /**
     * @return array
     */
    protected function getPostParams(): array
    {
        return $this->_postParams;
    }

    /**
     * @param array $postParams
     */
    protected function setPostParams(array $postParams)
    {
        $this->_postParams = $postParams;
    }

    /**
     * @return array
     */
    protected function getHeaders(): array
    {
        return $this->_headers;
    }

    /**
     * @param array $headers
     */
    protected function setHeaders(array $headers)
    {
        $this->_headers = $headers;
    }

    /**
     * @return array
     */
    protected function getCookies(): array
    {
        return $this->_cookies;
    }

    /**
     * @param array $cookies
     */
    protected function setCookies(array $cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * @return array
     */
    protected function getUpload(): array
    {
        return $this->_upload;
    }

    /**
     * @param array $upload
     */
    protected function setUpload(array $upload)
    {
        $this->_upload = $upload;
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
    protected function getService()
    {
        return $this->_service;
    }

    /**
     * @param mixed $service
     */
    protected function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * @return mixed
     */
    protected function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $host
     */
    protected function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @return mixed
     */
    protected function getCodeField()
    {
        return $this->_codeField;
    }

    /**
     * @param mixed $codeField
     */
    protected function setCodeField($codeField)
    {
        $this->_codeField = $codeField;
    }

    /**
     * @return mixed
     */
    protected function getMsgField()
    {
        return $this->_msgField;
    }

    /**
     * @param mixed $msgField
     */
    protected function setMsgField($msgField)
    {
        $this->_msgField = $msgField;
    }

    /**
     * @return mixed
     */
    protected function getDataField()
    {
        return $this->_dataField;
    }

    /**
     * @param mixed $dataField
     */
    protected function setDataField($dataField)
    {
        $this->_dataField = $dataField;
    }

    /**
     * @return string
     */
    protected function getRequestMethod(): string
    {
        return $this->_requestMethod;
    }

    /**
     * @param string $requestMethod
     */
    protected function setRequestMethod(string $requestMethod)
    {
        $this->_requestMethod = $requestMethod;
    }

    /**
     * @return int
     */
    protected function getConnectTimeout(): int
    {
        return $this->_connectTimeout;
    }

    /**
     * @param int $connectTimeout
     */
    protected function setConnectTimeout(int $connectTimeout)
    {
        $this->_connectTimeout = $connectTimeout;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout(): int
    {
        return $this->_requestTimeout;
    }

    /**
     * @param int $requestTimeout
     */
    protected function setRequestTimeout(int $requestTimeout)
    {
        $this->_requestTimeout = $requestTimeout;
    }

    /**
     * @return mixed
     */
    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    /**
     * @param mixed $requestUri
     */
    public function setRequestUri($requestUri)
    {
        $this->_requestUri = $requestUri;
    }


    private function __construct(){}
    private function __clone(){}
}