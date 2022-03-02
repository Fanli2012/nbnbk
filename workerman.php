#!/usr/bin/env php
<?php
// 入口文件
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','push/Worker');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';

/*
在命令行下运行，启动监听服务
php workerman.php

打开chrome浏览器，先打开push.app域名下的网页（js跨域不能通讯），按F12打开调试控制台，在Console一栏输入(或者把下面代码放入到html页面用js运行)

ws = new WebSocket("ws://push.app:2346");
ws.onopen = function() {
    alert("连接成功");
    ws.send('tom');
    alert("给服务端发送一个字符串：tom");
};
ws.onmessage = function(e) {
    alert("收到服务端的消息：" + e.data);
};
继续测试

ws.send('保持连接，发第二次信息，查看服务器回应');
*/