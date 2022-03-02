<?php
namespace app\weixin\controller;
use app\common\lib\Helper;

class Util
{
	//微信端接口请求
    public static function curl_request($url, $params = array(), $method = 'GET', $headers = array())
    {
		$res = curl_request($url, $params, $method, $headers);
		if ($res['code'] == 8005 || $res['code'] == 9002)
		{
			//登录后跳转链接
			$return_url = url(request()->controller().'/'.request()->action());
			if($_SERVER['QUERY_STRING']){$return_url = $return_url.'?'.$_SERVER['QUERY_STRING'];}
			session('weixin_history_back_url', $return_url);
			$url = url('login/index');
			header('Location: '.$url);exit;
		}
		
		return $res;
	}

	/**
     * 设置Cookie
     * @access public
	 * @param $name Cookie名称
     * @param $value Cookie值
     * @param int $expire Cookie的过期时间，如果设置成零或者忽略参数，Cookie会在会话结束时过期(也就是关掉浏览器时)，time()+60*60*24*30就是设置Cookie 30天后过期
     * @return bool
     */
    public static function set_cookie($name, $value, $expire = 0)
    {
		setcookie($name, $value, $expire, '/');
    }

    /**
     * 数据集为JSON字符串
     * @access public
     * @param array $data 数据
     * @param integer $options json参数
     * @return string
     */
    public static function echo_json($data, $options = JSON_UNESCAPED_UNICODE)
    {
        exit(json_encode($data, $options));
    }
}