<?php
class Bootstrap extends Yaf\Bootstrap_Abstract{

    public function _initLoader(Yaf\Dispatcher $dispatcher){
        /* 注册本地类名前缀, 这部分类名将会在本地类库查找 */
//        Yaf\Loader::getInstance()->registerLocalNameSpace([]);
    }

    public function _initConfig() {
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher) {
        //注册一个插件
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
        $dispatcher->getRouter()->addRoute("appRouter",new vendor\router\AppRouter());
//        $dispatcher->getRouter()->addRoute('default',new Yaf\Route_Static());
    }

    public function _initView(Yaf\Dispatcher $dispatcher){
        //在这里注册自己的view控制器,smarty 的使用放到Web_Controller_TplBase中去，当需要渲染页面的时候继承该基类
        $dispatcher->autoRender(false);
    }
}