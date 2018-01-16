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
        defined("APPLICATION_PATH") || define("APPLICATION_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . APP_NAME);
        defined("APP_MODE") || define("APP_MODE", get_cfg_var("yaf.environ"));
        return true;
    }

    private static function runYaf($bootstrap = true){
        $config = Config::get();
        $yaf = new Yaf_Application($config);
        if($bootstrap){
            $yaf->bootstrap()->run();
        }else{
            $yaf->run();
        }
        return true;
    }

    private static function getAppName()
    {
        $app_name = null;
        if(PHP_SAPI != 'cli') {//CGI
            $script = str_replace(['/','\\'],DIRECTORY_SEPARATOR ,rtrim($_SERVER['SCRIPT_FILENAME'],'/\\'));
            if(strpos($script, ROOT_PATH.DIRECTORY_SEPARATOR) === 0){
                $script = substr($script, strlen(ROOT_PATH)+1);
            }
            $script = explode(DIRECTORY_SEPARATOR, $script);
            if(count($script) == 3 && $script[2] == 'index.php')
            {
                $app_name = $script[1];
            }
        } else {
            $file = $_SERVER['argv'][0];
            if($file{0} != '/') {
                $cwd = getcwd();
                $full_path = realpath($file);
            } else {
                $full_path = $file;
            }
            if(strpos($full_path, APP_PATH.'/') === 0) {
                $s = substr($full_path, strlen(APP_PATH)+1);
                if(($pos = strpos($s, '/')) > 0)
                {
                    $app_name = substr($s, 0, $pos);
                }
            }
        }
        return $app_name;
    }
}