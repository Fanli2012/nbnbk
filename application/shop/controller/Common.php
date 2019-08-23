<?php
namespace app\shop\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;

class Common extends Controller
{
    protected $login_info;
    /**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
		parent::_initialize();
		
        $this->login_info = session('shop_info');
        $this->assign('login_info', $this->login_info);
    }
	
    //设置空操作
    public function _empty()
    {
        return $this->error('您访问的页面不存在或已被删除');
    }
}