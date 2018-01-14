<?php
define("APP_NAME","app_user");
define("ROOT_PATH", dirname(__FILE__,3));
try{
    require_once ROOT_PATH . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR ."Init.php";
    Init::run();
//    require_once ROOT_PATH . "/common/utils/Conf.php";
//    Utils_Conf::app()->get();
//    $config = ROOT_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . APP_NAME;
//    $app = new Yaf_Application($config.DIRECTORY_SEPARATOR.'application.ini');
//    $app->bootstrap()->run();
}catch (Exception $e){
    var_dump("IndexError:" . $e->getMessage());
}
