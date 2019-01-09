<?php
include "Config.php";
class Init{
    public static function run($bootstrap = true){
        //初始化常量
        self::initConsts();
        //Yaf 初始化
        self::runYaf($bootstrap);
    }

    private static function initConsts(){
        defined("ROOT_PATH") || define("ROOT_PATH", dirname(__FILE__));
        defined("APPLICATION_PATH") || define("APPLICATION_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
        defined("CONFIG_PATH") || define("CONFIG_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);
        defined("APP_MODE") || define("APP_MODE", get_cfg_var("yaf.environ"));
        return true;
    }

    private static function runYaf($bootstrap = true){
        $config = Config::get("application");
        $yaf = new Yaf\Application($config);
//        $yaf = new Yaf_Application(APPLICATION_PATH."/conf/application.ini");
        if($bootstrap){
            $yaf->bootstrap()->run();
        }else{
            $yaf->run();
        }
        return true;
    }
}