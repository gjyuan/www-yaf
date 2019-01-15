<?php
class Config {
    private $appConfPath;
    private $_config;
    private $fileMap;
    private static $_configApp;
    private function __construct(){
        if(defined("CONFIG_PATH")) {
            defined("APP_MODE") || define("APP_MODE", get_cfg_var("yaf.environ"));
            $this->appConfPath = CONFIG_PATH;
        }else{
            throw new ConfigException("Make sure you have defined CONFIG_PATH value");
        }
    }
    private static function app(){
        if(empty(self::$_configApp)){
            self::$_configApp = new self();
        }
        return self::$_configApp;
    }

    /**对外输出配置
     * 获取配置信息 默认输出application的信息
     * 接收两种类型参数：
     * 一个参数：string configFileName.key.key eg:application.application.directory
     * 两个参数：string configFileName , string key.key  eg:get('application','application.directory')
     * @return string
     */
    public static function get(){
        $argsNum = func_num_args();
        if($argsNum == 1){
            $args = explode('.',func_get_arg(0),2);
            $configFileName = $args[0] ?? "";
            $name = $args[1] ?? "";
            if(empty($configFileName)) return "";
        }elseif($argsNum == 2){
            $configFileName = func_get_arg(0);
            $name = func_get_arg(1);
        }else{
            return "";
        }
        $conf = self::app()->getConfigMap($configFileName);
        return self::app()->getValFromArrayByName($conf,$name);
    }

    /**指定配置文件目录，读取该目录下的配置信息
     * @param string $path 建议用绝对路径
     * @param string $configKey 配置信息 eg：neirong-api.url
     * @return string
     */
    public static function getByPath($path,$configKey){
        $args = explode('.',$configKey,2);
        $configFileName = $args[0] ?? "";
        $name = $args[1] ?? "";
        if(empty($configFileName)) return "";
        $conf = self::app()->getConfigMap($configFileName,$path);
        return self::app()->getValFromArrayByName($conf,$name);
    }

    //获取配置全局array
    private function getConfigMap($fileName = "", $configPath = CONFIG_PATH){
        $fileName = !empty($fileName) ? $fileName : "application";//默认获取application的配置
        if(!empty($this->_config[$fileName])){
            return $this->_config[$fileName];
        }
        $confFile = $this->getConfFile($fileName,$configPath);
        if(!isset($this->fileMap[$confFile])){
            $confIni = new \Yaf\Config\Ini($confFile);
            $this->fileMap[$confFile] = $confIni->get(APP_MODE)->toArray();
        }
        $config = $this->setServerConfig($this->fileMap[$confFile]);
        $this->_config[$fileName] = $config;
        return $this->_config[$fileName];
    }

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
    //根据fileKey获取配置文件列表
    private function getConfFile($fileKey, $path = CONFIG_PATH){
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileKey . ".ini";
    }
    private function getConfFiles($fileKey){
        return [CONFIG_PATH . DIRECTORY_SEPARATOR . $fileKey . ".ini"];
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
//    private function getConfMap($fileKey=""){
//        $fileKey = !empty($fileKey) ? $fileKey : "application";//默认获取application的配置
//        if(!empty($this->_config[$fileKey])){
//            return $this->_config[$fileKey];
//        }
//        $confFiles = $this->getConfFiles($fileKey);
//        $config = [];
//        foreach($confFiles as $f){
//            if(!isset($this->fileMap[$f])){
//                $confIni = new \Yaf\Config\Ini($f);
//                $this->fileMap[$f] = $confIni->get(APP_MODE)->toArray();
//            }
//            $config[] = $this->fileMap[$f];
//        }
//        $config = $this->setServerConfig($this->merge(...$config));
//        $this->_config[$fileKey] = $config;
//        return $this->_config[$fileKey];
//    }
//根据名字获取数组的值
class ConfigException extends Exception{
}
