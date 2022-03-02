<?php
// 入口文件

//禁止代理IP访问
empty($_SERVER['HTTP_VIA']) or exit('Access Denied');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';