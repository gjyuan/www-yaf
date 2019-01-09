<?php
use Web\Controller\ApiBase;
class IndexController extends ApiBase {

    public function indexAction() {
        var_dump(new Vendor_Smarty_Adapter());
        $this->success(Config::get());
    }

}
