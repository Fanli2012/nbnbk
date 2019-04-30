<?php
if(strlen($_SERVER['REQUEST_URI'])>100){header("HTTP/1.1 404 Not Found");header("Status: 404 Not Found");exit;}
// 入口文件

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 加载框架引导文件
require __DIR__ . '/../FLi/start.php';