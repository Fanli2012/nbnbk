<?php
if(strlen($_SERVER['REQUEST_URI'])>100){exit;}
// 入口文件

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 加载框架引导文件
require __DIR__ . '/../FLi/start.php';