<?php

namespace app\common\lib;

class Helper
{
    //保留两位小数，最后一位会四舍五入
    public static function formatPrice($price)
    {
        return sprintf("%.2f", $price);
    }

    /**
     * 取得随机字符串
     *
     * @param int $length 生成随机数的长度
     * @param int $numeric 是否只产生数字随机数 0字母数字，1纯数字，2纯字母
     * @return string
     */
    public static function getRandomString($length, $numeric = 0)
    {
        $letter = ['0', 'a', '1', 'b', '2', 'c', '3', 'd', '4', 'e', '5', 'f', '6', 'g', '7', 'h', '8', 'i', '9', 'j', '2', 'k', '3', 'l', '4', 'm', '5', 'n', '6', 'o', '7', 'p', '8', 'q', '9', 'r', '2', 's', '3', 't', '4', 'u', 'v', 'w', 'x', 'y', 'z'];
        if ($numeric == 1) {
            $letter = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        } elseif ($numeric == 2) {
            $letter = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        }
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $letter[array_rand($letter, 1)];
        }
        return $result;
    }

    //生成二维码
    public static function qrcode($url, $size = 150)
    {
        return 'data:image/png;base64,' . base64_encode(\QrCode::format('png')->encoding('UTF-8')->size($size)->margin(0)->errorCorrection('H')->generate($url));
    }

    //获取浏览器信息
    public static function getBrowser()
    {
        $browser = array('name' => 'unknown', 'version' => 'unknown');

        if (empty($_SERVER['HTTP_USER_AGENT'])) return $browser;

        $agent = $_SERVER["HTTP_USER_AGENT"];

        // Chrome should checked before safari
        if (strpos($agent, 'Firefox') !== false) $browser['name'] = "firefox";
        if (strpos($agent, 'Opera') !== false) $browser['name'] = 'opera';
        if (strpos($agent, 'Safari') !== false) $browser['name'] = 'safari';
        if (strpos($agent, 'Chrome') !== false) $browser['name'] = "chrome";

        // Check the name of browser
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) $browser['name'] = 'ie';
        if (strpos($agent, 'Edge') !== false) $browser['name'] = 'edge';

        // Check the version of browser
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];
        if (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];
        if (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];
        if (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];

        if ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs)) $browser['version'] = $regs[1];
        if (preg_match('/rv:(\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];
        if (preg_match('/Edge\/(\d+)\..*/i', $agent, $regs)) $browser['version'] = $regs[1];

        return $browser;
    }

    /**
     * 检查是否是AJAX请求。
     * Check is ajax request.
     *
     * @static
     * @access public
     * @return bool
     */
    public static function isAjaxRequest()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true;
        if (isset($_GET['HTTP_X_REQUESTED_WITH']) && $_GET['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true;
        return false;
    }

    /**
     * 检查是否是POST请求
     */
    public static function isPostRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') return true;
        if ($_POST) return true;

        return false;
    }

    /**
     * 是否是GET提交的
     */
    public static function isGetRequest()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;
    }

    /**
     * 301跳转。
     * Header 301 Moved Permanently.
     *
     * @param  string $locate
     * @access public
     * @return void
     */
    public static function header301($locate)
    {
        header('HTTP/1.1 301 Moved Permanently');
        die(header('Location:' . $locate));
    }

    /**
     * 获取远程IP。
     * Get remote ip.
     *
     * @access public
     * @return string
     */
    public static function getRemoteIp()
    {
        $ip = '0.0.0.0';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim(current($arr));
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 建立文件夹
     *
     * @param string $aimUrl
     * @return viod
     */
    public static function createDir($aimUrl)
    {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        $result = true;

        foreach ($arr as $str) {
            $aimDir .= $str . '/';

            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir);
            }
        }

        return $result;
    }

    //判断访问终端是否是微信浏览器
    public static function isWechatBrowser()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }

        return false;
    }

    //判断是不是https
    public static function isHttpsRequest()
    {
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) return true;

        if ($_SERVER['SERVER_PORT'] == 443) return true;

        return false;
    }

    /**
     * @name php获取中文字符拼音首字母
     * @param $str
     * @return null|string
     */
    public function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }

        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;

        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';

        return '';
    }

    /**
     * 图片转base64
     * @param image_file String 图片路径
     * @return 转为base64的图片
     */
    public static function Base64EncodeImage($image_file)
    {
        if (file_exists($image_file) || is_file($image_file)) {
            $base64_image = '';
            $image_info = getimagesize($image_file);
            $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
            $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
            return $base64_image;
        }

        return false;
    }

    //提取数字
    public static function findNum($str = '')
    {
        $str = trim($str);
        if (empty($str)) {
            return '';
        }
        $reg = '/(\d{3}(\.\d+)?)/is';//匹配数字的正则表达式
        preg_match_all($reg, $str, $result);
        if (is_array($result) && !empty($result) && !empty($result[1]) && !empty($result[1][0])) {
            return $result[1][0];
        }

        return '';
    }

    /**
     * 过滤emoji
     */
    public static function filterEmoji($str)
    {
        // preg_replace_callback执行一个正则表达式搜索并且使用一个回调进行替换
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);

        return $str;
    }

    /**
     * http 404
     */
    public static function http404()
    {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }

    /**
     * http 301
     */
    public static function http301($url)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location:$url");
        exit;
    }

    /**
     * 下划线转驼峰
     * 思路:
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     */
    public static function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    /**
     * 驼峰命名转下划线命名
     * 思路:
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     */
    public static function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    //判断是移动端访问
    public static function is_mobile_access()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset ($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT']) {
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
            );
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * IP/域名校验 支持 IP(单IP,多IP,*通配符,IP段) 域名(单域名,多域名,*通配符)
     * 根据判断实现IP地址 白名单黑名单
     * 测试示例
        // 限制域名测试
        $domain = '2.baidu.com';
        $domain_list = '*.baidu.com,qq.com';
        if (in_host ( $domain, $domain_list )) {
            echo ('domain in');
        } else {
            echo ('domain is not in');
        }
        // 限制IP测试
        $host = '127.1.1.88';
        $list = '127.0.0.*,192.168.1.1,192.168.1.70,127.1.1.33-127.1.1.100';
        if (in_host ( $host, $list )) {
            echo ('ip in');
        } else {
            echo ('ip is not in');
        }
     * @param unknown $host 当前host 127.0.0.2
     * @param unknown $list 允许的host列表 127.0.0.*,192.168.1.1,192.168.1.70,127.1.1.33-127.1.1.100
     * @return boolean
     */
    public static function ip_domain_check($host, $list)
    {
        $list = ',' . $list . ',';
        $is_in = false;
        // 1.判断最简单的情况
        $is_in = strpos($list, ',' . $host . ',') === false ? false : true;
        // 2.判断通配符情况
        if (!$is_in && strpos($list, '*') !== false) {
            $hosts = array();
            $hosts = explode('.', $host);
            // 组装每个 * 通配符的情况
            foreach ($hosts as $k1 => $v1) {
                $host_now = '';
                foreach ($hosts as $k2 => $v2) {
                    $host_now .= ($k2 == $k1 ? '*' : $v2) . '.';
                }
                // 组装好后进行判断
                if (strpos($list, ',' . substr($host_now, 0, -1) . ',') !== false) {
                    $is_in = true;
                    break;
                }
            }
        }
        // 3.判断IP段限制
        if (!$is_in && strpos($list, '-') !== false) {
            $lists = explode(',', trim($list, ','));
            $host_long = ip2long($host);
            foreach ($lists as $k => $v) {
                if (strpos($v, '-') !== false) {
                    list ($host1, $host2) = explode('-', $v);
                    if ($host_long >= ip2long($host1) && $host_long <= ip2long($host2)) {
                        $is_in = true;
                        break;
                    }
                }
            }
        }
        return $is_in;
    }

	/**
	 * 二维数组根据某个字段排序
	 * @param array $array 要排序的数组
	 * @param string $keys 要排序的键字段
	 * @param string $sort 排序类型 SORT_ASC SORT_DESC 
	 * @return array 排序后的数组
	 */
	public static function arraySort($array, $keys, $sort = SORT_DESC) {
		$keysValue = [];
		foreach ($array as $k => $v) {
			$keysValue[$k] = $v[$keys];
		}
		array_multisort($keysValue, $sort, $array);
		return $array;
	}
}