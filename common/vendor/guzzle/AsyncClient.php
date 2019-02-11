<?php
namespace vendor\guzzle;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use vendor\composer\Base;

class AsyncClient extends Base {
    private $promises = [];
    private static $_instance = [];
    /**
     * @return static
     */
    public static function app(){
        $class = get_called_class();
        if(empty(self::$_instance[$class])){
            $instance = new static();
            self::$_instance[$class] = $instance;
        }
        return self::$_instance[$class];
    }


    public function request(){
        if(empty($this->promises)){
            return [];
        }
        $result = Promise\unwrap($this->promises);
        $data = [];
        foreach($result as $key=>$response){
            $data[$key] = \GuzzleHttp\json_decode($response->getBody()->getContents());
        }
        return $data;
    }
    private function getResultObj(Response $response){
        $result = new Result($this->getCodeKey(),$this->getDataKey(),$this->getMsgKey(),$this->getSuccessCode());
        $result->setResponse($response);
        return $result;
    }

    public function addPromises($key, PromiseInterface $promise){
        $this->promises[$key] = $promise;
    }

    /**
     * @return array
     */
    public function getPromises(): array
    {
        return $this->promises;
    }

    /**
     * @param array $promises
     */
    public function setPromises(array $promises)
    {
        $this->promises = $promises;
    }

}