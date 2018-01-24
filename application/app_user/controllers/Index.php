<?php
class IndexController extends Web_Controller_ApiBase {

    public function indexAction() {
        var_dump(new Vendor_Smarty_Adapter());
        $this->success(Config::get());
    }

}
