<?php

class Web_Controller_TplBase extends Web_Controller_Base{
    private $__smarty;
    private $__tplPath = null;
    protected function init(){
        parent::init();
        $this->setIsApi(false);
        $this->setTplPath(APPLICATION_PATH . DIRECTORY_SEPARATOR ."views");
    }
    private function _getSmarty(){
        if(empty($this->__smarty)){
            $this->__smarty = new Vendor_Smarty_Adapter($this->getTplPath(),Config::get('smarty'));
        }
        return $this->__smarty;
    }
    protected function setTplPath($tplPath = APPLICATION_PATH . DIRECTORY_SEPARATOR ."views"){
        if(is_dir($tplPath)){
            $this->__tplPath = rtrim($tplPath,'\\/') . DIRECTORY_SEPARATOR;
        }else{
            throw new Exception("Template path: {$tplPath} is not exist");
        }
    }
    protected function getTplPath(){
        return !empty($this->__tplPath) ? $this->__tplPath : "./";//为空则返回当前路径
    }
    private function getTplPostfix(){
        $postfix = Config::get("application",'application.view.ext');
        return !empty($postfix) ? $postfix : "phtml";
    }
    private function getTplFile($tpl=""){
        if(strpos($tpl,'.')=== false){//如果不存在 . 则认为没有扩展名，采用系统设定的扩展名
            $tpl .= "." . $this->getTplPostfix();
        }
        if(strpos($tpl,'/') === 0){//如果以'/'开头的模板路径，直接默认在view路径下，否则对应到其controller目录下
            $tplFile = $this->getTplPath() . $tpl;
        }else{
            $tplFile = $this->getTplPath() . strtolower($this->getRequest()->getControllerName()) . DIRECTORY_SEPARATOR . $tpl;
        }
        if(is_file($tplFile)) {
            return $tplFile;
        }else{
            throw new Exception("Missing template {$tplFile}");
        }
    }
    protected function display($tplName, $params = array()){
        $tpl = $this->getTplFile($tplName);
        $smarty = $this->_getSmarty();
        $smarty->display($tpl,$params);
    }

}