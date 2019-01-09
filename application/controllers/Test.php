<?php
use vendor\web\controller\TplBase;
class TestController extends TplBase {
    public function helloAction() {
        $this->display("hello",['name'=>"gaojiyaun",'list'=>['ddd','sss','qqqq','dfghj']]);
    }

}
