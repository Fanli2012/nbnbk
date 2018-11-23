<?php
use	think\Route;

// 路由配置文件
// 把规则长的url放到前面，优先匹配，不然会出错，比如分页

//Route::rule('hello/:name','index/index/hello');

// 设置name变量规则（采用正则定义）
/* Route::pattern('name','\w+');
// 支持批量添加
Route::pattern([
    'name'  =>		'\w+',
    'id'    =>		'\d+',
]); */

//子域名路由配置，需在config开启【域名部署】'url_domain_deploy' => true
Route::domain('m',	function(){
    // 批量路由规则设置
    Route::rule([
        //其它
        'sitemap'           => array('index/Index/sitemap',array('ext'=>'xml')), //XML地图
        //文章
        'articlelist/[:key]'=> array('index/Article/index',array('key'=>'[a-z0-9]*')),
        'p/<id>'            => array('index/Article/detail',array('id'=>'\d+')),
        //标签
        'taglist/[:key]'    => array('index/Tag/index',array('key'=>'[a-z0-9]*')),
        'tag/<id>'          => array('index/Tag/detail',array('id'=>'\d+')),
        //店铺
        'shoplist/[:key]'   => array('index/Shop/index',array('key'=>'[a-z0-9]+')),
        'shop/<id>'         => array('index/Shop/detail',array('id'=>'\d+')),
        //商品
        'goodslist/[:key]'   => array('index/Goods/index',array('key'=>'[a-z0-9]*')),
        'goods/<id>'        => array('index/Goods/detail',array('id'=>'\d+')),
        //单页
        'pagelist/<key>'    => array('index/Page/index',array('key'=>'[a-z0-9]*')),
        'page/<id>'         => array('index/Page/detail',array('ext'=>'html'),array('id'=>'[a-z0-9]+')),
        
    ]);
});

return [
    /* '__pattern__' => [
        'name' => '\w+',
    ], */
    //其它
    'sitemap'           => array('index/Index/sitemap',array('ext'=>'xml')), //XML地图
    //文章
    'articlelist/[:key]'=> array('index/Article/index',array('key'=>'[a-z0-9]*')),
    'p/<id>'            => array('index/Article/detail',array('id'=>'\d+')),
    //标签
    'taglist/[:key]'    => array('index/Tag/index',array('key'=>'[a-z0-9]*')),
    'tag/<id>'          => array('index/Tag/detail',array('id'=>'\d+')),
    //店铺
    'shoplist/[:key]'   => array('index/Shop/index',array('key'=>'[a-z0-9]+')),
    'shop/<id>'         => array('index/Shop/detail',array('id'=>'\d+')),
    //商品
    'goodslist/[:key]'   => array('index/Goods/index',array('key'=>'[a-z0-9]*')),
    'goods/<id>'        => array('index/Goods/detail',array('id'=>'\d+')),
    //单页
    'pagelist/<key>'    => array('index/Page/index',array('key'=>'[a-z0-9]*')),
    'page/<id>'         => array('index/Page/detail',array('ext'=>'html'),array('id'=>'[a-z0-9]+')),
    
    //api路由
    'api/listarc'    => array('index/Server/listarc',array('method'=>'post')),
];