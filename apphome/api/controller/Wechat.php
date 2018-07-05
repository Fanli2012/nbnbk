<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\lib\wechat\WechatAuth;

class Wechat extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    // 以code换取 用户唯一标识openid 和 会话密钥session_key
    public function miniprogramWxlogin()
	{
        //参数
        $where = array();
        $code = input('code',null);
        if($code == null){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $wechat = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $wechat->miniprogram_wxlogin($code);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
}