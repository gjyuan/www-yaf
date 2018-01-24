<?php

class Config {
    private $applicationPath;
    private $appConfPath;
    private $_config;
    private $fileMap;
    private static $_configApp;
    public function __construct(){
        if(defined("ROOT_PATH") && defined("APP_NAME")) {
            defined("APPLICATION_PATH") || define("APPLICATION_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . APP_NAME);
            defined("APP_CONF_PATH") || define("APP_CONF_PATH", APPLICATION_PATH . DIRECTORY_SEPARATOR . 'conf');
            defined("APP_MODE") || define("APP_MODE", get_cfg_var("yaf.environ"));
            $this->applicationPath = APPLICATION_PATH;
            $this->appConfPath = APP_CONF_PATH;
        }else{
            $constName = defined("APP_NAME") ? "APP_NAME" : "ROOT_PATH";
            throw new ConfigException("Make sure you have defined " . $constName . " value");
        }
    }

    /**对外输出接口 应用的路径path
     * @return string
     */
    public static function getApplicationPath(){
        return self::app()->applicationPath;
    }

    /**对外输出配置
     * @param string $fileKey
     * @param string $name
     * @return mixed
     */
    public static function get($fileKey="application",$name=""){
        $conf = self::app()->getConfMap($fileKey);
        return self::app()->getValFromArrayByName($conf,$name);
    }

    /**根据全部变量的值 application.pool.value 获取配置文件的值
     * @param string $fullName
     * @return array|string
     */
    public static function getValue($fullName=""){
        list($fileKey,$name) = explode(',',$fullName,2);
        if(empty($fileKey) || empty($name)) return [];
        $conf = self::app()->getConfMap($fileKey);
        return self::app()->getValFromArrayByName($conf,$name);
    }
    //单例模式
    private static function app(){
        if(empty(self::$_configApp)){
            self::$_configApp = new self();
        }
        return self::$_configApp;
    }
    //获取配置全局array
    private function getConfMap($fileKey=""){
        $fileKey = !empty($fileKey) ? $fileKey : "application";//默认获取application的配置
        if(!empty($this->_config[$fileKey])){
            return $this->_config[$fileKey];
        }
        $confFiles = $this->getConfFiles($fileKey);
        $config = [];
        foreach($confFiles as $f){
            if(!isset($this->fileMap[$f])){
                $confIni = new Yaf_Config_Ini($f);
                $this->fileMap[$f] = $confIni->get(APP_MODE)->toArray();
            }
            $config[] = $this->fileMap[$f];
        }
        $config = $this->setServerConfig($this->merge(...$config));
        $this->_config[$fileKey] = $config;
        return $this->_config[$fileKey];
    }
    //根据名字获取数组的值
    private function getValFromArrayByName($conf,$name){
        if(empty($name)) return $conf;
        $keyArr = explode(".",$name);
        $val = $conf;
        foreach($keyArr as $k){
            if(!isset($val[$k])){
                $val = ""; break;
            }
            $val = $val[$k];
        }
        return $val;
    }
    //根据fileKey获取配置文件列表
    private function getConfFiles($fileKey){
        return array_filter([$this->getCommonConfigFile($fileKey),$this->getAppConfigFile($fileKey)]);
    }
    //获取通用配置的文件
    private function getCommonConfigFile($fileKey){
        if(defined("ROOT_PATH")){
            $file = ROOT_PATH . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR . $fileKey . ".ini";
            return is_file($file) ? $file : "";
        }
        return "";
    }
    //获取应用下的配置
    private function getAppConfigFile($fileKey){
        if(empty($fileKey)) return "";
        $file = $this->appConfPath . DIRECTORY_SEPARATOR . $fileKey . ".ini";
        return is_file($file) ? $file : "";
    }
    //多配置文件配置合并
    private function merge(){
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($res[$k])) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }
    //替换 $_SERVER （fpm）中的配置
    private function setServerConfig($config=[]){
        foreach($config as $k=>$c){
            if(is_array($c)){
                $config[$k] = $this->setServerConfig($c);
            }elseif(is_string($c) && strpos($c,"MATRIX_") !== false){
                $config[$k] = isset($_SERVER[$c]) ? $_SERVER[$c] : $c;
            }
        }
        return $config;
    }
    //采用数组引用的方式会改变数组指针的指向，yaf底层是c语言实现，指针错乱会导致不可预知的异常，后续待查
    private function setServerConfigBak(&$config = []){
        $callback = function(&$value){
            if(is_string($value) && strpos($value,"MATRIX_") !== false){
                $value = isset($_SERVER[$value]) ? $_SERVER[$value] : $value;
            }
        };
        array_walk_recursive($config, $callback);
    }
}

class ConfigException extends Exception{
}
