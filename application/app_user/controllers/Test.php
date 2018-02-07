<?php
class TestController extends Web_Controller_TplBase {

    public function helloAction() {
        $this->display("hello",['name'=>"gaojiyaun",'list'=>['ddd','sss','qqqq','dfghj']]);
    }

}
