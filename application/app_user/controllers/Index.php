<?php
class IndexController extends Web_Controller_ApiBase {

    public function indexAction() {
        $this->success(Config::get());
    }

}
