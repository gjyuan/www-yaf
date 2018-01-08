<?php
class Vendor_Router_AppRouter implements Yaf_Route_Interface{
    private $_applicationDirectory;
    private $_defaultController;
    private $_defaultAction;

    private function getApplicationDirectory(){
        if(empty($this->_applicationDirectory)){
            $this->_applicationDirectory = Yaf_Application::app()->getConfig()->get("application.directory");
        }
        return $this->_applicationDirectory;
    }

    private function getDefaultController(){
        if(empty($this->_defaultController)){
            $controller = Yaf_Application::app()->getConfig()->get("application.dispatcher.defaultController");
            $this->_defaultController = empty($controller) ? "Index" : $controller;
        }
        return $this->_defaultController;
    }

    private function getDefaultAction(){
        if(empty($this->_defaultAction)){
            $action = Yaf_Application::app()->getConfig()->get("application.dispatcher.defaultAction");
            $this->_defaultAction = empty($action) ? "index" : $action;
        }
        return $this->_defaultAction;
    }

    private function getControllerName($controllerArr = []){
        $controllerArr = is_array($controllerArr) ? $controllerArr : [$controllerArr];
        foreach($controllerArr as &$c){
            $c = ucfirst($c);
        }
        $controllerRoute = implode(DIRECTORY_SEPARATOR,$controllerArr);
        $cFile = $this->getApplicationDirectory() . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR . $controllerRoute . ".php";
        if(is_file($cFile)){
            return implode("_",$controllerArr);
        }else{
            return false;
        }
    }

    private function generateControllerAndAction($uriArr=[]){
        switch (count($uriArr)){
            case 0:
                $controller = $this->getControllerName($this->getDefaultController());
                $action = $this->getDefaultAction();
                break;
            case 1:
                $controller = $this->getControllerName($uriArr);
                $action = $this->getDefaultAction();
                break;
            default:
                $controller = $this->getControllerName($uriArr);
                if(!$controller){
                    $action = array_pop($uriArr);
                    $controller = $this->getControllerName($uriArr);
                }else{
                    $action = $this->getDefaultAction();
                }
                break;
        }
        return $controller ? [$controller,$action] : [null,null];
    }

    public function route($request){
        $uriStr = trim($_SERVER['REQUEST_URI']," /");
        $pos = strpos($uriStr,"?");
        $requestUri = $pos === false ? $uriStr : substr($uriStr,0,$pos);
        $uriArr = array_filter(explode('/',$requestUri));
        list($controller,$action) = $this->generateControllerAndAction($uriArr);
        if(!empty($controller)){
            $request->setModuleName("Index");
            $request->setActionName($action);
            $request->setControllerName($controller);
        }else{
            return false;
        }
        return true;
    }

    public function assemble(array $info, array $query = NULL)
    {
        // TODO: Implement assemble() method.
    }
}