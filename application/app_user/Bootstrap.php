<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initLoader(Yaf_Dispatcher $dispatcher){
        /* 注册本地类名前缀, 这部分类名将会在本地类库查找 */
        Yaf_Loader::getInstance()->registerLocalNameSpace(array());
    }

    public function _initConfig() {
        //把配置保存起来
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
        $dispatcher->getRouter()->addRoute("appRouter",new Vendor_Router_AppRouter());
//        $dispatcher->getRouter()->addRoute('default',new Yaf_Route_Static());
    }

    public function _initView(Yaf_Dispatcher $dispatcher){
        //在这里注册自己的view控制器，例如smarty,firekylin
    }
}