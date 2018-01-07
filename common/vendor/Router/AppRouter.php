<?php
class Vendor_Router_AppRouter implements Yaf_Route_Interface{

    public function route($request){
        $uri = explode('/',trim($_SERVER['REQUEST_URI'],'/'));
        var_dump($uri);
        $request->setModuleName("Index");
        $request->setActionName(array_pop($uri));
        $request->setControllerName($uri[0] . "_" . array_pop($uri));

        return true;

    }

    public function assemble(array $info, array $query = NULL)
    {
        // TODO: Implement assemble() method.
    }
}