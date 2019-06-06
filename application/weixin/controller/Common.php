<?php
namespace app\weixin\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;

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
		
		$this->isWechatBrowser = Helper::isWechatBrowser();
		$this->assign('isWechatBrowser', $this->isWechatBrowser);
		
    }
	
    //设置空操作
    public function _empty()
    {
        Helper::http404();
    }
}