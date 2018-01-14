<?php
class IndexController extends Web_Controller_ApiBase {

    public function indexAction() {
        var_dump(Config::get());exit;
        $this->success("hello world");
    }

}
