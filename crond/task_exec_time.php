<?php
// 定时任务执行时间
file_put_contents(dirname(__FILE__) . "/task_exec_time.txt", "执行时间：" . date("Y-m-d H:i:s", time()) . "\n", FILE_APPEND); //写入txt文件追加
