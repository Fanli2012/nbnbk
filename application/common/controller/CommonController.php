<?php
/**
 * 公共控制器统一继承
 */

namespace app\common\controller;

use think\Controller;
use app\common\lib\Helper;

class CommonController extends Controller
{
    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
        
        $server_name = trim($_SERVER['SERVER_NAME']);
        /* $allow_domain = array('tzlc.');
        foreach ($allow_domain as $value) {
            $value = trim($value);
            if (strpos($server_name, $value) !== false) {
                break;
            } else {
				exit;
			}
        } */
		if (Helper::ip_domain_check(request()->ip(), sysconfig('CMS_IP_BLACKLIST'))) {
			exit;
		}
        //$this->prevent_cc_attack();
    }

    //防止快速刷新，在3秒内连续刷新页面5次以上禁止访问
    public function prevent_cc_attack()
    {
        $seconds = '3'; //时间段[秒]
        $refresh = '5'; //刷新次数
        //设置监控变量
        $cur_time = time();
        if (session('last_time')) {
            session('refresh_times', (session('refresh_times') + 1));
        } else {
            session('refresh_times', 1);
            session('last_time', $cur_time);
        }
        //处理监控结果
        if (($cur_time - session('last_time')) < $seconds) {
            if (session('refresh_times') >= $refresh) {
                //拒绝访问
                exit('Access Denied');
            }
        } else {
            session('refresh_times', 0);
            session('last_time', $cur_time);
        }
    }
}