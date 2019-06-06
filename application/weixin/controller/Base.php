<?php
namespace app\weixin\controller;

class Base extends Common
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
        
        $this->login_info = session('weixin_user_info');
        $this->assign('login_info', $this->login_info);
        
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
            if(!session('weixin_user_info'))
			{
				header('Location: '.url('login/index'));exit;
			}
        }
	}
}