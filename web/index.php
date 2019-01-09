<?php
define("ROOT_PATH", dirname(__FILE__,2));
try{
    include ROOT_PATH . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR ."Init.php";
    Init::run();
}catch (Exception $e){
    var_dump("IndexError:" . $e->getMessage());
}
