<?php
class TestController extends Web_Controller_TplBase {

    public function helloAction() {
        echo "ddd<br>";
        $this->getView()->assign('name',"gaojiyaun");
        $this->display("hello",['name'=>"gaojiyaun",'list'=>['ddd','sss','qqqq','dfghj']]);
    }

}
