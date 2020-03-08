<?php

namespace app\user\controller;

use think\Controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;

class Login extends Controller
{
    /**
     * 登录页面
     */
    public function index()
    {
		$return_url = '';
        if (isset($_REQUEST['return_url']) && !empty($_REQUEST['return_url'])) {
            $return_url = $_REQUEST['return_url'];
            session('history_back_url', $return_url);
        }
        if ($return_url == '' && session('history_back_url')) {
            $return_url = session('history_back_url');
        }

        if (session('user_info')) {
            header("Location: " . url('user/Index/index'));
            exit;
        }

        return $this->fetch();
    }

    /**
     * 登录处理页面
     */
    public function dologin()
    {
        $return_url = '';
        if (isset($_REQUEST['return_url']) && !empty($_REQUEST['return_url'])) {
            $return_url = $_REQUEST['return_url'];
            session('history_back_url', $return_url);
        }
        if ($return_url == '' && session('history_back_url')) {
            $return_url = session('history_back_url');
        }

        //验证码验证
        if (!captcha_check(input('captcha', null))) {
            $this->error('验证码错误');
        }

        if (input('user_name', null) != null) {
            $user_name = input('user_name');
        } else {
            $this->error('请输入账号');
        }//用户名
        if (input('password', null) != null) {
            $password = md5(input('password'));
        } else {
            $this->error('请输入密码');
        }//密码
        //echo '<pre>';print_r($_POST);exit;
        //$sql = "(user_name = '".$user_name."' and password = '".$password."') or (email = '".$user_name."' and password = '".$password."')";
        $user = db("user")->where(function ($query) use ($user_name, $password) {
            $query->where('user_name', $user_name)->where('password', $password);
        })->whereOr(function ($query) use ($user_name, $password) {
            $query->where('email', $user_name)->where('password', $password);
        })->whereOr(function ($query) use ($user_name, $password) {
            $query->where('mobile', $user_name)->where('password', $password);
        })->find();

        if (!$user) {
            $this->error('登录失败！请重新登录');
        }

        session('user_info', logic('User')->getUserInfo(array('id' => $user['id'])));
        session('history_back_url', null);

        if ($return_url != '') {
            header('Location: ' . $return_url);
            exit;
        }

        header('Location: ' . url('user/Index/index'));
        exit;
    }

    /**
     * 注册
     */
    public function reg()
    {
        if (Helper::isPostRequest()) {
            $_POST['smstype'] = 1; //注册
            $res = logic('User')->pcRegister($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('user/Login/index'), '', 1);
            }

            $this->error($res['msg']);
        }

        return $this->fetch();
    }

    /**
     * 忘记密码
     */
    public function resetpwd()
    {
        if (Helper::isPostRequest()) {
            $_POST['smstype'] = 3; //密码修改
            $res = logic('User')->pcResetPwd($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('user/Login/index'), '', 1);
            }

            $this->error($res['msg']);
        }

        return $this->fetch();
    }

    /**
     * 注册获取短信验证码
     * @param $mobile 手机号
     * @param $captcha 验证码
     * @return string 成功失败信息
     */
    public function getRegSmscode()
    {
        //验证码验证
        if (!captcha_check(input('captcha', null))) {
            exit(json_encode(ReturnData::create(ReturnData::FAIL, null, '图形验证码错误')));
        }

        $mobile = input('mobile', null);
        $check = validate('VerifyCode');
        if (!$check->scene('get_smscode_by_smsbao')->check($_REQUEST)) {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, $check->getError())));
        }

        $res = model('VerifyCode')->getVerifyCodeBySmsbao($mobile, input('type', 1));
        if ($res['code'] == ReturnData::SUCCESS) {
            exit(json_encode(ReturnData::create(ReturnData::SUCCESS)));
        }

        exit(json_encode(ReturnData::create(ReturnData::FAIL, null, $res['msg'])));
    }

    //退出登录
    public function loginout()
    {
        session('user_info', null);
        $this->success('退出成功', '/');
    }
}