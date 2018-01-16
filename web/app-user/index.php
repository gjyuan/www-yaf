<?php
define("APP_NAME","app_user");
define("ROOT_PATH", dirname(__FILE__,3));
try{
    include ROOT_PATH . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR ."Init.php";
    Init::run();
}catch (Exception $e){
    var_dump("IndexError:" . $e->getMessage());
}
