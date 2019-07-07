<?php
namespace app\weixin\controller;

class Base extends Common
{
	/**
     * 初始化
     * @param void
     * @return void
     */
	public function _initialize()
	{
        parent::_initialize();
        
		//判断是否登录
        $this->isLogin();
    }
	
	//判断是否登录
	public function isLogin()
	{
		//哪些方法不需要TOKEN验证
        $uncheck = array(
			'article/index',
			'article/detail',
			'articletype/index',
			'articletype/detail'
		);
        if (!in_array(strtolower(request()->controller().'/'.request()->action()), $uncheck))
        {
            $weixin_user_info = session('weixin_user_info');
            if(!($weixin_user_info && isset($weixin_user_info['token']['expire_time']) && $weixin_user_info['token']['expire_time'] > time()))
			{
                session('weixin_user_info', null);
				header('Location: '.url('login/index'));exit;
			}
        }
	}
}