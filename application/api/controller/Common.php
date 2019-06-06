<?php
namespace app\api\controller;
use think\Request;
use think\Db;
use think\Controller;

class Common extends Controller
{
    /**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
        parent::_initialize();
		
		//跨域访问
        if (config('app_debug') == true)
		{
            header("Access-Control-Allow-Origin:*");
            // 响应类型
            header("Access-Control-Allow-Methods:GET,POST");
            // 响应头设置
            header("Access-Control-Allow-Headers:x-requested-with,content-type,x-access-token,x-access-appid");
        }
    }
	
    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}