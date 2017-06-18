<?php
namespace app\fladmin\controller;

class User extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function index()
    {
        return $this->fetch();
    }
    
    public function edit()
    {
		$this->assign('row',db('user')->where("id=1")->find());
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["username"])){$data['username'] = $map['username'] = $_POST["username"];}else{$this->success('用户名不能为空', CMS_ADMIN.'User/edit' , 3);exit;}//用户名
        if(!empty($_POST["oldpwd"])){$map['pwd'] = md5($_POST["oldpwd"]);}else{$this->success('旧密码错误', CMS_ADMIN.'User/edit' , 3);exit;}
        if($_POST["newpwd"]==$_POST["newpwd2"]){$data['pwd'] = md5($_POST["newpwd"]);}else{$this->success('密码错误', CMS_ADMIN.'User/edit' , 3);exit;}
        if($_POST["oldpwd"]==$_POST["newpwd"]){$this->error('新旧密码不能一致！', CMS_ADMIN.'User/edit' ,1);exit;}
        
        $User = db("user")->where($map)->find();
        
        if($User)
        {
            if(db('user')->where("id=1")->update($data)){session(null);$this->success('修改成功，请重新登录', CMS_ADMIN.'Login' , 3);}
        }
        else
        {
            $this->error('修改失败！旧用户名或密码错误', CMS_ADMIN.'User/edit' ,1);
        }
    }
}