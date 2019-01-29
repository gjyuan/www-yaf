<?php
namespace vendor\guzzle;
use GuzzleHttp\Psr7\Response;
class Result {
    private $_success = true;
    private $_httpCode;
    private $_codeKey;
    private $_dataKey = "data";
    private $_msgKey = "msg";
    private $_successCode = null;
    private $_code;
    private $_data;
    private $_msg;
    private $_response;
    private $_contents;
    private $_isJson = true;
    private $_noFormat = false;
    public function __construct($codeKey,$dataKey,$msgKey,$successCode){
        $this->_codeKey = $codeKey;
        $this->_dataKey = $dataKey;
        $this->_msgKey = $msgKey;
        $this->_successCode = $successCode;
    }

    public function getResult(){
        if($this->isSuccess()){
            if($this->isJson()){
                return \GuzzleHttp\json_decode($this->getContents(),true);
            }else{
                return $this->getContents();
            }
        }else{
            return false;
        }
    }
    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse(Response $response)
    {
        $this->_response = $response;
        try{
            $httpCode = $response->getStatusCode();
            $this->setHttpCode($httpCode);
            if($httpCode == 200){
                $this->setContents($response->getBody()->getContents());
                $result = \GuzzleHttp\json_decode($this->getContents(),true);
                if(empty($this->getCodeKey()) || $this->getSuccessCode() === null){
                    $this->setNoFormat(true);
                }else{
                    $this->setCode($result[$this->getCodeKey()] ?? "");
                    $this->setData($result[$this->getDataKey()] ?? []);
                    $this->setMsg($result[$this->getMsgKey()] ?? "");
                    if($this->getCode() != $this->getSuccessCode()){
                        $this->setSuccess(false);
                        //TODO 记录失败日志
                    }
                }
            }
        }catch (\Exception $e){
            //非json格式
            $this->setIsJson(false);
            $this->setNoFormat(true);
        }
    }

    /**
     * @return mixed
     */
    protected function getContents()
    {
        return $this->_contents;
    }

    /**
     * @param mixed $contents
     */
    protected function setContents($contents)
    {
        $this->_contents = $contents;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->_success;
    }

    /**
     * @param bool $success
     */
    protected function setSuccess(bool $success)
    {
        $this->_success = $success;
    }

    /**
     * @return mixed
     */
    protected function isJson()
    {
        return $this->_isJson;
    }

    /**
     * @param mixed $isJson
     */
    protected function setIsJson($isJson)
    {
        $this->_isJson = $isJson;
    }

    /**
     * @return bool
     */
    protected function isNoFormat(): bool
    {
        return $this->_noFormat;
    }

    /**
     * @param bool $noFormat
     */
    protected function setNoFormat(bool $noFormat)
    {
        $this->_noFormat = $noFormat;
    }

    /**
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    /**
     * @param mixed $httpCode
     */
    protected function setHttpCode($httpCode)
    {
        $this->_httpCode = $httpCode;
        if($httpCode != 200){
            $this->setSuccess(false);
            $this->setData([]);
            $this->setMsg("HTTP ERROR : http_code={$httpCode}");
        }
    }

    /**
     * @return mixed
     */
    protected function getCodeKey()
    {
        return $this->_codeKey;
    }

    /**
     * @param mixed $codeKey
     */
    protected function setCodeKey($codeKey)
    {
        $this->_codeKey = $codeKey;
    }

    /**
     * @return string
     */
    protected function getDataKey(): string
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
    public function getMsgKey(): string
    {
        return $this->_msgKey;
    }

    /**
     * @param string $msgKey
     */
    protected function setMsgKey(string $msgKey)
    {
        $this->_msgKey = $msgKey;
    }

    /**
     * @return mixed
     */
    public function getSuccessCode()
    {
        return $this->_successCode;
    }

    /**
     * @param mixed $successCode
     */
    protected function setSuccessCode($successCode)
    {
        $this->_successCode = $successCode;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @param mixed $code
     */
    protected function setCode($code)
    {
        $this->_code = $code;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        if($this->isNoFormat()){
            return $this->getResult();
        }else{
            return $this->_data;
        }
    }

    /**
     * @param mixed $data
     */
    protected function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->_msg;
    }

    /**
     * @param mixed $msg
     */
    protected function setMsg($msg)
    {
        $this->_msg = $msg;
    }

}