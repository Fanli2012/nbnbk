#!/bin/bash
killall nginx
sleep 1
/usr/local/nginx/sbin/nginx
sleep 1
/usr/local/php/bin/php /var/www/crond/task_exec_time.php
