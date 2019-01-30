<?php
namespace vendor\guzzle;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use vendor\composer\Base;

class Client extends Base {
    const METHOD_GET = "GET";
    const METHOD_POST = "POST";
    protected static $_instance;

    protected $_service;//服务名称 关联配置文件
    protected $_baseUri;
    protected $_host;
    protected $_codeKey;
    protected $_msgKey = "msg";
    protected $_dataKey = "data";
    protected $_client;
    protected $_requestMethod  = "GET";
    protected $_requestUri;
    protected $_queryParams = [];
    protected $_postParams = [];
    protected $_bodyParams;
    protected $_jsonParams = [];
    protected $_headers = [];
    protected $_cookies = [];
    protected $_upload = [];
    protected $_connectTimeout = 1;//默认
    protected $_requestTimeout = 1.5;//请求超时时间默认1.5s

    protected $_successCode = null;
    protected $_onlyJsonResult = false;

    protected function init(){
        $config = $this->getServiceConfig($this->getService());
        $this->setBaseUri($config['baseUri'] ?? "");
        $this->setCodeKey($config['codeKey'] ?? "");
        $this->setDataKey($config['dataKey'] ?? "");
        $this->setMsgKey($config['msgKey'] ?? "");
        $this->setSuccessCode($config['successCode'] ?? "");
        $this->setConnectTimeout($config['connectTimeout'] ?? 1);
        $this->setRequestTimeout($config['requestTimeout'] ?? 1.5);
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
        $option = [
            'connect_timeout' => $this->getConnectTimeout(),
            'timeout'         => $this->getRequestTimeout(),
        ];
        if(!empty($this->getHeaders())){
            $option['headers'] = $this->getHeaders();
        }
        if(!empty($this->getQueryParams())){
            $option['query'] = $this->getQueryParams();
        }
        if(!empty($this->getPostParams())){
            $option['form_params'] = $this->getPostParams();
        }
        if(!empty($this->getBodyParams())){
            $option['body'] = $this->getBodyParams();
        }
        if(!empty($this->getJsonParams())){
            $option['json'] = $this->getJsonParams();
        }
        return $option;
    }

    /**
     * @return Result
     */
    protected function request(){
        $option = $this->getOption();
        $response = $this->getClient()->request($this->getRequestMethod(),$this->getRequestUrl(),$option);
        return $this->getResultObj($response);
    }

    private function getResultObj(Response $response){
        $result = new Result($this->getCodeKey(),$this->getDataKey(),$this->getMsgKey(),$this->getSuccessCode());
        $result->setResponse($response);
        return $result;
    }

    protected function getServiceConfig($configKey){
        if(empty($configKey)) return [];
        return \Config::getByPath(CONFIG_PATH . DIRECTORY_SEPARATOR . "service", $configKey);
    }

    /**获取请求的url，连带query 参数
     * @return string
     */
    private function getRequestUrl(){
        $queryParams = $this->getQueryParams();
        $uri = $this->getRequestUri();
        if(strpos('?',$uri) === false){
            $url = $uri . "?" . \GuzzleHttp\Psr7\build_query($queryParams);
        }else{
            $url = $uri . "&" . \GuzzleHttp\Psr7\build_query($queryParams);
        }
        return $url;
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
        $this->_queryParams = array_merge($this->_queryParams,$queryParams);
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
        $this->_postParams = array_merge($this->_postParams,$postParams);
    }

    /**
     * @return mixed
     */
    public function getBodyParams()
    {
        return $this->_bodyParams;
    }

    /**
     * @param $bodyParams
     * @throws \Exception
     */
    public function setBodyParams($bodyParams)
    {
        if(is_string($bodyParams) || is_resource($bodyParams) || $bodyParams instanceof StreamInterface){
            $this->_bodyParams = $bodyParams;
        }else{
            throw new \Exception("body param must be string|resource|Psr\Http\Message\StreamInterface");
        }
    }

    /**
     * @return array
     */
    public function getJsonParams(): array
    {
        return $this->_jsonParams;
    }

    /**
     * @param array $jsonParams
     */
    public function setJsonParams(array $jsonParams)
    {
        $this->_jsonParams = $jsonParams;
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
    public function getCodeKey()
    {
        return $this->_codeKey;
    }

    /**
     * @param mixed $codeKey
     */
    public function setCodeKey($codeKey)
    {
        $this->_codeKey = $codeKey;
    }

    /**
     * @return string
     */
    public function getMsgKey(): string
    {
        return $this->_msgKey;
    }

    /**
     * @param string $msgKey
     */
    public function setMsgKey(string $msgKey)
    {
        $this->_msgKey = $msgKey;
    }

    /**
     * @return string
     */
    public function getDataKey(): string
    {
        return $this->_dataKey;
    }

    /**
     * @param string $dataKey
     */
    public function setDataKey(string $dataKey)
    {
        $this->_dataKey = $dataKey;
    }

    /**
     * @return string
     */
    public function getSuccessCode(): string
    {
        return $this->_successCode;
    }

    /**
     * @param string $successCode
     */
    public function setSuccessCode(string $successCode)
    {
        $this->_successCode = $successCode;
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
        return (int)($this->_connectTimeout > 0 ? $this->_connectTimeout : 1);
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
        return (int)($this->_requestTimeout > 0 ? $this->_requestTimeout : 1);
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
     * @param string $method
     */
    public function setRequestUri($requestUri,$method="")
    {
        $this->_requestUri = $requestUri;
        $method = strtoupper($method);
        if(in_array($method,[self::METHOD_GET,self::METHOD_POST])){
            $this->setRequestMethod($method);
        }
    }

    /**
     * @return bool
     */
    public function isOnlyJsonResult(): bool
    {
        return $this->_onlyJsonResult;
    }

    /**
     * @param bool $onlyJsonResult
     */
    public function setOnlyJsonResult(bool $onlyJsonResult)
    {
        $this->_onlyJsonResult = $onlyJsonResult;
    }


    private function __construct(){}
    private function __clone(){}
}