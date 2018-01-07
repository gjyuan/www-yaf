<?php
class IndexController extends Yaf_Controller_Abstract {

    public function indexAction() {
        $data = Yaf_Dispatcher::getInstance()->getRouter()->getCurrentRoute();
        var_dump($data);
        echo "hello world";
    }

}
