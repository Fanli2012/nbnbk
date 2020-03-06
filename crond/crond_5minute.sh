#!/bin/bash
# nginx
ps -ef | grep nginx |grep -v grep > /dev/null
if [ $? != 0 ];then
    killall nginx
    sleep 2
    /usr/local/nginx/sbin/nginx