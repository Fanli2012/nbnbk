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

// 完整域名绑定到admin模块
Route::domain('m.thinkphp5.com','wap/Index');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
	// 定义普通路由
    //'detail/:id' => 'index/Index/detail',
    'tags'              => array('index/Index/tags',array('ext'=>'html')),
    'search'                        => 'index/Index/search',
    
    'cat<cat>/id<id>'   => array('index/Index/detail',array('ext'=>'html')), //详情页
    'cat<cat>/<page>'   => array('index/Index/category',array('ext'=>'html'),array('cat'=>'\d+','page'=>'\d+')), //分类页，分页
    'cat<cat>'          => ['index/Index/category',['ext'=>'html'],['cat'=>'\d+']], //分类页
    
    'tag<tag>/<page>'   => array('index/Index/tag',array('ext'=>'html'),array('tag'=>'\d+','page'=>'\d+')), //标签页，分页
    'tag<tag>'          => array('index/Index/tag',array('ext'=>'html'),array('tag'=>'\d+')), //标签页
    
    '<id>'              => array('index/Index/page',array('ext'=>'html'),array('id'=>'[a-zA-Z0-9]+')),
];