<?php
namespace app\fladmin\controller;

class Index extends Base
{
	public function index()
	{
        return $this->fetch();
    }
	
    public function upconfig()
	{
        updateconfig();
        $this->success('缓存更新成功！');
    }
    
    public function upcache()
	{
        dir_delete(APP_PATH.'../runtime/');
        $this->success('缓存更新成功！');
    }
}
