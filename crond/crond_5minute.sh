#!/bin/bash
# nginx
ps -ef | grep nginx |grep -v grep > /dev/null
if [ $? != 0 ];then
    killall nginx
    sleep 1
    /usr/local/nginx/sbin/nginx