#!/bin/bash
killall php-fpm
sleep 1
/usr/local/php/bin/php-fpm start
