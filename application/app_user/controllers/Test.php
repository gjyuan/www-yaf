<?php
class TestController extends Web_Controller_TplBase {

    public function helloAction() {
        $this->getView()->assign('name',"gaojiyaun");
        $this->display("hello",['name'=>"gaojiyaun"]);
    }

}
