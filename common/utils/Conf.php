<?php
class Utils_Conf {
    private $applicationPath;
    private $appConfPath;
    private $_config;
    private $fileMap;
    private static $_configApp;
    public function __construct(){
        if(defined("ROOT_PATH") && defined("APP_NAME")) {
            defined("APPLICATION_PATH") || define("APPLICATION_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . APP_NAME);
            defined("APP_CONF_PATH") || define("APP_CONF_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . APP_NAME);
            defined("APP_MODE") || define("APP_MODE", get_cfg_var("yaf.environ"));
            $this->applicationPath = APPLICATION_PATH;
            $this->appConfPath = APP_CONF_PATH;
        }else{
            $constName = defined("APP_NAME") ? "APP_NAME" : "ROOT_PATH";
            throw new ConfigException("Make sure you have defined " . $constName . " value");
        }
    }

    public static function app(){
        if(empty(self::$_configApp)){
            self::$_configApp = new self();
        }
        return self::$_configApp;
    }

    public static function getApplicationPath(){
        return self::app()->applicationPath;
    }

    public static function getAppConfPath(){
        return self::app()->appConfPath;
    }

    public static function get($key,$name){
        self::app()->getConf();
    }
    private function getConf($key=""){
        $app = self::app();
        $key = !empty($key) ? $key : "application";//默认获取application的配置
        if(!empty($app->_config[$key])){
            return $app->_config[$key];
        }
        $confFiles = $app->getConfFiles($key);
        $config = [];
        foreach($confFiles as $f){
            if(!isset($app->fileMap[$f])){
                $confIni = new Yaf_Config_Ini($f);
                $app->fileMap[$f] = $confIni->get(APP_MODE)->toArray();
            }
            $config[] = $app->fileMap[$f];
        }
        $app->_config[$key] = $app->merge(...$config);
        return $app->_config[$key];
    }

    private function getConfFiles($key){
        $app = self::app();
        return array_filter([$app->getCommonConfigFile($key),$app->getAppConfigFile($key)]);
    }

    private function getCommonConfigFile($key){
        if(defined("ROOT_PATH")){
            $file = ROOT_PATH . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR . $key . ".ini";
            return is_file($file) ? $file : "";
        }
        return "";
    }
    private function getAppConfigFile($key){
        if(empty($key)) return "";
        $file = self::getAppConfPath() . DIRECTORY_SEPARATOR . $key . ".ini";
        return is_file($file) ? $file : "";
    }

    private static function merge(){
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
}

class ConfigException extends Exception{
}
