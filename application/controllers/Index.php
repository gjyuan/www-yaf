<?php
use vendor\web\controller\ApiBase;
class IndexController extends ApiBase {

    public function indexAction() {
        $this->success(Config::get('log'));
    }

}
