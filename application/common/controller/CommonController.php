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

    // 加载语言包
    public function load_language_pack()
    {
        $think_var = cookie('think_var');
        if (!$think_var) {
            $think_var = cache('think_var');
            if (!$think_var) {
                if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    $think_var = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                } else {
                    $think_var = 'zh-cn'; // 默认简体中文
                }
            }
            cookie('think_var', $think_var);
        }
        $lang = substr($think_var, 0, 4); // 只取前 4 位，这样只判断最优先的语言。如果取前 5 位，可能出现 en,zh 的情况，影响判断。
        if (preg_match("/zh-c/i", $lang)) {
            $res = "简体中文";
            $think_var = 'zh-cn';
        } elseif (preg_match("/zh/i", $lang)) {
            $res = "繁體中文";
            $think_var = 'zh-tw';
        } elseif (preg_match("/en/i", $lang)) {
            $res = "English";
            $think_var = 'en-us';
        } elseif (preg_match("/fr/i", $lang)) {
            $res = "French";
        } elseif (preg_match("/de/i", $lang)) {
            $res = "German";
        } elseif (preg_match("/jp/i", $lang)) {
            $res = "Japanese";
        } elseif (preg_match("/ko/i", $lang)) {
            $res = "Korean";
        } elseif (preg_match("/es/i", $lang)) {
            $res = "Spanish";
        } elseif (preg_match("/sv/i", $lang)) {
            $res = "Swedish";
        } elseif (preg_match("/ru/i", $lang)) {
            $res = "Russia";
        } else {
            $res = '语言切换';
            $think_var = 'zh-cn';
        }
        cookie('think_var', $think_var);
        \think\Lang::load(APP_PATH . 'common/lang/' . $think_var . '.php');
    }

}