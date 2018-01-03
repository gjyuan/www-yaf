<?php
ini_set("yaf.use_namespace",1);
ini_set("yaf.namespace",1);
define("APP_PATH", realpath(dirname(__FILE__) . '/../'));
$app = new Yaf_Application(APP_PATH . "/conf/application.ini", ini_get('yaf.environ'));
$app->run();