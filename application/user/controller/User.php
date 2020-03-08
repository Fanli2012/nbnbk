<?php

namespace app\user\controller;

use think\Db;
use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserLogic;
use app\common\lib\Validator;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new UserLogic();
    }

    public function index()
    {
        $this->assign('posts', db("page")->order('id desc')->select());
        return $this->fetch();
    }

    public function doadd()
    {
        $_POST['pubdate'] = time();//更新时间
        $_POST['click'] = rand(200, 500);//点击

        if (db("page")->insert($_POST)) {
            $this->success('添加成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('添加失败！请修改后重新添加', CMS_ADMIN . 'Page/add', 3);
        }
    }

    public function add()
    {
        return $this->fetch();
    }

    public function edit()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $id = "";
        }
        if (preg_match('/[0-9]*/', $id)) {
        } else {
            exit;
        }

        $this->assign('id', $id);
        $this->assign('row', db('page')->where("id=$id")->find());

        return $this->fetch();
    }

    public function doedit()
    {
        if (!empty($_POST["id"])) {
            $id = $_POST["id"];
            unset($_POST["id"]);
        } else {
            $id = "";
            exit;
        }
        $_POST['pubdate'] = time();//更新时间

        if (db('page')->where("id=$id")->update($_POST)) {
            $this->success('修改成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('修改失败！请修改后重新添加', CMS_ADMIN . 'Page/edit?id=' . $_POST["id"], 3);
        }
    }

    public function del()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('删除失败！请重新提交', CMS_ADMIN . 'Page', 3);
        } //if(preg_match('/[0-9]*/',$id)){}else{exit;}

        if (db('page')->where("id in ($id)")->delete()) {
            $this->success('删除成功', CMS_ADMIN . 'Page', 1);
        } else {
            $this->error('删除失败！请重新提交', CMS_ADMIN . 'Page', 3);
        }
    }

    public function setting()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
			//验证数据
			$edit_data = array();
			if (!empty($_POST['sex'])) { $edit_data['sex'] = $_POST['sex']; }
			if (!empty($_POST['nickname'])) { $edit_data['nickname'] = $_POST['nickname']; }
			$validate = new Validate([
				['sex', 'require|in:0,1,2', '请选择性别|性别：1男2女'],
				['nickname', 'require|max:30', '昵称不能为空|昵称不能超过30个字符']
			]);
			if (!$validate->check($edit_data)) {
				$this->error($validate->getError());
			}
            $where['id'] = $this->login_info['id'];

			$edit_data['update_time'] = time();
			$res = model('User')->edit($edit_data, $where);
			if (!$res) {
				$this->error('操作失败');
			}

			session('user_info', $this->getLogic()->getUserInfo(array('id' => $this->login_info['id'])));
			$this->success('操作成功');
        }

        $this->assign('post', $this->getLogic()->getUserInfo($where));
        return $this->fetch();
    }

    public function change_password()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];
			if (!empty($_POST['password'])) {
				if (!Validator::isPWD($_POST['password'])) {
					$this->error('密码6-18位，至少一个大写字母，一个小写字母和一个数字');
				}
			}
			$validate = new Validate([
				['password', 'require|max:20', '密码不能为空|密码不能超过20个字符'],
				['old_password', 'require|max:20', '密码不能为空|密码不能超过20个字符'],
				['re_password', 'require|max:20|confirm:password', '确认密码不能为空|确认密码不能超过20个字符|密码与确认密码不一致'],
			]);
			if (!$validate->check($_POST)) {
				$this->error($validate->getError());
			}

			$record = model('User')->getOne($where);
			if (!$record) {
				$this->error('用户不存在');
			}

			if ($_POST['re_password'] != $_POST['password']) {
				$this->error('两次密码不一致');
			}
			if (logic('User')->passwordEncrypt($_POST['old_password']) != $record['password']) {
				$this->error('旧密码错误');
			}
			if (logic('User')->passwordEncrypt($_POST['password']) == $record['password']) {
				$this->error('新旧密码不能一致');
			}

			$res = model('User')->edit(['password' => logic('User')->passwordEncrypt($_POST['password'])], $where);
			if (!$res) {
				$this->error('操作失败');
			}

			session('user_info', null);
            $this->success('操作成功', url('user/login/index'), '', 1);
        }

        return $this->fetch();
    }

    //设置头像
    public function setavatar()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
			//验证数据
			if (empty($_POST['head_img'])) {
				$this->error('请上传头像');
			}

            $where['id'] = $this->login_info['id'];

            $post_data = array(
                'img' => $_POST['head_img']
            );
            $url = url('api/Image/base64ImageUpload');
            $res = curl_request($url, $post_data, 'POST');
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }
			
			if (!$res['data']) {
				$this->error('系统繁忙，请重新上传');
			}

            $res = model('User')->edit(['head_img' => $res['data'], 'update_time' => time()], $where);
            if (!$res) {
                $this->error('操作失败');
            }

            $this->success('操作成功');
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

    //设置封面
    public function setcover()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

    //设置二维码
    public function setqrcode()
    {
        $where['id'] = $this->login_info['id'];

        if (Helper::isPostRequest()) {
            $where['id'] = $this->login_info['id'];

            $postdata = array(
                'img' => $_POST['qrcode']
            );
            $url = url('api/Image/base64ImageUpload');
            $res = curl_request($url, $postdata, 'POST');
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $res = $this->getLogic()->edit(['qrcode' => $res['data']], $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg']);
            }

            $this->error($res['msg']);
        }

        $this->assign('post', $this->getLogic()->getOne($where));
        return $this->fetch();
    }

}